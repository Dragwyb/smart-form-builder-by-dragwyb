import React, { useState, useEffect, act } from 'react';
import { useSelector, useDispatch, useStore } from 'react-redux';
import Canvas from './Canvas';
import FieldSettings from './FieldSettings';
import FormSettings from './FormSettings';
import Preview from './Preview';
import { saveForm, resetSectionSettings, updateFieldValues, updateFieldOrder } from '../../store/actions';
import { Button } from '../Common';
import { Dashicon } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { DndContext, useSensor, useSensors, PointerSensor, MouseSensor, TouchSensor } from '@dnd-kit/core';
import { restrictToParentElement, createSnapModifier } from '@dnd-kit/modifiers';
import SidebarFieldOverlay from '../SidebarFieldOverlay';
import { Utils as Helper, AddField } from '../Utils';
import ToolBar from '../Toolbar/Toolbar';
import ToolbarSettings from '../Toolbar/ToolbarSettings';

const Editor = () => {
    const activeTab = useSelector(state => state.activeToolbar);
    const selectedField = useSelector(state => state.selectedField);
    const previewMode = useSelector(state => state.previewMode);
    const formData = useSelector(state => state.form);
    const fields = useSelector(state => state.form.fields); // Assuming fields are stored in Redux
    const formStatus = useSelector(state => state.formStatus || 'draft');
    const [activeDrag, setActiveDrag] = useState(null);
    const [sidebarDrag, setSidebarDrag] = useState(null);
    const [dropIndicatorPosition, setDropIndicatorPosition] = useState(false);
    const [dropIndex, setDropIndex] = useState(false);

    const values = useSelector(state => state.values); // Assuming values are stored in Redux

    const errors = useSelector(state => state.errors); // Assuming errors are stored in Redux
    const sectionSettings = useSelector(state => state.sectionSettings);

    const dispatch = useDispatch();


    const store = useStore();
    const state = store.getState();

    const Utils = Helper(state, dispatch);

    const handleSave = async () => {
        try {
            dispatch(saveForm(formData));
        } catch (error) {
            console.error('Save failed:', error);
        }
    };

    useEffect(() => {
        if (sectionSettings) {
            dispatch(resetSectionSettings());
        }
    }, [selectedField])

    const handleExit = () => {
        window.location.href = DragwybEditor.adminUrl;
    };

    const setSelectedFieldHandler = (field) => {
        Utils.setSelectedField({ value: field });
        const defaultToolbar = DragwybEditor?.EditorToolbars?.Default ?? false;
        Utils.setActiveTab({ value: false === field ? defaultToolbar : false });
    }

    const setActiveTabHandler = (value) => {
        Utils.setSelectedField({ value: false });
        Utils.setActiveTab({ value: value });
    }

    const setPreviewModeHandler = (value) => {
        console.log(value);
        Utils.setPreviewMode({ value: value });
    }

    const selectedFieldSetting = () => {
        let value = null;
        Object.values(fields).forEach(field => {
            if (field._id === selectedField._id) {
                value = field;
            }
        })

        return value;
    }

    const fieldValueHandler = ({ fieldId, value }) => {
        dispatch(updateFieldValues(fieldId, value));
    };

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 5 // drag starts only after moving 5px
            }
        }),
        useSensor(MouseSensor, {
            activationConstraint: {
                distance: 5 // drag starts only after moving 5px
            }
        }),
        useSensor(TouchSensor, {
            activationConstraint: {
                delay: 5, // drag starts only after moving 5px
                tolerance: 6
            }
        })
    );

    const gridSize = 20; // pixels
    const snapToGridModifier = createSnapModifier(gridSize);

    const handleDragMove = (event) => {
        const { active, over, delta, activatorEvent } = event;

        if (activeDrag === null) {
            const activeDragData = { activeDrag: active };

            if (over && activatorEvent) {
                activeDragData.extraData = { offsetX: activatorEvent.offsetX, offsetY: activatorEvent.offsetY, width: over.rect.width, height: over.rect.height }
            }

            if (active?.data?.current?.currentIndex >= 0) {
                setDropIndex(active.data.current.currentIndex);
            }

            setActiveDrag(activeDragData);
        }

        if (over) {
            const activeId = active.id.replace('canvas-drag-', '');
            const overId = over?.id.replace('canvas-drop-', '');

            if (!overId) {
                false !== dropIndex && setDropIndex(false);
                false !== dropIndicatorPosition && setDropIndicatorPosition(false)
                return;
            };

            // Skip if we’re hovering over ourselves
            if (activeId === overId) {
                return;
            }

            let extraTop = 0;


            let newdropIndex = over.data.current.currentIndex;
            let activeIndex = active?.data?.current?.currentIndex;

            if (newdropIndex === dropIndex) {
                extraTop += 15;
            }

            if (newdropIndex > activeIndex) {
                extraTop -= activeDrag.extraData.height + 15;
            }

            const movingPosiont = event.activatorEvent.clientY + delta.y;
            const overRect = over.rect.top + over.rect.height / 2 + extraTop;

            let indicatorPos = 'top';

            if (active?.data?.current?.canvasDrag && newdropIndex > activeIndex) {
                newdropIndex--;
                dropIndicatorPosition !== 'bottom' && setDropIndicatorPosition('bottom');
            } else {
                dropIndicatorPosition !== false && setDropIndicatorPosition(false);
            }

            if (overRect < movingPosiont) {
                newdropIndex++;
            }

            if (dropIndex !== newdropIndex) {
                setDropIndex(newdropIndex);
            }
        }
    }

    const handleDragEnd = (event) => {

        setActiveDrag(null);
        setSidebarDrag(null);
        setDropIndex(false)
        setDropIndicatorPosition(false)

        const { active, over } = event;

        if (!over) return;
        if (!over.id.startsWith('canvas-drop-')) return;
        if (!over.data.current || over.data.current.canvasDrop === null || over.data.current.canvasDrop === undefined) return;

        const isFromSidebar = active?.data?.current?.fromSidebar;
        const isCanvasDrag = active?.data?.current?.canvasDrag;
        const index = dropIndex;

        if (isFromSidebar) {
            const type = active.data.current.type;

            const newField = Utils.AddField({ type, Utils, index: index });

            dispatch({ type: 'ADD_FIELD_AT_INDEX', payload: { field: newField, index: fields.length } });
            setSelectedFieldHandler(newField);
            return;
        } else if (isCanvasDrag) {
            const oldIndex = active?.data?.current?.currentIndex;

            if (oldIndex !== index) {
                dispatch(updateFieldOrder(oldIndex, index));
            }
        }
    };

    const mainTabs = {
        fields:
        {
            iconCls: 'fas fa-plus mr-2 text-sm',
            settingName: 'fields'
        },
        general:
        {
            iconCls: 'fas fa-paint-brush mr-2 text-sm',
            settingName: 'general'
        },
        advance:
        {
            iconCls: 'fas fa-cog mr-2 text-sm',
            settingName: 'settings'
        }
    }

    return (
        <div className="dragwyb-editor">
            <div className="dragwyb-editor__header">
                <div className="dragwyb-editor__details">
                    <h2>Dragwyb Form Builder</h2>
                    <div className="dragwyb-editor__title">
                        {/* <input
                            type="text"
                            value={}
                            onChange={e => dispatch({
                                type: 'UPDATE_FORM_TITLE',
                                payload: e.target.value
                            })}
                            placeholder={DragwybBuilder.i18n.formTitle}
                        /> */}
                        <p>
                            {formData.title}
                        </p>
                    </div>
                    <div className="dragwyb-editor__status" data-status={formStatus}>
                        <span data-status={formStatus}></span>
                        <p>{formStatus.charAt(0).toUpperCase() + formStatus.slice(1)}</p>
                    </div>
                </div>
                <div className='dragwyb-editor__tabs'>
                    {Object.keys(mainTabs).map(tab => {
                        return <div className="dragwyb-editor__tab"
                            onClick={() => {
                                setActiveTab(mainTabs[tab].settingName);
                                setSelectedField(null)
                                setPreviewMode(false);
                            }}
                            title={DragwybBuilder.i18n[tab]}
                            data-tab={mainTabs[tab].settingName}
                        >
                            <Button className={`${activeTab === mainTabs[tab].settingName ? ' active' : ''}`}>
                                <i className={mainTabs[tab].iconCls} />
                                {DragwybBuilder.i18n[tab]}
                            </Button>
                        </div>
                    })}
                </div>
                <div className="dragwyb-editor__actions">
                    <Button onClick={() => Utils.setPreviewMode({ value: !previewMode })} className='dragwyb-preview'>
                        <i className={`far fa-eye${previewMode ? '-slash' : ''}`} />
                        {previewMode ? __('Disable', 'dragwyb-form-builder') : __('Enable', 'dragwyb-form-builder')}
                    </Button>
                    <Button onClick={handleExit} className=''>
                        {DragwybBuilder.i18n.exit}
                    </Button>
                    <Button onClick={handleSave} className='primary'>
                        <i className="fas fa-save mr-2" />
                        {DragwybBuilder.i18n.save}
                    </Button>
                </div>
            </div>
            <div className="dragwyb-editor__body">
                {previewMode ? (
                    <Preview fields={fields} values={values} errors={errors} onChange={fieldValueHandler} />
                ) : (
                    <>
                        <DndContext
                            sensors={sensors}
                            onDragEnd={handleDragEnd}
                            onDragCancel={() => {
                                setActiveDrag(null);
                            }}
                            onDragMove={handleDragMove}
                        // modifiers={[restrictToParentElement, snapToGridModifier]}
                        >
                            <div className="dragwyb-editor__sidebar">
                                {/* {activeTab === 'fields' && (<Controls onFieldSelect={setSelectedFieldHandler} Utils={Utils} />)}
                                {activeTab === 'settings' && (<FormSettings />)}
                                {selectedField && (
                                    <div className="dragwyb-editor__settings">
                                        <FieldSettings
                                            activeField={selectedField}
                                            fieldValue={selectedFieldSetting()}
                                            onClose={() => setSelectedFieldHandler(null)}
                                            key={selectedField.id}
                                            sectionSettings={sectionSettings}
                                        />
                                    </div>
                                )} */}
                                {activeTab && <ToolbarSettings setting={activeTab} Utils={Utils} />}
                                {selectedField && (
                                    <div className="dragwyb-editor__settings">
                                        <FieldSettings
                                            activeField={selectedField}
                                            fieldValue={selectedFieldSetting()}
                                            onClose={() => setSelectedFieldHandler(false)}
                                            key={selectedField.id}
                                            sectionSettings={sectionSettings}
                                        />
                                    </div>
                                )}
                            </div>
                            <div className="dragwyb-editor__main">
                                <Canvas
                                    selectedField={selectedField}
                                    onFieldSelect={setSelectedFieldHandler}
                                    fields={fields}
                                    values={values}
                                    onChange={({ fieldId, value }) => fieldValueHandler({ fieldId, value })}
                                    errors={errors}
                                    Utils={Utils}
                                    sidebarDrag={sidebarDrag}
                                    dropIndex={dropIndex}
                                    dropIndicatorPosition={dropIndicatorPosition}
                                />
                            </div>
                            <ToolBar toolbars={mainTabs} activeTab={activeTab} setActiveTab={setActiveTabHandler} setPreviewMode={setPreviewModeHandler} />
                            {activeDrag && <SidebarFieldOverlay data={activeDrag} fields={fields} />}
                        </DndContext>
                    </>
                )}
            </div>
        </div>
    );
};

export default Editor; 