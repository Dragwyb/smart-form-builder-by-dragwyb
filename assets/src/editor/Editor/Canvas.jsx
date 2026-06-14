import React, { useCallback, useMemo, useRef } from "react";
import { useDispatch, useSelector, useStore } from "react-redux";
import { useDraggable, useDroppable } from "../components/Common";
import * as Fields from "./Fields";
import { addField } from "../store/actions";
import { __, sprintf } from "@wordpress/i18n";
import { Utils as Helper } from '../components/Utils';
import IconsManager from "../components/IconsManager";

const RenderItem = React.memo(({
    fieldId,
    values,
    index,
    dropInfo,
    onFieldSelect,
    onDuplicate,
    onDelete,
    errors,
    Utils,
    lastContainer = true,
    store,
    perviewIFrame
}) => {

    const isButtonContainerFunc = field => {
        if (lastContainer === false) return false;

        if (!field || !field.is_root_container || !field.children || field.children.length === 0) {
            return false;
        }

        const state = store.getState();
        const formFields = state.form.fields;

        return field.children.some(childId => {
            const childField = formFields[childId];
            return childField && childField.type === 'button';
        });
    };

    const field = useSelector((state) => state.form.fields[fieldId]);

    const isButtonContainer = useMemo(() => {
        return isButtonContainerFunc(field);
    }, [field])

    if (!field) {
        return null;
    }

    const fieldSettings = DragwybEditor.fields.fields[field.type];
    const allowedChildren = fieldSettings?.allow_child || false;
    const isRootContainer = field?.is_root_container || false;
    const childrens = field.children;

    const { setNodeRef: dropRef } = allowedChildren === true ? useDroppable({
        id: `canvas-drop-field-${field._id}`,
        data: {
            canvasDrop: true,
            currentId: isRootContainer ? 'root' : fieldId,
            index: index,
        },
    }) : {};

    const {
        attributes,
        listeners,
        setNodeRef: dragRef,
        isDragging,
    } = allowedChildren === true && isButtonContainer === false ? useDraggable({
        id: `canvas-drag-field-${field._id}`,
        data: {
            canvasDrag: true,
            currentId: fieldId,
            index: index,
        },
    }) : {};

    const setNodeRef = useCallback((Node) => {
        if (!Node) return;
        if (dropRef) dropRef(Node);
        if (dragRef) dragRef(Node);
    }, [dropRef, dragRef]);

    let wrapperClass = [];
    if (field.type !== 'row') {
        wrapperClass = ['dragwyb-field-wrapper', `dragwyb-${field.type}-field`];
    }
    let id = `dragwyb-field-wrapper-${field._id}`;

    if (field.css_classes) {
        wrapperClass.push(field.css_classes);
    }

    if (allowedChildren === true) {
        wrapperClass.push(`dragwyb-${field.type}`, 'dragwyb-has-actions');
        id = `dragwyb-${field.type}-${field._id}`;
    }

    if (['button', 'file', 'radio', 'checkbox', 'range'].includes(field.type)) {
        wrapperClass.push("dragwyb-no-float");
    }

    wrapperClass = DragwybBuilder.Hooks.applyFilter('Dragwyb/Field/WrapperClass', wrapperClass, fieldId, field.type, field.attributes, Utils);
    wrapperClass = DragwybBuilder.Hooks.applyFilter(`Dragwyb/Field/WrapperClass/${field.type}`, wrapperClass, fieldId, field.type, field.attributes, Utils);

    const onRootContainerSelect = useCallback(() => {
        const id = field._id;

        if (!id) {
            return;
        }

        onFieldSelect({ id });
    }, [field._id, onFieldSelect]);

    const onFieldSelectHandler = useCallback((e) => {
        const id = field._id;

        if (!id || isRootContainer) {
            return;
        }

        onFieldSelect({ id });
    }, [field._id, isRootContainer, onFieldSelect]);

    return (
        <>
            {dropInfo && dropInfo.index === index && (dropInfo.targetId === fieldId || (isRootContainer && dropInfo.targetId === 'root')) && (
                <div className="dragwyb-editor-indicator"></div>
            )}
            {!isDragging &&
                <div
                    ref={setNodeRef}
                    className={wrapperClass.join(' ')}
                    onClick={onFieldSelectHandler}
                    {...listeners}
                    {...attributes}
                    id={id}
                >
                    <Fields.Preview fields={[field]} values={values} errors={errors} childrens={childrens} Utils={Utils} perviewIFrame={perviewIFrame}>
                        {childrens && childrens.length > 0 && (
                            childrens.map((childId, childIndex) => (
                                <React.Fragment key={childId || `empty-${childIndex}`}>
                                    {!childId ? null :
                                        <RenderItem
                                            fieldId={childId}
                                            index={childIndex}
                                            dropInfo={dropInfo}
                                            onFieldSelect={onFieldSelect}
                                            onDuplicate={onDuplicate}
                                            onDelete={onDelete}
                                            values={values}
                                            errors={errors}
                                            Utils={Utils}
                                            perviewIFrame={perviewIFrame}
                                        />
                                    }
                                </React.Fragment>
                            ))
                        )}
                    </Fields.Preview>
                    {isRootContainer && false === dropInfo && (
                        <div className="field-actions">
                            <button
                                title={__("Duplicate", "smart-form-builder-by-dragwyb")}
                                className="duplicate"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    onDuplicate(field);
                                }}
                            >
                                <span className="dashicons dashicons-admin-page"></span>
                            </button>
                            <button
                                title={sprintf(__('Edit %s', 'smart-form-builder-by-dragwyb'), fieldSettings.label)}
                                className="settings"
                                onClick={onRootContainerSelect}
                            >
                                <span className="dashicons dashicons-menu"></span>
                            </button>
                            <button
                                title={isButtonContainer ? __("Cannot delete button", "smart-form-builder-by-dragwyb") : __("Delete", "smart-form-builder-by-dragwyb")}
                                className={`delete ${isButtonContainer ? 'disabled' : ''}`}
                                onClick={(e) => {
                                    if (isButtonContainer) {
                                        return;
                                    }
                                    e.stopPropagation();
                                    onDelete(field._id);
                                }}
                                disabled={isButtonContainer}
                            >
                                <span className="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    )}
                </div>
            }
        </>
    );
});

