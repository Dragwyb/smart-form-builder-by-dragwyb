import React from 'react';
import { __ } from '@wordpress/i18n';
import { useDraggable, useDroppable } from "../../components/Common";
import { helpers } from '../../utils/helpers';

const Field = ({ field, value = '', onChange, errors = [], disabled = false, children, childrens, Utils }) => {

    const getHtml = () => {
        return <div>Unsupported field type: {field.type}</div>;
    };


    if (['grid', 'column'].includes(field.type)) {
        const extensibleUtils = {};
        extensibleUtils.useDraggable = useDraggable;
        extensibleUtils.useDroppable = useDroppable;

        extensibleUtils.setSelectedSettingId = helpers.setSelectedSettingId;
        extensibleUtils.setActiveTab = helpers.setActiveTab;

        Object.freeze(extensibleUtils);

        Utils = { ...Utils, ...extensibleUtils };
    }

    let Html = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/FieldRender/' + field.type, getHtml(), children, field.type, field._id, value, field, onChange, Utils, childrens);

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