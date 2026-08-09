import React, { useState, useEffect } from 'react';
import TabNavigation from './components/TabNavigation';
import TabContent from './components/TabContent';
import Toast from './components/Toast';

const App = () => {
    const { restUrl, nonce, currentTab } = window.DragwybSettingsData || {};

    const validTabs = ['integrations', 'performance', 'fields_manager', 'import_export'];

    const getInitialTab = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab') || currentTab;
        if (tabParam && validTabs.includes(tabParam)) {
            return tabParam;
        }
        return 'integrations';
    };

    const [settings, setSettings] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isSaving, setIsSaving] = useState(false);
    const [activeTab, setActiveTabState] = useState(getInitialTab);
    const [toast, setToast] = useState({ show: false, message: '', type: 'success' });

    const handleTabChange = (newTab) => {
        if (!validTabs.includes(newTab)) return;
        setActiveTabState(newTab);
        const url = new URL(window.location.href);
        url.searchParams.set('tab', newTab);
        window.history.pushState({}, '', url.toString());
    };

    useEffect(() => {
        fetchSettings();

        const handlePopState = () => {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            if (tabParam && validTabs.includes(tabParam)) {
                setActiveTabState(tabParam);
            }
        };

        window.addEventListener('popstate', handlePopState);
        return () => window.removeEventListener('popstate', handlePopState);
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

    const saveSettings = async () => {
        setIsSaving(true);
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
        } catch (error) {
            showToast('An error occurred while saving', 'error');
        } finally {
            setIsSaving(false);
        }
    };

    const handleSettingChange = (tab, key, value) => {
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
        return <div className="dragwyb-settings-loading">Loading settings...</div>;
    }

    return (
        <div className="dragwyb-settings-dashboard">
            <div className="dragwyb-settings-header">
                <h1>Smart Form Builder Settings</h1>
                <button
                    className="dragwyb-btn-primary"
                    onClick={saveSettings}
                    disabled={isSaving}
                >
                    {isSaving ? 'Saving...' : 'Save Settings'}
                </button>
            </div>

            <div className="dragwyb-settings-body">
                <TabNavigation activeTab={activeTab} setActiveTab={handleTabChange} />
                <TabContent
                    activeTab={activeTab}
                    settings={settings}
                    handleSettingChange={handleSettingChange}
                    showToast={showToast}
                />
            </div>

            {toast.show && <Toast message={toast.message} type={toast.type} />}
        </div>
    );
};

export default App;
