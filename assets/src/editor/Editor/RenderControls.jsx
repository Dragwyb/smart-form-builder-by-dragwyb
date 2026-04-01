import React, { useEffect, useState } from "react";
import { useDispatch, useSelector, useStore } from "react-redux";
import shouldRenderField from './shouldRenderField';
import DragwybControlBase from '../controlBase'
import { Utils as Helper } from '../components/Utils';
import ControlsConditions from "./controlsCondition";
import { FaUndo } from "react-icons/fa";
import { __ } from "@wordpress/i18n";

const RenderControl = ({
    selectedTab,
    selectedToolbar,
    controlKey,
    settings,
    toolbarSettings,
    fieldValue,
    handleChange,
    defautlActiveSection,
    defautlActiveTab,
    setResetControlEvent = () => { },
    setValueChangedCheck = () => { }
}) => {
    const [isStyleSelectorAdd, setIsStyleSelectorAdd] = useState(false);
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

    const getSectionSettings = () => {
        const store = useStore();
        const state = store.getState();

        return state.sectionSettings || {};
    }

    // 🔹 Merge values: section + field-level
    const shouldRenderSettings = { ...fieldValue, ...getSectionSettings() };

    const [shouldRender, setShouldRender] = useState(shouldRenderField(settings, shouldRenderSettings, toolbarSettings?.controls))


    const dispatch = useDispatch();
    const store = useStore();
    const state = store.getState();
    const Utils = Helper(state, dispatch);

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

    const resetControlEventLifting = (event) => {
        setResetControlEvent(controlKey, event);
    }

    const valueChangedCheckLifting = (event) => {
        setValueChangedCheck(controlKey, event);
    }

    const conditionUpdateHandler = (value, renderStyleSelector = true) => {
        if (shouldRender !== value) {
            setShouldRender(value);

            if (!value && settings.selectors && !renderStyleSelector) {
                const selectedSetting = selectedTab && '' !== selectedTab && selectedTab !== selectedToolbar ? selectedTab : false;
                const uniqueSelector = `${selectedToolbar}${selectedSetting ? '_' + selectedSetting : ''}_${controlKey}`;

                Utils.deleteStyleSelectors({ key: uniqueSelector, responsiveType: settings.responsive_type });
            }
        } else {
            if (value && settings.selectors && renderStyleSelector && !isStyleSelectorAdd) {
                setIsStyleSelectorAdd(true);
                new Control({
                    id: controlKey,
                    toolbarId: selectedToolbar,
                    selectedSetting: selectedTab,
                    settings: settings,
                    value: shouldRenderSettings[controlKey],
                    Utils: Utils,
                }).renderStyleSelector();
            }
        }
    }

    if (!shouldRender) {
        return <ControlsConditions
            controlKey={controlKey}
            conditions={settings.conditions}
            updateHandler={conditionUpdateHandler}
            isResponsiveControl={settings.responsive_control}
            responsiveType={settings.responsive_type}
        />;
    }

    // 🔹 Handle "section" & "tabs" special cases
    if (settings.type === "section") {
        defautlActiveSection(controlKey, settings, settings.conditions);
    }

    if (settings.type === "tabs") {
        defautlActiveTab(controlKey, settings);
    }

    const selectedSettings = { ...fieldValue, ...getSectionSettings() };

    // 🔹 Resolve current value
    let fieldVal = selectedSettings[controlKey];
    if (settings.type === "section" && !fieldVal) {
        fieldVal = selectedSettings["section"];
    }

    // 🔹 Build control element
    let ControlElement = (
        <>
            <ControlsConditions
                controlKey={controlKey}
                conditions={settings.conditions}
                responsiveType={settings.responsive_type}
                isResponsiveControl={settings.responsive_control}
                updateHandler={conditionUpdateHandler}
            />
            <div key={controlKey} className="dragwyb-setting-row" data-type={settings.type}>
                <Control
                    key={selectedTab}
                    id={controlKey}
                    toolbarId={selectedToolbar}
                    selectedSetting={selectedTab}
                    settings={settings}
                    value={fieldVal}
                    handleChange={handleChange}
                    Utils={Utils}
                    resetControlEventLifting={resetControlEventLifting}
                    valueChangedCheckLifting={valueChangedCheckLifting}
                />
            </div>
        </>
    );

    return ControlElement;
};

export default RenderControl;
