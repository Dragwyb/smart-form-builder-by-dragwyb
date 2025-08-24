import Fields from "./fields";
import AdvanceSettings from "./advacne-settings";

const initializeFields = () => {

    const defaultFields = {
        'fields': (args) => new Fields(args),
        'advance-settings': (args) => new AdvanceSettings(args)
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