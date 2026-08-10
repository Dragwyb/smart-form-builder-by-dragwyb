/**
 * Input mask handler for Dragwyb Mask fields.
 */
class DragwybMaskField extends DragwybBuilder.DragwybFormFrontendBase {
	init() {
		this.config = window.DragwybMaskData || {};
		this.errorMessages = this.config.errorMessages || {};
		this.pluginUrl = this.config.pluginUrl || '';

		this.cardLogos = {
			Visa: this.pluginUrl + 'assets/svg-icons/visa-logo.svg',
			MasterCard: this.pluginUrl + 'assets/svg-icons/mastercard-logo.svg',
			'American Express': this.pluginUrl + 'assets/svg-icons/amex-logo.svg',
			Discover: this.pluginUrl + 'assets/svg-icons/discover-logo.svg',
			JCB: this.pluginUrl + 'assets/svg-icons/jcb-logo.svg',
			'Diners Club': this.pluginUrl + 'assets/svg-icons/cc-logo.svg',
			Maestro: this.pluginUrl + 'assets/svg-icons/maestro-logo.svg',
			UnionPay: this.pluginUrl + 'assets/svg-icons/cc-logo.svg',
			RuPay: this.pluginUrl + 'assets/svg-icons/rupay-logo.svg',
			Unknown: this.pluginUrl + 'assets/svg-icons/cc-logo.svg',
		};

		this.formatFunctions = {
			'mask-cnpj': (digits) => this.formatString(digits, '##.###.###/####-##'),
			'mask-cpf': (digits) => this.formatString(digits, '###.###.###-##'),
			'mask-cep': (digits) => this.formatString(digits, '#####-###'),
			'mask-phus': (digits) => this.formatString(digits, '(###) ###-####'),
			'mask-ph8': (digits) => this.formatString(digits, '####-####'),
			'mask-ddd8': (digits) => this.formatString(digits, '(##) ####-####'),
			'mask-ddd9': (digits) => this.formatString(digits, '(##) #####-####'),
			'mask-dmy': (digits) => this.formatString(digits, '##/##/####'),
			'mask-mdy': (digits) => this.formatString(digits, '##/##/####'),
			'mask-hms': (digits) => this.formatString(digits, '##:##:##'),
			'mask-hm': (digits) => this.formatString(digits, '##:##'),
			'mask-dmyhm': (digits) => this.formatString(digits, '##/##/#### ##:##'),
			'mask-mdyhm': (digits) => this.formatString(digits, '##/##/#### ##:##'),
			'mask-my': (digits) => this.formatString(digits, '##/####'),
			'mask-ccs': (digits) => this.formatCreditCard(digits, 'space'),
			'mask-cch': (digits) => this.formatCreditCard(digits, 'hyphen'),
			'mask-ccmy': (digits) => this.formatString(digits, '##/##'),
			'mask-ccmyy': (digits) => this.formatString(digits, '##/####'),
			'mask-ipv4': (digits) => this.formatString(digits, '###.###.###.###'),
		};

		this.validators = {
			'mask-cnpj': (val) => this.isValidCNPJ(val),
			'mask-cpf': (val) => this.isValidCPF(val),
			'mask-cep': (val) => /^\d{5}-\d{3}$/.test(val),
			'mask-phus': (val) => /^\(\d{3}\) \d{3}-\d{4}$/.test(val),
			'mask-ph8': (val) => /^\d{4}-\d{4}$/.test(val),
			'mask-ddd8': (val) => /^\(\d{2}\) \d{4}-\d{4}$/.test(val),
			'mask-ddd9': (val) => /^\(\d{2}\) 9\d{4}-\d{4}$/.test(val),
			'mask-dmy': (val) => this.isValidDateTime(val, 'DMY'),
			'mask-mdy': (val) => this.isValidDateTime(val, 'MDY'),
			'mask-hms': (val) => this.isValidDateTime(val, 'HMS'),
			'mask-hm': (val) => this.isValidDateTime(val, 'HM'),
			'mask-dmyhm': (val) => this.isValidDateTime(val, 'DMY-HM'),
			'mask-mdyhm': (val) => this.isValidDateTime(val, 'MDY-HM'),
			'mask-my': (val) => this.isValidDateTime(val, 'MY'),
			'mask-ccs': (val) => this.isValidCreditCard(val),
			'mask-cch': (val) => this.isValidCreditCard(val),
			'mask-ccmy': (val) => this.isValidExpiry(val, 'MM/YY'),
			'mask-ccmyy': (val) => this.isValidExpiry(val, 'MM/YYYY'),
			'mask-ipv4': (val) => this.isValidIPv4(val),
		};

		this.bindMaskEvents();
		this.bindValidation();
	}

