import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';

document.addEventListener('DOMContentLoaded', () => {
    /**
     * Moves all elements matching the noticeSelector to directly after the headerSelector,
     * and sets their display to 'block'.
     * 
     * @param {string} noticeSelector - The CSS selector for the notice elements (e.g., '.notice')
     * @param {string} headerSelector - The CSS selector for the header element (e.g., '.dragwyb-dashboard-header')
     */
    function moveNoticesAfterHeader(noticeSelector, headerSelector) {
        // 1. Find the target header element
        const header = document.querySelector(headerSelector);

        if (!header) {
            console.warn(`Could not find header: ${headerSelector}`);
            return;
        }

        header.style.display = 'flex';

        // 2. Find all notice elements
        const notices = document.querySelectorAll(noticeSelector);

        if (notices.length === 0) {
            return;
        }

        // 3. Move all notices immediately after the header
        header.after(...notices);

        // 4. Set display to 'block' for each notice
        notices.forEach(notice => {
            notice.style.display = 'block';
        });
    }

    moveNoticesAfterHeader('.notice', '.dragwyb-dashboard-header');

    const rootElement = document.getElementById('dragwyb-settings-root');
    if (rootElement) {
        const root = createRoot(rootElement);
        root.render(<App />);
    }
});
