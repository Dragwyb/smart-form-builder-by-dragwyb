import React from 'react';
import { useDispatch, useSelector, useStore } from 'react-redux';
import {
    DndContext,
    closestCenter,
    PointerSensor,
    useSensor,
    useSensors,
    DragOverlay
} from '@dnd-kit/core';
import {
    arrayMove,
    SortableContext,
    useSortable,
    verticalListSortingStrategy
} from '@dnd-kit/sortable';
import {restrictToParentElement, createSnapModifier} from '@dnd-kit/modifiers';

import { CSS } from '@dnd-kit/utilities';
import * as Fields from './Fields';
import { updateFieldOrder, duplicateField } from '../../store/actions';
import Helper from '../Utils';

const SortableItem = ({ field, selectedField, onFieldSelect, values, onChange, errors, index, onDuplicate, onDelete }) => {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
    } = useSortable({ id: field._id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition
    };

    return (
        <div
            ref={setNodeRef}
            style={style}
            {...attributes}
            {...listeners}
            className={`field-wrapper ${selectedField?._id === field._id ? 'selected' : ''}`}
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
    );
};

const Canvas = ({ selectedField, onFieldSelect, fields, values, onChange, errors }) => {
    const dispatch = useDispatch();

    const store = useStore();
    const state = store.getState();

    const Utils = Helper(state, dispatch);

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 5,
            },
        })
    );

    const handleDragEnd = (event) => {
        const { active, over } = event;

        if (active.id !== over?.id) {
            const oldIndex = fields.findIndex(f => f._id === active.id);
            const newIndex = fields.findIndex(f => f._id === over.id);
            dispatch(updateFieldOrder(oldIndex, newIndex));
        }
    };

    const handleDuplicateField = (field, index) => {
        const deepClone = JSON.parse(JSON.stringify(field));
        const id = Utils.generateId();
        deepClone._id = id;

        const fieldControls = DragwybEditor.fieldTypes[deepClone.type]?.controls || {};

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
        onFieldSelect(deepClone);
    };

    const handleDeleteField = (id) => {
        onFieldSelect(null);
        dispatch({ type: 'DELETE_FIELD', payload: id });
    };

    const gridSize = 20; // pixels
    const snapToGridModifier = createSnapModifier(gridSize);

    return (
        <div className="dragwyb-canvas">
            <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd} modifiers={[restrictToParentElement, snapToGridModifier]}>
                <SortableContext items={fields.map(f => f._id)} strategy={verticalListSortingStrategy}>
                    <div className="dragwyb-canvas__fields">
                        {fields.map((field, index) => (
                            <SortableItem
                                key={field._id}
                                field={field}
                                index={index}
                                selectedField={selectedField}
                                onFieldSelect={onFieldSelect}
                                values={values}
                                onChange={onChange}
                                errors={errors}
                                onDuplicate={(field)=>handleDuplicateField(field, index)}
                                onDelete={handleDeleteField}
                            />
                        ))}
                    </div>
                </SortableContext>
            </DndContext>
            {fields.length === 0 && (
                <div className="dragwyb-canvas__empty">
                    <p>{DragwybBuilder.i18n.emptyForm}</p>
                </div>
            )}
        </div>
    );
};

export default Canvas;
