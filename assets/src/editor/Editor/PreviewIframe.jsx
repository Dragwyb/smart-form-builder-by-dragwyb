import React, { useState } from 'react';
import { createPortal } from 'react-dom';

const PreviewIframe = ({ children, url }) => {
    const [mountNode, setMountNode] = useState(null);

    const iframeSrc = `${url}&dragwyb_iframe_mode=true`;

    const handleLoad = (event) => {
        const iframe = event.target;
        const doc = iframe.contentWindow.document;

        const targetDiv = doc.getElementById('dragwyb-iframe-root');

        if (targetDiv) {
            setMountNode(targetDiv);
            // Add a class for specific iframe styling if needed
            doc.body.classList.add('dragwyb-iframe-body');
        } else {
            console.warn("Dragwyb: Iframe root #dragwyb-iframe-root not found. Falling back to body.");
            setMountNode(doc.body);
        }
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
                display: 'block'
            }}
        >
            {mountNode && createPortal(children, mountNode)}
        </iframe>
    );
};

export default PreviewIframe;