RenderItem.displayName = 'RenderItem';

const AddFieldMsg = React.memo(({ setActiveTab, updateFieldSelect }) => {
    const activeTab = useSelector((state) => state.activeToolbar);

    const { setNodeRef, isOver } = useDroppable({
        id: `canvas-drop-`,
        data: {
            canvasFieldDrop: true,
            canvasDrop: true,
            currentId: null,
            index: 0
        },
    });

    let emptyMessage = __("Click or drag fields here to start building.", "smart-form-builder-by-dragwyb");
    if (isOver) {
        emptyMessage = __("Drop field here", "smart-form-builder-by-dragwyb");
    }

    return (
        <div
            ref={setNodeRef}
            className={`dragwyb-canvas__add-field${isOver ? " active" : ""}`}
            onClick={() => {
                if (activeTab === 'fields') {
                    updateFieldSelect({ id: false });
                } else {
                    setActiveTab("fields");
                }
            }}
        >
            <div className={`dragwyb-canvas__add-field-wrapper ${isOver ? " drag-active" : ""}`}>
                {(!isOver && activeTab !== 'fields') && <span className="dragwyb-canvas__add-field-icon"><IconsManager.renderIcons icon={{ type: 'solid', icon: 'plus' }} /></span>}
                <p>{emptyMessage}</p>
            </div>
        </div>
    );
});

AddFieldMsg.displayName = 'AddFieldMsg';

