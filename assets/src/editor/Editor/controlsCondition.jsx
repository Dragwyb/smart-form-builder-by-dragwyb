import { useSelector } from "react-redux";
import { useEffect, useRef, useCallback } from "react";
import DragwybToolbarBase from "../toolbarBase";
import shouldRenderField from "./shouldRenderField";

const ControlsConditions = ({
    conditions,
    updateHandler,
    controlKey,
    isResponsiveControl,
    responsiveType: controlResponsiveType,
    toolbarId,
    settingId
}) => {
    const activeToolbar = useSelector(state => state.activeToolbar);
    const activeSelectedSetting = useSelector(state => state.selectedSettingId);
    const setting = toolbarId || activeToolbar;
    const selectedToolbar = settingId !== undefined ? settingId : activeSelectedSetting;
    const tabScope = setting || 'fields';
    const formData = useSelector(state => state.form);
    const sectionSettings = useSelector(state => state.sectionSettings?.[tabScope] || {});
    const activeToolbarData = formData?.[setting] || {};
    const responsiveType = useSelector(state => state.responsiveType);

    const prevConditions = useRef(null);
    const debounceTimer = useRef(null);
    // Ref to store the previous data for comparison (shallow keys only)
    const prevDataRef = useRef(null);
    // Cache for activeToolbarData to avoid re-stringifying
    const prevToolbarDataRef = useRef(null);

    const getResponsiveDevice = useCallback((size) => (
        size < 768 ? 'mobile' : size < 1024 ? 'tablet' : 'desktop'
    ), []);

    if (!prevDataRef.current) {
        prevDataRef.current = { setting, selectedToolbar, controlKey, conditions, sectionSettings };
        prevToolbarDataRef.current = activeToolbarData;
    }

    const shouldRenderCallback = useCallback((timer) => {
        if (debounceTimer.current) {
            clearTimeout(debounceTimer.current);
        }

        debounceTimer.current = setTimeout(() => {
            if (!setting) return;

            const toolbarData = selectedToolbar && formData?.[setting];
            const toolbarSettings = DragwybEditor[setting];

            let toolBarHtml = false;
            let toolBarObject = DragwybBuilder.Hooks.applyFilter(
                'Dragwyb/Editor/toolbarRender/' + setting,
                toolBarHtml,
                setting,
                selectedToolbar,
                toolbarData,
                toolbarSettings
            );

            if (!(toolBarObject instanceof DragwybToolbarBase || toolBarObject instanceof DragwybEditor.editor.extends.ToolbarBase)) {
                toolBarHtml = <></>;
                toolBarObject = new DragwybToolbarBase([toolBarHtml, setting, selectedToolbar, toolbarData, toolbarSettings]);
            }

            const toolbarValue = toolBarObject.getToolbarValue();
            const settings = toolBarObject.getToolbarSettings();
            const selectedSettings = { ...toolbarValue, ...sectionSettings };

            let shouldRenderStyleSelector = true;
            if (settings.controls[controlKey].selectors && Object.keys(settings.controls[controlKey].selectors).length > 0) {
                const styleConditions = { ...settings.controls[controlKey].conditions };
                delete styleConditions.section;

                Object.keys(styleConditions).forEach((key) => {
                    if (key && settings.controls[key]?.type && ["section", "tabs"].includes(settings.controls[key].type)) {
                        delete styleConditions[key];
                    }
                });

                if (Object.keys(styleConditions).length > 0) {
                    const cloneField = { ...settings.controls[controlKey], conditions: styleConditions };
                    shouldRenderStyleSelector = shouldRenderField(cloneField, toolbarValue, settings.controls, true);
                }
            }

            if (isResponsiveControl) {
                const deviceType = getResponsiveDevice(responsiveType);
                const conditionMatched = controlResponsiveType === deviceType;
                if (!conditionMatched) {
                    prevConditions.current = { shouldRender: false, shouldRenderStyleSelector };
                    updateHandler(false, shouldRenderStyleSelector);
                    return;
                }
            }

            const shouldRender = shouldRenderField(settings.controls[controlKey], selectedSettings, settings.controls);

            prevConditions.current = { shouldRender, shouldRenderStyleSelector }
            updateHandler(shouldRender, shouldRenderStyleSelector);
        }, timer);
    }, [setting, selectedToolbar, formData, controlKey, sectionSettings, isResponsiveControl, responsiveType, controlResponsiveType, getResponsiveDevice, updateHandler]);

    useEffect(() => {
        if (!setting || !conditions || Object.keys(conditions).length === 0) {
            return;
        }

        const prev = prevDataRef.current;

        // Shallow key-level comparison instead of JSON.stringify
        const hasToolbarChanged =
            prev.setting !== setting ||
            prev.selectedToolbar !== selectedToolbar ||
            prev.controlKey !== controlKey ||
            prev.conditions !== conditions ||
            prevToolbarDataRef.current !== activeToolbarData; // Reference comparison — Redux gives new ref on change

        if (hasToolbarChanged) {
            // Priority 1: Heavy change (e.g. Color Picker) -> 100ms debounce
            shouldRenderCallback(100);
        } else {
            // Priority 2: Only sectionSettings changed -> 0ms immediate
            shouldRenderCallback(0);
        }

        // Update refs for the next render (no JSON.stringify needed)
        prevDataRef.current = { setting, selectedToolbar, controlKey, conditions, sectionSettings };
        prevToolbarDataRef.current = activeToolbarData;

        return () => clearTimeout(debounceTimer.current);
    }, [setting, selectedToolbar, formData, controlKey, conditions, sectionSettings, activeToolbarData, shouldRenderCallback]);

    useEffect(() => {
        const prev = prevDataRef.current;

        // Shallow comparison instead of JSON.stringify
        const noChanges =
            prev.setting === setting &&
            prev.selectedToolbar === selectedToolbar &&
            prev.controlKey === controlKey &&
            prev.conditions === conditions &&
            prev.sectionSettings === sectionSettings &&
            prevToolbarDataRef.current === activeToolbarData;

        if (noChanges) {
            if (isResponsiveControl) {
                if (!prevConditions.current) prevConditions.current = {};
                const { shouldRender = true } = prevConditions.current;
                const deviceType = getResponsiveDevice(responsiveType);
                const conditionMatched = controlResponsiveType === deviceType;

                if (conditionMatched !== shouldRender) {
                    shouldRenderCallback(0);
                }
            }
            return;
        };
    }, [responsiveType, setting, selectedToolbar, controlKey, conditions, sectionSettings, activeToolbarData, isResponsiveControl, controlResponsiveType, getResponsiveDevice, shouldRenderCallback]);

    return null;
};

export default ControlsConditions;