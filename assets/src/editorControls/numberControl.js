import Reset from '../editor/components/Common/Reset';

export default class NumberControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'number';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;
        const { default: defaultValue, min = 0, max = 100, step = 1 } = settings;

        return (
            <div className="dragwyb-control dragwyb-control--number" data-control="number" id={`control-${id}`}>
                <this.RenderLabel
                    attr={
                        { htmlFor: id }
                    }
                />
                <input
                    type="number"
                    id={id}
                    name={id}
                    className="dragwyb-control__input"
                    min={min}
                    max={max}
                    step={step}
                    value={value || defaultValue}
                    onChange={(e) => this.updateControlHandler(id, parseFloat(e.target.value))}
                />
            </div>
        );
    }
}