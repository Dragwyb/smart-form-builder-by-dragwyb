const PreviewLoading = () => {
    return (
        <div className="dragwyb-editor-loader-wrapper">
            <div className="dragwyb-editor-loader-container">
                {/* Visual Stage */}
                <div className="dragwyb-editor-loader-stage">
                    <svg className="dragwyb-editor-loader-ring-svg" viewBox="0 0 300 300">
                        <circle
                            className="dragwyb-editor-loader-ring-fill"
                            cx="150"
                            cy="150"
                            r="140"
                        />
                    </svg>

                    {/* Professional Form Preview */}
                    <div className="dragwyb-editor-loader-form-preview">
                        <div className="dragwyb-editor-loader-field-line dragwyb-editor-loader-w-30"></div>

                        <div className="dragwyb-editor-loader-form-row">
                            <div className="dragwyb-editor-loader-field-line dragwyb-editor-loader-w-50"></div>
                            <div className="dragwyb-editor-loader-field-line dragwyb-editor-loader-w-50"></div>
                        </div>

                        <div className="dragwyb-editor-loader-field-line dragwyb-editor-loader-w-100"></div>
                        <div className="dragwyb-editor-loader-field-line dragwyb-editor-loader-w-100"></div>
                        <div className="dragwyb-editor-loader-field-line dragwyb-editor-loader-w-100"></div>

                        <div className="dragwyb-editor-loader-field-line dragwyb-editor-loader-btn-fill"></div>
                    </div>
                </div>

                {/* Text Content */}
                <div className="dragwyb-editor-loader-bottom-ui">
                    <h1 className="dragwyb-editor-loader-loading-title">
                        Form Builder Loading
                        <span className="dragwyb-editor-loader-dot-ani">.</span>
                        <span className="dragwyb-editor-loader-dot-ani">.</span>
                        <span className="dragwyb-editor-loader-dot-ani">.</span>
                    </h1>

                    <div className="dragwyb-editor-loader-loading-status-label">
                        Preparing Assets
                    </div>

                    <p className="dragwyb-editor-loader-loading-sub-text">
                        Syncing Component & Styles
                    </p>
                </div>
            </div>
        </div>
    );
};

export default PreviewLoading;