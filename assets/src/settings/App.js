import React, { useState, useEffect, useRef } from 'react';
import TabNavigation from './components/TabNavigation';
import TabContent from './components/TabContent';
import SidebarDetails from './components/SidebarDetails';
import Toast from './components/Toast';
import { FaWpforms, FaSave } from 'react-icons/fa';

const App = () => {
    const { restUrl, nonce, currentTab } = window.DragwybSettingsData || {};

    const validTabs = ['integrations', 'performance', 'fields_manager', 'smtp', 'gdpr_privacy', 'import_export'];

    const getInitialTab = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab') || currentTab;
        if (tabParam && validTabs.includes(tabParam)) {
            return tabParam;
        }
        return 'integrations';
    };

    const [settings, setSettings] = useState(null);
    const settingsRef = useRef(settings);
    const [isLoading, setIsLoading] = useState(true);
    const [activeTab, setActiveTabState] = useState(getInitialTab);
    const [toast, setToast] = useState({ show: false, message: '', type: 'success' });

    useEffect(() => {
        settingsRef.current = settings;
    }, [settings]);

    const handleTabChange = (newTab) => {
        if (!validTabs.includes(newTab)) return;
        setActiveTabState(newTab);
        const url = new URL(window.location.href);
        url.searchParams.set('tab', newTab);
        window.history.pushState({}, '', url.toString());
    };

    const handleSaveClick = (e) => {
        const btnWrapper = e.target.classList.contains('dragwyb-btn-primary-add') ? e.target : e.target.closest('.dragwyb-btn-primary-add');

        if (btnWrapper) {
            saveSettings(btnWrapper.querySelector('span'), settingsRef.current);
        }
    }

    useEffect(() => {
        fetchSettings();

        const handlePopState = () => {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            if (tabParam && validTabs.includes(tabParam)) {
                setActiveTabState(tabParam);
            }
        };

        const saveBtn = document.querySelector('.dragwyb-db-header-actions .dragwyb-btn-primary-add');
        if (saveBtn) {
            saveBtn.addEventListener('click', handleSaveClick);
        }

        window.addEventListener('popstate', handlePopState);
        return () => {
            window.removeEventListener('popstate', handlePopState);
            if (saveBtn) {
                saveBtn.removeEventListener('click', handleSaveClick);
            }
        };
    }, []);

    const fetchSettings = async () => {
        try {
            const response = await fetch(restUrl, {
                headers: {
                    'X-WP-Nonce': nonce
                }
            });
            const result = await response.json();
            if (result.status === 'success') {
                setSettings(result.data);
            }
        } catch (error) {
            showToast('Failed to load settings', 'error');
        } finally {
            setIsLoading(false);
        }
    };

    const saveSettings = async (ele, settings) => {
        if (!ele) return;
        ele.innerText = 'Saving...';
        try {
            const response = await fetch(restUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify(settings)
            });
            const result = await response.json();

            if (result.status === 'success') {
                setSettings(result.data);
                showToast(result.message || 'Settings saved successfully', 'success');
            } else {
                showToast(result.message || 'Failed to save settings', 'error');
            }
            ele.innerText = 'Save Settings';
        } catch (error) {
            showToast('An error occurred while saving', 'error');
        } finally {
            ele.innerText = 'Save Settings';
        }
    };

    const handleSettingChange = (tab, key, value) => {

        if (key === 'tracking_enabled') {
            const trackingNotice = document.querySelector('.dragwyb-tracking-notice');

            if (trackingNotice) {
                if (value) {
                    trackingNotice.classList.remove('show-notice');
                    trackingNotice.classList.add('hide-notice');
                } else {
                    trackingNotice.classList.remove('hide-notice');
                    trackingNotice.classList.add('show-notice');
                }
            }
        }
        setSettings(prev => ({
            ...prev,
            [tab]: {
                ...prev[tab],
                [key]: { ...prev[tab][key], value: value }
            }
        }));
    };

    const showToast = (message, type = 'success') => {
        setToast({ show: true, message, type });
        setTimeout(() => {
            setToast({ show: false, message: '', type: 'success' });
        }, 3000);
    };

    if (isLoading) {
        return (
            <div className="dragwyb-settings-loading">
                <div className="dragwyb-loading-spinner"></div>
                <p>Loading settings...</p>
            </div>
        );
    }

    return (
        <div className="dragwyb-settings-dashboard">
            {/* Horizontal Tabs Bar */}
            <TabNavigation activeTab={activeTab} setActiveTab={handleTabChange} />

            {/* Dashboard Content Body: 2 Columns */}
            <div className="dragwyb-settings-body-grid">
                <div className="dragwyb-settings-main-col">
                    <TabContent
                        activeTab={activeTab}
                        settings={settings}
                        handleSettingChange={handleSettingChange}
                        showToast={showToast}
                    />
                </div>
                <div className="dragwyb-settings-sidebar-col">
                    <SidebarDetails />
                </div>
            </div>

            {toast.show && <Toast message={toast.message} type={toast.type} />}
        </div>
    );
};

export default App;
