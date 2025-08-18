import React, { useState } from 'react';
import { SearchInput } from '../../components/Common'
import { useDraggable } from '@dnd-kit/core';

const SidebarField = ({ type, label, icon, handleAddField }) => {
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
            onClick={() => { handleAddField(type) }}
            className={`field-type ${isDragging ? 'dragging' : ''}`}
        >
            <i className={icon}></i>
            <p>{label}</p>
        </div>
    );
};

const Controls = () => {

    DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/Sidebar/Render/fields', (html, toolbarData, Utils) => { return <RenderFields Utils={Utils} toolbarData={toolbarData} />; });
    DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/Sidebar/Values/fields', (data, key) => { return fieldValues({ data, key }) });
    DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/Sidebar/Settings/fields', (data, key, setting) => { return fieldSettings({ setting, data, key }) });

    const getSelectedField = (data, key) => {
        let value = null;

        Object.values(data).forEach(field => {
            if (field._id === key) {
                value = field;
            }
        })

        return value;
    }

    const fieldValues = ({ data, key }) => { 
        const selectedField=getSelectedField(data, key);

        return selectedField ? selectedField : data;
    }
    
    const fieldSettings = ({ setting, data, key }) => { 
        const selectedField=getSelectedField(data, key);

        if(setting && selectedField.type && setting[selectedField.type] ){
            return setting[selectedField.type];
        }

        return setting;
    }

    const RenderFields = ({ html, Utils, toolbarData }) => {

        const [searchField, setSearchField] = useState('');

        if (toolbarData && toolbarData.length > 0) {
            return false;
        }

        const fieldTypes = DragwybEditor.fields;

        const handleAddField = (type) => {
            Utils.AddField({ type, Utils });
        };

        const searchFieldHandler = (value) => {
            setSearchField(value);
        }

        return (
            <div className="dragwyb-controls">
                <div className="dragwyb-controls__search">
                    <SearchInput
                        value={searchField}
                        onChange={searchFieldHandler}
                        placeholder='Search fields...'
                    />
                </div>
                <div className="dragwyb-controls__fields">
                    {Object.entries(fieldTypes).map(([type, config]) => (
                        <SidebarField
                            handleAddField={handleAddField}
                            type={type}
                            icon={config.icon}
                            label={config.label}
                        />
                    ))}
                </div>
            </div>
        );
    }
};

export default Controls; 