import React, { useEffect } from 'react';
import { createPortal } from 'react-dom';
import Button from './Button';

const Modal = ({
    isOpen,
    onClose,
    title,
    children,
    footer,
    size = 'medium',
    className = '',
}) => {
    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        }
        return () => {
            document.body.style.overflow = '';
        };
    }, [isOpen]);

    if (!isOpen) return null;

    const modalContent = (
        <div className="dragwyb-modal__overlay" onClick={onClose}>
            <div 
                className={`dragwyb-modal__content dragwyb-modal__content--${size} ${className}`}
                onClick={e => e.stopPropagation()}
            >
                <div className="dragwyb-modal__header">
                    <h2 className="dragwyb-modal__title">{title}</h2>
                    <Button
                        variant="icon"
                        icon="no-alt"
                        onClick={onClose}
                        className="dragwyb-modal__close"
                    />
                </div>
                <div className="dragwyb-modal__body">
                    {children}
                </div>
                {footer && (
                    <div className="dragwyb-modal__footer">
                        {footer}
                    </div>
                )}
            </div>
        </div>
    );

    return createPortal(
        modalContent,
        document.getElementById('dragwyb-modal-root')
    );
};

export default Modal; 