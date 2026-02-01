class textField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'text'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;

        return (
            <>
                <div className="dragwyb-input-group">
                    <input
                        type="text"
                        id={fieldId}
                        className="dragwyb-field-input"
                        placeholder={s.placeholder || ' '}
                        defaultValue={s.default_value}
                        onChange={(e) => this.updateField(this.id, e.target.value)}
                    />
                    {label && (
                        <label htmlFor={fieldId} className="dragwyb-field-label">
                            {label}
                            {s.required === 'yes' && <span className="dragwyb-required">*</span>}
                        </label>
                    )}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class emailField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'email'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;

        return (
            <>
                <div className="dragwyb-input-group">
                    <input
                        type="email"
                        id={fieldId}
                        className="dragwyb-field-input"
                        placeholder={s.placeholder || ' '}
                        defaultValue={s.default_value}
                        onChange={(e) => this.updateField(this.id, e.target.value)}
                    />
                    {label && (
                        <label htmlFor={fieldId} className="dragwyb-field-label">
                            {label}
                            {s.required === 'yes' && <span className="dragwyb-required">*</span>}
                        </label>
                    )}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class dateField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'date'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;

        return (
            <>
                <div className="dragwyb-input-group">
                    <input
                        type="date"
                        id={fieldId}
                        className="dragwyb-field-input"
                        placeholder={s.placeholder || ' '}
                        defaultValue={s.default_value}
                        onChange={(e) => this.updateField(this.id, e.target.value)}
                    />
                    {label && (
                        <label htmlFor={fieldId} className="dragwyb-field-label">
                            {label}
                            {s.required === 'yes' && <span className="dragwyb-required">*</span>}
                        </label>
                    )}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

// 2. Textarea
class textAreaField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'textarea'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;

        return (
            <>
                <div className="dragwyb-input-group">
                    <textarea
                        id={fieldId}
                        rows={s.rows || 4}
                        placeholder={s.placeholder || ' '}
                        className="dragwyb-field-input"
                        onChange={(e) => this.updateField(this.id, e.target.value)}
                    ></textarea>
                    {label && (
                        <label htmlFor={fieldId} className="dragwyb-field-label">
                            {label}
                            {s.required === 'yes' && <span className="dragwyb-required">*</span>}
                        </label>
                    )}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

// 3. Select
class selectField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'select'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const options = s.options_list || [];
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;

        return (
            <>
                <div className="dragwyb-input-group">
                    <select id={fieldId} className="dragwyb-field-input" multiple={s.multiple === 'yes'}
                        onChange={(e) => this.updateField(this.id, e.target.value)}
                    >
                        {options.map((opt, i) => (
                            <option key={i} value={opt.option_value}>{opt.option_label}</option>
                        ))}
                    </select>
                    {label && (
                        <label htmlFor={fieldId} className="dragwyb-field-label">
                            {label}
                            {s.required === 'yes' && <span className="dragwyb-required">*</span>}
                        </label>
                    )}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

// 4. Radio (Standard Order)
class radioField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'radio'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const options = s.options_list || [];
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;
        const layoutClass = s.layout === 'inline' ? 'dragwyb-inline' : '';

        return (
            <>
                <div className="dragwyb-input-group">
                    {label && (
                        <label htmlFor={fieldId} className="dragwyb-field-label">
                            {label}
                            {s.required === 'yes' && <span className="dragwyb-required">*</span>}
                        </label>
                    )}
                    <div className={`dragwyb-options-container ${layoutClass}`}>
                        {options.map((opt, i) => (
                            <label key={i} className="dragwyb-option-item">
                                <input type="radio" name={fieldId} value={opt.option_value}
                                    onChange={(e) => this.updateField(this.id, e.target.value)} />
                                <span className="dragwyb-radio-label">{opt.option_label}</span>
                            </label>
                        ))}
                    </div>
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class fileField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'file'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;

        return (
            <>
                {label && (
                    <label htmlFor={fieldId} className="dragwyb-field-label">
                        {label}
                        {s.required === 'yes' && <span className="dragwyb-required">*</span>}
                    </label>
                )}
                <div className="dragwyb-file-upload-container">
                    <input type="file" id={fieldId} className="dragwyb-field-input" disabled />
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

// 5. Button
class ButtonField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'button'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const buttonType = s.button_type || 'button';

        return (
            <button type={buttonType} onClick={(e) => e.preventDefault()}>
                {s.text || 'Submit'}
            </button>
        );
    }
}

const initializeFields = () => {
    const defaultFields = {
        'text': (args) => new textField(args),
        'textarea': (args) => new textAreaField(args),
        'select': (args) => new selectField(args),
        'radio': (args) => new radioField(args),
        'file': (args) => new fileField(args),
        'email': (args) => new emailField(args),
        'date': (args) => new dateField(args),
        'button': (args) => new ButtonField(args),
    };

    Object.keys(defaultFields).forEach(key =>
        DragwybBuilder.Hooks.addFilter(
            'Dragwyb/Editor/FieldRender/' + key,
            (...args) => defaultFields[key](args)
        )
    );
};

jQuery(document).on('Dragwyb:editorInit', () => {
    initializeFields();
});