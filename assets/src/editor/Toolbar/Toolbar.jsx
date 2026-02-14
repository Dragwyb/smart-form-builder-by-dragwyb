import { useSelector } from "react-redux";
import { Button } from "../components/Common";

const ToolBar = ({ setActiveTab, setSettingId }) => {
    const activeTab = useSelector(state => state.activeToolbar);
    const toolbars = DragwybEditor.EditorToolbars.toolbars;

    if (!toolbars || Object.keys(toolbars).length < 1) {
        return <></>;
    }

    return <div className='dragwyb-editor__toolbar'>
        {Object.keys(toolbars).map(tab => {
            return <div key={tab} className={`dragwyb-editor__toolbar-item ${activeTab === tab ? ' active' : ''}`}
                onClick={(e) => {
                    e.preventDefault();
                    if (tab === activeTab) {
                        return;
                    }
                    setActiveTab(tab);
                    setSettingId({ id: tab, tab: tab });
                }}
                title={toolbars[tab].name}
                data-tab={tab}
            >
                <span className="dragwyb-editor__toolbar-item-icon">
                    <i className={toolbars[tab].icon} />
                </span>
                <span className="dragwyb-editor__toolbar-item-text">
                    {toolbars[tab].name}
                </span>
            </div>
        })}
    </div>
}

export default ToolBar;