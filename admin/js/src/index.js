import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';

if (DragwybEditor && DragwybEditor.editorContainer) {
    const container = document.getElementById(DragwybEditor.editorContainer);
    const root = createRoot(container);
    root.render(<App />);
}