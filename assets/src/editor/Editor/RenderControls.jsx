import React, { useCallback, useEffect, useMemo, useState } from "react";
import { useDispatch, useSelector, useStore } from "react-redux";
import shouldRenderField from './shouldRenderField';
import DragwybControlBase from '../controlBase'
import { Utils as Helper } from '../components/Utils';
import ControlsConditions from "./controlsCondition";
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

    const tabScope = selectedToolbar || selectedTab || 'fields';
    // Proper useSelector for scoped sectionSettings
    const sectionSettings = useSelector((state) => state.sectionSettings?.[tabScope] || {});

    // Determine validity upfront (no early returns before hooks)
    const isValid = settings && typeof settings === "object" && settings.type && DragwybEditor.controlTypes?.[settings.type];

    // 🔹 Merge values: section + field-level
    const shouldRenderSettings = useMemo(
        () => ({ ...fieldValue, ...sectionSettings }),
        [fieldValue, sectionSettings]
    );

    const [shouldRender, setShouldRender] = useState(() =>
        isValid ? shouldRenderField(settings, { ...fieldValue, ...sectionSettings }, toolbarSettings?.controls) : false
    );

    const dispatch = useDispatch();
    const store = useStore();
    const state = store.getState();

    // Memoize Utils to avoid recreation on every render
    const Utils = useMemo(() => {
        return Helper(state, dispatch);
    }, [state, dispatch]);

    // 🔹 Control lookup via filter (memoized — type doesn't change per instance)
    const Control = useMemo(() => {
        if (!isValid) return null;

        let Ctrl = DragwybBuilder.Hooks.applyFilter(
            "Dragwyb/Editor/ControlRender/" + settings.type,
            false
        );

        // 🔹 Validate Control class
        const isValidControl =
            Ctrl &&
            (Ctrl.prototype instanceof DragwybControlBase ||
                Ctrl.prototype instanceof DragwybEditor.editor.extends.ControlBase);

        if (!isValidControl) {
            console.warn(
                `[RenderControl] Invalid or missing Control for type "${settings.type}". Falling back to ControlBase.`
            );
            Ctrl = DragwybEditor.editor.extends.ControlBase;
        }

        return Ctrl;
    }, [isValid, settings?.type]);

    const resetControlEventLifting = useCallback((event) => {
        setResetControlEvent(controlKey, event);
    }, [controlKey, setResetControlEvent]);

    const valueChangedCheckLifting = useCallback((event) => {
        setValueChangedCheck(controlKey, event);
    }, [controlKey, setValueChangedCheck]);

    const conditionUpdateHandler = useCallback((value, renderStyleSelector = true) => {
        if (shouldRender !== value) {
            setShouldRender(value);

            if (!value && settings?.selectors && !renderStyleSelector) {
                const selectedSetting = selectedTab && '' !== selectedTab && selectedTab !== selectedToolbar ? selectedTab : false;
                const uniqueSelector = `${selectedToolbar}${selectedSetting ? '_' + selectedSetting : ''}_${controlKey}`;

                setIsStyleSelectorAdd(false);
                Utils.deleteStyleSelectors({ key: uniqueSelector, responsiveType: settings.responsive_type });
            }
        } else {
            if (value && settings?.selectors && renderStyleSelector && !isStyleSelectorAdd) {
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
    }, [shouldRender, settings, selectedToolbar, selectedTab, controlKey, Utils, isStyleSelectorAdd, Control, shouldRenderSettings]);

    // 🔹 Handle "section" & "tabs" special cases — deferred to avoid setState-during-render
    useEffect(() => {
        if (!isValid || !shouldRender) return;

        if (settings.type === "section") {
            defautlActiveSection(controlKey, settings, settings.conditions);
        }

        if (settings.type === "tabs") {
            defautlActiveTab(controlKey, settings);
        }
    }, [isValid, shouldRender, settings?.type, controlKey, settings?.conditions, defautlActiveSection, defautlActiveTab]);

    // 🔹 Validate settings — return after all hooks
    if (!isValid) {
        if (!settings || typeof settings !== "object") {
            console.error(`[RenderControl] Invalid settings for key: ${controlKey}`);
        } else if (!settings.type) {
            console.error(`[RenderControl] Missing "type" in settings for key: ${controlKey}`);
        } else {
            console.error(`[RenderControl] Unknown control type "${settings.type}" for key: ${controlKey}`);
        }
        return null;
    }

    if (!shouldRender) {
        return <ControlsConditions
            controlKey={controlKey}
            conditions={settings.conditions}
            updateHandler={conditionUpdateHandler}
            isResponsiveControl={settings.responsive_control}
            responsiveType={settings.responsive_type}
            toolbarId={selectedToolbar}
            settingId={selectedTab}
        />;
    }

    const selectedSettings = { ...fieldValue, ...sectionSettings };

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
                toolbarId={selectedToolbar}
                settingId={selectedTab}
            />
            <div key={controlKey} className="dragwyb-setting-row" data-type={settings.type}>
                <Control
                    key={settings.picker === 'time' ? `${selectedTab}-${fieldValue?.time_24hr || 'no'}` : selectedTab}
                    id={controlKey}
                    toolbarId={selectedToolbar}
                    selectedSetting={selectedTab}
                    settings={settings}
                    value={fieldVal}
                    fieldValue={fieldValue}
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
