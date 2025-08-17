import { Button } from "../Common";

const ToolBar = ({ toolbarss, activeTab, setActiveTab, setPreviewMode }) => {
    const toolbars=DragwybEditor.EditorToolbars.toolbars;

    if(!toolbars || Object.keys(toolbars).length < 1){
        return <></>;
    }

    return <div className='dragwyb-editor__toolbar'>
        {Object.keys(toolbars).map(tab => {
            return <div className={`dragwyb-editor__toolbar-item ${activeTab === tab ? ' active' : ''}`}
                onClick={() => {
                    setActiveTab(tab);
                    setPreviewMode(false);
                }}
                title={toolbars[tab].name}
                data-tab={tab}
            >
                <i className={toolbars[tab].icon} />
                {toolbars[tab].name}
            </div>
        })}
    </div>
}

export default ToolBar;