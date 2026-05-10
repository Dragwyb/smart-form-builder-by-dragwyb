import { __ } from "@wordpress/i18n";
import { useEffect, useRef, useMemo, useCallback } from "react";
import { useStore, useDispatch, useSelector } from 'react-redux';
import FieldSettings from "../Editor/FieldSettings";
import DragwybToolbarBase from "../toolbarBase"
import { Utils as Helper } from '../components/Utils';
import { useDraggable, useDroppable } from "../components/Common";

const ToolbarSettings = () => {
  const setting = useSelector(state => state.activeToolbar);
  const selectedToolbar = useSelector(state => state.selectedSettingId);
  const formData = useSelector(state => state.form);
  const toolbarRef = useRef(null);

  if (!setting) {
    return null;
  }

  const sidebarRef = useRef(null);

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

  const toolbarData = selectedToolbar && formData[setting];
  const toolbarSettings = DragwybEditor[setting];

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

  const updateToolBar = useCallback(({ key, value, toolbarObj }) => {
    if (!DragwybEditor.EditorToolbars || !DragwybEditor.EditorToolbars.toolbars || !DragwybEditor.EditorToolbars.toolbars[key]) {
      return;
    }

    Utils.updateToolbarSetting({ id: key, value });
  }, [Utils]);

  let toolBarHtml = false;

  let toolBarObject = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/toolbarRender/' + setting, toolBarHtml, setting, selectedToolbar, toolbarData, toolbarSettings, updateToolBar, { ...Utils, ...extensibleUtils });

  if (!(toolBarObject instanceof DragwybToolbarBase || toolBarObject instanceof DragwybEditor.editor.extends.ToolbarBase)) {
    toolBarHtml = false;
    toolBarObject = new DragwybToolbarBase([toolBarHtml, setting, selectedToolbar, toolbarData, toolbarSettings, updateToolBar, { ...Utils, ...extensibleUtils }]);
  }

  const settings = toolBarObject.getToolbarSettings();
  const toolbarHTML = toolBarObject.render();

  const onSettingChangeHandler = useCallback((key, value) => {
    toolbarRef.current.updateToolbarHandler(key, value);
  }, []);

  toolbarRef.current = toolBarObject;

  return <div className="dragwyb-editor__sidebar" ref={sidebarRef} >
    {toolbarHTML && toolbarHTML}
    {settings && settings.controls && <div className="dragwyb-editor__settings">
      <FieldSettings
        selectedTab={setting}
        toolbarValue={toolbarRef.current.getToolbarValue()}
        toolbarSettings={settings}
        onSettingChange={onSettingChangeHandler}
      />
    </div>}
  </div>
};

export default ToolbarSettings;