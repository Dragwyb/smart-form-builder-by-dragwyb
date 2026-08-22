import React, { useState, useCallback, useMemo } from "react";
import { useDispatch, useStore } from "react-redux";
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
    KeyboardSensor,
    pointerWithin,
    rectIntersection,
    closestCenter,
} from "@dnd-kit/core";
import { sortableKeyboardCoordinates } from "@dnd-kit/sortable";

import SidebarFieldOverlay from "../components/SidebarFieldOverlay";
import { Utils as Helper } from "../components/Utils";
import ToolBar from "../Toolbar/Toolbar";
import Header from "./header";
import ToolbarSettings from "../Toolbar/ToolbarSettings";
import FieldSettingsSidebar from "./FieldSettingsSidebar";
import PreviewIframe from "./PreviewIframe";
import PreviewLoading from "./previewLoading";
import Notice from "../components/Common/Notice";

class SmartKeyboardSensor extends KeyboardSensor {
    static activators = [
        {
            eventName: "onKeyDown",
            handler: (event, options, context) => {
                const { target } = event.nativeEvent;
                if (
                    target &&
                    (
                        ["INPUT", "TEXTAREA", "SELECT"].includes(target.tagName) ||
                        target.isContentEditable
                    )
                ) {
                    return false;
                }
                return KeyboardSensor.activators[0].handler(event, options, context);
            },
        },
    ];
}

