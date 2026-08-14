<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Helper\Helper;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Time extends Field_Base {

	protected function register_scripts() {
		return $this->flatpickr_enabled() ? array( 'dragwyb-flatpickr-fields' ) : array();
	}

	protected function register_styles() {
		return $this->flatpickr_enabled() ? array( 'dragwyb-flatpickr' ) : array();
	}

	private function flatpickr_enabled(): bool {
		$settings = $this->get_field_settings();
		return empty( $settings ) || $this->field_key_exist( $settings, 'use_native_time', 'no' ) !== 'yes';
	}

	public function __construct() {
		parent::__construct();
		$this->register_time_assets();
	}

	private function register_time_assets(): void {
		Helper::register_flatpickr_assets();

		if ( ! wp_script_is( 'dragwyb-flatpickr-fields', 'registered' ) ) {
			wp_register_script(
				'dragwyb-flatpickr-fields',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/flatpickr-fields.js' ),
				array( 'jquery', 'dragwyb-form-frontend', 'dragwyb-flatpickr' ),
				DRAGWYB_FORM_BUILDER_VERSION,
				true
			);
		}
	}

	private function flatpickr_conditions(): array {
		return array(
			'use_native_time!' => 'yes',
		);
	}

	protected function init(): void {
		$this->type     = 'time';
		$this->name     = __( 'Time Field', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'fas fa-clock';
		$this->category = 'advanced-fields';
		$this->keywords = array( 'hour', 'minute', 'clock', 'flatpickr' );
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
				'default' => __( 'Time', 'smart-form-builder-by-dragwyb' ),
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
				'label'   => __( 'Placeholder', 'smart-form-builder-by-dragwyb' ),
				'default' => 'HH:MM',
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
			'use_native_time',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Native HTML5', 'smart-form-builder-by-dragwyb' ),
				'description'  => __( 'Use the browser native time input instead of Flatpickr.', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'no',
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
			'section_content_flatpickr',
			array(
				'label'      => __( 'Flatpickr Settings', 'smart-form-builder-by-dragwyb' ),
				'tab'        => self::ContentTab,
				'conditions' => $this->flatpickr_conditions(),
			)
		);

		$this->add_control(
			'time_24hr',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( '24-Hour Time', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'min_time',
			array(
				'type'        => Controls::DATE,
				'label'       => __( 'Min Time', 'smart-form-builder-by-dragwyb' ),
				'picker'      => 'time',
				'date_format' => 'H:i',
				'description' => __( 'Earliest selectable time.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'max_time',
			array(
				'type'        => Controls::DATE,
				'label'       => __( 'Max Time', 'smart-form-builder-by-dragwyb' ),
				'picker'      => 'time',
				'date_format' => 'H:i',
				'description' => __( 'Latest selectable time.', 'smart-form-builder-by-dragwyb' ),
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

		// Label Style
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

		$this->end_section();
	}

	/**
	 * @return array<string, mixed>
	 */
	private function build_flatpickr_config( array $settings ): array {
		$config = array(
			'dateFormat' => 'H:i',
			'allowInput' => true,
			'enableTime' => true,
			'noCalendar' => true,
			'time_24hr'  => $this->field_key_exist( $settings, 'time_24hr', 'no' ) === 'yes',
		);

		$min_time = (string) $this->field_key_exist( $settings, 'min_time', '' );
		$max_time = (string) $this->field_key_exist( $settings, 'max_time', '' );
		if ( '' !== $min_time ) {
			$config['minTime'] = $min_time;
		}
		if ( '' !== $max_time ) {
			$config['maxTime'] = $max_time;
		}

		return $config;
	}

	private function time_to_minutes( string $time ): ?int {
		if ( ! preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $time, $matches ) ) {
			return null;
		}

		return ( (int) $matches[1] * 60 ) + (int) $matches[2];
	}

	protected function render_field() {
		$settings    = $this->get_field_settings();
		$id          = $this->get_the_id();
		$field_id    = $this->field_key_exist( $settings, 'field_id', $id );
		$label       = $this->field_key_exist( $settings, 'label', 'Time' );
		$value       = $this->field_key_exist( $settings, 'default_value', '' );
		$help        = $this->field_key_exist( $settings, 'help_text', '' );
		$required    = $this->field_key_exist( $settings, 'required', '' ) === 'yes';
		$classes     = $this->field_key_exist( $settings, 'css_classes', '' );
		$use_native  = $this->field_key_exist( $settings, 'use_native_time', 'no' ) === 'yes';
		$placeholder = $this->field_key_exist( $settings, 'placeholder', 'HH:MM' );

		$input_class = 'dragwyb-field-input dragwyb-time-field';
		if ( $use_native ) {
			$input_class .= ' dragwyb-use-native';
		}

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'    => $this->field_wrapper_id( $id ),
				'class' => $this->field_wrapper_class( $classes, $settings ),
			)
		);

		$input_attrs = array(
			'type'  => $use_native ? 'time' : 'text',
			'id'    => $field_id,
			'name'  => $field_id,
			'value' => $value,
			'class' => $input_class,
		);

		if ( $use_native ) {
			$min_time = (string) $this->field_key_exist( $settings, 'min_time', '' );
			$max_time = (string) $this->field_key_exist( $settings, 'max_time', '' );
			if ( '' !== $min_time ) {
				$input_attrs['min'] = $min_time;
			}
			if ( '' !== $max_time ) {
				$input_attrs['max'] = $max_time;
			}
		} else {
			$input_attrs['placeholder']    = $placeholder;
			$input_attrs['data-fp-config'] = wp_json_encode( $this->build_flatpickr_config( $settings ) );
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

		if ( empty( $value ) && ( isset( $field_attr['required'] ) && $field_attr['required'] == 'yes' ) ) {
			$error_handler->add_error( $field_id, __( 'This field is required.', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		if ( empty( $value ) ) {
			return;
		}

		$value      = (string) $value;
		$value_mins = $this->time_to_minutes( $value );
		if ( null === $value_mins ) {
			$error_handler->add_error( $field_id, __( 'Please enter a valid time.', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		$min_time = isset( $field_attr['min_time'] ) ? (string) $field_attr['min_time'] : '';
		$max_time = isset( $field_attr['max_time'] ) ? (string) $field_attr['max_time'] : '';

		if ( '' !== $min_time ) {
			$min_mins = $this->time_to_minutes( $min_time );
			if ( null !== $min_mins && $value_mins < $min_mins ) {
				$error_handler->add_error( $field_id, __( 'Value is below minimum', 'smart-form-builder-by-dragwyb' ) );
				return;
			}
		}

		if ( '' !== $max_time ) {
			$max_mins = $this->time_to_minutes( $max_time );
			if ( null !== $max_mins && $value_mins > $max_mins ) {
				$error_handler->add_error( $field_id, __( 'Value exceeds maximum', 'smart-form-builder-by-dragwyb' ) );
				return;
			}
		}
	}

	public function sanitize( $value = null ) {
		if ( $value && is_string( $value ) ) {
			return sanitize_text_field( $value );
		}
		return null;
	}
}
