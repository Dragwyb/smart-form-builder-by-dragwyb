<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls;

use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;
use Dragwyb\Form_Builder\Includes\Controls\Group\Group_Control_Base;

abstract class Register_Controls_Base {

	private ?string $current_section      = null;
	private ?string $current_tabs         = null;
	private ?string $current_tab          = null;
	private ?array $settings_arr          = array();
	private ?array $current_section_stack = array();
	private ?array $current_tabs_stack    = array();
	private ?array $current_control_stack = array();
	private ?array $current_popover       = array();
	private ?object $control_base;
	private static ?int $form_id = 0;

	public function __construct() {
		$this->control_base = Controls::instance();
	}

	final public function set_form_id( int $id = 0 ): void {
		$form_id       = absint( sanitize_text_field( $id ) );
		self::$form_id = $form_id;
	}

	protected function get_form_id(): int {
		return self::$form_id;
	}

	protected function tab_condition( &$conditions, $data ) {
		return $conditions;
	}

	protected function header_controls(): array {
		return array();
	}

	final public function get_settings(): array {
		return $this->settings_arr;
	}

	public static function validate_id( $id, $type ) {
		if ( ! is_string( $id ) || ! preg_match( '/^[A-Za-z0-9_]+$/', $id ) ) {
			// translators: %s is the type of the control
			throw new \Exception( sprintf( esc_html__( '%s ID must only contain letters, numbers, and underscores.', 'smart-form-builder-by-dragwyb' ), esc_html( $type ) ) );

			return false;
		}

		return sanitize_text_field( $id );
	}

	final protected function start_section( string $id = '', array $data = array() ): void {
		if ( ! $id = self::validate_id( $id, 'Section' ) ) {
			return;
		}

		if ( $this->current_section !== null ) {
			throw new \Exception( esc_html__( 'A section is already started.', 'smart-form-builder-by-dragwyb' ) );
		}

		if ( isset( $this->settings_arr[ $id ] ) ) {
			throw new \Exception( esc_html__( 'Do not use duplicate section ID use unique Id.', 'smart-form-builder-by-dragwyb' ) );
		}

		do_action( 'Dragwyb/Editor/before_section_start/' . sanitize_text_field( $id ), $this, $this->current_section );

		$this->current_section = $id; // Assuming type is the section identifier

		$conditions = isset( $data['conditions'] ) ? $data['conditions'] : array();

		$conditions = $this->tab_condition( $conditions, $data );

		do_action( 'Dragwyb/Editor/after_section_start/' . sanitize_text_field( $id ), $this, $this->current_section );

		$this->settings_arr[ $this->current_section ] = $this->controller_settings(
			array_merge(
				$data,
				array(
					'type'       => 'section',
					'conditions' => $conditions,
				)
			)
		);
	}

	final protected function end_section(): void {
		if ( $this->current_section === null ) {
			throw new \Exception( esc_html__( 'No section is currently open.', 'smart-form-builder-by-dragwyb' ) );
		}

		do_action( 'Dragwyb/Editor/before_section_end/' . sanitize_text_field( $this->current_section ), $this, $this->current_section, $this->settings_arr[ $this->current_section ] );

		$this->settings_arr = array_merge( $this->settings_arr, $this->current_section_stack, $this->current_control_stack );

		do_action( 'Dragwyb/Editor/after_section_end/' . sanitize_text_field( $this->current_section ), $this, $this->current_section );

		$this->current_section = null;

		$this->current_control_stack = array();
		$this->current_section_stack = array();
	}

