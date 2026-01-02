import Fields from "./fields";

const initializeFields = () => {

    const defaultFields = {
        'fields': (args) => new Fields(args),
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