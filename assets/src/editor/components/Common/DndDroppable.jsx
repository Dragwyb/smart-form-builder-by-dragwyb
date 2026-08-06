import {
    useDroppable as Droppable,
} from '@dnd-kit/core';

const useDroppable=({id, data={}, disabled=false})=>{
    const DroppableProps = Droppable(
        {
            id: id,
            data,
            disabled
        }
    );

    return DroppableProps;
}

export default useDroppable;