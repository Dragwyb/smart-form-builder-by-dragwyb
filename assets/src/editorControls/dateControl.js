export default class DateControl extends DragwybEditor.editor.extends.ControlBase {
    flatpickrInstance = null;

    controlName() {
        return 'date';
    }

    getMode() {
        const mode = this.settings?.mode || 'single';
        return ['single', 'multiple', 'range'].includes(mode) ? mode : 'single';
    }

    isTimePicker() {
        return this.settings?.picker === 'time';
    }

    isTime24hr() {
        if (this.fieldValue && Object.prototype.hasOwnProperty.call(this.fieldValue, 'time_24hr')) {
            return this.fieldValue.time_24hr === 'yes';
        }
        return this.settings?.time_24hr !== false;
    }

    getDateFormat() {
        if (this.isTimePicker()) {
            return this.settings?.date_format || 'H:i';
        }
        return this.settings?.date_format || 'Y-m-d';
    }

    getPlaceholder() {
        if (this.isTimePicker()) {
            return 'HH:MM';
        }

        const mode = this.getMode();
        if (mode === 'multiple') {
            return 'YYYY-MM-DD, YYYY-MM-DD';
        }
        if (mode === 'range') {
            return 'YYYY-MM-DD to YYYY-MM-DD';
        }
        return 'YYYY-MM-DD';
    }

    parseDefaultDates(value, mode) {
        if (!value) {
            return null;
        }

        if (this.isTimePicker()) {
            return value;
        }

        if (mode === 'multiple') {
            return String(value)
                .split(',')
                .map((part) => part.trim())
                .filter(Boolean);
        }

        if (mode === 'range') {
            const parts = String(value).split(/\s+to\s+/i).map((part) => part.trim()).filter(Boolean);
            return parts.length ? parts : null;
        }

        return value;
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

        const mode = this.getMode();
        const isTime = this.isTimePicker();
        const { value = this.settings.default || '' } = this.state;
        const options = {
            dateFormat: this.getDateFormat(),
            allowInput: true,
            mode: isTime ? 'single' : mode,
            conjunction: ', ',
            defaultDate: this.parseDefaultDates(value, mode),
            onChange: (selectedDates, dateStr) => {
                this.updateControlHandler(this.id, dateStr);
            },
        };

        if (isTime) {
            options.enableTime = true;
            options.noCalendar = true;
            options.time_24hr = this.isTime24hr();
        }

        this.flatpickrInstance = window.flatpickr(input, options);
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value = settings.default || '' } = this.state;
        const mode = this.getMode();
        const isTime = this.isTimePicker();

        return (
            <div
                className="dragwyb-control dragwyb-control--date"
                data-control="date"
                data-mode={mode}
                data-picker={isTime ? 'time' : 'date'}
                id={`control-${id}`}
            >
                <this.RenderLabel
                    attr={{ htmlFor: id }}
                />
                <input
                    type="text"
                    className="dragwyb-control__input"
                    id={id}
                    name={id}
                    value={value}
                    placeholder={this.getPlaceholder()}
                    onChange={(e) => this.updateControlHandler(id, e.target.value)}
                />
            </div>
        );
    }
}
