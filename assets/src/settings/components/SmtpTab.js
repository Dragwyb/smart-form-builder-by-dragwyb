import React, { useState } from 'react';
import RenderSettingsGroup from './RenderSettingsGroup';

const ProviderSteps = {
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
    const i18n = window.DragwybSettingsData?.i18n || {};
    const smtpI18n = i18n.smtp || {};

    const getValue = (key, defaultVal = '') => {
        return smtpData[key]?.value !== undefined ? smtpData[key].value : (smtpData[key]?.default ?? defaultVal);
    };

    const isEnabled = getValue('smtp_enabled', false) === true || getValue('smtp_enabled', 'no') === 'yes';

    const [selectedProvider, setSelectedProvider] = useState(settings?.smtp?.smtp_provider?.value || '');
    const [testEmail, setTestEmail] = useState('');
    const [isTesting, setIsTesting] = useState(false);
    const [testResult, setTestResult] = useState(null);

    const handleProviderChange = (pKey) => {
        setSelectedProvider(pKey);
        if (pKey && ProviderSteps[pKey]) {
            const p = ProviderSteps[pKey];
            handleSettingChange('smtp', 'smtp_provider', pKey);
            handleSettingChange('smtp', 'smtp_host', p.host);
            handleSettingChange('smtp', 'smtp_port', p.port);
            handleSettingChange('smtp', 'smtp_encryption', p.encryption);
            handleSettingChange('smtp', 'smtp_auth', p.auth);
        }
    };

    const handleSettingChangeHandler = (tab, key, value) => {
        if (key === 'smtp_provider') {
            handleProviderChange(value);
        }
        handleSettingChange(tab, key, value);
    };

    const handleTestEmail = async () => {
        if (!testEmail) {
            showToast(smtpI18n.enter_test_email_error || 'Please enter a test email address', 'error');
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
                setTestResult({ success: false, message: result.message || smtpI18n.test_email_failed || 'Test email failed.' });
                showToast('Test email failed', 'error');
            }
        } catch (err) {
            setTestResult({ success: false, message: smtpI18n.test_email_error || 'An error occurred while sending test email.' });
            showToast('An error occurred while testing', 'error');
        } finally {
            setIsTesting(false);
        }
    };

    const extraFields = Object.keys(smtpData);

    return (
        <div className="dragwyb-settings-section">
            <h2>{smtpI18n.title || 'Email Delivery (SMTP) Settings'}</h2>
            <p className="dragwyb-settings-desc">
                {smtpI18n.shortcode_description || smtpI18n.description || i18n.shortcode_description || 'Route form notification and confirmation emails through your custom SMTP server to ensure high inbox deliverability.'}
            </p>

            <div>
                <RenderSettingsGroup
                    tabSettings={smtpData}
                    allSettings={settings}
                    tabKey="smtp"
                    handleSettingChange={handleSettingChangeHandler}
                    isItemVisible={(key) => isEnabled || key === 'smtp_enabled'}
                    renderExtraAfterItem={(key, data) => (
                        (key === 'smtp_provider' && selectedProvider && ProviderSteps[selectedProvider]) ? (
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
                                    {data?.label || 'Provider'} {smtpI18n.setup_instructions || 'Setup Instructions'}
                                </h4>
                                <ol style={{ margin: 0, paddingLeft: '20px' }}>
                                    {ProviderSteps[selectedProvider]?.steps?.map((step, idx) => (
                                        <li key={idx} style={{ marginBottom: '4px' }}>{step}</li>
                                    ))}
                                </ol>
                            </div>
                        ) : null
                    )}
                />

                {isEnabled &&
                    <div className='dragwyb-setting-row'>
                        <div className="dragwyb-settings-section">
                            <div className='dragwyb-settings-container dragwyb-container-danger'>
                                <h3>{smtpI18n.send_test_email_title || 'Send Test Email'}</h3>
                                <p style={{ fontSize: '13px', color: '#64748b' }}>
                                    {smtpI18n.send_test_email_desc || 'Save your settings first, then send a test email to verify your SMTP connection.'}
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
                                    >
                                        {isTesting ? (smtpI18n.sending || 'Sending...') : (smtpI18n.send_test_btn || 'Send Test Email')}
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
                    </div>
                }
            </div>
        </div>
    );
};

export default SmtpTab;
