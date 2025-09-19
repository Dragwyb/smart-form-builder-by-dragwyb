import UnitSelector from './common/UnitSelector';
import { RiLink, RiLinkUnlink } from "react-icons/ri";

export default class DimensionsControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return "dimensions";
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { label, units = ["px", "%", "em", "rem"], default: defaultValue } = settings;
        const { value } = this.state;

        // fallback
        const currentValue =
            value || defaultValue || { top: "", right: "", bottom: "", left: "", unit: "px", linked: true };

        const updateValue = (key, val) => {
            val = val && val !== '' ? Number(val) : val;
            let newValue = { ...currentValue };

            if (currentValue.linked && ["top", "right", "bottom", "left"].includes(key)) {
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
            this.updateControlHandler(id, newValue);
        };

        return (
            <div
                className="dragwyb-control dragwyb-control--dimensions"
                data-control="dimensions"
                id={`control-${id}`}
            >
                {/* Header */}
                <div className="dragwyb-dimensions__header">
                    {label && (
                        <label className="dragwyb-control__label" htmlFor={id}>
                            {label}
                        </label>
                    )}
                    {units && Object.keys(units).length > 1 &&
                        <UnitSelector
                            units={units}
                            value={currentValue.unit}
                            onChange={updateUnit}
                        />
                    }
                </div>

                {/* Fields */}
                <div
                    className={`dragwyb-dimensions__row ${
                        currentValue.linked ? "is-linked" : "is-unlinked"
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
                                className={`dragwyb-dimensions__link ${
                                    currentValue.linked ? "is-linked" : ""
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
                                />
                                <input
                                    type="number"
                                    value={currentValue.right}
                                    onChange={(e) => updateValue("right", e.target.value)}
                                />
                                <input
                                    type="number"
                                    value={currentValue.bottom}
                                    onChange={(e) => updateValue("bottom", e.target.value)}
                                />
                                <input
                                    type="number"
                                    value={currentValue.left}
                                    onChange={(e) => updateValue("left", e.target.value)}
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
}