export default class GalleryControl extends DragwybEditor.editor.extends.ControlBase {

    controlName() {
        return 'gallery';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        // Ensure value is an array
        const images = Array.isArray(this.state.value) ? this.state.value : [];

        return (
            <div className="dragwyb-control dragwyb-control--gallery" id={`control-${id}`}>
                <this.RenderLabel />
                <div className="dragwyb-gallery-box">
                    {/* Image Grid Preview */}
                    <div className="dragwyb-gallery-grid">
                        {images.map((image, index) => (
                            <div key={image.id} className="dragwyb-gallery-thumbnail">
                                <img src={image.url} alt={`Gallery item ${index}`} />
                                <div
                                    className="dragwyb-gallery-remove"
                                    onClick={() => this.removeImage(image.id)}
                                    title="Remove Image"
                                >
                                    <DragwybEditor.editor.IconsManager.Render icon={{ type: 'solid', icon: 'times' }} />
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* Add Button */}
                    <button
                        type="button"
                        className="dragwyb-btn-add-gallery"
                        onClick={() => this.openMediaFrame()}
                    >
                        <DragwybEditor.editor.IconsManager.Render icon={{ type: 'solid', icon: 'plus-circle' }} />
                        {images.length > 0 ? ' Edit Gallery' : ' Add Images'}
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
            title: 'Select Images',
            button: {
                text: 'Insert Gallery'
            },
            library: {
                type: 'image'
            },
            multiple: true
        });

        // When images are selected
        this.frame.on('select', () => {
            const selection = this.frame.state().get('selection');
            const newImages = [];

            selection.map((attachment) => {
                attachment = attachment.toJSON();
                newImages.push({
                    id: attachment.id,
                    url: attachment.url
                });
            });

            this.updateControlHandler(this.id, newImages);
        });

        // Pre-select existing images when opening
        this.frame.on('open', () => {
            const selection = this.frame.state().get('selection');
            const savedImages = Array.isArray(this.state.value) ? this.state.value : [];

            savedImages.forEach((image) => {
                const attachment = wp.media.attachment(image.id);
                attachment.fetch();
                selection.add(attachment ? [attachment] : []);
            });
        });

        this.frame.open();
    }

    removeImage(idToRemove) {
        const currentImages = Array.isArray(this.state.value) ? this.state.value : [];
        const newImages = currentImages.filter(img => img.id !== idToRemove);
        this.updateControlHandler(this.id, newImages);
    }
}