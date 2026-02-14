import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { useDispatch, useSelector } from 'react-redux';
import { updateIframeNode } from '../store/actions';
import UpdateFormLabelPosition from './updateFormLabelPosition';
import Scrollbar from '../components/Scrollbar';

const PreviewIframe = ({ children, url, style = {} }) => {
    const [mountNode, setMountNode] = useState(null);
    const responsiveType = useSelector((state) => state.responsiveType);
    const dispatch = useDispatch();

    const iframeSrc = `${url}&dragwyb_iframe_mode=true`;

    const handleLoad = (event) => {
        const iframe = event.target;
        const doc = iframe.contentWindow.document;

        setTimeout(() => {
            setMountNode(doc);
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
                ...style,
                '--preview-width': `${responsiveType >= 1024 ? '100%' : responsiveType + 'px'}`,
            }}
            data-responsive-type={responsiveType >= 1024 ? 'desktop' : responsiveType >= 768 ? 'tablet' : 'mobile'}
        >
            {mountNode && <UpdateFormLabelPosition />}
            {mountNode && createPortal(<Scrollbar isIframe={true} iframeRef={mountNode}>{children}</Scrollbar>, mountNode.body)}
        </iframe>
    );
};

export default PreviewIframe;