const Editor = () => {
    const [activeDrag, setActiveDrag] = useState(null);
    const [dropInfo, setDropInfo] = useState(false);

    const PREVIEW_URL = DragwybEditor?.previewUrl;

    const dispatch = useDispatch();
    const store = useStore();

    // Lazy Utils — reads fresh state at call-time, not capture-time
    const Utils = useMemo(() => {
        const getState = () => store.getState();
        return Helper(getState(), dispatch);
    }, [store, dispatch]);

    const resetSection = useCallback(() => {
        dispatch(resetSectionSettings('fields'));
    }, [dispatch]);

    const setSelectedSettingId = useCallback(({ id = false }) => {
        if (store?.getState()?.selectedSettingId === id) {
            return;
        }
        Utils.setSelectedSettingId({ value: id });
        resetSection();
    }, [Utils, resetSection]);

    const setActiveTabHandler = useCallback((value) => {
        Utils.setActiveTab({ value: value });
    }, [Utils]);

    const sensors = useSensors(
        useSensor(PointerSensor, { activationConstraint: { distance: 8 } }),
        useSensor(SmartKeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );

    const measureDroppableContainers = useCallback((node) => {
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
    }, []);

    const measureDraggableContainers = useCallback((node) => {
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
    }, []);

    // Memoize measuring config to prevent DndContext re-init
    const measuringConfig = useMemo(() => ({
        droppable: {
            strategy: 'always',
            measure: measureDroppableContainers
        },
        draggable: {
            strategy: 'always',
            measure: measureDraggableContainers
        }
    }), [measureDroppableContainers, measureDraggableContainers]);

    // --- Drag Handlers (memoized) ---

    const handleDragStart = useCallback((event) => {
        const { active } = event;

        const activeDragData = { activeDrag: active };

        if (active?.data?.current?.currentId) {
            setDropInfo({
                targetId: active.data.current.currentId,
                index: active.data.current.index
            });
        }

        setActiveDrag(activeDragData);
    }, []);

    const handleDragMove = useCallback((event) => {
        const { active, over } = event;

        // If we are not over anything, clear indicators
        if (!over) {
            setDropInfo(prev => prev !== false ? false : prev);
            return;
        }

        if (over.data.current?.rowDropColumn) {
            setDropInfo(prev => prev !== false ? false : prev);
            return;
        }

        const activeId = active.id.replace("canvas-drag-", "");
        const overId = over.id.replace("canvas-drop-", "");

        // Don't drop on self
        if (activeId === overId) {
            setDropInfo(false);
            return;
        }

        let newDropInfo = over.data.current?.currentId;

        // If simply hovering a container/wrapper, just set index
        if (over.data.current?.canvasFieldDrop || over.data.current?.addInitialField) {
            const dropObj = { targetId: newDropInfo || 'root', index: 0 };
            setDropInfo(prev => {
                if (!prev || prev.targetId !== dropObj.targetId || prev.index !== dropObj.index) {
                    return dropObj;
                }
                return prev;
            });
            return;
        }

        // 1. Get the live, normalized coordinates of both items
        const activeRect = active.rect.current?.translated;
        const overRect = over.rect;

        if (!activeRect || !overRect) return;

        // 2. Calculate the exact vertical center of the dragged item
        const activeMiddleY = activeRect.top + (activeRect.height / 2) + 10;

        // 3. Calculate the 50% middle line of the hovered item
        const overMiddleY = overRect.top + (overRect.height / 2);

        let targetIndex = over.data.current?.index ?? 0;
        let targetId = over.data.current?.currentId || 'root';
        let fieldId = over.data.current?.fieldId;

        // 4. Check if the center of the dragged item crosses the 50% mark
        if (activeMiddleY > overMiddleY) {
            targetIndex = targetIndex + 1;
        }

        const dropObj = {
            targetId,
            fieldId,
            index: targetIndex,
            isChild: over.data.current?.isChild,
            isRoot: over.data.current?.isRootContainer
        };

        setDropInfo(prev => {
            if (!prev || prev.targetId !== dropObj.targetId || prev.fieldId !== dropObj.fieldId || prev.index !== dropObj.index) {
                return dropObj;
            }
            return prev;
        });
    }, []);

    const handleDragEnd = useCallback((event) => {
        let currentDropInfo = dropInfo;
        setActiveDrag(null);
        setDropInfo(false);

        const { active, over } = event;

        // Validation: Did we drop on a valid canvas dropzone?
        if (!over || !over.id.startsWith("canvas-drop-")) return;

        const isFromSidebar = active?.data?.current?.fromSidebar;
        const isCanvasDrag = active?.data?.current?.canvasDrag;
        const isDropColumn = over?.data?.current?.rowDropColumn;

        if (isDropColumn) {
            const targetId = over.data.current.parentId;
            const targetColIndex = over.data.current.index;

            currentDropInfo = {
                targetId,
                index: targetColIndex
            };
        }

        const finalId = currentDropInfo !== false && currentDropInfo?.index !== undefined
            ? currentDropInfo
            : { targetId: over.data.current?.currentId || 'root', index: over.data.current?.index ?? 0 };

        let finalIndex = finalId.index;
        if (isFromSidebar) {
            const type = active.data.current.type;
            const state = store.getState();
            const rootContainers = state.form.rootContainers || [];
            const fields = state.form.fields || {};

            if (rootContainers.length > 0) {
                const lastRootId = rootContainers[rootContainers.length - 1];
                const lastRootField = fields[lastRootId];
                const isLastSubmitButton = lastRootField && lastRootField.children && lastRootField.children.some(childId => fields[childId]?.type === 'button');

                if (isLastSubmitButton && type !== 'button') {
                    if ((finalId.targetId === 'root' || !currentDropInfo?.targetId || currentDropInfo?.targetId === 'root') && (finalIndex === undefined || finalIndex >= rootContainers.length)) {
                        finalIndex = rootContainers.length - 1;
                    }
                }
            }

            const addFieldData = {
                type,
                Utils,
                index: finalIndex,
            };

            if (currentDropInfo && currentDropInfo.targetId && currentDropInfo.targetId !== 'root') {
                addFieldData.parentContainer = {
                    rootContainerId: currentDropInfo.targetId,
                    activeColumnIndex: finalIndex
                };
            }

            const newField = Utils.AddField(addFieldData);
            setSelectedSettingId({ id: newField._id });
        } else if (isCanvasDrag) {
            const currentId = active?.data?.current?.currentId;
            const sourceParentId = active?.data?.current?.parentId || 'root';
            const sourceIndex = active?.data?.current?.index;
            const targetId = currentDropInfo?.targetId || over.data.current?.currentId || 'root';
            let updatedIndex = currentDropInfo?.index !== undefined ? currentDropInfo.index : over.data.current?.index;

            if (currentId && targetId && updatedIndex !== undefined && updatedIndex >= 0) {
                if (sourceParentId === targetId && updatedIndex > sourceIndex) {
                    updatedIndex = updatedIndex - 1;
                }

                if (sourceParentId === targetId && updatedIndex === sourceIndex) {
                    return;
                }

                dispatch(updateFieldOrder(currentId, targetId, updatedIndex, sourceParentId));

                let fieldLabel = '';
                if (active?.data?.current?.attributes) {
                    fieldLabel = active?.data?.current?.attributes?.label;
                }
                if (typeof fieldLabel !== 'string' || '' === fieldLabel) {
                    fieldLabel = currentId;
                }

                const historyLabel = `Move Field (${fieldLabel})`;
                dispatch({
                    type: 'ADD_HISTORY_SNAPSHOT',
                    payload: { label: historyLabel }
                });
            }
        }
    }, [dropInfo, Utils, setSelectedSettingId, dispatch, store]);

    const handleDragCancel = useCallback(() => setActiveDrag(null), []);

    return (
        <div className="dragwyb-editor">
            <PreviewLoading />
            <Header />

            <div className="dragwyb-editor__body">
                <DndContext
                    sensors={sensors}
                    onDragStart={handleDragStart}
                    onDragEnd={handleDragEnd}
                    onDragCancel={handleDragCancel}
                    onDragMove={handleDragMove}
                    measuring={measuringConfig}
                >
                    <ToolBar
                        setActiveTab={setActiveTabHandler}
                    />
                    <ToolbarSettings />

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
                            dropInfo={dropInfo}
                            setActiveTab={setActiveTabHandler}
                        />
                    </PreviewIframe>
                    <FieldSettingsSidebar onFieldSelect={setSelectedSettingId} />
                    {activeDrag && <SidebarFieldOverlay data={activeDrag} />}
                </DndContext>
            </div>
            <Notice />
        </div>
    );
};

export default Editor;