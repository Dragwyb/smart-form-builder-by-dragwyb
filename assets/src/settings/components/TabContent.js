import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import ToggleSwitch from './ToggleSwitch';
import ImportExportTab from './ImportExportTab';
import SmtpTab from './SmtpTab';
import GdprPrivacyTab from './GdprPrivacyTab';
import RenderSettingsGroup from './RenderSettingsGroup';

const TabContent = ({ activeTab, settings, handleSettingChange, showToast }) => {

    const renderDynamicSection = (tabKey, title, description) => {
        const tabSettings = settings?.[tabKey] || {};
        return (
            <div className="dragwyb-settings-section">
                {title && <h2>{title}</h2>}
                {description && <p className="dragwyb-settings-desc">{description}</p>}
                <RenderSettingsGroup
                    tabSettings={tabSettings}
                    tabKey={tabKey}
                    handleSettingChange={handleSettingChange}
                />
            </div>
        );
    };

    const renderFieldsManager = () => {
        return (
            <div className="dragwyb-settings-section">
                <h2>Fields Manager</h2>
                <p className="dragwyb-settings-desc">Enable or disable specific fields from appearing in the form builder editor.</p>
                <div className="dragwyb-fields-grid">
                    {settings && settings.fields_manager && Object.keys(settings.fields_manager).map(key => (
                        <div key={key} className="dragwyb-field-toggle-card">
                            <ToggleSwitch
                                label={settings.fields_manager[key].name || key}
                                checked={(settings.fields_manager[key]?.value && settings.fields_manager[key]?.value === true) || (settings.fields_manager[key]?.value !== false && settings.fields_manager[key]?.default === true)}
                                onChange={val => handleSettingChange('fields_manager', key, val ? true : false)}
                            />
                        </div>
                    ))}
                </div>
            </div>
        );
    };

    return (
        <div className="dragwyb-settings-content">
            <AnimatePresence mode="wait">
                <motion.div
                    key={activeTab}
                    initial={{ opacity: 0, x: 20 }}
                    animate={{ opacity: 1, x: 0 }}
                    exit={{ opacity: 0, x: -20 }}
                    transition={{ duration: 0.2 }}
                >
                    {activeTab === 'integrations' && renderDynamicSection('integrations', 'API & Integrations', 'Manage API keys for third-party services like Google reCAPTCHA.')}
                    {activeTab === 'performance' && renderDynamicSection('performance', 'Performance & Assets', 'Control which assets are loaded on the frontend to improve page speed.')}
                    {activeTab === 'fields_manager' && renderFieldsManager()}
                    {activeTab === 'smtp' && (
                        <SmtpTab
                            settings={settings}
                            handleSettingChange={handleSettingChange}
                            showToast={showToast}
                        />
                    )}
                    {activeTab === 'gdpr_privacy' && (
                        <GdprPrivacyTab
                            settings={settings}
                            handleSettingChange={handleSettingChange}
                        />
                    )}
                    {activeTab === 'import_export' && <ImportExportTab showToast={showToast} />}
                </motion.div>
            </AnimatePresence>
        </div>
    );
};

export default TabContent;
