export default class DateControl extends DragwybEditor.editor.extends.ControlBase {
    flatpickrInstance = null;

    controlName() {
        return 'date';
    }

    onRender() {
        this.initFlatpickr();
    }

    onDestroy() {
        if (this.flatpickrInstance) {
            this.flatpickrInstance.destroy();
            this.flatpickrInstance = null;
        }
    }

    initFlatpickr() {
        if (typeof window.flatpickr !== 'function' || this.flatpickrInstance) {
            return;
        }

        const input = document.getElementById(this.id);
        if (!input) {
            return;
        }

        const { value = this.settings.default || '' } = this.state;

        this.flatpickrInstance = window.flatpickr(input, {
            enableTime: false,
            dateFormat: 'Y-m-d',
            allowInput: true,
            defaultDate: value || null,
            onChange: (selectedDates, dateStr) => {
                this.updateControlHandler(this.id, dateStr);
            },
        });
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value = settings.default || '' } = this.state;

        return (
            <div className="dragwyb-control dragwyb-control--date" data-control="date" id={`control-${id}`}>
                <this.RenderLabel
                    attr={{ htmlFor: id }}
                />
                <input
                    type="text"
                    className="dragwyb-control__input"
                    id={id}
                    name={id}
                    value={value}
                    placeholder="YYYY-MM-DD"
                    onChange={(e) => this.updateControlHandler(id, e.target.value)}
                />
            </div>
        );
    }
}
