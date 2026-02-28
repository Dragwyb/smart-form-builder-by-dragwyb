import { __, sprintf } from "@wordpress/i18n";
import { useState } from "react";
import { useEffect, useRef } from "react";
import { useStore, useDispatch } from "react-redux";
import PropTypes from "prop-types";
import { useSelector } from 'react-redux';
import FieldSettings from "../Editor/FieldSettings";
import DragwybToolbarBase from "../toolbarBase"
import { Utils as Helper, AddField } from '../components/Utils';
import { useDraggable, useDroppable } from "../components/Common";
import { GiConsoleController } from "react-icons/gi";

const ToolbarSettings = ({ setActiveTab, position }) => {
  const setting = useSelector(state => state.activeToolbar);
  const selectedToolbar = useSelector(state => state.selectedSettingId);
  const [toolbarValue, setToolbarValue] = useState({});
  const [isToolbarSet, setIsToolbarSet] = useState(false);
  const toolbarRef = useRef(null);

  if (!setting) {
    return null;
  }

  const sidebarRef = useRef(null);

  const dispatch = useDispatch();
  const store = useStore();
  const state = store.getState();

  const Utils = Helper(state, dispatch);

  const extensibleUtils = {};
  extensibleUtils.useDraggable = useDraggable;
  extensibleUtils.useDroppable = useDroppable;

  Object.freeze(extensibleUtils);

  const formData = state.form;
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

  const updateToolBar = ({ key, value, toolbarObj }) => {
    if (!DragwybEditor.EditorToolbars || !DragwybEditor.EditorToolbars.toolbars || !DragwybEditor.EditorToolbars.toolbars[key]) {
      return;
    }

    Utils.updateToolbarSetting({ id: key, value });
  }

  let toolBarHtml = false;

  let toolBarObject = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/toolbarRender/' + setting, toolBarHtml, setting, selectedToolbar, toolbarData, toolbarSettings, updateToolBar, { ...Utils, ...extensibleUtils });

  if (!(toolBarObject instanceof DragwybToolbarBase || toolBarObject instanceof DragwybEditor.editor.extends.ToolbarBase)) {
    toolBarHtml = false;
    toolBarObject = new DragwybToolbarBase([toolBarHtml, setting, selectedToolbar, toolbarData, toolbarSettings, updateToolBar, { ...Utils, ...extensibleUtils }]);
  }

  useEffect(() => {
    if (selectedToolbar) {
      setToolbarValue(toolBarObject.getToolbarValue());
    }
  }, [selectedToolbar])

  if (!isToolbarSet) {
    setToolbarValue(toolBarObject.getToolbarValue());
    setIsToolbarSet(true);
  }

  const setUpdateToolbarValueHandler = () => {
    setToolbarValue(toolBarObject.getToolbarValue());
  }

  const settings = toolBarObject.getToolbarSettings();
  const toolbarHTML = toolBarObject.render();

  const onSettingChangeHandler = (key, value) => {
    toolbarRef.current.updateToolbarHandler(key, value);
  }

  toolbarRef.current = toolBarObject;

  return <div className="dragwyb-editor__sidebar" ref={sidebarRef} >
    {toolbarHTML && <div className="dragwyb-controls" id={`dragwyb-controls__${setting}`}>{toolbarHTML}</div>}
    {settings && settings.controls && <div className="dragwyb-editor__settings">
      <FieldSettings
        selectedTab={setting}
        toolbarValue={toolbarValue}
        toolbarSettings={settings}
        onClose={() => setActiveTab(setting)}
        onSettingChange={onSettingChangeHandler}
        setUpdateToolbarValue={setUpdateToolbarValueHandler}
      />
    </div>}
  </div>
};

ToolbarSettings.propTypes = {
  setting: PropTypes.string.isRequired, // only allows string
};

export default ToolbarSettings;