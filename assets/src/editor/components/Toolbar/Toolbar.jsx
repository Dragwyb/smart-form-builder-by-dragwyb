import { Button } from "../Common";

const ToolBar = ({ toolbars, activeTab, setActiveTab, setPreviewMode }) => {
    return <div className='dragwyb-editor__toolbar'>
        {Object.keys(toolbars).map(tab => {
            return <div className={`dragwyb-editor__toolbar-item ${activeTab === toolbars[tab].settingName ? ' active' : ''}`}
                onClick={() => {
                    setActiveTab(toolbars[tab].settingName);
                    setPreviewMode(false);
                }}
                title={DragwybBuilder.i18n[tab]}
                data-tab={toolbars[tab].settingName}
            >
                <i className={toolbars[tab].iconCls} />
                {DragwybBuilder.i18n[tab]}
            </div>
        })}
    </div>
}

export default ToolBar;