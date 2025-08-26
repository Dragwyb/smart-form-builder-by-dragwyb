import RepeaterSortable from "./RepeaterSortable.jsx";

class repeaterControl extends DragwybEditor.editor.extends.ControlBase {

    controlName() {
        return 'repeater';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        const { settings, id, value: repeaterItems } = this;

        return (
            <>
                <label>{settings.label}</label>
                <div className="dragwyb-repeater-field">
                    {repeaterItems && repeaterItems.length > 0 &&
                        <RepeaterSortable
                            items={repeaterItems}
                            settings={settings}
                            updateControls={this.updateControls.bind(this)}
                            controlId={id}
                            utils={this.Utils}
                        />
                    }
                    <div className="add-repeater-btn">
                        <button onClick={() => {
                            const updated = [...repeaterItems || [], { _id: this.Utils.generateId(), attributes: {} }];
                            this.updateControls(id, updated);
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



export default repeaterControl;