	getMaskClass(input) {
		if (!input) {
			return '';
		}
		if (input.dataset.maskClass) {
			return input.dataset.maskClass;
		}
		const classes = Object.keys(this.formatFunctions).concat(['mask-moneyc', 'mask-moneyd']);
		return classes.find((cls) => input.classList.contains(cls)) || '';
	}

	isMoneyMask(maskClass) {
		return maskClass === 'mask-moneyc' || maskClass === 'mask-moneyd';
	}

	formatString(digits, pattern) {
		let formatted = '';
		let index = 0;
		for (const char of pattern) {
			if (char === '#') {
				if (index < digits.length) {
					formatted += digits[index++];
				} else {
					break;
				}
			} else {
				formatted += char;
			}
		}
		return formatted;
	}

	stripCNPJ(value) {
		const s = String(value).toUpperCase().replace(/[^A-Z0-9]/g, '');
		if (s.length <= 12) {
			return s;
		}
		return s.slice(0, 12) + s.slice(12).replace(/\D/g, '').slice(0, 2);
	}

	formatMoneyInput(value, type, prefix, input) {
		const decimalSeparator = type === 'C' ? '.' : ',';
		const thousandSeparator = type === 'C' ? ',' : '.';
		let rawDigits = String(value).replace(/\D/g, '');
		let decimalPlaces = 2;

		if (input && input.dataset && input.dataset.decimalPlaces) {
			decimalPlaces = Number(input.dataset.decimalPlaces) || 2;
		}

		if (rawDigits.length === 0) {
			return `${prefix}0${decimalSeparator}${'0'.repeat(decimalPlaces)}`;
		}

		while (rawDigits.length < decimalPlaces + 1) {
			rawDigits = '0' + rawDigits;
		}

		const cents = rawDigits.slice(-decimalPlaces);
		let wholeNumber = rawDigits.slice(0, -decimalPlaces).replace(/^0+/, '') || '0';
		wholeNumber = wholeNumber.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);

