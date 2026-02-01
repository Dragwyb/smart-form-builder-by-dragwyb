export default class RawHtmlControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'raw_html';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { raw } = settings;

        const value = raw || '';

        if (!value || value === '') return <></>;

        return (
            <div className="dragwyb-control dragwyb-control--raw-html" data-control="raw_html" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label" htmlFor={id}>
                        {settings.label}
                    </label>
                )}
                <div
                    dangerouslySetInnerHTML={{ __html: value }}
                />
            </div>
        );
    }
}