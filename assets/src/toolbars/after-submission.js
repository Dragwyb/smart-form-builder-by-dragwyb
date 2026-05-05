import { __ } from '@wordpress/i18n';

class AfterSubmissions extends DragwybEditor.editor.extends.ToolbarBase {
    constructor(args) {
        super(args);
    }

    toolBarName() {
        return 'after-submission';
    }

    getToolbarSettings() {
        this.settings.id = this.id;
        this.settings.panelHeading = this.settings.label ?? this.toolBarName();

        let afterSubmissionControls = { ...this.settings.controls };

        let selectedOptions = this.getToolbarValue()?.after_submissions || [];

        const sortedControls = {};

        selectedOptions.forEach(key => {
            sortedControls[key] = {};
        });

        const afterSubmissionControl = afterSubmissionControls.after_submissions;

        delete afterSubmissionControls.after_submissions;

        Object.keys(afterSubmissionControls).forEach(controlKey => {
            const control = afterSubmissionControls[controlKey];

            if (control.conditions && control.conditions.after_submissions) {
                const conditionValue = control.conditions.after_submissions;
                if (sortedControls[conditionValue]) {
                    delete afterSubmissionControls[controlKey];
                    sortedControls[conditionValue][controlKey] = control;
                }
            }
        });

        Object.keys(sortedControls).forEach(key => {
            afterSubmissionControls = { ...afterSubmissionControls, ...sortedControls[key] };
        });

        afterSubmissionControls = { ...{ after_submissions: afterSubmissionControl }, ...afterSubmissionControls };

        this.settings.controls = afterSubmissionControls;

        return this.settings;
    }
}

export default AfterSubmissions;
