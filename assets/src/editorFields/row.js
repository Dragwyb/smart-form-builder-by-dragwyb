import { Utils } from "../editor/components/Utils";
import { FaPlus } from "react-icons/fa";

class rowField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'row'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const children = this.children;
        const childrenIds = this.childrenIds;
        const totalColumns = this.attributes?.columns || 1;

        const missingColumns = totalColumns - childrenIds.length;

        const addFields = (e) => {
            e.stopPropagation();
            this.Utils.updateActiveRootContainer({ rootContainerId: this.id, activeColumnIndex: parseInt(e.target.dataset.index) });
            this.Utils.setSelectedSettingId({ value: false });
            this.Utils.setActiveTab({ value: 'fields' });
        }

        return <>
            {childrenIds && childrenIds.length > 0 && <>{childrenIds.map((id, index) => id ? <React.Fragment key={id}>{children[index]}</React.Fragment> : <div key={index} className="dragwyb-field__add-column" onClick={addFields} data-index={index}><FaPlus /></div>)}</>}
            {missingColumns > 0 && Array.from({ length: missingColumns }).map((_, index) => (
                <div key={index} className="dragwyb-field__add-column" onClick={addFields} data-index={childrenIds.length + index}>
                    <FaPlus />
                </div>
            ))}
        </>
    }
}

const intializeRow = (field, Utils) => {

    if (!field?.children) {
        const columns = field?.attributes?.columns || 1;

        field.children = [];
        for (let i = 0; i < columns; i++) {
            field.children.push(null);
        }
    }

    return field;
}

jQuery(document).on('Dragwyb:editorInit', () => {
    DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/AddField/row', intializeRow, Utils);
});

export default rowField;
