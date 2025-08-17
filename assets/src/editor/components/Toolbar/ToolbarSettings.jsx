import { __, sprintf } from "@wordpress/i18n";
import PropTypes from "prop-types";
import { useSelector } from 'react-redux';
import FieldSettings from "../Editor/FieldSettings";

const ToolbarSettings = ({ setting, Utils, toolbarData, selectedToolbar = false }) => {
    const sectionSettings = useSelector(state => state.sectionSettings);
    const getSetting = () => {
        let Html = <h1 className="toolbar-not-found">{sprintf(__('Selected (%s) Data Not Found', 'dragwyb-form-builder'), setting)}</h1>;

        Html = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/Sidebar/Render/' + setting, Html, toolbarData, Utils);

        return Html ? <div className="dragwyb-editor__toolbar_settings">{Html}</div> : <></>;
    }

    const selectedToolbarValues = () => {
        // console.log('Dragwyb/Editor/Sidebar_Values'+ setting);
        const toolbarValues = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/Sidebar/Values/' + setting, toolbarData, selectedToolbar);

        return toolbarValues;
    }

    const selectedToolbarSettings = () => {
        const toolbarSettings = DragwybEditor[setting];

        const toolbarValues = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/Sidebar/Settings/' + setting, toolbarData, selectedToolbar, toolbarSettings);

        return toolbarValues;
    }

    return <>
        {getSetting()}
        {toolbarData && <div className="dragwyb-editor__settings">
            <FieldSettings
                activeFieldID={!selectedToolbar ? setting : selectedToolbar}
                fieldValue={selectedToolbarValues()}
                fieldSettings={selectedToolbarSettings()}
                onClose={() => setSelectedFieldHandler(false)}
                sectionSettings={sectionSettings}
            />
        </div>}
    </>
};

ToolbarSettings.propTypes = {
    setting: PropTypes.string.isRequired, // only allows string
};

export default ToolbarSettings;