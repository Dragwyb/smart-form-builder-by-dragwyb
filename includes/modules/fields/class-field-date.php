<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Date extends Field_Base {

	protected function register_scripts() {
		return $this->flatpickr_enabled() ? array( 'dragwyb-date-field' ) : array();
	}

	protected function register_styles() {
		return $this->flatpickr_enabled() ? array( 'dragwyb-flatpickr' ) : array();
	}

	/**
	 * Whether Flatpickr assets should load for this field instance.
	 */
	private function flatpickr_enabled(): bool {
		$settings = $this->get_field_settings();
		// Empty settings = editor bootstrap; load assets there.
		// Flatpickr is used unless Native HTML5 is enabled.
		return empty( $settings ) || $this->field_key_exist( $settings, 'use_native_date', 'no' ) !== 'yes';
	}

	public function __construct() {
		parent::__construct();
		$this->register_date_assets();
	}

	/**
	 * Register Flatpickr and date-field scripts/styles.
	 */
	private function register_date_assets(): void {
		if ( ! wp_script_is( 'dragwyb-flatpickr', 'registered' ) ) {
			wp_register_script(
				'dragwyb-flatpickr',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/flatpickr/flatpickr.js' ),
				array(),
				DRAGWYB_FORM_BUILDER_VERSION,
				true
			);
		}

		if ( ! wp_style_is( 'dragwyb-flatpickr', 'registered' ) ) {
			wp_register_style(
				'dragwyb-flatpickr',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/flatpickr/flatpickr.min.css' ),
				array(),
				DRAGWYB_FORM_BUILDER_VERSION,
				'all'
			);
		}

		if ( ! wp_script_is( 'dragwyb-date-field', 'registered' ) ) {
			wp_register_script(
				'dragwyb-date-field',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/date-field.js' ),
				array( 'jquery', 'dragwyb-form-frontend', 'dragwyb-flatpickr' ),
				DRAGWYB_FORM_BUILDER_VERSION,
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
				'default' => __( 'Select Date', 'smart-form-builder-by-dragwyb' ),
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
				'default' => 'YYYY-MM-DD',
			)
		);

		$this->add_control(
			'use_native_date',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Native HTML5', 'smart-form-builder-by-dragwyb' ),
				'description'  => __( 'Use the browser native date input instead of Flatpickr.', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'min_date',
			array(
				'type'        => Controls::DATE,
				'label'       => __( 'Min Date', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Earliest allowed date.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'max_date',
			array(
				'type'        => Controls::DATE,
				'label'       => __( 'Max Date', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Latest allowed date.', 'smart-form-builder-by-dragwyb' ),
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
		$this->type     = 'date';
		$this->name     = __( 'Date Field', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'far fa-calendar';
		$this->category = 'standard-fields';
		$this->keywords = array( 'calendar', 'day', 'month', 'year' );
	}

	protected function render_field() {
		$settings      = $this->get_field_settings();
		$id            = $this->get_the_id();
		$field_id      = $this->field_key_exist( $settings, 'field_id', uniqid( 'date_' ) );
		$label         = $this->field_key_exist( $settings, 'label', 'Select Date' );
		$placeholder   = $this->field_key_exist( $settings, 'placeholder', 'YYYY-MM-DD' );
		$required      = $this->field_key_exist( $settings, 'required', '' ) === 'yes';
		$classes       = $this->field_key_exist( $settings, 'css_classes', '' );
		$use_native = $this->field_key_exist( $settings, 'use_native_date', 'no' ) === 'yes';
		$min_date   = $this->field_key_exist( $settings, 'min_date', '' );
		$max_date   = $this->field_key_exist( $settings, 'max_date', '' );

		$input_class = 'dragwyb-field-input dragwyb-date-field';
		if ( $use_native ) {
			$input_class .= ' dragwyb-use-native';
		}

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'    => $this->field_wrapper_id( $id ),
				'class' => $this->field_wrapper_class( $classes, $settings ) . ' dragwyb-no-float',
			)
		);

		$input_attrs = array(
			'type'        => 'date',
			'id'          => $field_id,
			'name'        => $field_id,
			'placeholder' => $placeholder,
			'class'       => $input_class,
			'pattern'     => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
		);

		if ( ! empty( $min_date ) ) {
			$input_attrs['min'] = $min_date;
		}

		if ( ! empty( $max_date ) ) {
			$input_attrs['max'] = $max_date;
		}

		$this->add_field_attributes( 'input', $input_attrs );
		?>
		<div <?php $this->render_field_attributes( 'wrapper' ); ?>>
			<?php if ( ! empty( $label ) ) : ?>
				<label for="<?php echo esc_attr( $field_id ); ?>" class="dragwyb-label">
					<?php echo esc_html( $label ); ?>
					<?php if ( $required ) : ?>
						<span class="required">*</span>
					<?php endif; ?>
				</label>
			<?php endif; ?>

			<input 
			<?php
			$this->render_field_attributes( 'input' );
			echo $required ? 'required' : '';
			?>
			/>
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

		if ( ! empty( $value ) ) {
			$date = \DateTime::createFromFormat( 'Y-m-d|', $value );
			if ( ! $date || $date->format( 'Y-m-d' ) !== $value ) {
				$error_handler->add_error( $field_id, __( 'Invalid date format', 'smart-form-builder-by-dragwyb' ) );
				return;
			}

			// Check min date
			if ( isset( $field_attr['min_date'] ) && ! empty( $field_attr['min_date'] ) ) {
				try {
					$min_date = new \DateTime( $field_attr['min_date'] );
					$min_date->setTime( 0, 0, 0 );
					if ( $date < $min_date ) {
						$error_handler->add_error( $field_id, __( 'Value is below minimum', 'smart-form-builder-by-dragwyb' ) );
						return;
					}
				} catch ( \Exception $e ) {
					// Gracefully ignore invalid configuration
				}
			}

			// Check max date
			if ( isset( $field_attr['max_date'] ) && ! empty( $field_attr['max_date'] ) ) {
				try {
					$max_date = new \DateTime( $field_attr['max_date'] );
					$max_date->setTime( 0, 0, 0 );
					if ( $date > $max_date ) {
						$error_handler->add_error( $field_id, __( 'Value exceeds maximum', 'smart-form-builder-by-dragwyb' ) );
					}
				} catch ( \Exception $e ) {
					// Gracefully ignore invalid configuration
				}
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
			$date = \DateTime::createFromFormat( 'Y-m-d|', $value );
			return $date ? $date->format( 'Y-m-d' ) : null;
		}

		return null;
	}
}
