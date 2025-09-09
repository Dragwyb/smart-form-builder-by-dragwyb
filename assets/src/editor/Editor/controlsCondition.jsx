import { useSelector } from "react-redux";
import DragwybToolbarBase from "../toolbarBase"
import shouldRenderField from "./shouldRenderField";

const ControlsConditions = ({ conditions, updateHandler, controlKey }) => {
    const setting = useSelector(state => state.activeToolbar);
    const selectedToolbar = useSelector(state => state.selectedSettingId);

    if (!setting) {
        return null;
    }

    if (conditions && Object.keys(conditions).length > 0) {
        const formData = useSelector(state => state.form);

        const toolbarData = selectedToolbar && formData[setting];
        const toolbarSettings = DragwybEditor[setting];
        const sectionSettings = useSelector(state => state.sectionSettings);

        let toolBarHtml = false;

        let toolBarObject = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/toolbarRender/' + setting, toolBarHtml, setting, selectedToolbar, toolbarData, toolbarSettings);

        if (!(toolBarObject instanceof DragwybToolbarBase || toolBarObject instanceof DragwybEditor.editor.extends.ToolbarBase)) {
            toolBarHtml = <></>;
            toolBarObject = new DragwybToolbarBase([toolBarHtml, setting, selectedToolbar, toolbarData, toolbarSettings]);
        }

        const toolbarValue = toolBarObject.getToolbarValue();
        const settings = toolBarObject.getToolbarSettings();

        const selectedSettings = { ...toolbarValue, ...sectionSettings };

        const shouldRender=shouldRenderField(settings.controls[controlKey], selectedSettings);
        
        updateHandler(shouldRender);

    }

    return null;
}

export default ControlsConditions;