
import '../../sass/editorControls.scss';
import RepeaterControl from './Repeater/index';

class SectionControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'section';
    }

    bind () {
        if (!this.shouldRender()) return <></>;
        
        const { settings, id } = this;
        const {value}=this.state;

        let sectionCls = 'section-control';

        if(id === value){
            sectionCls += ' section-active';
        }

        return (
            <div id={id} className={sectionCls} onClick={()=>{this.updateControlHandler(id, !(id===value))}}>
                {settings.label}
            </div>
        );
    }

    updateControlHandler(key, value){
        this.setState({value: value ? key : ''})
        this.updateControls(key, value);
    }
}

class TextControl extends DragwybEditor.editor.extends.ControlBase {
    controlName (){
        return 'text';
    }

    bind(){
        if (!this.shouldRender()) return <></>;
        
        const { settings, id } = this;
        const {value = settings.default}=this.state;

        return <>
            <label for={id}>{settings.label}</label>
            <input type={this.controlName} id={id} name={id} onChange={e => this.updateControlHandler(id, e.target.value)} value={value}/>
        </>
    }
}

class SelectControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'select';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        
        const { settings, id } = this;
        const {value}=this.state;

        const options=settings.options || {}

        return (
            <>
                <label htmlFor={id}>{settings.label}</label>
                <select
                    id={id}
                    name={id}
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, e.target.value)}
                >
                    {(Object.keys(options)).map((key) => (
                        <option key={key} value={key}>
                            {options[key]}
                        </option>
                    ))}
                </select>
            </>
        );
    }
}

class TextareaControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'textarea';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        
        const { settings, id } = this;
        const {value}=this.state;

        return (
            <>
                <label htmlFor={id}>{settings.label}</label>
                <textarea
                    id={id}
                    name={id}
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, e.target.value)}
                />
            </>
        );
    }
}

class CheckboxControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'checkbox';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        
        const { settings, id } = this;
        const {value}=this.state;

        return (
            <>
                <label>
                    <input
                        type="checkbox"
                        id={id}
                        name={id}
                        checked={!!value}
                        onChange={(e) => this.updateControlHandler(id, e.target.checked)}
                    />
                    {settings.label}
                </label>
            </>
        );
    }
}

class RadioControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'radio';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        
        const { settings, id } = this;
        const {value}=this.state;

        return (
            <>
                <label>{settings.label}</label>
                <div id={id}>
                    {(settings.options || []).map((opt) => (
                        <label key={opt.value}>
                            <input
                                type="radio"
                                name={id}
                                value={opt.value}
                                checked={value === opt.value}
                                onChange={(e) => this.updateControlHandler(id, e.target.value)}
                            />
                            {opt.label}
                        </label>
                    ))}
                </div>
            </>
        );
    }
}

class SliderControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'slider';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        
        const { settings, id } = this;
        const {value}=this.state;

        const min = settings.min ?? 0;
        const max = settings.max ?? 100;
        const step = settings.step ?? 1;

        return (
            <>
                <label htmlFor={id}>{settings.label}: {value}</label>
                <input
                    type="range"
                    id={id}
                    name={id}
                    min={min}
                    max={max}
                    step={step}
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, parseFloat(e.target.value))}
                />
            </>
        );
    }
}

class NumberControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'number';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const {value}=this.state;

        const min = settings.min ?? 0;
        const max = settings.max ?? 100;
        const step = settings.step ?? 1;

        return (
            <>
                <label htmlFor={id}>{settings.label}</label>
                <input
                    type="number"
                    id={id}
                    name={id}
                    min={min}
                    max={max}
                    step={step}
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, parseFloat(e.target.value))}
                />
            </>
        );
    }
}

class ColorControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'color';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const {value}=this.state;

        return (
            <>
                <label htmlFor={id}>{settings.label}</label>
                <input
                    type="color"
                    id={id}
                    name={id}
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, e.target.value)}
                />
            </>
        );
    }
}

class TabsControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'tabs';
    }

    bind () {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const {value}=this.state;

        const options = settings.tabs || [];

        if (!value && value === '' && Object.keys(options).length > 0) {
            this.updateControlHandler(id, Object.keys(options)[0]);
        }
    
        return (
            <div id={id} className="tabs-control">
                <div className="tabs">
                    {Object.keys(options).map((key) => (
                        <div
                            key={key}
                            type="tab"
                            className={`tab${key === value ? ' active' : ''}`}
                            onClick={()=>this.updateControlHandler(id, key)}
                        >
                            {options[key].label}
                        </div>
                    ))}
                </div>
            </div>
        );
    }
}

class PopoverToggle extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return "popover-toggle";
    }

    bind() {
        if (!this.shouldRender()) return null;

        const { settings, id } = this;
        const { value } = this.state;
        const isActive = id === value;

        const clickHandler = (e)=>{
            const ele=e.target;
            const popoverWrp=jQuery(ele).closest('.setting-row').next('.dragwyb-popover');
        
            popoverWrp.toggle();
        }

        return (
            <button
                id={id}
                type="button"
                className={`dragwyb-popover-toggle ${isActive ? "is-active" : ""}`}
                aria-pressed={isActive}
                onClick={() => this.updateControlHandler(id, !isActive)}
            >
                {settings.label && (
                    <span className="dragwyb-popover-toggle__label">{settings.label}</span>
                )}
                {settings.icon && (
                    <i className={`dragwyb-popover-toggle__icon ${settings.icon}`} aria-hidden="true" onClick={clickHandler}/>
                )}
            </button>
        );
    }
}

const initializeControls=()=>{
    const defaultControls={
        'text': TextControl,
        'select': SelectControl,
        'textarea': TextareaControl,
        'checkbox': CheckboxControl,
        'radio': RadioControl,
        'slider': SliderControl,
        'number': NumberControl,
        'color': ColorControl,
        'tabs': TabsControl,
        'section': SectionControl,
        'repeater': RepeaterControl,
        'popover-toggle': PopoverToggle
    }

    Object.keys(defaultControls).map(key => DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/ControlRender/'+key,()=>{return defaultControls[key]}))

}

jQuery(document).on('Dragwyb:editorInit', () => {
    initializeControls();
});