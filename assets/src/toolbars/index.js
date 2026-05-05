import Fields from "./fields";
import AfterSubmission from "./after-submission";

const initializeFields = () => {

    const defaultFields = {
        'fields': (args) => new Fields(args),
        'after-submission': (args) => new AfterSubmission(args),
    };

    Object.keys(defaultFields).forEach(key =>
        DragwybBuilder.Hooks.addFilter(
            'Dragwyb/Editor/toolbarRender/' + key,
            (...args) => defaultFields[key](args)
        )
    );
};

jQuery(document).on('Dragwyb:editorInit', () => {
    initializeFields();
});