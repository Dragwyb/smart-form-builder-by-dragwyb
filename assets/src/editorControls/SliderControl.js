import UnitSelector from './common/UnitSelector';
import Slider from '../editor/components/Common/Slider';
import Reset from '../editor/components/Common/Reset';

export default class SliderControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'slider';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { label, range, default: defaultValue = {} } = settings;
        const { value = {} } = this.state;

        // Fallback to default value if no value is set
        // const currentValue = value || defaultValue || { unit: "px", size: 0 };
        const currentValue = { size: this.getValidValue(value.size, defaultValue.size, 0), unit: this.getValidValue(value.unit, defaultValue.unit, 'px') }

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
            const newValue = { ...currentValue, size: '' !== newSize ? Number(newSize) : '' };
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
                    <div className="dragwyb-control__header dragwyb-label-inline">
                        <this.RenderLabel
                            attr={
                                { htmlFor: id }
                            }
                        />
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
                    <Slider
                        value={currentValue.size}
                        min={unitRange.min}
                        max={unitRange.max}
                        step={unitRange.step || 1}
                        onChange={(value) => updateSize(value)}
                    />
                </div>
            </div>
        );
    }

    valueChanged() {
        const { default: defaultValue = {} } = this.settings;
        const { value = {} } = this.state;

        const currentValue = { size: this.getValidValue(value.size, defaultValue.size, ""), unit: this.getValidValue(value.unit, defaultValue.unit, 'px') }

        let defaultVal = { size: this.getValidValue(defaultValue.size, ""), unit: this.getValidValue(defaultValue.unit, "px") }

        return !this.Utils.compareTwoObjects({ obj1: defaultVal, obj2: currentValue });
    }
}