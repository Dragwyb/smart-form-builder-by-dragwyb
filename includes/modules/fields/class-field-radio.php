<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Repeater\Repeater;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Radio extends Field_Base {

	protected function register_scripts() {
		return array( 'dragwyb-radio-field' );
	}

	public function __construct() {
		parent::__construct();

		if ( ! wp_script_is( 'dragwyb-radio-field', 'registered' ) ) {
			$js_assets_info = array(
				'version'      => DRAGWYB_FORM_BUILDER_VERSION,
				'dependencies' => array( 'jquery', 'dragwyb-form-frontend' ),
			);

			wp_register_script(
				'dragwyb-radio-field',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/radio-field.js' ),
				$js_assets_info['dependencies'],
				esc_attr( $js_assets_info['version'] ),
				true
			);
		}
	}

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
				'default' => __( 'Choose Option', 'smart-form-builder-by-dragwyb' ),
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
			'radio_style',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Radio Style', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'outline'    => __( 'Classic Circle', 'smart-form-builder-by-dragwyb' ),
					'solid_fill' => __( 'Solid Accent Circle', 'smart-form-builder-by-dragwyb' ),
					'card'       => __( 'Bordered Box Cards', 'smart-form-builder-by-dragwyb' ),
					'button'     => __( 'Segmented Button Group', 'smart-form-builder-by-dragwyb' ),
					'chip'       => __( 'Selection Pills / Chips', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'outline',
				'label_inline' => true,
			)
		);

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
				'type'       => Controls::REPEATER,
				'label'      => __( 'Options', 'smart-form-builder-by-dragwyb' ),
				'items'      => $repeater->get_settings(),
				'default'    => array(
					array(
						'option_label' => 'Yes',
						'option_value' => 'yes',
					),
					array(
						'option_label' => 'No',
						'option_value' => 'no',
					),
				),
				'item_label' => '{{option_label}}',
			)
		);

		$this->add_control(
			'default_value',
			array(
				'type'  => Controls::TEXT,
				'label' => __( 'Default Value', 'smart-form-builder-by-dragwyb' ),
			)
		);

		// Choose layout: stacked vertically or side-by-side
		$this->add_control(
			'layout',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Layout', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'block'  => __( 'Vertical (List)', 'smart-form-builder-by-dragwyb' ),
					'inline' => __( 'Horizontal (Inline)', 'smart-form-builder-by-dragwyb' ),
				),
				'label_inline' => true,
				'default'      => 'inline',
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
				'label' => __( 'Radio Appearance', 'smart-form-builder-by-dragwyb' ),
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
			'toggle_bg_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-bg-color: {{VALUE}};' ),
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
			'toggle_border_width',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Border Width', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 1,
						'max' => 10,
					),
				),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-border-width: {{VALUE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'toggle_primary_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Active / Checked Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-primary-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'toggle_spacing',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Option Spacing', 'smart-form-builder-by-dragwyb' ),
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

	protected function init(): void {
		$this->type     = 'radio';
		$this->name     = __( 'Radio Button', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'fas fa-dot-circle';
		$this->category = 'standard-fields';
		$this->keywords = array( 'multiple', 'choices', 'options', 'single' );
	}

	protected function render_field() {
		$settings = $this->get_field_settings();

		$id          = $this->get_the_id();
		$field_id    = $this->field_key_exist( $settings, 'field_id', $id );
		$label       = $this->field_key_exist( $settings, 'label', '' );
		$options     = $this->field_key_exist( $settings, 'options_list', array() );
		$layout      = $this->field_key_exist( $settings, 'layout', 'inline' );
		$radio_style = $this->field_key_exist( $settings, 'radio_style', 'outline' );
		$help        = $this->field_key_exist( $settings, 'help_text', '' );
		$classes     = $this->field_key_exist( $settings, 'css_classes', '' );

		$raw_default_value = (string) $this->field_key_exist( $settings, 'default_value', '' );
		$default_value     = sanitize_text_field( $raw_default_value );

		$layout_class = ( $layout === 'inline' ) ? 'dragwyb-inline-options' : '';
		$style_class  = 'dragwyb-radio-style-' . $radio_style;

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

				<div class="dragwyb-options-container <?php echo esc_attr( trim( $layout_class . ' ' . $style_class ) ); ?>">
					<?php
					foreach ( $options as $index => $opt ) :
						$opt_id     = $field_id . '_' . $index;
						$opt        = $this->field_key_exist( $opt, 'attributes', $opt );
						$opt_val    = sanitize_text_field( (string) ( $opt['option_value'] ?? '' ) );
						$opt_lbl    = (string) ( $opt['option_label'] ?? '' );
						$is_checked = ( '' !== $default_value && $opt_val === $default_value );

						$input_attrs = array(
							'type'  => 'radio',
							'id'    => $opt_id,
							'name'  => $field_id,
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
							<span class="dragwyb-radio-label"><?php echo esc_html( $opt_lbl ); ?></span>
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

		if ( empty( $value ) && isset( $field_attr['required'] ) && 'yes' == $field_attr['required'] ) {
			$error_handler->add_error( $field_id, __( 'This field is required', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		if ( empty( $value ) ) {
			return;
		}

		$option_values = array();

		$field_options = isset( $field_attr['options_list'] ) ? $field_attr['options_list'] : array();

		if ( is_array( $field_options ) ) {
			foreach ( $field_options as $field_option ) {
				if ( isset( $field_option['attributes']['option_value'] ) ) {
					$option_values[] = $field_option['attributes']['option_value'];
				}
			}
		}

		if ( ! in_array( $value, $option_values, true ) ) {
			$error_handler->add_error( $field_id, __( 'Invalid option selected', 'smart-form-builder-by-dragwyb' ) );
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
