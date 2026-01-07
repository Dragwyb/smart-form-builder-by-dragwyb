import { useState, useEffect } from 'react'; // Import useState and useEffect
import { useSelector, useStore, useDispatch } from 'react-redux';
import { Utils as Helper } from '../../components/Utils';
import { Button, SaveBtn } from '../../components/Common';
import { __ } from '@wordpress/i18n';
import { escUrl } from '../../utils/escaping';

// Import the icons you requested
import { FaSun, FaMoon } from 'react-icons/fa';

const Header = () => {
    // Existing Selectors
    const formTitle = useSelector(state => state?.form?.advance?.form_name || DragwybEditor.formData.title);
    const formStatus = useSelector(state => state?.form?.advance?.form_status || DragwybEditor.formData.status);

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

    const setActiveTabHandler = (value) => {
        Utils.setSelectedSettingId({ value: value });
        Utils.setActiveTab({ value: value });
    }

    const statusHtml = <>
        <p>{formStatus.charAt(0).toUpperCase() + formStatus.slice(1)}</p>
    </>;

    return (
        <div className="dragwyb-editor__header">
            <div className="dragwyb-editor__details">
                <h2>Dragwyb Form Builder</h2>
            </div>

            <div className="dragwyb-editor__form-status">
                <div className="dragwyb-editor__title" onClick={() => setActiveTabHandler('advance')}>
                    <h2>{formTitle}</h2>
                </div>
                <div className="dragwyb-editor__status" data-status={formStatus} onClick={() => setActiveTabHandler('advance')}>
                    {statusHtml}
                </div>
            </div>

            <div className="dragwyb-editor__actions">
                <div
                    className="dragwyb-editor__theme-toggle"
                    onClick={toggleTheme}
                    title={theme === 'light' ? __('Switch to Dark Mode', 'dragwyb-form-builder') : __('Switch to Light Mode', 'dragwyb-form-builder')}                >
                    <div>
                        {theme === 'light' ? <FaMoon color='black' /> : <FaSun color="#f39c12" />}
                    </div>
                </div>
                <a href={escUrl(DragwybEditor.previewUrl)} className='dragwyb-editor__preview-toggle' target="_blank">
                    <i className='far fa-eye' title={__('Preview', 'dragwyb-form-builder')} />
                </a>
                <hr />
                <a href={escUrl(DragwybEditor.adminUrl)} className='dragwyb-button dragwyb-button--default dragwyb-button--medium'>
                    {DragwybBuilder.i18n.exit}
                </a>
                <SaveBtn />
            </div>
        </div>
    );
}

export default Header;