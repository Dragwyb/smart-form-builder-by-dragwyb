import React, { useState } from 'react';

const Tabs = ({ children, defaultTab = 0, onChange }) => {
    const [activeTab, setActiveTab] = useState(defaultTab);

    const handleTabChange = (index) => {
        setActiveTab(index);
        if (onChange) {
            onChange(index);
        }
    };

    return (
        <div className="dragwyb-tabs">
            <div className="dragwyb-tabs__nav">
                {React.Children.map(children, (child, index) => (
                    <button
                        key={index}
                        className={`dragwyb-tabs__nav-item ${activeTab === index ? 'active' : ''}`}
                        onClick={() => handleTabChange(index)}
                    >
                        {child.props.label}
                    </button>
                ))}
            </div>
            <div className="dragwyb-tabs__content">
                {React.Children.toArray(children)[activeTab]}
            </div>
        </div>
    );
};

export const Tab = ({ children }) => (
    <div className="dragwyb-tabs__panel">
        {children}
    </div>
);

Tabs.Tab = Tab;
export default Tabs; 