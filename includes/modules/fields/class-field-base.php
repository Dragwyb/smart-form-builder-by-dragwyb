<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Includes\Controls\Register_Controls_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Categories\Categories;
use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;
use Dragwyb\Form_Builder\Includes\Repeater\Repeater;

abstract class Field_Base extends Register_Controls_Base {

	protected string $type;
	protected string $name;
	protected string $icon;
	protected bool $allow_child       = false;
	protected bool $is_root_container = false;
	protected string $category        = Categories::STANDARD_FIELDS;
	protected array $settings;
	protected Frontend_Render $frontend_handler;
	protected array $keywords        = array();
	private ?array $display_settings = array();
	private $field_id                = 0;
	private array $field_attributes  = array();

	const ContentTab = 'content_tab';
	const StyleTab   = 'style_tab';
	const AdvanceTab = 'advance_tab';

	protected function register_scripts() {
		return array();
	}

	protected function register_styles() {
		return array();
	}

	protected function editor_scripts() {
		return array();
	}

	protected function editor_styles() {
		return array();
	}

	abstract protected function register_field_controls();

	final protected function register_controls(): void {
		$this->layout_id_controls();
		$this->register_field_controls();
	}

	public function __construct() {
		$this->init();
		parent::__construct();

		// Add filter for sanitizing field values.
		add_filter( 'Dragwyb/Field/Value/Sanitize/' . $this->get_type(), array( $this, 'sanitize' ), 10, 2 );
		// Add filter for validating field values.
		add_action( 'Dragwyb/Field/Value/Validate/' . $this->get_type(), array( $this, 'validate' ), 10, 4 );
	}

	abstract protected function init(): void;

	public function enqueue_assets() {
		$scripts = $this->register_scripts();
		$styles  = $this->register_styles();

		if ( is_array( $scripts ) ) {
			foreach ( $scripts as $script ) {
				if ( ! wp_script_is( $script, 'enqueued' ) ) {
					wp_enqueue_script( $script );
				}
			}
		}

		if ( is_array( $styles ) ) {
			foreach ( $styles as $style ) {
				if ( ! wp_style_is( $style, 'enqueued' ) ) {
					wp_enqueue_style( $style );
				}
			}
		}
	}

	public function enqueue_editor_assets() {
		$scripts = $this->editor_scripts();
		$styles  = $this->editor_styles();

		if ( is_array( $scripts ) ) {
			foreach ( $scripts as $script ) {
				if ( ! wp_script_is( $script, 'enqueued' ) ) {
					wp_enqueue_script( $script );
				}
			}
		}

		if ( is_array( $styles ) ) {
			foreach ( $styles as $style ) {
				if ( ! wp_style_is( $style, 'enqueued' ) ) {
					wp_enqueue_style( $style );
				}
			}
		}
	}

	public function get_type(): string {
		return sanitize_text_field( $this->type );
	}

	public function get_name(): string {
		return sanitize_text_field( $this->name );
	}

	public function get_icon(): string {
		return sanitize_text_field( $this->icon );
	}

	public function get_category(): string {
		return sanitize_text_field( $this->category );
	}

	public function is_root_container(): bool {
		return (bool) $this->is_root_container;
	}

	public function get_allow_child(): bool {
		return (bool) $this->allow_child;
	}

	/**
	 * Get the keywords for this field.
	 *
	 * @return array|false Array of keywords or false if not set.
	 */
	public function get_keywords() {
		if ( ! empty( $this->keywords ) && is_array( $this->keywords ) ) {
			return array_map( 'sanitize_text_field', $this->keywords );
		}

		return false;
	}


	public function set_the_id( $id = '' ): void {
		$field_id       = sanitize_text_field( $id );
		$this->field_id = $field_id;
	}

	public function get_the_id(): string {
		return $this->field_id;
	}

	protected function tab_condition( &$conditions, $data ): array {
		if ( ( isset( $data['tab'] ) && ! empty( $data['tab'] ) ) ) {
			$conditions['header_controls'] = $data['tab'];
		} elseif ( ! isset( $data['tab'] ) || empty( $data['tab'] ) ) {
			$conditions['header_controls'] = self::ContentTab;
		}

		return $conditions;
	}

	public function set_field_settings( array $setting ) {
		$this->display_settings = $setting;
		$this->test_settings    = $setting;
	}

	protected function get_field_settings(): array {
		return $this->display_settings;
	}

	public function set_frontend_handler( Frontend_Render $frontend_render ): void {
		$this->frontend_handler = $frontend_render;
	}

