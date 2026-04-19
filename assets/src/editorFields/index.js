import rowField from './row';

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
                            !opt.attributes ? null :
                                <option key={i} value={opt.attributes.option_value}>{opt.attributes.option_label}</option>
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
        const layoutClass = s.layout === 'inline' ? 'dragwyb-inline-options' : '';

        return (
            <>
                <div className="dragwyb-input-group">
                    <div className={`dragwyb-options-container ${layoutClass}`}>
                        {options.map((opt, i) => (
                            !opt.attributes ? null :
                                <label key={i} className="dragwyb-option-item">
                                    <input type="radio" name={fieldId} value={opt.attributes.option_value}
                                        onChange={(e) => this.updateField(this.id, e.target.value)} />
                                    <span className="dragwyb-radio-label">{opt.attributes.option_label}</span>
                                </label>
                        ))}
                    </div>
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

class checkboxField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'checkbox'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const options = s.options_list || [];
        const layoutClass = s.layout === 'inline' ? 'dragwyb-inline-options' : '';

        return (
            <>
                <div className="dragwyb-input-group">
                    {s.label && <div className="dragwyb-field-label">{s.label}</div>}
                    <div className={`dragwyb-options-container ${layoutClass}`}>
                        {options.map((opt, i) => (
                            !opt.attributes ? null :
                                <label key={i} className="dragwyb-option-item">
                                    <input type="checkbox" name={`${fieldId}[]`} value={opt.attributes.option_value} />
                                    <span className="dragwyb-radio-label">{opt.attributes.option_label}</span>
                                </label>
                        ))}
                    </div>
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class numberField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'number'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const { label = defaultLabel } = s;
        return (
            <>
                <div className="dragwyb-input-group">
                    <input
                        type="number"
                        id={fieldId}
                        className="dragwyb-field-input"
                        placeholder={s.placeholder || ' '}
                        min={s.min_val}
                        max={s.max_val}
                        step={s.step}
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

class hiddenField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'hidden'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;

        // Custom style to represent invisible field in editor
        const placeholderStyle = {
            padding: '10px',
            border: '1px dashed #9ca3af',
            backgroundColor: '#f3f4f6',
            color: '#6b7280',
            fontSize: '13px',
            borderRadius: '4px',
            display: 'flex',
            alignItems: 'center',
            gap: '8px'
        };

        return (
            <>
                <div style={placeholderStyle}>
                    <i className="fas fa-eye-slash"></i>
                    <strong>Hidden Field:</strong> {fieldId}
                    <span style={{ fontSize: '11px', marginLeft: 'auto' }}>(Value: {s.default_value || '(empty)'})</span>
                </div>
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
        'checkbox': (args) => new checkboxField(args),
        'number': (args) => new numberField(args),
        'hidden': (args) => new hiddenField(args),
        'button': (args) => new ButtonField(args),
        'row': (args) => new rowField(args),
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