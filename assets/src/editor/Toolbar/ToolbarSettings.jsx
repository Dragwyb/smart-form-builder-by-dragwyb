import { __, sprintf } from "@wordpress/i18n";
import { useEffect } from "react";
import { useStore, useDispatch } from "react-redux";
import PropTypes from "prop-types";
import { useSelector } from 'react-redux';
import FieldSettings from "../Editor/FieldSettings";
import DragwybToolbarBase from "../toolbarBase"
import { Utils as Helper, AddField } from '../components/Utils';
import { useDraggable, useDroppable } from "../components/Common";

const ToolbarSettings = ({ setting, selectedToolbar = false, setActiveTab }) => {

    const dispatch = useDispatch();
    const store = useStore();
    const state = store.getState();

    const Utils = Helper(state, dispatch);

    const extensibleUtils = {};
    extensibleUtils.useDraggable = useDraggable;
    extensibleUtils.useDroppable = useDroppable;

    Object.freeze(extensibleUtils);

    const formData = useSelector(state => state.form);
    const toolbarData = selectedToolbar && formData[setting];
    const toolbarSettings = DragwybEditor[setting];
    const sectionSettings = useSelector(state => state.sectionSettings);

    const updateToolBar = ({ key, value, toolbarObj }) => {

        if(!DragwybEditor.EditorToolbars || !DragwybEditor.EditorToolbars.toolbars || !DragwybEditor.EditorToolbars.toolbars[key]){
            return;
        }

        Utils.updateToolbarSetting({ id: key, value });
    }

    let toolBarHtml = false;

    let toolBarObject = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/toolbarRender/' + setting, toolBarHtml, setting, selectedToolbar, toolbarData, toolbarSettings, updateToolBar, { ...Utils, ...extensibleUtils });

    if (!(toolBarObject instanceof DragwybToolbarBase || toolBarObject instanceof DragwybEditor.editor.extends.ToolbarBase)) {
        toolBarHtml = <></>;
        toolBarObject = new DragwybToolbarBase([toolBarHtml, setting, selectedToolbar, toolbarData, toolbarSettings, updateToolBar, { ...Utils, ...extensibleUtils }]);
    }

    const toolbarValue = toolBarObject.getToolbarValue();
    const settings = toolBarObject.getToolbarSettings();

    return <>
        <div className="dragwyb-controls" id={`dragwyb-controls__${setting}`}>{toolBarObject.render()}</div>
        {settings && settings.controls && <div className="dragwyb-editor__settings">
            <FieldSettings
                fieldValue={toolbarValue}
                fieldSettings={settings}
                onClose={() => setActiveTab(setting)}
                sectionSettings={sectionSettings}
                onSettingChange={toolBarObject.updateToolbarHandler}
            />
        </div>}
    </>
};

ToolbarSettings.propTypes = {
    setting: PropTypes.string.isRequired, // only allows string
};

export default ToolbarSettings;