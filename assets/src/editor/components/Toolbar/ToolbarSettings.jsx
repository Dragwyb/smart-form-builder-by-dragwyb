import { __, sprintf } from "@wordpress/i18n";
import PropTypes from "prop-types";

const ToolbarSettings = ({setting, Utils}) => {
    const getSetting = () =>{
        let Html=<h1 className="toolbar-not-found">{sprintf(__('Selected (%s) Toolbar Not Found', 'dragwyb-form-builder'), setting)}</h1>;
       
        Html = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/Toolbar_Render/' + setting, Html, Utils);
        return <div className="dragwyb-editor__toolbar_settings">{Html}</div>;
    }

    return getSetting()
};

ToolbarSettings.propTypes = {
    setting: PropTypes.string.isRequired, // only allows string
};

export default ToolbarSettings;