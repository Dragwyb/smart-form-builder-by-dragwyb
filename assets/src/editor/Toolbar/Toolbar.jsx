import { useSelector } from "react-redux";

const ToolBar = ({ setActiveTab, setSettingId }) => {
    const activeTab = useSelector(state => state.activeToolbar);
    const selectedSetting = useSelector(state => state.selectedSettingId);
    const toolbars = DragwybEditor.EditorToolbars.toolbars;


    if (!toolbars || Object.keys(toolbars).length < 1) {
        return <></>;
    }

    return <div className='dragwyb-editor__toolbar'>
        {Object.keys(toolbars).map(tab => {
            return <div key={tab} className={`dragwyb-editor__toolbar-item ${activeTab === tab && (!selectedSetting || selectedSetting === tab) ? ' active' : ''}`}
                onClick={(e) => {
                    e.preventDefault();
                    if (tab === activeTab && (!selectedSetting || selectedSetting === tab)) {
                        return;
                    }
                    setActiveTab(tab);
                    setSettingId({ id: tab, tab: tab });
                }}
                title={toolbars[tab].name}
                data-tab={tab}
            >
                <span className="dragwyb-editor__toolbar-item-icon">
                    <DragwybEditor.editor.IconsManager.Render icon={toolbars[tab].icon} />
                </span>
                <span className="dragwyb-editor__toolbar-item-text">
                    {toolbars[tab].name}
                </span>
            </div>
        })}
    </div>
}

export default ToolBar;