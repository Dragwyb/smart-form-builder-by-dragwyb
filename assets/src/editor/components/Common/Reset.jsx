import { LiaUndoAltSolid } from "react-icons/lia";
import { __ } from "@wordpress/i18n";

const Reset = ({ handler, disabled }) => {

    const onClickHandler = () => {
        if (disabled) return;
        handler();
    }

    const title = disabled ? '' : __('Reset to default', 'dragwyb-form-builder')

    return (
        <div className={`dragwyb-control_reset${disabled ? ' disabled' : ''}`} onClick={onClickHandler} title={title}>
            <LiaUndoAltSolid size="1rem" />
        </div>
    )
}

export default Reset;