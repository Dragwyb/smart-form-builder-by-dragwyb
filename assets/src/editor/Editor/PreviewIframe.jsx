import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { useDispatch, useSelector, useStore } from 'react-redux';
import { updateIframeNode } from '../store/actions';
import UpdateFormLabelPosition from './updateFormLabelPosition';
import Scrollbar from '../components/Scrollbar';
import { editorFormReady } from '../utils/helpers'

const PreviewIframe = ({ children, url, style = {} }) => {
    const [mountNode, setMountNode] = useState(null);
    const responsiveType = useSelector((state) => state.responsiveType);
    const dispatch = useDispatch();
    const store = useStore();
    const state = store.getState();

    const iframeSrc = `${url}&dragwyb_iframe_mode=true`;

    const handleLoad = (event) => {
        const iframe = event.target;
        const doc = iframe.contentWindow.document;

        setMountNode(doc);
        dispatch(updateIframeNode(doc));
        const editorToolBars = DragwybEditor?.EditorToolbars?.toolbars;

        if (editorToolBars) {
            const toolbarKeys = Object.keys(editorToolBars);
            toolbarKeys.forEach((key) => {
                const toolbarLocalizeData = DragwybEditor?.[key];

                const iframeWindow = doc.defaultView;

                if (!iframeWindow.hasOwnProperty('DragwybEditor')) {
                    iframeWindow.DragwybEditor = {};
                }

                if (!iframeWindow.DragwybEditor.hasOwnProperty(key)) {
                    iframeWindow.DragwybEditor[key] = toolbarLocalizeData;
                }
            });
        }

        setTimeout(() => {
            editorFormReady({ state: { form: { id: state.form.id }, iframeEle: doc } })
        }, 100);
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