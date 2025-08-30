import React, {useState} from 'react';
import { SearchInput } from '../editor/components/Common';

const SidebarField = (props) => {
  const { type, label, icon, addFieldHandler, useDraggable } = props;

  const { attributes, listeners, setNodeRef, isDragging } = useDraggable({
    id: `sidebar-${type}`,
    data: {
      fromSidebar: true,
      type,
    },
  });

  return (
    <div
      ref={setNodeRef}
      {...listeners}
      {...attributes}
      onClick={() => addFieldHandler(type)}
      className={`field-type ${isDragging ? 'dragging' : ''}`}
    >
      <i className={icon}></i>
      <p>{label}</p>
    </div>
  );
}

const Sidebar = ({fieldTypes, Utils, addFieldHandler}) => {
  const [renderFields, setRenderFields]=useState(fieldTypes);

  const searchFieldHandler=(value)=>{
    if(value === ''){
      setRenderFields(fieldTypes);
      return;
    }
    const searchFields={};

    Object.keys(fieldTypes).forEach(key => {
      if(key.startsWith(value)){
        searchFields[key]=fieldTypes[key];
      }else if(fieldTypes[key] && fieldTypes[key].keywords && fieldTypes[key].keywords.length > 0){
        const keywords=fieldTypes[key].keywords;
        const keywordsExist=keywords.filter(key=>key.startsWith(value));

        if(keywordsExist && keywordsExist.length > 0){
          searchFields[key]=fieldTypes[key];
        }
      }
    });
    setRenderFields(searchFields);
  }

  return <>
    <div className="dragwyb-controls__search">
      <SearchInput
        onChange={searchFieldHandler}
        placeholder="Search fields..."
      />
    </div>
    <div className="dragwyb-controls__fields">
      {Object.entries(renderFields).map(([type, config]) => (
        <SidebarField
          key={type}
          addFieldHandler={(t) => addFieldHandler(t, Utils)}
          type={type}
          icon={config.icon}
          label={config.label}
          useDraggable={Utils.useDraggable}
        />
      ))}
    </div>
  </>
}

class Fields extends DragwybEditor.editor.extends.ToolbarBase {
  constructor(args) {
    super(args);
  }

  toolBarName() {
    return 'fields';
  }

  bind() { }

  // ✅ Use normal render method
  render() {
    const Utils = this.Utils;

    if ((this.settingId && 'fields' !== this.settingId) || !this.shouldRender()) {
      return false;
    }

    const fieldTypes = DragwybEditor.fields;

    return <Sidebar fieldTypes={fieldTypes} Utils={Utils} addFieldHandler={this.addFieldHandler}/>;
  }

  getToolbarSettings() {
    const key = this.settingId;
    const data = this.toolbarData;
    const setting = this.settings;

    if (key === 'fields' || !key) return false;
    const selectedField = this.getSelectedField(data, key);

    if (setting && selectedField.type && setting[selectedField.type]) {
      return setting[selectedField.type];
    }

    return setting;
  }

  getToolbarValue() {
    const key = this.settingId;
    const data = this.toolbarData;


    if (key === 'fields' || !key) return false;
    const selectedField = this.getSelectedField(data, key);

    return selectedField && selectedField.attributes ? selectedField.attributes : data;
  }

  getSelectedField(data, key) {
    let value = null;

    Object.values(data).forEach(field => {
      if (field._id === key) {
        value = field;
      }
    })

    return value;
  }

  updateToolbarHandler = (key, value) => {

    if (this.toolbarData) {
      let valueUpdate = false;
      this.toolbarData.map(field => {
        if (field._id === this.settingId && field.attributes) {
          valueUpdate = true;
          field.attributes[key] = value;
        }
      })

      if (valueUpdate) {
        this.updateToolbar();
      }
    }
  }

  addFieldHandler = (type, Utils) => {
    Utils.AddField({ type, Utils });
  };
}

export default Fields;
