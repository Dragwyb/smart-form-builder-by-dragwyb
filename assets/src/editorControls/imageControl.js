import React from "react";

export default class ImageControl extends DragwybEditor.editor.extends.ControlBase {

    controlName() {
        return 'image';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        // Ensure value is an object
        const image = this.state.value && typeof this.state.value === 'object' && !Array.isArray(this.state.value) ? this.state.value : {};

        return (
            <div className="dragwyb-control dragwyb-control--image" id={`control-${id}`}>
                <this.RenderLabel />
                <div className="dragwyb-gallery-box">
                    {/* Image Preview */}
                    {image.url ? (
                        <div className="dragwyb-gallery-grid">
                            <div className="dragwyb-gallery-thumbnail">
                                <img src={image.url} alt="Selected Image" />
                                <div
                                    className="dragwyb-gallery-remove"
                                    onClick={() => this.removeImage()}
                                    title="Remove Image"
                                >
                                    <i className="fa fa-times"></i>
                                </div>
                            </div>
                        </div>
                    ) : null}

                    {/* Add Button */}
                    <button
                        type="button"
                        className="dragwyb-btn-add-gallery"
                        onClick={() => this.openMediaFrame()}
                        style={{ marginTop: image.url ? '10px' : '0' }}
                    >
                        <i className={image.url ? "fa fa-pencil" : "fa fa-plus-circle"}></i>
                        {image.url ? ' Change Image' : ' Select Image'}
                    </button>
                </div>
            </div>
        );
    }

    openMediaFrame() {
        // If the frame already exists, reopen it
        if (this.frame) {
            this.frame.open();
            return;
        }

        // Create a new media frame
        this.frame = wp.media({
            title: 'Select Image',
            button: {
                text: 'Select Image'
            },
            library: {
                type: 'image'
            },
            multiple: false
        });

        // When image is selected
        this.frame.on('select', () => {
            const attachment = this.frame.state().get('selection').first().toJSON();

            this.updateControlHandler(this.id, {
                id: attachment.id,
                url: attachment.url
            });
        });

        // Pre-select existing image when opening
        this.frame.on('open', () => {
            const selection = this.frame.state().get('selection');
            const savedImage = this.state.value && typeof this.state.value === 'object' && !Array.isArray(this.state.value) ? this.state.value : {};

            if (savedImage.id) {
                const attachment = wp.media.attachment(savedImage.id);
                attachment.fetch();
                selection.add(attachment ? [attachment] : []);
            }
        });

        this.frame.open();
    }

    removeImage() {
        this.updateControlHandler(this.id, { id: '', url: '' });
    }
}
