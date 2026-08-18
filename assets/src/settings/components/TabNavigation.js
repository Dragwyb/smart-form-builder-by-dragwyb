import React from 'react';
import {
    FaPlug,
    FaTachometerAlt,
    FaTable,
    FaEnvelope,
    FaShieldAlt,
    FaExchangeAlt
} from 'react-icons/fa';

const tabs = [
    { id: 'integrations', label: 'Integrations', Icon: FaPlug },
    { id: 'performance', label: 'Performance', Icon: FaTachometerAlt },
    { id: 'fields_manager', label: 'Fields Manager', Icon: FaTable },
    { id: 'smtp', label: 'SMTP', Icon: FaEnvelope },
    { id: 'gdpr_privacy', label: 'GDPR / Privacy', Icon: FaShieldAlt },
    { id: 'import_export', label: 'Import / Export', Icon: FaExchangeAlt }
];

const TabNavigation = ({ activeTab, setActiveTab }) => {
    return (
        <nav className="dragwyb-settings-nav-bar">
            <ul className="dragwyb-settings-horizontal-tabs">
                {tabs.map(({ id, label, Icon }) => (
                    <li
                        key={id}
                        className={`dragwyb-tab-item ${activeTab === id ? 'active' : ''}`}
                        onClick={() => setActiveTab(id)}
                    >
                        <Icon className="dragwyb-tab-icon" />
                        <span>{label}</span>
                    </li>
                ))}
            </ul>
        </nav>
    );
};

export default TabNavigation;
