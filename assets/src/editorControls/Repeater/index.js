import RepeaterSortable from "./RepeaterSortable.jsx";

class RepeaterControl extends DragwybEditor.editor.extends.ControlBase {

    controlName() {
        return 'repeater';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        const { settings, id } = this;
        const { value: repeaterItems, tabsSettings = {} } = this.state;

        
        const updateHandler = (id, item) => {
            this.setState({value: item})
            this.updateControls(id, item)
        }

        const updateTabsSettings = (id, value) => {
            const tabsSettings = this?.state?.tabsSettings || {};

            if (tabsSettings && (!tabsSettings[id] || tabsSettings[id] !== value)) {
                this.setState({ tabsSettings: { ...tabsSettings, [id]: value } });
            }
        }

        const resetTabSettings = (id, value) => {
            this.setState({ tabsSettings: {} });
        }

        const addItemHandler = () => {
            let updated = { _id: this.Utils.generateId(), attributes: {} };

            if (settings.items && Object.keys(settings.items).length > 0) {
                Object.keys(settings.items).forEach(item => {
                    let defaultValue = settings.items[item]?.default;

                    if (defaultValue) {
                        defaultValue = DragwybBuilder.Hooks.applyFilter(`Dragwyb/Editor/AddControl/${settings.items[item].type}.defaultValue`, defaultValue, settings, this.Utils);

                        updated.attributes[item] = defaultValue;
                    }

                });
            }

            updated = [...repeaterItems || [], ...[updated]];
            updateHandler(id, updated);

        }

        return (
            <>
                <label>{settings.label}</label>
                <div className="dragwyb-repeater-field">
                    {repeaterItems && repeaterItems.length > 0 &&
                        <RepeaterSortable
                            items={repeaterItems}
                            settings={settings}
                            updateControls={updateHandler}
                            controlId={id}
                            Utils={this.Utils}
                            updateTabsHandler={updateTabsSettings}
                            tabsSettings={tabsSettings}
                        />
                    }
                    <div className="add-repeater-btn">
                        <button onClick={addItemHandler}>
                            {settings.add_item}
                        </button>
                    </div>
                </div>
            </>
        );
    }
}

const defaultValueHandler = (settings, attributes, Utils) => {
    if (settings.items) {
        Object.keys(settings.items).map(key => {
            if (!attributes.hasOwnProperty(key)) {
                let defaultValue = settings.items[key]?.default;

                if (defaultValue) {
                    defaultValue = DragwybBuilder.Hooks.applyFilter(`Dragwyb/Editor/AddControl/${settings.items[key].type}.defaultValue`, defaultValue, settings, Utils);

                    attributes[key] = defaultValue;
                }
            }
        })
    }
    return attributes;
}

const intializeRepeater = ([data, settings, utils]) => {
    const defaultValue = [];
    if (!data || data.length <= 0) {
        const newItem = { _id: utils.generateId(), attributes: {} }
        newItem.attributes = defaultValueHandler(settings, newItem.attributes, utils);
        return [newItem];
    }

    data.forEach((value, index) => {
        defaultValue[index] = {};

        defaultValue[index]._id = utils.generateId();

        if (value.attributes) {
            defaultValue[index].attributes = value.attributes;
        } else {
            defaultValue[index].attributes = value;
        }

        defaultValue[index].attributes = defaultValueHandler(settings, defaultValue[index].attributes, utils);
    });

    return defaultValue;
}

jQuery(document).on('Dragwyb:editorInit', () => {
    DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/AddControl/repeater.defaultValue', (...args) => intializeRepeater(args));
    DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/DuplicateControl/repeater.duplicateValue', (...args) => intializeRepeater(args));
});



export default RepeaterControl;