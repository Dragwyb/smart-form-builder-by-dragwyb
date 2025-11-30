import { useState, useEffect } from 'react'; // Import useState and useEffect
import { useSelector, useStore, useDispatch } from 'react-redux';
import { Utils as Helper } from '../../components/Utils';
import { Button, SaveBtn } from '../../components/Common';
import { __ } from '@wordpress/i18n';

// Import the icons you requested
import { FaSun, FaMoon } from 'react-icons/fa';

const Header = () => {
    // Existing Selectors
    const formTitle = useSelector(state => state?.form?.advance?.form_name || DragwybEditor.formData.title);
    const formStatus = useSelector(state => state?.form?.advance?.form_status || DragwybEditor.formData.status);
    const previewMode = useSelector(state => state.previewMode);

    // --- NEW: Theme State Management ---
    // You can default to 'light' or check localStorage/OS preference
    const defaultTheme = localStorage.getItem("DragwybEditorTheme") || 'light';
    const [theme, setTheme] = useState(defaultTheme);

    // Apply the theme to the body tag whenever the state changes
    const toggleTheme = () => {
        const newTheme = theme === 'light' ? 'dark' : 'light';
        const oldClass = newTheme === 'light' ? 'dark' : 'light';
        const bodyEleCls = document.body.classList;

        if (oldClass === 'dark') {
            bodyEleCls.remove(oldClass)
        } else {
            bodyEleCls.add(newTheme);
        }

        localStorage.setItem("DragwybEditorTheme", newTheme);
        setTheme(newTheme);
    };

    const dispatch = useDispatch();
    const store = useStore();
    const state = store.getState();
    const Utils = Helper(state, dispatch);

    const handleExit = () => {
        window.location.href = DragwybEditor.adminUrl;
    };

    const setActiveTabHandler = (value) => {
        Utils.setSelectedSettingId({ value: value });
        Utils.setActiveTab({ value: value });
        Utils.setPreviewMode({ value: false });
    }

    const statusHtml = <>
        <span data-status={formStatus}></span>
        <p>{formStatus.charAt(0).toUpperCase() + formStatus.slice(1)}</p>
    </>;

    return (
        <div className="dragwyb-editor__header">
            <div className="dragwyb-editor__details">
                <h2>Dragwyb Form Builder</h2>

                <div className="dragwyb-editor__status" data-status={formStatus} onClick={() => setActiveTabHandler('advance')}>
                    {statusHtml}
                </div>

                {/* --- NEW: Theme Toggle Button --- */}
                <div
                    className="dragwyb-editor__theme-toggle"
                    onClick={toggleTheme}
                    title={theme === 'light' ? __('Switch to Dark Mode', 'dragwyb-form-builder') : __('Switch to Light Mode', 'dragwyb-form-builder')}                >
                    <div>
                        {theme === 'light' ? <FaMoon color='black' /> : <FaSun color="#f39c12" />}
                    </div>
                </div>
                {/* -------------------------------- */}

            </div>

            <div className="dragwyb-editor__title" onClick={() => setActiveTabHandler('advance')}>
                <h2>{formTitle}</h2>
            </div>

            <div className="dragwyb-editor__actions">
                <Button onClick={() => Utils.setPreviewMode({ value: !previewMode })} className='dragwyb-preview'>
                    <i className={`far fa-eye${previewMode ? '-slash' : ''}`} />
                    {previewMode ? __('Disable', 'dragwyb-form-builder') : __('Enable', 'dragwyb-form-builder')}
                </Button>
                <Button onClick={handleExit} className=''>
                    {DragwybBuilder.i18n.exit}
                </Button>
                <SaveBtn />
            </div>
        </div>
    );
}

export default Header;