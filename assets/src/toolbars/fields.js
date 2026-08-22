import React, { useState } from 'react';
import { SearchInput } from '../editor/components/Common';
import { __ } from '@wordpress/i18n';
import Scrollbar from '../editor/components/Scrollbar';
import { TiArrowSortedDown } from "react-icons/ti";

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
      className={`field-type${isDragging ? ' dragging' : ''}`}
    >
      <DragwybEditor.editor.IconsManager.Render icon={icon} />
      <p>{label}</p>
    </div>
  );
}

const Sidebar = ({ Utils, addFieldHandler }) => {
  const fieldTypes = DragwybEditor.fields.fields;
  const categories = DragwybEditor.fields.categories;
  const defaultActiveCategory = [];

  Object.keys(categories).forEach(key => {
    defaultActiveCategory[key] = true;
  })

  const [renderFields, setRenderFields] = useState(fieldTypes);
  const [isSearch, setIsSearch] = useState(false);
  const [activeCategory, setActiveCategory] = useState(defaultActiveCategory);

  const searchFieldHandler = (value) => {
    if (value === '') {
      if (isSearch !== false) {
        setIsSearch(prev => !prev);
      }
      setRenderFields(fieldTypes);
      return;
    }

    if (isSearch !== true) {
      setIsSearch(prev => !prev);
    }

    const searchFields = {};

    Object.keys(fieldTypes).forEach(key => {
      if (key.startsWith(value)) {
        searchFields[key] = fieldTypes[key];
      } else if (fieldTypes[key] && fieldTypes[key].keywords && fieldTypes[key].keywords.length > 0) {
        const keywords = fieldTypes[key].keywords;
        const keywordsExist = keywords.filter(key => key.startsWith(value));

        if (keywordsExist && keywordsExist.length > 0) {
          searchFields[key] = fieldTypes[key];
        }
      }
    });
    setRenderFields(searchFields);
  }

  const toggleCategory = (key) => {
    setActiveCategory(prev => ({
      ...prev,
      [key]: !prev[key]
    }));
  }

  return <>
    <div className="dragwyb-controls__search">
      <SearchInput
        onChange={searchFieldHandler}
        placeholder="Search fields..."
        id="dragwyb-controls__search"
      />
    </div>
    <div className="dragwyb-controls__fields">
      <Scrollbar>
        {isSearch ?
          (Object.entries(renderFields).map(([type, config]) => (
            <SidebarField
              key={'search' + type}
              addFieldHandler={(t) => addFieldHandler(t, Utils)}
              type={type}
              icon={config.icon}
              label={config.label}
              useDraggable={Utils.useDraggable}
            />
          )))
          :
          (Object.entries(categories).map(([key, { name, icon, fields }]) => (
            <React.Fragment key={key}>
              <div className={`dagwyb-widget-category-section`} id={`category-section-${key}`} onClick={() => { toggleCategory(key) }}>
                <DragwybEditor.editor.IconsManager.Render icon={icon} />
                <p className="dragwyb-section__title">{name}</p>
                <span className="dragwyb-section__icon">
                  <TiArrowSortedDown size={20} />
                </span>
              </div>
              {
                activeCategory[key] &&
                fields.map(field => {
                  return <SidebarField
                    key={'active' + field}
                    addFieldHandler={(t) => addFieldHandler(t, Utils)}
                    type={field}
                    icon={renderFields[field].icon}
                    label={renderFields[field].label}
                    useDraggable={Utils.useDraggable}
                  />
                })
              }
            </React.Fragment>
          )))
        }
      </Scrollbar>
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

    if (!this.shouldRender()) return;

    return <div className="dragwyb-controls" id={`dragwyb-controls__${this.toolBarName}`}> <Sidebar Utils={Utils} addFieldHandler={this.addFieldHandler} /></div>;
  }

  getToolbarSettings() {
    const key = this.settingId;
    const data = this.toolbarData;
    const setting = this.settings;
    let selectedFieldSettings = setting;
    selectedFieldSettings.panelHeading = setting.label ?? this.toolBarName;

    if (key === 'fields' || !key) return false;
    const selectedField = this.getSelectedField(data, key);

    if (selectedField && setting.fields && selectedField.type && setting.fields[selectedField.type]) {
      selectedFieldSettings = setting.fields[selectedField.type];
      selectedFieldSettings.panelHeading = <>{__('Field Settings', 'smart-form-builder-by-dragwyb')} <span>{selectedFieldSettings.label}</span></>;
    }

    selectedFieldSettings.id = key;
    return selectedFieldSettings;
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

      if (this.toolbarData && this.toolbarData.hasOwnProperty(this.settingId)) {
        valueUpdate = true;
        this.toolbarData[this.settingId].attributes[key] = value;

        if (value === undefined) {
          delete this.toolbarData[this.settingId].attributes[key];
        } else if (this.toolbarData[this.settingId].type && typeof DragwybEditor.fields.fields[this.toolbarData[this.settingId].type]?.controls?.[key]?.default === 'object' && this.Utils.compareTwoObjects({ obj1: DragwybEditor.fields.fields[this.toolbarData[this.settingId].type].controls[key].default, obj2: value })) {
          delete this.toolbarData[this.settingId].attributes[key];
        } else if (DragwybEditor.fields.fields[this.toolbarData[this.settingId].type]?.controls?.[key]?.default === value) {
          delete this.toolbarData[this.settingId].attributes[key];
        }

        this.toolbarData[this.settingId].attributes = { ...this.toolbarData[this.settingId].attributes };
      }

      if (valueUpdate) {
        this.Utils.editorFormReady();
        this.updateToolbar();
      }
    }
  }

  addFieldHandler = (type, Utils) => {
    Utils.AddField({ type, Utils });
  };
}

export default Fields;
