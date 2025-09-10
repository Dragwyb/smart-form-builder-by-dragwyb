import React from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useDraggable, useDroppable } from '../components/Common';
import * as Fields from './Fields';
import { duplicateField } from '../store/actions';
import { Button } from '../components/Common';
import { __ } from '@wordpress/i18n';

const RenderItem = ({ field, values, index, dropIndex, dropIndicatorPosition, onFieldSelect, onDuplicate, onDelete, errors }) => {
    const selectedField = useSelector(state => state.selectedSettingId);
    
    const { setNodeRef: dropRef, isOver } = useDroppable(
        {
            id: `canvas-drop-field-${field._id}`,
            data: {
                canvasDrop: true,
                currentIndex: index
            }
        }
    );

    const { attributes, listeners, setNodeRef: dragRef, isDragging } = useDraggable({
        id: `canvas-drag-field-${field._id}`,
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

    if (selectedField && selectedField === field._id) {
        wrapperClass += ' selected';
    }

    if (isDragging) {
        wrapperClass += ' dragwyb-start-drag'
    }

    const onFieldSelectHandler=(id)=>{
        if(selectedField !== id){
            onFieldSelect({id})
        }
    }

    return <>
        {dropIndex === index && 'bottom' !== dropIndicatorPosition && <span className='dragwyb-editor-indicator'></span>}
        <div
            ref={setNodeRef}
            className={wrapperClass}
            onClick={() => onFieldSelectHandler(field._id)}
            {...listeners}
            {...attributes}
            id={`field-wrapp-${field._id}`}
        >
            <Fields.Preview
                fields={[field]}
                values={values}
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
        {(dropIndex === index && 'bottom' === dropIndicatorPosition) && <span className='dragwyb-editor-indicator'></span>}
    </>
}

const EmptyCanvas = ({ setActiveTab, isOver }) => {
    const activeTab = useSelector(state => state.activeToolbar);

    let emptyMessage = __('Begin creating your form by dragging fields from the sidebar, or simply click a field to add it.', 'dragwyb-form-builder');

    if (activeTab !== 'fields') {
        emptyMessage = __('Click “Add Field” to open the field tab and start building your form.', 'dragwyb-form-builder');
    }

    if (isOver) {
        emptyMessage = __('Release the mouse or lift your finger to drop the field into your form.', 'dragwyb-form-builder');
    }

    return <div className="dragwyb-canvas__empty">
        <div className={`dragwyb-canvas__empty-wrapper ${isOver ? ' drag-active' : ''}`}>
            {activeTab !== 'fields' &&
                <Button onClick={() => setActiveTab('fields')} className='add-field'>
                    <i className='fas fa-plus' />
                    {__('Add Field', 'dragwyb-form-builder')}
                </Button>
            }
            <p>{emptyMessage}</p>
        </div>
    </div>
}

const Canvas = ({ onFieldSelect, Utils, dropIndex, dropIndicatorPosition, setActiveTab }) => {
    const values = useSelector(state => state.values); // Assuming values are stored in Redux
    const formData = useSelector(state => state.form); // Assuming fields are stored in Redux
    const fields = formData.fields; // Assuming fields are stored in Redux
    const errors = useSelector(state => state.errors); // Assuming errors are stored in Redux

    const { setNodeRef, isOver } = useDroppable(
        {
            id: `canvas-drop-wrapper`,
            data: {
                canvasFieldDrop: true,
                canvasDrop: true,
                currentIndex: fields ? fields.length || 0 : 0
            }
        }
    );

    const dispatch = useDispatch();

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
        onFieldSelect({id: deepClone._id});
    };

    const handleDeleteField = (id) => {
        onFieldSelect({id: false});
        dispatch({ type: 'DELETE_FIELD', payload: id });
    };

    let canvasCls = "dragwyb-canvas";

    if (!fields || fields.length === 0) {
        canvasCls += " canvas-empty";
    }

    return (
        <div className="dragwyb-canvas-wrapper" ref={setNodeRef}>
            <div className={canvasCls}>
            {fields && fields.length > 0 &&
                <>
                    {fields.map((field, index) =>
                        <RenderItem
                            key={field._id}
                            field={field}
                            values={values}
                            onFieldSelect={onFieldSelect}
                            onDuplicate={(field) => handleDuplicateField(field, index)}
                            onDelete={handleDeleteField}
                            errors={errors}
                            index={index}
                            dropIndex={dropIndex}
                            dropIndicatorPosition={dropIndicatorPosition}
                        />
                    )}
                    {dropIndex && dropIndex === fields.length ? <span className='dragwyb-editor-indicator'></span> : ''}
                </>
            }
            {(!fields || fields.length === 0) && (
                <EmptyCanvas setActiveTab={setActiveTab} isOver={isOver}/>
            )}
            </div>
        </div>
    );
};

export default Canvas;
