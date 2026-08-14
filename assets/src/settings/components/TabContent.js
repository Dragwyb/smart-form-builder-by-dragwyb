import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import ToggleSwitch from './ToggleSwitch';
import ImportExportTab from './ImportExportTab';
import SmtpTab from './SmtpTab';
import GdprPrivacyTab from './GdprPrivacyTab';

const TabContent = ({ activeTab, settings, handleSettingChange, showToast }) => {

    const renderIntegrations = () => (
        <div className="dragwyb-settings-section">
            <h2>API & Integrations</h2>
            <p className="dragwyb-settings-desc">Manage API keys for third-party services like Google reCAPTCHA.</p>
            {
                settings && settings.integrations && Object.keys(settings.integrations).map(key => (
                    <div key={key} className="dragwyb-setting-row">
                        <div className="dragwyb-setting-info">
                            <h4>{settings.integrations[key].label}</h4>
                        </div>
                        <div className="dragwyb-setting-control">
                            <input
                                type="text"
                                value={settings.integrations[key].value}
                                onChange={e => handleSettingChange('integrations', key, e.target.value)}
                                placeholder="Enter API Key"
                            />
                        </div>
                    </div>
                ))
            }
        </div>
    );

    const renderPerformance = () => (
        <div className="dragwyb-settings-section">
            <h2>Performance & Assets</h2>
            <p className="dragwyb-settings-desc">Control which assets are loaded on the frontend to improve page speed.</p>
            {
                settings && settings.performance && Object.keys(settings.performance).map(key => (
                    <ToggleSwitch
                        key={key}
                        label={settings.performance[key].label}
                        description={settings.performance[key].description}
                        checked={(settings.performance[key]?.value && settings.performance[key]?.value === 'yes') || (!settings.performance[key]?.value && settings.performance[key]?.default === 'yes')}
                        onChange={val => handleSettingChange('performance', key, val ? 'yes' : 'no')}
                    />
                ))
            }
        </div>
    );

    const renderFieldsManager = () => {

        return (

            <div className="dragwyb-settings-section">
                <h2>Fields Manager</h2>
                <p className="dragwyb-settings-desc">Enable or disable specific fields from appearing in the form builder editor.</p>
                <div className="dragwyb-fields-grid">
                    {settings && settings.fields_manager && Object.keys(settings.fields_manager).map(key => (
                        <div key={key} className="dragwyb-field-toggle-card">
                            <ToggleSwitch
                                label={settings.fields_manager[key].name}
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
                    {activeTab === 'integrations' && renderIntegrations()}
                    {activeTab === 'performance' && renderPerformance()}
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
