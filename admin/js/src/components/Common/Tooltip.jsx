import React, { useState } from 'react';

const Tooltip = ({ content, children, position = 'top' }) => {
    const [isVisible, setIsVisible] = useState(false);

    return (
        <div 
            className="dragwyb-tooltip-wrapper"
            onMouseEnter={() => setIsVisible(true)}
            onMouseLeave={() => setIsVisible(false)}
        >
            {children}
            {isVisible && (
                <div className={`dragwyb-tooltip dragwyb-tooltip--${position}`}>
                    {content}
                </div>
            )}
        </div>
    );
};

export default Tooltip; 