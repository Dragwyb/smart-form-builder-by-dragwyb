import React from 'react';
import RenderSettingItem from './RenderSettingItem';

export const isValueMatch = (currentVal, expectedVal) => {
    if (expectedVal === true || expectedVal === 'yes') {
        return currentVal === true || currentVal === 'yes';
    }
    if (expectedVal === false || expectedVal === 'no') {
        return currentVal === false || currentVal === 'no' || currentVal === undefined || currentVal === null || currentVal === '';
    }
    return String(currentVal) === String(expectedVal);
};

export const evaluateSettingCondition = (condition, tabSettings = {}, allSettings = {}) => {
    if (!condition || typeof condition !== 'object' || Object.keys(condition).length === 0) {
        return true;
    }

    const keys = Object.keys(condition);

    for (let rawKey of keys) {
        const isNot = rawKey.endsWith('!');
        const targetKey = isNot ? rawKey.slice(0, -1) : rawKey;
        const expectedVal = condition[rawKey];

        let targetItem = tabSettings[targetKey];
        if (!targetItem && allSettings) {
            for (let tKey in allSettings) {
                if (allSettings[tKey] && allSettings[tKey][targetKey]) {
                    targetItem = allSettings[tKey][targetKey];
                    break;
                }
            }
        }

        const currentVal = targetItem?.value !== undefined ? targetItem.value : (targetItem?.default ?? undefined);

        let conditionMet = false;

        if (Array.isArray(expectedVal)) {
            const matchesAny = expectedVal.some(val => isValueMatch(currentVal, val));
            conditionMet = isNot ? !matchesAny : matchesAny;
        } else {
            const matches = isValueMatch(currentVal, expectedVal);
            conditionMet = isNot ? !matches : matches;
        }

        if (!conditionMet) {
            return false;
        }
    }

    return true;
};

export const groupSettingsIntoSections = (tabSettings) => {
    if (!tabSettings) return [];

    const keys = Object.keys(tabSettings);
    const sections = [];
    let currentSection = null;

    keys.forEach((key) => {
        const item = tabSettings[key];
        if (!item) return;

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

const RenderSettingsGroup = ({ tabSettings, allSettings, tabKey, handleSettingChange, renderExtraAfterItem, isItemVisible }) => {
    if (!tabSettings) return null;

    const sections = groupSettingsIntoSections(tabSettings);

    return (
        <>
            {sections.map((section, idx) => {
                const visibleItems = section.items.filter(({ key, data }) => {
                    const cond = data.condition || data.conditions;
                    if (cond && !evaluateSettingCondition(cond, tabSettings, allSettings)) {
                        return false;
                    }
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
