import React, { useState } from "react";
import { useDispatch, useSelector, useStore } from "react-redux";
import { updatePopoverControls, resetPopoverControls, updatePopoverInitStatus } from "../store/actions";
import { FaUndo } from "react-icons/fa";
import { __ } from "@wordpress/i18n";
import { Utils as Helper } from '../components/Utils';
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
    const state = store.getState();
    const Utils = Helper(state, dispatch);

    const [resetControlsEvent, setResetControlsEvent] = useState({});
    const [valuesChangedCheck, setValuesChangedCheck] = useState({});

    const setResetControlEventHandler = (controlKey, event) => {
        setResetControlsEvent(prev => ({
            ...prev,
            [controlKey]: event
        }));
    };

    const setValueChangedCheckHandler = (controlKey, event) => {
        setValuesChangedCheck(prev => ({
            ...prev,
            [controlKey]: event
        }));
    };

    const popOverControl = (settings) => {
        let popOverStatus = settings.popover;

        if (popOverStatus.end === true) {
            let PopoverControls = Utils.PopoverControls();
            const PopoverTitle = popOverStatus.title;

            if (!PopoverControls && typeof PopoverControls !== 'object') {
                return <></>;
            }

            const firstControlKey = PopoverControls[0] || controlKey;

            dispatch(resetPopoverControls());
            dispatch(updatePopoverInitStatus(false));

            if (firstControlKey !== activePopoverControlKey) {
                return <div className="dragwyb-popover-wrapper" data-popover-key={firstControlKey}></div>
            }

            PopoverControls = [...PopoverControls || [], controlKey];

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

            return <div className="dragwyb-popover-wrapper active" data-popover-key={firstControlKey}>
                <div className="dragwyb-popover">
                    {PopoverTitle && <div className="dragwyb-popover__title">
                        {PopoverTitle}
                        <span onClick={resetControlsValues}>
                            <FaUndo size={12} title={__('Reset to Default', 'dragwyb-form-builder')} />
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
            </div>;
        };

        dispatch(updatePopoverControls(controlKey, popOverStatus))
        return null;
    }

    return popOverControl(settings);
};

export default RenderPopoverControls;
