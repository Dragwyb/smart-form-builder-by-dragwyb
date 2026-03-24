import React from "react";
import { useDispatch, useSelector } from "react-redux";
import { useDraggable, useDroppable } from "../components/Common";
import * as Fields from "./Fields";
import { duplicateField } from "../store/actions";
import { __, sprintf } from "@wordpress/i18n";
import { GiConsoleController } from "react-icons/gi";

const RenderItem = ({
    fieldId,
    values,
    index,
    dropId,
    dropIndicatorPosition,
    onFieldSelect,
    onDuplicate,
    onDelete,
    errors,
    Utils
}) => {
    const field = useSelector((state) => state.form.fields[fieldId]);

    if (!field) {
        return null;
    }

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
    } = allowedChildren === true ? useDraggable({
        id: `canvas-drag-field-${field._id}`,
        data: {
            canvasDrag: true,
            currentId: fieldId,
            index: index,
        },
    }) : {};

    // Combine refs
    const setNodeRef = (Node) => {
        if (!Node) return;
        if (dropRef) dropRef(Node);
        if (dragRef) dragRef(Node);
    };

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

    if (isDragging) {
        wrapperClass += " dragwyb-start-drag";
    }

    const onRootContainerSelect = () => {
        const id = field._id;

        if (!id || selectedField === id) {
            return;
        }

        onFieldSelect({ id });
    }

    const onFieldSelectHandler = () => {
        const id = field._id;

        if (!id || isRootContainer || selectedField === id) {
            return;
        }

        onFieldSelect({ id });
    };

    if (dropId && dropId.targetId === (isRootContainer ? 'root' : fieldId)) {
        if ("bottom" !== dropIndicatorPosition && dropId.index === index) {
            wrapperClass += ' indicator-top';
        }
        
        if ("bottom" === dropIndicatorPosition && dropId.index === index + 1) {
            wrapperClass += ' indicator-bottom';
        }
    }

    return (
        <>
            <div
                ref={setNodeRef}
                className={wrapperClass}
                onClick={onFieldSelectHandler}
                {...listeners}
                {...attributes}
                id={id}
            >
                {allowedChildren && <span style={{ position: "absolute", zIndex: 999, background: "#fff" }}>{"allowedChildren: " + fieldId}</span>}
                <Fields.Preview fields={[field]} values={values} errors={errors} childrens={childrens} Utils={Utils}>
                    {childrens && childrens.length > 0 && (
                        childrens.map((childId, childIndex) => (
                            <>
                                {!childId ? null :
                                    <RenderItem
                                        key={childId} // Use ID as key, not the whole object
                                        fieldId={childId} // Pass ID instead of the full object
                                        index={childIndex}
                                        dropId={dropId}
                                        onFieldSelect={onFieldSelect}
                                        onDuplicate={onDuplicate}
                                        onDelete={onDelete}
                                        values={values}
                                        errors={errors}
                                        Utils={Utils}
                                    />
                                }
                            </>
                        ))
                    )}
                </Fields.Preview>
                {isRootContainer && (
                    <div className="field-actions">
                        <button
                            title={__("Duplicate", "dragwyb-form-builder")}
                            className="duplicate"
                            onClick={(e) => {
                                e.stopPropagation();
                                onDuplicate(field);
                            }}
                        >
                            <span className="dashicons dashicons-admin-page"></span>
                        </button>
                        <button
                            title={sprintf(__('%s Settings', 'dragwyb-form-builder'), fieldSettings.label)}
                            className="settings"
                            onClick={onRootContainerSelect}
                        >
                            <span className="dashicons dashicons-menu"></span>
                        </button>
                        <button
                            title={__("Delete", "dragwyb-form-builder")}
                            className="delete"
                            onClick={(e) => {
                                e.stopPropagation();
                                onDelete(field._id);
                            }}
                        >
                            <span className="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                )}
            </div>
        </>
    );
};

const AddFieldMsg = ({ setActiveTab, isOver, updateFieldSelect }) => {
    const activeTab = useSelector((state) => state.activeToolbar);
    let emptyMessage = __("Add field", "dragwyb-form-builder");

    if (isOver) {
        emptyMessage = __("Drag field here.", "dragwyb-form-builder");
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
};

const Canvas = ({
    onFieldSelect,
    Utils,
    dropId,
    dropIndicatorPosition,
    setActiveTab
}) => {
    const values = useSelector((state) => state.values);
    const formData = useSelector((state) => state.form);
    const errors = useSelector((state) => state.errors);
    const rootContainers = useSelector((state) => state.form.rootContainers);
    const dispatch = useDispatch();

    // Main Droppable Wrapper (for dropping into empty list or at end)
    const { setNodeRef, isOver } = useDroppable({
        id: `canvas-drop-wrapper`,
        data: {
            canvasFieldDrop: true,
            canvasDrop: true,
            currentId: null,
        },
    });

    const handleDuplicateField = (field, index) => {
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

        dispatch(duplicateField(deepClone, index + 1));
        onFieldSelect({ id: deepClone._id });
    };

    const handleDeleteField = (id) => {
        onFieldSelect({ id: false });
        dispatch({ type: "DELETE_FIELD", payload: id });
    };

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
                                            dropId={dropId}
                                            dropIndicatorPosition={dropIndicatorPosition}
                                            Utils={Utils}
                                        />
                                    ))}
                                </>
                            )}
                        </form>
                        <AddFieldMsg
                            setActiveTab={setActiveTab}
                            updateFieldSelect={onFieldSelect}
                        />
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Canvas;