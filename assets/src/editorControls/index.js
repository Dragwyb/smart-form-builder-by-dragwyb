
import '../../sass/editorControls.scss';
import RepeaterControl from './Repeater/index';
import { RiArrowDownSLine, RiLink, RiLinkUnlink } from "react-icons/ri";
import UnitSelector from './common/UnitSelector';

class SectionControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'section';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;

        let sectionCls = 'dragwyb-control dragwyb-control--section';

        if (id === value) {
            sectionCls += ' section-active';
        }

        return (
            <div className={sectionCls} data-control="section" id={`control-${id}`} onClick={() => { this.updateControlHandler(id, !(id === value)) }}>
                <span className="dragwyb-section__title">{settings.label}</span>
                <RiArrowDownSLine />
            </div>
        );
    }

    updateControlHandler(key, value) {
        this.setState({ value: value ? key : '' })
        this.updateControls(key, value);
    }
}

class TextControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'text';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value = settings.default } = this.state;

        return (
            <div className="dragwyb-control dragwyb-control--text" data-control="text" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label" htmlFor={id}>
                        {settings.label}
                    </label>
                )}
                <input
                    type="text"
                    className="dragwyb-control__input"
                    id={id}
                    name={id}
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, e.target.value)}
                />
            </div>
        );
    }
}

class SelectControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'select';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;
        const options = settings.options || {};

        return (
            <div className="dragwyb-control dragwyb-control--select" data-control="select" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label" htmlFor={id}>
                        {settings.label}
                    </label>
                )}
                <select
                    id={id}
                    name={id}
                    className="dragwyb-control__select"
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, e.target.value)}
                >
                    {Object.keys(options).map((key) => (
                        <option key={key} value={key}>
                            {options[key]}
                        </option>
                    ))}
                </select>
            </div>
        );
    }
}

class TextareaControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'textarea';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;

        return (
            <div className="dragwyb-control dragwyb-control--textarea" data-control="textarea" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label" htmlFor={id}>
                        {settings.label}
                    </label>
                )}
                <textarea
                    id={id}
                    name={id}
                    className="dragwyb-control__textarea"
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, e.target.value)}
                />
            </div>
        );
    }
}

class SwitcherControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'switcher';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value = settings.default } = this.state;
        const returnValue = settings.return_value;

        const changeHandler = () => {
            const updatedValue = value === returnValue ? null : returnValue;
            this.updateControlHandler(id, updatedValue)
        }

        return (
            <div
                className="dragwyb-control dragwyb-control--switcher"
                data-control="switcher"
                id={`control-${id}`}
            >
                {settings.label && (
                    <label
                        className="dragwyb-control__label"
                        htmlFor={id}
                    >
                        {settings.label}
                    </label>
                )}

                <label className="dragwyb-switcher">
                    <input
                        type="checkbox"
                        id={id}
                        name={id}
                        checked={value === returnValue}
                        onChange={changeHandler}
                    />
                    <span className="dragwyb-switcher__slider">
                        {settings.show_label === true && (value === returnValue ? settings.on_label : settings.off_label)}
                    </span>
                </label>
            </div>
        );
    }
}

class RadioControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'radio';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;

        return (
            <div className="dragwyb-control dragwyb-control--radio" data-control="radio" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label">{settings.label}</label>
                )}
                <div className="dragwyb-control__options">
                    {options.map((opt) => (
                        <label key={opt.value} className="dragwyb-radio">
                            <input
                                type="radio"
                                name={id}
                                value={opt.value}
                                checked={value === opt.value}
                                onChange={(e) => this.updateControlHandler(id, e.target.value)}
                            />
                            <span className="dragwyb-radio__custom" />
                            <span className="dragwyb-radio__label">{opt.label}</span>
                        </label>
                    ))}
                </div>
            </div>
        );
    }
}

class SliderControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'slider';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { label, range, default: defaultValue } = settings;
        const { value } = this.state;

        // Fallback to default value if no value is set
        const currentValue = value || defaultValue || { unit: "px", size: 0 };

        const units = settings.units;

        const updateUnit = (newUnit) => {
            const newValue = { ...currentValue, unit: newUnit };

            // Ensure size is valid for new unit
            if (range[newUnit]) {
                const { min = 0, max = 100, step = 1 } = range && range[newUnit] ? range[newUnit] : { min: 0, max: 100, step: 1 };
                if (newValue.size < min) newValue.size = min;
                if (newValue.size > max) newValue.size = max;
                if (step && newValue.size % step !== 0) {
                    newValue.size = Math.round(newValue.size / step) * step;
                }
            }

            this.updateControlHandler(id, newValue);
        };

        const updateSize = (newSize) => {
            const newValue = { ...currentValue, size: Number(newSize) };
            this.updateControlHandler(id, newValue);
        };

        const unitRange = range && range[currentValue.unit] ? range[currentValue.unit] : { min: 0, max: 100, step: 1 };

        return (
            <div
                className="dragwyb-control dragwyb-control--slider"
                data-control="slider"
                id={`control-${id}`}
            >
                {label && (
                    <div className="dragwyb-control__header">
                        <label className="dragwyb-control__label" htmlFor={id}>
                            {label}
                        </label>
                        {units && Object.keys(units).length > 1 &&
                            <UnitSelector
                                units={units}
                                value={currentValue.unit}
                                onChange={updateUnit}
                            />
                        }
                    </div>
                )}

                <div className="dragwyb-slider__row">
                    <input
                        type="range"
                        id={id}
                        min={unitRange.min}
                        max={unitRange.max}
                        step={unitRange.step || currentValue.unit === 'px' ? 1 : 0.1}
                        value={currentValue.size}
                        onChange={(e) => updateSize(e.target.value)}
                        className="dragwyb-slider__input"
                    />
                    <input
                        type="number"
                        className="dragwyb-slider__number"
                        value={currentValue.size}
                        min={unitRange.min}
                        max={unitRange.max}
                        step={unitRange.step || 1}
                        onChange={(e) => updateSize(e.target.value)}
                    />
                </div>
            </div>
        );
    }
}

