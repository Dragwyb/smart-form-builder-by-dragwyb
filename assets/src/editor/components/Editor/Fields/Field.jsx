import React from 'react';
import { __ } from '@wordpress/i18n';
// import DateField from './DateField';
// import EmailField from './EmailField';
// import PhoneField from './PhoneField';
// import NumberField from './NumberField';
// import HiddenField from './HiddenField';
// import SignatureField from './SignatureField';
// import RatingField from './RatingField';

const Field = ({ field, value = '', onChange, errors = [], disabled = false }) => {
    const getHtml = () => {
        return <div>Unsupported field type: {field.type}</div>;
    };

    let Html = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/FieldRender/' + field.type, getHtml(), field.type, field._id, value, field, onChange);

    return (
        <div className={`dragwyb-field dragwyb-field--${field.type} ${field.className || ''}`}>
            <div className="dragwyb-field__input">
                {Html}
            </div>
            {errors.length > 0 && (
                <div className="dragwyb-field__errors">
                    {errors.map((error, index) => (
                        <div key={index} className="dragwyb-field__error">{error}</div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default Field; 