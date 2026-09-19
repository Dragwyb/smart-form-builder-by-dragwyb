<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Mask extends Field_Base {

	/**
	 * Whether mask assets have been localized.
	 *
	 * @var bool
	 */
	private static $assets_localized = false;

	protected function register_scripts() {
		return array( 'dragwyb-mask-field' );
	}

	protected function register_styles() {
		return array( 'dragwyb-mask-field' );
	}

	public function __construct() {
		parent::__construct();
		$this->register_mask_assets();
	}

	/**
	 * Register mask field scripts and styles.
	 */
	private function register_mask_assets(): void {
		if ( ! wp_style_is( 'dragwyb-mask-field', 'registered' ) ) {
			wp_register_style(
				'dragwyb-mask-field',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/mask-field.css' ),
				array(),
				DRAGWYB_FORM_BUILDER_VERSION,
				'all'
			);
		}

		if ( ! wp_script_is( 'dragwyb-mask-field', 'registered' ) ) {
			wp_register_script(
				'dragwyb-mask-field',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/mask-field.js' ),
				array( 'jquery', 'dragwyb-form-frontend' ),
				DRAGWYB_FORM_BUILDER_VERSION,
				true
			);
		}

		$this->localize_mask_assets();
	}

	/**
	 * Localize shared mask script data once.
	 */
	private function localize_mask_assets(): void {
		if ( self::$assets_localized ) {
			return;
		}

		self::$assets_localized = true;

		wp_localize_script(
			'dragwyb-mask-field',
			'DragwybMaskData',
			array(
				'pluginUrl'     => DRAGWYB_FORM_BUILDER_URL,
				'errorMessages' => $this->get_error_messages(),
			)
		);
	}

	/**
	 * Default validation error messages keyed by mask class.
	 *
	 * @return array<string, string>
	 */
	private function get_error_messages(): array {
		return array(
			'mask-cnpj'   => __( 'Invalid CNPJ.', 'smart-form-builder-by-dragwyb' ),
			'mask-cpf'    => __( 'Invalid CPF.', 'smart-form-builder-by-dragwyb' ),
			'mask-cep'    => __( 'Invalid CEP (XXXXX-XXX).', 'smart-form-builder-by-dragwyb' ),
			'mask-phus'   => __( 'Invalid number: (123) 456-7890', 'smart-form-builder-by-dragwyb' ),
			'mask-ph8'    => __( 'Invalid number: 1234-5678', 'smart-form-builder-by-dragwyb' ),
			'mask-ddd8'   => __( 'Invalid number: (DDD) 1234-5678', 'smart-form-builder-by-dragwyb' ),
			'mask-ddd9'   => __( 'Invalid number: (DDD) 91234-5678', 'smart-form-builder-by-dragwyb' ),
			'mask-dmy'    => __( 'Invalid date: dd/mm/yyyy', 'smart-form-builder-by-dragwyb' ),
			'mask-mdy'    => __( 'Invalid date: mm/dd/yyyy', 'smart-form-builder-by-dragwyb' ),
			'mask-hms'    => __( 'Invalid time: hh:mm:ss', 'smart-form-builder-by-dragwyb' ),
			'mask-hm'     => __( 'Invalid time: hh:mm', 'smart-form-builder-by-dragwyb' ),
			'mask-dmyhm'  => __( 'Invalid date: dd/mm/yyyy hh:mm', 'smart-form-builder-by-dragwyb' ),
			'mask-mdyhm'  => __( 'Invalid date: mm/dd/yyyy hh:mm', 'smart-form-builder-by-dragwyb' ),
			'mask-my'     => __( 'Invalid date: mm/yyyy', 'smart-form-builder-by-dragwyb' ),
			'mask-ccs'    => __( 'Invalid credit card number.', 'smart-form-builder-by-dragwyb' ),
			'mask-cch'    => __( 'Invalid credit card number.', 'smart-form-builder-by-dragwyb' ),
			'mask-ccmy'   => __( 'Invalid expiry date.', 'smart-form-builder-by-dragwyb' ),
			'mask-ccmyy'  => __( 'Invalid expiry date.', 'smart-form-builder-by-dragwyb' ),
			'mask-ipv4'   => __( 'Invalid IPv4 address.', 'smart-form-builder-by-dragwyb' ),
			'mask-custom' => __( 'Invalid format.', 'smart-form-builder-by-dragwyb' ),
		);
	}

	protected function init(): void {
		$this->type     = 'mask';
		$this->name     = __( 'Mask Field', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'fas fa-mask';
		$this->category = 'advanced-fields';
		$this->keywords = array( 'mask', 'format', 'cpf', 'cnpj', 'cep', 'phone', 'money', 'credit', 'ip', 'date' );
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
				'default' => __( 'Masked Input', 'smart-form-builder-by-dragwyb' ),
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
				'default' => '',
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
			'section_content_mask',
			array(
				'label' => __( 'Mask Settings', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::ContentTab,
			)
		);

		$this->add_control(
			'mask_type',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Mask Type', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'phone'       => __( 'Phone', 'smart-form-builder-by-dragwyb' ),
					'datetime'    => __( 'Date & Time', 'smart-form-builder-by-dragwyb' ),
					'money'       => __( 'Money', 'smart-form-builder-by-dragwyb' ),
					'credit_card' => __( 'Credit Card', 'smart-form-builder-by-dragwyb' ),
					'brazilian'   => __( 'Brazilian Formats', 'smart-form-builder-by-dragwyb' ),
					'ip'          => __( 'IP Address', 'smart-form-builder-by-dragwyb' ),
					'custom'      => __( 'Custom Mask', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'phone',
				'label_inline' => true,
			)
		);

		$this->add_control(
			'custom_mask',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Mask Pattern', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Use 0 for numbers, A for letters and * for numbers or letters.', 'smart-form-builder-by-dragwyb' ),
				'default'     => '',
				'placeholder' => '0000-0000',
				'conditions'  => array( 'mask_type' => 'custom' ),
			)
		);

		$this->add_control(
			'custom_mask_help',
			array(
				'type'       => Controls::RAW_HTML,
				'raw'        => '
					<div class="dragwyb-mask-help">
						<strong>Mask characters:</strong><br>
						0 = Number<br>
						A = Letter<br>
						* = Number or Letter<br>
						Other characters are automatically inserted.
						<br><br>
						Example: <code>0000-0000</code>
					</div>
				',
				'conditions' => array( 'mask_type' => 'custom' ),
			)
		);

		$this->add_control(
			'auto_placeholder',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Mask Placeholders', 'smart-form-builder-by-dragwyb' ),
				'description'  => __( 'Show a format hint as the placeholder.', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'phone_format',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Phone Format', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'phone_usa'  => __( 'Phone (USA)', 'smart-form-builder-by-dragwyb' ),
					'phone_d8'   => __( 'Phone (8-digit)', 'smart-form-builder-by-dragwyb' ),
					'phone_ddd8' => __( 'Phone (DDD + 8-digit)', 'smart-form-builder-by-dragwyb' ),
					'phone_ddd9' => __( 'Phone (DDD + 9-digit)', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'phone_usa',
				'label_inline' => true,
				'conditions'   => array( 'mask_type' => 'phone' ),
			)
		);

		$this->add_control(
			'datetime_format',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Date Format', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'dmy'   => __( 'Date (dd/mm/yyyy)', 'smart-form-builder-by-dragwyb' ),
					'mdy'   => __( 'Date (mm/dd/yyyy)', 'smart-form-builder-by-dragwyb' ),
					'dmyhm' => __( 'DateTime (dd/mm/yyyy hh:mm)', 'smart-form-builder-by-dragwyb' ),
					'mdyhm' => __( 'DateTime (mm/dd/yyyy hh:mm)', 'smart-form-builder-by-dragwyb' ),
					'hm'    => __( 'Time (hh:mm)', 'smart-form-builder-by-dragwyb' ),
					'hms'   => __( 'Time (hh:mm:ss)', 'smart-form-builder-by-dragwyb' ),
					'my'    => __( 'Month/Year (mm/yyyy)', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'dmy',
				'label_inline' => true,
				'conditions'   => array( 'mask_type' => 'datetime' ),
			)
		);

		$this->add_control(
			'money_format',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Thousand Separator', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'dot'   => __( 'Dot (.)', 'smart-form-builder-by-dragwyb' ),
					'comma' => __( 'Comma (,)', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'dot',
				'label_inline' => true,
				'conditions'   => array( 'mask_type' => 'money' ),
			)
		);

		$this->add_control(
			'money_prefix',
			array(
				'type'       => Controls::TEXT,
				'label'      => __( 'Currency Prefix', 'smart-form-builder-by-dragwyb' ),
				'default'    => '$',
				'conditions' => array( 'mask_type' => 'money' ),
			)
		);

		$this->add_control(
			'money_decimal_places',
			array(
				'type'       => Controls::NUMBER,
				'label'      => __( 'Decimal Places', 'smart-form-builder-by-dragwyb' ),
				'default'    => 2,
				'min'        => 0,
				'max'        => 4,
				'conditions' => array( 'mask_type' => 'money' ),
			)
		);

		$this->add_control(
			'credit_card_options',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Credit Card Options', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'space'                   => __( 'Card number (spaces)', 'smart-form-builder-by-dragwyb' ),
					'hyphen'                  => __( 'Card number (hyphens)', 'smart-form-builder-by-dragwyb' ),
					'credit_card_date'        => __( 'Expiry Date (MM/YY)', 'smart-form-builder-by-dragwyb' ),
					'credit_card_expiry_date' => __( 'Expiry Date (MM/YYYY)', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'hyphen',
				'label_inline' => true,
				'conditions'   => array( 'mask_type' => 'credit_card' ),
			)
		);

		$this->add_control(
			'brazilian_format',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Brazilian Format', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'cpf'  => __( 'CPF', 'smart-form-builder-by-dragwyb' ),
					'cnpj' => __( 'CNPJ', 'smart-form-builder-by-dragwyb' ),
					'cep'  => __( 'CEP', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'cpf',
				'label_inline' => true,
				'conditions'   => array( 'mask_type' => 'brazilian' ),
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
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-label' => 'color: {{VALUE}};',
					'{{WRAPPER}}'                      => '--dragwyb-label-color: {{VALUE}};',
				),
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
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-label' => 'margin-bottom: {{VALUE}}{{UNIT}};',
					'{{WRAPPER}}'                      => '--dragwyb-label-spacing: {{VALUE}}{{UNIT}};',
				),
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
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-input' => 'background-color: {{VALUE}};',
					'{{WRAPPER}}'                      => '--dragwyb-input-bg: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'input_placeholder_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Placeholder Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-input::placeholder' => 'color: {{VALUE}};',
					'{{WRAPPER}}'                                  => '--dragwyb-input-placeholder-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'input_text_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Text Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-input' => 'color: {{VALUE}};',
					'{{WRAPPER}}'                      => '--dragwyb-input-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			'input_border',
			array(
				'type'              => Controls::GROUP_BORDER,
				'selector'          => '{{WRAPPER}} .dragwyb-field-input',
				'variable_selector' => '{{WRAPPER}}',
				'prefix'            => 'input',
			)
		);

		$this->end_tab();

		$this->start_tab( 'tab_input_focus', array( 'label' => __( 'Focus', 'smart-form-builder-by-dragwyb' ) ) );

		$this->add_control(
			'input_focus_border_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Active Border Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-input:focus' => 'border-color: {{VALUE}};',
					'{{WRAPPER}}'                            => '--dragwyb-input-focus-border: {{VALUE}};',
				),
			)
		);

		$this->end_tab();

		$this->end_tabs();

		$this->add_control(
			'input_padding',
			array(
				'type'       => Controls::DIMENSIONS,
				'label'      => __( 'Inner Padding', 'smart-form-builder-by-dragwyb' ),
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dragwyb-field-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}}'                      => '--dragwyb-input-pt: {{TOP}}{{UNIT}}; --dragwyb-input-pr: {{RIGHT}}{{UNIT}}; --dragwyb-input-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-input-pl: {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->end_section();
	}

	/**
	 * Resolve the concrete mask CSS class from field settings.
	 *
	 * @param array $settings Field attributes.
	 * @return string
	 */
	private function resolve_mask_class( array $settings ): string {
		$mask_type = $this->field_key_exist( $settings, 'mask_type', 'phone' );

		switch ( $mask_type ) {
			case 'phone':
				$phone_format = $this->field_key_exist( $settings, 'phone_format', 'phone_usa' );
				$map          = array(
					'phone_usa'  => 'mask-phus',
					'phone_d8'   => 'mask-ph8',
					'phone_ddd8' => 'mask-ddd8',
					'phone_ddd9' => 'mask-ddd9',
				);
				return $map[ $phone_format ] ?? 'mask-phus';

			case 'datetime':
				$datetime_format = $this->field_key_exist( $settings, 'datetime_format', 'dmy' );
				$map             = array(
					'dmy'   => 'mask-dmy',
					'mdy'   => 'mask-mdy',
					'dmyhm' => 'mask-dmyhm',
					'mdyhm' => 'mask-mdyhm',
					'hm'    => 'mask-hm',
					'hms'   => 'mask-hms',
					'my'    => 'mask-my',
				);
				return $map[ $datetime_format ] ?? 'mask-dmy';

			case 'money':
				return 'mask-moneyc';

			case 'credit_card':
				$cc_option = $this->field_key_exist( $settings, 'credit_card_options', 'hyphen' );
				$map       = array(
					'space'                   => 'mask-ccs',
					'hyphen'                  => 'mask-cch',
					'credit_card_date'        => 'mask-ccmy',
					'credit_card_expiry_date' => 'mask-ccmyy',
				);
				return $map[ $cc_option ] ?? 'mask-cch';

			case 'brazilian':
				$br_format = $this->field_key_exist( $settings, 'brazilian_format', 'cpf' );
				$map       = array(
					'cpf'  => 'mask-cpf',
					'cnpj' => 'mask-cnpj',
					'cep'  => 'mask-cep',
				);
				return $map[ $br_format ] ?? 'mask-cpf';

			case 'ip':
				return 'mask-ipv4';

			case 'custom':
				return 'mask-custom';

			default:
				return 'mask-phus';
		}
	}

	private function get_custom_placeholder( string $pattern ): string {
		return strtr(
			$pattern,
			array(
				'0' => 'X',
				'A' => 'X',
				'*' => 'X',
			)
		);
	}

	/**
	 * Auto placeholder text for the resolved mask class.
	 *
	 * @param string $mask_class Mask CSS class.
	 * @param array  $settings   Field attributes.
	 * @return string
	 */
	private function get_auto_placeholder( string $mask_class, array $settings ): string {
		$placeholders = array(
			'mask-phus'   => '(XXX) XXX-XXXX',
			'mask-ph8'    => 'XXXX-XXXX',
			'mask-ddd8'   => '(XX) XXXX-XXXX',
			'mask-ddd9'   => '(XX) XXXXX-XXXX',
			'mask-dmy'    => 'XX/XX/XXXX',
			'mask-mdy'    => 'XX/XX/XXXX',
			'mask-hm'     => 'XX:XX',
			'mask-hms'    => 'XX:XX:XX',
			'mask-dmyhm'  => 'XX/XX/XXXX XX:XX',
			'mask-mdyhm'  => 'XX/XX/XXXX XX:XX',
			'mask-my'     => 'XX/XXXX',
			'mask-ccs'    => 'XXXX XXXX XXXX XXXX',
			'mask-cch'    => 'XXXX-XXXX-XXXX-XXXX',
			'mask-ccmy'   => 'XX/XX',
			'mask-ccmyy'  => 'XX/XXXX',
			'mask-cpf'    => 'XXX.XXX.XXX-XX',
			'mask-cnpj'   => 'XX.XXX.XXX/XXXX-XX',
			'mask-cep'    => 'XXXXX-XXX',
			'mask-ipv4'   => 'XXX.XXX.XXX.XXX',
			'mask-custom' => $this->get_custom_placeholder(
				(string) $this->field_key_exist( $settings, 'custom_mask', '' )
			),
		);

		if ( 'mask-moneyc' === $mask_class ) {
			$prefix            = (string) $this->field_key_exist( $settings, 'money_prefix', '$' );
			$format            = $this->field_key_exist( $settings, 'money_format', 'dot' );
			$decimal_places    = (int) $this->field_key_exist( $settings, 'money_decimal_places', 2 );
			$decimal_separator = 'dot' === $format ? ',' : '.';
			$decimals          = str_repeat( '0', max( 0, $decimal_places ) );
			return $prefix . '0' . $decimal_separator . $decimals;
		}

		return $placeholders[ $mask_class ] ?? '';
	}

	/**
	 * Whether this mask shows a credit-card brand logo.
	 *
	 * @param string $mask_class Mask CSS class.
	 * @return bool
	 */
	private function shows_card_logo( string $mask_class ): bool {
		return in_array( $mask_class, array( 'mask-ccs', 'mask-cch' ), true );
	}

	protected function render_field() {
		$settings         = $this->get_field_settings();
		$id               = $this->get_the_id();
		$field_id         = $this->field_key_exist( $settings, 'field_id', uniqid( 'field_' ) );
		$label            = $this->field_key_exist( $settings, 'label', 'Masked Input' );
		$value            = $this->field_key_exist( $settings, 'default_value', '' );
		$help             = $this->field_key_exist( $settings, 'help_text', '' );
		$required         = $this->field_key_exist( $settings, 'required', '' ) === 'yes';
		$classes          = $this->field_key_exist( $settings, 'css_classes', '' );
		$mask_class       = $this->resolve_mask_class( $settings );
		$auto_placeholder = $this->field_key_exist( $settings, 'auto_placeholder', 'yes' ) === 'yes';
		$placeholder      = $this->field_key_exist( $settings, 'placeholder', '' );

		if ( $auto_placeholder || '' === trim( (string) $placeholder ) ) {
			$auto = $this->get_auto_placeholder( $mask_class, $settings );
			if ( $auto_placeholder && '' !== $auto ) {
				$placeholder = $auto;
			}
		}

		if ( '' === $placeholder ) {
			$placeholder = ' ';
		}

		$money_format         = $this->field_key_exist( $settings, 'money_format', 'dot' );
		$money_prefix         = (string) $this->field_key_exist( $settings, 'money_prefix', '$' );
		$money_decimal_places = (string) $this->field_key_exist( $settings, 'money_decimal_places', '2' );

		$inputmode = 'numeric';
		if ( 'mask-cnpj' === $mask_class ) {
			$inputmode = 'text';
		} elseif ( in_array( $mask_class, array( 'mask-phus', 'mask-ph8', 'mask-ddd8', 'mask-ddd9' ), true ) ) {
			$inputmode = 'tel';
		}

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'    => $this->field_wrapper_id( $id ),
				'class' => $this->field_wrapper_class( $classes, $settings ),
			)
		);

		$input_attrs = array(
			'type'                  => 'text',
			'id'                    => $field_id,
			'name'                  => $field_id,
			'value'                 => $value,
			'placeholder'           => $placeholder,
			'class'                 => 'dragwyb-field-input dragwyb-mask-input ' . $mask_class,
			'inputmode'             => $inputmode,
			'data-mask-class'       => $mask_class,
			'data-moneymask-format' => $money_format,
			'data-moneymask-prefix' => $money_prefix,
			'data-decimal-places'   => $money_decimal_places,
			'data-custom-mask'      => $this->field_key_exist( $settings, 'custom_mask', '' ),
			'autocomplete'          => 'off',
		);

		$this->add_field_attributes( 'input', $input_attrs );

		$error_class = 'error-' . str_replace( 'mask-', '', $mask_class );
		?>
		<div <?php $this->render_field_attributes( 'wrapper' ); ?>>
			<div class="dragwyb-input-group">
				<input
				<?php
				$this->render_field_attributes( 'input' );
				echo $required ? ' required' : '';
				?>
				/>
				<?php if ( ! empty( $label ) ) : ?>
					<?php $this->render_field_label( $field_id, $label, $required, $settings ); ?>
				<?php endif; ?>
				<?php if ( $this->shows_card_logo( $mask_class ) ) : ?>
					<img class="dragwyb-card-logo" src="" alt="" hidden />
				<?php endif; ?>
			</div>
			<div class="dragwyb-mask-error <?php echo esc_attr( $error_class ); ?>" hidden></div>
			<?php if ( ! empty( $help ) ) : ?>
				<div class="dragwyb-field-help"><?php echo esc_html( $help ); ?></div>
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

		$mask_class = $this->resolve_mask_class( $field_attr );
		if ( ! $this->is_valid_masked_value( (string) $value, $mask_class, $field_attr ) ) {
			$messages = $this->get_error_messages();
			$message  = $messages[ $mask_class ] ?? __( 'Invalid value format.', 'smart-form-builder-by-dragwyb' );
			$error_handler->add_error( $field_id, $message );
		}
	}

	/**
	 * Server-side mask value validation.
	 *
	 * @param string $value      Submitted value.
	 * @param string $mask_class Mask CSS class.
	 * @return bool
	 */
	private function is_valid_masked_value( string $value, string $mask_class, array $settings = array() ): bool {
		switch ( $mask_class ) {
			case 'mask-phus':
				return (bool) preg_match( '/^\(\d{3}\) \d{3}-\d{4}$/', $value );
			case 'mask-ph8':
				return (bool) preg_match( '/^\d{4}-\d{4}$/', $value );
			case 'mask-ddd8':
				return (bool) preg_match( '/^\(\d{2}\) \d{4}-\d{4}$/', $value );
			case 'mask-ddd9':
				return (bool) preg_match( '/^\(\d{2}\) 9\d{4}-\d{4}$/', $value );
			case 'mask-dmy':
				return $this->is_valid_date_time( $value, 'DMY' );
			case 'mask-mdy':
				return $this->is_valid_date_time( $value, 'MDY' );
			case 'mask-hms':
				return $this->is_valid_date_time( $value, 'HMS' );
			case 'mask-hm':
				return $this->is_valid_date_time( $value, 'HM' );
			case 'mask-dmyhm':
				return $this->is_valid_date_time( $value, 'DMY-HM' );
			case 'mask-mdyhm':
				return $this->is_valid_date_time( $value, 'MDY-HM' );
			case 'mask-my':
				return $this->is_valid_date_time( $value, 'MY' );
			case 'mask-ccs':
			case 'mask-cch':
				return $this->is_valid_credit_card( $value );
			case 'mask-ccmy':
				return $this->is_valid_expiry( $value, 'MM/YY' );
			case 'mask-ccmyy':
				return $this->is_valid_expiry( $value, 'MM/YYYY' );
			case 'mask-cpf':
				return $this->is_valid_cpf( $value );
			case 'mask-cnpj':
				return $this->is_valid_cnpj( $value );
			case 'mask-cep':
				return (bool) preg_match( '/^\d{5}-\d{3}$/', $value );
			case 'mask-ipv4':
				return $this->is_valid_ipv4( $value );
			case 'mask-moneyc':
				return (bool) preg_match( '/^[^0-9]*[0-9]/d*[.,]\d+$/', $value );
			case 'mask-custom':
				return $this->is_valid_custom_mask(
					$value,
					(string) ( $settings['custom_mask'] ?? '' )
				);
			default:
				return true;
		}
	}

	private function is_valid_custom_mask( string $value, string $pattern ): bool {
		if ( '' === $pattern ) {
			return false;
		}

		$regex = '';

		foreach ( str_split( $pattern ) as $char ) {
			switch ( $char ) {
				case '0':
					$regex .= '\d';
					break;
				case 'A':
					$regex .= '[A-Za-z]';
					break;
				case '*':
					$regex .= '[A-Za-z0-9]';
					break;
				default:
					$regex .= preg_quote( $char, '/' );
					break;
			}
		}

		return (bool) preg_match( '/^' . $regex . '$/', $value );
	}

	/**
	 * Validate date/time strings against a format key.
	 *
	 * @param string $value  Input value.
	 * @param string $format Format key.
	 * @return bool
	 */
	private function is_valid_date_time( string $value, string $format ): bool {
		$patterns = array(
			'DMY'    => '/^(\d{2})\/(\d{2})\/(\d{4})$/',
			'MDY'    => '/^(\d{2})\/(\d{2})\/(\d{4})$/',
			'HMS'    => '/^(\d{2}):(\d{2}):(\d{2})$/',
			'HM'     => '/^(\d{2}):(\d{2})$/',
			'DMY-HM' => '/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/',
			'MDY-HM' => '/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/',
			'MY'     => '/^(\d{2})\/(\d{4})$/',
		);

		if ( ! isset( $patterns[ $format ] ) || ! preg_match( $patterns[ $format ], $value, $match ) ) {
			return false;
		}

		$parts = array();
		switch ( $format ) {
			case 'DMY':
				$parts = array(
					'day'   => (int) $match[1],
					'month' => (int) $match[2],
					'year'  => (int) $match[3],
				);
				break;
			case 'MDY':
				$parts = array(
					'month' => (int) $match[1],
					'day'   => (int) $match[2],
					'year'  => (int) $match[3],
				);
				break;
			case 'HMS':
				$parts = array(
					'hour'   => (int) $match[1],
					'minute' => (int) $match[2],
					'second' => (int) $match[3],
				);
				break;
			case 'HM':
				$parts = array(
					'hour'   => (int) $match[1],
					'minute' => (int) $match[2],
				);
				break;
			case 'DMY-HM':
				$parts = array(
					'day'    => (int) $match[1],
					'month'  => (int) $match[2],
					'year'   => (int) $match[3],
					'hour'   => (int) $match[4],
					'minute' => (int) $match[5],
				);
				break;
			case 'MDY-HM':
				$parts = array(
					'month'  => (int) $match[1],
					'day'    => (int) $match[2],
					'year'   => (int) $match[3],
					'hour'   => (int) $match[4],
					'minute' => (int) $match[5],
				);
				break;
			case 'MY':
				$parts = array(
					'month' => (int) $match[1],
					'year'  => (int) $match[2],
				);
				break;
		}

		if ( isset( $parts['year'] ) && ( $parts['year'] < 1500 || $parts['year'] > 3000 ) ) {
			return false;
		}
		if ( isset( $parts['month'] ) && ( $parts['month'] < 1 || $parts['month'] > 12 ) ) {
			return false;
		}
		if ( isset( $parts['day'] ) ) {
			$days_in_month = (int) gmdate( 't', gmmktime( 0, 0, 0, $parts['month'], 1, $parts['year'] ) );
			if ( $parts['day'] < 1 || $parts['day'] > $days_in_month ) {
				return false;
			}
		}
		if ( isset( $parts['hour'] ) && ( $parts['hour'] < 0 || $parts['hour'] >= 24 ) ) {
			return false;
		}
		if ( isset( $parts['minute'] ) && ( $parts['minute'] < 0 || $parts['minute'] >= 60 ) ) {
			return false;
		}
		if ( isset( $parts['second'] ) && ( $parts['second'] < 0 || $parts['second'] >= 60 ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate card expiry in the future.
	 *
	 * @param string $value  Expiry value.
	 * @param string $format MM/YY or MM/YYYY.
	 * @return bool
	 */
	private function is_valid_expiry( string $value, string $format ): bool {
		$pattern = 'MM/YY' === $format ? '/^(\d{2})\/(\d{2})$/' : '/^(\d{2})\/(\d{4})$/';
		if ( ! preg_match( $pattern, $value, $match ) ) {
			return false;
		}

		$month = (int) $match[1];
		$year  = (int) $match[2];

		if ( 'MM/YY' === $format ) {
			$year += 2000;
		}

		if ( $month < 1 || $month > 12 ) {
			return false;
		}

		$current_year  = (int) gmdate( 'Y' );
		$current_month = (int) gmdate( 'n' );

		if ( $year < $current_year || ( $year === $current_year && $month < $current_month ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Luhn credit-card validation.
	 *
	 * @param string $card_number Card number with separators.
	 * @return bool
	 */
	private function is_valid_credit_card( string $card_number ): bool {
		$cleaned = preg_replace( '/\D/', '', $card_number );
		$len     = strlen( (string) $cleaned );

		if ( $len < 15 || $len > 16 ) {
			return false;
		}

		$sum           = 0;
		$should_double = false;

		for ( $i = $len - 1; $i >= 0; $i-- ) {
			$digit = (int) $cleaned[ $i ];
			if ( $should_double ) {
				$digit *= 2;
				if ( $digit > 9 ) {
					$digit -= 9;
				}
			}
			$sum          += $digit;
			$should_double = ! $should_double;
		}

		return 0 === $sum % 10;
	}

	/**
	 * Validate Brazilian CPF.
	 *
	 * @param string $cpf CPF value.
	 * @return bool
	 */
	private function is_valid_cpf( string $cpf ): bool {
		$cpf = preg_replace( '/\D/', '', $cpf );
		if ( 11 !== strlen( (string) $cpf ) || preg_match( '/^(\d)\1+$/', (string) $cpf ) ) {
			return false;
		}

		for ( $length = 9; $length <= 10; $length++ ) {
			$sum = 0;
			for ( $i = 0; $i < $length; $i++ ) {
				$sum += (int) $cpf[ $i ] * ( $length + 1 - $i );
			}
			$result = ( $sum * 10 ) % 11;
			$result = 10 === $result ? 0 : $result;
			if ( $result !== (int) $cpf[ $length ] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate Brazilian CNPJ (numeric or alphanumeric).
	 *
	 * @param string $cnpj CNPJ value.
	 * @return bool
	 */
	private function is_valid_cnpj( string $cnpj ): bool {
		$cnpj = strtoupper( preg_replace( '/[.\-\/]/', '', $cnpj ) );
		if ( ! preg_match( '/^[A-Z0-9]{12}\d{2}$/', (string) $cnpj ) || preg_match( '/^(.)\1{13}$/', (string) $cnpj ) ) {
			return false;
		}

		$calc = static function ( string $value, int $length ): int {
			$weights = 12 === $length
				? array( 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2 )
				: array( 6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2 );
			$sum     = 0;
			foreach ( $weights as $i => $weight ) {
				$sum += ( ord( $value[ $i ] ) - 48 ) * $weight;
			}
			$remainder = $sum % 11;
			return $remainder < 2 ? 0 : 11 - $remainder;
		};

		$first  = $calc( $cnpj, 12 );
		$second = $calc( substr( $cnpj, 0, 12 ) . (string) $first, 13 );

		return $first === (int) $cnpj[12] && $second === (int) $cnpj[13];
	}

	/**
	 * Validate IPv4 address.
	 *
	 * @param string $ip IP value.
	 * @return bool
	 */
	private function is_valid_ipv4( string $ip ): bool {
		if ( ! preg_match( '/^(?:\d{1,3}\.){3}\d{1,3}$/', $ip ) ) {
			return false;
		}

		foreach ( explode( '.', $ip ) as $octet ) {
			$num = (int) $octet;
			if ( $num < 0 || $num > 255 ) {
				return false;
			}
		}

		return true;
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
