class textField extends DragwybEditor.FieldBase {
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

class textAreaField extends DragwybEditor.FieldBase {
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

class selectField extends DragwybEditor.FieldBase {
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

class radioField extends DragwybEditor.FieldBase {
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

class fileField extends DragwybEditor.FieldBase {
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

class emailField extends DragwybEditor.FieldBase {
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

const initializeFields = () => {
    const defaultFields = {
        'text': (args) => new textField(args),
        'textarea': (args) => new textAreaField(args),
        'select': (args) => new selectField(args),
        'radio': (args) => new radioField(args),
        'file': (args) => new fileField(args),
        'email': (args) => new emailField(args),
    };

    Object.keys(defaultFields).forEach(key =>
        DragwybBuilder.Hooks.addFilter(
            'Dragwyb/Editor/FieldRender/' + key,
            (...args) => defaultFields[key](args)
        )
    );
};

jQuery(document).on('Dragwyb:editorInit', () => {   
    DragwybBuilder.Hooks.addAction('Dragwyb/Editor/FieldBase',initializeFields);
});