class DimensionsControl extends DragwybEditor.editor.extends.ControlBase {
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
            value || defaultValue || { top: "", right: "", bottom: "", left: "", unit: "px", isLinked: true };

        const updateValue = (key, val) => {
            let newValue = { ...currentValue };

            if (currentValue.isLinked && ["top", "right", "bottom", "left"].includes(key)) {
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
            let newValue = { ...currentValue, isLinked: !currentValue.isLinked };
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
                    {currentValue.isLinked && (
                        <div className="dragwyb-dimensions__unit">
                            <select value={currentValue.unit} onChange={(e) => updateUnit(e.target.value)}>
                                {units.map((u) => (
                                    <option key={u} value={u}>
                                        {u}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}
                </div>

                {/* Fields */}
                <div
                    className={`dragwyb-dimensions__row ${
                        currentValue.isLinked ? "is-linked" : "is-unlinked"
                    }`}
                >
                    {currentValue.isLinked ? (
                        <>
                            <input
                                type="number"
                                value={currentValue.top}
                                placeholder="All"
                                onChange={(e) => updateValue("top", e.target.value)}
                            />
                            <button
                                type="button"
                                className={`dragwyb-dimensions__link ${
                                    currentValue.isLinked ? "is-linked" : ""
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
                                    placeholder="T"
                                    value={currentValue.top}
                                    onChange={(e) => updateValue("top", e.target.value)}
                                />
                                <input
                                    type="number"
                                    placeholder="R"
                                    value={currentValue.right}
                                    onChange={(e) => updateValue("right", e.target.value)}
                                />
                                <input
                                    type="number"
                                    placeholder="B"
                                    value={currentValue.bottom}
                                    onChange={(e) => updateValue("bottom", e.target.value)}
                                />
                                <input
                                    type="number"
                                    placeholder="L"
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
                            <div className="dragwyb-dimensions__unit">
                                <select
                                    value={currentValue.unit}
                                    onChange={(e) => updateUnit(e.target.value)}
                                >
                                    {units.map((u) => (
                                        <option key={u} value={u}>
                                            {u}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </>
                    )}
                </div>
            </div>
        );
    }
}

class NumberControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'number';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;

        const min = settings.min ?? 0;
        const max = settings.max ?? 100;
        const step = settings.step ?? 1;

        return (
            <div className="dragwyb-control dragwyb-control--number" data-control="number" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label" htmlFor={id}>
                        {settings.label}
                    </label>
                )}
                <input
                    type="number"
                    id={id}
                    name={id}
                    className="dragwyb-control__input"
                    min={min}
                    max={max}
                    step={step}
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, parseFloat(e.target.value))}
                />
            </div>
        );
    }
}

class ColorControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'color';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;

        return (
            <div className="dragwyb-control dragwyb-control--color" data-control="color" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label" htmlFor={id}>
                        {settings.label}
                    </label>
                )}
                <div className="dragwyb-color__wrapper">
                    <input
                        type="color"
                        id={id}
                        name={id}
                        className="dragwyb-control__color"
                        value={value}
                        onChange={(e) => this.updateControlHandler(id, e.target.value)}
                    />
                    <span
                        className="dragwyb-color__preview"
                        style={{ backgroundColor: value }}
                    />
                </div>
            </div>
        );
    }
}

class TabsControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'tabs';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;
        const options = settings.tabs || [];

        if (!value && value === '' && Object.keys(options).length > 0) {
            this.updateControlHandler(id, Object.keys(options)[0]);
        }

        return (
            <div className="dragwyb-control dragwyb-control--tabs" data-control="tabs" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label">
                        {settings.label}
                    </label>
                )}
                <div className="dragwyb-tabs__nav">
                    {Object.keys(options)?.map((option) => (
                        <button
                            key={options[option].value}
                            type="button"
                            className={`dragwyb-tabs__nav-item ${value === option ? 'is-active' : ''}`}
                            onClick={() => this.updateControlHandler(id, option)}
                        >
                            {options[option].label}
                        </button>
                    ))}
                </div>
            </div>
        );
    }
}

class PopoverToggleControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return "popover-toggle";
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;

        return (
            <div className="dragwyb-control dragwyb-control--popover-toggle" data-control="popover-toggle" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label">
                        {settings.label}
                    </label>
                )}
                <button
                    type="button"
                    className={`dragwyb-popover__trigger ${value ? 'is-active' : ''}`}
                    onClick={() => this.updateControlHandler(id, !value)}
                >
                    {settings.buttonLabel || 'Toggle'}
                </button>
                {value && (
                    <div className="dragwyb-popover__content">
                        {this.props.children || settings.content}
                    </div>
                )}
            </div>
        );
    }
}

const initializeControls = () => {
    const defaultControls = {
        'text': TextControl,
        'select': SelectControl,
        'textarea': TextareaControl,
        'switcher': SwitcherControl,
        'dimensions': DimensionsControl,
        'radio': RadioControl,
        'slider': SliderControl,
        'number': NumberControl,
        'color': ColorControl,
        'tabs': TabsControl,
        'section': SectionControl,
        'repeater': RepeaterControl,
        'popover-toggle': PopoverToggleControl
    }

    Object.keys(defaultControls).map(key => DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/ControlRender/' + key, () => { return defaultControls[key] }))

}

jQuery(document).on('Dragwyb:editorInit', () => {
    initializeControls();
});