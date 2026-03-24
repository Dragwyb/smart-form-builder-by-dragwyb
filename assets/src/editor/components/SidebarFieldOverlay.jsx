import React from 'react';
import { useSelector } from 'react-redux';
import { DragOverlay } from '@dnd-kit/core';

const snapToCursor = ({ activatorEvent, activeNodeRect, transform }) => {
    if (activeNodeRect && activatorEvent) {
        let activatorCoordinates = {
            x: activatorEvent.clientX ?? activatorEvent.touches?.[0]?.clientX ?? 0,
            y: activatorEvent.clientY ?? activatorEvent.touches?.[0]?.clientY ?? 0,
        };

        const iframe = document.getElementById('dragwyb-preview-iframe');

        if (activatorEvent.view && iframe && activatorEvent.view === iframe.contentWindow) {
            if (iframe) {
                const iframeRect = iframe.getBoundingClientRect();
                activatorCoordinates.x += iframeRect.left;
                activatorCoordinates.y += iframeRect.top;
            }
        }

        const offsetX = activatorCoordinates.x - activeNodeRect.left - 10;
        const offsetY = activatorCoordinates.y - activeNodeRect.top - 40;

        return {
            ...transform,
            x: transform.x + offsetX,
            y: transform.y + offsetY,
        };
    }

    return transform;
};

const SidebarFieldOverlay = ({ data }) => {
    const fields = useSelector(state => state.form.fields); // Assuming fields are stored in Redux

    let type = false;
    let wrapperCls = '';
    const { activeDrag } = data;
    let fieldStyle = {};
    if (activeDrag?.data?.current?.fromSidebar) {
        type = activeDrag.data.current.type;
    } else if (activeDrag?.data?.current?.canvasDrag) {
        const fieldId = activeDrag.data.current.currentId
        wrapperCls = 'canvas-overlay-field';

        type = fields[fieldId].type;
    }

    if (!type) return null;

    const fieldTypes = DragwybEditor.fields.fields;

    const config = fieldTypes[type];

    if (!config) return null;

    return (
        type &&
        <DragOverlay
            dropAnimation={{
                duration: 200,
                easing: 'ease'
            }}
            // style={{width: 100, height: 100}}
            adjustScale={false} // optional: avoid scale distortion
            className={wrapperCls}
            modifiers={[snapToCursor]}
        >
            <div className="dragwyb-overlay-preview" style={fieldStyle}>
                {config.icon && <i className={config.icon}></i>}
                <p>{config.label}</p>
            </div>
        </DragOverlay>
    );
};

export default SidebarFieldOverlay;