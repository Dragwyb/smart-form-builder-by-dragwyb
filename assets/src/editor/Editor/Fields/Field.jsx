import React from 'react';
import { __ } from '@wordpress/i18n';

const Field = ({ field, value = '', onChange, errors = [], disabled = false }) => {
    const getHtml = () => {
        return <div>Unsupported field type: {field.type}</div>;
    };

    let Html = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/FieldRender/' + field.type, getHtml(), field.type, field._id, value, field, onChange);

    return (
        <>
            {Html}
            {errors.length > 0 && (
                <div className="dragwyb-field__errors">
                    {errors.map((error, index) => (
                        <div key={index} className="dragwyb-field__error">{error}</div>
                    ))}
                </div>
            )}
        </>
    );
};

export default Field; 