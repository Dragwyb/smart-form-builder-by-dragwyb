import UnitSelector from "./common/UnitSelector";
import { RiLink, RiLinkUnlink } from "react-icons/ri";
import { __ } from "@wordpress/i18n";

export default class DimensionsControl extends DragwybEditor.editor.extends
    .ControlBase {
    controlName() {
        return "dimensions";
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const {
            label = __("Dimension", "smart-form-builder-by-dragwyb"),
            units = ["px", "%", "em", "rem"],
            default: defaultValue = {},
        } = settings;
        const { value = {} } = this.state;

        // fallback
        const currentValue = {
            top: this.getValidValue(value.top, defaultValue.top, ""),
            right: this.getValidValue(value.right, defaultValue.right, ""),
            bottom: this.getValidValue(value.bottom, defaultValue.bottom, ""),
            left: this.getValidValue(value.left, defaultValue.left, ""),
            unit: this.getValidValue(value.unit, defaultValue.unit, "px"),
            linked: this.getValidValue(value.linked, defaultValue.linked, settings.linked),
        };

        const updateValue = (key, val) => {
            val = val && val !== "" ? Number(val) : val;
            let newValue = { ...currentValue };

            if (
                currentValue.linked &&
                ["top", "right", "bottom", "left"].includes(key)
            ) {
                newValue.top = newValue.right = newValue.bottom = newValue.left = val;
            } else {
                newValue[key] = val;
            }

            this.updateControlHandler(id, newValue);
        };

        const updateUnit = (newUnit) => {
            let newValue = { ...currentValue, unit: newUnit };
            this.updateControlHandler(id, newValue);
        };

        const toggleLink = () => {
            let newValue = { ...currentValue, linked: !currentValue.linked };
            const sides = ["top", "right", "bottom", "left"];
            let largetValue = null;
            if (!currentValue.linked) {
                sides.forEach((side) => {
                    if (currentValue[side] > largetValue || null === largetValue) {
                        largetValue = currentValue[side];
                    }
                });
                newValue.top =
                    newValue.right =
                    newValue.bottom =
                    newValue.left =
                    largetValue;
            }

            this.updateControlHandler(id, newValue);
        };

        return (
            <div
                className="dragwyb-control dragwyb-control--dimensions"
                data-control="dimensions"
                id={`control-${id}`}
            >
                {/* Header */}
                <div className="dragwyb-dimensions__header dragwyb-label-inline">
                    <this.RenderLabel
                        attr={
                            { htmlFor: id }
                        }
                    />
                    {units && Object.keys(units).length > 1 && (
                        <UnitSelector
                            units={units}
                            value={currentValue.unit}
                            onChange={updateUnit}
                        />
                    )}
                </div>

                {/* Fields */}
                <div
                    className={`dragwyb-dimensions__row ${currentValue.linked ? "is-linked" : "is-unlinked"
                        }`}
                >
                    {currentValue.linked ? (
                        <>
                            <input
                                type="number"
                                value={currentValue.top}
                                onChange={(e) => updateValue("top", e.target.value)}
                            />
                            <button
                                type="button"
                                className={`dragwyb-dimensions__link ${currentValue.linked ? "is-linked" : ""
                                    }`}
                                onClick={toggleLink}
                            >
                                <RiLink />
                            </button>
                        </>
                    ) : (
                        <>
                            <div className="dragwyb-dimensions__inputs">
                                <input
                                    type="number"
                                    value={currentValue.top}
                                    onChange={(e) => updateValue("top", e.target.value)}
                                    title={__("Top", "smart-form-builder-by-dragwyb")}
                                />
                                <input
                                    type="number"
                                    value={currentValue.right}
                                    onChange={(e) => updateValue("right", e.target.value)}
                                    title={__("Right", "smart-form-builder-by-dragwyb")}
                                />
                                <input
                                    type="number"
                                    value={currentValue.bottom}
                                    onChange={(e) => updateValue("bottom", e.target.value)}
                                    title={__("Bottom", "smart-form-builder-by-dragwyb")}
                                />
                                <input
                                    type="number"
                                    value={currentValue.left}
                                    onChange={(e) => updateValue("left", e.target.value)}
                                    title={__("Left", "smart-form-builder-by-dragwyb")}
                                />
                            </div>
                            <button
                                type="button"
                                className="dragwyb-dimensions__link"
                                onClick={toggleLink}
                            >
                                <RiLinkUnlink />
                            </button>
                        </>
                    )}
                </div>
            </div>
        );
    }

    getStyleSelectorPlaceholder(value, placeholders) {
        placeholders = JSON.parse(JSON.stringify(placeholders));
        let valueExists = false;

        Object.keys(placeholders).forEach((placeholder) => {
            if (
                (!value || !value[placeholders[placeholder]] &&
                    value[placeholders[placeholder]] !== 0) ||
                "" === value[placeholders[placeholder]]
            ) {
                delete placeholders[placeholder];
            } else if ((value[placeholders[placeholder]] || 0 === value[placeholders[placeholder]]) && valueExists === false && placeholder !== 'UNIT') {
                valueExists = true;
            }
        });

        if (!valueExists) {
            placeholders = {};
        }

        return placeholders;
    }

    valueChanged() {
        const { default: defaultValue = {} } = this.settings;
        const value = this.state.value || {};

        const currentValue = {
            top: this.getValidValue(value.top, defaultValue.top, ""),
            right: this.getValidValue(value.right, defaultValue.right, ""),
            bottom: this.getValidValue(value.bottom, defaultValue.bottom, ""),
            left: this.getValidValue(value.left, defaultValue.left, ""),
            unit: this.getValidValue(value.unit, defaultValue.unit, "px"),
            linked: this.getValidValue(value.linked, defaultValue.linked, this.settings.linked),
        };

        let defaultVal = {
            top: this.getValidValue(defaultValue.top, ""),
            right: this.getValidValue(defaultValue.right, ""),
            bottom: this.getValidValue(defaultValue.bottom, ""),
            left: this.getValidValue(defaultValue.left, ""),
            unit: this.getValidValue(defaultValue.unit, "px"),
            linked: this.getValidValue(defaultValue.linked, false),
        };

        return !this.Utils.compareTwoObjects({ obj1: defaultVal, obj2: currentValue });
    }
}