	final protected function start_tabs( string $id = '', array $data = array() ): void {
		if ( ! $id = self::validate_id( $id, 'Tabs' ) ) {
			return;
		}

		if ( $this->current_section === null ) {
			throw new \Exception( esc_html__( 'No section is currently open.', 'smart-form-builder-by-dragwyb' ) );
		}

		if ( $this->current_tabs !== null ) {
			throw new \Exception( esc_html__( 'Tabs are already started.', 'smart-form-builder-by-dragwyb' ) );
		}

		if ( $this->current_section_stack && isset( $this->current_section_stack[ $id ] ) ) {
			throw new \Exception( esc_html__( 'Do not use duplicate tabs ID use unique Id.', 'smart-form-builder-by-dragwyb' ) );
		}

		$this->current_tabs = $id; // Assuming type is the tabs identifier

		$conditions            = isset( $data['conditions'] ) ? $data['conditions'] : array();
		$conditions['section'] = $this->current_section;

		if ( isset( $this->settings_arr[ $this->current_section ]['conditions'] ) ) {
			$conditions = array_merge( $conditions, $this->settings_arr[ $this->current_section ]['conditions'] );
		}

		$this->current_control_stack[ $this->current_tabs ] = $this->controller_settings(
			array_merge(
				$data,
				array(
					'type'       => 'tabs',
					'conditions' => $conditions,
				)
			)
		);
		$this->current_section_stack[ $this->current_tabs ] = array(
			'type'       => 'tabs',
			'conditions' => $conditions,
		);
	}

	final protected function end_tabs(): void {
		if ( $this->current_tabs === null ) {
			throw new \Exception( esc_html__( 'No tabs are currently open.', 'smart-form-builder-by-dragwyb' ) );
		}

		$this->current_control_stack[ $this->current_tabs ]['tabs'] = $this->current_tabs_stack;

		$this->current_tabs_stack = array();
		unset( $this->current_section_stack[ $this->current_tabs ] );
		$this->current_tabs = null;
	}

	final protected function start_tab( string $id = '', array $data = array() ): void {
		if ( ! $id = self::validate_id( $id, 'Tab' ) ) {
			return;
		}

		if ( $this->current_section === null ) {
			throw new \Exception( esc_html__( 'No section is currently open.', 'smart-form-builder-by-dragwyb' ) );
		}

		if ( $this->current_tabs === null ) {
			throw new \Exception( esc_html__( 'Tabs must be started before a tab can be opened.', 'smart-form-builder-by-dragwyb' ) );
		}

		if ( $this->current_tabs_stack && isset( $this->current_tabs_stack[ $id ] ) ) {
			throw new \Exception( esc_html__( 'Do not use duplicate tab ID use unique Id.', 'smart-form-builder-by-dragwyb' ) );
		}

		if ( $this->current_tab !== null ) {
			throw new \Exception( esc_html__( 'Tab are already started.', 'smart-form-builder-by-dragwyb' ) );
		}
		$this->current_tab = $id; // Assuming type is the tabs identifier

		$this->current_tabs_stack[ $id ] = $this->controller_settings( array_merge( array( 'type' => 'tab' ), $data ) );
	}

	final protected function end_tab(): void {
		if ( $this->current_tab === null ) {
			throw new \Exception( esc_html__( 'No tab are currently open.', 'smart-form-builder-by-dragwyb' ) );
		}

		$this->current_tab = null;
	}

	final protected function start_popover( array $data = array() ): void {
		if ( $this->current_section === null ) {
			throw new \Exception( esc_html__( 'No section is currently open.', 'smart-form-builder-by-dragwyb' ) );
		}

		if ( isset( $this->current_popover['initialize'] ) ) {
			throw new \Exception( esc_html__( 'Popover are already started.', 'smart-form-builder-by-dragwyb' ) );
		}

		$this->current_popover['initialize'] = false;

		if ( isset( $data['title'] ) ) {
			$this->current_popover['title'] = sanitize_text_field( $data['title'] );
		}
	}

	final protected function end_popover(): void {
		if ( ! isset( $this->current_popover['initialize'] ) ) {
			throw new \Exception( esc_html__( 'No popover are currently open.', 'smart-form-builder-by-dragwyb' ) );
		}

		$last_control = $this->get_last_control();

		if ( isset( $this->current_control_stack[ $last_control['key'] ] ) ) {
			$this->current_control_stack[ $last_control['key'] ]['popover'] = array( 'end' => true );

			if ( isset( $this->current_popover['title'] ) ) {
				$this->current_control_stack[ $last_control['key'] ]['popover']['title'] = $this->current_popover['title'];
			}
		}

		$this->current_popover = array();
	}

