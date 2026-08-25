<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;
use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;
use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;

class Field_Step extends Field_Base {

	private ?Frontend_Render $frontend_instance = null;

	protected function register_scripts() {
		return array( 'dragwyb-form-step-field' );
	}

	public function __construct() {
		parent::__construct();

		if ( ! wp_script_is( 'dragwyb-form-step-field', 'registered' ) ) {
			$js_assets_info = array(
				'version'      => DRAGWYB_FORM_BUILDER_VERSION,
				'dependencies' => array( 'jquery', 'dragwyb-form-frontend' ),
			);

			wp_register_script(
				'dragwyb-form-step-field',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/step-field.js' ),
				$js_assets_info['dependencies'],
				esc_attr( $js_assets_info['version'] ),
				true
			);
		}
	}

	protected function init(): void {
		$this->type              = 'step';
		$this->name              = __( 'Step Break', 'smart-form-builder-by-dragwyb' );
		$this->icon              = 'fas fa-shoe-prints';
		$this->category          = 'advanced-fields';
		$this->keywords          = array( 'step', 'break', 'page', 'wizard' );
		$this->allow_child       = false;
		$this->is_root_container = true;
	}

	protected function header_controls(): array {
		$header_tab = array();
		$tabs       = array(
			self::ContentTab => array(
				'label' => 'Content',
			),
			self::AdvanceTab => array(
				'label' => 'Advance',
			),
		);

		$tabs = apply_filters( 'Dragwyb/Editor/render_controls/header_tabs', $tabs, $this->type );

		$header_tab['header_controls'] = array(
			'type' => 'tabs',
			'tabs' => $tabs,
		);

		return $header_tab;
	}

