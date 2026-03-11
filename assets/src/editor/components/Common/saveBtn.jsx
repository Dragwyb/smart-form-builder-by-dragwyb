import { useDispatch, useSelector } from "react-redux";
import { saveForm } from "../../store/actions";
import { Button } from '../Common';

const SaveBtn = () => {
    const formData = useSelector(state => state.form);
    const dispatch = useDispatch();
    const updateSaveState = useSelector(state => state.updateSaveState);

    const handleSave = async () => {
        try {
            dispatch(saveForm(formData));
        } catch (error) {
            console.error('Save failed:', error);
        }
    };

    return <Button onClick={handleSave} className='primary' disabled={updateSaveState}>
        <i className="fas fa-save mr-2" />
        {DragwybBuilder.i18n.save}
    </Button>
}

export default SaveBtn;