<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Email extends Field_Base {

	protected function register_field_controls(): void {
		// ==============================================================
		// CONTENT TAB
		// ==============================================================

		$this->start_section(
			'section_content_general',
			array(
				'label' => __( 'General Settings', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::ContentTab,
			)
		);

		$this->add_control(
			'label',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Label', 'smart-form-builder-by-dragwyb' ),
				'default' => __( 'Email Address', 'smart-form-builder-by-dragwyb' ),
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
				'default' => __( 'name@example.com', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'default_value',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Default Value', 'smart-form-builder-by-dragwyb' ),
				'default' => '',
			)
		);

		$this->add_control(
			'help_text',
			array(
				'type'        => Controls::TEXTAREA,
				'label'       => __( 'Help Text', 'smart-form-builder-by-dragwyb' ),
				'rows'        => 3,
				'description' => __( 'Text that appears below the field to guide the user.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'required',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Required Field', 'smart-form-builder-by-dragwyb' ),
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

		// Input Style
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

	protected function init(): void {
		$this->type     = 'email';
		$this->keywords = array( 'contact', 'address', 'mail' );
		$this->name     = __( 'Email Field', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'fas fa-envelope';
		$this->category = 'standard-fields';
	}

	protected function render_field() {
		$settings    = $this->get_field_settings();
		$id          = $this->get_the_id();
		$field_id    = $this->field_key_exist( $settings, 'field_id', uniqid( 'field_' ) );
		$label       = $this->field_key_exist( $settings, 'label', 'Email Address' );
		$placeholder = $this->field_key_exist( $settings, 'placeholder', ' ' );
		$value       = $this->field_key_exist( $settings, 'default_value', '' );
		$help        = $this->field_key_exist( $settings, 'help_text', '' );
		$required    = $this->field_key_exist( $settings, 'required', '' ) === 'yes';
		$classes     = $this->field_key_exist( $settings, 'css_classes', '' );

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'    => $this->field_wrapper_id( $id ),
				'class' => $this->field_wrapper_class( $classes ),
			)
		);

		$this->add_field_attributes(
			'input',
			array(
				'type'        => 'email',
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

		if ( empty( $value ) && isset( $field_attr['required'] ) && 'yes' == $field_attr['required'] ) {
			$error_handler->add_error( $field_id, __( 'This field is required', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		if ( empty( $value ) ) {
			return;
		}

		if ( filter_var( $value, FILTER_VALIDATE_EMAIL ) === false && ! empty( $value ) ) {
			$error_handler->add_error( $field_id, __( 'Invalid email address', 'smart-form-builder-by-dragwyb' ) );
		}
	}

	/**
	 * Sanitize the field value.
	 *
	 * @param string $default The default value.
	 * @param mixed  $value The value to sanitize.
	 * @return mixed Sanitized value.
	 */
	public function sanitize( $default = '', $value = null ) {
		if ( $value && is_string( $value ) ) {
			return sanitize_email( $value );
		}

		return sanitize_text_field( $default );
	}
}
