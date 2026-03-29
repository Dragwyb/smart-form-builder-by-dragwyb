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
import PreviewLoading from "./previewLoading";
import Notice from "../components/Common/Notice";

const Editor = () => {
    const [activeDrag, setActiveDrag] = useState(null);
    const [dropInfo, setDropInfo] = useState(false);

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

        if (iframe && node.ownerDocument === iframe.contentDocument) {
            const iframeRect = iframe.getBoundingClientRect();

            return {
                top: rect.top + iframeRect.top - 10,
                left: rect.left + iframeRect.left,
                bottom: rect.bottom + iframeRect.top,
                right: rect.right + iframeRect.left,
                width: rect.width,
                height: rect.height,
                x: rect.x + iframeRect.left,
                y: rect.y + iframeRect.top,
            };
        }

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
        const { active } = event;

        const activeDragData = { activeDrag: active };

        if (active?.data?.current?.currentId) {
            setDropInfo({
                targetId: active.data.current.currentId,
                index: active.data.current.index
            });
        }

        setActiveDrag(activeDragData);
    };

    const handleDragMove = (event) => {
        const { active, over } = event; // No need for delta or activatorEvent here!

        // If we are not over anything, clear indicators
        if (!over) {
            if (dropInfo !== false) setDropInfo(false);
            return;
        }

        const activeId = active.id.replace("canvas-drag-", "");
        const overId = over.id.replace("canvas-drop-", "");

        // Don't drop on self
        if (activeId === overId) {
            setDropInfo(false);
            return;
        }

        let newDropInfo = over.data.current.currentId;

        // If simply hovering a container/wrapper, just set index
        if (over.data.current.canvasFieldDrop || over.data.current.addInitialField) {
            const dropObj = { targetId: newDropInfo, index: 0 };
            if (!dropInfo || dropInfo.targetId !== dropObj.targetId || dropInfo.index !== dropObj.index) {
                setDropInfo(dropObj);
            }
            return;
        }

        // 1. Get the live, normalized coordinates of both items
        const activeRect = active.rect.current.translated;
        const activeIndex = active.data.current.index;
        const overRect = over.rect;

        if (!activeRect || !overRect) return;

        // 2. Calculate the exact vertical center of the dragged item
        const activeMiddleY = activeRect.top + (activeRect.height / 2) + 10;

        // 3. Calculate the 50% middle line of the hovered item
        const overMiddleY = overRect.top + (overRect.height / 2);

        let targetIndex = over.data.current.index;
        let targetId = over.data.current.currentId;

        // 4. Check if the center of the dragged item crosses the 50% mark
        if (activeMiddleY > overMiddleY) {
            targetIndex = targetIndex + 1;
        }

        const dropObj = { targetId, index: targetIndex };

        if (!dropInfo || dropInfo.targetId !== dropObj.targetId || dropInfo.index !== dropObj.index) {
            setDropInfo(dropObj);
        }
    };

    const handleDragEnd = (event) => {
        setActiveDrag(null);
        setDropInfo(false);

        const { active, over } = event;

        // Validation: Did we drop on a valid canvas dropzone?
        if (!over || !over.id.startsWith("canvas-drop-")) return;

        const isFromSidebar = active?.data?.current?.fromSidebar;
        const isCanvasDrag = active?.data?.current?.canvasDrag;

        const finalId = dropInfo !== false && dropInfo.index !== undefined ? dropInfo : { targetId: over.data.current.currentId, index: over.data.current.index };

        if (isFromSidebar) {
            const type = active.data.current.type;
            const addFieldData = {
                type,
                Utils,
                index: finalId.index,
            }

            if (dropInfo.targetId !== 'root') {
                addFieldData.parentContainer = {
                    rootContainerId: dropInfo.targetId,
                    activeColumnIndex: finalId.index
                };
            }

            const newField = Utils.AddField(addFieldData);
            setSelectedSettingId({ id: newField._id });
        } else if (isCanvasDrag) {
            if (active?.data?.current?.currentId && dropInfo && dropInfo.targetId && dropInfo.index >= 0) {
                const currentIndex = active?.data?.current?.index;
                let updatedIndex = dropInfo.index;

                if (updatedIndex > currentIndex) {
                    updatedIndex = updatedIndex - 1;
                }

                if (updatedIndex === currentIndex) {
                    setDropInfo(false);
                    return;
                }

                if (updatedIndex >= 0) {
                    dispatch(updateFieldOrder(active?.data?.current?.currentId, dropInfo.targetId, updatedIndex));
                }
            }
        }
    };

    return (
        <div className="dragwyb-editor">
            <PreviewLoading />
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
                            dropInfo={dropInfo}
                            setActiveTab={setActiveTabHandler}
                        />
                    </PreviewIframe>
                    {activeDrag && <SidebarFieldOverlay data={activeDrag} />}
                </DndContext>
            </div>
            <Notice />
        </div>
    );
};

export default Editor;