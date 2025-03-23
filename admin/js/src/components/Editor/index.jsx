import React, { useState, useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import Canvas from './Canvas';
import Controls from './Controls';
import FieldSettings from './FieldSettings';
import FormSettings from './FormSettings';
import Preview from './Preview';
import { saveForm } from '../../store/actions';
import { Button } from '../Common';

const Editor = () => {
    const [activeTab, setActiveTab] = useState('fields');
    const [selectedField, setSelectedField] = useState(null);
    const [previewMode, setPreviewMode] = useState(false);
    const formData = useSelector(state => state.form);
    const fields = useSelector(state => state.form.fields); // Assuming fields are stored in Redux
    const values = useSelector(state => state.form.fields.reduce((acc, field) => {
        acc[field.id] = field.values;
        return acc;
    }, {})); // Assuming values are stored in Redux
    const errors = useSelector(state => state.errors); // Assuming errors are stored in Redux
    const dispatch = useDispatch();

    const handleSave = async () => {
        try {
            await dispatch(saveForm(formData));
        } catch (error) {
            console.error('Save failed:', error);
        }
    };

    const handleExit = () => {
        window.location.href = DragwybEditor.adminUrl;
    };

    const handleChange = (fieldId, value) => {
        dispatch(updateFieldValues(fieldId, value));
    };

    const selectedFieldSetting=()=>{
        let value=null;
        Object.values(fields).forEach(field=>{
            if(field.id === selectedField.id){
                value=field;
            }
        })

        return value;
    }

    return (
        <div className="dragwyb-editor">
            <div className="dragwyb-editor__header">
                <div className="dragwyb-editor__title">
                    <input
                        type="text"
                        value={formData.title}
                        onChange={e => dispatch({
                            type: 'UPDATE_FORM_TITLE',
                            payload: e.target.value
                        })}
                        placeholder={DragwybEditor.i18n.formTitle}
                    />
                </div>
                <div className="dragwyb-editor__tabs">
                    <Button
                        isActive={activeTab === 'fields' && !previewMode}
                        onClick={() => {
                            setActiveTab('fields');
                            setPreviewMode(false);
                        }}
                    >
                        {DragwybEditor.i18n.addField}
                    </Button>
                    <Button
                        isActive={activeTab === 'settings' && !previewMode}
                        onClick={() => {
                            setActiveTab('settings');
                            setPreviewMode(false);
                        }}
                    >
                        {DragwybEditor.i18n.formSettings}
                    </Button>
                    <Button
                        isActive={previewMode}
                        onClick={() => setPreviewMode(!previewMode)}
                    >
                        {DragwybEditor.i18n.preview}
                    </Button>
                </div>
                <div className="dragwyb-editor__actions">
                    <Button onClick={handleExit}>
                        {DragwybEditor.i18n.cancel}
                    </Button>
                    <Button isPrimary onClick={handleSave}>
                        {DragwybEditor.i18n.save}
                    </Button>
                </div>
            </div>
            <div className="dragwyb-editor__body">
                {previewMode ? (
                    <Preview fields={fields} values={values} errors={errors} onChange={handleChange}/>
                ) : (
                    <>
                        <div className="dragwyb-editor__sidebar">
                            {activeTab === 'fields' ? (
                                <Controls onFieldSelect={setSelectedField} />
                            ) : (
                                <FormSettings />
                            )}
                        </div>
                        <div className="dragwyb-editor__main">
                            <Canvas
                                selectedField={selectedField}
                                onFieldSelect={setSelectedField}
                                fields={fields}
                                values={values}
                                onChange={(name, value) => dispatch({
                                    type: 'UPDATE_FIELD_VALUE',
                                    payload: { name, value }
                                })}
                                errors={errors}
                            />
                        </div>
                        {selectedField && (
                            <div className="dragwyb-editor__settings">
                                <FieldSettings
                                    field={selectedField}
                                    fieldSettings={selectedFieldSetting()}
                                    onClose={() => setSelectedField(null)}
                                />
                            </div>
                        )}
                    </>
                )}
            </div>
        </div>
    );
};

export default Editor; 