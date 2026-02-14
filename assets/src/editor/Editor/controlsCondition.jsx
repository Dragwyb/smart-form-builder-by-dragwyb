import { useSelector } from "react-redux";
import { useEffect, useRef } from "react";
import DragwybToolbarBase from "../toolbarBase";
import shouldRenderField from "./shouldRenderField";

const ControlsConditions = ({ conditions, updateHandler, controlKey, isResponsiveControl, responsiveType: controlResponsiveType }) => {
    const setting = useSelector(state => state.activeToolbar);
    const selectedToolbar = useSelector(state => state.selectedSettingId);
    const formData = useSelector(state => state.form);
    const sectionSettings = useSelector(state => state.sectionSettings);
    const activeToolbarData = formData?.[setting];
    const responsiveType = useSelector(state => state.responsiveType);

    const prevConditions = useRef(null);

    const debounceTimer = useRef(null);
    // Ref to store the previous data for comparison
    const prevDataRef = useRef(null);

    const getResponsiveDevice = (size) => (
        size < 768 ? 'mobile' : size < 1024 ? 'tablet' : 'desktop'
    );

    if (!prevDataRef.current) {
        prevDataRef.current = { setting, selectedToolbar, activeToolbarData: JSON.parse(JSON.stringify(activeToolbarData)), controlKey, conditions, sectionSettings };
    }

    const shouldRenderCallback = (timer, styleRender = true, responsiveCheck = false) => {
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
            if (settings.controls[controlKey].selectors && Object.keys(settings.controls[controlKey].selectors).length > 0 && styleRender) {
                const styleConditions = JSON.parse(JSON.stringify(settings.controls[controlKey].conditions));
                delete styleConditions.section;

                Object.keys(styleConditions).forEach((key) => {
                    if (key && settings.controls[key]?.type && ["section", "tabs"].includes(settings.controls[key].type)) {
                        delete styleConditions[key];
                    }
                });

                if (Object.keys(styleConditions).length > 0) {
                    const cloneField = JSON.parse(JSON.stringify(settings.controls[controlKey]));
                    cloneField.conditions = styleConditions;
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
    };

    useEffect(() => {
        if (!setting || !conditions || Object.keys(conditions).length === 0) {
            return;
        }

        const activeToolbarData = formData?.[setting];

        const noChanges = JSON.stringify(prevDataRef.current) === JSON.stringify({ setting, selectedToolbar, activeToolbarData, controlKey, conditions, sectionSettings });

        if (noChanges) {
            if (isResponsiveControl) {
                if (!prevConditions.current) prevConditions.current = {};
                const { shouldRender = true, shouldRenderStyleSelector = true } = prevConditions.current;
                const deviceType = getResponsiveDevice(responsiveType);
                const conditionMatched = controlResponsiveType === deviceType;

                if (conditionMatched !== shouldRender) {
                    prevConditions.current.shouldRender = conditionMatched;
                    updateHandler(conditionMatched, shouldRenderStyleSelector);
                }
            }
            return
        };

        // Check if the primary toolbar data has changed
        const hasToolbarChanged =
            JSON.stringify(prevDataRef.current.activeToolbarData) !== JSON.stringify(activeToolbarData) ||
            prevDataRef.current.setting !== setting ||
            prevDataRef.current.selectedToolbar !== selectedToolbar ||
            prevDataRef.current.controlKey !== controlKey ||
            prevDataRef.current.conditions !== conditions;

        if (hasToolbarChanged) {
            // Priority 1: Heavy change (e.g. Color Picker) -> 100ms debounce
            shouldRenderCallback(100, true);
        } else {
            // Priority 2: Only sectionSettings changed -> 0ms immediate
            shouldRenderCallback(0, false);
        }

        // Update ref for the next render
        prevDataRef.current = { setting, selectedToolbar, activeToolbarData: JSON.parse(JSON.stringify(activeToolbarData)), controlKey, conditions, sectionSettings };

        return () => clearTimeout(debounceTimer.current);
    }, [setting, selectedToolbar, formData, controlKey, conditions, sectionSettings, responsiveType]);

    return null;
};

export default ControlsConditions;