		return `${prefix}${wholeNumber}${decimalSeparator}${cents}`;
	}

	handleMoneyInput(event) {
		const input = event.target;
		const oldValue = input.value;
		const oldCursorPos = input.selectionStart;
		const moneyPrefix = input.dataset.moneymaskPrefix || '$';
		const moneymaskFormat = input.dataset.moneymaskFormat || 'dot';
		const type = moneymaskFormat === 'dot' ? 'D' : 'C';
		const decimalSeparator = type === 'C' ? '.' : ',';
		const emptyBase = `${moneyPrefix}0${decimalSeparator}${'0'.repeat(Number(input.dataset.decimalPlaces || 2))}`;

		let newValue = '';
		if (input.value !== emptyBase && input.value !== '') {
			newValue = this.formatMoneyInput(input.value, type, moneyPrefix, input);
		} else {
			newValue = '';
		}

		if (input.value === newValue) {
			return;
		}

		input.value = newValue;
		const newCursorPos = oldCursorPos + (newValue.length - oldValue.length);
		this.setCaretPosition(input, Math.max(0, newCursorPos));
	}

	detectCardType(number) {
		const cleaned = number.replace(/\D/g, '');
		const cardPatterns = {
			Visa: /^4/,
			MasterCard: /^5[1-5]/,
			'American Express': /^3[47]/,
			Discover: /^6(?:011|5)/,
			JCB: /^(?:2131|1800|35)/,
			'Diners Club': /^3(?:0[0-5]|[689])/,
			UnionPay: /^(62|81)/,
			RuPay: /^(60|65|81|82|508)/,
			Maestro: /^(50|5[6-9]|6[0-9])/,
		};

		for (const card in cardPatterns) {
			if (cardPatterns[card].test(cleaned)) {
				return card;
			}
		}
		return 'Unknown';
	}

	formatCreditCard(digits, formatType) {
		const cardType = this.detectCardType(digits);
		if (cardType === 'American Express') {
			digits = digits.slice(0, 15);
			return formatType === 'space'
				? this.formatString(digits, '#### ###### #####')
				: this.formatString(digits, '####-######-#####');
		}
		digits = digits.slice(0, 16);
		return formatType === 'space'
			? this.formatString(digits, '#### #### #### ####')
			: this.formatString(digits, '####-####-####-####');
	}

	updateCardLogo(input) {
		const $input = jQuery(input);
		const $logo = $input.siblings('.dragwyb-card-logo');
		if (!$logo.length) {
			return;
		}

		const cardNumber = input.value.replace(/\D/g, '');
		if (!cardNumber) {
			$logo.attr('hidden', true).attr('src', '');
			return;
		}

		const cardType = this.detectCardType(cardNumber);
		if (this.cardLogos[cardType]) {
			$logo.attr('src', this.cardLogos[cardType]).removeAttr('hidden');
		} else {
			$logo.attr('hidden', true);
		}
	}

	getCaretPosition(input) {
		return input.selectionStart;
	}

	getDigitIndexFromCaret(formattedStr, caretPos, alphanumeric) {
		let count = 0;
		const pattern = alphanumeric ? /[A-Za-z0-9]/ : /\d/;
		for (let i = 0; i < caretPos; i++) {
			if (pattern.test(formattedStr.charAt(i))) {
				count++;
			}
		}
		return count;
	}

	mapDigitIndexToCaret(formattedStr, digitIndex, alphanumeric) {
		let count = 0;
		const pattern = alphanumeric ? /[A-Za-z0-9]/ : /\d/;
		for (let i = 0; i < formattedStr.length; i++) {
			if (pattern.test(formattedStr.charAt(i))) {
				if (count === digitIndex) {
					return i;
				}
				count++;
			}
		}
		return formattedStr.length;
	}

	setCaretPosition(elem, pos) {
		if (elem.setSelectionRange) {
			elem.focus();
			elem.setSelectionRange(pos, pos);
		}
	}

	applyFormat(input) {
		const maskClass = this.getMaskClass(input);
		if (!maskClass) {
			return;
		}

		if (this.isMoneyMask(maskClass)) {
			this.handleMoneyInput({ target: input });
			return;
		}

		const formatFunction = this.formatFunctions[maskClass];
		if (!formatFunction) {
			return;
		}

		const oldCaret = this.getCaretPosition(input);
		const isCnpj = maskClass === 'mask-cnpj';
		const rawDigits = isCnpj ? this.stripCNPJ(input.value) : input.value.replace(/\D/g, '');
		const digitIndex = this.getDigitIndexFromCaret(input.value, oldCaret, isCnpj);
		let newVal = formatFunction(rawDigits);
		const newCaret = this.mapDigitIndexToCaret(newVal, digitIndex, isCnpj);

		if (newVal === '(') {
			newVal = '';
		}

		input.value = newVal;
		this.setCaretPosition(input, newCaret || 0);

		if (maskClass === 'mask-ccs' || maskClass === 'mask-cch') {
			this.updateCardLogo(input);
		}
	}

	showError(input, message) {
		const $input = jQuery(input);
		const $wrapper = $input.closest('.dragwyb-mask-field');
		const $error = $wrapper.find('.dragwyb-mask-error');

		$input.addClass('dragwyb-error');
		if ($error.length) {
			$error.text(message).addClass('is-visible').removeAttr('hidden');
		}
		input.setCustomValidity(message || 'Invalid value.');
	}

	clearError(input) {
		const $wrapper = jQuery(input).closest('.dragwyb-mask-field');
		const $error = $wrapper.find('.dragwyb-mask-error');

		jQuery(input).removeClass('dragwyb-error');
		if ($error.length) {
			$error.text('').removeClass('is-visible').attr('hidden', true);
		}
		input.setCustomValidity('');
	}

	validateInput(input) {
		const maskClass = this.getMaskClass(input);
		if (!maskClass || this.isMoneyMask(maskClass)) {
			this.clearError(input);
			return true;
		}

		let val = String(input.value || '').trim();
		if (val.length === 1 && !/\d/.test(val)) {
			input.value = '';
			val = '';
		}

		if (val === '') {
			this.clearError(input);
			return true;
		}

		const validator = this.validators[maskClass];
		if (!validator) {
			this.clearError(input);
			return true;
		}

		if (!validator(val)) {
			const message = this.errorMessages[maskClass] || 'Invalid value format.';
			this.showError(input, message);
			return false;
		}

		this.clearError(input);
		return true;
	}

	bindMaskEvents() {
		const $root = this.$container;

		$root.on('input focus', '.dragwyb-mask-input', (event) => {
			this.applyFormat(event.target);
		});

		$root.on('focus', '.mask-moneyc, .mask-moneyd', (event) => {
			const input = event.target;
			const moneyPrefix = input.dataset.moneymaskPrefix || '$';
			const moneymaskFormat = input.dataset.moneymaskFormat || 'dot';
			const type = moneymaskFormat === 'dot' ? 'D' : 'C';
			const decimalSeparator = type === 'C' ? '.' : ',';
			const decimalPlaces = Number(input.dataset.decimalPlaces || 2);
			const baseFormat = `${moneyPrefix}0${decimalSeparator}${'0'.repeat(decimalPlaces)}`;

			if (jQuery(input).val().trim() === '') {
				jQuery(input).val(baseFormat);
			}
		});

		$root.on('blur', '.mask-moneyc, .mask-moneyd', (event) => {
			const input = event.target;
			const moneyPrefix = input.dataset.moneymaskPrefix || '$';
			const moneymaskFormat = input.dataset.moneymaskFormat || 'dot';
			const type = moneymaskFormat === 'dot' ? 'D' : 'C';
			const decimalSeparator = type === 'C' ? '.' : ',';
			const decimalPlaces = Number(input.dataset.decimalPlaces || 2);
			const baseFormat = `${moneyPrefix}0${decimalSeparator}${'0'.repeat(decimalPlaces)}`;
			const val = jQuery(input).val().trim();
			const numericValue = val
				.replace(new RegExp(`[^0-9${decimalSeparator}]`, 'g'), '')
				.replace(decimalSeparator, '.');

			if (parseFloat(numericValue) === 0 || val === baseFormat) {
				jQuery(input).val('');
			}
			this.clearError(input);
		});

		$root.on('blur', '.dragwyb-mask-input', (event) => {
			const maskClass = this.getMaskClass(event.target);
			if (!this.isMoneyMask(maskClass)) {
				this.validateInput(event.target);
			}
		});

		$root.on('keydown', '.dragwyb-mask-input', (event) => {
			if (event.key !== 'Backspace') {
				return;
			}

			const input = event.target;
			const maskClass = this.getMaskClass(input);
			if (!maskClass) {
				return;
			}

			if (this.isMoneyMask(maskClass)) {
				const moneyPrefix = input.dataset.moneymaskPrefix || '$';
				const moneymaskFormat = input.dataset.moneymaskFormat || 'dot';
				const type = moneymaskFormat === 'dot' ? 'D' : 'C';
				const decimalSeparator = type === 'C' ? '.' : ',';
				const decimalPlaces = Number(input.dataset.decimalPlaces || 2);
				const baseFormat = `${moneyPrefix}0${decimalSeparator}${'0'.repeat(decimalPlaces)}`;

				if (input.value === baseFormat) {
					event.preventDefault();
				}
				return;
			}

			if (input.selectionStart !== input.selectionEnd) {
				event.preventDefault();
				input.value = '';
				this.setCaretPosition(input, 0);
				if (maskClass === 'mask-ccs' || maskClass === 'mask-cch') {
					this.updateCardLogo(input);
				}
				return;
			}

			const caretPos = this.getCaretPosition(input);
			const isCnpj = maskClass === 'mask-cnpj';
			const digitIndex = this.getDigitIndexFromCaret(input.value, caretPos, isCnpj);
			if (digitIndex === 0) {
				return;
			}

			event.preventDefault();

			const rawDigits = isCnpj ? this.stripCNPJ(input.value) : input.value.replace(/\D/g, '');
			const newDigits = rawDigits.slice(0, digitIndex - 1) + rawDigits.slice(digitIndex);
			const formatFunction = this.formatFunctions[maskClass];

			if (formatFunction) {
				const formatted = formatFunction(newDigits);
				input.value = formatted;
				const newCaretPos = this.mapDigitIndexToCaret(formatted, digitIndex - 1, isCnpj);
				this.setCaretPosition(input, newCaretPos);
				if (maskClass === 'mask-ccs' || maskClass === 'mask-cch') {
					this.updateCardLogo(input);
				}
			}
		});
	}

	validateAll() {
		this.$container.find('.dragwyb-mask-input').each((_, input) => {
			this.validateInput(input);
		});
	}

	bindValidation() {
		if (!this.formId) {
			return;
		}

		DragwybBuilder.Hooks.addAction(`dragwyb/frontend/form/before_submit${this.formId}`, () => {
			this.validateAll();
		});
	}

	isValidDateTime(value, format) {
		const patterns = {
			DMY: /^(\d{2})\/(\d{2})\/(\d{4})$/,
			MDY: /^(\d{2})\/(\d{2})\/(\d{4})$/,
			HMS: /^(\d{2}):(\d{2}):(\d{2})$/,
			HM: /^(\d{2}):(\d{2})$/,
			'DMY-HM': /^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/,
			'MDY-HM': /^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/,
			MY: /^(\d{2})\/(\d{4})$/,
		};

		const expectedParts = {
			DMY: ['day', 'month', 'year'],
			MDY: ['month', 'day', 'year'],
			HMS: ['hour', 'minute', 'second'],
			HM: ['hour', 'minute'],
			'DMY-HM': ['day', 'month', 'year', 'hour', 'minute'],
			'MDY-HM': ['month', 'day', 'year', 'hour', 'minute'],
			MY: ['month', 'year'],
		};

		const regex = patterns[format];
		if (!regex) {
			return false;
		}

		const match = value.match(regex);
		if (!match) {
			return false;
		}

		const parts = {};
		expectedParts[format].forEach((part, index) => {
			parts[part] = parseInt(match[index + 1], 10);
		});

		if (parts.year && (parts.year < 1500 || parts.year > 3000)) {
			return false;
		}
		if (parts.month && (parts.month < 1 || parts.month > 12)) {
			return false;
		}
		if (parts.day) {
			const daysInMonth = new Date(parts.year, parts.month, 0).getDate();
			if (parts.day < 1 || parts.day > daysInMonth) {
				return false;
			}
		}
		if (parts.hour !== undefined && (parts.hour < 0 || parts.hour >= 24)) {
			return false;
		}
		if (parts.minute !== undefined && (parts.minute < 0 || parts.minute >= 60)) {
			return false;
		}
		if (parts.second !== undefined && (parts.second < 0 || parts.second >= 60)) {
			return false;
		}

		return true;
	}

	isValidExpiry(value, format) {
		const regex = format === 'MM/YY' ? /^(\d{2})\/(\d{2})$/ : /^(\d{2})\/(\d{4})$/;
		const match = value.match(regex);
		if (!match) {
			return false;
		}

		let month = parseInt(match[1], 10);
		let year = parseInt(match[2], 10);
		const currentYear = new Date().getFullYear();
		const currentMonth = new Date().getMonth() + 1;

		if (format === 'MM/YY') {
			year += 2000;
		}

		if (month < 1 || month > 12) {
			return false;
		}

		if (year < currentYear || (year === currentYear && month < currentMonth)) {
			return false;
		}

		return true;
	}

	isValidCreditCard(cardNumber) {
		const cleaned = cardNumber.replace(/\D/g, '');
		if (cleaned.length < 15 || cleaned.length > 16) {
			return false;
		}

		let sum = 0;
		let shouldDouble = false;

		for (let i = cleaned.length - 1; i >= 0; i--) {
			let digit = parseInt(cleaned.charAt(i), 10);
			if (shouldDouble) {
				digit *= 2;
				if (digit > 9) {
					digit -= 9;
				}
			}
			sum += digit;
			shouldDouble = !shouldDouble;
		}

		return sum % 10 === 0;
	}

	isValidCNPJ(cnpj) {
		cnpj = cnpj.toUpperCase().replace(/[.\-\/]/g, '');
		if (!/^[A-Z0-9]{12}\d{2}$/.test(cnpj)) {
			return false;
		}
		if (/^(.)\1{13}$/.test(cnpj)) {
			return false;
		}

		const calcCheckDigit = (value, length) => {
			const weights =
				length === 12
					? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
					: [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
			let sum = 0;
			for (let i = 0; i < weights.length; i++) {
				sum += (value.charCodeAt(i) - 48) * weights[i];
			}
			const remainder = sum % 11;
			return remainder < 2 ? 0 : 11 - remainder;
		};

		const firstCheck = calcCheckDigit(cnpj, 12);
		const secondCheck = calcCheckDigit(cnpj.slice(0, 12) + firstCheck, 13);

		return firstCheck === parseInt(cnpj.charAt(12), 10) && secondCheck === parseInt(cnpj.charAt(13), 10);
	}

	isValidCPF(cpf) {
		cpf = cpf.replace(/\D/g, '');
		if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) {
			return false;
		}

		const validateCPF = (value, length) => {
			let sum = 0;
			for (let i = 0; i < length; i++) {
				sum += parseInt(value.charAt(i), 10) * (length + 1 - i);
			}
			let result = (sum * 10) % 11;
			result = result === 10 ? 0 : result;
			return result === parseInt(value.charAt(length), 10);
		};

		return validateCPF(cpf, 9) && validateCPF(cpf, 10);
	}

	isValidIPv4(ip) {
		if (!/^(?:\d{1,3}\.){3}\d{1,3}$/.test(ip)) {
			return false;
		}
		return ip.split('.').every((octet) => {
			const num = parseInt(octet, 10);
			return num >= 0 && num <= 255;
		});
	}
}

const initMaskField = (container, formId) => {
	new DragwybMaskField(container, formId);
};

jQuery(document).on('Dragwyb:frontendInit', () => {
	DragwybBuilder.Hooks.addAction('dragwyb/frontend/form_ready', initMaskField);
});

jQuery(document).on('Dragwyb:editorAppLoaded', () => {
	DragwybBuilder.Hooks.addAction('dragwyb/editorPreview/form_ready', initMaskField);
});
