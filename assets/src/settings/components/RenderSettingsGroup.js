import React from 'react';
import RenderSettingItem from './RenderSettingItem';

export const groupSettingsIntoSections = (tabSettings) => {
    if (!tabSettings) return [];

    const keys = Object.keys(tabSettings);
    const sections = [];
    let currentSection = null;

    keys.forEach((key) => {
        const item = tabSettings[key];
        if (!item) return;

        // Check if a new section starts
        if (item.start_section || !currentSection) {
            if (currentSection) {
                sections.push(currentSection);
            }
            currentSection = {
                title: typeof item.start_section === 'string' ? item.start_section : null,
                hasContainer: Boolean(item.start_section),
                containerClass: item.class || item.container_class || item.containerClass || '',
                items: []
            };
        }

        currentSection.items.push({ key, data: item });

        // Check if section explicitly ends
        if (item.end_section) {
            sections.push(currentSection);
            currentSection = null;
        }
    });

    if (currentSection) {
        sections.push(currentSection);
    }

    return sections;
};

const RenderSettingsGroup = ({ tabSettings, tabKey, handleSettingChange, renderExtraAfterItem, isItemVisible }) => {
    if (!tabSettings) return null;

    const sections = groupSettingsIntoSections(tabSettings);

    return (
        <>
            {sections.map((section, idx) => {
                const visibleItems = section.items.filter(({ key, data }) => {
                    return !isItemVisible || isItemVisible(key, data);
                });

                if (visibleItems.length === 0) return null;

                const renderContent = () => (
                    <>
                        {section.title && (
                            <h3 style={{ fontSize: '16px', fontWeight: '600', color: '#1e293b', marginBottom: '16px', marginTop: 0 }}>
                                {section.title}
                            </h3>
                        )}
                        {visibleItems.map(({ key, data }) => (
                            <React.Fragment key={key}>
                                <RenderSettingItem
                                    itemKey={key}
                                    itemData={data}
                                    tabKey={tabKey}
                                    handleSettingChange={handleSettingChange}
                                    hideStartSectionHeader={section.hasContainer}
                                />
                                {renderExtraAfterItem && renderExtraAfterItem(key, data)}
                            </React.Fragment>
                        ))}
                    </>
                );

                if (section.hasContainer) {
                    const containerClassName = `dragwyb-settings-container ${section.containerClass || ''}`.trim();
                    return (
                        <div key={idx} className={containerClassName}>
                            {renderContent()}
                        </div>
                    );
                }

                return (
                    <React.Fragment key={idx}>
                        {renderContent()}
                    </React.Fragment>
                );
            })}
        </>
    );
};

export default RenderSettingsGroup;
