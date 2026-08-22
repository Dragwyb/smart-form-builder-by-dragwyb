import { __, sprintf } from "@wordpress/i18n";
import React, { useEffect, useRef, useMemo, useCallback } from "react";
import { useStore, useDispatch, useSelector } from 'react-redux';
import { FaXmark } from "react-icons/fa6";
import FieldSettings from "./FieldSettings";
import DragwybToolbarBase from "../controlBase/../toolbarBase";
import { Utils as Helper } from '../components/Utils';
import { useDraggable, useDroppable } from "../components/Common";
import { resetSectionSettings } from "../store/actions";

const FieldSettingsSidebar = ({ onFieldSelect }) => {
  const selectedFieldId = useSelector(state => state.selectedSettingId);
  const formData = useSelector(state => state.form);
  const fields = formData?.fields || {};
  const selectedField = selectedFieldId && fields[selectedFieldId] ? fields[selectedFieldId] : null;

  const toolbarRef = useRef(null);
  const historyTimeoutRef = useRef(null);
  const sidebarRef = useRef(null);
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

  // Resizable from Left edge (handle: 'w')
  useEffect(() => {
    if (!selectedField) return;

    const $sidebar = window.jQuery(sidebarRef.current);
    const rootElement = document.querySelector('.dragwyb-editor');
    if ($sidebar.length) {
      $sidebar.resizable({
        handles: "w",
        minWidth: 315,
        maxWidth: 700,
        resize: function (event, ui) {
          if (rootElement) {
            rootElement.style.setProperty(
              "--panel-width-right",
              ui.size.width + "px"
            );
          }
          // Prevent jQuery UI from modifying left style which breaks right-side pinning
          $sidebar.css({
            left: "",
            width: ui.size.width + "px"
          });
        },
        stop: function (event, ui) {
          $sidebar.css({ left: "" });
        },
      });
    }

    return () => {
      if ($sidebar.length && $sidebar.data("ui-resizable")) {
        $sidebar.resizable("destroy");
      }
    };
  }, [selectedField]);

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
    <div className="dragwyb-editor__field-sidebar" ref={sidebarRef}>
      <div className="dragwyb-editor__field-sidebar-header">
        <div className="dragwyb-editor__field-sidebar-title-group">
          {fieldIcon && (
            <span className="dragwyb-editor__field-sidebar-icon">
              <DragwybEditor.editor.IconsManager.Render icon={fieldIcon} />
            </span>
          )}
          <div className="dragwyb-editor__field-sidebar-meta">
            <h3 className="dragwyb-editor__field-sidebar-title" title={displayLabel}>
              {displayLabel}
            </h3>
            <span className="dragwyb-editor__field-sidebar-badge">
              {fieldTypeLabel}
            </span>
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
