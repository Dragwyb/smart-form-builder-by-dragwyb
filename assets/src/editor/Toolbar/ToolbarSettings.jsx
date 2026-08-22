import { __ } from "@wordpress/i18n";
import { useEffect, useRef, useMemo, useCallback } from "react";
import { useStore, useDispatch, useSelector } from 'react-redux';
import FieldSettings from "../Editor/FieldSettings";
import DragwybToolbarBase from "../toolbarBase";
import { Utils as Helper } from '../components/Utils';
import { useDraggable, useDroppable } from "../components/Common";
import { updateActiveToolbar } from "../store/actions";
import HistoryPanel from "../Editor/header/HistoryPanel";

const ToolbarSettings = () => {
  // --- 1. Hook Declarations (Must be at the top level) ---
  const setting = useSelector(state => state.activeToolbar);
  const selectedToolbar = useSelector(state => state.selectedSettingId);
  const formData = useSelector(state => state.form);
  const toolbarRef = useRef(null);
  const historyTimeoutRef = useRef(null);
  const sidebarRef = useRef(null);
  const pendingHistoryDispatchRef = useRef(null);
  const lastSettingRef = useRef(setting);

  const dispatch = useDispatch();
  const store = useStore();
  const state = store.getState();

  // Memoize Utils to avoid recreation on every render
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

  // Flush pending changes on unmount
  useEffect(() => {
    return () => {
      if (historyTimeoutRef.current) {
        clearTimeout(historyTimeoutRef.current);
      }
      if (pendingHistoryDispatchRef.current) {
        pendingHistoryDispatchRef.current();
      }
    };
  }, []);

  // Flush pending changes immediately when active toolbar tab changes
  useEffect(() => {
    if (lastSettingRef.current !== setting) {
      if (historyTimeoutRef.current) {
        clearTimeout(historyTimeoutRef.current);
        historyTimeoutRef.current = null;
        if (pendingHistoryDispatchRef.current) {
          pendingHistoryDispatchRef.current();
          pendingHistoryDispatchRef.current = null;
        }
      }
      lastSettingRef.current = setting;
    }
  }, [setting]);

  useEffect(() => {
    const $sidebar = window.jQuery(sidebarRef.current);
    const rootElement = document.querySelector('.dragwyb-editor');
    if ($sidebar.length) {
      $sidebar.resizable({
        helper: "resizable-helper",
        minWidth: 315,
        maxWidth: 700,
        resize: function (event, ui) {
          // ✅ Update CSS variable on resize
          rootElement.style.setProperty(
            "--panel-width",
            ui.size.width + "px"
          );
        },
        stop: function (event, ui) {
          $sidebar[0].style = '';
        },
      });
    }

    return () => {
      if ($sidebar.length && $sidebar.data("ui-resizable")) {
        $sidebar.resizable("destroy");
      }
    };
  }, []);

  const updateToolBar = useCallback(({ key, value, selectedToolBarId, toolbarObj }) => {
    if (!DragwybEditor.EditorToolbars || !DragwybEditor.EditorToolbars.toolbars || !DragwybEditor.EditorToolbars.toolbars[key]) {
      return;
    }

    Utils.updateToolbarSetting({ id: key, value, selectedToolBarId });
  }, [Utils]);

  const onSettingChangeHandler = useCallback((key, value) => {
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
  }, [dispatch, setting]);

  // --- 2. Conditional Early Returns (Must be placed after all hooks) ---
  if (!setting) {
    return null;
  }

  if (setting === 'history') {
    return <div className="dragwyb-editor__sidebar" ref={sidebarRef}>
      <HistoryPanel onClose={() => dispatch(updateActiveToolbar(DragwybEditor?.EditorToolbars?.Default ?? 'fields'))} />
    </div>;
  }

  // --- 3. Normal Render Logic ---
  const toolbarTabId = setting === 'fields' ? 'fields' : selectedToolbar;
  const toolbarData = formData[setting];
  const toolbarSettings = DragwybEditor[setting];

  let toolBarHtml = false;
  let toolBarObject = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/toolbarRender/' + setting, toolBarHtml, setting, toolbarTabId, toolbarData, toolbarSettings, updateToolBar, { ...Utils, ...extensibleUtils });

  if (!(toolBarObject instanceof DragwybToolbarBase || toolBarObject instanceof DragwybEditor.editor.extends.ToolbarBase)) {
    toolBarHtml = false;
    toolBarObject = new DragwybToolbarBase([toolBarHtml, setting, toolbarTabId, toolbarData, toolbarSettings, updateToolBar, { ...Utils, ...extensibleUtils }]);
  }

  const settings = toolBarObject.getToolbarSettings();
  const toolbarHTML = toolBarObject.render();

  toolbarRef.current = toolBarObject;

  const toolbarValues = toolbarRef.current.getToolbarValue();

  return <div className="dragwyb-editor__sidebar" ref={sidebarRef} >
    {toolbarHTML && toolbarHTML}
    {setting !== 'fields' && settings && settings.controls && (
      <div className="dragwyb-editor__settings">
        <FieldSettings
          selectedTab={setting}
          toolbarValue={toolbarValues}
          toolbarSettings={settings}
          onSettingChange={onSettingChangeHandler}
        />
      </div>
    )}
  </div>;
};

export default ToolbarSettings;