import { FaPlus } from "react-icons/fa";

const DroppableColumnZone = ({ index, onClick, Utils, parentId }) => {
    const { setNodeRef, isOver } = Utils.useDroppable({
        id: `canvas-drop-column-${parentId}-${index}`, // Unique ID for each column
        data: {
            rowDropColumn: true,
            parentId,
            index, // Pass the index so you know WHICH column was dropped into
        },
    });

    return (
        <div
            ref={setNodeRef}
            className={`dragwyb-field__add-column${isOver ? ' drag-over' : ''}`}
            onClick={onClick}
            data-index={index}
        >
            <FaPlus />
        </div>
    );
};

class rowField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'row'; }
    bind() {
        if (!this.shouldRender()) return <></>;

        const children = this.children;
        const childrenIds = this.childrenIds || [];
        const totalColumns = this.attributes?.columns || 1;

        const missingColumns = totalColumns - childrenIds.length;

        const addFields = (e) => {
            const columnEle = e.target.classList.contains('dragwyb-field__add-column')
                ? e.target
                : e.target.closest('.dragwyb-field__add-column');

            this.Utils.updateActiveRootContainer({
                rootContainerId: this.id,
                activeColumnIndex: parseInt(columnEle.dataset.index)
            });
            this.Utils.setActiveTab({ value: 'fields' });
        }

        return (
            <>
                {childrenIds && childrenIds.length > 0 && (
                    <>
                        {childrenIds.map((id, index) =>
                            id ? (
                                <React.Fragment key={id}>{children[index]}</React.Fragment>
                            ) : (
                                <DroppableColumnZone
                                    key={`empty-${index}`}
                                    index={index}
                                    onClick={addFields}
                                    Utils={this.Utils}
                                    parentId={this.id}
                                />
                            )
                        )}
                    </>
                )}

                {missingColumns > 0 && Array.from({ length: missingColumns }).map((_, index) => {
                    const actualIndex = childrenIds.length + index;
                    return (
                        <DroppableColumnZone
                            key={`missing-${actualIndex}`}
                            index={actualIndex}
                            onClick={addFields}
                            Utils={this.Utils}
                            parentId={this.id}
                        />
                    );
                })}
            </>
        );
    }
}

export default rowField;
