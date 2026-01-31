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

    const [shouldRender, setShouldRender] = useState(shouldRenderField(settings, shouldRenderSettings))
    const [resetControlEvent, setResetControlEvent] = useState(null);
    const [valueChangedCheck, setValueChangedCheck] = useState(false);
    const dispatch = useDispatch();
    const store = useStore();
    const state = store.getState();
    const Utils = Helper(state, dispatch);

    const conditionUpdateHandler = (value) => {
        if (shouldRender !== value) {
            setShouldRender(value);

            if (!value && settings.selectors) {
                const uniqueSelector = `${selectedToolbar}${selectedTab && '' !== selectedTab ? '_' + selectedTab : ''}_${controlKey}`;

                Utils.deleteStyleSelectors({ key: uniqueSelector });
            }
        }
    }

    if (!shouldRender) {
        return <ControlsConditions
            controlKey={controlKey}
            conditions={settings.conditions}
            updateHandler={conditionUpdateHandler}
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
                updateHandler={conditionUpdateHandler}
            />
            <div key={controlKey} className="dragwyb-setting-row" data-type={settings.type}>
                <Control
                    key={controlKey}
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
        const popOverStatus = settings.popover;

        if (popOverStatus.end === true) {
            const PopoverControls = Utils.PopoverControls();
            const PopoverTitle = popOverStatus.title;

            const controlElements = [];
            const resetControlEvents = [];
            const valueChanged = [];
            let popoverUpdate = false;

            dispatch(resetPopoverControls());
            dispatch(updatePopoverInitStatus(false));

            if (!PopoverControls && typeof PopoverControls !== 'object') {
                return;
            }

            PopoverControls[controlKey] = { control: ControlElement, resetControlEvent, valueChangedCheck };

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

        dispatch(updatePopoverControls(controlKey, ControlElement, resetControlEvent, valueChangedCheck, popOverStatus))

        return null;
    }

    return ControlElement;
};

export default RenderControl;
