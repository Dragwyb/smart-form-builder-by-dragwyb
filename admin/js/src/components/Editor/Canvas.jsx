import React from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';
import * as Fields from './Fields';
import { updateFieldOrder } from '../../store/actions';
import Preview from './Preview';

const Canvas = ({ selectedField, onFieldSelect, fields, values, onChange, errors }) => {
    const dispatch = useDispatch();

    const handleDragEnd = (result) => {
        if (!result.destination) return;

        dispatch(updateFieldOrder(
            result.source.index,
            result.destination.index
        ));
    };

    return (
        <div className="dragwyb-canvas">
            <Preview fields={fields} values={values} onChange={onChange} errors={errors} />
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
                                    key={field.id}
                                    draggableId={field.id}
                                    index={index}
                                >
                                    {(provided, snapshot) => (
                                        <div
                                            ref={provided.innerRef}
                                            {...provided.draggableProps}
                                            {...provided.dragHandleProps}
                                            className={`field-wrapper ${
                                                selectedField?.id === field.id ? 'selected' : ''
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
                                                        dispatch({
                                                            type: 'DUPLICATE_FIELD',
                                                            payload: field.id
                                                        });
                                                    }}
                                                >
                                                    <span className="dashicons dashicons-admin-page"></span>
                                                </button>
                                                <button
                                                    className="delete"
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        dispatch({
                                                            type: 'DELETE_FIELD',
                                                            payload: field.id
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
                    <p>{DragwybEditor.i18n.emptyForm}</p>
                </div>
            )}
        </div>
    );
};

export default Canvas; 