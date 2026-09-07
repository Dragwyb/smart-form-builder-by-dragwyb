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
    lastField = false,
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
    }, [field]);

    const fieldSettings = field ? DragwybEditor.fields.fields[field.type] : null;
    const allowedChildren = fieldSettings?.allow_child || false;
    const isRootContainer = field?.is_root_container || false;
    const childrens = field?.children;

    const isDroppableEnabled = Boolean(field);
    const { setNodeRef: dropRef } = useDroppable({
        id: field ? `canvas-drop-field-${field._id}` : `canvas-drop-field-disabled-${fieldId}`,
        data: {
            canvasDrop: true,
            currentId: isRootContainer ? 'root' : (field?.parentId || 'root'),
            fieldId: field?._id,
            index: index,
            isChild: !isRootContainer,
            isRootContainer: isRootContainer,
        },
        disabled: !isDroppableEnabled,
    });

    const isDraggableEnabled = Boolean(
        field &&
        isButtonContainer === false
    );

    const {
        attributes,
        listeners,
        setNodeRef: dragRef,
        isDragging,
    } = useDraggable({
        id: field ? `canvas-drag-field-${field._id}` : `canvas-drag-field-disabled-${fieldId}`,
        data: {
            canvasDrag: true,
            currentId: fieldId,
            index: index,
            parentId: isRootContainer ? 'root' : (field?.parentId || 'root'),
            isRootContainer: isRootContainer,
            type: field?.type,
            attributes: field?.attributes,
        },
        disabled: !isDraggableEnabled,
    });

    const setNodeRef = useCallback((Node) => {
        if (!Node) return;
        if (dropRef) dropRef(Node);
        if (dragRef) dragRef(Node);
    }, [dropRef, dragRef]);

    const onRootContainerSelect = useCallback(() => {
        const id = field?._id;

        if (!id) {
            return;
        }

        onFieldSelect({ id });
    }, [field?._id, onFieldSelect]);

    const onFieldSelectHandler = useCallback((e) => {
        const id = field?._id;

        if (!id) {
            return;
        }

        if (e && e.stopPropagation) {
            e.stopPropagation();
        }

        onFieldSelect({ id });
    }, [field?._id, isRootContainer, allowedChildren, onFieldSelect]);

    if (!field) {
        return null;
    }

    const selectedSettingId = useSelector((state) => state.selectedSettingId);
    const isSelected = selectedSettingId === field._id;

    let wrapperClass = [];
    if (field.type !== 'row') {
        wrapperClass = ['dragwyb-field-wrapper', `dragwyb-${field.type}-field`, 'dragwyb-has-actions'];
        if (lastField) {
            wrapperClass.push('dragwyb-last-field');
        }
    } else if (isButtonContainer) {
        wrapperClass.push('dragwyb-last-row');
    }

    let id = `dragwyb-field-wrapper-${field._id}`;

    if (field.css_classes) {
        wrapperClass.push(field.css_classes);
    }

    if (allowedChildren === true || isRootContainer === true) {
        wrapperClass.push(`dragwyb-${field.type}`, 'dragwyb-has-actions');
        id = `dragwyb-${field.type}-${field._id}`;
    }

    if (isSelected) {
        wrapperClass.push('selected');
    }

    if (['button', 'file', 'radio', 'checkbox', 'range', 'gdpr'].includes(field.type)) {
        wrapperClass.push("dragwyb-no-float");
    }

    wrapperClass = DragwybBuilder.Hooks.applyFilter('Dragwyb/Field/WrapperClass', wrapperClass, fieldId, field.type, field.attributes, Utils);
    wrapperClass = DragwybBuilder.Hooks.applyFilter(`Dragwyb/Field/WrapperClass/${field.type}`, wrapperClass, fieldId, field.type, field.attributes, Utils);

    const isIndicatorBefore = Boolean(
        dropInfo && (
            (!isRootContainer && dropInfo.targetId === field.parentId && dropInfo.index === index) ||
            (isRootContainer && dropInfo.targetId === 'root' && dropInfo.index === index)
        )
    );

    const isIndicatorAfter = Boolean(
        dropInfo && (
            (!isRootContainer && lastField && dropInfo.targetId === field.parentId && dropInfo.index === index + 1) ||
            (isRootContainer && lastContainer && dropInfo.targetId === 'root' && dropInfo.index === index + 1)
        )
    );

    return (
        <>
            {isIndicatorBefore && (
                <div className="dragwyb-editor-indicator"></div>
            )}
            {!isDragging &&
                <div
                    ref={setNodeRef}
                    className={wrapperClass.join(' ')}
                    onClick={onFieldSelectHandler}
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
                                            lastField={childrens.length === childIndex + 1}
                                            store={store}
                                        />
                                    }
                                </React.Fragment>
                            ))
                        )}
                    </Fields.Preview>
                    {!isRootContainer && false === dropInfo && (
                        <div className="field-actions">
                            {field.parentId && (
                                <button
                                    type="button"
                                    title={__("Select Row", "smart-form-builder-by-dragwyb")}
                                    className="select-row"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        onFieldSelect({ id: field.parentId });
                                    }}
                                >
                                    <span className="dashicons dashicons-grid-view"></span>
                                </button>
                            )}
                            <button
                                type="button"
                                title={__("Drag", "smart-form-builder-by-dragwyb")}
                                className="drag-handle"
                                {...listeners}
                                {...attributes}
                            >
                                <span className="dashicons dashicons-move"></span>
                            </button>
                            <button
                                type="button"
                                title={__("Duplicate", "smart-form-builder-by-dragwyb")}
                                className="duplicate"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    onDuplicate(field, index);
                                }}
                            >
                                <span className="dashicons dashicons-admin-page"></span>
                            </button>
                            <button
                                type="button"
                                title={sprintf(__('Edit %s', 'smart-form-builder-by-dragwyb'), fieldSettings?.label || field.type)}
                                className="settings"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    onFieldSelect({ id: field._id });
                                }}
                            >
                                <span className="dashicons dashicons-edit"></span>
                            </button>
                            <button
                                type="button"
                                title={field.type === 'button' ? __("Cannot delete button", "smart-form-builder-by-dragwyb") : __("Delete", "smart-form-builder-by-dragwyb")}
                                className={`delete ${field.type === 'button' ? 'disabled' : ''}`}
                                onClick={(e) => {
                                    if (field.type === 'button') {
                                        return;
                                    }
                                    e.stopPropagation();
                                    onDelete(field, fieldSettings?.label || field.type);
                                }}
                                disabled={field.type === 'button'}
                            >
                                <span className="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    )}
                    {isRootContainer && false === dropInfo && (
                        <div className="field-actions">
                            <button
                                type="button"
                                title={__("Drag Row", "smart-form-builder-by-dragwyb")}
                                className="drag-handle"
                                {...listeners}
                                {...attributes}
                            >
                                <span className="dashicons dashicons-move"></span>
                            </button>
                            <button
                                type="button"
                                title={__("Duplicate Row", "smart-form-builder-by-dragwyb")}
                                className="duplicate"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    onDuplicate(field, index);
                                }}
                            >
                                <span className="dashicons dashicons-admin-page"></span>
                            </button>
                            <button
                                type="button"
                                title={sprintf(__('Edit %s', 'smart-form-builder-by-dragwyb'), fieldSettings?.label || field.type)}
                                className="settings"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    onRootContainerSelect();
                                }}
                            >
                                <span className="dashicons dashicons-edit"></span>
                            </button>
                            <button
                                type="button"
                                title={isButtonContainer ? __("Cannot delete button row", "smart-form-builder-by-dragwyb") : __("Delete Row", "smart-form-builder-by-dragwyb")}
                                className={`delete ${isButtonContainer ? 'disabled' : ''}`}
                                onClick={(e) => {
                                    if (isButtonContainer) {
                                        return;
                                    }
                                    e.stopPropagation();
                                    onDelete(field, fieldSettings?.label || field.type);
                                }}
                                disabled={isButtonContainer}
                            >
                                <span className="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    )}
                </div>
            }
            {isIndicatorAfter && (
                <div className="dragwyb-editor-indicator"></div>
            )}
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

    const handleDeleteField = useCallback((fieldValues, fieldType) => {
        onFieldSelect({ id: false });
        dispatch({ type: "DELETE_FIELD", payload: fieldValues._id });


        let fieldLabel = fieldValues?.attributes?.label;

        if (typeof fieldLabel !== 'string' || '' === fieldLabel) {
            fieldLabel = fieldValues._id;
        }

        const historyLabel = `Delete ${fieldType}, (${fieldLabel})`;

        dispatch({
            type: 'ADD_HISTORY_SNAPSHOT',
            payload: { label: historyLabel }
        });
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
                        <form className="dragwyb-form" action="#" autoComplete="off" onSubmit={(e) => { e.preventDefault(); return false; }}>
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