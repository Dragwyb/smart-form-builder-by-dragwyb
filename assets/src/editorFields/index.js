class textField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() {
        return 'text';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        return (
            <label for={this.id}>
                {this.attributes?.text_label && this.attributes.text_label}
                <input
                    type={this.fieldName}
                    value={this.value}
                    id={this.id}
                    onChange={(e) => this.updateField(this.id, e.target.value)} />
            </label>
        );
    }
}

class textAreaField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() {
        return 'textarea';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        return (
            <textarea
                value={this.value}
                onChange={(e) => this.updateField(this.id, e.target.value)}
            />
        );
    }
}

class selectField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() {
        return 'select';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        return (
            <select
                value={this.value}
                onChange={(e) => this.updateField(this.id, e.target.value)}
            >
                {(this.options || []).map((option, index) => (
                    <option key={index} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        );
    }
}

class radioField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() {
        return 'radio';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        return (
            <div>
                {this.options && this.options.length > 0 ? (this.options).map((option, index) => (
                    <label key={index}>
                        <input
                            type={this.fieldName}
                            name={this.id}
                            value={option.value}
                            checked={this.value === option.value}
                            onChange={(e) => this.updateField(this.id, e.target.value)}
                        />
                        {option.label}
                    </label>
                )) : <input type={this.fieldName} />}
            </div>
        );
    }
}

class fileField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() {
        return 'file';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        return (
            <input
                type={this.fieldName}
                onChange={(e) => this.updateField(this.id, e.target.files[0])}
            />
        );
    }
}

class emailField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() {
        return 'email';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        return (
            <input
                type={this.fieldName}
                value={this.value}
                onChange={(e) => this.updateField(this.id, e.target.value)}
            />
        );
    }
}

class ButtonField extends DragwybEditor.editor.extends.FieldBase {

    // Unique identifier for this field type
    fieldName() {
        return 'button';
    }

    // Main render method
    bind() {
        if (!this.shouldRender()) return <></>;

        // 1. Get Settings (Defaults match your PHP)
        const text = this.attributes.text || 'Submit';
        const align = this.attributes.button_align || 'left';
        const width = this.attributes.width || 'auto';
        const action = this.attributes.button_action || 'submit'; // 'submit' or 'reset'

        // 2. Build Classes
        let btnClasses = `dragwyb-btn dragwyb-btn-${action}`;
        if (width === '100%') {
            btnClasses += ' dragwyb-btn-block';
        }

        // 3. Wrapper Style for Alignment
        const wrapperStyle = {
            textAlign: align,
            marginTop: '10px' // Visual separation in editor
        };

        return (
            <div
                className="dragwyb-field-button-wrapper"
                style={wrapperStyle}
            >
                <button
                    type="button" // Always 'button' in editor to prevent form submission
                    className={btnClasses}
                    id={`dragwyb_btn_${this.id}`}
                    // Prevent default action in editor
                    onClick={(e) => e.preventDefault()}
                    type={action}
                >
                    {text}
                </button>
            </div>
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