<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Phone extends Field_Base {

	/**
	 * Whether phone country-code assets have been localized.
	 *
	 * @var bool
	 */
	private static bool $assets_localized = false;

	protected function register_scripts() {
		return $this->country_code_enabled() ? array( 'dragwyb-phone-country-code' ) : array();
	}

	protected function register_styles() {
		return $this->country_code_enabled() ? array( 'dragwyb-phone-country-code' ) : array();
	}

	/**
	 * Whether country code assets should load for this field instance.
	 */
	private function country_code_enabled(): bool {
		$settings = $this->get_field_settings();
		// Empty settings = editor bootstrap; load assets there.
		return empty( $settings ) || $this->field_key_exist( $settings, 'country_code_enabled', 'no' ) === 'yes';
	}

	public function __construct() {
		parent::__construct();
		$this->register_phone_assets();
	}

	/**
	 * Register intl-tel-input and country-code scripts/styles.
	 */
	private function register_phone_assets(): void {
		if ( ! wp_script_is( 'dragwyb-intl-tel-input', 'registered' ) ) {
			wp_register_script(
				'dragwyb-intl-tel-input',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/intlTelInput.js' ),
				array(),
				DRAGWYB_FORM_BUILDER_VERSION,
				true
			);
		}

		if ( ! wp_script_is( 'dragwyb-phone-country-translations', 'registered' ) ) {
			wp_register_script(
				'dragwyb-phone-country-translations',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/phone-country-translations.min.js' ),
				array(),
				DRAGWYB_FORM_BUILDER_VERSION,
				true
			);
		}

		if ( ! wp_style_is( 'dragwyb-intl-tel-input', 'registered' ) ) {
			wp_register_style(
				'dragwyb-intl-tel-input',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/intlTelInput.min.css' ),
				array(),
				DRAGWYB_FORM_BUILDER_VERSION,
				'all'
			);
		}

		if ( ! wp_style_is( 'dragwyb-phone-country-code', 'registered' ) ) {
			wp_register_style(
				'dragwyb-phone-country-code',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/phone-country-code.css' ),
				array( 'dragwyb-intl-tel-input' ),
				DRAGWYB_FORM_BUILDER_VERSION,
				'all'
			);
		}

		if ( ! wp_script_is( 'dragwyb-phone-country-code', 'registered' ) ) {
			wp_register_script(
				'dragwyb-phone-country-code',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/phone-country-code.js' ),
				array( 'jquery', 'dragwyb-form-frontend', 'dragwyb-intl-tel-input', 'dragwyb-phone-country-translations' ),
				DRAGWYB_FORM_BUILDER_VERSION,
				true
			);
		}

		$this->localize_phone_assets();
	}

	/**
	 * Localize shared country-code script data once.
	 */
	private function localize_phone_assets(): void {
		if ( self::$assets_localized ) {
			return;
		}

		self::$assets_localized = true;

		$error_map = array(
			__( 'The phone number you entered is not valid. Please check the format and try again.', 'smart-form-builder-by-dragwyb' ),
			__( 'The country code you entered is not recognized. Please ensure it is correct and try again.', 'smart-form-builder-by-dragwyb' ),
			__( 'The phone number you entered is too short. Please enter a complete phone number, including the country code.', 'smart-form-builder-by-dragwyb' ),
			__( 'The phone number you entered is too long. Please ensure it is in the correct format and try again.', 'smart-form-builder-by-dragwyb' ),
			__( 'The phone number you entered is not valid. Please check the format and try again.', 'smart-form-builder-by-dragwyb' ),
		);

		wp_localize_script(
			'dragwyb-phone-country-code',
			'DragwybPhoneCountryData',
			array(
				'pluginDir'   => DRAGWYB_FORM_BUILDER_URL,
				'utilsScript' => DRAGWYB_FORM_BUILDER_URL . 'assets/js/utils.js',
				'errorMap'    => $error_map,
			)
		);
	}

	/**
	 * Language options for the country dropdown i18n.
	 *
	 * @return array<string, string>
	 */
	private function get_i18n_language_options(): array {
		return array(
			'en' => __( 'English', 'smart-form-builder-by-dragwyb' ),
			'ar' => __( 'Arabic', 'smart-form-builder-by-dragwyb' ),
			'bg' => __( 'Bulgarian', 'smart-form-builder-by-dragwyb' ),
			'bn' => __( 'Bengali', 'smart-form-builder-by-dragwyb' ),
			'bs' => __( 'Bosnian', 'smart-form-builder-by-dragwyb' ),
			'ca' => __( 'Catalan', 'smart-form-builder-by-dragwyb' ),
			'cs' => __( 'Czech', 'smart-form-builder-by-dragwyb' ),
			'da' => __( 'Danish', 'smart-form-builder-by-dragwyb' ),
			'de' => __( 'German', 'smart-form-builder-by-dragwyb' ),
			'ee' => __( 'Estonian', 'smart-form-builder-by-dragwyb' ),
			'el' => __( 'Greek', 'smart-form-builder-by-dragwyb' ),
			'es' => __( 'Spanish', 'smart-form-builder-by-dragwyb' ),
			'fa' => __( 'Persian', 'smart-form-builder-by-dragwyb' ),
			'fi' => __( 'Finnish', 'smart-form-builder-by-dragwyb' ),
			'fr' => __( 'French', 'smart-form-builder-by-dragwyb' ),
			'hi' => __( 'Hindi', 'smart-form-builder-by-dragwyb' ),
			'hr' => __( 'Croatian', 'smart-form-builder-by-dragwyb' ),
			'hu' => __( 'Hungarian', 'smart-form-builder-by-dragwyb' ),
			'id' => __( 'Indonesian', 'smart-form-builder-by-dragwyb' ),
			'it' => __( 'Italian', 'smart-form-builder-by-dragwyb' ),
			'ja' => __( 'Japanese', 'smart-form-builder-by-dragwyb' ),
			'ko' => __( 'Korean', 'smart-form-builder-by-dragwyb' ),
			'mr' => __( 'Marathi', 'smart-form-builder-by-dragwyb' ),
			'nl' => __( 'Dutch', 'smart-form-builder-by-dragwyb' ),
			'no' => __( 'Norwegian', 'smart-form-builder-by-dragwyb' ),
			'pl' => __( 'Polish', 'smart-form-builder-by-dragwyb' ),
			'pt' => __( 'Portuguese', 'smart-form-builder-by-dragwyb' ),
			'ro' => __( 'Romanian', 'smart-form-builder-by-dragwyb' ),
			'ru' => __( 'Russian', 'smart-form-builder-by-dragwyb' ),
			'sk' => __( 'Slovak', 'smart-form-builder-by-dragwyb' ),
			'sv' => __( 'Swedish', 'smart-form-builder-by-dragwyb' ),
			'te' => __( 'Telugu', 'smart-form-builder-by-dragwyb' ),
			'th' => __( 'Thai', 'smart-form-builder-by-dragwyb' ),
			'tr' => __( 'Turkish', 'smart-form-builder-by-dragwyb' ),
			'uk' => __( 'Ukrainian', 'smart-form-builder-by-dragwyb' ),
			'ur' => __( 'Urdu', 'smart-form-builder-by-dragwyb' ),
			'vi' => __( 'Vietnamese', 'smart-form-builder-by-dragwyb' ),
			'zh' => __( 'Chinese', 'smart-form-builder-by-dragwyb' ),
		);
	}

	protected function init(): void {
		$this->type     = 'phone';
		$this->name     = __( 'Phone Field', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'fas fa-phone';
		$this->category = 'advanced-fields';
		$this->keywords = array( 'mobile', 'contact', 'number', 'telephone', 'cell', 'country', 'dial' );
	}

	protected function register_field_controls(): void {
		$this->start_section(
			'section_content_general',
			array(
				'label' => __( 'Basic Settings', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::ContentTab,
			)
		);

		$this->add_control(
			'label',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Field Label', 'smart-form-builder-by-dragwyb' ),
				'default' => __( 'Phone Number', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'label_icon',
			array(
				'type'  => Controls::ICON,
				'label' => __( 'Label Icon', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'placeholder',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Placeholder Text', 'smart-form-builder-by-dragwyb' ),
				'default' => __( '+1 234 567 8900', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'default_value',
			array(
				'type'  => Controls::TEXT,
				'label' => __( 'Default Value', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'help_text',
			array(
				'type'  => Controls::TEXTAREA,
				'label' => __( 'Instructional Text', 'smart-form-builder-by-dragwyb' ),
				'rows'  => 3,
			)
		);

		$this->end_section();

		$this->start_section(
			'section_content_country_code',
			array(
				'label' => __( 'Country Code', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::ContentTab,
			)
		);

		$this->add_control(
			'country_code_enabled',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Enable Country Code', 'smart-form-builder-by-dragwyb' ),
				'label_on'     => __( 'Show', 'smart-form-builder-by-dragwyb' ),
				'label_off'    => __( 'Hide', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'country_code_default',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Default Country', 'smart-form-builder-by-dragwyb' ),
				'default'     => 'us',
				'description' => __( 'ISO alpha-2 code, e.g. us, in, gb.', 'smart-form-builder-by-dragwyb' ),
				'conditions'  => array(
					'country_code_enabled' => 'yes',
				),
			)
		);

		$this->add_control(
			'country_code_include',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Only Countries', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Comma-separated ISO codes to show only, e.g. ca, in, us, gb.', 'smart-form-builder-by-dragwyb' ),
				'conditions'  => array(
					'country_code_enabled' => 'yes',
				),
			)
		);

		$this->add_control(
			'country_code_exclude',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Exclude Countries', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Comma-separated ISO codes to hide, e.g. af, pk.', 'smart-form-builder-by-dragwyb' ),
				'conditions'  => array(
					'country_code_enabled' => 'yes',
				),
			)
		);

		$this->add_control(
			'dial_code_visibility',
			array(
				'type'       => Controls::CHOOSE,
				'label'      => __( 'Dial Code Visibility', 'smart-form-builder-by-dragwyb' ),
				'options'    => array(
					'show'     => array(
						'title' => __( 'Show', 'smart-form-builder-by-dragwyb' ),
						'icon'  => 'far fa-eye',
					),
					'hide'     => array(
						'title' => __( 'Hide', 'smart-form-builder-by-dragwyb' ),
						'icon'  => 'far fa-eye-slash',
					),
					'separate' => array(
						'title' => __( 'Separate', 'smart-form-builder-by-dragwyb' ),
						'icon'  => 'fas fa-arrows-alt-h',
					),
				),
				'default'    => 'show',
				'conditions' => array(
					'country_code_enabled' => 'yes',
				),
			)
		);

		$this->add_control(
			'country_strict_mode',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Strict Mode', 'smart-form-builder-by-dragwyb' ),
				'label_on'     => __( 'Yes', 'smart-form-builder-by-dragwyb' ),
				'label_off'    => __( 'No', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => __( 'Only allow numeric characters (and an optional leading +). Caps length at the maximum valid number length.', 'smart-form-builder-by-dragwyb' ),
				'conditions'   => array(
					'country_code_enabled' => 'yes',
				),
			)
		);

		$this->add_control(
			'country_internationalisation',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Internationalisation', 'smart-form-builder-by-dragwyb' ),
				'options'      => $this->get_i18n_language_options(),
				'default'      => 'en',
				'label_inline' => true,
				'conditions'   => array(
					'country_code_enabled' => 'yes',
				),
			)
		);

		$this->add_control(
			'country_show_flags',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Show Flags', 'smart-form-builder-by-dragwyb' ),
				'label_on'     => __( 'Yes', 'smart-form-builder-by-dragwyb' ),
				'label_off'    => __( 'No', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'conditions'   => array(
					'country_code_enabled' => 'yes',
				),
			)
		);

		$this->end_section();

		$this->start_section(
			'section_content_validation',
			array(
				'label' => __( 'Validation Rules', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::ContentTab,
			)
		);

		$this->add_control(
			'required',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Is Required?', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->end_section();

		$this->start_section(
			'section_style_label',
			array(
				'label' => __( 'Label Appearance', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::StyleTab,
			)
		);

		$this->add_control(
			'label_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-label-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'label_spacing',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Bottom Margin', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-label-spacing: {{VALUE}}{{UNIT}};' ),
			)
		);

		$this->end_section();

		$this->start_section(
			'section_style_input',
			array(
				'label' => __( 'Input Box Style', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::StyleTab,
			)
		);

		$this->start_tabs( 'tabs_input_style' );

		$this->start_tab( 'tab_input_normal', array( 'label' => __( 'Normal', 'smart-form-builder-by-dragwyb' ) ) );

		$this->add_control(
			'input_bg_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-input-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'input_placeholder_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Placeholder Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-input-placeholder-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'input_text_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Text Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-input-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			'input_border',
			array(
				'type'     => Controls::GROUP_BORDER,
				'selector' => '{{WRAPPER}}',
				'prefix'   => 'input',
			)
		);

		$this->add_control(
			'input_padding',
			array(
				'type'       => Controls::DIMENSIONS,
				'label'      => __( 'Inner Padding', 'smart-form-builder-by-dragwyb' ),
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}}' => '--dragwyb-input-pt: {{TOP}}{{UNIT}}; --dragwyb-input-pr: {{RIGHT}}{{UNIT}}; --dragwyb-input-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-input-pl: {{LEFT}}{{UNIT}};' ),
				'separator'  => 'before',
			)
		);

		$this->end_tab();

		$this->start_tab( 'tab_input_focus', array( 'label' => __( 'Focus', 'smart-form-builder-by-dragwyb' ) ) );

		$this->add_control(
			'input_focus_border_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Active Border Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-input-focus-border: {{VALUE}};' ),
			)
		);

		$this->end_tab();

		$this->end_tabs();

		$this->end_section();
	}

	/**
	 * Normalize a comma-separated country list to lowercase ISO codes.
	 *
	 * @param mixed $value Raw control value.
	 * @return string Comma-separated sanitized codes.
	 */
	private function normalize_country_list( $value ): string {
		if ( ! is_string( $value ) || '' === trim( $value ) ) {
			return '';
		}

		$parts = array_filter(
			array_map(
				static function ( $code ) {
					$code = strtolower( trim( $code ) );
					return preg_match( '/^[a-z]{2}$/', $code ) ? $code : '';
				},
				explode( ',', $value )
			)
		);

		return implode( ',', $parts );
	}

	protected function render_field() {
		$settings    = $this->get_field_settings();
		$id          = $this->get_the_id();
		$field_id    = $this->field_key_exist( $settings, 'field_id', uniqid( 'field_' ) );
		$label       = $this->field_key_exist( $settings, 'label', 'Phone Number' );
		$placeholder = $this->field_key_exist( $settings, 'placeholder', ' ' );
		$value       = $this->field_key_exist( $settings, 'default_value', '' );
		$help        = $this->field_key_exist( $settings, 'help_text', '' );
		$required    = $this->field_key_exist( $settings, 'required', '' ) === 'yes';
		$classes     = $this->field_key_exist( $settings, 'css_classes', '' );

		$country_enabled = $this->field_key_exist( $settings, 'country_code_enabled', 'no' ) === 'yes';

		$default_country = (string) $this->field_key_exist( $settings, 'country_code_default', 'us' );
		if ( preg_match( '/[^a-zA-Z]/', $default_country ) ) {
			$default_country = '';
		} else {
			$default_country = strtolower( $default_country );
		}

		$include_countries = $this->normalize_country_list( $this->field_key_exist( $settings, 'country_code_include', '' ) );
		$exclude_countries = $this->normalize_country_list( $this->field_key_exist( $settings, 'country_code_exclude', '' ) );

		$include_sorted = array_filter( explode( ',', $include_countries ) );
		$exclude_sorted = array_filter( explode( ',', $exclude_countries ) );
		sort( $include_sorted );
		sort( $exclude_sorted );
		$common_countries = ( ! empty( $include_sorted ) && $include_sorted === $exclude_sorted ) ? 'same' : '';

		$wrapper_class = $this->field_wrapper_class( $classes, $settings );
		if ( $country_enabled ) {
			$wrapper_class .= ' country-code-enabled dragwyb-no-float';
		}

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'    => $this->field_wrapper_id( $id ),
				'class' => $wrapper_class,
			)
		);

		$input_attrs = array(
			'type'        => 'tel',
			'id'          => $field_id,
			'name'        => $field_id,
			'value'       => $value,
			'placeholder' => $placeholder,
			'class'       => 'dragwyb-field-input',
		);

		if ( $country_enabled ) {
			$iti_config = implode(
				'|',
				array(
					'cc',
					$default_country,
					$include_countries,
					$exclude_countries,
					$this->field_key_exist( $settings, 'dial_code_visibility', 'show' ),
					$this->field_key_exist( $settings, 'country_strict_mode', 'no' ),
					$this->field_key_exist( $settings, 'country_internationalisation', 'en' ),
					$this->field_key_exist( $settings, 'country_show_flags', 'yes' ),
				)
			);

			$input_attrs['data-country-code']         = 'yes';
			$input_attrs['data-default-country']      = $default_country;
			$input_attrs['data-include-countries']    = $include_countries;
			$input_attrs['data-exclude-countries']    = $exclude_countries;
			$input_attrs['data-common-countries']     = $common_countries;
			$input_attrs['data-dial-code-visibility'] = $this->field_key_exist( $settings, 'dial_code_visibility', 'show' );
			$input_attrs['data-strict-mode']          = $this->field_key_exist( $settings, 'country_strict_mode', 'no' );
			$input_attrs['data-internationalisation'] = $this->field_key_exist( $settings, 'country_internationalisation', 'en' );
			$input_attrs['data-show-flags']           = $this->field_key_exist( $settings, 'country_show_flags', 'yes' );
			$input_attrs['data-iti-config']           = $iti_config;
			$input_attrs['autocomplete']             = 'tel';
		}

		$this->add_field_attributes( 'input', $input_attrs );
		?>
		<div <?php $this->render_field_attributes( 'wrapper' ); ?>>
			<div class="dragwyb-input-group">
				<input
				<?php
				$this->render_field_attributes( 'input' );
				echo $required ? 'required' : '';
				?>
				/>
				<?php if ( ! empty( $label ) ) : ?>
					<?php $this->render_field_label( $field_id, $label, $required, $settings ); ?>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $help ) ) : ?>
				<div class="dragwyb-field-help"><?php echo wp_kses_post( $help ); ?></div>
			<?php endif; ?>
		</div>
		<?php
	}

	public function validate( $value, $field_id, $form_config, Form_Submission_Handler $error_handler ): void {
		if ( ! isset( $form_config['fields'][ $field_id ] ) ) {
			$error_handler->add_error( $field_id, __( 'Invalid field.', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		$field_attr = isset( $form_config['fields'][ $field_id ]['attributes'] ) ? $form_config['fields'][ $field_id ]['attributes'] : array();

		if ( empty( $value ) && ( isset( $field_attr['required'] ) && 'yes' === $field_attr['required'] ) ) {
			$error_handler->add_error( $field_id, __( 'This field is required.', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		if ( empty( $value ) ) {
			return;
		}

		$country_enabled = isset( $field_attr['country_code_enabled'] ) && 'yes' === $field_attr['country_code_enabled'];

		if ( $country_enabled ) {
			if ( ! preg_match( '/^\+/', (string) $value ) ) {
				$error_handler->add_error( $field_id, __( 'Country code missing!', 'smart-form-builder-by-dragwyb' ) );
				return;
			}

			if ( preg_match_all( '/\+/', (string) $value ) > 1 ) {
				$error_handler->add_error( $field_id, __( 'Invalid Number!', 'smart-form-builder-by-dragwyb' ) );
				return;
			}

			if ( ! preg_match( '/^\+[0-9\-\.\(\)\s]+$/', (string) $value ) ) {
				$error_handler->add_error( $field_id, __( 'Please enter a valid phone number.', 'smart-form-builder-by-dragwyb' ) );
				return;
			}

			return;
		}

		if ( ! preg_match( '/^[0-9\-\.\(\)\s+]+$/', (string) $value ) ) {
			$error_handler->add_error( $field_id, __( 'Please enter a valid phone number.', 'smart-form-builder-by-dragwyb' ) );
		}
	}

	/**
	 * Sanitize the field value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @return mixed Sanitized value.
	 */
	public function sanitize( $value = null ) {
		if ( $value && is_string( $value ) ) {
			return sanitize_text_field( $value );
		}
		return null;
	}
}
