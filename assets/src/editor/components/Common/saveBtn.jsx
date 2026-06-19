import { useDispatch, useSelector } from "react-redux";
import { saveForm } from "../../store/actions";
import { Button } from '../Common';
import IconsManager from "../IconsManager";

const SaveBtn = () => {
    const formData = useSelector(state => state.form);
    const dispatch = useDispatch();
    const updateSaveState = useSelector(state => state.updateSaveState);

    const handleSave = async () => {
        if (updateSaveState) return;
        try {
            dispatch(saveForm(formData));
        } catch (error) {
            console.error('Save failed:', error);
        }
    };

    return <Button onClick={handleSave} className='primary' disabled={updateSaveState}>
        <IconsManager.renderIcons icon={{ type: 'solid', icon: 'save' }} />
        {updateSaveState ? <>Saving<span className="dragwyb-text-loader" style={{ '--dragwyb-text-loader-delay': '0s' }}></span><span className="dragwyb-text-loader" style={{ '--dragwyb-text-loader-delay': '0.1s' }}></span><span className="dragwyb-text-loader" style={{ '--dragwyb-text-loader-delay': '0.2s' }}></span></> : DragwybBuilder.i18n.save
        }
    </Button >
}

export default SaveBtn;