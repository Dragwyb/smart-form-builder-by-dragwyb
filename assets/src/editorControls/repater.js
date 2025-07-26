import shouldRenderField from "../editor/components/Editor/shouldRenderField";

class repeaterControl extends DragwybEditor.ControlBase {

    controlName() {
        return 'repeater';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id, value: repeaterItems } = this;

        const updateHandler = (key, value, index) => {
            if (!repeaterItems[index]) {
                return;
            }

            repeaterItems[index][key] = value;

            this.updateControls(id, repeaterItems);
        }
        const deleteHandler = (key, value) => { }
        const copyHandler = (key, value) => { }

        const addFieldHandler = () => {
            repeaterItems.push({_id: this.utils.generateId()})

            this.updateControls(id, repeaterItems);
        }
        const copyFieldHandler = (id) => { }
        const deleteFieldHandler = (id) => { }

        return (
            <div class="dragwyb-repeater-field">
                <label>{settings.label}</label>
                {repeaterItems.map((repeaterItem, index) => {
                    return (<div class="dragwyb-repeater-item">
                        <div class="dragwyb-repeater-header">
                            <span class="repeater-title">Option Title One</span>
                            <div class="repeater-controls">
                                <span class="drag-icon">⠿</span>
                                <button class="copy-btn" title="Copy">📄</button>
                                <button class="delete-btn" title="Delete">🗑️</button>
                            </div>
                        </div>
                        <div class="dragwyb-repeater-body">
                            {Object.values(settings.fields).map(data => {
                                return renderControls({ key: data.name, value: repeaterItem[data.name] || '', repeaterValue: repeaterItem, settings: data, updateHandler: (key, value) => updateHandler(key, value, index) })
                            })}
                        </div>
                    </div>)
                })}

                <div className="add-repeater-btn"><button onClick={addFieldHandler}>{settings.add_item}</button></div>
            </div>
        );
    }
}


const renderControls = ({ key, settings, value, repeaterValue, updateHandler }) => {

    if (!settings.type) {
        return;
    }

    if (!DragwybEditor.controlTypes[settings.type]) {
        return <></>;
    }

    const shouldRender = shouldRenderField(settings, repeaterValue);

    if (!shouldRender) {
        return;
    }

    // if (settings.type === 'tabs') {
    //     defautlActiveTab(key, settings)
    // }

    const getHtml = () => {
        return <div>Unsupported Controller type: {settings.type}</div>;
    }


    let html = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/ControlRender/' + settings.type, getHtml(), key, settings, value, updateHandler);

    return <div key={key} className="setting-row" dataType={settings.type}>{html}</div>;
};


const intializeRepeater = ([data, utils]) => {
    if (!data || data.length <= 0) {
        return [{ _id: utils.generateId() }];
    }

    data.forEach((_, index) => {
        data[index]._id = utils.generateId();
    });

    return data;
}

jQuery(document).on('Dragwyb:editorInit', () => {
    DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/AddControl/repeater.defaultValue', (...args) => intializeRepeater(args));
    DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/DuplicateControl/repeater.duplicateValue', (...args) => intializeRepeater(args));
});



export default repeaterControl;