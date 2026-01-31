import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { useDispatch } from 'react-redux';
import { updateIframeNode } from '../store/actions';

const PreviewIframe = ({ children, url, style = {} }) => {
    const [mountNode, setMountNode] = useState(null);
    const dispatch = useDispatch();

    const iframeSrc = `${url}&dragwyb_iframe_mode=true`;

    const handleLoad = (event) => {
        const iframe = event.target;
        const doc = iframe.contentWindow.document;

        setTimeout(() => {
            setMountNode(doc.body);
            dispatch(updateIframeNode(doc));
            jQuery(document).trigger('Dragwyb:editorAppLoaded');
        }, 1500);
    };

    return (
        <iframe
            src={iframeSrc}
            onLoad={handleLoad}
            id="dragwyb-preview-iframe"
            title="Form Preview"
            style={{
                width: '100%',
                height: '100%',
                border: 'none',
                display: 'block',
                ...style,
            }}
        >
            {mountNode && createPortal(children, mountNode)}
        </iframe>
    );
};

export default PreviewIframe;