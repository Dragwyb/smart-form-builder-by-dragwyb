export default class ChooseControl extends DragwybEditor.editor.extends.ControlBase {

    controlName() {
        return 'choose';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        // Default to empty string if no value is set
        const currentValue = this.state.value !== undefined ? this.state.value : (settings.default || '');
        const labelInline = settings.label_inline || false;

        // Ensure options exist
        const options = settings.options || {};

        let wrapperClass = 'dragwyb-control dragwyb-control--choose';

        if (labelInline) {
            wrapperClass += ' dragwyb-label-inline';
        }

        return (
            <div className={wrapperClass} data-control="choose" id={`control-${id}`}>

                {settings.label && (
                    <label className="dragwyb-control__label" htmlFor={id}>
                        {settings.label}
                    </label>
                )}

                <div className="dragwyb-control__content">
                    <div className="dragwyb-choose-group">
                        {Object.keys(options).map((optionKey) => {
                            const option = options[optionKey];
                            const isActive = currentValue === optionKey;

                            return (
                                <div
                                    key={optionKey}
                                    className={`dragwyb-choose-option ${isActive ? 'active' : ''}`}
                                    onClick={() => this.handleOptionClick(optionKey, currentValue)}
                                    title={option.title}
                                >
                                    {/* Render Icon if it exists */}
                                    {option.icon && (
                                        <i className={option.icon} aria-hidden="true"></i>
                                    )}

                                    {/* Fallback to text if no icon, or for tooltips */}
                                    {!option.icon && <span>{option.title}</span>}

                                    <input
                                        type="radio"
                                        name={id}
                                        value={optionKey}
                                        checked={isActive}
                                        readOnly
                                        style={{ display: 'none' }}
                                    />
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>
        );
    }

    handleOptionClick(newValue, currentValue) {
        let finalValue = newValue;

        // If toggle is enabled and user clicks the active item, unselect it.
        if (this.settings.toggle && newValue === currentValue) {
            finalValue = '';
        }

        this.updateControlHandler(this.id, finalValue);
    }
}