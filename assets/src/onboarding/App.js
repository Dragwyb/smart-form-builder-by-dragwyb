import React, { useState, useEffect } from 'react';

// Comprehensive Icon Component
const Icon = ({ name }) => {
    switch (name) {
        case 'check':
            return (
                <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12L3.41 13.41L9 19L21 7L19.59 5.59L9 16.17Z" /></svg>
            );
        case 'templates':
            return (
                <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-8 14H5v-6h6v6zm0-8H5V5h6v4zm8 8h-6v-4h6v4zm0-6h-6V5h6v6z" /></svg>
            );
        case 'font':
            return (
                <svg viewBox="0 0 24 24"><path d="M9.93 13.5h4.14L12 7.98zM20 2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-4.05 16.5l-1.14-3H9.17l-1.12 3H5.96l5.11-13h1.86l5.11 13h-2.09z" /></svg>
            );
        case 'mail':
            return (
                <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" /></svg>
            );
        case 'shield':
            return (
                <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8s0 .01 0 0z" /></svg>
            );
        case 'code':
            return (
                <svg viewBox="0 0 24 24"><path d="M9.4 16.6L4.8 12L9.4 7.4L8 6L2 12L8 18l1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4z" /></svg>
            );
        case 'export':
            return (
                <svg viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z" /></svg>
            );
        case 'users':
            return (
                <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" /></svg>
            );
        case 'screen':
            return (
                <svg viewBox="0 0 24 24"><path d="M21 3H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h5v2h8v-2h5c1.1 0 1.99-.9 1.99-2L23 5c0-1.1-.9-2-2-2zm0 14H3V5h18v12z" /></svg>
            );
        case 'eye':
            return (
                <svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" /></svg>
            );
        case 'clock':
            return (
                <svg viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z" /></svg>
            );
        case 'barchart':
            return (
                <svg viewBox="0 0 24 24"><path d="M5 9.2h3V19H5zM10.6 5h2.8v14h-2.8zM16.2 13H19v6h-2.8z" /></svg>
            );
        case 'bell':
            return (
                <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z" /></svg>
            );
        case 'chat':
            return (
                <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z" /></svg>
            );
        case 'briefcase':
            return (
                <svg viewBox="0 0 24 24"><path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z" /></svg>
            );
        case 'wordpress':
            return (
                <svg viewBox="0 0 19.22 19.22">
                    <g>
                        <path d="M18.029,5.13c-1.452-2.841-5.127-5.528-9.752-4.876C0.865,1.295-3.04,10.682,2.878,16.452   C10.036,23.431,23.25,15.328,18.029,5.13z M7.581,18.541c-3.828-0.652-7.065-4.636-7.141-8.708   C0.343,4.748,3.871,1.395,8.104,0.775c5.924-0.869,11.265,3.456,10.449,10.103C17.885,16.331,12.805,19.432,7.581,18.541z    M5.84,5.479C5.605,5.768,4.851,5.537,4.62,5.827c0.95,2.88,1.793,5.87,2.961,8.533c1.114-2.884,2.343-5.696,0.522-8.533   c-0.292-0.29-1.273,0.111-1.22-0.523c0.778-0.312,4.271-0.312,5.05,0c-0.002,0.578-0.982,0.177-1.219,0.523   c0.963,2.811,1.735,5.814,2.961,8.36c0.327-2.11,1.444-2.872,1.393-5.052c-0.055-2.484-3.17-4.662-0.174-5.748   C11.977,0.257,4.153,1.215,2.878,5.13C4.041,5.38,5.613,4.608,5.84,5.479z M13.676,16.452c3.17-1.164,5.382-6.925,2.96-10.452   C16.688,9.838,14.62,13.03,13.676,16.452z M5.84,16.8C4.671,13.206,3.468,9.648,2.007,6.35C0.334,10.7,2.473,15.388,5.84,16.8z    M7.231,17.322c1.154,0.604,3.683,0.419,4.876,0c-0.761-2.374-1.339-4.929-2.612-6.793C8.884,12.937,8.044,15.117,7.231,17.322z" />
                        <g>
                        </g>
                        <g>
                        </g>
                        <g>
                        </g>
                        <g>
                        </g>
                        <g>
                        </g>
                        <g>
                        </g>
                        <g>
                        </g>
                        <g>
                        </g>
                        <g>
                        </g>
                        <g>
                        </g>
                        <g>
                        </g>
                        <g>
                        </g>
                        <g>
                        </g>
                        <g>
                        </g>
                        <g>
                        </g>
                    </g>
                </svg>
            );
        case 'elementor':
            return (
                <svg viewBox="0 0 24 24"><path d="M3 3h18v18H3V3zm2 2v14h4V5H5zm6 0v3h8V5h-8zm0 5.5v3h8v-3h-8zm0 5.5v3h8v-3h-8z" /></svg>
            );
        case 'calendar':
            return (
                <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z" /></svg>
            );
        case 'gift':
            return (
                <svg viewBox="0 0 24 24"><path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.67C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h7v6h2V8h7v6z" /></svg>
            );
        default:
            return null;
    }
};