	/**
	 * Get the form settings.
	 *
	 * @return array|null Array of settings or null if not set.
	 */
	protected function get_toolbars_values( string $type = '' ) {
		if ( isset( $type ) && ! empty( $type ) ) {
			return $this->frontend_handler->get_toolbars_values( $type );
		}

		return array();
	}

	protected function get_field_data( string $field_id ): array {
		if ( isset( $field_id ) && ! empty( $field_id ) ) {
			return $this->frontend_handler->get_field_data( $field_id );
		}

		return array();
	}

	protected function get_module( string $type ) {
		if ( isset( $type ) && ! empty( $type ) ) {
			return $this->frontend_handler->get_module( $type );
		}

		return array();
	}

	protected function get_control_handler( string $type ) {
		if ( isset( $type ) && ! empty( $type ) ) {
			return $this->frontend_handler->get_control( $type );
		}

		return array();
	}

	abstract protected function render_field();

	/**
	 * Validate the field value.
	 * Do not return the value without sanitizing or validating.
	 *
	 * @param string|array            $value The value to validate.
	 * @param string                  $field_id The ID of the field.
	 * @param array                   $settings The settings of the field.
	 * @param Form_Submission_Handler $error_handler The Error handler.
	 * @return void
	 */
	abstract public function validate( $value, $field_id, $settings, Form_Submission_Handler $error_handler ): void;

	/**
	 * Sanitize the field value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @return mixed Sanitized value.
	 */
	abstract public function sanitize( $value = null );

	public function render() {
		$this->field_attributes = array();
		$this->enqueue_assets();
		$this->render_field();
		$this->localize_condition_data();
	}

	/**
	 * Added render attributes
	 *
	 * @param string $key type unique key
	 * @param array  $attributes The attributes array
	 * @return void Do not return any value
	 */
	protected function add_field_attributes( string $key, array $attributes ): void {
		$safe_key  = sanitize_key( $key ); // Sanitize for use in the hook name
		$safe_type = sanitize_text_field( $this->type );

		// Filter hook to modify the attributes data before adding/storing.
		$attributes = apply_filters( 'Dragwyb/Field/Before_Add_Attributes', $attributes, $key, $this );
		$attributes = apply_filters( "Dragwyb/Field/Before_Add_Attributes/{$safe_type}/{$safe_key}", $attributes, $this );

		if ( ! isset( $this->field_attributes[ $key ] ) ) {
			$this->field_attributes[ $key ] = array();
		}

		foreach ( $attributes as $attr_key => $attr_value ) {
			if ( ! isset( $this->field_attributes[ $key ][ $attr_key ] ) ) {
				$this->field_attributes[ $key ][ $attr_key ] = array();
			}

			if ( is_array( $attr_value ) ) {
				$this->field_attributes[ $key ][ $attr_key ] = array_merge( $this->field_attributes[ $key ][ $attr_key ], $attr_value );
			} else {
				$this->field_attributes[ $key ][ $attr_key ][] = $attr_value;
			}
		}
	}

