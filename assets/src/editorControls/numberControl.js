import Reset from '../editor/components/Common/Reset';

export default class NumberControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'number';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { default: defaultValue, min = 0, max = 100, step = 1 } = settings;
        const { value = defaultValue } = this.state;

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
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, e.target.value)}
                />
            </div>
        );
    }
}