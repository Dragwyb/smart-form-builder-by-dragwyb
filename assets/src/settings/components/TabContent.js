import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import ToggleSwitch from './ToggleSwitch';

const TabContent = ({ activeTab, settings, handleSettingChange }) => {

    const renderIntegrations = () => (
        <div className="dragwyb-settings-section">
            <h2>API & Integrations</h2>
            <p className="dragwyb-settings-desc">Manage API keys for third-party services like Google reCAPTCHA.</p>

            <div className="dragwyb-setting-row">
                <div className="dragwyb-setting-info">
                    <h4>Google reCAPTCHA v2 Site Key</h4>
                </div>
                <div className="dragwyb-setting-control">
                    <input
                        type="text"
                        value={settings.integrations.recaptcha_v2_site_key}
                        onChange={e => handleSettingChange('integrations', 'recaptcha_v2_site_key', e.target.value)}
                        placeholder="Enter Site Key"
                    />
                </div>
            </div>

            <div className="dragwyb-setting-row">
                <div className="dragwyb-setting-info">
                    <h4>Google reCAPTCHA v2 Secret Key</h4>
                </div>
                <div className="dragwyb-setting-control">
                    <input
                        type="password"
                        value={settings.integrations.recaptcha_v2_secret_key}
                        onChange={e => handleSettingChange('integrations', 'recaptcha_v2_secret_key', e.target.value)}
                        placeholder="Enter Secret Key"
                    />
                </div>
            </div>

            <div className="dragwyb-setting-row">
                <div className="dragwyb-setting-info">
                    <h4>hCaptcha Site Key</h4>
                </div>
                <div className="dragwyb-setting-control">
                    <input
                        type="text"
                        value={settings.integrations.hcaptcha_site_key}
                        onChange={e => handleSettingChange('integrations', 'hcaptcha_site_key', e.target.value)}
                        placeholder="Enter Site Key"
                    />
                </div>
            </div>

            <div className="dragwyb-setting-row">
                <div className="dragwyb-setting-info">
                    <h4>hCaptcha Secret Key</h4>
                </div>
                <div className="dragwyb-setting-control">
                    <input
                        type="password"
                        value={settings.integrations.hcaptcha_secret_key}
                        onChange={e => handleSettingChange('integrations', 'hcaptcha_secret_key', e.target.value)}
                        placeholder="Enter Secret Key"
                    />
                </div>
            </div>
        </div>
    );

    const renderPerformance = () => (
        <div className="dragwyb-settings-section">
            <h2>Performance & Assets</h2>
            <p className="dragwyb-settings-desc">Control which assets are loaded on the frontend to improve page speed.</p>

            <ToggleSwitch
                label="Load Font Awesome"
                description="Disable this if your theme already loads Font Awesome."
                checked={settings.performance.load_font_awesome === 'yes'}
                onChange={val => handleSettingChange('performance', 'load_font_awesome', val ? 'yes' : 'no')}
            />

            <ToggleSwitch
                label="Load SVG icons"
                description="Disable this if your theme already loads SVG icons."
                checked={settings.performance.load_svg_icons === 'yes'}
                onChange={val => handleSettingChange('performance', 'load_svg_icons', val ? 'yes' : 'no')}
            />

            <ToggleSwitch
                label="Load Default CSS"
                description="Disable this to completely remove default form styling (for advanced users)."
                checked={settings.performance.load_default_css === 'yes'}
                onChange={val => handleSettingChange('performance', 'load_default_css', val ? 'yes' : 'no')}
            />
        </div>
    );

    const renderFieldsManager = () => {
        const fields = [
            { key: 'field_text', label: 'Text Field' },
            { key: 'field_email', label: 'Email Field' },
            { key: 'field_textarea', label: 'Textarea' },
            { key: 'field_select', label: 'Select Dropdown' },
            { key: 'field_radio', label: 'Radio Buttons' },
            { key: 'field_checkbox', label: 'Checkboxes' },
            { key: 'field_number', label: 'Number Field' },
            { key: 'field_hidden', label: 'Hidden Field' },
            { key: 'field_date', label: 'Date Picker' },
            { key: 'field_time', label: 'Time Picker' },
            { key: 'field_phone', label: 'Phone Field' },
            { key: 'field_url', label: 'URL Field' },
            { key: 'field_name', label: 'Name Field' },
            { key: 'field_address', label: 'Address Field' },
            { key: 'field_range', label: 'Range Slider' },
            { key: 'field_file', label: 'File Upload' },
            { key: 'field_captcha', label: 'Captcha Field' },
            { key: 'field_row', label: 'Row / Columns' },
            { key: 'field_section', label: 'Section Break' },
            { key: 'field_html', label: 'Custom HTML' },
            { key: 'field_button', label: 'Submit Button' },
        ];

        return (
            <div className="dragwyb-settings-section">
                <h2>Fields Manager</h2>
                <p className="dragwyb-settings-desc">Enable or disable specific fields from appearing in the form builder editor.</p>
                <div className="dragwyb-fields-grid">
                    {fields.map(field => (
                        <div key={field.key} className="dragwyb-field-toggle-card">
                            <ToggleSwitch
                                label={field.label}
                                checked={settings.fields_manager[field.key]}
                                onChange={val => handleSettingChange('fields_manager', field.key, val)}
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
                </motion.div>
            </AnimatePresence>
        </div>
    );
};

export default TabContent;
