
import '../../sass/editorControls.scss';
import repeaterControl from './Repeater/index';

class sectionControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'section';
    }

    bind () {
        if (!this.shouldRender()) return <></>;
        
        const { settings, id, value } = this;

        let sectionCls = 'section-control';

        if(id === value){
            sectionCls += ' section-active';
        }

        return (
            <div id={id} className={sectionCls} onClick={()=>{this.updateControls(id, !(id===value))}}>
                {settings.label}
            </div>
        );
    }
}

class textControl extends DragwybEditor.editor.extends.ControlBase {
    controlName (){
        return 'text';
    }

    bind(){
        if (!this.shouldRender()) return <></>;
        
        const { settings, id, value } = this;
        
        return <>
            <label for={id}>{settings.label}</label>
            <input type={this.controlName} id={id} name={id} onChange={e => this.updateControls(id, e.target.value)} value={value}/>
        </>
    }
}

class selectControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'select';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        
        const { settings, id, value } = this;

        const options=settings.options || {}

        return (
            <>
                <label htmlFor={id}>{settings.label}</label>
                <select
                    id={id}
                    name={id}
                    value={value}
                    onChange={(e) => this.updateControls(id, e.target.value)}
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

class textareaControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'textarea';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        
        const { settings, id, value } = this;

        return (
            <>
                <label htmlFor={id}>{settings.label}</label>
                <textarea
                    id={id}
                    name={id}
                    value={value}
                    onChange={(e) => this.updateControls(id, e.target.value)}
                />
            </>
        );
    }
}

class checkboxControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'checkbox';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        
        const { settings, id, value } = this;

        return (
            <>
                <label>
                    <input
                        type="checkbox"
                        id={id}
                        name={id}
                        checked={!!value}
                        onChange={(e) => this.updateControls(id, e.target.checked)}
                    />
                    {settings.label}
                </label>
            </>
        );
    }
}

class radioControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'radio';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        
        const { settings, id, value } = this;

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
                                onChange={(e) => this.updateControls(id, e.target.value)}
                            />
                            {opt.label}
                        </label>
                    ))}
                </div>
            </>
        );
    }
}

class sliderControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'slider';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        
        const { settings, id, value } = this;

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
                    onChange={(e) => this.updateControls(id, parseFloat(e.target.value))}
                />
            </>
        );
    }
}

class numberControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'number';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id, value } = this;

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
                    onChange={(e) => this.updateControls(id, parseFloat(e.target.value))}
                />
            </>
        );
    }
}

class colorControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'color';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id, value } = this;

        return (
            <>
                <label htmlFor={id}>{settings.label}</label>
                <input
                    type="color"
                    id={id}
                    name={id}
                    value={value}
                    onChange={(e) => this.updateControls(id, e.target.value)}
                />
            </>
        );
    }
}

class tabsControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'tabs';
    }

    bind () {
        if (!this.shouldRender()) return <></>;

        const { settings, id, value } = this;

        const options = settings.tabs || [];

        return (
            <div id={id} className="tabs-control">
                <div className="tabs">
                    {Object.keys(options).map((key) => (
                        <div
                            key={key}
                            type="tab"
                            className={`tab${key === value ? ' active' : ''}`}
                            onClick={()=>this.updateControls(id, key)}
                        >
                            {options[key].label}
                        </div>
                    ))}
                </div>
            </div>
        );
    }
}

const initializeControls=()=>{
    const defaultControls={
        'text': (args)=>new textControl(args),
        'select': (args)=>new selectControl(args),
        'textarea': (args)=>new textareaControl(args),
        'checkbox': (args)=>new checkboxControl(args),
        'radio': (args)=>new radioControl(args),
        'slider': (args)=>new sliderControl(args),
        'number': (args)=>new numberControl(args),
        'color': (args)=>new colorControl(args),
        'tabs': (args)=>new tabsControl(args),
        'section': (args)=>new sectionControl(args),
        'repeater': (args) => new repeaterControl(args),
    }

    Object.keys(defaultControls).map(key => DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/ControlRender/'+key,(...args)=>{return defaultControls[key](args)}))

}

jQuery(document).on('Dragwyb:editorInit', () => {
    initializeControls();
});