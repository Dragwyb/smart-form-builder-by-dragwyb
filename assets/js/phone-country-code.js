/**
 * Country code handler for Dragwyb phone fields (intl-tel-input).
 */
class DragwybPhoneCountryCode extends DragwybBuilder.DragwybFormFrontendBase {
	init() {
		this.iti = {};
		this.config = window.DragwybPhoneCountryData || {};
		this.translations = window.CCFEFCountryTranslations || {};

		this.$container.find('.dragwyb-phone-field input[type="tel"]').each((_, el) => {
			if (el.dataset.countryCode === 'yes') {
				this.initField(jQuery(el));
			} else {
				this.destroyField(el);
			}
		});

		this.bindValidation();
	}

	parseCountryList(value) {
		if (!value || typeof value !== 'string') {
			return [];
		}
		return value
			.split(',')
			.map((code) => code.trim().toLowerCase())
			.filter((code) => /^[a-z]{2}$/.test(code));
	}

	getAllowedCountries(includeCountries, excludeCountries) {
		if (!includeCountries.length) {
			return [];
		}
		return includeCountries.filter((code) => !excludeCountries.includes(code));
	}

	getConfigSignature(input) {
		return input ? String(input.getAttribute('data-iti-config') || '') : '';
	}

	destroyField(input) {
		if (!input || typeof window.intlTelInput !== 'function') {
			return;
		}

		const instance = window.intlTelInput.getInstance && window.intlTelInput.getInstance(input);
		if (instance && typeof instance.destroy === 'function') {
			instance.destroy();
		}

		if (typeof input.dataset.dragwybPlaceholder !== 'undefined') {
			input.setAttribute('placeholder', input.dataset.dragwybPlaceholder);
		}

		delete input.dataset.dragwybPlaceholder;
		delete input.dataset.dragwybItiInit;
		delete input.dataset.dragwybItiConfig;
	}

	/**
	 * Strip dial code from a user-provided placeholder when hide/separate.
	 * Skips empty placeholders so ITI auto-placeholders are left alone.
	 */
	syncPlaceholder(input, dialCodeVisibility) {
		const original = input.dataset.dragwybPlaceholder;
		if (!original || !original.trim()) {
			return;
		}

		const hideDialCode = dialCodeVisibility === 'hide' || dialCodeVisibility === 'separate';
		const placeholder = hideDialCode ? original.replace(/^\s*\+\d+[\s-]*/, '') : original;
		input.setAttribute('placeholder', placeholder);
	}

