<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Repeater\Repeater;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Checkbox extends Field_Base {

	protected function init(): void {
		$this->type     = 'checkbox';
		$this->name     = __( 'Checkbox Group', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'fas fa-check-square';
		$this->category = 'standard-fields';
		$this->keywords = array( 'multiple', 'choices', 'options', 'tick' );
	}

	protected function register_field_controls(): void {
		// --- Content Tab ---
		$this->start_section(
			'section_content',
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
				'default' => __( 'Select Options', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'label_icon',
			array(
				'type'  => Controls::ICON,
				'label' => __( 'Label Icon', 'smart-form-builder-by-dragwyb' ),
			)
		);

		// Repeater for Checkbox Options
		$repeater = new Repeater();
		$repeater->add_control(
			'option_label',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Label', 'smart-form-builder-by-dragwyb' ),
				'default' => 'Option 1',
			)
		);
		$repeater->add_control(
			'option_value',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Value', 'smart-form-builder-by-dragwyb' ),
				'default' => 'val_1',
			)
		);

		$this->add_control(
			'options_list',
			array(
				'type'        => Controls::REPEATER,
				'label'       => __( 'Options', 'smart-form-builder-by-dragwyb' ),
				'items'       => $repeater->get_settings(),
				'default'     => array(
					array(
						'option_label' => 'Choice 1',
						'option_value' => 'c1',
					),
					array(
						'option_label' => 'Choice 2',
						'option_value' => 'c2',
					),
				),
				'title_field' => '{{{ option_label }}}',
			)
		);

		$this->add_control(
			'default_value',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Default Value', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'If multiple values, enter values separated by commas e.g. val_1,val_2', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'layout',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Layout', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'block'  => __( 'Vertical (List)', 'smart-form-builder-by-dragwyb' ),
					'inline' => __( 'Horizontal (Inline)', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'block',
				'label_inline' => true,
			)
		);

		$this->add_control(
			'help_text',
			array(
				'type'  => Controls::TEXTAREA,
				'label' => __( 'Help Text', 'smart-form-builder-by-dragwyb' ),
				'rows'  => 3,
			)
		);

		$this->add_control(
			'required',
			array(
				'type'  => Controls::SWITCHER,
				'label' => __( 'Required', 'smart-form-builder-by-dragwyb' ),
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
			'section_style_toggle',
			array(
				'label' => __( 'Checkbox Appearance', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::StyleTab,
			)
		);

		$this->add_control(
			'option_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Text Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-option-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			'option_typography',
			array(
				'type'     => Controls::GROUP_TYPOGRAPHY,
				'label'    => __( 'Typography', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}}',
				'prefix'   => 'option',
			)
		);

		$this->add_control(
			'toggle_size',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Size', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 50,
					),
				),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-size: {{VALUE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'toggle_primary_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Primary Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-primary-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'toggle_border_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Border Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-border-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'toggle_spacing',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Spacing', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-spacing: {{VALUE}}{{UNIT}};' ),
			)
		);

		$this->end_section();
	}

	protected function render_field() {
		$settings = $this->get_field_settings();
		$id       = $this->get_the_id();
		$field_id = $this->field_key_exist( $settings, 'field_id', uniqid( 'field_' ) );
		$label    = $this->field_key_exist( $settings, 'label', '' );
		$options  = $this->field_key_exist( $settings, 'options_list', array() );
		$layout   = $this->field_key_exist( $settings, 'layout', 'block' );
		$help     = $this->field_key_exist( $settings, 'help_text', '' );
		$classes  = $this->field_key_exist( $settings, 'css_classes', '' );

		$raw_default_value = (string) $this->field_key_exist( $settings, 'default_value', '' );
		$default_value     = sanitize_text_field( $raw_default_value );
		$default_vals      = array_map( 'sanitize_text_field', array_filter( array_map( 'trim', explode( ',', $default_value ) ), 'strlen' ) );

		$layout_class = ( $layout === 'inline' ) ? 'dragwyb-inline-options' : '';

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'    => $this->field_wrapper_id( $id ),
				'class' => $this->field_wrapper_class( $classes, $settings ) . ' dragwyb-no-float',
			)
		);

		?>
		<div <?php $this->render_field_attributes( 'wrapper' ); ?>>
			<div class="dragwyb-input-group">
				<?php $this->render_field_label( '', $label, isset( $required ) ? $required : false, $settings ); ?>

				<div class="dragwyb-options-container <?php echo esc_attr( $layout_class ); ?>">
					<?php
					foreach ( $options as $index => $opt ) :
						$opt_id   = $field_id . '_' . $index;
						$opt_attr = $this->field_key_exist( $opt, 'attributes', array() );

						if ( ! isset( $opt_attr['option_value'] ) ) {
							continue;
						}

						$label_text = isset( $opt_attr['option_label'] ) ? (string) $opt_attr['option_label'] : '';
						$opt_val    = sanitize_text_field( (string) $opt_attr['option_value'] );
						$is_checked = in_array( $opt_val, $default_vals, true );

						$input_attrs = array(
							'type'  => 'checkbox',
							'id'    => $opt_id,
							'name'  => $field_id . '[]',
							'value' => $opt_val,
						);

						if ( $is_checked ) {
							$input_attrs['checked'] = 'checked';
						}

						$this->add_field_attributes( "input_{$index}", $input_attrs );
						$item_class = 'dragwyb-option-item' . ( $is_checked ? ' is-checked' : '' );
						?>
						<label class="<?php echo esc_attr( $item_class ); ?>" for="<?php echo esc_attr( $opt_id ); ?>">
							<input <?php $this->render_field_attributes( "input_{$index}" ); ?> />
							<span class="dragwyb-radio-label"><?php echo esc_html( $label_text ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
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

		$value        = (array) $value;
		$options      = isset( $field_attr['options_list'] ) ? $field_attr['options_list'] : array();
		$valid_values = array();

		foreach ( $options as $option ) {
			if ( ! isset( $option['option_value'] ) ) {
				continue;
			}
			$valid_values[] = $option['option_value'];
		}

		if ( empty( $value ) && isset( $field_attr['required'] ) && 'yes' == $field_attr['required'] ) {
			$error_handler->add_error( $field_id, __( 'This field is required', 'smart-form-builder-by-dragwyb' ) );
		}

		if ( empty( $value ) ) {
			return;
		}

		foreach ( $value as $val ) {
			if ( ! in_array( $val, $valid_values ) ) {
				$error_handler->add_error( $field_id, __( 'Invalid value', 'smart-form-builder-by-dragwyb' ) );
			}
		}
	}

	/**
	 * Sanitize the field value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @return mixed Sanitized value.
	 */
	public function sanitize( $value = null ) {
		if ( $value ) {
			if ( ! is_array( $value ) ) {
				$value = array( $value );
			}
			return array_map( 'sanitize_text_field', $value );
		}

		return null;
	}
}
