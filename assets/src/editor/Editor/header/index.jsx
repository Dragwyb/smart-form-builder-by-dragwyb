import { useEffect, useCallback, useMemo } from 'react';
import { useSelector, useStore, useDispatch } from 'react-redux';
import { Utils as Helper } from '../../components/Utils';
import { SaveBtn } from '../../components/Common';
import { __ } from '@wordpress/i18n';
import { escUrl } from '../../utils/escaping';
import { updateThemeMode } from '../../store/actions';
import ResponsiveDevices from '../../components/Common/ResponsiveDevices';

// Import the icons you requested
import { FaSun, FaMoon } from 'react-icons/fa';

const Header = () => {
    // Existing Selectors
    const formStatus = useSelector(state => state?.form?.advance?.form_status || DragwybEditor.formData.status);
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
                <h2>Smart Form Builder by Dragwyb</h2>
                <div className="dragwyb-editor__status" data-status={formStatus} onClick={() => setActiveTabHandler('advance')}>
                    {statusHtml}
                </div>
            </div>

            <div className="dragwyb-editor__form-devices">
                <ResponsiveDevices Utils={Utils} />
            </div>

            <div className="dragwyb-editor__actions">
                <div
                    className="dragwyb-editor__theme-toggle"
                    onClick={toggleTheme}
                    title={themeMode === 'light' ? __('Switch to Dark Mode', 'smart-form-builder-by-dragwyb') : __('Switch to Light Mode', 'smart-form-builder-by-dragwyb')}                >
                    <div>
                        {themeMode === 'light' ? <FaMoon color='#fff' /> : <FaSun color="#f39c12" />}
                    </div>
                </div>
                <a href={escUrl(DragwybEditor.previewUrl)} className='dragwyb-editor__preview-toggle' target="_blank">
                    <i className='far fa-eye' title={__('Preview', 'smart-form-builder-by-dragwyb')} />
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