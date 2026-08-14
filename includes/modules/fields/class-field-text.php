<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Text extends Field_Base {

	public function __construct() {
		parent::__construct();
	}

	protected function init(): void {
		$this->type     = 'text';
		$this->name     = __( 'Text Field', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'fas fa-font';
		$this->category = 'standard-fields';
		$this->keywords = array( 'input', 'string', 'single' );
	}

	protected function register_field_controls(): void {
		/**
		 * TAB: CONTENT
		 */
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
				'default' => __( 'Text Field', 'smart-form-builder-by-dragwyb' ),
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
				'default' => __( 'Enter text...', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'default_value',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Default Value', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Pre-fill the field for the user.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'help_text',
			array(
				'type'        => Controls::TEXTAREA,
				'label'       => __( 'Instructional Text', 'smart-form-builder-by-dragwyb' ),
				'rows'        => 3,
				'description' => __( 'A short hint displayed below the input.', 'smart-form-builder-by-dragwyb' ),
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

		$this->add_control(
			'min_length',
			array(
				'type'  => Controls::NUMBER,
				'label' => __( 'Minimum Length', 'smart-form-builder-by-dragwyb' ),
				'min'   => 0,
			)
		);

		$this->add_control(
			'max_length',
			array(
				'type'  => Controls::NUMBER,
				'label' => __( 'Maximum Length', 'smart-form-builder-by-dragwyb' ),
				'min'   => 1,
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

	protected function render_field() {
		$settings    = $this->get_field_settings();
		$id          = $this->get_the_id();
		$field_id    = $this->field_key_exist( $settings, 'field_id', $id );
		$label       = $this->field_key_exist( $settings, 'label', 'Text Field' );
		$placeholder = $this->field_key_exist( $settings, 'placeholder', ' ' ); // Space for float logic
		$value       = $this->field_key_exist( $settings, 'default_value', '' );
		$help        = $this->field_key_exist( $settings, 'help_text', '' );
		$required    = $this->field_key_exist( $settings, 'required', '' ) === 'yes';
		$classes     = $this->field_key_exist( $settings, 'css_classes', '' );

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'    => $this->field_wrapper_id( $id ),
				'class' => $this->field_wrapper_class( $classes, $settings ),
			)
		);

		$this->add_field_attributes(
			'input',
			array(
				'type'        => 'text',
				'id'          => $field_id,
				'name'        => $field_id,
				'value'       => $value,
				'placeholder' => $placeholder,
				'class'       => 'dragwyb-field-input',
			)
		);
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

		if ( empty( $value ) && ( isset( $field_attr['required'] ) && $field_attr['required'] == 'yes' ) ) {
			$error_handler->add_error( $field_id, __( 'This field is required.', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		if ( empty( $value ) ) {
			return;
		}

		$min_length = isset( $field_attr['min_length'] ) ? (int) $field_attr['min_length'] : null;
		$max_length = isset( $field_attr['max_length'] ) ? (int) $field_attr['max_length'] : null;

		if ( isset( $min_length ) && strlen( $value ) < $min_length ) {
			// translators: %d is the minimum length.
			$error_handler->add_error( $field_id, sprintf( esc_html__( 'This field requires at least %d characters.', 'smart-form-builder-by-dragwyb' ), esc_html( $min_length ) ) );
			return;
		}

		if ( isset( $max_length ) && strlen( $value ) > $max_length ) {
			// translators: %d is the maximum length.
			$error_handler->add_error( $field_id, sprintf( esc_html__( 'This field requires at most %d characters.', 'smart-form-builder-by-dragwyb' ), esc_html( $max_length ) ) );
			return;
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
