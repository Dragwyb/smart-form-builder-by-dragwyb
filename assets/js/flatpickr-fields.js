/**
 * Flatpickr handlers for Dragwyb date and time fields.
 */
class DragwybFlatpickrFieldBase extends DragwybBuilder.DragwybFormFrontendBase {
	selector() {
		return '';
	}

	init() {
		if (typeof window.flatpickr !== 'function' || !this.selector()) {
			return;
		}

		this.$container.find(this.selector()).each((_, el) => {
			this.initField(el);
		});
	}

	initField(input) {
		if (jQuery(input).hasClass('dragwyb-use-native')) {
			return;
		}

		window.flatpickr(input, this.buildOptions(input));
	}

	parseConfig(input) {
		const raw = input.getAttribute('data-fp-config');
		if (!raw) {
			return {};
		}

		try {
			return JSON.parse(raw);
		} catch (e) {
			return {};
		}
	}

	buildOptions() {
		return {};
	}
}

class DragwybDateField extends DragwybFlatpickrFieldBase {
	selector() {
		return 'input.dragwyb-date-field';
	}

	resolveBound(bound) {
		if (bound === null || bound === undefined || bound === '') {
			return null;
		}

		if (bound === 'today') {
			return 'today';
		}

		if (typeof bound === 'object' && bound.type === 'relative') {
			const days = parseInt(bound.days, 10) || 0;
			return new Date().fp_incr(days);
		}

		return bound;
	}

	parseDateList(text) {
		if (!text || typeof text !== 'string') {
			return [];
		}

		const items = [];

		text.split(/\r?\n|,/).forEach((chunk) => {
			const line = chunk.trim();
			if (!line) {
				return;
			}

			if (line.indexOf(':') !== -1 && !/^\d{4}-\d{2}-\d{2}$/.test(line)) {
				const parts = line.split(':').map((part) => part.trim());
				if (parts.length === 2 && parts[0] && parts[1]) {
					items.push({ from: parts[0], to: parts[1] });
					return;
				}
			}

			items.push(line);
		});

		return items;
	}

	buildOptions(input) {
		const config = this.parseConfig(input);
		const options = {
			allowInput: config.allowInput !== false,
			dateFormat: config.dateFormat || 'Y-m-d',
			mode: config.mode || 'single',
		};

		if (config.enableTime) {
			options.enableTime = true;
			options.time_24hr = !!config.time_24hr;
			if (config.minTime) {
				options.minTime = config.minTime;
			}
			if (config.maxTime) {
				options.maxTime = config.maxTime;
			}
		}

		if (config.inline) {
			options.inline = true;
		}
		if (config.weekNumbers) {
			options.weekNumbers = true;
		}

		if (config.altInput) {
			options.altInput = true;
			options.altFormat = config.altFormat || 'F j, Y';
		}

		if (config.mode === 'multiple' && config.conjunction) {
			options.conjunction = config.conjunction;
		}

		const minDate = this.resolveBound(config.minDate);
		const maxDate = this.resolveBound(config.maxDate);
		if (minDate !== null) {
			options.minDate = minDate;
		}
		if (maxDate !== null) {
			options.maxDate = maxDate;
		}

		const enableDates = this.parseDateList(config.enableDates);
		if (enableDates.length) {
			options.enable = enableDates;
		} else {
			const disable = [];

			if (config.disableWeekends) {
				disable.push((date) => date.getDay() === 0 || date.getDay() === 6);
			}

			this.parseDateList(config.disableDates).forEach((item) => disable.push(item));

			if (disable.length) {
				options.disable = disable;
			}
		}

		return options;
	}
}

class DragwybTimeField extends DragwybFlatpickrFieldBase {
	selector() {
		return 'input.dragwyb-time-field';
	}

	buildOptions(input) {
		const config = this.parseConfig(input);
		const options = {
			allowInput: config.allowInput !== false,
			enableTime: true,
			noCalendar: true,
			dateFormat: config.dateFormat || 'H:i',
			time_24hr: !!config.time_24hr,
		};

		if (config.minTime) {
			options.minTime = config.minTime;
		}
		if (config.maxTime) {
			options.maxTime = config.maxTime;
		}

		return options;
	}
}

jQuery(document).on('Dragwyb:frontendInit', () => {
	DragwybBuilder.Hooks.addAction('dragwyb/frontend/form_ready', (container, formId) => {
		new DragwybDateField(container, formId);
		new DragwybTimeField(container, formId);
	});
});

jQuery(document).on('Dragwyb:editorAppLoaded', () => {
	DragwybBuilder.Hooks.addAction('dragwyb/editorPreview/form_ready', (container, formId) => {
		new DragwybDateField(container, formId);
		new DragwybTimeField(container, formId);
	});
});
