<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;
use Override;

class Field_Range extends Field_Base {


	protected function register_scripts() {
		return array( 'dragwyb-range-slider' );
	}

	protected function register_styles() {
		return array( 'dragwyb-range-slider' );
	}

	public function __construct() {
		parent::__construct();
		// The script is typically already registered by range slider fields, but we ensure it's there
		if ( ! wp_script_is( 'dragwyb-range-slider', 'registered' ) ) {
			$js_assets_info = array(
				'version'      => DRAGWYB_FORM_BUILDER_VERSION,
				'dependencies' => array( 'jquery', 'dragwyb-form-frontend' ),
			);

			wp_register_style( 'dragwyb-range-slider', esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/range-slider.css' ), array(), esc_attr( $js_assets_info['version'] ), 'all' );
			wp_register_script( 'dragwyb-range-slider', esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/range-slider.min.js' ), $js_assets_info['dependencies'], esc_attr( $js_assets_info['version'] ), true );
		}
	}

	protected function init(): void {
		$this->type     = 'range';
		$this->name     = __( 'Range Slider', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'fas fa-sliders-h';
		$this->category = 'advanced-fields';
		$this->keywords = array( 'slider', 'scale', 'number' );
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
				'default' => __( 'Range', 'smart-form-builder-by-dragwyb' ),
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
			'default_value',
			array(
				'type'    => Controls::NUMBER,
				'label'   => __( 'Default Value', 'smart-form-builder-by-dragwyb' ),
				'default' => 50,
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
			'min_val',
			array(
				'type'    => Controls::NUMBER,
				'label'   => __( 'Minimum Value', 'smart-form-builder-by-dragwyb' ),
				'default' => 0,
			)
		);

		$this->add_control(
			'max_val',
			array(
				'type'    => Controls::NUMBER,
				'label'   => __( 'Maximum Value', 'smart-form-builder-by-dragwyb' ),
				'default' => 100,
			)
		);

		$this->add_control(
			'step_val',
			array(
				'type'    => Controls::NUMBER,
				'label'   => __( 'Step Size', 'smart-form-builder-by-dragwyb' ),
				'default' => 1,
				'min'     => 1,
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
			'range_track_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Track Background', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-range-track-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'range_progress_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Fill Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-range-progress-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'range_thumb_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Dot Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-range-thumb-color: {{VALUE}};' ),
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

	protected function render_field() {
		$settings = $this->get_field_settings();
		$id       = $this->get_the_id();
		$field_id = $this->field_key_exist( $settings, 'field_id', $id );
		$label    = $this->field_key_exist( $settings, 'label', 'Range' );
		$value    = $this->field_key_exist( $settings, 'default_value', '50' );
		$min_val  = $this->field_key_exist( $settings, 'min_val', '0' );
		$max_val  = $this->field_key_exist( $settings, 'max_val', '100' );
		$step_val = $this->field_key_exist( $settings, 'step_val', '1' );
		$help     = $this->field_key_exist( $settings, 'help_text', '' );
		$required = $this->field_key_exist( $settings, 'required', '' ) === 'yes';
		$classes  = $this->field_key_exist( $settings, 'css_classes', '' );

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'    => $this->field_wrapper_id( $id ),
				'class' => $this->field_wrapper_class( $classes, $settings ) . ' dragwyb-no-float',
			)
		);

		$this->add_field_attributes(
			'input',
			array(
				'type'  => 'range',
				'id'    => $field_id,
				'name'  => $field_id,
				'value' => $value,
				'min'   => $min_val,
				'max'   => $max_val,
				'step'  => $step_val,
				'class' => 'dragwyb-field-input dragwyb-hidden-range',
			)
		);
		?>
		<div <?php $this->render_field_attributes( 'wrapper' ); ?>>
			<div class="dragwyb-input-group">
				<?php if ( ! empty( $label ) ) : ?>
					<?php $this->render_field_label( $field_id, $label, $required, $settings ); ?>
				<?php endif; ?>
				<div class="dragwyb-custom-range-container">
					<div class="dragwyb-range-track">
						<div class="dragwyb-range-progress"></div>
						<div class="dragwyb-range-thumb"></div>
					</div>
					<input 
					<?php
					$this->render_field_attributes( 'input' );
					echo $required ? 'required' : '';
					?>
					/>
				</div>
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

		if ( ! is_numeric( $value ) ) {
			$error_handler->add_error( $field_id, __( 'Please enter a valid number.', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		$min_val = isset( $field_attr['min_val'] ) ? (float) $field_attr['min_val'] : null;
		$max_val = isset( $field_attr['max_val'] ) ? (float) $field_attr['max_val'] : null;

		if ( $min_val !== null && (float) $value < $min_val ) {
			// translators: %s is the minimum value.
			$error_handler->add_error( $field_id, sprintf( esc_html__( 'Value must be greater than or equal to %s.', 'smart-form-builder-by-dragwyb' ), esc_html( $min_val ) ) );
		}

		if ( $max_val !== null && (float) $value > $max_val ) {
			// translators: %s is the maximum value.
			$error_handler->add_error( $field_id, sprintf( esc_html__( 'Value must be less than or equal to %s.', 'smart-form-builder-by-dragwyb' ), esc_html( $max_val ) ) );
		}
	}

	/**
	 * Sanitize the field value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @return mixed Sanitized value.
	 */
	public function sanitize( $value = null ) {
		if ( $value && is_numeric( $value ) ) {
			return floatval( $value );
		}
		return null;
	}
}
