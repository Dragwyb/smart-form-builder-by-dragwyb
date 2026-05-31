import FieldBase from '../editor/fieldBase';
import IconsManager from '../editor/components/IconsManager';

window.DragwybEditor = {};
window.DragwybEditor.editor = {};
window.DragwybEditor.editor.extends = {};
window.DragwybEditor.editor.IconsManager = IconsManager;
window.DragwybEditor.editor.extends.FieldBase = FieldBase;

jQuery(document).on('Dragwyb:init', () => {
    jQuery(document).trigger('Dragwyb:editorAppLoaded');
})
