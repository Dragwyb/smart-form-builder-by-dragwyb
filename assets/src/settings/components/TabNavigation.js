import React from 'react';

const tabs = [
    { id: 'integrations', label: 'Integrations', icon: 'fa-plug' },
    { id: 'performance', label: 'Performance', icon: 'fa-bolt' },
    { id: 'fields_manager', label: 'Fields Manager', icon: 'fa-list-alt' },
    { id: 'smtp', label: 'SMTP', icon: 'fa-envelope' },
    { id: 'gdpr_privacy', label: 'GDPR / Privacy', icon: 'fa-shield-alt' },
    { id: 'import_export', label: 'Import/Export', icon: 'fa-file-import' }
];

const TabNavigation = ({ activeTab, setActiveTab }) => {
    return (
        <div className="dragwyb-settings-sidebar">
            <ul className="dragwyb-settings-tabs">
                {tabs.map(tab => (
                    <li 
                        key={tab.id} 
                        className={activeTab === tab.id ? 'active' : ''}
                        onClick={() => setActiveTab(tab.id)}
                    >
                        <i className={`fas ${tab.icon}`}></i>
                        {tab.label}
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default TabNavigation;
