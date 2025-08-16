import React from "react";
import { useSortable } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import shouldRenderField from "../../editor/components/Editor/shouldRenderField";
import { Field } from "../../editor/components/Editor/Fields";
import { __ } from "@wordpress/i18n";

const renderControls = ({
  key,
  settings,
  value,
  repeaterValue,
  updateHandler,
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

  // if (settings.type === 'tabs') {
  //     defautlActiveTab(key, settings)
  // }

  const getHtml = () => {
    return <div>Unsupported Controller type: {settings.type}</div>;
  };

  let html = DragwybBuilder.Hooks.applyFilter(
    "Dragwyb/Editor/ControlRender/" + settings.type,
    getHtml(),
    key,
    settings,
    value,
    updateHandler
  );

  return (
    <div key={key} className="setting-row" dataType={settings.type}>
      {html}
    </div>
  );
};

const SortableRepeaterItem = ({
  id,
  index,
  onDelete,
  onCopy,
  updateHandler,
  settings,
  repeaterItem,
  repeaterItems
}) => {
  const { attributes, listeners, setNodeRef, transform, transition } =
    useSortable({ id });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
  };

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
    <div style={style} className="dragwyb-repeater-item" data-id={id}>
      <div ref={setNodeRef} {...attributes} {...listeners}>
        {repeaterHeading(settings.item_label, index)}
        <span><i class="fa-regular fa-copy" onClick={()=>{onCopy(repeaterItem, index+1)}} title={__('Copy', 'dragwyb-form-builder')}></i></span>
        {repeaterItems.length > 1 && <span><i class="fa-solid fa-xmark" onClick={()=>{onDelete(id)}}  title={__('Delete', 'dragwyb-form-builder')}></i></span>}
      </div>
      {Object.values(settings.items).map((data) => {
        return renderControls({
          key: data.name,
          value: repeaterItem[data.name] || "",
          repeaterValue: repeaterItem,
          settings: data,
          updateHandler: (key, value) => updateHandler(key, value, index),
        });
      })}
    </div>
  );
};

export default SortableRepeaterItem;
