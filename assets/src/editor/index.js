import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import FormControls from './controls';
import '../../sass/editor.scss';
import DragwybFieldBase from './fieldBase';

DragwybEditor.FieldBase = DragwybFieldBase;

jQuery(document).on('Dragwyb:init', () => {   
    if (DragwybEditor && DragwybEditor.editorContainer) {
        DragwybEditor.Controls = FormControls;
        const container = document.getElementById(DragwybEditor.editorContainer);
        const root = createRoot(container);
        root.render(<App />);
        
        jQuery(document).trigger('Dragwyb:editorInit');
        DragwybBuilder.Hooks.doAction('Dragwyb/Editor/fieldBase');
    }
})