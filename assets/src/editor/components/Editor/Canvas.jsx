import React from 'react';
import { useDispatch } from 'react-redux';
import {
    useDroppable,
    useDraggable
} from '@dnd-kit/core';
import {
    SortableContext,
    useSortable,
    verticalListSortingStrategy
} from '@dnd-kit/sortable';

import { CSS } from '@dnd-kit/utilities';
import * as Fields from './Fields';
import { duplicateField } from '../../store/actions';
import { Button } from '../Common';
import { __ } from '@wordpress/i18n';

const RenderItem = ({ field, values, index, dropIndex, dropIndicatorPosition, onChange, onFieldSelect, onDuplicate, onDelete, errors, selectedField }) => {
    const { setNodeRef: dropRef, isOver } = useDroppable(
        {
            id: `canvas-drop-${field._id}`,
            data: {
                canvasDrop: true,
                currentIndex: index
            }
        }
    );

    const { attributes, listeners, setNodeRef: dragRef, isDragging } = useDraggable({
        id: `canvas-drag-${field._id}`,
        data: {
            canvasDrag: true,
            currentIndex: index,
        },
    });

    const setNodeRef = (Node) => {
        if (!Node) return;
        dropRef(Node)
        dragRef(Node)
    };

    let wrapperClass = 'field-wrapper';

    if (selectedField?._id === field._id) {
        wrapperClass += ' selected';
    }

    if (isDragging) {
        wrapperClass += ' dragwyb-start-drag'
    }

    return <>
        {dropIndex === index && 'bottom' !== dropIndicatorPosition && <span className='dragwyb-editor-indicator'></span>}
        <div
            ref={setNodeRef}
            className={wrapperClass}
            onClick={() => onFieldSelect(field._id)}
            {...listeners}
            {...attributes}
            id={`field-wrapp-${field._id}`}
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
                        onDuplicate(field);
                    }}
                >
                    <span className="dashicons dashicons-admin-page"></span>
                </button>
                <button
                    className="delete"
                    onClick={(e) => {
                        e.stopPropagation();
                        onDelete(field._id);
                    }}
                >
                    <span className="dashicons dashicons-trash"></span>
                </button>
            </div>
        </div>
        {dropIndex === index && 'bottom' === dropIndicatorPosition && <span className='dragwyb-editor-indicator'></span>}
    </>
}

const Canvas = ({ selectedField, onFieldSelect, fields, values, onChange, errors, Utils, dropIndex, dropIndicatorPosition, activeTab, setActiveTab }) => {

    const dispatch = useDispatch();

    const { setNodeRef, isOver } = useDroppable(
        {
            id: `canvas-add-field`,
            data: {
                addField: true,
                currentIndex: 0
            }
        }
    );

    const handleDuplicateField = (field, index) => {
        const deepClone = JSON.parse(JSON.stringify(field));
        const id = Utils.generateId();
        deepClone._id = id;

        const fieldControls = DragwybEditor.fields[deepClone.type]?.controls || {};

        Object.keys(deepClone.attributes || {}).forEach(id => {
            if (!['tabs', 'tab', 'section'].includes(fieldControls[id]?.type)) {
                let value = deepClone.attributes[id];
                value = DragwybBuilder.Hooks.applyFilter(
                    `Dragwyb/Editor/DuplicateControl/${fieldControls[id].type}.duplicateValue`,
                    value,
                    Utils
                );
                deepClone.attributes[id] = value;
            }
        });

        dispatch(duplicateField(deepClone, index + 1, dispatch));
        onFieldSelect(deepClone._id);
    };

    const handleDeleteField = (id) => {
        onFieldSelect(null);
        dispatch({ type: 'DELETE_FIELD', payload: id });
    };

    let canvasCls = "dragwyb-canvas";

    if (!fields || fields.length === 0) {
        canvasCls += " canvas-empty";
    }

    return (
        <div className={canvasCls}>
            {fields && fields.length > 0 && fields.map((field, index) =>
                <RenderItem
                    field={field}
                    selectedField={selectedField}
                    values={values}
                    onChange={onChange}
                    onFieldSelect={onFieldSelect}
                    onDuplicate={(field) => handleDuplicateField(field, index)}
                    onDelete={handleDeleteField}
                    errors={errors}
                    index={index}
                    dropIndex={dropIndex}
                    dropIndicatorPosition={dropIndicatorPosition}
                />
            )}
            {(!fields || fields.length === 0) && (
                <div className="dragwyb-canvas__empty" ref={setNodeRef}>
                    <div className={`dragwyb-canvas__empty-wrapper ${isOver ? ' drag-active': ''}`}>
                        {activeTab !== 'fields' ?
                            <>
                                <Button onClick={() => setActiveTab('fields')} className='add-field'>
                                    <i className='fas fa-plus' />
                                    {__('Add Field.', 'dragwyb-form-builder')}
                                </Button>
                                <p>{__('Click on a Add Field to add it to your form.', 'dragwyb-form-builder')}</p>
                            </> :
                            <p>{DragwybBuilder.i18n.emptyForm}</p>
                        }
                    </div>
                </div>
            )}
        </div>
    );
};

export default Canvas;
