
import { useEffect } from "react";
import { useSelector } from "react-redux";

const UpdateFormLabelPosition = () => {
    const labelPosition = useSelector((state) => state?.form?.style?.label_position || 'top');
    const labelFloat = useSelector((state) => state?.form?.style?.floating_style || 'outlined');
    const iframeNode = useSelector(state => state?.iframeEle);

    useEffect(() => {
        if (!iframeNode || !labelPosition) {
            return;
        }

        const form = iframeNode.querySelector('.dragwyb-form-wrapper');

        if (!form) {
            return;
        }

        const existingClassList = form.classList;

        const filterClassList = Array.from(existingClassList).filter((item) => !item.startsWith('dragwyb-layout-') && !item.startsWith('dragwyb-float-'));

        filterClassList.push(`dragwyb-layout-${labelPosition}`);

        if (labelPosition === 'floating') {
            filterClassList.push(`dragwyb-float-${labelFloat}`);
        }

        form.className = filterClassList.join(' ');
    }, [labelPosition, iframeNode, labelFloat]);

    return null;
}

export default UpdateFormLabelPosition;