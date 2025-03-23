import React from 'react';
import { Dashicon } from '@wordpress/components';

const Button = ({
    children,
    variant = 'default',
    size = 'medium',
    icon,
    onClick,
    disabled = false,
    className = '',
    type = 'button',
    loading = false,
}) => {
    const baseClass = 'dragwyb-button';
    const classes = [
        baseClass,
        `${baseClass}--${variant}`,
        `${baseClass}--${size}`,
        loading && `${baseClass}--loading`,
        className
    ].filter(Boolean).join(' ');

    return (
        <button
            type={type}
            className={classes}
            onClick={onClick}
            disabled={disabled || loading}
        >
            {loading && <span className="dragwyb-button__spinner" />}
            {icon && <Dashicon icon={icon} />}
            {children && <span className="dragwyb-button__text">{children}</span>}
        </button>
    );
};

export default Button; 