import UnitSelector from './common/UnitSelector';
import { RiLink, RiLinkUnlink } from "react-icons/ri";
import Select from "../editor/components/Common/Select";
import SelectGroup from "../editor/components/Common/SelectGroup";

export default class FontsControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return "fonts";
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;
        const { label, options } = settings;

        const optionsByGroups = (options) => {
            const groups = [];
            Object.keys(options).map((opt) => {
                const group = options[opt];
                if (!groups[group]) groups[group] = { label: group, options: [] };
                groups[group].options.push({ value: opt, label: opt })
            })

            return Object.values(groups)
        }

        // fallback
        const currentValue = value || "Default";

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
                </div>

                {/* Fields */}
                <SelectGroup
                    options={optionsByGroups(options)}
                    value={currentValue}
                    onChange={(e) => this.setState({ value: e.target.value })}
                />
            </div>
        );
    }
}