import React, { useState, useMemo, useCallback, useEffect, useRef } from "react";
import { useDispatch, useSelector, useStore } from "react-redux";
import { updatePopoverControls, resetPopoverControls, updatePopoverInitStatus } from "../store/actions";
import { FaUndo } from "react-icons/fa";
import { __ } from "@wordpress/i18n";
import RenderControl from './RenderControls';

const RenderPopoverControls = ({
    selectedToolbar,
    selectedTab,
    controlKey,
    settings,
    toolbarSettings,
    fieldValue,
    handleChange,
    defautlActiveTab,
    defautlActiveSection
}) => {

    const activePopoverControlKey = useSelector((state) => state.activePopoverKey);
    const dispatch = useDispatch();
    const store = useStore();

    const [resetControlsEvent, setResetControlsEvent] = useState({});
    const [valuesChangedCheck, setValuesChangedCheck] = useState({});
    const [PopoverControls, setPopoverControls] = useState(false);

    const setResetControlEventHandler = useCallback((controlKey, event) => {
        setResetControlsEvent(prev => ({
            ...prev,
            [controlKey]: event
        }));
    }, []);

    const setValueChangedCheckHandler = useCallback((controlKey, event) => {
        setValuesChangedCheck(prev => ({
            ...prev,
            [controlKey]: event
        }));
    }, []);

    // Track what dispatch action is needed — computed during render, executed in useEffect
    const pendingAction = useRef(null);

    let popOverStatus = settings.popover;
    let renderOutput = null;

    if (popOverStatus.end === true) {
        const PopoverTitle = popOverStatus.title;

        if (!PopoverControls && typeof PopoverControls !== 'object') {
            // Schedule reset via effect
            pendingAction.current = { type: 'reset' };
            renderOutput = <></>;
        } else {
            const firstControlKey = PopoverControls[0] || controlKey;

            // Schedule reset via effect
            pendingAction.current = { type: 'reset' };

            if (firstControlKey !== activePopoverControlKey) {
                renderOutput = <div className="dragwyb-popover-wrapper" data-popover-key={firstControlKey}></div>;
            } else {
                let popoverUpdate = false;

                if (valuesChangedCheck && Object.keys(valuesChangedCheck).length > 0) {
                    Object.keys(valuesChangedCheck).forEach((key) => {
                        if (popoverUpdate === true) {
                            return;
                        }

                        if (typeof valuesChangedCheck[key] === 'function') {
                            popoverUpdate = valuesChangedCheck[key]();
                        }
                    });
                }

                const resetControlsValues = () => {
                    if (resetControlsEvent && Object.keys(resetControlsEvent).length > 0) {
                        Object.keys(resetControlsEvent).forEach((key) => {
                            if (typeof resetControlsEvent[key] === 'function') {
                                resetControlsEvent[key]();
                            }
                        });
                    }
                }

                renderOutput = <div className="dragwyb-popover-wrapper active" data-popover-key={firstControlKey}>
                    <div className="dragwyb-popover-container">
                        <div className="dragwyb-popover">
                            {PopoverTitle && <div className="dragwyb-popover__title">
                                {PopoverTitle}
                                <span onClick={resetControlsValues}>
                                    <FaUndo size={12} title={__('Reset to Default', 'smart-form-builder-by-dragwyb')} />
                                </span>
                            </div>}
                            {
                                PopoverControls.map((key) => {
                                    return (
                                        <RenderControl
                                            key={key}
                                            selectedToolbar={selectedToolbar}
                                            selectedTab={selectedTab}
                                            controlKey={key}
                                            settings={toolbarSettings.controls[key]}
                                            toolbarSettings={toolbarSettings}
                                            fieldValue={fieldValue}
                                            handleChange={handleChange}
                                            defautlActiveTab={defautlActiveTab}
                                            defautlActiveSection={defautlActiveSection}
                                            popOverStatus={true}
                                            setResetControlEvent={setResetControlEventHandler}
                                            setValueChangedCheck={setValueChangedCheckHandler}
                                        />
                                    )
                                })
                            }
                        </div>
                    </div>
                </div>;
            }
        }
    } else {
        // Schedule popover registration via effect
        pendingAction.current = { type: 'register', controlKey, popOverStatus };
    }

    // Defer all dispatches to after render — avoids "setState during render" warning
    useEffect(() => {
        const action = pendingAction.current;
        if (!action) return;
        pendingAction.current = null;

        if (action.type === 'reset') {
            if (store.getState().popoverControls && !PopoverControls) {
                setPopoverControls([...store.getState().popoverControls, controlKey]);
            }
            dispatch(resetPopoverControls());
            dispatch(updatePopoverInitStatus(false));
        } else if (action.type === 'register') {
            dispatch(updatePopoverControls(action.controlKey, action.popOverStatus));
        }
    });

    return renderOutput;
};

export default RenderPopoverControls;
