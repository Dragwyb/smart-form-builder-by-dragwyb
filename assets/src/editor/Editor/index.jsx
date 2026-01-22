import React, { useState } from "react";
import { useSelector, useDispatch, useStore } from "react-redux";
import Canvas from "./Canvas";
import {
    resetSectionSettings,
    updateFieldOrder,
} from "../store/actions";
import {
    DndContext,
    useSensor,
    useSensors,
    PointerSensor,
    MouseSensor,
    TouchSensor,
} from "@dnd-kit/core";

import SidebarFieldOverlay from "../components/SidebarFieldOverlay";
import { Utils as Helper } from "../components/Utils";
import ToolBar from "../Toolbar/Toolbar";
import Header from "./header";
import ToolbarSettings from "../Toolbar/ToolbarSettings";
import PreviewIframe from "./PreviewIframe";

const Editor = () => {
    const [activeDrag, setActiveDrag] = useState(null);
    const [dropIndex, setDropIndex] = useState(false);
    const [dropIndicatorPosition, setDropIndicatorPosition] = useState(false);

    const PREVIEW_URL = DragwybEditor?.previewUrl;

    const dispatch = useDispatch();
    const store = useStore();
    const state = store.getState();
    const Utils = Helper(state, dispatch);

    const resetSection = () => {
        dispatch(resetSectionSettings());
    };

    const setSelectedSettingId = ({ id = false, tab = "fields" }) => {
        Utils.setSelectedSettingId({ value: id });
        resetSection();
        const defaultToolbar = DragwybEditor?.EditorToolbars?.Default ?? false;
        Utils.setActiveTab({ value: false === id ? defaultToolbar : tab });
    };

    const setActiveTabHandler = (value) => {
        Utils.setSelectedSettingId({ value: false });
        resetSection();
        Utils.setActiveTab({ value: value });
    };

    const sensors = useSensors(
        useSensor(PointerSensor, { activationConstraint: { distance: 5 } }),
        useSensor(MouseSensor, { activationConstraint: { distance: 5 } }),
        useSensor(TouchSensor, { activationConstraint: { delay: 5, tolerance: 6 } })
    );

    const measureDroppableContainers = (node) => {
        const rect = node.getBoundingClientRect();

        const iframe = document.getElementById('dragwyb-preview-iframe');

        if (iframe && node.ownerDocument !== document) {
            const iframeRect = iframe.getBoundingClientRect();

            return {
                top: rect.top + iframeRect.top,
                left: rect.left + iframeRect.left,
                bottom: rect.bottom + iframeRect.top,
                right: rect.right + iframeRect.left,
                width: rect.width,
                height: rect.height,
                x: rect.x + iframeRect.left,
                y: rect.y + iframeRect.top,
            };
        }

        console.log(rect.top)

        // Standard measuring for everything else
        return {
            top: rect.top,
            left: rect.left,
            bottom: rect.bottom,
            right: rect.right,
            width: rect.width,
            height: rect.height,
            x: rect.x,
            y: rect.y,
        };
    };

    const measureDraggableContainers = (node) => {
        const rect = node.getBoundingClientRect();

        const iframe = document.getElementById('dragwyb-preview-iframe');

        const finalPosition = {
            top: rect.top,
            left: rect.left,
            bottom: rect.bottom,
            right: rect.right,
            width: rect.width,
            height: rect.height,
            x: rect.x,
            y: rect.y,
        };

        const iframeRect = iframe.getBoundingClientRect();

        if (iframe && node.ownerDocument !== document) {
            finalPosition.top = rect.top + iframeRect.top;
            finalPosition.left = rect.left + iframeRect.left;
            finalPosition.bottom = rect.bottom + iframeRect.top;
            finalPosition.right = rect.right + iframeRect.left;
            finalPosition.width = rect.width;
            finalPosition.height = rect.height;
            finalPosition.x = rect.x + iframeRect.left;
            finalPosition.y = rect.y + iframeRect.top;
        } else {
            finalPosition.top = rect.top + iframeRect.top;
            finalPosition.bottom = rect.bottom + iframeRect.top;
            finalPosition.height = rect.height;
            finalPosition.y = rect.y + iframeRect.top;
        }

        return finalPosition;
    };

    // --- Drag Handlers ---

    const handleDragStart = (event) => {
        const { active, activatorEvent } = event;

        const activeDragData = { activeDrag: active };

        if (activatorEvent) {
            activeDragData.extraData = {
                offsetX: activatorEvent.offsetX,
                offsetY: activatorEvent.offsetY,
                width: active.rect.current?.initial?.width || 0,
                height: active.rect.current?.initial?.height || 0,
            };
        }

        if (active?.data?.current?.currentIndex >= 0) {
            setDropIndex(active.data.current.currentIndex);
        }

        setActiveDrag(activeDragData);
    };

    const handleDragMove = (event) => {
        const { active, over, delta, activatorEvent } = event;

        // If we are not over anything, clear indicators
        if (!over) {
            if (dropIndex !== false) setDropIndex(false);
            if (dropIndicatorPosition !== false) setDropIndicatorPosition(false);
            return;
        }

        const activeId = active.id.replace("canvas-drag-", "");
        const overId = over.id.replace("canvas-drop-", "");

        // Don't drop on self
        if (activeId === overId) {
            setDropIndex(false);
            setDropIndicatorPosition(false);
            return;
        }

        let newDropIndex = over.data.current.currentIndex;

        const isCanvasDrag = active?.data?.current?.canvasDrag;
        const activeIndex = active?.data?.current?.currentIndex;

        // If simply hovering a container/wrapper, just set index
        if (over.data.current.canvasFieldDrop || over.data.current.addInitialField) {
            if (dropIndex !== newDropIndex) setDropIndex(newDropIndex);
            setDropIndicatorPosition(false);
            return;
        }

        // Detailed field sorting logic
        const pointerY = activatorEvent.clientY + delta.y; // Or use event.active.rect.current.translated.top
        const overMiddle = over.rect.top + (over.rect.height / 2);

        let targetIndex = newDropIndex;

        // Determine "After" vs "Before"
        if (pointerY > overMiddle) {
            targetIndex = newDropIndex + 1;
            setDropIndicatorPosition("bottom");
        } else {
            setDropIndicatorPosition("top"); // or false/default
        }

        // Adjustment for moving items downwards in the same list
        if (isCanvasDrag && activeIndex < targetIndex) {
            targetIndex -= 1;
        }

        if (dropIndex !== targetIndex) {
            setDropIndex(targetIndex);
        }
    };

    const handleDragEnd = (event) => {
        setActiveDrag(null);
        setDropIndex(false);
        setDropIndicatorPosition(false);

        const { active, over } = event;

        // Validation: Did we drop on a valid canvas dropzone?
        if (!over || !over.id.startsWith("canvas-drop-")) return;

        const isFromSidebar = active?.data?.current?.fromSidebar;
        const isCanvasDrag = active?.data?.current?.canvasDrag;

        const finalIndex = dropIndex !== false ? dropIndex : (over.data.current.currentIndex || 0);

        if (isFromSidebar) {
            const type = active.data.current.type;
            const newField = Utils.AddField({ type, Utils, index: finalIndex });
            setSelectedSettingId({ id: newField._id });
        } else if (isCanvasDrag) {
            const oldIndex = active?.data?.current?.currentIndex;
            if (oldIndex !== undefined && oldIndex !== finalIndex) {
                dispatch(updateFieldOrder(oldIndex, finalIndex));
            }
        }
    };

    return (
        <div className="dragwyb-editor">
            <Header />

            <div className="dragwyb-editor__body">
                <DndContext
                    sensors={sensors}
                    onDragStart={handleDragStart}
                    onDragEnd={handleDragEnd}
                    onDragCancel={() => setActiveDrag(null)}
                    onDragMove={handleDragMove}
                    measuring={{
                        droppable: {
                            strategy: 'always',
                            measure: measureDroppableContainers // <--- Apply the fix
                        },
                        draggable: {
                            strategy: 'always',
                            measure: measureDraggableContainers
                        }
                    }}
                >
                    <ToolBar
                        setActiveTab={setActiveTabHandler}
                        setSettingId={setSelectedSettingId}
                    />
                    <ToolbarSettings setActiveTab={setActiveTabHandler} />

                    {/* The Iframe Shield: Crucial for dragging over iframe */}
                    {activeDrag && (
                        <div
                            style={{
                                position: 'fixed',
                                top: 0,
                                left: 0,
                                width: '100%',
                                height: '100%',
                                zIndex: 9998,
                                cursor: 'grabbing'
                            }}
                        />
                    )}

                    <PreviewIframe url={PREVIEW_URL}>
                        <Canvas
                            onFieldSelect={setSelectedSettingId}
                            Utils={Utils}
                            dropIndex={dropIndex}
                            dropIndicatorPosition={dropIndicatorPosition}
                            setActiveTab={setActiveTabHandler}
                        />
                    </PreviewIframe>
                    {activeDrag && <SidebarFieldOverlay data={activeDrag} />}
                </DndContext>
            </div>
        </div>
    );
};

export default Editor;