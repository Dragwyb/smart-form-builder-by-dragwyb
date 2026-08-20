import React, { useEffect, useState } from 'react';
import { useSelector } from 'react-redux';

const PreviewLoading = () => {
    const iframeEle = useSelector(state => state?.iframeEle);
    const isTemplateOpen = DragwybEditor?.formData?.rootContainers?.length > 0 ? false : true;
    const url = new URL(window.location.href);
    const isInsertTemplate = url.searchParams.get('setup-template');
    const [isOpenLoading, setIsOpenLoading] = useState(true);

    useEffect(() => {
        if (iframeEle) {
            if (isInsertTemplate && 'blank' !== isInsertTemplate && isTemplateOpen) {
                setTimeout(() => {
                    setIsOpenLoading(false)
                }, 2000);
                return;
            }

            setIsOpenLoading(false);
            return;
        }
    }, [iframeEle])

    if (!isOpenLoading) return null;

    const pluginUrl = DragwybEditor.pluginUrl;
    return (isOpenLoading &&
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

                    <div className="dragwyb-editor-loader-form-preview">
                        <img src={pluginUrl + 'assets/img/logo.png'} alt="Smart Form Builder by Dragwyb" />
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