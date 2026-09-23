import { __, sprintf } from "@wordpress/i18n";
import React, { useMemo, useCallback } from "react";
import { useDispatch } from 'react-redux';
import { FaArrowUp, FaArrowDown, FaRegClone } from "react-icons/fa6";
import { addField, updateField } from "../store/actions";

const FieldQuickBar = ({ selectedField, selectedFieldId, formData, fields, Utils, onFieldSelect, store, onSettingChangeHandler }) => {
    const isRow = selectedField?.is_root_container || selectedField?.type === 'row';
    const rootContainers = formData?.rootContainers || [];
    const dispatch = useDispatch();

    // Calculate position among siblings and within the form structure
    const positionInfo = useMemo(() => {
        if (!selectedField) {
            return { currentIndex: -1, totalSiblings: 0, canMoveUp: false, canMoveDown: false, parentTargetId: null, isRow: false };
        }

        if (isRow) {
            const idx = rootContainers.indexOf(selectedFieldId);
            return {
                currentIndex: idx,
                totalSiblings: rootContainers.length,
                canMoveUp: idx > 0,
                canMoveDown: idx >= 0 && idx < rootContainers.length - 1,
                parentTargetId: 'root',
                isRow: true
            };
        } else {
            const parentId = selectedField.parentId;
            const parentField = fields[parentId];
            const siblings = parentField?.children || [];
            const childIdx = siblings.indexOf(selectedFieldId);
            const parentRowIdx = rootContainers.indexOf(parentId);

            // A field can move up if not first column in row, OR if its parent row can move up
            const canMoveUp = childIdx > 0 || parentRowIdx > 0;
            // A field can move down if not last column in row, OR if its parent row can move down
            const canMoveDown = (childIdx >= 0 && childIdx < siblings.length - 1) || (parentRowIdx >= 0 && parentRowIdx < rootContainers.length - 1);

            return {
                currentIndex: childIdx,
                totalSiblings: siblings.length,
                canMoveUp,
                canMoveDown,
                parentTargetId: parentId,
                parentRowIdx,
                childIdx,
                isRow: false
            };
        }
    }, [selectedField, selectedFieldId, isRow, rootContainers, fields]);

    const handleMove = useCallback((direction) => {
        if (!selectedField) return;

        if (positionInfo.isRow) {
            if (positionInfo.currentIndex === -1) return;
            const newIndex = direction === 'up' ? positionInfo.currentIndex - 1 : positionInfo.currentIndex + 1;
            if (newIndex < 0 || newIndex >= rootContainers.length) return;

            dispatch({
                type: 'UPDATE_FIELD_ORDER',
                payload: {
                    currentId: selectedFieldId,
                    targetId: 'root',
                    index: newIndex
                }
            });
        } else {
            const parentId = selectedField.parentId;
            const parentField = fields[parentId];
            const siblings = parentField?.children || [];
            const childIdx = siblings.indexOf(selectedFieldId);
            const parentRowIdx = rootContainers.indexOf(parentId);

            if (direction === 'up') {
                if (childIdx > 0) {
                    // Swap with previous sibling column in same row
                    dispatch({
                        type: 'UPDATE_FIELD_ORDER',
                        payload: {
                            currentId: selectedFieldId,
                            targetId: parentId,
                            index: childIdx - 1
                        }
                    });
                } else if (parentRowIdx > 0) {
                    // First column: move the parent row up in the form
                    dispatch({
                        type: 'UPDATE_FIELD_ORDER',
                        payload: {
                            currentId: parentId,
                            targetId: 'root',
                            index: parentRowIdx - 1
                        }
                    });
                }
            } else if (direction === 'down') {
                if (childIdx >= 0 && childIdx < siblings.length - 1) {
                    // Swap with next sibling column in same row
                    dispatch({
                        type: 'UPDATE_FIELD_ORDER',
                        payload: {
                            currentId: selectedFieldId,
                            targetId: parentId,
                            index: childIdx + 1
                        }
                    });
                } else if (parentRowIdx >= 0 && parentRowIdx < rootContainers.length - 1) {
                    // Last column: move the parent row down in the form
                    dispatch({
                        type: 'UPDATE_FIELD_ORDER',
                        payload: {
                            currentId: parentId,
                            targetId: 'root',
                            index: parentRowIdx + 1
                        }
                    });
                }
            }
        }

        const label = selectedField?.attributes?.label || selectedField?.type || 'Field';
        dispatch({
            type: 'ADD_HISTORY_SNAPSHOT',
            payload: { label: `Move ${label} ${direction}` }
        });
        Utils.editorFormReady();
    }, [positionInfo, selectedField, selectedFieldId, rootContainers, fields, dispatch, Utils]);

    const handleDuplicate = useCallback(() => {
        if (!selectedField) return;
        let deepClone = JSON.parse(JSON.stringify(selectedField));
        const newId = Utils.generateId();
        deepClone._id = newId;

        const fieldConfig = DragwybEditor.fields?.fields?.[deepClone.type] || {};
        if (fieldConfig.controls?.field_id) {
            deepClone.attributes.field_id = `field_${newId}`;
        }

        if (fieldConfig.controls) {
            const fieldControls = fieldConfig.controls;
            Object.keys(deepClone.attributes || {}).forEach(attrKey => {
                if (!['tabs', 'tab', 'section'].includes(fieldControls[attrKey]?.type)) {
                    deepClone.attributes[attrKey] = DragwybBuilder.Hooks.applyFilter(
                        `Dragwyb/Editor/DuplicateControl/${fieldControls[attrKey]?.type}.duplicateValue`,
                        deepClone.attributes[attrKey],
                        fieldControls[attrKey],
                        Utils
                    );
                }
            });
        }

        if (deepClone.is_root_container && deepClone.children?.length > 0) {
            deepClone.children = [];
        }

        const targetIndex = positionInfo.currentIndex !== -1 ? positionInfo.currentIndex + 1 : null;
        dispatch(addField({ field: deepClone, index: targetIndex }));
        onFieldSelect({ id: deepClone._id });
        Utils.duplicateStyleSelectors({ cloneId: deepClone._id, currentId: selectedField._id, dispatch, state: store.getState() });

        const label = selectedField?.attributes?.label || selectedField?.type || 'Field';
        dispatch({
            type: 'ADD_HISTORY_SNAPSHOT',
            payload: { label: `Duplicate ${label}` }
        });
    }, [selectedField, Utils, positionInfo.currentIndex, dispatch, onFieldSelect, store]);

    const handleSetWidth = useCallback((percentage) => {
        if (!selectedFieldId || !selectedField) return;

        const isRowField = selectedField.is_root_container || selectedField.type === 'row';
        const widthVal = { size: percentage, unit: '%' };

        // 1. Update toolbar handler / attributes
        onSettingChangeHandler('field_width', widthVal);

        // 2. Dispatch updateStyleSelectors so StyleLoader updates <style id="dragwyb-form-..."> in iframe
        const uniqueKey = `fields_${selectedFieldId}_field_width`;
        const selectors = isRowField
            ? { '{{WRAPPER}}': '--dragwyb-row-width: {{VALUE}}{{UNIT}};' }
            : { '{{WRAPPER}}': '--dragwyb-field-width: {{VALUE}}{{UNIT}};' };

        Utils.updateStyleSelectors({
            key: uniqueKey,
            value: widthVal,
            selectors: selectors,
            placeholders: { VALUE: 'size', UNIT: 'unit' },
            toolbarType: 'fields',
            itemId: selectedFieldId
        });

        // 3. Immediately dispatch UPDATE_FIELD to Redux so state.form.fields[selectedFieldId] attributes update instantly
        const updatedField = {
            ...selectedField,
            attributes: {
                ...(selectedField.attributes || {}),
                field_width: widthVal
            }
        };
        dispatch(updateField(selectedFieldId, updatedField));

        // 4. Directly update CSS variable on iframe DOM node for instantaneous visual feedback
        const iframeEle = store.getState().iframeEle;
        if (iframeEle) {
            const elemId = isRowField
                ? `dragwyb-row-${selectedFieldId}`
                : `dragwyb-field-wrapper-${selectedFieldId}`;
            const elem = iframeEle.getElementById(elemId);
            if (elem) {
                elem.style.setProperty(
                    isRowField ? '--dragwyb-row-width' : '--dragwyb-field-width',
                    `${percentage}%`
                );
            }
        }

        // 5. Notify iframe form ready
        Utils.editorFormReady();
    }, [selectedFieldId, selectedField, onSettingChangeHandler, Utils, dispatch, store]);


    return <div className="dragwyb-editor__field-quick-bar">
        <div className="dragwyb-editor__field-quick-group">
            <span className="dragwyb-editor__field-quick-label">{__('Order', 'smart-form-builder-by-dragwyb')}</span>
            <div className="dragwyb-editor__field-quick-actions">
                <button
                    type="button"
                    className="dragwyb-editor__field-quick-btn"
                    disabled={!positionInfo.canMoveUp}
                    onClick={() => handleMove('up')}
                    title={__('Move Up', 'smart-form-builder-by-dragwyb')}
                >
                    <FaArrowUp size={10} />
                    <span>{__('Up', 'smart-form-builder-by-dragwyb')}</span>
                </button>
                <button
                    type="button"
                    className="dragwyb-editor__field-quick-btn"
                    disabled={!positionInfo.canMoveDown}
                    onClick={() => handleMove('down')}
                    title={__('Move Down', 'smart-form-builder-by-dragwyb')}
                >
                    <FaArrowDown size={10} />
                    <span>{__('Down', 'smart-form-builder-by-dragwyb')}</span>
                </button>
                <button
                    type="button"
                    className="dragwyb-editor__field-quick-btn"
                    onClick={handleDuplicate}
                    title={__('Duplicate Field', 'smart-form-builder-by-dragwyb')}
                >
                    <FaRegClone size={10} />
                    <span>{__('Clone', 'smart-form-builder-by-dragwyb')}</span>
                </button>
            </div>
        </div>

        {selectedField.type !== 'button' && (
            <div className="dragwyb-editor__field-quick-group">
                <span className="dragwyb-editor__field-quick-label">
                    {selectedField.is_root_container ? __('Row Width', 'smart-form-builder-by-dragwyb') : __('Width', 'smart-form-builder-by-dragwyb')}
                </span>
                <div className="dragwyb-editor__field-quick-widths">
                    {[
                        { label: '25%', value: 25 },
                        { label: '33%', value: 33.33 },
                        { label: '50%', value: 50 },
                        { label: '100%', value: 100 },
                    ].map(preset => {
                        const rawWidth = selectedField?.attributes?.field_width?.size;
                        const currentWidth = rawWidth !== undefined && rawWidth !== null ? Number(rawWidth) : 100;
                        const isActive = Math.abs(currentWidth - preset.value) < 1;
                        return (
                            <button
                                key={preset.label}
                                type="button"
                                className={`dragwyb-editor__field-width-pill ${isActive ? 'is-active' : ''}`}
                                onClick={() => handleSetWidth(preset.value)}
                                title={sprintf(__('Set width to %s', 'smart-form-builder-by-dragwyb'), preset.label)}
                            >
                                {preset.label}
                            </button>
                        );
                    })}
                </div>
            </div>
        )}
    </div>
}

export default FieldQuickBar;