import React from 'react';
import { DragOverlay } from '@dnd-kit/core';

const SidebarFieldOverlay = ({ data, fields }) => {
    let type=false;
    let wrapperCls='';
    const{activeDrag, extraData}=data;
    let fieldStyle={};
    if(activeDrag?.data?.current?.fromSidebar){
        type=activeDrag.data.current.type;
    }else if(activeDrag?.data?.current?.canvasDrag){
        const fieldIndex=activeDrag.data.current.currentIndex
        wrapperCls='canvas-overlay-field';
        type=fields[fieldIndex].type;

        if(extraData){
            let transformY=extraData.offsetY;
            if(extraData.offsetY + 64 > extraData.height){
                transformY = extraData.offsetY + 64 - extraData.height
            }
            fieldStyle.transform=`translate(${extraData.offsetX}px,${transformY}px)`;

        }
    }

    if(!type) return null;

    const fieldTypes = DragwybEditor.fieldTypes;

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
        >
            <div className="dragwyb-overlay-preview field-type" style={fieldStyle}>
                {config.icon && <i className={config.icon}></i>}
                <p>{config.label}</p>
            </div>
        </DragOverlay>
    );
};

export default SidebarFieldOverlay;