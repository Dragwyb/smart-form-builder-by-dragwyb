
import '../../sass/editorControls.scss';

class sectionControl extends DragwybEditor.ControlBase {
    controlName() {
        return 'section';
    }

    bind ({id, settings, value}) {
        if (!this.shouldRender()) return <></>;
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

class textControl extends DragwybEditor.ControlBase {
    controlName (){
        return 'text';
    }

    bind(){
        const settings=this.settings;
        const id=this.id;
        const events=this.events;
        const value=this.value;

        if (!this.shouldRender()) return <></>;
        
        return <>
            <label for={id}>{settings.label}</label>
            <input type={this.controlName} id={id} name={id} onChange={e => this.updateControls(id, e.target.value)} value={value}/>
        </>
    }
}

class selectControl extends DragwybEditor.ControlBase {
    controlName() {
        return 'select';
    }

    bind() {
        const { settings, id, events, value } = this;

        if (!this.shouldRender()) return <></>;

        return (
            <>
                <label htmlFor={id}>{settings.label}</label>
                <select
                    id={id}
                    name={id}
                    value={value}
                    onChange={(e) => this.updateControls(id, e.target.value)}
                >
                    {(settings.options || []).map((opt) => (
                        <option key={opt.value} value={opt.value}>
                            {opt.label}
                        </option>
                    ))}
                </select>
            </>
        );
    }
}

class textareaControl extends DragwybEditor.ControlBase {
    controlName() {
        return 'textarea';
    }

    bind() {
        const { settings, id, events, value } = this;

        if (!this.shouldRender()) return <></>;

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

class checkboxControl extends DragwybEditor.ControlBase {
    controlName() {
        return 'checkbox';
    }

    bind() {
        const { settings, id, events, value } = this;

        if (!this.shouldRender()) return <></>;

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

class radioControl extends DragwybEditor.ControlBase {
    controlName() {
        return 'radio';
    }

    bind() {
        const { settings, id, events, value } = this;
        if (!this.shouldRender()) return <></>;

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

class sliderControl extends DragwybEditor.ControlBase {
    controlName() {
        return 'slider';
    }

    bind() {
        const { settings, id, events, value } = this;
        
        if (!this.shouldRender()) return <></>;

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

class numberControl extends DragwybEditor.ControlBase {
    controlName() {
        return 'number';
    }

    bind() {
        const { settings, id, events, value } = this;
        if (!this.shouldRender()) return <></>;

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

class colorControl extends DragwybEditor.ControlBase {
    controlName() {
        return 'color';
    }

    bind() {
        const { settings, id, events, value } = this;
        if (!this.shouldRender()) return <></>;

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

class tabsControl extends DragwybEditor.ControlBase {
    controlName() {
        return 'tabs';
    }

    bind ({id, settings, value}) {
        if (!this.shouldRender()) return <></>;

        const options = settings.tabs || [];

        return (
            <div id={id} className="tabs-control">
                <div className="tab-buttons">
                    {Object.keys(options).map((key) => (
                        <button
                            key={key}
                            type="button"
                            className={key === value ? 'active' : ''}
                            onClick={()=>this.updateControls(id, key)}
                        >
                            {options[key].label}
                        </button>
                    ))}
                </div>
            </div>
        );
    }
}


jQuery(document).on('Dragwyb:editorInit', () => {   
    const defaultControls={
        'text': ()=>new textControl(),
        'select': ()=>new selectControl(),
        'textarea': ()=>new textareaControl(),
        'checkbox': ()=>new checkboxControl(),
        'radio': ()=>new radioControl(),
        'slider': ()=>new sliderControl(),
        'number': ()=>new numberControl(),
        'color': ()=>new colorControl(),
        'tabs': ()=>new tabsControl(),
        'section': ()=>new sectionControl(),
    }
    const registerControls=()=>{
        Object.keys(defaultControls).map(key=>{return defaultControls[key]()});
    }

    DragwybBuilder.Hooks.addAction('Dragwyb/Editor/ControlBase',registerControls);
});