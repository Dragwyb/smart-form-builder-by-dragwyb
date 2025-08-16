import React, { useState, useRef } from 'react';
import { useDispatch, useStore } from 'react-redux';
import { addField, updateFieldValues } from '../../store/actions';
import { useSelector } from 'react-redux';
import {Utils as Helper, AddField as CreateNewField} from '../Utils';
import { SearchInput } from '../../components/Common'
import { useDraggable, DragOverlay } from '@dnd-kit/core';

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

const Controls = ({ onFieldSelect, Utils }) => {
    const fieldTypes = DragwybEditor.fieldTypes;
    const [searchField, setSearchField] = useState('');
    const dispatch = useDispatch();

    const handleAddField = (type) => {
        const field=CreateNewField(type, dispatch, Utils)
        onFieldSelect(field);
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
};

export default Controls; 