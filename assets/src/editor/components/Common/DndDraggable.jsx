import {
    useDraggable as Draggable
} from '@dnd-kit/core';

const useDraggable=({id, data={}, disabled=false})=>{
    const DraggableProps = Draggable({
        id: id,
        data,
        disabled
    });

    return DraggableProps;
}

export default useDraggable;
