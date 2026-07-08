
import { useEffect } from "react";
import { useSelector } from "react-redux";

const updateFieldWrapperClass = (wrapperClass, Id, type, attributes, utils) => {
    if (attributes && attributes.label_icon && attributes.label_icon.icon && attributes.label_icon.icon !== '') {
        const labelIconsPosition = utils.getToolbarSetting({ toolbar: 'style', settingId: 'label_icon_position', defaultValue: 'before' })

        const labelPositionClass = `dragwyb-field-label-icon-${labelIconsPosition}`;
        wrapperClass.push(labelPositionClass)
    }

    return wrapperClass;
}

const UpdateFormLabelPosition = () => {
    const labelPosition = useSelector((state) => state?.form?.style?.label_position || 'top');
    const labelFloat = useSelector((state) => state?.form?.style?.floating_style || 'outlined');
    const formBgType = useSelector((state) => state?.form?.style?.form_container_bg_background || 'color');
    const labelIconsPosition = useSelector((state) => state?.form?.style?.label_icon_position || 'before');
    const stepIndicatorType = useSelector((state) => state?.form?.style?.step_indicator_type || 'numbers');

    const iframeNode = useSelector(state => state?.iframeEle);

    useEffect(() => {
        if (!iframeNode) {
            return;
        }

        const stepIndicators = iframeNode.querySelectorAll('.dragwyb-step-indicator-container.dragwyb-editor-preview');
        if (stepIndicators && stepIndicators.length > 0) {
            stepIndicators.forEach(stepIndicator => {
                stepIndicator.setAttribute('data-step-indicator', stepIndicatorType);
            })
        }
    }, [iframeNode, stepIndicatorType]);

    useEffect(() => {
        if (!iframeNode || !labelPosition) {
            return;
        }

        const form = iframeNode.querySelector('.dragwyb-form-wrapper');

        if (!form) {
            return;
        }

        const existingClassList = form.classList;

        const filterClassList = Array.from(existingClassList).filter((item) => !item.startsWith('dragwyb-layout-') && !item.startsWith('dragwyb-float-') && !item.startsWith('dragwyb-bg-'));

        filterClassList.push(`dragwyb-layout-${labelPosition}`);

        filterClassList.push(`dragwyb-bg-${formBgType}`);

        if (labelPosition === 'floating') {
            filterClassList.push(`dragwyb-float-${labelFloat}`);
        }

        form.className = filterClassList.join(' ');
    }, [labelPosition, iframeNode, labelFloat, formBgType]);

    useEffect(() => {
        if (!iframeNode || !labelIconsPosition) {
            return;
        }

        const labelIcons = iframeNode.querySelectorAll('.dragwyb-label-icon');

        if (!labelIcons || labelIcons.length === 0) {
            return;
        }

        const labelPositionClass = `dragwyb-field-label-icon-${labelIconsPosition}`;

        labelIcons.forEach((labelIcon) => {
            const fieldWrapper = labelIcon.closest('.dragwyb-field-wrapper');
            const fieldWrapperCLassList = fieldWrapper.classList;
            const filterClassList = Array.from(fieldWrapperCLassList).filter((item) => !item.startsWith('dragwyb-field-label-icon-'));

            filterClassList.push(labelPositionClass);
            fieldWrapper.className = filterClassList.join(' ');
        });

        DragwybBuilder.Hooks.removeFilter('Dragwyb/Field/WrapperClass', updateFieldWrapperClass)

        DragwybBuilder.Hooks.addFilter('Dragwyb/Field/WrapperClass', updateFieldWrapperClass);

    }, [labelIconsPosition, iframeNode])

    return null;
}

export default UpdateFormLabelPosition;