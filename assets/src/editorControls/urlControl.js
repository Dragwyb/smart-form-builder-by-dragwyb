import { useState } from 'react'; // Assuming you can use hooks, or convert to class state if strictly class-based

export default class UrlControl extends DragwybEditor.editor.extends.ControlBase {

    controlName() {
        return 'url';
    }

    constructor(props) {
        super(props);
        // Add local state for toggling the "More Options" view
        this.state = {
            ...this.state,
            showOptions: false
        };
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        // Ensure we have a valid object structure
        const value = this.state.value || { url: '', is_external: false, nofollow: false };

        return (
            <div className="dragwyb-control dragwyb-control--url" id={`control-${id}`}>

                <this.RenderLabel
                    attr={
                        { htmlFor: id }
                    }
                />

                <div className="dragwyb-control__content">
                    <div className="dragwyb-url-input-wrapper">
                        {/* Main URL Input */}
                        <input
                            type="text"
                            className="dragwyb-control__input"
                            placeholder={settings.placeholder || 'https://'}
                            value={value.url || ''}
                            onChange={(e) => this.handleInputChange('url', e.target.value)}
                        />

                        {/* Toggle Options Button */}
                        <button
                            type="button"
                            className={`dragwyb-url-toggle ${this.state.showOptions ? 'active' : ''}`}
                            onClick={() => this.setState({ showOptions: !this.state.showOptions })}
                            title="Link Options"
                        >
                            <i className="fa fa-cog"></i>
                        </button>
                    </div>

                    {/* Advanced Options Panel */}
                    {this.state.showOptions && (
                        <div className="dragwyb-url-options">
                            <div className="dragwyb-url-option">
                                <label>
                                    <input
                                        type="checkbox"
                                        checked={value.is_external || false}
                                        onChange={(e) => this.handleInputChange('is_external', e.target.checked)}
                                    />
                                    <span>Open in new window</span>
                                </label>
                            </div>
                            <div className="dragwyb-url-option">
                                <label>
                                    <input
                                        type="checkbox"
                                        checked={value.nofollow || false}
                                        onChange={(e) => this.handleInputChange('nofollow', e.target.checked)}
                                    />
                                    <span>Add nofollow</span>
                                </label>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        );
    }

    handleInputChange(key, newValue) {
        // Clone the current state object to avoid mutation
        const currentData = {
            url: '',
            is_external: false,
            nofollow: false,
            ...(this.state.value || {})
        };

        // Update specific key
        currentData[key] = newValue;

        // Save entire object
        this.updateControlHandler(this.id, currentData);
    }
}