	final protected function add_control( string $id = '', array $data = array() ): void {
		if ( ! $id = self::validate_id( $id, 'Control' ) ) {
			return;
		}

		if ( $this->current_section === null ) {
			throw new \Exception( esc_html__( 'No section is currently open to add controls.', 'smart-form-builder-by-dragwyb' ) );
		}

		if ( isset( $this->settings_arr[ $id ] ) || isset( $this->current_control_stack[ $id ] ) ) {
			// translators: %s is the id of the control
			throw new \Exception( sprintf( esc_html__( 'Do not use duplicate %s ID use unique Id.', 'smart-form-builder-by-dragwyb' ), esc_html( $id ) ) );
		}

		do_action( 'Dragwyb/Editor/before_add_control/' . sanitize_text_field( $id ), $this, $id, $data, $this->current_section );

		$conditions = isset( $data['conditions'] ) ? $data['conditions'] : array();

		if ( isset( $this->current_section_stack[ $this->current_tabs ]['conditions'] ) ) {
			$conditions                        = array_merge( $this->current_section_stack[ $this->current_tabs ]['conditions'], $conditions );
			$conditions[ $this->current_tabs ] = $this->current_tab;
		} elseif ( isset( $this->settings_arr[ $this->current_section ]['conditions'] ) ) {
			$conditions            = array_merge( $conditions, $this->settings_arr[ $this->current_section ]['conditions'] );
			$conditions['section'] = $this->current_section;
		} else {
			$conditions = array_merge( $conditions, array( 'section' => $this->current_section ) );
		}

		$control_data = $this->controller_settings( array_merge( $data, array( 'conditions' => $conditions ) ) );

		if ( isset( $this->current_popover['initialize'] ) && ! isset( $control_data['popover'] ) ) {
			$control_data['popover'] = array();

			if ( false === $this->current_popover['initialize'] ) {
				$control_data['popover']['start']    = true;
				$this->current_popover['initialize'] = true;
			}
		}

		$this->current_control_stack[ $id ] = $control_data;

		do_action( 'Dragwyb/Editor/after_add_control/' . sanitize_text_field( $id ), $this, $id, $data, $this->current_section );
	}

	final protected function add_group_control( string $id = '', array $data = array() ): void {
		if ( $this->current_section === null ) {
			throw new \Exception( esc_html__( 'No section is currently open to add controls.', 'smart-form-builder-by-dragwyb' ) );
		}

		$id = sanitize_text_field( $id );

		if ( isset( $this->settings_arr[ $id ] ) || isset( $this->current_control_stack[ $id ] ) ) {
			throw new \Exception( esc_html__( 'Do not use duplicate control ID use unique Id.', 'smart-form-builder-by-dragwyb' ) );
		}

		$conditions = isset( $data['conditions'] ) ? $data['conditions'] : array();

		if ( isset( $this->current_section_stack[ $this->current_tabs ]['conditions'] ) ) {
			$conditions                        = array_merge( $this->current_section_stack[ $this->current_tabs ]['conditions'], $conditions );
			$conditions[ $this->current_tabs ] = $this->current_tab;
		} elseif ( isset( $this->settings_arr[ $this->current_section ]['conditions'] ) ) {
			$conditions = array_merge( $conditions, $this->settings_arr[ $this->current_section ]['conditions'] );
		} else {
			$conditions = array_merge( $conditions, array( 'section' => $this->current_section ) );
		}

		$control_data = $this->group_controller_settings( $id, array_merge( $data, array( 'conditions' => $conditions ) ) );

		$this->add_control(
			$id . '_popover_toggle',
			array(
				'type'  => Controls::POPOVER_TOGGLE,
				'label' => isset( $control_data['name'] ) ? sanitize_text_field( $control_data['name'] ) : __( 'Popover Toggle', 'smart-form-builder-by-dragwyb' ),
				'icon'  => isset( $control_data['icon'] ) ? $control_data['icon'] : 'fas fa-pen',
			)
		);

		$this->start_popover(
			array(
				'title' => $control_data['name'],
			)
		);

		foreach ( $control_data['controls'] as $id => $data ) {
			if ( isset( $id ) && ! empty( $id ) && is_array( $data ) && count( $data ) > 1 ) {
				$this->add_control( $id, $data );
			}
		}

		$this->end_popover();
	}

