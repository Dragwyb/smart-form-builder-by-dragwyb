import React from 'react';

const Panel = ({ children, title, bage = false, onClose }) => {
    return (
        <div className="dragwyb-panel">
            <div className="dragwyb-panel__header">
                <h3>{title}</h3>
                {onClose && (
                    <button className="close-button" onClick={onClose}>
                        ×
                    </button>
                )}
            </div>
            <div className="dragwyb-panel__content">
                {children}
            </div>
        </div>
    );
};

export default Panel; 