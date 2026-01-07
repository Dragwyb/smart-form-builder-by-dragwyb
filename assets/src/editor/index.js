import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import DragwybControlBase from './controlBase';
import DragwybFieldBase from './fieldBase';
import DragwybToolbarBase from './toolbarBase';
import '../../sass/editor.scss';

if (!DragwybEditor.editor) {
    DragwybEditor.editor = {};
}

if (!DragwybEditor.editor.extends) {
    DragwybEditor.editor.extends = {};
}

DragwybEditor.editor.extends.FieldBase = DragwybFieldBase;
DragwybEditor.editor.extends.ControlBase = DragwybControlBase;
DragwybEditor.editor.extends.ToolbarBase = DragwybToolbarBase;

Object.freeze(DragwybEditor);

const formIdExist = () => {
    const url = new URL(window.location.href);
    const params = url.searchParams;

    if (!params.has('form_id')) {
        const formId = DragwybBuilder.formId;
        params.set('form_id', formId); // Replace '123' with your dynamic value
        url.search = params.toString();
        window.history.replaceState({}, '', url);
    }
}

jQuery(document).on('Dragwyb:init', () => {
    if (DragwybEditor && DragwybEditor.editorContainer) {
        const container = document.getElementById(DragwybEditor.editorContainer);
        const root = createRoot(container);

        const theme = localStorage.getItem("DragwybEditorTheme") || 'light';
        const oldClass = theme === 'light' ? 'dark' : 'light';
        const bodyEleCls = document.body.classList;

        if (oldClass === 'dark') {
            bodyEleCls.remove(oldClass)
        } else {
            bodyEleCls.add(theme);
        }

        // Check form id exists or not in URL if not then appen it using js for prevent page reload id not removed.
        formIdExist();

        root.render(<App />);

        jQuery(document).trigger('Dragwyb:editorInit');
    }
})