import React from 'react';
import { SearchInput } from '../editor/components/Common';

const SidebarField = (props) => {
  const { type, label, icon, handleAddField, useDraggable } = props;

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
      onClick={() => handleAddField(type)}
      className={`field-type ${isDragging ? 'dragging' : ''}`}
    >
      <i className={icon}></i>
      <p>{label}</p>
    </div>
  );
}

class Fields extends DragwybEditor.editor.extends.ToolbarBase {
  constructor(args) {
    super(args);
    this.state = {
      searchField: "dasf",
    };
  }

  toolBarName() {
    return 'fields';
  }

  bind() { }

  // ✅ Use normal render method
  render() {
    const Utils = this.Utils;

    // ✅ state instead of props
    const { searchField } = this.state;

    if ((this.settingId && 'fields' !== this.settingId) || !this.shouldRender()) {
      return false;
    }

    const fieldTypes = DragwybEditor.fields;


    return (
      <>
        <div className="dragwyb-controls__search">
          <SearchInput
            value={searchField}
            onChange={this.searchFieldHandler}
            placeholder="Search fields..."
          />
        </div>
        <div className="dragwyb-controls__fields">
          {Object.entries(fieldTypes).map(([type, config]) => (
            <SidebarField
              key={type}
              handleAddField={(t) => this.handleAddField(t, Utils)}
              type={type}
              icon={config.icon}
              label={config.label}
              useDraggable={this.Utils.useDraggable}
            />
          ))}
        </div>
      </>
    );
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

    return selectedField ? selectedField : data;
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

  handleAddField = (type, Utils) => {
    Utils.AddField({ type, Utils });
  };

  // ✅ Proper event handler
  searchFieldHandler = (e) => {
    this.setState(
      { searchField: JSON.stringify(new Date()) },
      () => {
        console.log("Updated state:", this.state); // ✅ should now print correctly
      }
    );
  };
}

export default Fields;
