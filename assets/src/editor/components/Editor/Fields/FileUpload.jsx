import React, { useRef } from 'react';
import { __ } from '@wordpress/i18n';
import { formatFileSize } from '../../../utils/helpers';

const FileUpload = ({ field, value, onChange, disabled }) => {
    const fileInput = useRef(null);

    const handleFileChange = (e) => {
        const files = Array.from(e.target.files);
        if (field.multiple) {
            onChange(files);
        } else {
            onChange(files[0]);
        }
    };

    const handleDrop = (e) => {
        e.preventDefault();
        if (disabled) return;

        const files = Array.from(e.dataTransfer.files);
        if (field.multiple) {
            onChange(files);
        } else {
            onChange(files[0]);
        }
    };

    const renderFileList = () => {
        if (!value) return null;
        const files = Array.isArray(value) ? value : [value];
        
        return (
            <div className="dragwyb-file-list">
                {files.map((file, index) => (
                    <div key={index} className="dragwyb-file-item">
                        <span>{file.name}</span>
                        <span>({formatFileSize(file.size)})</span>
                        <button
                            type="button"
                            onClick={() => {
                                const newFiles = Array.isArray(value) 
                                    ? value.filter((_, i) => i !== index)
                                    : null;
                                onChange(newFiles);
                            }}
                            disabled={disabled}
                        >
                            ×
                        </button>
                    </div>
                ))}
            </div>
        );
    };

    return (
        <div
            className="dragwyb-file-upload"
            onDragOver={(e) => e.preventDefault()}
            onDrop={handleDrop}
        >
            <input
                ref={fileInput}
                type="file"
                id={field.id}
                name={field.name}
                onChange={handleFileChange}
                accept={field.accept}
                multiple={field.multiple}
                required={field.required}
                disabled={disabled}
                style={{ display: 'none' }}
            />
            <div className="dragwyb-file-upload__dropzone">
                <button
                    type="button"
                    onClick={() => fileInput.current?.click()}
                    disabled={disabled}
                >
                    {__('Choose File', 'dragwyb')}
                </button>
                <span>{__('or drag and drop', 'dragwyb')}</span>
            </div>
            {renderFileList()}
        </div>
    );
};

export default FileUpload; 