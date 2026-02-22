import React, { useEffect, useState } from "react";
import { useDispatch, useSelector, useStore } from "react-redux";
import { updatePopoverControls, resetPopoverControls, updatePopoverInitStatus } from "../store/actions";
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
}) => {
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
    const [resetControlEvent, setResetControlEvent] = useState(null);
    const [valueChangedCheck, setValueChangedCheck] = useState(false);
    const dispatch = useDispatch();
    const store = useStore();
    const state = store.getState();
    const Utils = Helper(state, dispatch);

    const conditionUpdateHandler = (value, renderStyleSelector = true) => {
        if (shouldRender !== value) {
            setShouldRender(value);

            if (!value && settings.selectors && !renderStyleSelector) {
                const selectedSetting = selectedTab && '' !== selectedTab && selectedTab !== selectedToolbar ? selectedTab : false;
                const uniqueSelector = `${selectedToolbar}${selectedSetting ? '_' + selectedSetting : ''}_${controlKey}`;

                Utils.deleteStyleSelectors({ key: uniqueSelector });
            }
        }
    }

    const popOverResponsiveEnd = (settings) => {
        if (settings.responsive && settings.responsive_control) {
            const controlResponsiveType = settings.responsive_type;
            let mobileControlKey = controlKey.replace(`_${controlResponsiveType}`, '');
            mobileControlKey = `${mobileControlKey}_mobile`;

            if (toolbarSettings?.controls?.[mobileControlKey] && toolbarSettings?.controls?.[mobileControlKey].popover && toolbarSettings.controls[mobileControlKey].popover.end === true) {
                return true;
            }
        }

        return false;
    }

    const popOverControl = (settings, ControlEle = false) => {
        let popOverStatus = settings.popover;

        const popOverResponsiveEndStatus = popOverResponsiveEnd(settings);

        if (popOverStatus.end === true || popOverResponsiveEndStatus === true) {
            const PopoverControls = Utils.PopoverControls();
            const PopoverTitle = popOverStatus.title;

            const controlElements = [];
            const resetControlEvents = [];
            const valueChanged = [];
            let popoverUpdate = false;


            if (!PopoverControls && typeof PopoverControls !== 'object') {
                return;
            }

            dispatch(resetPopoverControls());
            dispatch(updatePopoverInitStatus(false));

            PopoverControls[controlKey] = { control: ControlEle, resetControlEvent, valueChangedCheck };

            Object.values(PopoverControls).forEach((control) => {
                controlElements.push(control.control);
                resetControlEvents.push(control.resetControlEvent);
                valueChanged.push(control.valueChangedCheck);
            });

            valueChanged.forEach((check) => {
                if (popoverUpdate === true) {
                    return;
                }

                if (typeof check === 'function') {
                    popoverUpdate = check();
                }
            });

            const resetControlsValues = () => {
                resetControlEvents.forEach((event) => {
                    if (typeof event === 'function') {
                        event();
                    }
                });
            }

            return <div className="dragwyb-popover" style={{ display: "none" }}>
                {PopoverTitle && <div className="dragwyb-popover__title">
                    {PopoverTitle}
                    <span onClick={resetControlsValues}>
                        <FaUndo size={12} title={__('Reset to Default', 'dragwyb-form-builder')} />
                    </span>
                </div>}
                <div className="dragwyb-popover__container">{controlElements}</div>
            </div>;
        };

        if (settings.responsive && settings.responsive_control && 'desktop' !== settings.responsive_type) {
            const desktopControlKey = controlKey.replace(`_${settings.responsive_type}`, '');
            const desktopControl = toolbarSettings?.controls?.[desktopControlKey];

            if (desktopControl) {
                popOverStatus = desktopControl.popover;
            }
        }

        dispatch(updatePopoverControls(controlKey, ControlEle, resetControlEvent, valueChangedCheck, popOverStatus))
        return null;
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
        setResetControlEvent(() => event);
    }

    const valueChangedCheckLifting = (event) => {
        setValueChangedCheck(() => event);
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

    if (settings.popover) {
        return popOverControl(settings, ControlElement);
    }

    return ControlElement;
};

export default RenderControl;
