import React from 'react';
import { __ } from '@wordpress/i18n';
import { useDraggable, useDroppable } from "../../components/Common";

const Field = ({ field, value = '', errors = [], disabled = false, children, childrens, Utils, perviewIFrame }) => {

    const getHtml = () => {
        return <div>Unsupported field type: {field.type}</div>;
    };

    if (['row'].includes(field.type)) {
        const extensibleUtils = {};
        extensibleUtils.useDraggable = useDraggable;
        extensibleUtils.useDroppable = useDroppable;

        Utils = { ...Utils, ...extensibleUtils };
    }

    const previewFrameWindow = perviewIFrame.defaultView;

    let Html = previewFrameWindow.DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/FieldRender/' + field.type, getHtml(), children, field.type, field._id, value, field, Utils, childrens);

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