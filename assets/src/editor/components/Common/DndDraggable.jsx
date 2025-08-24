import {
    useDraggable as Draggable
} from '@dnd-kit/core';

const useDraggable=({id, data={}})=>{
    const DraggableProps = Draggable({
        id: id,
        data
    });

    return DraggableProps;
}

export default useDraggable;
