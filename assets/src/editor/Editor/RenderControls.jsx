import React, {useEffect, useState} from "react";
import { useDispatch, useSelector, useStore } from "react-redux";
import { updatePopoverControls, resetPopoverControls, updatePopoverInitStatus } from "../store/actions";
import shouldRenderField from './shouldRenderField';
import DragwybControlBase from '../controlBase'
import { Utils as Helper } from '../components/Utils';

const RenderControl = ({
    controlKey,
    settings,
    fieldValue,
    sectionSettings,
    handleChange,
    defautlActiveSection,
    defautlActiveTab,
}) => {

    const dispatch = useDispatch();
    const store = useStore();
    const state = store.getState();

    const Utils = Helper(state, dispatch);

    try {
        // 🔹 Validate settings early
        if (!settings || typeof settings !== "object") {
            console.error(`[RenderControl] Invalid settings for key: ${controlKey}`);
            return null;
        }

        if (!settings.type) {
            console.error(`[RenderControl] Missing "type" in settings for key: ${controlKey}`);
            return null;
        }

        if (!DragwybEditor.controlTypes[settings.type]) {
            console.error(`[RenderControl] Unknown control type "${settings.type}" for key: ${controlKey}`);
            return null;
        }

        // 🔹 Merge values: section + field-level
        const selectedSettings = { ...fieldValue, ...sectionSettings };

        // 🔹 Conditional rendering check
        const shouldRender = shouldRenderField(settings, selectedSettings);
        if (!shouldRender) {
            // console.info(`[RenderControl] Skipping render for "${controlKey}" (conditions not met)`);
            return null;
        }

        // 🔹 Handle "section" & "tabs" special cases
        if (settings.type === "section") {
            defautlActiveSection(controlKey, settings, settings.conditions);
        }

        if (settings.type === "tabs") {
            defautlActiveTab(controlKey, settings);
        }

        // 🔹 Resolve current value
        let fieldVal = selectedSettings[controlKey];
        if (settings.type === "section" && !fieldVal) {
            fieldVal = selectedSettings["section"];
        }

        // 🔹 Control lookup via filter
        let Control = DragwybBuilder.Hooks.applyFilter(
            "Dragwyb/Editor/ControlRender/" + settings.type,
            false
        );

        // 🔹 Validate Control class
        const isValidControl =
            Control &&
            (Control.prototype instanceof DragwybControlBase ||
                Control.prototype instanceof DragwybEditor.editor.extends.ControlBase);

        if (!isValidControl) {
            console.warn(
                `[RenderControl] Invalid or missing Control for type "${settings.type}". Falling back to ControlBase.`
            );
            Control = DragwybEditor.editor.extends.ControlBase;
        }

        // 🔹 Build control element
        let ControlElement = (
            <div key={controlKey} className="setting-row" data-type={settings.type}>
                <Control
                    key={controlKey}
                    id={controlKey}
                    settings={settings}
                    value={fieldVal}
                    handleChange={handleChange}
                    Utils={Utils}
                />
            </div>
        );

        if(settings.popover){
            const popOverStatus=settings.popover;

            
            if(popOverStatus.end === true){
                const PopoverControls=Utils.PopoverControls();

                dispatch(resetPopoverControls());
                dispatch(updatePopoverInitStatus(false));

                if(!PopoverControls && typeof PopoverControls !== 'object'){
                    return;
                }
                
                PopoverControls[controlKey]=ControlElement;

                return  <div className="dragwyb-popover"><div className="dragwyb-popover__container">{Object.values(PopoverControls)}</div></div>;
            };

            const element=dispatch(updatePopoverControls(controlKey, ControlElement, popOverStatus))


            return null;
        }

        return ControlElement;
    } catch (error) {
        console.log(error)
        // console.error(`[RenderControl] Fatal error rendering control "${controlKey}":`, error);
        return null;
    }
};

export default RenderControl;