const Canvas = ({
    onFieldSelect,
    dropInfo,
    setActiveTab
}) => {
    // const values = useSelector((state) => state.values);
    const values = {};
    const errors = useSelector((state) => state.errors);
    const rootContainers = useSelector((state) => state.form.rootContainers);
    const dispatch = useDispatch();
    const store = useStore();
    const state = store.getState();
    const perviewIFrame = useSelector(state => state.iframeEle);

    const Utils = useMemo(() => {
        return Helper(state, dispatch);
    }, [state, dispatch]);

    const { setNodeRef, isOver } = useDroppable({
        id: `canvas-drop-wrapper`,
        data: {
            canvasFieldDrop: true,
            canvasDrop: true,
            currentId: null,
        },
    });

    const handleDuplicateField = useCallback((field, index, parentID = null) => {
        const formData = store.getState().form;
        let deepClone = JSON.parse(JSON.stringify(field));
        const id = Utils.generateId();
        deepClone._id = id;

        if (DragwybEditor.fields.fields[deepClone.type] && DragwybEditor.fields.fields[deepClone.type].controls) {
            const fieldControls = DragwybEditor.fields.fields[deepClone.type].controls;

            if (fieldControls.field_id) {
                deepClone.attributes.field_id = `field_${deepClone._id}`;
            }
        }

        const type = deepClone.type;

        if (DragwybEditor.fields.fields[type] && DragwybEditor.fields.fields[type].controls) {
            const fieldControls = DragwybEditor.fields.fields[type].controls;
            Object.keys(deepClone.attributes).forEach(id => {
                if (!['tabs', 'tab', 'section'].includes(fieldControls[id]?.type)) {
                    deepClone.attributes[id] = DragwybBuilder.Hooks.applyFilter(`Dragwyb/Editor/DuplicateControl/${fieldControls[id].type}.duplicateValue`, deepClone.attributes[id], fieldControls[id], Utils);
                }
            })
        }

        let childreIds = [];
        if (deepClone.is_root_container && deepClone.children.length > 0) {
            childreIds = deepClone.children;
            deepClone.children = [];
        }

        if (deepClone.parentId && parentID) {
            deepClone.parentId = parentID;
        }

        dispatch(addField({ field: deepClone, index: index + 1 }));
        onFieldSelect({ id: deepClone._id });
        Utils.duplicateStyleSelectors({ cloneId: deepClone._id, currentId: field._id, dispatch, state: store.getState() });

        if (childreIds.length > 0) {
            childreIds.forEach((childId, childIndex) => {
                handleDuplicateField(formData.fields[childId], childIndex - 1, deepClone._id);
            });
        }
    }, [Utils, dispatch, onFieldSelect, store]);

    const handleDeleteField = useCallback((id) => {
        onFieldSelect({ id: false });
        dispatch({ type: "DELETE_FIELD", payload: id });
    }, [dispatch, onFieldSelect]);

    let canvasCls = "dragwyb-canvas";
    if (!rootContainers || rootContainers.length === 0) {
        canvasCls += " canvas-empty";
    }

    return (
        <div className="dragwyb-editor__main">
            <div className="dragwyb-canvas-wrapper" ref={setNodeRef}>
                <div className={canvasCls}>
                    <div
                        className="dragwyb-form-wrapper"
                        id={`dragwyb-form-wrapper-${DragwybEditor.formId}`}
                    >
                        <form className="dragwyb-form" action="#" onSubmit={(e) => { e.preventDefault(); return false; }}>
                            {rootContainers && rootContainers.length > 0 && (
                                <>
                                    {rootContainers.map((fieldKey, index) => (
                                        <RenderItem
                                            key={fieldKey}
                                            fieldId={fieldKey}
                                            values={values}
                                            onFieldSelect={onFieldSelect}
                                            onDuplicate={(field) => handleDuplicateField(field, index)}
                                            onDelete={handleDeleteField}
                                            errors={errors}
                                            index={index}
                                            dropInfo={dropInfo}
                                            Utils={Utils}
                                            lastContainer={rootContainers.length === index + 1}
                                            store={store}
                                            perviewIFrame={perviewIFrame}
                                        />
                                    ))}
                                </>
                            )}
                        </form>
                        {(!rootContainers || rootContainers.length === 0) && (
                            <AddFieldMsg
                                setActiveTab={setActiveTab}
                                updateFieldSelect={onFieldSelect}
                            />
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Canvas;