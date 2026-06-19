import React, { useMemo } from 'react';
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

const SidebarFieldOverlay = React.memo(({ data }) => {
    const { activeDrag } = data;

    // Determine fieldId needed from activeDrag (avoid subscribing to all fields)
    const canvasDragFieldId = activeDrag?.data?.current?.canvasDrag
        ? activeDrag.data.current.currentId
        : null;

    // Only select the specific field we need — not all fields
    const canvasDragFieldType = useSelector(
        (state) => canvasDragFieldId ? state.form.fields[canvasDragFieldId]?.type : null
    );

    const type = useMemo(() => {
        if (activeDrag?.data?.current?.fromSidebar) {
            return activeDrag.data.current.type;
        }
        return canvasDragFieldType || false;
    }, [activeDrag, canvasDragFieldType]);

    if (!type) return null;

    const fieldTypes = DragwybEditor.fields.fields;
    const config = fieldTypes[type];
    if (!config) return null;

    const wrapperCls = canvasDragFieldId ? 'canvas-overlay-field' : '';

    return (
        <DragOverlay
            dropAnimation={{
                duration: 200,
                easing: 'ease'
            }}
            adjustScale={false}
            className={wrapperCls}
            modifiers={[snapToCursor]}
        >
            <div className="dragwyb-overlay-preview">
                {config.icon && <DragwybEditor.editor.IconsManager.Render icon={config.icon} width={15} />}
                <p>{config.label}</p>
            </div>
        </DragOverlay>
    );
});

SidebarFieldOverlay.displayName = 'SidebarFieldOverlay';

export default SidebarFieldOverlay;