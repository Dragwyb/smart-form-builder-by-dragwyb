import React, { useCallback, useMemo, useRef } from "react";
import { useDispatch, useSelector, useStore } from "react-redux";
import { useDraggable, useDroppable } from "../components/Common";
import * as Fields from "./Fields";
import { addField } from "../store/actions";
import { __, sprintf } from "@wordpress/i18n";
import { Utils as Helper } from '../components/Utils';

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
    isButtonContainer = false
}) => {

    const attributesRef = useRef(null);
    const totalChildrensRef = useRef(0);

    function isFieldEqual(prevProps, nextProps) {

        if (prevProps.fields[fieldId].attributes !== nextProps.fields[fieldId].attributes) {
            return false;
        }


        if (nextProps.fields[fieldId].is_root_container && nextProps.fields[fieldId].children) {
            if (prevProps.fields[fieldId].children?.length !== nextProps.fields[fieldId].children?.length) {
                return false;
            } else if (nextProps.fields[fieldId]?.children && totalChildrensRef !== nextProps.fields[fieldId].children?.length) {
                return false;
            }
        }

        if (null === attributesRef.current) {
            return prevProps.fields[fieldId].attributes === nextProps.fields[fieldId].attributes;
        }

        return JSON.stringify(attributesRef.current) === JSON.stringify({ ...nextProps.fields[fieldId].attributes });
    }

    const formData = useSelector((state) => state.form, isFieldEqual);
    const field = formData.fields[fieldId];

    attributesRef.current = field.attributes ? { ...field.attributes } : [];
    totalChildrensRef.current = field?.children?.length || 0;

    const selectedField = useSelector((state) => state.selectedSettingId);
    const fieldSettings = DragwybEditor.fields.fields[field.type];
    const allowedChildren = fieldSettings?.allow_child || false;
    const isRootContainer = field?.is_root_container || false;

    const childrens = field.children;

    // Droppable for this specific field (for sorting)
    const { setNodeRef: dropRef } = allowedChildren === true ? useDroppable({
        id: `canvas-drop-field-${field._id}`,
        data: {
            canvasDrop: true,
            currentId: isRootContainer ? 'root' : fieldId,
            index: index,
        },
    }) : {};

    // Draggable for this specific field
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

    // Combine refs
    const setNodeRef = useCallback((Node) => {
        if (!Node) return;
        if (dropRef) dropRef(Node);
        if (dragRef) dragRef(Node);
    }, [dropRef, dragRef]);

    let wrapperClass = `dragwyb-field-wrapper dragwyb-${field.type}-field${field.css_classes || ''}`;
    let id = `dragwyb-field-wrapper-${field._id}`;

    if (allowedChildren === true) {
        wrapperClass = `dragwyb-${field.type} dragwyb-has-actions`;
        id = `dragwyb-${field.type}-${field._id}`;
    }

    if (['button', 'file', 'radio', 'checkbox'].includes(field.type)) {
        wrapperClass += " dragwyb-no-float";
    }

    if (selectedField && selectedField === field._id) {
        wrapperClass += " selected";
    }

    const onRootContainerSelect = useCallback(() => {
        const id = field._id;

        if (!id || selectedField === id) {
            return;
        }

        onFieldSelect({ id });
    }, [field._id, selectedField, onFieldSelect]);

    const onFieldSelectHandler = useCallback(() => {
        const id = field._id;

        if (!id || isRootContainer || selectedField === id) {
            return;
        }

        onFieldSelect({ id });
    }, [field._id, isRootContainer, selectedField, onFieldSelect]);

    if (!field) {
        return null;
    }

    return (
        <>
            {dropInfo && dropInfo.index === index && (dropInfo.targetId === fieldId || (isRootContainer && dropInfo.targetId === 'root')) && (
                <div className="dragwyb-editor-indicator"></div>
            )}
            {!isDragging &&
                <div
                    ref={setNodeRef}
                    className={wrapperClass}
                    onClick={onFieldSelectHandler}
                    {...listeners}
                    {...attributes}
                    id={id}
                >
                    <Fields.Preview fields={[field]} values={values} errors={errors} childrens={childrens} Utils={Utils}>
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

const AddFieldMsg = React.memo(({ setActiveTab, isOver, updateFieldSelect }) => {
    const activeTab = useSelector((state) => state.activeToolbar);
    let emptyMessage = __("Add field", "smart-form-builder-by-dragwyb");

    if (isOver) {
        emptyMessage = __("Drag field here.", "smart-form-builder-by-dragwyb");
    }

    return (
        <div
            className="dragwyb-canvas__add-field"
            onClick={() => {
                if (activeTab === 'fields') {
                    updateFieldSelect({ id: false });
                } else {
                    setActiveTab("fields");
                }
            }}
        >
            <div className={`dragwyb-canvas__add-field-wrapper ${isOver ? " drag-active" : ""}`}>
                <i className="fas fa-plus" />
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
    const values = useSelector((state) => state.values);
    const errors = useSelector((state) => state.errors);
    const rootContainers = useSelector((state) => state.form.rootContainers);
    // Granular selector — only subscribe to fields object reference
    const formFields = useSelector((state) => state.form.fields);
    const dispatch = useDispatch();
    const store = useStore();
    const state = store.getState();

    // Memoize Utils to avoid recreation on every render
    const Utils = useMemo(() => {
        return Helper(state, dispatch);
    }, [state, dispatch]);

    // Main Droppable Wrapper (for dropping into empty list or at end)
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
            childreIds.map((childId, childIndex) => {
                handleDuplicateField(formData.fields[childId], childIndex - 1, deepClone._id);
            })
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

    // Memoize isButtonContainer check
    const isButtonContainer = useCallback((fieldKey) => {
        const field = formFields[fieldKey];
        if (!field || !field.is_root_container || !field.children || field.children.length === 0) {
            return false;
        }

        for (let i = 0; i < field.children.length; i++) {
            const childField = formFields[field.children[i]];
            if (childField && childField.type === 'button') {
                return true;
            }
        }

        return false;
    }, [formFields]);

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
                                            isButtonContainer={rootContainers.length === index + 1 ? isButtonContainer(fieldKey) : false}
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