export default class PopoverToggleControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return "popover-toggle";
    }

    constructor(props) {
        super(props);

        this.boundClickHandler = this.documentClickHandler.bind(this);
    }

    documentClickHandler(e) {
        const parentToogle = e?.target?.closest(`#control-${this.id}`);
        const ToogleEle = e?.target?.id === `control-${this.id}`;
        const activePopOver = e?.target?.closest('.dragwyb-popover-wrapper');
        const responsiveComponent = e?.target?.closest('.dragwyb-editor__responsive-devices');

        const isEditorClick = e?.target?.closest('#dragwyb-form-builder-editor-wrapper');

        if (parentToogle || ToogleEle || responsiveComponent || !isEditorClick) {
            return;
        }

        if (activePopOver) {
            if (activePopOver.classList.contains('active')) {
                return;
            }
        }

        document.removeEventListener('click', this.boundClickHandler);
        this.Utils.updateactivePopoverKey({ activePopoverKey: false });
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id, Utils } = this;
        const { value } = this.state;
        const isActive = id === value;
        const labelInline = settings.label_inline || true;

        const clickHandler = (e) => {
            document.removeEventListener('click', this.boundClickHandler);
            const ele = e.target;
            const popoverWrp = jQuery(ele).closest('.dragwyb-setting-row').next('.dragwyb-popover-wrapper');
            const popoverKey = popoverWrp.data('popover-key');

            if (popoverWrp.hasClass('active')) {
                Utils.updateactivePopoverKey({ activePopoverKey: false });
                return;
            }

            Utils.updateactivePopoverKey({ activePopoverKey: popoverKey });
            document.addEventListener('click', this.boundClickHandler);
        }

        return (
            <div className="dragwyb-control dragwyb-control--popover-toggle" data-control="popover-toggle" id={`control-${id}`}>
                <div
                    id={id}
                    type="button"
                    className={`dragwyb-popover__trigger ${isActive ? "is-active" : ""}${labelInline ? " dragwyb-label-inline" : ""}`}
                    aria-pressed={isActive}
                    onClick={clickHandler}
                >
                    <this.RenderLabel />
                    {settings.icon && (
                        <span className="dragwyb-popover-toggle__icon"><DragwybEditor.editor.IconsManager.Render icon={settings.icon} /></span>
                    )}
                </div>
            </div>
        );
    }
}