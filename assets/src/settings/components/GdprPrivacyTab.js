import React from 'react';
import RenderSettingItem from './RenderSettingItem';

const GdprPrivacyTab = ({ settings, handleSettingChange }) => {
    const gdprData = settings?.gdpr_privacy || {};
    const i18n = window.DragwybSettingsData?.i18n || {};
    const gdprI18n = i18n.gdpr || {};

    return (
        <div className="dragwyb-settings-section">
            <h2>{gdprI18n.title || 'GDPR & Privacy Controls'}</h2>
            <p className="dragwyb-settings-desc">
                {gdprI18n.shortcode_description || gdprI18n.description || i18n.shortcode_description || 'Configure visitor tracking, cookie behavior, and data protection settings to comply with GDPR, CCPA, and global privacy regulations.'}
            </p>

            {Object.keys(gdprData).map(key => (
                <RenderSettingItem
                    key={key}
                    itemKey={key}
                    itemData={gdprData[key]}
                    tabKey="gdpr_privacy"
                    handleSettingChange={handleSettingChange}
                />
            ))}
        </div>
    );
};

export default GdprPrivacyTab;
