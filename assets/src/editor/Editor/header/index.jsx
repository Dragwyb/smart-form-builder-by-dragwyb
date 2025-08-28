import { useSelector,  useStore, useDispatch } from 'react-redux';
import { Utils as Helper } from '../../components/Utils';
import { Button, SaveBtn } from '../../components/Common';
import { __ } from '@wordpress/i18n';


const Header = () => {
    const formTitle=useSelector(state => state?.advance?.form_title || DragwybEditor.formData.title); 
    const formStatus = useSelector(state => state?.advance?.form_status || DragwybEditor.formData.status);
    const previewMode = useSelector(state => state.previewMode);

    const dispatch = useDispatch();

    const store = useStore();
    const state = store.getState();

    const Utils = Helper(state, dispatch);

    const handleExit = () => {
        window.location.href = DragwybEditor.adminUrl;
    };

    return <div className="dragwyb-editor__header">
        <div className="dragwyb-editor__details">
            <h2>Dragwyb Form Builder</h2>
            <div className="dragwyb-editor__title">
                <p>
                    {formTitle}
                </p>
            </div>
            <div className="dragwyb-editor__status" data-status={formStatus}>
                <span data-status={formStatus}></span>
                <p>{formStatus.charAt(0).toUpperCase() + formStatus.slice(1)}</p>
            </div>
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
    </div>;
}

export default Header;