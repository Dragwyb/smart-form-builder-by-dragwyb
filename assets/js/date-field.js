/**
 * Flatpickr date handler for Dragwyb date fields.
 */
class DragwybDateField extends DragwybBuilder.DragwybFormFrontendBase {
	init() {
		if (typeof window.flatpickr !== 'function') {
			return;
		}

		this.$container.find('input.dragwyb-date-field').each((_, el) => {
			const $input = jQuery(el);

			if ($input.hasClass('dragwyb-use-native')) {
				return;
			}

			window.flatpickr(el, {
				enableTime: false,
				dateFormat: 'Y-m-d',
				allowInput: true,
				minDate: el.getAttribute('min') || null,
				maxDate: el.getAttribute('max') || null,
			});
		});
	}
}

jQuery(document).on('Dragwyb:frontendInit', () => {
	DragwybBuilder.Hooks.addAction('dragwyb/frontend/form_ready', (container, formId) => {
		new DragwybDateField(container, formId);
	});
});

jQuery(document).on('Dragwyb:editorAppLoaded', () => {
	DragwybBuilder.Hooks.addAction('dragwyb/editorPreview/form_ready', (container, formId) => {
		new DragwybDateField(container, formId);
	});
});
