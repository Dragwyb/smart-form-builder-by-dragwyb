import React from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';
import * as Fields from './Fields';
import { updateFieldOrder, duplicateField } from '../../store/actions';
import Helper from '../Utils';

const Canvas = ({ selectedField, onFieldSelect, fields, values, onChange, errors }) => {
    const dispatch = useDispatch();
    const state=useSelector(state => state);
    const Utils=Helper(state, dispatch);

    const handleDragEnd = (result) => {
        if (!result.destination) return;

        dispatch(updateFieldOrder(
            result.source.index,
            result.destination.index
        ));
    };

    const handleDuplicateField = (field) => {
        const deepClone={...field}
        const id = `${Date.now()}`;
        dispatch(duplicateField(id, deepClone._id, dispatch));
        
        deepClone._id = id;
        
        const fieldControls = DragwybEditor.fieldTypes[deepClone.type].controls;

        if (deepClone.settings && Object.keys(deepClone.settings).length > 0) {
            Object.keys(deepClone.settings).map(id => {
                if (!['tabs', 'tab', 'section'].includes(fieldControls[id].type)) {
                    let duplicateValue = deepClone.settings[id];
                    duplicateValue = DragwybBuilder.Hooks.applyFilter(`Dragwyb/Editor/DuplicateControl/${fieldControls[id].type}.duplicateValue`, duplicateValue, Utils);
                    deepClone.settings[id] = duplicateValue;
                }
            })
        }

        onFieldSelect(deepClone);
    };

    return (
        <div className="dragwyb-canvas">
            <DragDropContext onDragEnd={handleDragEnd}>
                <Droppable droppableId="form-fields">
                    {(provided) => (
                        <div
                            ref={provided.innerRef}
                            {...provided.droppableProps}
                            className="dragwyb-canvas__fields"
                        >
                            {fields.map((field, index) => (
                                <Draggable
                                    key={field._id}
                                    draggableId={field._id}
                                    index={index}
                                >
                                    {(provided, snapshot) => (
                                        <div
                                            ref={provided.innerRef}
                                            {...provided.draggableProps}
                                            {...provided.dragHandleProps}
                                            className={`field-wrapper ${
                                                selectedField?._id === field._id ? 'selected' : ''
                                            } ${snapshot.isDragging ? 'dragging' : ''}`}
                                            onClick={() => onFieldSelect(field)}
                                        >
                                            <Fields.Preview
                                                fields={[field]}
                                                values={values}
                                                onChange={onChange}
                                                errors={errors}
                                            />
                                            <div className="field-actions">
                                                <button
                                                    className="duplicate"
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        handleDuplicateField(field);
                                                    }}
                                                >
                                                    <span className="dashicons dashicons-admin-page"></span>
                                                </button>
                                                <button
                                                    className="delete"
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        onFieldSelect(null)
                                                        dispatch({
                                                            type: 'DELETE_FIELD',
                                                            payload: field._id
                                                        });
                                                    }}
                                                >
                                                    <span className="dashicons dashicons-trash"></span>
                                                </button>
                                            </div>
                                        </div>
                                    )}
                                </Draggable>
                            ))}
                            {provided.placeholder}
                        </div>
                    )}
                </Droppable>
            </DragDropContext>
            {fields.length === 0 && (
                <div className="dragwyb-canvas__empty">
                    <p>{DragwybBuilder.i18n.emptyForm}</p>
                </div>
            )}
        </div>
    );
};

export default Canvas; 