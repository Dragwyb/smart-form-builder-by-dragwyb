import React from 'react';

export default class WysiwygControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'wysiwyg';
    }

    onRender() {
        if (!this.shouldRender()) return;

        const { id } = this;
        const editorId = `dragwyb-wysiwyg-${id}`;

        setTimeout(() => {
            if (typeof wp !== 'undefined' && wp.editor) {
                const updateHandler = (content) => {
                    this.updateControlHandler(id, content);
                };

                wp.editor.initialize(editorId, {
                    tinymce: {
                        wpautop: true,
                        setup: function (editor) {
                            editor.on('change', function () {
                                editor.save();
                                updateHandler(editor.getContent());
                            });
                            editor.on('keyup', function () {
                                updateHandler(editor.getContent());
                            });
                        }
                    },
                    quicktags: true,
                    mediaButtons: true
                });
            }
        });
    }

    onDestroy() {
        const { id } = this;
        const editorId = `dragwyb-wysiwyg-${id}`;

        if (typeof wp !== 'undefined' && wp.editor) {
            wp.editor.remove(editorId);
        }
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value = settings.default || '' } = this.state;
        const editorId = `dragwyb-wysiwyg-${id}`;

        return (
            <div className="dragwyb-control dragwyb-control--wysiwyg" data-control="wysiwyg" id={`control-${id}`}>
                <this.RenderLabel
                    attr={
                        { htmlFor: editorId }
                    }
                />
                <textarea
                    id={editorId}
                    name={editorId}
                    className="dragwyb-control__wysiwyg"
                    value={value}
                    onChange={(e) => {
                        this.updateControlHandler(id, e.target.value);
                    }}
                    style={{ minHeight: '150px', width: '100%' }}
                />
            </div>
        );
    }

    resetControl() {
        const { id, settings } = this;
        const { default: defaultValue = '' } = settings;
        const value = this.getValidValue(defaultValue);

        this.updateControlHandler(id, value);

        if (typeof wp !== 'undefined' && wp.editor) {
            const editorId = `dragwyb-wysiwyg-${id}`;

            if (typeof window.tinyMCE !== 'undefined') {
                const editor = window.tinyMCE.get(editorId);

                if (editor) {
                    // 2. Direct update: This is instant and doesn't flicker
                    editor.setContent(value);
                } else {
                    // Fallback: If TinyMCE isn't ready or was never initialized
                    const textarea = document.getElementById(editorId);
                    if (textarea) {
                        textarea.value = value;
                    }
                }
            }
        }
    }

    updateControlHandler(key, value) {
        this.setState({ value })
        this.updateControls(key, value);

        // If value is dynamic tag then update wysiwyg editor
        if (value.match(/^\{.*\}$/)) {
            const { id } = this;
            console.log('hello world')
            if (typeof wp !== 'undefined' && wp.editor) {
                const editorId = `dragwyb-wysiwyg-${id}`;

                if (typeof window.tinyMCE !== 'undefined') {
                    const editor = window.tinyMCE.get(editorId);

                    if (editor) {
                        editor.setContent(value);
                    }
                }
            }
        }
    }
}
