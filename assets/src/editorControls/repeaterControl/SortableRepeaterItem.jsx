import React from "react";
import { useSortable } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import shouldRenderField from "../../editor/Editor/shouldRenderField";
import { __ } from "@wordpress/i18n";
import DragwybControlBase from "../../editor/controlBase";

const renderControls = ({
  key,
  settings,
  value,
  repeaterValue,
  updateHandler,
  Utils
}) => {
  if (!settings.type) {
    return;
  }

  if (!DragwybEditor.controlTypes[settings.type]) {
    return <></>;
  }

  const shouldRender = shouldRenderField(settings, repeaterValue);

  if (!shouldRender) {
    return;
  }

  let Control = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/ControlRender/' + settings.type, false);

  if (!Control || (!Control.prototype instanceof DragwybControlBase || !Control.prototype instanceof DragwybEditor.editor.extends.ControlBase)) {
    Control = DragwybEditor.editor.extends.ControlBase;
  }

  return <div key={key} className="dragwyb-setting-row" dataType={settings.type}><Control
    key={key}
    id={key}
    settings={settings}
    value={value}
    handleChange={updateHandler}
    Utils={Utils}
  /></div>
};

const SortableRepeaterItem = ({
  id,
  index,
  onDelete,
  onCopy,
  updateHandler,
  settings,
  repeaterItem,
  repeaterItems,
  updateTabsHandler,
  activeRepeater,
  Utils
}) => {
  const { attributes, listeners, setNodeRef, transform, transition } =
    useSortable({ id });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
  };

  const updateActiveRepeater = (e) => {
    if (!e || !e.target || !e.target.classList || !e.target.classList.contains('dragwyb-repeater-item__header')) {
      return;
    }

    const id = e.target.dataset.id;

    updateTabsHandler('activeRepeaterId', activeRepeater !== id ? id : false);
  }

  const repeaterHeading = (key, index) => {
    let heading = "";

    if (repeaterItem[key]) {
      heading = repeaterItem[key];
    }

    if (!heading || "" === heading) {
      heading = `Item #${index + 1}`;
    }

    return heading;
  };

  return (
    <div
      style={style}
      className="dragwyb-repeater-item"
      data-id={id}
      onClick={updateActiveRepeater}
    >
      <div ref={setNodeRef} {...attributes} {...listeners} className="dragwyb-repeater-item__header" data-id={id}>
        {repeaterHeading(settings.item_label, index)}
        <span><i class="fa-regular fa-copy" onClick={() => { onCopy(repeaterItem, index + 1) }} title={__('Copy', 'dragwyb-form-builder')}></i></span>
        {repeaterItems.length > 1 && <span><i class="fa-regular fa-trash-can" onClick={() => { onDelete(id) }} title={__('Delete', 'dragwyb-form-builder')}></i></span>}
      </div>
      {(activeRepeater && activeRepeater === id) && Object.values(settings.items).map((data) => {
        return renderControls({
          key: data.name,
          value: repeaterItem[data.name] || "",
          repeaterValue: repeaterItem,
          settings: data,
          updateHandler: (key, value) => updateHandler(key, value, index),
          Utils: Utils
        });
      })}
    </div>
  );
};

export default SortableRepeaterItem;
