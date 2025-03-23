import React from 'react';
import Modal from './Modal';
import Button from './Button';

const ConfirmDialog = ({
    isOpen,
    onClose,
    onConfirm,
    title,
    message,
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    variant = 'danger'
}) => {
    const footer = (
        <>
            <Button onClick={onClose}>
                {cancelText}
            </Button>
            <Button 
                variant={variant} 
                onClick={() => {
                    onConfirm();
                    onClose();
                }}
            >
                {confirmText}
            </Button>
        </>
    );

    return (
        <Modal
            isOpen={isOpen}
            onClose={onClose}
            title={title}
            footer={footer}
            size="small"
        >
            <p>{message}</p>
        </Modal>
    );
};

export default ConfirmDialog; 