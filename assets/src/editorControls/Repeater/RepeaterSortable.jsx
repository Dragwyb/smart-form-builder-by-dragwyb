import React from 'react';
import {
  DndContext, closestCenter, useSensor, useSensors, PointerSensor
} from '@dnd-kit/core';
import {
  SortableContext, verticalListSortingStrategy
} from '@dnd-kit/sortable';
import SortableRepeaterItem from './SortableRepeaterItem.jsx'; // from previous message

const RepeaterSortable = ({ items, settings, updateControls, controlId, utils }) => {
  const sensors = useSensors(useSensor(PointerSensor, {
    activationConstraint: { distance: 5 },
  }));

  const onDragEnd = (event) => {
    const { active, over } = event;
    if (!over || active.id === over.id) return;

    const oldIndex = items.findIndex(i => i._id === active.id);
    const newIndex = items.findIndex(i => i._id === over.id);

    const newItems = [...items];
    const movedItem = newItems.splice(oldIndex, 1)[0];
    newItems.splice(newIndex, 0, movedItem);

    updateControls(controlId, newItems);
  };

  const updateHandler = (key, value, index) => {

    const updatedItems = [...items];

    updatedItems[index].attribues[key] = value;
    updateControls(controlId, updatedItems);
  };

  const onDelete = (id) => {
    updateControls(controlId, items.filter(i => i._id !== id));
  };

  const onCopy = (item, index) => {
    const newItem = {  _id: utils.generateId(), attribues: item };
    items.splice(index, 0, newItem);

    updateControls(controlId, items);
  };

  return (
    <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={onDragEnd}>
      <SortableContext items={items.map(i => i._id)} strategy={verticalListSortingStrategy}>
        {items.map((item, index) => (
          <SortableRepeaterItem
            key={item._id}
            id={item._id}
            index={index}
            onDelete={onDelete}
            onCopy={onCopy}
            updateHandler={updateHandler}
            settings={settings}
            repeaterItem={item.attribues}
          />
        ))}
      </SortableContext>
    </DndContext>
  );
};

export default RepeaterSortable;