	initField($input) {
		const input = $input[0];
		if (!input || typeof window.intlTelInput !== 'function') {
			return;
		}

		const dialCodeVisibility = input.getAttribute('data-dial-code-visibility') || 'show';
		const signature = this.getConfigSignature(input);
		if (input.dataset.dragwybItiInit === '1' && input.dataset.dragwybItiConfig === signature) {
			this.syncPlaceholder(input, dialCodeVisibility);
			return;
		}

		if (input.dataset.dragwybItiInit === '1' || $input.closest('.iti').length) {
			this.destroyField(input);
		}

		// Capture the user placeholder before ITI may replace it.
		if (typeof input.dataset.dragwybPlaceholder === 'undefined') {
			input.dataset.dragwybPlaceholder = input.getAttribute('placeholder') || '';
		}

		const uniqueId = $input.attr('id') || `phone_${Date.now()}`;
		const includeCountries = this.parseCountryList($input.attr('data-include-countries'));
		const excludeCountries = this.parseCountryList($input.attr('data-exclude-countries'));
		const allowedCountries = this.getAllowedCountries(includeCountries, excludeCountries);
		const commonCountries = $input.attr('data-common-countries') === 'same';
		const strictMode = $input.attr('data-strict-mode') === 'yes';
		const showFlags = String($input.attr('data-show-flags') || '') === 'yes';
		const langCode = $input.attr('data-internationalisation') || 'en';
		let defaultCountry = String($input.attr('data-default-country') || 'us').trim().toLowerCase();
		if (!/^[a-z]{2}$/.test(defaultCountry)) {
			defaultCountry = 'us';
		}

		if (allowedCountries.length && !allowedCountries.includes(defaultCountry)) {
			defaultCountry = allowedCountries[0];
		} else if (!includeCountries.length && excludeCountries.includes(defaultCountry)) {
			defaultCountry = excludeCountries.includes('us') ? '' : 'us';
		}

		const i18nMap = this.translations[langCode] || this.translations.en || {};
		const itiOptions = {
			initialCountry: defaultCountry,
			utilsScript: this.config.utilsScript,
			strictMode: !!strictMode,
			separateDialCode: dialCodeVisibility === 'separate',
			nationalMode: dialCodeVisibility === 'hide',
			i18n: i18nMap,
			formatOnDisplay: false,
			formatAsYouType: true,
			containerClass: 'dragwyb-intl-container' + (showFlags ? '' : ' iti--hide-flags'),
			useFullscreenPopup: false,
			customPlaceholder: (selectedCountryPlaceholder, selectedCountryData) => {
				if (commonCountries || !selectedCountryData || !selectedCountryPlaceholder || !selectedCountryData.dialCode) {
					return 'No country found';
				}

				let placeHolder = selectedCountryPlaceholder;
				if (selectedCountryData.iso2 === 'in') {
					placeHolder = selectedCountryPlaceholder.replace(/^0+/, '');
				}

				if (dialCodeVisibility === 'separate' || dialCodeVisibility === 'hide') {
					return placeHolder;
				}

				// ITI international examples already include the dial code — don't prepend again.
				if (/^\s*\+/.test(placeHolder)) {
					return placeHolder;
				}

				return `+${selectedCountryData.dialCode} ${placeHolder}`;
			},
		};

		// ITI ignores excludeCountries when onlyCountries is set — pass the filtered list.
		if (includeCountries.length) {
			itiOptions.onlyCountries = allowedCountries.length ? allowedCountries : includeCountries;
		} else if (excludeCountries.length) {
			itiOptions.excludeCountries = excludeCountries;
		}

		const iti = window.intlTelInput(input, itiOptions);
		if (defaultCountry && typeof iti.setCountry === 'function') {
			iti.setCountry(defaultCountry);
		}

		this.syncPlaceholder(input, dialCodeVisibility);

		input.dataset.dragwybItiInit = '1';
		input.dataset.dragwybItiConfig = signature;
		this.iti[uniqueId] = {
			iti,
			dialCodeVisibility,
			showFlags,
		};

		if ((commonCountries || (includeCountries.length && !allowedCountries.length)) && iti.countryList) {
			iti.countryList.style.display = 'none';
		}

		this.bindCountryCodeSync(iti, uniqueId);
		if (showFlags) {
			this.applyCustomFlags($input);
		}
		$input.removeAttr('pattern');
	}

	getTelInput(iti) {
		return iti && iti.telInput ? iti.telInput : null;
	}

	bindCountryCodeSync(iti, uniqueId) {
		const inputElement = this.getTelInput(iti);
		if (!inputElement) {
			return;
		}

		const meta = this.iti[uniqueId];
		let previousCode = `+${iti.getSelectedCountryData().dialCode || ''}`;

		const handleCountryChange = (e) => {
			if (meta.showFlags) {
				this.applyCustomFlags(jQuery(inputElement));
			}

			const currentCountryData = iti.getSelectedCountryData();
			const currentCode = `+${currentCountryData.dialCode || ''}`;

			if (e.currentTarget.value.startsWith(String(currentCountryData.dialCode || ''))) {
				this.updateCountryCodeHandler(e.currentTarget, '+', previousCode, meta.dialCodeVisibility);
			} else {
				this.updateCountryCodeHandler(e.currentTarget, currentCode, previousCode, meta.dialCodeVisibility);
				previousCode = currentCode;
			}

			this.validateSingleInput(inputElement, uniqueId);
		};

		inputElement.addEventListener('input', handleCountryChange);
		inputElement.addEventListener('countrychange', handleCountryChange);

		jQuery(inputElement).on('focus', () => {
			jQuery(inputElement).closest('.iti').addClass('input-focus');
		}).on('blur', () => {
			jQuery(inputElement).closest('.iti').removeClass('input-focus');
			this.ensureDialCodeInValue(uniqueId);
			this.validateSingleInput(inputElement, uniqueId);
		});
	}

	updateCountryCodeHandler(element, currentCode, previousCode, dialCodeVisibility) {
		let value = element.value;

		if (!currentCode || currentCode === '+undefined' || ['', '+'].includes(value)) {
			return;
		}

		if (currentCode !== previousCode) {
			value = value.replace(new RegExp(`^\\${previousCode}`), '');
		}

		if (!value.startsWith(currentCode)) {
			value = value.replace(/\+/g, '');
			if (value.startsWith('0')) {
				value = value.replace(/^0+/, '');
			}
			element.value = dialCodeVisibility === 'separate' || dialCodeVisibility === 'hide' ? value : currentCode + value;
		} else if (value.length > 12) {
			const plainCode = currentCode.replace('+', '');
			const doublePrefix = `+${plainCode}${plainCode}`;
			if (value.startsWith(doublePrefix)) {
				element.value = `+${value.slice(currentCode.length)}`;
			}
		}
	}

