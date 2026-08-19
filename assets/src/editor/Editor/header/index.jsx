import { useState, useEffect, useCallback, useMemo } from 'react';
import { useSelector, useStore, useDispatch } from 'react-redux';
import { Utils as Helper } from '../../components/Utils';
import { SaveBtn } from '../../components/Common';
import { __ } from '@wordpress/i18n';
import { escUrl } from '../../utils/escaping';
import { updateThemeMode, updateActiveToolbar, revertToHistory } from '../../store/actions';
import ResponsiveDevices from '../../components/Common/ResponsiveDevices';
import IconsManager from '../../components/IconsManager';

// Import the icons you requested
import { FaSun, FaMoon, FaHistory, FaFolderPlus } from 'react-icons/fa';
import TemplateLibrary from './TemplateLibrary';

const Header = () => {
    const [isTemplateOpen, setIsTemplateOpen] = useState(DragwybEditor?.formData?.rootContainers?.length > 0 ? false : true);
    // Existing Selectors
    const activeToolbar = useSelector(state => state?.activeToolbar);
    const formStatus = useSelector(state => state?.form?.advance?.form_status || DragwybEditor.formData.status);
    const formTitle = useSelector(state => state?.form?.advance?.form_name || DragwybEditor.formData.title);
    const themeMode = useSelector(state => state?.themeMode || 'light');
    const iframeEle = useSelector(state => state.iframeEle);
    const pluginUrl = DragwybEditor.pluginUrl;

    const dispatch = useDispatch();
    const store = useStore();

    // Memoize Utils
    const Utils = useMemo(() => {
        const state = store.getState();
        return Helper(state, dispatch);
    }, [store, dispatch]);

    useEffect(() => {
        const bodyEleCls = document.body.classList;
        const iframeBodyCls = iframeEle?.body?.classList;

        if (themeMode === 'dark') {
            bodyEleCls.add('dark');
            iframeBodyCls?.add('dark');
        } else {
            bodyEleCls.remove('dark');
            iframeBodyCls?.remove('dark');
        }

    }, [themeMode, iframeEle]);

    // Keyboard Shortcuts for Undo & Redo
    useEffect(() => {
        if (!iframeEle) {
            return;
        }

        const handleKeyDown = (e) => {
            const isCtrl = e.ctrlKey || e.metaKey;
            if (isCtrl && !e.altKey) {

                if (e.key.toLowerCase() === 'z') {
                    e.preventDefault();
                    const stateHistory = store.getState().history || { past: [], currentIndex: -1 };
                    if (e.shiftKey) {
                        // Ctrl+Shift+Z or Cmd+Shift+Z -> Redo
                        if (stateHistory.currentIndex < stateHistory.past.length - 1) {
                            dispatch(revertToHistory(stateHistory.currentIndex + 1));
                        }
                    } else {
                        // Ctrl+Z or Cmd+Z -> Undo
                        if (stateHistory.currentIndex > -1) {
                            dispatch(revertToHistory(stateHistory.currentIndex - 1));
                        }
                    }
                } else if (e.key.toLowerCase() === 'y') {
                    // Ctrl+Y or Cmd+Y -> Redo
                    e.preventDefault();
                    const stateHistory = store.getState().history || { past: [], currentIndex: -1 };
                    if (stateHistory.currentIndex < stateHistory.past.length - 1) {
                        dispatch(revertToHistory(stateHistory.currentIndex + 1));
                    }
                }
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        iframeEle.addEventListener('keydown', handleKeyDown);
        return () => {
            window.removeEventListener('keydown', handleKeyDown);
            iframeEle.removeEventListener('keydown', handleKeyDown);
        };
    }, [dispatch, store, iframeEle]);

    // Apply the theme to the body tag whenever the state changes
    const toggleTheme = useCallback(() => {
        dispatch(updateThemeMode(themeMode === 'light' ? 'dark' : 'light'));
    }, [dispatch, themeMode]);

    const setActiveTabHandler = useCallback((value) => {
        Utils.setSelectedSettingId({ value: value });
        Utils.setActiveTab({ value: value });
    }, [Utils]);

    const statusHtml = <>
        <p>{formStatus.charAt(0).toUpperCase() + formStatus.slice(1)}</p>
    </>;

    return (
        <div className="dragwyb-editor__header">
            <div className="dragwyb-editor__details">
                <img src={pluginUrl + 'assets/img/logo.png'} alt="Smart Form Builder by Dragwyb" width={40} />
                <h2 onClick={() => setActiveTabHandler('advance')}>{formTitle}</h2>
                <div className="dragwyb-editor__status" data-status={formStatus} onClick={() => setActiveTabHandler('advance')}>
                    {statusHtml}
                </div>
            </div>

            <div className="dragwyb-editor__form-devices">
                <ResponsiveDevices Utils={Utils} />
            </div>

            <div className="dragwyb-editor__actions">
                <div
                    className={`dragwyb-editor__history-toggle${activeToolbar === 'history' ? ' active' : ''}`}
                    onClick={() => {
                        const nextToolbar = activeToolbar === 'history' ? (DragwybEditor?.EditorToolbars?.Default ?? 'fields') : 'history';
                        dispatch(updateActiveToolbar(nextToolbar));
                    }}
                    title={__('View History', 'smart-form-builder-by-dragwyb')}
                >
                    <FaHistory color='#fff' />
                </div>
                <div
                    className={`dragwyb-editor__templates-toggle${isTemplateOpen ? ' active' : ''}`}
                    onClick={() => setIsTemplateOpen(!isTemplateOpen)}
                    title={__('Open Template Library', 'smart-form-builder-by-dragwyb')}
                >
                    <FaFolderPlus color='#fff' />
                </div>
                <div
                    className="dragwyb-editor__theme-toggle"
                    onClick={toggleTheme}
                    title={themeMode === 'light' ? __('Switch to Dark Mode', 'smart-form-builder-by-dragwyb') : __('Switch to Light Mode', 'smart-form-builder-by-dragwyb')}                >
                    {themeMode === 'light' ? <FaMoon color='#fff' /> : <FaSun color="#f39c12" />}
                </div>
                <a href={escUrl(DragwybEditor.previewUrl)} className='dragwyb-editor__preview-toggle' target="_blank" title={__('Frontend Preview', 'smart-form-builder-by-dragwyb')}>
                    <IconsManager icon='far fa-eye' title={__('Preview', 'smart-form-builder-by-dragwyb')} />
                </a>
                <hr />
                <a href={escUrl(DragwybEditor.adminUrl)} className='dragwyb-button dragwyb-button--default dragwyb-button--medium'>
                    {DragwybBuilder.i18n.exit}
                </a>
                <SaveBtn />
            </div>
            {iframeEle &&
                <TemplateLibrary
                    isOpen={isTemplateOpen}
                    onClose={() => setIsTemplateOpen(false)}
                />
            }
        </div>
    );
}

export default Header;