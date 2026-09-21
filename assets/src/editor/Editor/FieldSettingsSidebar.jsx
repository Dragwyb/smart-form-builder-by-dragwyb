import { __, sprintf } from "@wordpress/i18n";
import React, { useEffect, useRef, useMemo, useCallback, useState } from "react";
import { useStore, useDispatch, useSelector } from 'react-redux';
import { FaXmark, FaCopy, FaCheck, FaArrowUp, FaArrowDown, FaRegClone } from "react-icons/fa6";
import FieldSettings from "./FieldSettings";
import DragwybToolbarBase from "../controlBase/../toolbarBase";
import { Utils as Helper } from '../components/Utils';
import { useDraggable, useDroppable } from "../components/Common";
import { resetSectionSettings, addField, updateField } from "../store/actions";

const FieldSettingsSidebar = ({ onFieldSelect }) => {
  const selectedFieldId = useSelector(state => state.selectedSettingId);
  const formData = useSelector(state => state.form);
  const fields = formData?.fields || {};
  const selectedField = selectedFieldId && fields[selectedFieldId] ? fields[selectedFieldId] : null;

  const toolbarRef = useRef(null);
  const historyTimeoutRef = useRef(null);
  const pendingHistoryDispatchRef = useRef(null);

  const dispatch = useDispatch();
  const store = useStore();
  const state = store.getState();

  const Utils = useMemo(() => {
    return Helper(state, dispatch);
  }, [state, dispatch]);

  const extensibleUtils = useMemo(() => {
    const utils = {};
    utils.useDraggable = useDraggable;
    utils.useDroppable = useDroppable;
    Object.freeze(utils);
    return utils;
  }, []);

  // Flush pending changes on unmount or field change
  useEffect(() => {
    return () => {
      if (historyTimeoutRef.current) {
        clearTimeout(historyTimeoutRef.current);
      }
      if (pendingHistoryDispatchRef.current) {
        pendingHistoryDispatchRef.current();
      }
    };
  }, [selectedFieldId]);

  const updateToolBar = useCallback(({ key, value, selectedToolBarId, toolbarObj }) => {
    if (!DragwybEditor.EditorToolbars || !DragwybEditor.EditorToolbars.toolbars || !DragwybEditor.EditorToolbars.toolbars[key]) {
      return;
    }
    Utils.updateToolbarSetting({ id: key, value, selectedToolBarId });
  }, [Utils]);

  const handleClose = useCallback(() => {
    dispatch(resetSectionSettings('fields'));
    onFieldSelect({ id: false });
  }, [dispatch, onFieldSelect]);

  const handleDeleteField = useCallback((id, fieldValues, fieldType) => {
    onFieldSelect({ id: false });
    dispatch({ type: "DELETE_FIELD", payload: id });

    let fieldLabel = fieldValues?.label;
    if (typeof fieldLabel !== 'string' || '' === fieldLabel) {
      fieldLabel = fieldValues?.field_id || id;
    }

    const historyLabel = `Delete ${fieldType || 'Field'}, (${fieldLabel})`;

    dispatch({
      type: 'ADD_HISTORY_SNAPSHOT',
      payload: { label: historyLabel }
    });
  }, [dispatch, onFieldSelect]);

  const onSettingChangeHandler = useCallback((key, value) => {
    if (!toolbarRef.current) return;
    toolbarRef.current.updateToolbarHandler(key, value);

    if (historyTimeoutRef.current) {
      clearTimeout(historyTimeoutRef.current);
    }

    const currentToolbarObj = toolbarRef.current;
    if (currentToolbarObj) {
      const currentSettings = currentToolbarObj.getToolbarSettings();
      const fieldName = currentSettings?.label || '';

      let controlLabel = key;
      if (currentSettings?.controls?.[key]?.label) {
        controlLabel = currentSettings.controls[key].label;
      } else {
        Object.values(currentSettings?.controls || {}).forEach(ctrl => {
          if (ctrl?.controls?.[key]?.label) {
            controlLabel = ctrl.controls[key].label;
          }
        });
      }

      const historyLabel = `${fieldName ? fieldName + ', ' : ''}${controlLabel}`;

      pendingHistoryDispatchRef.current = () => {
        dispatch({
          type: 'ADD_HISTORY_SNAPSHOT',
          payload: { label: historyLabel }
        });
      };
    }

    historyTimeoutRef.current = setTimeout(() => {
      if (pendingHistoryDispatchRef.current) {
        pendingHistoryDispatchRef.current();
        pendingHistoryDispatchRef.current = null;
      }
      historyTimeoutRef.current = null;
    }, 400);
  }, [dispatch]);

  const [copied, setCopied] = useState(false);

  const handleCopyId = useCallback((e) => {
    e.stopPropagation();
    if (selectedFieldId) {
      navigator.clipboard.writeText(selectedFieldId);
      setCopied(true);
      setTimeout(() => setCopied(false), 1800);
    }
  }, [selectedFieldId]);

  const isRow = selectedField?.is_root_container || selectedField?.type === 'row';
  const rootContainers = formData?.rootContainers || [];

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

  // Don't render if no field is selected or selectedSettingId is a toolbar name instead of a field
  if (!selectedFieldId || !selectedField || selectedFieldId === 'fields' || selectedFieldId === 'history') {
    return null;
  }

  const toolbarData = fields;
  const toolbarSettings = DragwybEditor.fields;

  let toolBarHtml = false;
  let toolBarObject = DragwybBuilder.Hooks.applyFilter(
    'Dragwyb/Editor/toolbarRender/fields',
    toolBarHtml,
    'fields',
    selectedFieldId,
    toolbarData,
    toolbarSettings,
    updateToolBar,
    { ...Utils, ...extensibleUtils }
  );

  if (!(toolBarObject instanceof DragwybToolbarBase || toolBarObject instanceof DragwybEditor.editor.extends.ToolbarBase)) {
    toolBarHtml = false;
    toolBarObject = new DragwybToolbarBase([
      toolBarHtml,
      'fields',
      selectedFieldId,
      toolbarData,
      toolbarSettings,
      updateToolBar,
      { ...Utils, ...extensibleUtils }
    ]);
  }

  const settings = toolBarObject.getToolbarSettings();
  toolbarRef.current = toolBarObject;
  const toolbarValues = toolbarRef.current.getToolbarValue();

  const fieldConfig = DragwybEditor.fields?.fields?.[selectedField.type] || {};
  const fieldIcon = fieldConfig?.icon;
  const fieldTypeLabel = fieldConfig?.label || selectedField.type;
  const displayLabel = selectedField?.attributes?.label || fieldTypeLabel;

  return (
    <div className="dragwyb-editor__field-sidebar">
      <div className="dragwyb-editor__field-sidebar-header">
        <div className="dragwyb-editor__field-sidebar-title-group">
          {fieldIcon && (
            <span className="dragwyb-editor__field-sidebar-icon">
              <DragwybEditor.editor.IconsManager.Render icon={fieldIcon} />
            </span>
          )}
          <div className="dragwyb-editor__field-sidebar-meta">
            <div className="dragwyb-editor__field-sidebar-title-row">
              <h3 className="dragwyb-editor__field-sidebar-title" title={displayLabel}>
                {displayLabel}
              </h3>
              <span className="dragwyb-editor__field-sidebar-badge">
                {fieldTypeLabel}
              </span>
            </div>
            <div className="dragwyb-editor__field-sidebar-subrow">
              <button
                type="button"
                className={`dragwyb-editor__field-id-pill ${copied ? 'is-copied' : ''}`}
                onClick={handleCopyId}
                title={copied ? __('Copied to clipboard!', 'smart-form-builder-by-dragwyb') : __('Click to copy Field ID', 'smart-form-builder-by-dragwyb')}
              >
                <span className="dragwyb-editor__field-id-prefix">#</span>
                <span className="dragwyb-editor__field-id-text">{selectedFieldId}</span>
                {copied ? (
                  <FaCheck size={10} className="dragwyb-editor__field-id-icon is-success" />
                ) : (
                  <FaCopy size={10} className="dragwyb-editor__field-id-icon" />
                )}
              </button>
            </div>
          </div>
        </div>
        <button
          type="button"
          className="dragwyb-editor__field-sidebar-close"
          onClick={handleClose}
          title={__('Close Field Settings', 'smart-form-builder-by-dragwyb')}
          aria-label={__('Close Field Settings', 'smart-form-builder-by-dragwyb')}
        >
          <FaXmark size={15} />
        </button>
      </div>

      {/* Quick Layout & Positioning Bar */}
      <div className="dragwyb-editor__field-quick-bar">
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

      <div className="dragwyb-editor__field-sidebar-content">
        {settings && settings.controls && (
          <div className="dragwyb-editor__settings">
            <FieldSettings
              selectedTab="fields"
              toolbarValue={toolbarValues}
              toolbarSettings={settings}
              onSettingChange={onSettingChangeHandler}
            />
          </div>
        )}
      </div>

      <div className="dragwyb-editor__field-sidebar-footer">
        <button
          type="button"
          className="dragwyb-editor__field-delete-btn"
          onClick={() => handleDeleteField(selectedFieldId, toolbarValues, fieldTypeLabel)}
        >
          <span className="dashicons dashicons-trash"></span>
          {sprintf(__('Delete %s', 'smart-form-builder-by-dragwyb'), fieldTypeLabel)}
        </button>
      </div>
    </div>
  );
};

export default FieldSettingsSidebar;