	protected function layout_id_controls(): void {
		$this->start_section(
			'section_advance_layout',
			array(
				'label' => __( 'Layout', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::AdvanceTab,
			)
		);

			// The Field ID is a unique identifier used for saving data and logic
		$this->add_control(
			'field_id',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Step ID', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Use this ID for custom scripts or logic.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'css_classes',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Custom CSS Classes', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Add custom classes to the wrapper.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->end_section();
	}

	protected function register_field_controls(): void {
		$this->start_section(
			'section_content_general',
			array(
				'label' => __( 'Step Settings', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::ContentTab,
			)
		);

		$this->add_control(
			'step_description',
			array(
				'type' => Controls::RAW_HTML,
				'raw'  => sprintf( __( 'Step field preview different from frontend. In editor show step field placeholder for style and visual previwe verify field on frontend.', 'smart-form-builder-by-dragwyb' ) ),
			)
		);

		$this->add_control(
			'label',
			array(
				'type'  => Controls::TEXT,
				'label' => __( 'Step Title', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'next_button_text',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Next Button Label', 'smart-form-builder-by-dragwyb' ),
				'default' => __( 'Next', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'prev_button_text',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Previous Button Label', 'smart-form-builder-by-dragwyb' ),
				'default' => __( 'Previous', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->end_section();
	}

	protected function render_field() {

		if ( ! isset( $this->frontend_instance ) || null === $this->frontend_instance ) {
			$this->frontend_instance = Frontend_Render::instance();
		}

		$settings            = $this->get_field_settings();
		$id                  = $this->get_the_id();
		$label               = $this->field_key_exist( $settings, 'label', '' );
		$next                = $this->field_key_exist( $settings, 'next_button_text', 'Next' );
		$prev                = $this->field_key_exist( $settings, 'prev_button_text', 'Previous' );
		$classes             = $this->field_key_exist( $settings, 'css_classes', '' );
		$root_conatiners     = $this->field_key_exist( $settings, 'root_containers', '' );
		$form_fields         = $this->field_key_exist( $settings, 'form_fields', array() );
		$is_active_step      = $this->field_key_exist( $settings, 'step_active', false );
		$step_index          = $this->field_key_exist( $settings, 'step_index', 0 );
		$indicator_type      = $this->field_key_exist( $settings, 'indicator_type', 'number' );
		$last_root_container = end( $root_conatiners );

		if ( ! $is_active_step ) {
			$classes .= ! empty( trim( $classes ) ) ? ' dragwyb-step-field_hidden' : 'dragwyb-step-field_hidden';
		}

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'              => 'dragwyb-step-' . $id,
				'class'           => 'dragwyb-step-field ' . $classes,
				'data-step-title' => $label,
				'data-next-label' => $next,
				'data-prev-label' => $prev,
			)
		);
		?>
		<div <?php $this->render_field_attributes( 'wrapper' ); ?>>
		<?php
		$child_filds           = 0;
		$render_submit_buttons = false;
		foreach ( $root_conatiners as $root_container ) :
			if ( ! isset( $form_fields[ $root_container ]['type'] ) || ! isset( $form_fields[ $root_container ]['is_root_container'] ) ) {
				continue;
			}

			if ( 'step' === $form_fields[ $root_container ]['type'] ) {
				break;
			}

			if ( $last_root_container === $root_container ) {
				$render_submit_buttons = true;
				continue;
			}

			$this->render_child_fields( $form_fields[ $root_container ] );
			++$child_filds;
			endforeach;
		?>
		<div class="dragwyb-step-navigation">
		<?php
		if ( ! $is_active_step ) : // Only show previous button for steps after the first one.
			?>
				<div class="dragwyb-step-prev">
					<button type="button" class="dragwyb-button dragwyb-button-prev" data-step-id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $prev ); ?></button>
				</div>
				<?php
				endif;
		if ( $render_submit_buttons ) :
			$this->render_submit_button( $form_fields[ $last_root_container ] );
			else :
				?>
			<div class="dragwyb-step-next">
				<button type="button" class="dragwyb-button dragwyb-button-next" data-step-id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $next ); ?></button>
			</div>
		<?php endif; ?>
		</div>
		</div>
				<?php
				if ( 'progress' === $indicator_type ) {
					return '';
				}

				// Step Indicator item html.
				$step_indicator_html = '';

				if ( ! $is_active_step ) {
					$step_indicator_html .= '<div class="dragwyb-step-divider"></div>';
				}

				$step_indicator_html .= '<div class="dragwyb-step-item' . ( $is_active_step ? ' active' : '' ) . '" data-step="' . esc_attr( $id ) . '"><div class="dragwyb-step-dot"><div class="dragwyb-step-number">' . absint( $step_index ) . '</div></div>';
				if ( ! empty( $label ) && 'numbers' === $indicator_type ) {
					$step_indicator_html .= '<div class="dragwyb-step-title">' . esc_html( $label ) . '</div>';
				}
				$step_indicator_html .= '</div>';

				return $step_indicator_html;
	}

	public function render_child_fields( $row_field ) {
		if ( ! isset( $row_field['_id'] ) || ! isset( $row_field['type'] ) || empty( $row_field['_id'] ) || empty( $row_field['type'] ) ) {
			return;
		}

		$type         = $row_field['type'];
		$field_module = $this->get_module( $type );

		if ( ! $field_module instanceof Field_Base ) {
			return;
		}

		$field_module->set_the_id( sanitize_text_field( $row_field['_id'] ) );
		$field_module->set_frontend_handler( $this->frontend_instance );

		$field_data['attributes'] = array();

		if ( isset( $row_field['children'] ) ) {
			$field_data['attributes'] = array_merge( $field_data['attributes'], array( 'children' => $row_field['children'] ) );
		}

		if ( isset( $row_field['attributes'] ) && ! empty( $row_field['attributes'] ) ) {
			$attributes = $row_field['attributes'];
			$this->attributes_loop( $attributes, $type, $field_data, $field_module );
			$field_module->set_field_settings( $field_data['attributes'] );
		} else {
			$field_module->set_field_settings( $field_data['attributes'] );
		}

		$field_module->render();
	}

	private function render_submit_button( $btn_root ) {
		if ( ! isset( $btn_root['_id'] ) || ! isset( $btn_root['type'] ) || empty( $btn_root['_id'] ) || empty( $btn_root['type'] ) ) {
			return;
		}

		$type               = $btn_root['type'];
		$field_module_cache = $this->get_module( $type );

		if ( ! $field_module_cache instanceof Field_Base ) {
			return;
		}

		$child_fields = $this->field_key_exist( $btn_root, 'children', array() );

		foreach ( $child_fields as $child_field ) {
			$field = $this->get_field_data( $child_field );

			$this->render_child_fields( $field );
		}
	}

	private function attributes_loop( $attributes, $type, &$field_data, $field_module_cache ): void {

		if ( ! $field_module_cache instanceof Field_Base ) {
			return;
		}

		foreach ( $attributes as $attribute => $value ) {
			if ( $field_control = $field_module_cache->get_control( $attribute ) ) {
				if ( isset( $field_control['type'] ) ) {
					$control_type = $field_control['type'];

					$control_obj = $this->get_control_handler( $control_type );

					if ( ! $control_obj || ! $control_obj instanceof Control_Base ) {
						continue;
					}

					$control_obj = $control_obj::newInstance();

					$control_obj->set_value( $value, $attribute, $field_control );
					$filtered_value = $control_obj->get_value();

					if ( isset( $filtered_value ) ) {
						$field_data['attributes'][ $attribute ] = $filtered_value;
					}
				}
			}
		}
	}

	public function validate( $value, $field_id, $form_config, Form_Submission_Handler $error_handler ): void {
		// Step field does not submit data.
	}

			/**
			 * Sanitize the field value.
			 *
			 * @param mixed $value The value to sanitize.
			 * @return mixed Sanitized value.
			 */
	public function sanitize( $value = null ) {
		return null;
	}
}
