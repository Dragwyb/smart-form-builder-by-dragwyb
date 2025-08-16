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
            onClick={()=>{handleAddField(type)}}
            className={`field-type ${isDragging ? 'dragging' : ''}`}
        >
            <i className={icon}></i>
            <p>{label}</p>
        </div>
    );
};

const Controls = () => {
    
    DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/Toolbar_Render/fields', (html, Utils)=>{return renderFields({html, Utils})});

    const renderFields = ({html, Utils}) =>{
       const fieldTypes = DragwybEditor.fieldTypes;
        const [searchField, setSearchField] = useState('');
        // const dispatch = useDispatch();
    
        const handleAddField = (type) => {
            // const field=CreateNewField(type, dispatch, Utils)
            Utils.AddField({ type, Utils });
            // onFieldSelect(field);
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