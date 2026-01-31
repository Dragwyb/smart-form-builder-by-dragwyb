import React from 'react';

/**
 * Heading Control
 * Used to separate sections in the settings sidebar.
 */
export default class HeadingControl extends DragwybEditor.editor.extends.ControlBase {

    controlName() {
        return 'heading';
    }

    render() {
        if (!this.shouldRender()) return <></>;

        const { settings } = this;
        // Default separator is 'none' if not specified
        const separator = settings.separator || 'none';

        return (
            <div className={`dragwyb-control dragwyb-control--heading dragwyb-control-separator-${separator}`}>
                <div className="dragwyb-heading-wrapper">
                    <span className="dragwyb-heading-title">
                        {settings.label}
                    </span>
                    <div className="dragwyb-heading-line"></div>
                </div>
            </div>
        );
    }
}