	/**
	 * Render HTML attributes for a specific key.
	 *
	 * @param string $key The key whose attributes to render.
	 * @return void
	 */
	protected function render_field_attributes( string $key ): void {
		if ( empty( $this->field_attributes[ $key ] ) ) {
			return;
		}

		$rendered_attributes = array();

		foreach ( $this->field_attributes[ $key ] as $attribute_key => $attribute_values ) {
			$attribute_key = sanitize_key( $attribute_key );

			// SECURITY: Block all inline event handlers (onclick, onmouseover, etc.).
			if ( 0 === strpos( $attribute_key, 'on' ) ) {
				continue;
			}

			if ( is_array( $attribute_values ) ) {
				$attribute_values = implode( ' ', $attribute_values );
			}

			// SECURITY: Use esc_url for URI attributes, otherwise use esc_attr.
			$uri_attributes = array( 'href', 'src', 'action', 'poster' );

			if ( in_array( $attribute_key, $uri_attributes, true ) ) {
				$safe_value = esc_url( (string) $attribute_values );
			} else {
				$safe_value = esc_attr( (string) $attribute_values );
			}

			$rendered_attributes[] = sprintf( '%1$s="%2$s"', esc_attr( $attribute_key ), $safe_value );
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- This is a safe escape as all attributes are properly escaped.
		echo implode( ' ', $rendered_attributes );
	}

	/**
	 * Render the field label.
	 *
	 * @param string $field_id The ID of the field.
	 * @param string $label The label text.
	 * @param bool   $required Whether the field is required.
	 * @param array  $settings The field settings.
	 * @return void
	 */
	protected function render_field_label( string $field_id = '', string $label, bool $required, array $settings ) {
		if ( empty( $label ) ) {
			return;
		}

		$field_icon = $this->field_key_exist( $settings, 'label_icon', array() );

		$icon_to_render = ! empty( $field_icon['icon'] ) ? $field_icon : '';

		$field_label_class = 'dragwyb-field-label';

		if ( ! empty( $icon_to_render['icon'] ) ) {
			$field_label_class .= ' dragwyb-field-label-icon';
		}
		?>
		<label for="<?php echo esc_attr( $field_id ); ?>" class="<?php echo esc_attr( $field_label_class ); ?> ">
			<?php
			if ( ! empty( $icon_to_render['icon'] ) ) {
				\Dragwyb\Form_Builder\Includes\Controls\Icons\Icons_Manager::render_icon( $icon_to_render, array( 'class' => 'dragwyb-label-icon' ) );
			}

			if ( ! empty( $icon_to_render['icon'] ) ) {
				echo '<span class="dragwyb-field-label-text">';
			}

			echo esc_html( $label );

			if ( $required ) :
				?>
				<span class="dragwyb-required">*</span>
				<?php
				endif;
			if ( ! empty( $icon_to_render['icon'] ) ) {
				echo '</span>';
			}
			?>
		</label>
		<?php
	}

	protected function field_wrapper_id( string $id = '' ) {
		$wrapper_id = 'dragwyb-field-wrapper-' . esc_attr( $id );

		$wrapper_id = apply_filters( 'Dragwyb/Field/Wrapper_ID/' . sanitize_text_field( $this->type ), $wrapper_id, $id );
		return $wrapper_id;
	}

	protected function field_wrapper_class( string $classes = '', array $settings = array() ) {
		$wrapper_class = 'dragwyb-field-wrapper dragwyb-' . esc_attr( $this->type ) . '-field';

		$toolbar_settings = $this->get_toolbars_values( 'style' );

		$global_icon_position = $this->field_key_exist( $toolbar_settings, 'label_icon_position', 'before' );

		$field_icon = $this->field_key_exist( $settings, 'label_icon', array() );

		$icon_to_render = ! empty( $field_icon['icon'] ) ? $field_icon : '';

		if ( ! empty( $icon_to_render['icon'] ) ) {
			$wrapper_class .= ' dragwyb-field-label-icon-' . esc_attr( $global_icon_position );
		}

		if ( $classes && ! empty( $classes ) ) {
			$wrapper_class .= ' ' . esc_attr( $classes );
		}

		$wrapper_class = apply_filters( 'Dragwyb/Field/Wrapper_Class/' . sanitize_text_field( $this->type ), $wrapper_class, $classes );

		return $wrapper_class;
	}

	protected function field_key_exist( array $array, string $key, $default = false ) {
		return $this->field_array_key_exist( $array, $key, $default );
	}

	private function field_array_key_exist( array $array, string $key, $default = false ) {
		if ( isset( $array[ $key ] ) ) {
			return $array[ $key ];
		}

		return $default;
	}

	protected function layout_id_controls(): void {
		$this->start_section(
			'section_advance_layout',
			array(
				'label' => __( 'Layout & ID', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::AdvanceTab,
			)
		);

		// The Field ID is a unique identifier used for saving data and logic
		$this->add_control(
			'field_id',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Field ID', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Use this ID for custom scripts or logic.', 'smart-form-builder-by-dragwyb' ),
				'dynamic'     => array( 'active' => false ),
			)
		);

		// column span
		$this->add_responsive_control(
			'column_span',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Grid Column Span', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 1,
						'max' => 12,
					),
				),
				'default'   => array(
					'size' => 1,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-column-span: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'field_width',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Width', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px'  => array(
						'min' => 1,
						'max' => 1000,
					),
					'%'   => array(
						'min' => 1,
						'max' => 100,
					),
					'em'  => array(
						'min' => 1,
						'max' => 100,
					),
					'rem' => array(
						'min' => 1,
						'max' => 100,
					),
				),
				'units'     => array( 'px', '%', 'em', 'rem' ),
				'default'   => array(
					'size' => 100,
					'unit' => '%',
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-field-width: {{VALUE}}{{UNIT}};',
				),
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

		$this->start_section(
			'section_advance_logic',
			array(
				'label' => __( 'Conditional Logic', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::AdvanceTab,
			)
		);

		$this->add_control(
			'enable_logic',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Enable Logic', 'smart-form-builder-by-dragwyb' ),
				'default'      => 'no',
				'return_value' => 'yes',
			)
		);

