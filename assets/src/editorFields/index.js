import rowField from './row';

class textField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'text'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;

        return (
            <>
                <div className="dragwyb-input-group">
                    <input
                        type="text"
                        id={fieldId}
                        className="dragwyb-field-input"
                        placeholder={s.placeholder || ' '}
                        defaultValue={s.default_value}
                    />
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class emailField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'email'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;

        return (
            <>
                <div className="dragwyb-input-group">
                    <input
                        type="email"
                        id={fieldId}
                        className="dragwyb-field-input"
                        placeholder={s.placeholder || ' '}
                        defaultValue={s.default_value}
                    />
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class dateField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'date'; }

    buildFpConfig(s) {
        const isDateTime = s.picker_type === 'datetime';
        const mode = s.selection_mode || 'single';
        const allowedFormats = ['Y-m-d', 'Y/m/d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'm-d-Y'];
        let dateFormat = allowedFormats.includes(s.date_format) ? s.date_format : 'Y-m-d';

        if (isDateTime) {
            dateFormat = `${dateFormat} H:i`;
        }

        const resolveBound = (modeKey, dateKey, daysKey, defaultDays) => {
            const boundMode = s[modeKey] || 'none';
            if (boundMode === 'none') {
                return s[dateKey] || null;
            }
            if (boundMode === 'today') {
                return 'today';
            }
            if (boundMode === 'custom') {
                return s[dateKey] || null;
            }
            if (boundMode === 'relative') {
                return {
                    type: 'relative',
                    days: parseInt(s[daysKey] !== undefined && s[daysKey] !== '' ? s[daysKey] : defaultDays, 10) || 0,
                };
            }
            return null;
        };

        const config = {
            mode,
            dateFormat,
            allowInput: true,
        };

        if (isDateTime) {
            config.enableTime = true;
            config.time_24hr = s.time_24hr === 'yes';
            if (s.min_time) {
                config.minTime = s.min_time;
            }
            if (s.max_time) {
                config.maxTime = s.max_time;
            }
        }

        if (s.inline_calendar === 'yes') {
            config.inline = true;
        }
        if (s.week_numbers === 'yes') {
            config.weekNumbers = true;
        }
        if (s.alt_input === 'yes') {
            config.altInput = true;
            const presets = ['F j, Y', 'M j, Y', 'j F Y', 'd/m/Y', 'm/d/Y', 'Y-m-d', 'l, F j, Y'];
            if (s.alt_format === 'custom') {
                config.altFormat = (s.alt_format_custom || 'F j, Y').trim() || 'F j, Y';
            } else if (s.alt_format && !presets.includes(s.alt_format)) {
                config.altFormat = s.alt_format;
            } else {
                config.altFormat = presets.includes(s.alt_format) ? s.alt_format : 'F j, Y';
            }
        }

        if (mode === 'multiple') {
            config.conjunction = s.conjunction || ', ';
        }

        if (s.disable_weekends === 'yes') {
            config.disableWeekends = true;
        }
        if (s.disable_dates) {
            config.disableDates = s.disable_dates;
        }
        if (s.enable_dates) {
            config.enableDates = s.enable_dates;
        }

        const minDate = resolveBound('min_date_mode', 'min_date', 'min_date_days', 0);
        const maxDate = resolveBound('max_date_mode', 'max_date', 'max_date_days', 14);
        if (minDate) {
            config.minDate = minDate;
        }
        if (maxDate) {
            config.maxDate = maxDate;
        }

        return config;
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;
        const useNative = s.use_native_date === 'yes';
        const inputClass = [
            'dragwyb-field-input',
            'dragwyb-date-field',
            useNative ? 'dragwyb-use-native' : '',
        ].filter(Boolean).join(' ');

        const inputProps = {
            type: useNative ? 'date' : 'text',
            id: fieldId,
            className: inputClass,
            placeholder: s.placeholder || ' ',
        };

        if (useNative) {
            inputProps.pattern = '[0-9]{4}-[0-9]{2}-[0-9]{2}';
            if (s.min_date) {
                inputProps.min = s.min_date;
            }
            if (s.max_date) {
                inputProps.max = s.max_date;
            }
        } else {
            inputProps['data-fp-config'] = JSON.stringify(this.buildFpConfig(s));
        }

        return (
            <>
                <div className="dragwyb-input-group" key={useNative ? 'native' : 'flatpickr'}>
                    <input {...inputProps} />
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

// 2. Textarea
class textAreaField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'textarea'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;

        return (
            <>
                <div className="dragwyb-input-group">
                    <textarea
                        id={fieldId}
                        rows={s.rows || 4}
                        placeholder={s.placeholder || ' '}
                        className="dragwyb-field-input"
                    ></textarea>
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

// 3. Select
class selectField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'select'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const options = s.options_list || [];
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;
        const defaultValue = s.default_value !== undefined ? String(s.default_value).trim() : '';
        const isMultiple = s.multiple === 'yes' || s.multiple === true;
        const defaultVals = isMultiple
            ? (defaultValue ? defaultValue.split(',').map(v => v.trim()) : [])
            : (defaultValue ? [defaultValue] : []);

        return (
            <>
                <div className="dragwyb-input-group">
                    <select id={fieldId} className="dragwyb-field-input" multiple={isMultiple} value={isMultiple ? defaultVals : (defaultVals[0] || '')} readOnly>
                        {options.map((opt, i) => (
                            !opt.attributes ? null :
                                <option key={i} value={opt.attributes.option_value}>{opt.attributes.option_label}</option>
                        ))}
                    </select>
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

// 4. Radio (Standard Order)
class radioField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'radio'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const options = s.options_list || [];
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;
        const radioStyle = s.radio_style || 'outline';
        const styleClass = `dragwyb-radio-style-${radioStyle}`;
        const layoutClass = s.layout !== 'block' ? 'dragwyb-inline-options' : '';
        const defaultValue = s.default_value !== undefined ? String(s.default_value).trim() : '';

        return (
            <>
                <div className="dragwyb-input-group">
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                    <div className={`dragwyb-options-container ${layoutClass} ${styleClass}`}>
                        {options.map((opt, i) => {
                            if (!opt.attributes) return null;
                            const val = String(opt.attributes.option_value || '');
                            const isChecked = defaultValue ? val === defaultValue : i === 0;
                            return (
                                <label key={i} className={`dragwyb-option-item ${isChecked ? 'is-checked' : ''}`}>
                                    <input type="radio" name={fieldId} value={val} checked={isChecked} readOnly />
                                    <span className="dragwyb-radio-label">{opt.attributes.option_label}</span>
                                </label>
                            );
                        })}
                    </div>
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class fileField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'file'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;

        return (
            <>
                {label && <this.RenderLabel
                    id={fieldId}
                    label={label}
                    required={s.required}
                    settings={s}
                />}
                <div className="dragwyb-file-upload-container">
                    <input type="file" id={fieldId} className="dragwyb-field-input" disabled />
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class checkboxField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'checkbox'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const options = s.options_list || [];
        const layoutClass = s.layout === 'inline' ? 'dragwyb-inline-options' : '';
        const defaultValue = s.default_value !== undefined ? String(s.default_value).trim() : '';
        const defaultVals = defaultValue ? defaultValue.split(',').map(v => v.trim()) : [];

        return (
            <>
                <div className="dragwyb-input-group">
                    {s.label && <this.RenderLabel
                        id={''}
                        label={s.label}
                        required={s.required}
                        settings={s}
                    />}
                    <div className={`dragwyb-options-container ${layoutClass}`}>
                        {options.map((opt, i) => {
                            if (!opt.attributes) return null;
                            const val = String(opt.attributes.option_value || '');
                            const isChecked = defaultVals.includes(val);
                            return (
                                <label key={i} className={`dragwyb-option-item ${isChecked ? 'is-checked' : ''}`}>
                                    <input type="checkbox" name={`${fieldId}[]`} value={val} checked={isChecked} readOnly />
                                    <span className="dragwyb-radio-label">{opt.attributes.option_label}</span>
                                </label>
                            );
                        })}
                    </div>
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class numberField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'number'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const { label = defaultLabel } = s;
        return (
            <>
                <div className="dragwyb-input-group">
                    <input
                        type="number"
                        id={fieldId}
                        className="dragwyb-field-input"
                        placeholder={s.placeholder || ' '}
                        min={s.min_val}
                        max={s.max_val}
                        step={s.step}
                    />
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class hiddenField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'hidden'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;

        // Custom style to represent invisible field in editor
        const placeholderStyle = {
            padding: '10px',
            border: '1px dashed #9ca3af',
            backgroundColor: '#f3f4f6',
            color: '#6b7280',
            fontSize: '13px',
            borderRadius: '4px',
            display: 'flex',
            alignItems: 'center',
            gap: '8px'
        };

        return (
            <>
                <div style={placeholderStyle}>
                    <DragwybEditor.editor.IconsManager.Render icon={{ type: 'solid', icon: 'eye-slash' }} width={15} />
                    <strong>Hidden Field:</strong> {fieldId}
                    <span style={{ fontSize: '11px', marginLeft: 'auto' }}>(Value: {s.default_value || '(empty)'})</span>
                </div>
            </>
        );
    }
}

// 5. Button
class ButtonField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'button'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const buttonType = s.button_type || 'button';

        return (
            <button type={buttonType} onClick={(e) => e.preventDefault()}>
                {s.text || 'Submit'}
            </button>
        );
    }
}

class urlField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'url'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;
        return (
            <>
                <div className="dragwyb-input-group">
                    <input type="url" id={fieldId} className="dragwyb-field-input" placeholder={s.placeholder || ' '} defaultValue={s.default_value} />
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class phoneField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'phone'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;
        const countryEnabled = s.country_code_enabled === 'yes';

        const normalizeList = (value) => {
            if (!value || typeof value !== 'string') return '';
            return value
                .split(',')
                .map((code) => code.trim().toLowerCase())
                .filter((code) => /^[a-z]{2}$/.test(code))
                .join(',');
        };

        const includeCountries = normalizeList(s.country_code_include);
        const excludeCountries = normalizeList(s.country_code_exclude);
        const includeList = includeCountries ? includeCountries.split(',') : [];
        const excludeList = excludeCountries ? excludeCountries.split(',') : [];
        const includeSorted = includeList.length ? [...includeList].sort().join(',') : '';
        const excludeSorted = excludeList.length ? [...excludeList].sort().join(',') : '';
        const commonCountries = includeSorted && includeSorted === excludeSorted ? 'same' : '';

        let defaultCountry = String(s.country_code_default || 'us').trim().toLowerCase();
        if (!/^[a-z]{2}$/.test(defaultCountry)) {
            defaultCountry = 'us';
        }

        const dialCodeVisibility = s.dial_code_visibility || 'show';
        const strictMode = s.country_strict_mode || 'no';
        const i18n = s.country_internationalisation || 'en';
        const showFlags = s.country_show_flags === 'yes' || s.country_show_flags === undefined ? 'yes' : 'no';

        const itiPreviewKey = countryEnabled
            ? [
                'cc',
                defaultCountry,
                includeCountries,
                excludeCountries,
                dialCodeVisibility,
                strictMode,
                i18n,
                showFlags,
            ].join('|')
            : 'off';

        const inputProps = {
            type: 'tel',
            id: fieldId,
            className: 'dragwyb-field-input',
            placeholder: s.placeholder || ' ',
            defaultValue: s.default_value,
        };

        if (countryEnabled) {
            Object.assign(inputProps, {
                'data-country-code': 'yes',
                'data-default-country': defaultCountry,
                'data-include-countries': includeCountries,
                'data-exclude-countries': excludeCountries,
                'data-common-countries': commonCountries,
                'data-dial-code-visibility': dialCodeVisibility,
                'data-strict-mode': strictMode,
                'data-internationalisation': i18n,
                'data-show-flags': showFlags,
                'data-iti-config': itiPreviewKey,
                autoComplete: 'tel',
            });
        }

        return (
            <>
                <div className="dragwyb-input-group" key={`phone-group-${fieldId}-${itiPreviewKey}`}>
                    <input {...inputProps} />
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class maskField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'mask'; }

    resolveMaskClass(s) {
        const maskType = s.mask_type || 'phone';
        if (maskType === 'phone') {
            const map = {
                phone_usa: 'mask-phus',
                phone_d8: 'mask-ph8',
                phone_ddd8: 'mask-ddd8',
                phone_ddd9: 'mask-ddd9',
            };
            return map[s.phone_format || 'phone_usa'] || 'mask-phus';
        }
        if (maskType === 'datetime') {
            const map = {
                dmy: 'mask-dmy',
                mdy: 'mask-mdy',
                dmyhm: 'mask-dmyhm',
                mdyhm: 'mask-mdyhm',
                hm: 'mask-hm',
                hms: 'mask-hms',
                my: 'mask-my',
            };
            return map[s.datetime_format || 'dmy'] || 'mask-dmy';
        }
        if (maskType === 'money') return 'mask-moneyc';
        if (maskType === 'credit_card') {
            const map = {
                space: 'mask-ccs',
                hyphen: 'mask-cch',
                credit_card_date: 'mask-ccmy',
                credit_card_expiry_date: 'mask-ccmyy',
            };
            return map[s.credit_card_options || 'hyphen'] || 'mask-cch';
        }
        if (maskType === 'brazilian') {
            const map = { cpf: 'mask-cpf', cnpj: 'mask-cnpj', cep: 'mask-cep' };
            return map[s.brazilian_format || 'cpf'] || 'mask-cpf';
        }
        if (maskType === 'ip') return 'mask-ipv4';
        if (maskType === 'custom') return 'mask-custom';
        return 'mask-phus';

    }

    getAutoPlaceholder(maskClass, s) {
        const placeholders = {
            'mask-phus': '(XXX) XXX-XXXX',
            'mask-ph8': 'XXXX-XXXX',
            'mask-ddd8': '(XX) XXXX-XXXX',
            'mask-ddd9': '(XX) XXXXX-XXXX',
            'mask-dmy': 'XX/XX/XXXX',
            'mask-mdy': 'XX/XX/XXXX',
            'mask-hm': 'XX:XX',
            'mask-hms': 'XX:XX:XX',
            'mask-dmyhm': 'XX/XX/XXXX XX:XX',
            'mask-mdyhm': 'XX/XX/XXXX XX:XX',
            'mask-my': 'XX/XXXX',
            'mask-ccs': 'XXXX XXXX XXXX XXXX',
            'mask-cch': 'XXXX-XXXX-XXXX-XXXX',
            'mask-ccmy': 'XX/XX',
            'mask-ccmyy': 'XX/XXXX',
            'mask-cpf': 'XXX.XXX.XXX-XX',
            'mask-cnpj': 'XX.XXX.XXX/XXXX-XX',
            'mask-cep': 'XXXXX-XXX',
            'mask-ipv4': 'XXX.XXX.XXX.XXX',
        };


        if (maskClass === 'mask-custom') {
            return (s.custom_mask || '').replace(/[0A*]/g, 'X');
        }

        if (maskClass === 'mask-moneyc') {
            const prefix = s.money_prefix !== undefined && s.money_prefix !== ''
                ? s.money_prefix
                : '$';

            const separator = s.money_format === 'comma' ? '.' : ',';
            const decimals = '0'.repeat(
                Math.max(0, parseInt(s.money_decimal_places || 2, 10) || 2)
            );

            return `${prefix}0${separator}${decimals}`;
        }

        return placeholders[maskClass] || '';
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;
        const maskClass = this.resolveMaskClass(s);
        const autoPlaceholder = s.auto_placeholder === 'yes' || s.auto_placeholder === undefined;
        let placeholder = s.placeholder || '';

        if (autoPlaceholder) {
            const auto = this.getAutoPlaceholder(maskClass, s);
            if (auto) placeholder = auto;
        }
        if (!placeholder) placeholder = ' ';

        const showCardLogo = maskClass === 'mask-ccs' || maskClass === 'mask-cch';
        const inputmode = maskClass === 'mask-cnpj'
            ? 'text'
            : (['mask-phus', 'mask-ph8', 'mask-ddd8', 'mask-ddd9'].includes(maskClass) ? 'tel' : 'numeric');

        return (
            <>
                <div className="dragwyb-input-group">
                    <input
                        type="text"
                        id={fieldId}
                        className={`dragwyb-field-input dragwyb-mask-input ${maskClass}`}
                        placeholder={placeholder}
                        defaultValue={s.default_value}
                        inputMode={inputmode}
                        data-mask-class={maskClass}
                        data-custom-mask={s.custom_mask || ''}
                        data-moneymask-format={s.money_format || 'dot'}
                        data-moneymask-prefix={s.money_prefix !== undefined ? s.money_prefix : '$'}
                        data-decimal-places={s.money_decimal_places || '2'}
                        autoComplete="off"
                    />
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                    {showCardLogo && <img className="dragwyb-card-logo" src="" alt="" hidden />}
                </div>
                <div className={`dragwyb-mask-error error-${maskClass.replace('mask-', '')}`} hidden></div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class nameField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'name'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;
        return (
            <>
                <div className="dragwyb-input-group">
                    <input type="text" id={fieldId} className="dragwyb-field-input" placeholder={s.placeholder || ' '} defaultValue={s.default_value} />
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class addressField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'address'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;
        return (
            <>
                <div className="dragwyb-input-group">
                    <textarea id={fieldId} className="dragwyb-field-input" rows="3" placeholder={s.placeholder || ' '} defaultValue={s.default_value} />
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class timeField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'time'; }

    buildFpConfig(s) {
        const config = {
            dateFormat: 'H:i',
            allowInput: true,
            enableTime: true,
            noCalendar: true,
            time_24hr: s.time_24hr === 'yes',
        };

        if (s.min_time) {
            config.minTime = s.min_time;
        }
        if (s.max_time) {
            config.maxTime = s.max_time;
        }

        return config;
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;
        const useNative = s.use_native_time === 'yes';
        const inputClass = [
            'dragwyb-field-input',
            'dragwyb-time-field',
            useNative ? 'dragwyb-use-native' : '',
        ].filter(Boolean).join(' ');

        const inputProps = {
            type: useNative ? 'time' : 'text',
            id: fieldId,
            className: inputClass,
            defaultValue: s.default_value,
        };

        if (useNative) {
            if (s.min_time) {
                inputProps.min = s.min_time;
            }
            if (s.max_time) {
                inputProps.max = s.max_time;
            }
        } else {
            inputProps.placeholder = s.placeholder || 'HH:MM';
            inputProps['data-fp-config'] = JSON.stringify(this.buildFpConfig(s));
        }

        return (
            <>
                <div className="dragwyb-input-group" key={useNative ? 'native' : 'flatpickr'}>
                    <input {...inputProps} />
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class rangeField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'range'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const defaultLabel = DragwybEditor?.fields?.fields?.[this.fieldName]?.controls?.label?.default;
        const label = s.label || defaultLabel;
        const min = parseFloat(s.min_val) || 0;
        const max = parseFloat(s.max_val) || 100;
        const value = parseFloat(s.default_value) || 50;
        let percentage = ((value - min) / (max - min)) * 100;
        if (percentage < 0) percentage = 0;
        if (percentage > 100) percentage = 100;
        if (isNaN(percentage)) percentage = 50;

        return (
            <>
                <div className="dragwyb-input-group">
                    {label && <this.RenderLabel
                        id={fieldId}
                        label={label}
                        required={s.required}
                        settings={s}
                    />}
                    <div className="dragwyb-custom-range-container">
                        <div className="dragwyb-range-track">
                            <div className="dragwyb-range-progress" style={{ width: `${percentage}%` }}></div>
                            <div className="dragwyb-range-thumb" style={{ left: `${percentage}%` }}></div>
                        </div>
                        <input type="range" id={fieldId} className="dragwyb-field-input dragwyb-hidden-range" min={s.min_val} max={s.max_val} step={s.step_val} defaultValue={s.default_value} onChange={(e) => {
                            e.target.setAttribute('value', e.target.value);
                        }} />
                    </div>
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

class htmlField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'html'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        return (
            <div className="dragwyb-html-content" dangerouslySetInnerHTML={{ __html: s.raw_html || '<p>Enter your custom HTML here.</p>' }} />
        );
    }
}

class sectionField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'section'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        return (
            <div className="dragwyb-section-break">
                {s.title && <h3 className="dragwyb-section-title">{s.title}</h3>}
                {s.description && <p className="dragwyb-section-description">{s.description}</p>}
                <hr className="dragwyb-section-divider" />
            </div>
        );
    }
}

class captchaField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'captcha'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        return (
            <div className="dragwyb-captcha-placeholder" style={{ background: '#f9f9f9', border: '1px solid #ddd', padding: '15px', display: 'inline-block' }}>
                [ {s.captcha_type || 'recaptcha_v2'} Placeholder ]
            </div>
        );
    }
}

const StepPreview = ({ attributes, id }) => {
    const state = window.parent?.DragwybStore?.getState() || window.DragwybStore?.getState() || {};
    const formId = state?.form?.id;
    const indicatorType = state?.form?.style?.step_indicator_type || 'numbers';
    const fields = state?.form?.fields || {};

    let stepContainers = [];
    const allStepFIelds = document.querySelectorAll(`#dragwyb-form-wrapper-${formId} .dragwyb-field-wrapper.dragwyb-step-field`);
    if (allStepFIelds && allStepFIelds.length > 0) {
        allStepFIelds.forEach(stepField => {
            const stepId = stepField.id.replace('dragwyb-step-', '');
            stepContainers.push(stepId);
        });
    } else {
        const rootContainers = state?.form?.rootContainers || [];
        stepContainers = rootContainers.filter(cid => fields[cid]?.type === 'step');
    }

    if (!stepContainers.includes(id)) {
        stepContainers.push(id);
    }

    const stepIndex = stepContainers.indexOf(id) + 1;
    const totalSteps = stepContainers.length;
    const label = attributes.label || '';

    let containerCls = "dragwyb-step-indicator-container dragwyb-editor-preview";

    if (label && label !== '') {
        containerCls += ' step-has-title';
    }

    // Default to 'numbers' or 'dots' style
    const isDots = indicatorType === 'dots';
    return (
        <div className={containerCls} data-step-indicator={indicatorType} style={{ display: indicatorType === 'none' ? 'none' : 'flex' }}>
            <div className="dragwyb-step-indicator">
                <div className={`dragwyb-step-item${stepIndex === 1 ? ' active' : ''}`} style={{ display: ['numbers', 'dots'].includes(indicatorType) ? 'flex' : 'none' }}>
                    <div className="dragwyb-step-dot">
                        {!isDots && <div className="dragwyb-step-number">{stepIndex}</div>}
                    </div>
                    {(!isDots && label) && <div className="dragwyb-step-title">{label}</div>}
                </div>
                <span className="dragwyb-step-divider" style={{ display: ['numbers', 'dots'].includes(indicatorType) ? 'block' : 'none' }}></span>
                <div className="dragwyb-step-progress-wrapper" style={{ display: indicatorType === 'progress' ? 'block' : 'none' }}>
                    <div className="dragwyb-step-progress-text">
                        Step {stepIndex} of {totalSteps}
                    </div>
                    <div className="dragwyb-step-progress-bar">
                        <div className="dragwyb-step-progress-fill" style={{ width: stepIndex === 1 ? '100%' : 0 }}></div>
                    </div>
                </div>
            </div>
        </div >
    );
};

class stepField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'step'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        return <StepPreview attributes={this.attributes} id={this.id} />;
    }
}

class gdprField extends DragwybEditor.editor.extends.FieldBase {
    fieldName() { return 'gdpr'; }
    bind() {
        if (!this.shouldRender()) return <></>;
        const s = this.attributes;
        const fieldId = s.field_id || this.id;
        const consentText = s.consent_text || 'I consent to this website storing my submitted information so they can respond to my inquiry.';
        const privacyUrl = s.privacy_policy_url || '';

        let renderConsent = consentText;
        if (privacyUrl && consentText.toLowerCase().includes('privacy policy')) {
            renderConsent = consentText.replace(/Privacy Policy/gi, `<a href="${privacyUrl}" target="_blank" rel="noopener noreferrer" onclick="return false;">Privacy Policy</a>`);
        }

        return (
            <>
                <div className="dragwyb-input-group dragwyb-gdpr-field">
                    {s.label && <this.RenderLabel
                        id={''}
                        label={s.label}
                        required={s.required || 'yes'}
                        settings={s}
                    />}
                    <div className="dragwyb-options-container">
                        <label className="dragwyb-option-item dragwyb-gdpr-item">
                            <input type="checkbox" name={fieldId} value="yes" readOnly />
                            <span className="dragwyb-radio-label">
                                {privacyUrl && consentText.toLowerCase().includes('privacy policy') ? (
                                    <span dangerouslySetInnerHTML={{ __html: renderConsent }} />
                                ) : consentText}
                            </span>
                        </label>
                    </div>
                </div>
                {s.help_text && <div className="dragwyb-field-help">{s.help_text}</div>}
            </>
        );
    }
}

const initializeFields = () => {
    const defaultFields = {
        'text': (args) => new textField(args),
        'textarea': (args) => new textAreaField(args),
        'select': (args) => new selectField(args),
        'radio': (args) => new radioField(args),
        'file': (args) => new fileField(args),
        'email': (args) => new emailField(args),
        'date': (args) => new dateField(args),
        'checkbox': (args) => new checkboxField(args),
        'number': (args) => new numberField(args),
        'hidden': (args) => new hiddenField(args),
        'button': (args) => new ButtonField(args),
        'row': (args) => new rowField(args),
        'url': (args) => new urlField(args),
        'phone': (args) => new phoneField(args),
        'mask': (args) => new maskField(args),
        'name': (args) => new nameField(args),
        'address': (args) => new addressField(args),
        'time': (args) => new timeField(args),
        'range': (args) => new rangeField(args),
        'html': (args) => new htmlField(args),
        'section': (args) => new sectionField(args),
        'captcha': (args) => new captchaField(args),
        'step': (args) => new stepField(args),
        'gdpr': (args) => new gdprField(args),
    };

    Object.keys(defaultFields).forEach(key =>
        DragwybBuilder.Hooks.addFilter(
            'Dragwyb/Editor/FieldRender/' + key,
            (...args) => defaultFields[key](args)
        )
    );
};

jQuery(document).on('Dragwyb:editorAppLoaded', () => {
    initializeFields();
});