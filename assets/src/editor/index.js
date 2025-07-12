import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import FormControls from './controls';
import '../../sass/editor.scss';

jQuery(document).on('Dragwyb:init', () => {
    console.log("hello world")    
    if (DragwybEditor && DragwybEditor.editorContainer) {
        DragwybEditor.Controls = FormControls;
        const container = document.getElementById(DragwybEditor.editorContainer);
        const root = createRoot(container);
        root.render(<App />);
    }
})