	applyCustomFlags($scope) {
		const $root = $scope.closest('.dragwyb-phone-field');
		const $searchRoot = $root.length ? $root : $scope.closest('.iti');
		const pluginDir = this.config.pluginDir || '';

		$searchRoot.find('.iti__country-container .iti__flag:not(.iti__globe)').each(function () {
			const countryClass = String(this.className || '')
				.split(/\s+/)
				.find((cls) => cls.indexOf('iti__') === 0 && cls !== 'iti__flag' && cls !== 'iti__globe');

			if (!countryClass) {
				return;
			}

			const iso = countryClass.replace('iti__', '');
			if (!iso) {
				return;
			}

			this.style.backgroundImage = `url('${pluginDir}assets/flags/${iso}.svg')`;
			this.style.backgroundPosition = 'center';
			this.style.backgroundSize = 'cover';
			this.style.backgroundRepeat = 'no-repeat';
		});
	}

	ensureDialCodeInValue(uniqueId) {
		const meta = this.iti[uniqueId];
		if (!meta) {
			return;
		}

		const input = this.getTelInput(meta.iti);
		if (!input || !input.value) {
			return;
		}

		input.value = String(input.value).replace(/[^0-9+]/g, '');

		const dialCode = `+${meta.iti.getSelectedCountryData().dialCode || ''}`;

		if ((meta.dialCodeVisibility === 'separate' || meta.dialCodeVisibility === 'hide') && !input.value.startsWith('+')) {
			input.value = dialCode + input.value;
		}

		if (input.value.startsWith(dialCode + '0')) {
			input.value = input.value.replace(dialCode + '0', dialCode);
		}
	}

	validateSingleInput(inputElement, uniqueId) {
		const meta = this.iti[uniqueId];
		const errorMap = this.config.errorMap || [];

		if (!meta || !inputElement.value) {
			inputElement.setCustomValidity('');
			return true;
		}

		this.ensureDialCodeInValue(uniqueId);

		let isWrongCountryError = false;
		const onlyCountries = meta.iti.options.onlyCountries || [];

		if (onlyCountries.length > 0 && meta.iti.dialCodeToIso2Map) {
			const inputVal = inputElement.value;
			const matchedDial = Object.keys(meta.iti.dialCodeToIso2Map).find((code) => inputVal.startsWith('+' + code));
			if (!matchedDial) {
				isWrongCountryError = true;
			} else {
				const possibleCountries = meta.iti.dialCodeToIso2Map[matchedDial] || [];
				if (!possibleCountries.some((iso2) => onlyCountries.includes(iso2))) {
					isWrongCountryError = true;
				}
			}
		}

		if (meta.iti.isValidNumber() && !isWrongCountryError) {
			inputElement.setCustomValidity('');
			return true;
		}

		if (isWrongCountryError) {
			inputElement.setCustomValidity(errorMap[1] || 'Invalid country code.');
			return false;
		}

		const errorType = meta.iti.getValidationError();
		if (errorType !== undefined && errorMap[errorType]) {
			if (meta.dialCodeVisibility === 'separate' || meta.dialCodeVisibility === 'hide') {
				const dialCode = `+${meta.iti.getSelectedCountryData().dialCode || ''}`;
				if (inputElement.value.startsWith(dialCode)) {
					inputElement.value = inputElement.value.substring(dialCode.length);
				}
			}
			inputElement.setCustomValidity(errorMap[errorType]);
			return false;
		}

		inputElement.setCustomValidity(errorMap[0] || 'Invalid phone number.');
		return false;
	}

	prepareAllValues() {
		Object.keys(this.iti).forEach((uniqueId) => {
			this.ensureDialCodeInValue(uniqueId);
			const input = this.getTelInput(this.iti[uniqueId].iti);
			if (input) {
				this.validateSingleInput(input, uniqueId);
			}
		});
	}

	bindValidation() {
		if (!this.formId) {
			return;
		}

		DragwybBuilder.Hooks.addAction(`dragwyb/frontend/form/before_submit${this.formId}`, () => {
			this.prepareAllValues();
		});
	}
}

const initPhoneCountryCode = (container, formId) => {
	new DragwybPhoneCountryCode(container, formId);
};

jQuery(document).on('Dragwyb:frontendInit', () => {
	DragwybBuilder.Hooks.addAction('dragwyb/frontend/form_ready', initPhoneCountryCode);
});

jQuery(document).on('Dragwyb:editorAppLoaded', () => {
	DragwybBuilder.Hooks.addAction('dragwyb/editorPreview/form_ready', initPhoneCountryCode);
});