const App = () => {
    const data = window.DragwybOnboardingData || {};
    const totalSteps = 5;
    const dashboardUrl = data.dashboardUrl || 'admin.php?page=dragwyb-form-overview';

    // -------------------------------------------------------------
    // INITIAL STEP & TEMPLATE RESOLUTION (URL vs LOCALSTORAGE)
    // -------------------------------------------------------------
    const getStoredSetup = () => {
        try {
            const raw = localStorage.getItem('dragwyb_onboarding_setup');
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    };

    const getInitialSetup = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const urlStep = parseInt(urlParams.get('step'), 10);
        const stored = getStoredSetup();

        if (urlStep && urlStep >= 1 && urlStep <= totalSteps) {
            const template = stored?.template || 'contact';
            if (urlStep > 1) {
                localStorage.setItem('dragwyb_onboarding_setup', JSON.stringify({
                    step: urlStep,
                    template: template
                }));
            } else {
                localStorage.removeItem('dragwyb_onboarding_setup');
            }
            return { step: urlStep, template: template };
        } else {
            // URL does NOT have step parameter -> Clear localStorage and start from step 1
            localStorage.removeItem('dragwyb_onboarding_setup');
            return { step: 1, template: 'contact' };
        }
    };

    const initialSetup = getInitialSetup();
    const [currentStep, setCurrentStep] = useState(initialSetup.step);
    const [selectedTemplate, setSelectedTemplate] = useState(initialSetup.template);

    // -------------------------------------------------------------
    // UPDATE STEP & TEMPLATE IN STATE, URL & LOCALSTORAGE
    // -------------------------------------------------------------
    const updateStep = (newStep, newTemplate = selectedTemplate) => {
        const stepNum = Math.min(Math.max(newStep, 1), totalSteps);
        setCurrentStep(stepNum);

        const url = new URL(window.location.href);
        if (stepNum > 1) {
            url.searchParams.set('step', stepNum.toString());
            localStorage.setItem('dragwyb_onboarding_setup', JSON.stringify({
                step: stepNum,
                template: newTemplate
            }));
        } else {
            url.searchParams.delete('step');
            localStorage.removeItem('dragwyb_onboarding_setup');
        }
        window.history.pushState({}, '', url.toString());
    };

    const handleSelectTemplate = (templateId) => {
        setSelectedTemplate(templateId);
        if (currentStep > 1) {
            localStorage.setItem('dragwyb_onboarding_setup', JSON.stringify({
                step: currentStep,
                template: templateId
            }));
        }
    };

    // Listen to browser Back/Forward navigation
    useEffect(() => {
        const handlePopState = () => {
            const urlParams = new URLSearchParams(window.location.search);
            const urlStep = parseInt(urlParams.get('step'), 10);
            if (urlStep && urlStep >= 1 && urlStep <= totalSteps) {
                setCurrentStep(urlStep);
            } else {
                setCurrentStep(1);
            }
        };

        window.addEventListener('popstate', handlePopState);
        return () => window.removeEventListener('popstate', handlePopState);
    }, []);

    const handleSkipOrClose = () => {
        localStorage.removeItem('dragwyb_onboarding_setup');
        window.location.href = dashboardUrl;
    };

    const handleNext = () => {
        if (currentStep < totalSteps) {
            updateStep(currentStep + 1);
        }
    };

    const handlePrev = () => {
        if (currentStep > 1) {
            updateStep(currentStep - 1);
        }

        if (currentStep === 1) {
            handleSkipOrClose();
        }
    };

    const getProgressWidth = () => {
        return `${Math.min(100, ((currentStep) / (totalSteps)) * 100)}%`;
    };

    const updateOnboardSetupComplete = () => {
        const sendData = {
            action: 'dragwyb_onboard_setup_complete',
            _ajax_nonce: data.adminNonce,
        };

        fetch(data.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(sendData),
            credentials: 'same-origin',
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Success message handled by Toast
                } else {
                    console.error('Error updating onboarding setup complete:', result.data.message);
                }
            })
            .catch(error => {
                console.error('Error updating onboarding setup complete:', error);
            });
    };

    return (
        <div className="dragwyb-onboarding-fullscreen-wrapper" style={{ '--onboarding-completed-status': getProgressWidth() }}>
            <div className="dragwyb-onbarding-progress"></div>
            <div className="dragwyb-onboarding-container">
                {/* MAIN ONBOARDING CARD */}
                <div className="dragwyb-onboarding-main-card">

                    {/* STEP TRACKER */}
                    <div className="dragwyb-step-tracker">
                        {[
                            { num: 1 },
                            { num: 2 },
                            { num: 3 },
                            { num: 4 },
                            { num: 5 }
                        ].map((step) => {
                            const isActive = currentStep === step.num;
                            const isCompleted = currentStep > step.num;
                            return (
                                <div
                                    key={step.num}
                                    className={`dragwyb-tracker-item ${isActive ? 'is-active' : ''} ${isCompleted ? 'is-completed' : ''}`}
                                    onClick={() => updateStep(step.num)}
                                >
                                    <div className="dragwyb-tracker-circle">
                                        {isCompleted ? <Icon name="check" /> : step.num}
                                    </div>
                                </div>
                            );
                        })}
                    </div>

                    {/* STEP 1: OVERVIEW */}
                    {currentStep === 1 && (
                        <div className="dragwyb-step-content dragwyb-overview-step">
                            <div className="dragwyb-step-header">
                                <span className="dragwyb-step-tag">STEP 1 OF 5</span>
                                <h2 className="dragwyb-step-title">Everything You Need to Build Powerful Forms</h2>
                                <p className="dragwyb-step-description">
                                    From visual form building and smart automation to email delivery and analytics — create professional forms in minutes.
                                </p>
                            </div>

                            {/* Section 1: Highlighted Features */}
                            <div className="dragwyb-overview-section">
                                <div className="dragwyb-section-heading">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#f43f5e">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                    <span>Highlighted Features</span>
                                </div>

                                <div className="dragwyb-highlighted-grid">
                                    {/* Card 1 */}
                                    <div className="dragwyb-highlight-card">
                                        <div className="card-icon-circle">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                                <polyline points="22,6 12,13 2,6"></polyline>
                                            </svg>
                                        </div>
                                        <h4>Built-in SMTP</h4>
                                        <p>Reliable email delivery without any extra SMTP plugin.</p>
                                    </div>

                                    {/* Card 2 */}
                                    <div className="dragwyb-highlight-card">
                                        <div className="card-icon-circle">
                                            <svg viewBox="0 0 100 30" width="46" height="18" fill="none" stroke="currentColor" strokeWidth="2">
                                                <circle cx="15" cy="15" r="10" stroke="currentColor" fill="none" />
                                                <text x="15" y="19" fontSize="11" fontWeight="bold" textAnchor="middle" fill="currentColor" stroke="none">1</text>
                                                <line x1="27" y1="15" x2="43" y2="15" stroke="currentColor" />
                                                <circle cx="55" cy="15" r="10" stroke="currentColor" fill="none" />
                                                <text x="55" y="19" fontSize="11" fontWeight="bold" textAnchor="middle" fill="currentColor" stroke="none">2</text>
                                                <line x1="67" y1="15" x2="83" y2="15" stroke="currentColor" />
                                                <circle cx="90" cy="15" r="6" stroke="currentColor" fill="none" />
                                            </svg>
                                        </div>
                                        <h4>Multi-Step Forms</h4>
                                        <p>Create engaging multi-step forms with a simple drag &amp; drop builder.</p>
                                    </div>

                                    {/* Card 3 */}
                                    <div className="dragwyb-highlight-card">
                                        <div className="card-icon-circle">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M13 2L3 14h7v8l10-12h-7z" />
                                            </svg>
                                        </div>
                                        <h4>After Submission Actions</h4>
                                        <p>Save entries, send emails, redirect users and perform custom actions.</p>
                                    </div>

                                    {/* Card 4 */}
                                    <div className="dragwyb-highlight-card">
                                        <div className="card-icon-circle">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                                <rect x="9" y="2" width="6" height="6" rx="1" />
                                                <rect x="2" y="16" width="6" height="6" rx="1" />
                                                <rect x="16" y="16" width="6" height="6" rx="1" />
                                                <path d="M12 8v4M5 12h14M5 12v4M19 12v4" />
                                            </svg>
                                        </div>
                                        <h4>Smart Conditional Logic</h4>
                                        <p>Show or hide fields dynamically based on user input.</p>
                                    </div>

                                    {/* Card 5 */}
                                    <div className="dragwyb-highlight-card">
                                        <div className="card-icon-circle">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M5 12h3v7H5zM10.5 7h3v12h-3zM16 3h3v16h-3z" />
                                            </svg>
                                        </div>
                                        <h4>Built-in Analytics</h4>
                                        <p>Track submissions, visitors, sessions, pageviews and engagement.</p>
                                    </div>
                                </div>
                            </div>

                            {/* Section 2: More Powerful Capabilities */}
                            <div className="dragwyb-overview-section">
                                <div className="dragwyb-section-heading">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#f43f5e">
                                        <path d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z" />
                                    </svg>
                                    <span>More Powerful Capabilities</span>
                                </div>

                                <div className="dragwyb-capabilities-grid">
                                    {/* 1 */}
                                    <div className="dragwyb-capability-card">
                                        <div className="cap-icon-box">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <rect x="3" y="3" width="18" height="18" rx="2" strokeDasharray="3 3" />
                                                <path d="M15 15l5 5m-2.5-7.5l-5 5" strokeLinecap="round" />
                                            </svg>
                                        </div>
                                        <div className="cap-info">
                                            <h4>Drag &amp; Drop Visual Editor</h4>
                                        </div>
                                    </div>

                                    {/* 2 */}
                                    <div className="dragwyb-capability-card">
                                        <div className="cap-icon-box">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <rect x="2" y="3" width="14" height="11" rx="1" />
                                                <rect x="14" y="8" width="8" height="12" rx="1" />
                                            </svg>
                                        </div>
                                        <div className="cap-info">
                                            <h4>Responsive Live Preview</h4>
                                        </div>
                                    </div>

                                    {/* 3 */}
                                    <div className="dragwyb-capability-card">
                                        <div className="cap-icon-box text-badge">25+</div>
                                        <div className="cap-info">
                                            <h4>25+ Native Form Fields</h4>
                                        </div>
                                    </div>

                                    {/* 4 */}
                                    <div className="dragwyb-capability-card">
                                        <div className="cap-icon-box">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                <polyline points="14 2 14 8 20 8" />
                                                <line x1="8" y1="13" x2="16" y2="13" />
                                                <line x1="8" y1="17" x2="16" y2="17" />
                                            </svg>
                                        </div>
                                        <div className="cap-info">
                                            <h4>Built-in Templates</h4>
                                        </div>
                                    </div>

                                    {/* 5 */}
                                    <div className="dragwyb-capability-card">
                                        <div className="cap-icon-box">
                                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M7 14c-1.66 0-3 1.34-3 3 0 1.31-1.16 2-2 2 .92 1.22 2.49 2 4 2 2.21 0 4-1.79 4-4 0-1.66-1.34-3-3-3zm13.71-9.37l-1.34-1.34a.996.996 0 0 0-1.41 0L9 12.25 11.75 15l8.96-8.96c.39-.39.39-1.02 0-1.41z" /></svg>
                                        </div>
                                        <div className="cap-info">
                                            <h4>Preset Styles</h4>
                                        </div>
                                    </div>

                                    {/* 6 */}
                                    <div className="dragwyb-capability-card">
                                        <div className="cap-icon-box text-badge">Aa</div>
                                        <div className="cap-info">
                                            <h4>Separate Field &amp; Global Styles</h4>
                                        </div>
                                    </div>

                                    {/* 7 */}
                                    <div className="dragwyb-capability-card">
                                        <div className="cap-icon-box">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                                <polyline points="22,6 12,13 2,6" />
                                            </svg>
                                        </div>
                                        <div className="cap-info">
                                            <h4>User &amp; Admin Emails</h4>
                                        </div>
                                    </div>

                                    {/* 8 */}
                                    <div className="dragwyb-capability-card">
                                        <div className="cap-icon-box">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                                <path d="M9 12l2 2 4-4" />
                                            </svg>
                                        </div>
                                        <div className="cap-info">
                                            <h4>CAPTCHA Protection</h4>
                                        </div>
                                    </div>

                                    {/* 9 */}
                                    <div className="dragwyb-capability-card">
                                        <div className="cap-icon-box text-badge">[/]</div>
                                        <div className="cap-info">
                                            <h4>Shortcode Support</h4>
                                        </div>
                                    </div>

                                    {/* 10 */}
                                    <div className="dragwyb-capability-card">
                                        <div className="cap-icon-box">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M7 16V4m0 0L3 8m4-4l4 4m6 4v12m0 0l4-4m-4 4l-4-4" />
                                            </svg>
                                        </div>
                                        <div className="cap-info">
                                            <h4>Import &amp; Export Forms</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* STEP 2: ANALYTICS */}
                    {currentStep === 2 && (
                        <div className="dragwyb-step-content dragwyb-analytics-section">
                            <div className="dragwyb-step-header">
                                <span className="dragwyb-step-tag">STEP 2 OF 5</span>
                                <h2 className="dragwyb-step-title">Analytics & Traffic Tracking</h2>
                                <p className="dragwyb-step-description">
                                    Track performance, understand visitor behavior, and improve conversions.
                                </p>
                            </div>

                            <h3 className="section-subtitle-heading">What You Can Track</h3>

                            <div className="analytics-track-grid">
                                <div className="track-card">
                                    <div className="track-icon"><Icon name="export" /></div>
                                    <div className="track-info">
                                        <h4>Form Submissions</h4>
                                        <p>Track the total number of form submissions.</p>
                                    </div>
                                </div>

                                <div className="track-card">
                                    <div className="track-icon"><Icon name="users" /></div>
                                    <div className="track-info">
                                        <h4>Unique Visitors</h4>
                                        <p>See how many unique visitors interacted with your forms.</p>
                                    </div>
                                </div>

                                <div className="track-card">
                                    <div className="track-icon"><Icon name="screen" /></div>
                                    <div className="track-info">
                                        <h4>Total Sessions</h4>
                                        <p>Monitor total sessions generated on your forms.</p>
                                    </div>
                                </div>

                                <div className="track-card">
                                    <div className="track-icon"><Icon name="eye" /></div>
                                    <div className="track-info">
                                        <h4>Total Pageviews</h4>
                                        <p>Track total pageviews related to your forms.</p>
                                    </div>
                                </div>

                                <div className="track-card">
                                    <div className="track-icon"><Icon name="clock" /></div>
                                    <div className="track-info">
                                        <h4>Avg. Session Duration</h4>
                                        <p>Understand how much time visitors spend on your forms.</p>
                                    </div>
                                </div>

                                <div className="track-card">
                                    <div className="track-icon"><Icon name="barchart" /></div>
                                    <div className="track-info">
                                        <h4>Bounce Rate</h4>
                                        <p>Analyze bounce rate to measure engagement quality.</p>
                                    </div>
                                </div>
                            </div>

                            <h3 className="section-subtitle-heading">Additional Tracking Insights</h3>

                            <div className="insights-chips-row">
                                <div className="insight-chip"><Icon name="check" /> Traffic Overview</div>
                                <div className="insight-chip"><Icon name="check" /> Top Pages</div>
                                <div className="insight-chip"><Icon name="check" /> Traffic Sources</div>
                                <div className="insight-chip"><Icon name="check" /> Devices</div>
                                <div className="insight-chip"><Icon name="check" /> Live Visitors</div>
                            </div>

                            <div className="privacy-banner-card">
                                <div className="privacy-icon"><Icon name="shield" /></div>
                                <div className="privacy-text">
                                    <h4>Privacy Focused</h4>
                                    <p>All analytics are 100% GDPR-compliant and privacy focused. We respect your visitors' data.</p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* STEP 3: TEMPLATE */}
                    {currentStep === 3 && (
                        <div className="dragwyb-step-content dragwyb-templates-section">
                            <div className="dragwyb-step-header">
                                <span className="dragwyb-step-tag">STEP 3 OF 5</span>
                                <h2 className="dragwyb-step-title">Choose a Template</h2>
                                <p className="dragwyb-step-description">
                                    Select a template to get started quickly. You can customize everything later.
                                </p>
                            </div>

                            <div className="templates-2x2-grid">
                                <div
                                    className={`tpl-card ${selectedTemplate === 'contact' ? 'selected' : ''}`}
                                    onClick={() => handleSelectTemplate('contact')}
                                >
                                    {selectedTemplate === 'contact' && <div className="tpl-check-badge">✓</div>}
                                    <div className="tpl-icon-box"><Icon name="mail" /></div>
                                    <div className="tpl-body">
                                        <h4>Contact Form</h4>
                                        <p>Name, Email, Phone, Subject, Message & Consent</p>
                                    </div>
                                </div>

                                <div
                                    className={`tpl-card ${selectedTemplate === 'marketing' ? 'selected' : ''}`}
                                    onClick={() => handleSelectTemplate('marketing')}
                                >
                                    {selectedTemplate === 'marketing' && <div className="tpl-check-badge">✓</div>}
                                    <div className="tpl-icon-box"><Icon name="bell" /></div>
                                    <div className="tpl-body">
                                        <h4>Marketing Form</h4>
                                        <p>Lead generation with Work Email & Source tracking</p>
                                    </div>
                                </div>

                                <div
                                    className={`tpl-card ${selectedTemplate === 'feedback' ? 'selected' : ''}`}
                                    onClick={() => handleSelectTemplate('feedback')}
                                >
                                    {selectedTemplate === 'feedback' && <div className="tpl-check-badge">✓</div>}
                                    <div className="tpl-icon-box"><Icon name="chat" /></div>
                                    <div className="tpl-body">
                                        <h4>Feedback Form</h4>
                                        <p>Customer rating scale & detailed feedback text.</p>
                                    </div>
                                </div>

                                <div
                                    className={`tpl-card ${selectedTemplate === 'business' ? 'selected' : ''}`}
                                    onClick={() => handleSelectTemplate('bussiness')}
                                >
                                    {selectedTemplate === 'bussiness' && <div className="tpl-check-badge">✓</div>}
                                    <div className="tpl-icon-box"><Icon name="briefcase" /></div>
                                    <div className="tpl-body">
                                        <h4>Business Form</h4>
                                        <p>Quote requests with Budget & File attachment.</p>
                                    </div>
                                </div>
                            </div>

                            {/* BLANK FORM FULL WIDTH CARD */}
                            <div
                                className={`blank-form-card ${selectedTemplate === 'blank' ? 'selected' : ''}`}
                                onClick={() => handleSelectTemplate('blank')}
                            >
                                {selectedTemplate === 'blank' && <div className="tpl-check-badge">✓</div>}
                                <div className="blank-left">
                                    <div className="blank-icon-box">
                                        <Icon name="templates" />
                                        <span className="plus-badge">+</span>
                                    </div>
                                    <div className="blank-info">
                                        <h4>Blank Form</h4>
                                        <p>Start from scratch with a blank canvas and build your own custom form.</p>
                                    </div>
                                </div>

                                <div className="blank-features-right">
                                    <div className="blank-feat-item">
                                        <div className="feat-title"><Icon name="check" /> Complete Freedom</div>
                                        <div className="feat-desc">Add any fields you need</div>
                                    </div>
                                    <div className="blank-feat-item">
                                        <div className="feat-title"><Icon name="check" /> Fully Customizable</div>
                                        <div className="feat-desc">Design and style your form your way</div>
                                    </div>
                                    <div className="blank-feat-item">
                                        <div className="feat-title"><Icon name="check" /> Save Time</div>
                                        <div className="feat-desc">Reuse as a base for future forms</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* STEP 4: EMBED */}
                    {currentStep === 4 && (
                        <div className="dragwyb-step-content dragwyb-embed-section">
                            <div className="dragwyb-step-header">
                                <span className="dragwyb-step-tag">STEP 4 OF 5</span>
                                <h2 className="dragwyb-step-title">Embed Your Form Anywhere</h2>
                                <p className="dragwyb-step-description">
                                    Add your forms to any page, post, or widget area using the methods below.
                                </p>
                            </div>

                            <div className="embed-top-3-grid">
                                <div className="embed-block-card">
                                    <div className="embed-block-header">
                                        <div className="card-header-icon"><Icon name="wordpress" /></div>
                                        <h4>Gutenberg Block</h4>
                                    </div>
                                    <p>Add the Smart Form block in the Gutenberg editor.</p>
                                </div>

                                <div className="embed-block-card">
                                    <div className="embed-block-header">
                                        <div className="card-header-icon"><Icon name="elementor" /></div>
                                        <h4>Elementor Widget</h4>
                                    </div>
                                    <p>Search for "Smart Forms" in the Elementor widget.</p>
                                </div>

                                <div className="embed-block-card">
                                    <div className="embed-block-header">
                                        <div className="card-header-icon"><Icon name="code" /></div>
                                        <h4>Shortcode</h4>
                                    </div>
                                    <p>Copy and paste the shortcode anywhere on your site.</p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* STEP 5: LAUNCH */}
                    {currentStep === 5 && (
                        <div className="dragwyb-step-content dragwyb-launch-section">
                            <div className="dragwyb-step-header">
                                <span className="dragwyb-step-tag">STEP 5 OF 5</span>
                                <h2 className="dragwyb-step-title">You're All Set!</h2>
                                <p className="dragwyb-step-description">
                                    Your Smart Form Builder is ready to create amazing forms.
                                </p>
                            </div>

                            <div className="launch-hero-graphic">
                                <div className="check-hero-icon"><Icon name="check" /></div>
                            </div>

                            <div className="launch-3-cards-grid">
                                <div className="benefit-card">
                                    <div className="benefit-icon"><Icon name="calendar" /></div>
                                    <div className="benefit-info">
                                        <h4>Create Unlimited Forms</h4>
                                        <p>Build and manage unlimited forms for your site.</p>
                                    </div>
                                </div>

                                <div className="benefit-card">
                                    <div className="benefit-icon"><Icon name="font" /></div>
                                    <div className="benefit-info">
                                        <h4>Powerful & Flexible</h4>
                                        <p>Advanced features to handle any requirement.</p>
                                    </div>
                                </div>

                                <div className="benefit-card">
                                    <div className="benefit-icon"><Icon name="shield" /></div>
                                    <div className="benefit-info">
                                        <h4>Dedicated Support</h4>
                                        <p>We're here to help you succeed.</p>
                                    </div>
                                </div>
                            </div>

                            <div className="launch-callout-banner">
                                <div className="banner-left">
                                    <div className="gift-icon-box"><Icon name="gift" /></div>
                                    <div className="banner-info">
                                        <h4>Ready to build your first form?</h4>
                                        <p>Let's create a form and grow your business.</p>
                                    </div>
                                </div>
                                <a
                                    href={`${data.editorUrl}&setup-template=${selectedTemplate}`}
                                    className="banner-btn-link"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        updateOnboardSetupComplete();
                                        localStorage.removeItem('dragwyb_onboarding_setup');
                                        window.location.href = e.target.href;
                                    }}
                                >
                                    Create Your First Form →
                                </a>
                            </div>
                        </div>
                    )}

                    {/* FOOTER BAR WITH PAGINATION DOTS & BUTTONS (HIDDEN ON STEP 5) */}
                    {currentStep < totalSteps && (
                        <div className="dragwyb-onboarding-footer">
                            <button
                                className="dragwyb-btn-prev"
                                onClick={handlePrev}
                            >
                                {currentStep === 1 ? '← Skip Setup' : '← Previous'}
                            </button>

                            <button
                                className="dragwyb-btn-continue"
                                onClick={handleNext}
                            >
                                Continue →
                            </button>
                        </div>
                    )}

                </div>

            </div>
        </div>
    );
};

export default App;