		$conditional_repeater = new Repeater();

		$conditional_repeater->add_control(
			'condition_field_id',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Condition Field ID', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Enter the ID of the field to check.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$conditional_repeater->add_control(
			'condition_value',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Value', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'The value to compare against.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$conditional_repeater->add_control(
			'condition_operator',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Operator', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'equal'        => __( 'Equals', 'smart-form-builder-by-dragwyb' ),
					'not_equal'    => __( 'Not Equals', 'smart-form-builder-by-dragwyb' ),
					'contains'     => __( 'Contains', 'smart-form-builder-by-dragwyb' ),
					'not_contains' => __( 'Not Contains', 'smart-form-builder-by-dragwyb' ),
					'greater_than' => __( 'Greater Than', 'smart-form-builder-by-dragwyb' ),
					'less_than'    => __( 'Less Than', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'equal',
				'label_inline' => true,
			)
		);

		$conditional_repeater->add_control(
			'condition_action',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Action', 'smart-form-builder-by-dragwyb' ),
				'default'      => 'yes',
				'return_value' => 'yes',
				'on_label'     => __( 'Show', 'smart-form-builder-by-dragwyb' ),
				'off_label'    => __( 'Hide', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'logic_conditions',
			array(
				'type'       => Controls::REPEATER,
				'label'      => __( 'Conditions', 'smart-form-builder-by-dragwyb' ),
				'items'      => $conditional_repeater->get_settings(),
				'default'    => array(
					array(
						'condition_field_id' => '',
						'condition_value'    => '',
						'condition_operator' => 'equal',
						'condition_action'   => 'yes',
					),
				),
				'add_item'   => __( 'Add Condition', 'smart-form-builder-by-dragwyb' ),
				'item_label' => 'condition_field_id',
				'conditions' => array(
					'enable_logic' => 'yes',
				),
			)
		);

		$this->end_section();
	}

	/**
	 * Localize the condition data
	 * this function is final and can not be overridden
	 *
	 * @return void
	 */
	final public function localize_condition_data(): void {
		$settings = $this->get_field_settings();

		if ( ! isset( $settings['enable_logic'] ) || 'yes' !== $settings['enable_logic'] ) {
			return;
		}

		if ( ! isset( $settings['field_id'] ) || empty( $settings['field_id'] ) ) {
			return;
		}

		if ( ! isset( $settings['logic_conditions'] ) || empty( $settings['logic_conditions'] ) ) {
			return;
		}

		$condition_data   = array();
		$current_form_id  = (int) $this->get_form_id();
		$logic_conditions = $settings['logic_conditions'];

		foreach ( $logic_conditions as $logic_condition ) {
			$logic_attributes = $this->field_key_exist( $logic_condition, 'attributes', array() );

			if ( empty( $logic_attributes ) ) {
				continue;
			}

			if ( empty( $logic_attributes['condition_field_id'] ) ) {
				continue;
			}

			$condition_data[] = array(
				'field_id' => $logic_attributes['condition_field_id'],
				'value'    => $logic_attributes['condition_value'],
				'operator' => $logic_attributes['condition_operator'],
				'action'   => $logic_attributes['condition_action'],
			);
		}

		add_filter(
			'Dragwyb/Frontend/Form/Localize_Settings',
			function ( $localize_data, $form_id ) use ( $settings, $condition_data, $current_form_id ) {
				if ( $form_id !== $current_form_id ) {
					return $localize_data;
				}

				$field_id = $settings['field_id'];

				if ( ! isset( $localize_data['conditions'] ) ) {
					$localize_data['conditions'] = array();
				}

				if ( isset( $localize_data['conditions'][ $field_id ] ) ) {
					return $localize_data;
				}

				$localize_data['conditions'][ $field_id ] = $condition_data;

				return $localize_data;
			},
			10,
			2
		);
	}

	protected function header_controls(): array {
		$header_tab = array();
		$tabs       = array(
			self::ContentTab => array(
				'label' => 'Content',
			),
			self::StyleTab   => array(
				'label' => 'Style',
			),
			self::AdvanceTab => array(
				'label' => 'Advance',
			),
		);

		$tabs = apply_filters( 'Dragwyb/Editor/render_controls/header_tabs', $tabs );

		$header_tab['header_controls'] = array(
			'type' => 'tabs',
			'tabs' => $tabs,
		);

		return $header_tab;
	}
}
