export default class RawHtmlControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'raw_html';
    }

    sanitizeHtml(html) {
        if (!html || typeof html !== 'string') return '';
        if (typeof document === 'undefined') return '';

        const template = document.createElement('template');
        template.innerHTML = html;
        const allowedTags = ['A', 'B', 'BR', 'CODE', 'DIV', 'EM', 'I', 'LI', 'OL', 'P', 'SPAN', 'STRONG', 'UL'];
        const allowedAttrs = ['aria-label', 'class', 'href', 'rel', 'target', 'title'];

        template.content.querySelectorAll('*').forEach((node) => {
            if (!allowedTags.includes(node.tagName)) {
                node.replaceWith(document.createTextNode(node.textContent || ''));
                return;
            }

            [...node.attributes].forEach((attr) => {
                const name = attr.name.toLowerCase();
                const value = attr.value || '';

                if (!allowedAttrs.includes(name) || name.startsWith('on') || /^\s*javascript:/i.test(value)) {
                    node.removeAttribute(attr.name);
                }
            });

            if (node.tagName === 'A') {
                node.setAttribute('rel', 'noopener noreferrer');
            }
        });

        return template.innerHTML;
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { raw } = settings;

        const value = this.sanitizeHtml(raw || '');

        if (!value || value === '') return <></>;

        return (
            <div className="dragwyb-control dragwyb-control--raw-html" data-control="raw_html" id={`control-${id}`}>
                <this.RenderLabel
                    attr={
                        { htmlFor: id }
                    }
                />
                <div
                    dangerouslySetInnerHTML={{ __html: value }}
                />
            </div>
        );
    }
}
