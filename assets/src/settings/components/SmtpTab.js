import React, { useState } from 'react';
import ToggleSwitch from './ToggleSwitch';

const PROVIDERS = {
    gmail: {
        name: 'Gmail / Google Workspace',
        host: 'smtp.gmail.com',
        port: 587,
        encryption: 'tls',
        auth: true,
        steps: [
            'Your normal Google password will NOT work — you need an App Password.',
            'Go to Google Account → Security and ensure 2-Step Verification is enabled.',
            'Navigate to Security → App passwords, generate a key for "Smart Form Builder", and copy the 16-character code.',
            'Username: your full Gmail address. Password: the 16-character App Password.',
            'Set From Email to the same Gmail address.'
        ]
    },
    microsoft: {
        name: 'Microsoft 365 / Outlook',
        host: 'smtp.office365.com',
        port: 587,
        encryption: 'tls',
        auth: true,
        steps: [
            'Microsoft disables SMTP sending by default on many accounts ("SMTP AUTH").',
            'An admin must enable it: Microsoft 365 admin center → Users → Active Users → select user → Mail → Manage email apps → check "Authenticated SMTP".',
            'If 2FA is active, generate an App Password in your Microsoft Account security page.',
            'Username: full Microsoft mailbox address.'
        ]
    },
    brevo: {
        name: 'Brevo (Sendinblue)',
        host: 'smtp-relay.brevo.com',
        port: 587,
        encryption: 'tls',
        auth: true,
        steps: [
            'In Brevo, open Settings → SMTP & API → SMTP tab.',
            'Username: the login email shown there. Password: your generated SMTP key.',
            'Ensure your From Email is verified under Senders & Domains.'
        ]
    },
    sendgrid: {
        name: 'SendGrid',
        host: 'smtp.sendgrid.net',
        port: 587,
        encryption: 'tls',
        auth: true,
        steps: [
            'Create an API Key under SendGrid Settings → API Keys with "Mail Send" permission.',
            'Username: literally the word "apikey" (not your account username).',
            'Password: your generated API Key (starts with SG.).'
        ]
    },
    mailgun: {
        name: 'Mailgun',
        host: 'smtp.mailgun.org',
        port: 587,
        encryption: 'tls',
        auth: true,
        steps: [
            'In Mailgun, go to Sending → Domain settings → SMTP credentials for your domain.',
            'Username: the full SMTP login string (e.g. postmaster@yourdomain.com).',
            'Password: the password created for that SMTP user.'
        ]
    },
    ses: {
        name: 'Amazon SES',
        host: 'email-smtp.us-east-1.amazonaws.com',
        port: 587,
        encryption: 'tls',
        auth: true,
        steps: [
            'Generate SMTP Credentials in the SES Console → Account Dashboard → SMTP Settings.',
            'Note: Regular AWS Access Keys will NOT work; use generated SMTP credentials.',
            'Verify your domain or From address under Verified Identities.'
        ]
    },
    zoho: {
        name: 'Zoho Mail',
        host: 'smtp.zoho.com',
        port: 587,
        encryption: 'tls',
        auth: true,
        steps: [
            'Username: your full Zoho email address.',
            'If 2FA is enabled, generate an App Password in Zoho Account Security.',
            'Use smtp.zoho.eu or smtp.zoho.in if your account is hosted in EU or IN region.'
        ]
    },
    cpanel: {
        name: 'Web Host / cPanel Email',
        host: 'mail.yourdomain.com',
        port: 587,
        encryption: 'tls',
        auth: true,
        steps: [
            'In cPanel → Email Accounts → Connect Devices, copy the outgoing server settings.',
            'Username: full email address. Password: mailbox password.',
            'If port 587 (TLS) fails, try port 465 (SSL).'
        ]
    }
};

