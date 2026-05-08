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
        }, 100);
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
}
