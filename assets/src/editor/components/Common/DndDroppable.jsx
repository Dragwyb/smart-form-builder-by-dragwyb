import {
    useDroppable as Droppable,
} from '@dnd-kit/core';

const useDroppable=({id, data={}})=>{
    const DroppableProps = Droppable(
        {
            id: id,
            data
        }
    );

    return DroppableProps;
}

export default useDroppable;