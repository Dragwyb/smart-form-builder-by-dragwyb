import React from 'react';
import ToggleSwitch from './ToggleSwitch';

const GdprPrivacyTab = ({ settings, handleSettingChange }) => {
    const gdprData = settings?.gdpr_privacy || {};

    const getValue = (key, defaultVal) => {
        return gdprData[key]?.value !== undefined ? gdprData[key].value : (gdprData[key]?.default ?? defaultVal);
    };

    const isBool = (key, defaultVal = false) => {
        const val = getValue(key, defaultVal);
        return val === true || val === 'yes';
    };

    return (
        <div className="dragwyb-settings-section">
            <h2>GDPR & Privacy Controls</h2>
            <p className="dragwyb-settings-desc">
                Configure visitor tracking, cookie behavior, and data protection settings to comply with GDPR, CCPA, and global privacy regulations.
            </p>

            <div style={{ marginBottom: '24px' }}>
                <h3 style={{ fontSize: '16px', fontWeight: '600', color: '#1e293b', marginBottom: '12px' }}>
                    Visitor Tracking Settings
                </h3>

                <ToggleSwitch
                    label="Enable Visitor Tracking"
                    description="Collect visitor analytics, pageviews, and traffic source attribution across your website."
                    checked={isBool('tracking_enabled', true)}
                    onChange={val => handleSettingChange('gdpr_privacy', 'tracking_enabled', val)}
                />

                <ToggleSwitch
                    label="Track Logged-in Administrators"
                    description="Include site administrators in visitor analytics and session metrics."
                    checked={isBool('track_admins', false)}
                    onChange={val => handleSettingChange('gdpr_privacy', 'track_admins', val)}
                />

                <div className="dragwyb-setting-row">
                    <div className="dragwyb-setting-info">
                        <h4>Session Timeout (minutes)</h4>
                        <p style={{ margin: 0, fontSize: '13px', color: '#64748b' }}>
                            A new session starts after this many minutes of inactivity (default: 30).
                        </p>
                    </div>
                    <div className="dragwyb-setting-control">
                        <input
                            type="number"
                            min="5"
                            max="120"
                            value={getValue('session_timeout', 30)}
                            onChange={e => handleSettingChange('gdpr_privacy', 'session_timeout', parseInt(e.target.value, 10) || 30)}
                            style={{ width: '100px' }}
                        />
                    </div>
                </div>

                <div className="dragwyb-setting-row">
                    <div className="dragwyb-setting-info">
                        <h4>Cookie Duration (days)</h4>
                        <p style={{ margin: 0, fontSize: '13px', color: '#64748b' }}>
                            How long the visitor identification cookie persists (730 = 2 years).
                        </p>
                    </div>
                    <div className="dragwyb-setting-control">
                        <input
                            type="number"
                            min="1"
                            max="730"
                            value={getValue('cookie_duration', 730)}
                            onChange={e => handleSettingChange('gdpr_privacy', 'cookie_duration', parseInt(e.target.value, 10) || 730)}
                            style={{ width: '100px' }}
                        />
                    </div>
                </div>
            </div>

            <div style={{ paddingTop: '20px', borderTop: '1px solid #e2e8f0' }}>
                <h3 style={{ fontSize: '16px', fontWeight: '600', color: '#1e293b', marginBottom: '12px' }}>
                    Privacy & Data Anonymization
                </h3>

                <ToggleSwitch
                    label="Anonymize IP Addresses"
                    description="Removes the last octet of IPv4 addresses (e.g. 192.168.1.100 becomes 192.168.1.0) before storing."
                    checked={isBool('gdpr_anonymize_ip', false)}
                    onChange={val => handleSettingChange('gdpr_privacy', 'gdpr_anonymize_ip', val)}
                />

                <ToggleSwitch
                    label="Respect Do Not Track (DNT) Browser Header"
                    description="When enabled, tracking is completely disabled for visitors whose browser sends the DNT header."
                    checked={isBool('gdpr_respect_dnt', false)}
                    onChange={val => handleSettingChange('gdpr_privacy', 'gdpr_respect_dnt', val)}
                />

                <ToggleSwitch
                    label="Disable Tracking Cookies"
                    description="Prevents the plugin from setting visitor identification cookies. Tracking operates per-session without tracking returning visitors."
                    checked={isBool('gdpr_disable_user_cookies', false)}
                    onChange={val => handleSettingChange('gdpr_privacy', 'gdpr_disable_user_cookies', val)}
                />

                <ToggleSwitch
                    label="Disable User Details Collection"
                    description="Prevents recording IP address, user agent, and browser/OS details with form submissions and pageviews."
                    checked={isBool('gdpr_disable_user_details', false)}
                    onChange={val => handleSettingChange('gdpr_privacy', 'gdpr_disable_user_details', val)}
                />

                <div className="dragwyb-setting-row">
                    <div className="dragwyb-setting-info">
                        <h4>Auto-delete tracking data after (days)</h4>
                        <p style={{ margin: 0, fontSize: '13px', color: '#64748b' }}>
                            Automatically delete visitor tracking data older than this many days (0 = keep forever).
                        </p>
                    </div>
                    <div className="dragwyb-setting-control">
                        <input
                            type="number"
                            min="0"
                            max="3650"
                            value={getValue('gdpr_data_retention_days', 0)}
                            onChange={e => handleSettingChange('gdpr_privacy', 'gdpr_data_retention_days', parseInt(e.target.value, 10) || 0)}
                            style={{ width: '100px' }}
                        />
                    </div>
                </div>

                <ToggleSwitch
                    label="Retain Form Entries When Cleaning Tracking Data"
                    description="When checked, auto-delete only purges analytics sessions/pageviews but keeps form submission leads."
                    checked={isBool('gdpr_retain_entries', true)}
                    onChange={val => handleSettingChange('gdpr_privacy', 'gdpr_retain_entries', val)}
                />

                <div style={{ marginTop: '16px', padding: '12px 16px', background: '#fff5f5', border: '1px solid #fed7d7', borderRadius: '8px' }}>
                    <ToggleSwitch
                        label="Remove ALL Data on Plugin Uninstall"
                        description="Warning: Deleting the plugin will permanently wipe all forms, entries, and analytics tables. Leave unchecked to preserve data across reinstalls."
                        checked={isBool('remove_data_on_uninstall', false)}
                        onChange={val => handleSettingChange('gdpr_privacy', 'remove_data_on_uninstall', val)}
                    />
                </div>
            </div>
        </div>
    );
};

export default GdprPrivacyTab;
