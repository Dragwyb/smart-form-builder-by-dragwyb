import React from 'react';

const Spinner = ({ size = 'medium', className = '' }) => (
    <div className={`dragwyb-spinner dragwyb-spinner--${size} ${className}`}>
        <div className="dragwyb-spinner__bounce1"></div>
        <div className="dragwyb-spinner__bounce2"></div>
        <div className="dragwyb-spinner__bounce3"></div>
    </div>
);

export default Spinner; 