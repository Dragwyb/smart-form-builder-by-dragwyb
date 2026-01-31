import React from "react";
import { useDispatch, useSelector } from "react-redux";
import { useDraggable, useDroppable } from "../components/Common";
import * as Fields from "./Fields";
import { duplicateField } from "../store/actions";
import { __ } from "@wordpress/i18n";

const RenderItem = ({
    field,
    values,
    index,
    dropIndex,
    dropIndicatorPosition,
    onFieldSelect,
    onDuplicate,
    onDelete,
    errors,
}) => {
    const selectedField = useSelector((state) => state.selectedSettingId);

    // Droppable for this specific field (for sorting)
    const { setNodeRef: dropRef } = useDroppable({
        id: `canvas-drop-field-${field._id}`,
        data: {
            canvasDrop: true,
            currentIndex: index,
        },
    });

    // Draggable for this specific field
    const {
        attributes,
        listeners,
        setNodeRef: dragRef,
        isDragging,
    } = useDraggable({
        id: `canvas-drag-field-${field._id}`,
        data: {
            canvasDrag: true,
            currentIndex: index,
        },
    });

    // Combine refs
    const setNodeRef = (Node) => {
        if (!Node) return;
        dropRef(Node);
        dragRef(Node);
    };

    let wrapperClass = `dragwyb-field-wrapper dragwyb-${field.type}-field${field.className && "" !== field.className ? ` ${field.className}` : ""}`;

    if (selectedField && selectedField === field._id) {
        wrapperClass += " selected";
    }

    if (isDragging) {
        wrapperClass += " dragwyb-start-drag";
    }

    const onFieldSelectHandler = (id) => {
        if (selectedField !== id) {
            onFieldSelect({ id });
        }
    };

    return (
        <>
            {/* Top Indicator */}
            {dropIndex === index && "bottom" !== dropIndicatorPosition && (
                <span className="dragwyb-editor-indicator"></span>
            )}

            <div
                ref={setNodeRef}
                className={wrapperClass}
                onClick={() => onFieldSelectHandler(field._id)}
                {...listeners}
                {...attributes}
                id={`dragwyb-field-wrapper-${field._id}`}
            >
                <Fields.Preview fields={[field]} values={values} errors={errors} />
                <div className="field-actions">
                    <button
                        className="duplicate"
                        onClick={(e) => {
                            e.stopPropagation();
                            onDuplicate(field);
                        }}
                    >
                        <span className="dashicons dashicons-admin-page"></span>
                    </button>
                    <button
                        className="delete"
                        onClick={(e) => {
                            e.stopPropagation();
                            onDelete(field._id);
                        }}
                    >
                        <span className="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>

            {/* Bottom Indicator */}
            {dropIndex === index && "bottom" === dropIndicatorPosition && (
                <span className="dragwyb-editor-indicator"></span>
            )}
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
    dropIndex,
    dropIndicatorPosition,
    setActiveTab
}) => {
    const values = useSelector((state) => state.values);
    const formData = useSelector((state) => state.form);
    const fields = formData.fields || [];
    const errors = useSelector((state) => state.errors);

    const dispatch = useDispatch();

    // Main Droppable Wrapper (for dropping into empty list or at end)
    const { setNodeRef, isOver } = useDroppable({
        id: `canvas-drop-wrapper`,
        data: {
            canvasFieldDrop: true,
            canvasDrop: true,
            currentIndex: fields ? fields.length || 0 : 0,
        },
    });

    const handleDuplicateField = (field, index) => {
        const deepClone = JSON.parse(JSON.stringify(field));
        const id = Utils.generateId();
        deepClone._id = id;

        // (Truncated for brevity: your existing logic for clearing attributes goes here)

        dispatch(duplicateField(deepClone, index + 1, dispatch));
        onFieldSelect({ id: deepClone._id });
    };

    const handleDeleteField = (id) => {
        onFieldSelect({ id: false });
        dispatch({ type: "DELETE_FIELD", payload: id });
    };

    let canvasCls = "dragwyb-canvas";
    if (!fields || fields.length === 0) {
        canvasCls += " canvas-empty";
    }

    return (
        <>
            <div className="dragwyb-editor__main">
                <div className="dragwyb-canvas-wrapper" ref={setNodeRef}>
                    <div className={canvasCls}>
                        <div
                            className="dragwyb-form-wrapper"
                            id={`dragwyb-form-wrapper-${DragwybEditor.formId}`}
                        >
                            {fields && fields.length > 0 && (
                                <>
                                    {fields.map((field, index) => (
                                        <RenderItem
                                            key={field._id}
                                            field={field}
                                            values={values}
                                            onFieldSelect={onFieldSelect}
                                            onDuplicate={(field) => handleDuplicateField(field, index)}
                                            onDelete={handleDeleteField}
                                            errors={errors}
                                            index={index}
                                            dropIndex={dropIndex}
                                            dropIndicatorPosition={dropIndicatorPosition}
                                        />
                                    ))}
                                </>
                            )}
                            <AddFieldMsg
                                setActiveTab={setActiveTab}
                                isOver={isOver || dropIndex === fields.length}
                                updateFieldSelect={onFieldSelect}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default Canvas;