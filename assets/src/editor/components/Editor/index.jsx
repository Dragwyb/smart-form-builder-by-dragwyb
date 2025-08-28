import React, { useState, useEffect, act } from 'react';
import { useSelector, useDispatch, useStore } from 'react-redux';
import Canvas from './Canvas';
import Preview from './Preview';
import { saveForm, resetSectionSettings, updateFieldValues, updateFieldOrder } from '../../store/actions';
import { Button, SaveBtn } from '../Common';
import { Dashicon } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { DndContext, useSensor, useSensors, PointerSensor, MouseSensor, TouchSensor } from '@dnd-kit/core';
import { restrictToParentElement, createSnapModifier } from '@dnd-kit/modifiers';
import SidebarFieldOverlay from '../SidebarFieldOverlay';
import { Utils as Helper, AddField } from '../Utils';
import ToolBar from '../Toolbar/Toolbar';
// import ToolbarSettings from '../Toolbar/ToolbarSettingsold';
import ToolbarSettings from '../Toolbar/ToolbarSettings';

const Editor = () => {
    const activeTab = useSelector(state => state.activeToolbar);
    const selectedSettingId = useSelector(state => state.selectedSettingId);
    const previewMode = useSelector(state => state.previewMode);
    const formData = {};
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

    useEffect(() => {
        if (sectionSettings) {
            dispatch(resetSectionSettings());
        }
    }, [selectedSettingId])

    const handleExit = () => {
        window.location.href = DragwybEditor.adminUrl;
    };

    const setSelectedSettingId = ({id, tab= 'fields'}) => {
        Utils.setSelectedSettingId({ value: id });
        const defaultToolbar = DragwybEditor?.EditorToolbars?.Default ?? false;

        Utils.setActiveTab({ value: false === id ? defaultToolbar : tab });
    }

    const setActiveTabHandler = (value) => {
        Utils.setSelectedSettingId({ value: false });
        Utils.setActiveTab({ value: value });
        Utils.setPreviewMode({ value: false });
    }

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
                false !== dropIndex && setDropIndex(false);
                false !== dropIndicatorPosition && setDropIndicatorPosition(false)
                return;
            }

            let newdropIndex = over.data.current.currentIndex;

            if ((over.data.current.addInitialField || over.data.current.canvasFieldDrop) && dropIndex !== newdropIndex) {
                setDropIndex(newdropIndex);
                false !== dropIndicatorPosition && setDropIndicatorPosition(false)
                return;
            }

            if ((over.data.current.addInitialField || over.data.current.canvasFieldDrop) && dropIndex === newdropIndex) {
                false !== dropIndicatorPosition && setDropIndicatorPosition(false)
                return;
            }

            let activeIndex = active?.data?.current?.currentIndex;
            let extraTop = 0;

            if (newdropIndex === dropIndex) {
                extraTop += 15;
            }

            if (newdropIndex > activeIndex) {
                extraTop -= activeDrag.extraData.height + 15;
            }

            const movingPosiont = event.activatorEvent.clientY + delta.y;
            const overRect = over.rect.top + over.rect.height / 2 + extraTop;

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
        } else {
            false !== dropIndex && setDropIndex(false);
            false !== dropIndicatorPosition && setDropIndicatorPosition(false)
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

            setSelectedSettingId({id: newField._id});

            return;
        } else if (isCanvasDrag) {
            const oldIndex = active?.data?.current?.currentIndex;

            if (oldIndex !== index) {
                dispatch(updateFieldOrder(oldIndex, index));
            }
        }
    };

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
                <div className="dragwyb-editor__actions">
                    <Button onClick={() => Utils.setPreviewMode({ value: !previewMode })} className='dragwyb-preview'>
                        <i className={`far fa-eye${previewMode ? '-slash' : ''}`} />
                        {previewMode ? __('Disable', 'dragwyb-form-builder') : __('Enable', 'dragwyb-form-builder')}
                    </Button>
                    <Button onClick={handleExit} className=''>
                        {DragwybBuilder.i18n.exit}
                    </Button>
                    <SaveBtn />
                </div>
            </div>
            <div className="dragwyb-editor__body">
                {previewMode ? (
                    <Preview values={values} errors={errors}/>
                ) : (
                    <>
                        <DndContext
                            sensors={sensors}
                            onDragEnd={handleDragEnd}
                            onDragCancel={() => {
                                setActiveDrag(null);
                            }}
                            onDragMove={handleDragMove}
                        >
                            <div className="dragwyb-editor__sidebar">
                                {activeTab && <ToolbarSettings
                                    setting={activeTab}
                                    selectedToolbar={selectedSettingId}
                                    setActiveTab={setActiveTabHandler}
                                />}
                            </div>
                            <div className="dragwyb-editor__main">
                                <Canvas
                                    selectedSettingId={selectedSettingId}
                                    onFieldSelect={setSelectedSettingId}
                                    values={values}
                                    errors={errors}
                                    Utils={Utils}
                                    sidebarDrag={sidebarDrag}
                                    dropIndex={dropIndex}
                                    dropIndicatorPosition={dropIndicatorPosition}
                                    activeTab={activeTab}
                                    setActiveTab={setActiveTabHandler}
                                />
                            </div>
                            <ToolBar activeTab={activeTab} setActiveTab={setActiveTabHandler} setSettingId={setSelectedSettingId}/>
                            {activeDrag && <SidebarFieldOverlay data={activeDrag} />}
                        </DndContext>
                    </>
                )}
            </div>
        </div>
    );
};

export default Editor; 