const SmtpTab = ({ settings, handleSettingChange, showToast }) => {
    const smtpData = settings?.smtp || {};

    const getValue = (key, defaultVal = '') => {
        return smtpData[key]?.value !== undefined ? smtpData[key].value : (smtpData[key]?.default ?? defaultVal);
    };

    const isEnabled = getValue('smtp_enabled', false) === true || getValue('smtp_enabled', 'no') === 'yes';
    const isAuth = getValue('smtp_auth', true) === true || getValue('smtp_auth', 'yes') === 'yes';
    const applyToAll = getValue('smtp_apply_to_all', false) === true || getValue('smtp_apply_to_all', 'no') === 'yes';

    const [selectedProvider, setSelectedProvider] = useState('');
    const [testEmail, setTestEmail] = useState('');
    const [isTesting, setIsTesting] = useState(false);
    const [testResult, setTestResult] = useState(null);

    const handleProviderChange = (e) => {
        const pKey = e.target.value;
        setSelectedProvider(pKey);
        if (pKey && PROVIDERS[pKey]) {
            const p = PROVIDERS[pKey];
            handleSettingChange('smtp', 'smtp_host', p.host);
            handleSettingChange('smtp', 'smtp_port', p.port);
            handleSettingChange('smtp', 'smtp_encryption', p.encryption);
            handleSettingChange('smtp', 'smtp_auth', p.auth);
        }
    };

    const handleTestEmail = async () => {
        if (!testEmail) {
            showToast('Please enter a test email address', 'error');
            return;
        }

        setIsTesting(true);
        setTestResult(null);

        const { restUrl, nonce } = window.DragwybSettingsData || {};

        try {
            const response = await fetch(`${restUrl}/test-email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify({ email: testEmail })
            });

            const result = await response.json();
            if (result.status === 'success') {
                setTestResult({ success: true, message: result.message });
                showToast(result.message, 'success');
            } else {
                setTestResult({ success: false, message: result.message || 'Test email failed.' });
                showToast('Test email failed', 'error');
            }
        } catch (err) {
            setTestResult({ success: false, message: 'An error occurred while sending test email.' });
            showToast('An error occurred while testing', 'error');
        } finally {
            setIsTesting(false);
        }
    };

    return (
        <div className="dragwyb-settings-section">
            <h2>Email Delivery (SMTP) Settings</h2>
            <p className="dragwyb-settings-desc">
                Route form notification and confirmation emails through your custom SMTP server to ensure high inbox deliverability.
            </p>

            <ToggleSwitch
                label="Enable SMTP Delivery"
                description="Route Smart Form Builder emails via custom SMTP server."
                checked={isEnabled}
                onChange={val => handleSettingChange('smtp', 'smtp_enabled', val)}
            />

            {isEnabled && (
                <div style={{ marginTop: '20px' }}>
                    <div className="dragwyb-setting-row" style={{ flexDirection: 'column', alignItems: 'flex-start' }}>
                        <div className="dragwyb-setting-info" style={{ marginBottom: '8px' }}>
                            <h4>Guided Setup / Preset Provider</h4>
                            <p style={{ margin: 0, fontSize: '13px', color: '#666' }}>
                                Select your provider to pre-fill common server parameters and view instructions.
                            </p>
                        </div>
                        <div className="dragwyb-setting-control" style={{ width: '100%', maxWidth: '360px' }}>
                            <select
                                value={selectedProvider}
                                onChange={handleProviderChange}
                                style={{ width: '100%', padding: '8px 12px', borderRadius: '6px', border: '1px solid #ccc' }}
                            >
                                <option value="">— Choose a provider —</option>
                                {Object.keys(PROVIDERS).map(key => (
                                    <option key={key} value={key}>{PROVIDERS[key].name}</option>
                                ))}
                            </select>
                        </div>
                    </div>

                    {selectedProvider && PROVIDERS[selectedProvider] && (
                        <div style={{
                            background: '#f0f4fe',
                            border: '1px solid #c7d2fe',
                            borderRadius: '8px',
                            padding: '16px',
                            margin: '16px 0',
                            fontSize: '13px',
                            color: '#1e293b'
                        }}>
                            <h4 style={{ margin: '0 0 10px 0', color: '#3b82f6' }}>
                                <i className="fas fa-info-circle" style={{ marginRight: '6px' }}></i>
                                {PROVIDERS[selectedProvider].name} Setup Instructions
                            </h4>
                            <ol style={{ margin: 0, paddingLeft: '20px' }}>
                                {PROVIDERS[selectedProvider].steps.map((step, idx) => (
                                    <li key={idx} style={{ marginBottom: '4px' }}>{step}</li>
                                ))}
                            </ol>
                        </div>
                    )}

                    <div className="dragwyb-setting-row">
                        <div className="dragwyb-setting-info">
                            <h4>SMTP Host</h4>
                        </div>
                        <div className="dragwyb-setting-control">
                            <input
                                type="text"
                                value={getValue('smtp_host', '')}
                                onChange={e => handleSettingChange('smtp', 'smtp_host', e.target.value)}
                                placeholder="e.g. smtp.example.com"
                            />
                        </div>
                    </div>

                    <div className="dragwyb-setting-row">
                        <div className="dragwyb-setting-info">
                            <h4>SMTP Port</h4>
                        </div>
                        <div className="dragwyb-setting-control">
                            <input
                                type="number"
                                value={getValue('smtp_port', 587)}
                                onChange={e => handleSettingChange('smtp', 'smtp_port', parseInt(e.target.value, 10) || 587)}
                                placeholder="587"
                                style={{ width: '120px' }}
                            />
                        </div>
                    </div>

                    <div className="dragwyb-setting-row">
                        <div className="dragwyb-setting-info">
                            <h4>Encryption</h4>
                        </div>
                        <div className="dragwyb-setting-control">
                            <select
                                value={getValue('smtp_encryption', 'tls')}
                                onChange={e => handleSettingChange('smtp', 'smtp_encryption', e.target.value)}
                                style={{ width: '180px', padding: '8px 12px', borderRadius: '6px', border: '1px solid #ccc' }}
                            >
                                <option value="tls">TLS (Port 587)</option>
                                <option value="ssl">SSL (Port 465)</option>
                                <option value="none">None (Port 25)</option>
                            </select>
                        </div>
                    </div>

                    <ToggleSwitch
                        label="Server Requires Authentication"
                        checked={isAuth}
                        onChange={val => handleSettingChange('smtp', 'smtp_auth', val)}
                    />

                    {isAuth && (
                        <>
                            <div className="dragwyb-setting-row">
                                <div className="dragwyb-setting-info">
                                    <h4>SMTP Username</h4>
                                </div>
                                <div className="dragwyb-setting-control">
                                    <input
                                        type="text"
                                        value={getValue('smtp_username', '')}
                                        onChange={e => handleSettingChange('smtp', 'smtp_username', e.target.value)}
                                        placeholder="e.g. user@example.com"
                                    />
                                </div>
                            </div>

                            <div className="dragwyb-setting-row">
                                <div className="dragwyb-setting-info">
                                    <h4>SMTP Password</h4>
                                </div>
                                <div className="dragwyb-setting-control">
                                    <input
                                        type="password"
                                        value={getValue('smtp_password', '')}
                                        onChange={e => handleSettingChange('smtp', 'smtp_password', e.target.value)}
                                        placeholder="••••••••"
                                    />
                                </div>
                            </div>
                        </>
                    )}

                    <div className="dragwyb-setting-row">
                        <div className="dragwyb-setting-info">
                            <h4>From Email (Optional)</h4>
                        </div>
                        <div className="dragwyb-setting-control">
                            <input
                                type="email"
                                value={getValue('smtp_from_email', '')}
                                onChange={e => handleSettingChange('smtp', 'smtp_from_email', e.target.value)}
                                placeholder="noreply@yourdomain.com"
                            />
                        </div>
                    </div>

                    <div className="dragwyb-setting-row">
                        <div className="dragwyb-setting-info">
                            <h4>From Name (Optional)</h4>
                        </div>
                        <div className="dragwyb-setting-control">
                            <input
                                type="text"
                                value={getValue('smtp_from_name', '')}
                                onChange={e => handleSettingChange('smtp', 'smtp_from_name', e.target.value)}
                                placeholder="My Website"
                            />
                        </div>
                    </div>

                    <ToggleSwitch
                        label="Apply to all site emails"
                        description="Routes WooCommerce, password resets, and all WordPress emails through this SMTP."
                        checked={applyToAll}
                        onChange={val => handleSettingChange('smtp', 'smtp_apply_to_all', val)}
                    />

                    <div style={{
                        marginTop: '24px',
                        paddingTop: '20px',
                        borderTop: '1px solid #e2e8f0'
                    }}>
                        <h3>Send Test Email</h3>
                        <p style={{ fontSize: '13px', color: '#64748b' }}>
                            Save your settings first, then send a test email to verify your SMTP connection.
                        </p>
                        <div style={{ display: 'flex', gap: '10px', marginTop: '12px', alignItems: 'center' }}>
                            <input
                                type="email"
                                value={testEmail}
                                onChange={e => setTestEmail(e.target.value)}
                                placeholder="recipient@example.com"
                                style={{ width: '280px', padding: '8px 12px', borderRadius: '6px', border: '1px solid #ccc' }}
                            />
                            <button
                                type="button"
                                className="dragwyb-btn-primary"
                                onClick={handleTestEmail}
                                disabled={isTesting}
                                style={{ padding: '8px 16px' }}
                            >
                                {isTesting ? 'Sending...' : 'Send Test Email'}
                            </button>
                        </div>

                        {testResult && (
                            <div style={{
                                marginTop: '14px',
                                padding: '12px 16px',
                                borderRadius: '6px',
                                background: testResult.success ? '#f0fdf4' : '#fef2f2',
                                border: `1px solid ${testResult.success ? '#bbf7d0' : '#fecaca'}`,
                                color: testResult.success ? '#166534' : '#991b1b',
                                fontSize: '13px'
                            }}>
                                <strong>{testResult.success ? '✓ Success: ' : '✕ Error: '}</strong>
                                {testResult.message}
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
};

export default SmtpTab;
