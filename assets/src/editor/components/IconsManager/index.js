import React from 'react';

/**
 * IconsManager React component.
 *
 * Renders an SVG icon from the localized list if available,
 * otherwise falls back to a standard FontAwesome CSS-class icon.
 *
 * @param {Object} props
 * @param {Object|string} props.icon The icon object {icon: string, type: string} or CSS class string.
 * @param {string} [props.className=''] Extra CSS classes.
 */
const IconsManager = ({ icon, className = '', ...props }) => {
    if (!icon) {
        return null;
    }

    let iconName = '';
    let iconType = 'solid';

    if (typeof icon === 'object') {
        iconName = icon.icon || '';
        iconType = icon.type || 'solid';
    } else if (typeof icon === 'string') {
        // Handle FontAwesome class strings like "fas fa-user" or "fa-user"
        const parts = icon.trim().split(/\s+/);

        // Find a part starting with 'fa-'
        const faPart = parts.find(p => p.startsWith('fa-') && p !== 'fa');
        if (faPart) {
            iconName = faPart.substring(3);
        } else {
            // If it doesn't start with fa-, check if any non-prefix part is the icon name
            const nonPrefixParts = parts.filter(p => !['fas', 'far', 'fab', 'fa', 'dragwyb-icon'].includes(p));
            iconName = nonPrefixParts[0] || '';
        }

        // Map prefixes to types
        if (parts.includes('fab')) {
            iconType = 'brands';
        } else if (parts.includes('far')) {
            iconType = 'regular';
        } else if (parts.includes('dragwyb-icon')) {
            iconType = 'custom';
        } else {
            iconType = 'solid';
        }
    }

    if (!iconName) {
        return null;
    }

    // Check if we have SVG data localized for this icon
    const faIconsList = window.DragwybEditor?.faIconsList || {};
    const iconGroup = faIconsList[iconType];
    const iconData = iconGroup ? iconGroup[iconName] : null;

    if (iconData && Array.isArray(iconData)) {
        const width = iconData[0];
        const height = iconData[1];
        const path = iconData[4];

        // Combine default styles / classes to ensure it behaves nicely
        const svgClasses = `dragwyb-svg-icon dragwyb-svg-icon--${iconType} dragwyb-svg-icon--${iconName} ${className}`.trim();

        return (
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox={`0 0 ${width} ${height}`}
                fill="currentColor"
                className={svgClasses}
                aria-hidden="true"
                {...props}
            >
                <path d={path} />
            </svg>
        );
    }

    return null;
};

/**
 * Static method to get the valid icon groups.
 *
 * @return {string[]} Array of icon groups.
 */
IconsManager.iconsGroups = () => {
    return Object.keys(window.DragwybEditor?.faIconsList || {
        'solid': '',
        'brands': '',
        'regular': '',
        'custom': ''
    });
};

/**
 * Static method to retrieve all icon names belonging to a specific group/library.
 *
 * @param {string} group The icon group (solid, brands, regular, custom).
 * @return {string[]} Array of icon names.
 */
IconsManager.getIconsByGroup = (group) => {
    const faIconsList = window.DragwybEditor?.faIconsList || {};
    const groupData = faIconsList[group];

    if (!groupData) {
        return [];
    }
    if (Array.isArray(groupData)) {
        return groupData;
    }
    if (typeof groupData === 'object') {
        return Object.keys(groupData);
    }
    return [];
};

/**
 * Static method for rendering icons, similar to PHP IconsManager::render_icon.
 *
 * @param {Object|string} icon The icon object or class string.
 * @param {Object} [attributes={}] HTML attributes to pass to the element.
 * @param {string} [tag='i'] The HTML tag to render.
 * @return {JSX.Element} React element.
 */
IconsManager.Render = ({ icon, ...attributes }) => {
    return <IconsManager icon={icon} {...attributes} />;
};

/**
 * Alias static method for Render/render_icon.
 */
IconsManager.renderIcons = IconsManager.Render;

export default IconsManager;
