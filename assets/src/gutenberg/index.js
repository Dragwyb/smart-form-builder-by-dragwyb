import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Placeholder, Spinner } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import React from 'react';

/**
 * Smart Form SVG Icon
 */
const FormIcon = () => (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" strokeWidth="2" fill="none" />
        <line x1="7" y1="8" x2="17" y2="8" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        <line x1="7" y1="12" x2="14" y2="12" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        <line x1="7" y1="16" x2="11" y2="16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
    </svg>
);

registerBlockType('dragwyb/form', {
    apiVersion: 2,
    title: __('Smart Form', 'smart-form-builder-by-dragwyb'),
    description: __('Display a form created with Smart Form Builder.', 'smart-form-builder-by-dragwyb'),
    category: 'widgets',
    icon: FormIcon,
    keywords: [
        __('form', 'smart-form-builder-by-dragwyb'),
        __('contact form', 'smart-form-builder-by-dragwyb'),
        __('smart form', 'smart-form-builder-by-dragwyb'),
        __('dragwyb', 'smart-form-builder-by-dragwyb'),
    ],
    attributes: {
        formId: {
            type: 'string',
            default: '',
        },
    },
    edit: function Edit({ attributes, setAttributes }) {
        const blockProps = useBlockProps({
            className: 'dragwyb-gutenberg-block-wrapper',
        });

        const { formId } = attributes;

        // Retrieve available forms from localized script data
        const rawForms = window.dragwybBlockData?.forms || [];
        const formsOptions = [
            { label: __('— Select a Form —', 'smart-form-builder-by-dragwyb'), value: '' },
            ...rawForms.map((item) => ({
                label: item.label,
                value: String(item.value),
            })),
        ];

        const handleFormChange = (newFormId) => {
            setAttributes({ formId: String(newFormId || '') });
        };

        const formDropdown = (
            <SelectControl
                label={__('Select Form', 'smart-form-builder-by-dragwyb')}
                value={formId || ''}
                options={formsOptions}
                onChange={handleFormChange}
                help={__('Choose the form you want to display on this page.', 'smart-form-builder-by-dragwyb')}
            />
        );

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title={__('Form Settings', 'smart-form-builder-by-dragwyb')} initialOpen={true}>
                        {formDropdown}
                    </PanelBody>
                </InspectorControls>

                {!formId ? (
                    <Placeholder
                        icon={FormIcon}
                        label={__('Smart Form Builder', 'smart-form-builder-by-dragwyb')}
                        instructions={__('Select a form from the dropdown to embed and preview it here.', 'smart-form-builder-by-dragwyb')}
                        className="dragwyb-block-placeholder"
                    >
                        <div style={{ width: '100%', maxWidth: '380px', marginTop: '10px' }}>
                            {formDropdown}
                        </div>
                    </Placeholder>
                ) : (
                    <div className="dragwyb-block-rendered-form">
                        <ServerSideRender
                            block="dragwyb/form"
                            attributes={{ formId }}
                            LoadingResponsePlaceholder={() => (
                                <Placeholder
                                    icon={FormIcon}
                                    label={__('Loading Form Preview...', 'smart-form-builder-by-dragwyb')}
                                >
                                    <Spinner />
                                </Placeholder>
                            )}
                            EmptyResponsePlaceholder={() => (
                                <Placeholder
                                    icon={FormIcon}
                                    label={__('Smart Form Builder', 'smart-form-builder-by-dragwyb')}
                                    instructions={__('Form not found or has no published fields.', 'smart-form-builder-by-dragwyb')}
                                >
                                    <div style={{ width: '100%', maxWidth: '380px', marginTop: '10px' }}>
                                        {formDropdown}
                                    </div>
                                </Placeholder>
                            )}
                        />
                    </div>
                )}
            </div>
        );
    },
    save: function Save() {
        return null;
    },
});