	final function add_responsive_control( string $id = '', array $data = array() ): void {
		$responsive_types = array( 'desktop', 'tablet', 'mobile' );

		foreach ( $responsive_types as $index => $responsive_type ) {

			if ( 'desktop' !== $responsive_type && isset( $data[ $responsive_type . '_default' ] ) ) {
				$data['default'] = $data[ $responsive_type . '_default' ];
			} elseif ( 'desktop' !== $responsive_type ) {
				unset( $data['default'] );
			}

			$data['responsive_type']    = $responsive_type;
			$data['responsive_control'] = true;

			$control_id = $id;

			if ( 'desktop' !== $responsive_type ) {
				$control_id .= '_' . $responsive_type;
			}

			$this->add_control( $control_id, $data );
		}
	}

	private function get_last_control(): array {
		$keys     = array_keys( $this->current_control_stack );
		$last_key = end( $keys );

		return array(
			'key'   => $last_key,
			'value' => $this->current_control_stack[ $last_key ],
		);
	}

	private function controller_settings( array $data ): array {
		$controls_class      = Controls::class;
		$controls_base_class = Control_Base::class;

		if ( ! isset( $data['type'] ) || ! ( $this->control_base instanceof $controls_class ) ) {
			return array();
		}

		$type = sanitize_text_field( $data['type'] );

		$control_object = $this->control_base->get_control( $type );

		if ( ! $control_object || ! ( $control_object instanceof $controls_base_class ) ) {
			return array();
		}

		$control_object = $control_object->newInstance();

		$control_object->set_settings( $data );

		$value = $control_object->get_settings();

		if ( defined( 'DRAGWYB_EDITOR' ) && true === DRAGWYB_EDITOR && isset( $data['type'] ) && isset( $data['selectors'] ) && count( $data['selectors'] ) > 0 ) {
			$selector_placeholders           = $control_object->get_style_placeholders();
			$value['selectors_placeholders'] = $selector_placeholders;
		}

		return $value;
	}

	private function group_controller_settings( string $id = '', array $data = array() ): array {
		$id                       = sanitize_text_field( $id );
		$controls_class           = Controls::class;
		$group_control_base_class = Group_Control_Base::class;

		if ( ! isset( $data['type'] ) || ! ( $this->control_base instanceof $controls_class ) ) {
			return array();
		}

		$type = sanitize_text_field( $data['type'] );

		$control_object = $this->control_base->get_group_control( $type );

		if ( ! $control_object || ! ( $control_object instanceof $group_control_base_class ) ) {
			return array();
		}

		$control_object = $control_object->newInstance();

		$control_object->register_controls( $id, $data );
		$name = isset( $data['label'] ) ? $data['label'] : $control_object->get_name();
		$icon = isset( $data['icon'] ) ? $data['icon'] : $control_object->get_icon();
		$data = $control_object->get_settings();

		return array(
			'controls' => $data,
			'name'     => sanitize_text_field( $name ),
			'icon'     => $icon,
		);
	}

	abstract protected function register_controls(): void;

	final public function render_controls() {
		$this->settings_arr = $this->header_controls();

		$this->register_controls();

		return $this->get_settings();
	}

	final public function merge_controls_from_array( array $controls ): void {
		$this->settings_arr = array_merge( $this->settings_arr, $controls );
	}

	final public function remove_control( string $id ): void {
		unset( $this->settings_arr[ $id ] );
	}

	final public function update_control( string $id, array $data ): void {
		if ( ! isset( $this->settings_arr[ $id ] ) ) {
			return;
		}
		$this->settings_arr[ $id ] = $this->controller_settings( $data );
	}

	final public function get_control( $id ) {
		return $this->get_control_by_id( $id );
	}

	private function get_control_by_id( $id ) {
		$controls = $this->get_settings();

		if ( $controls && isset( $controls[ $id ] ) ) {
			return $controls[ $id ];
		}

		return false;
	}
}
