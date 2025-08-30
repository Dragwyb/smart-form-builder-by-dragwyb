import RepeaterSortable from "./RepeaterSortable.jsx";

class RepeaterControl extends DragwybEditor.editor.extends.ControlBase {

    controlName() {
        return 'repeater';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        const { settings, id } = this;
        const {value: repeaterItems, tabsSettings= {}}=this.state;

        const updateHandler=(id, item)=>{
            this.updateControls(id, item)
        }

        const updateTabsSettings=(id, value)=>{
            const tabsSettings=this?.state?.tabsSettings || {};
            
            if(tabsSettings && (!tabsSettings[id] || tabsSettings[id] !== value)){
                this.setState({tabsSettings: {...tabsSettings, [id]: value}});
            }
        }

        const resetTabSettings=(id, value)=>{
            this.setState({tabsSettings: {}});
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
                        <button onClick={() => {
                            const updated = [...repeaterItems || [], { _id: this.Utils.generateId(), attributes: {} }];
                            updateHandler(id, updated);
                        }}>
                            {settings.add_item}
                        </button>
                    </div>
                </div>
            </>
        );
    }
}

const intializeRepeater = ([data, utils]) => {
    const defaultValue = [];
    if (!data || data.length <= 0) {
        return [{ _id: utils.generateId(), attributes: {} }];
    }

    data.forEach((value, index) => {
        defaultValue[index] = {};

        defaultValue[index]._id = utils.generateId();

        if (value.attributes) {
            defaultValue[index].attributes = value.attributes;
        } else {
            defaultValue[index].attributes = value;
        }
    });

    return defaultValue;
}

jQuery(document).on('Dragwyb:editorInit', () => {
    DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/AddControl/repeater.defaultValue', (...args) => intializeRepeater(args));
    DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/DuplicateControl/repeater.duplicateValue', (...args) => intializeRepeater(args));
});



export default RepeaterControl;