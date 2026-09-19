<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group\Background;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Controls\Group\Group_Control_Base;

class Control_Background extends Group_Control_Base {

	protected function init(): void {
		$this->type = 'background';
		$this->name = __( 'Background', 'smart-form-builder-by-dragwyb' );
		$this->icon = 'fas fa-image';
	}

	protected function register_settings(): array {
		return array(
			'name'     => 'string',
			'label'    => 'string',
			'settings' => 'custom',
		);
	}

	protected function valid_default_settings(): array {
		return array(
			'background',
			'color',
			'color_stop',
			'color_b',
			'color_b_stop',
			'gradient_type',
			'gradient_angle',
			'gradient_position',
			'image',
			'position',
			'xpos',
			'ypos',
			'attachment',
			'repeat',
			'size',
			'bg_width',
			'selector',
			'variable_selector',
			'conditions',
			'prefix',
		);
	}

	protected function settings_setting_sanitize( $value ) {
		return $this->sanitize_control( $value );
	}

	protected function sanitize_control( $value, $settings = null ) {
		if ( ! is_array( $value ) ) {
			return array();
		}
		$sanitized = array();

		if ( isset( $value['selector'] ) ) {
			$sanitized['selector'] = sanitize_text_field( $value['selector'] );
		}

		if ( isset( $value['variable_selector'] ) ) {
			$sanitized['variable_selector'] = sanitize_text_field( $value['variable_selector'] );
		}

		foreach ( array( 'background', 'color', 'color_b', 'gradient_type', 'gradient_position', 'position', 'attachment', 'repeat', 'size', 'prefix' ) as $key ) {
			if ( isset( $value[ $key ] ) ) {
				$sanitized[ $key ] = sanitize_text_field( $value[ $key ] );
			}
		}

		foreach ( array( 'color_stop', 'color_b_stop', 'gradient_angle', 'xpos', 'ypos', 'bg_width' ) as $key ) {
			if ( isset( $value[ $key ] ) && is_array( $value[ $key ] ) ) {
				$sanitized[ $key ] = array(
					'size' => floatval( $value[ $key ]['size'] ?? 0 ),
					'unit' => sanitize_text_field( $value[ $key ]['unit'] ?? 'px' ),
				);
			}
		}

		if ( isset( $value['image'] ) && is_array( $value['image'] ) ) {
			$sanitized['image'] = array(
				'id'  => isset( $value['image']['id'] ) ? absint( $value['image']['id'] ) : '',
				'url' => isset( $value['image']['url'] ) ? esc_url_raw( $value['image']['url'] ) : '',
			);
		}

		return $sanitized;
	}

	protected function string_sanitize( $val ) {
		return sanitize_text_field( $val );
	}

	private function get_display_settings(): array {
		$defaults       = $this->default_setting();
		$valid_settings = $this->valid_default_settings();
		$user_data      = isset( $this->data['settings'] ) && is_array( $this->data['settings'] )
			? $this->data['settings']
			: $this->data;

		// Ensure we handle the root selector if passed directly in data
		if ( isset( $this->data['selector'] ) && empty( $user_data['selector'] ) ) {
			$user_data['selector'] = $this->data['selector'];
		}
		if ( isset( $this->data['variable_selector'] ) && empty( $user_data['variable_selector'] ) ) {
			$user_data['variable_selector'] = $this->data['variable_selector'];
		}

		$user_data = array_replace_recursive( $defaults, $user_data );

		$valid_user_data = array();

		foreach ( $valid_settings as $key ) {
			if ( ! array_key_exists( $key, $user_data ) ) {
				continue;
			}

			if ( isset( $user_data[ $key ] ) ) {
				$valid_user_data[ $key ] = $user_data[ $key ];
			}
		}

		return $valid_user_data;
	}

	protected function register_group_controls(): void {
		$settings          = $this->get_display_settings();
		$id                = $this->string_sanitize( $this->id );
		$selector          = isset( $settings['selector'] ) && ! empty( $settings['selector'] ) ? $settings['selector'] : false;
		$variable_selector = isset( $settings['variable_selector'] ) && ! empty( $settings['variable_selector'] ) ? $settings['variable_selector'] : '{{WRAPPER}}';
		$prefix            = isset( $settings['prefix'] ) && ! empty( $settings['prefix'] ) ? $settings['prefix'] : 'form';

		$direct_selectors = array(
			'color'      => 'background-color: {{VALUE}};',
			'image'      => 'background-image: url("{{URL}}");',
			'position'   => 'background-position: {{VALUE}};',
			'attachment' => 'background-attachment: {{VALUE}};',
			'repeat'     => 'background-repeat: {{VALUE}};',
			'size'       => 'background-size: {{VALUE}};',
		);

		$var_selectors = array(
			'color'             => '--dragwyb-' . $prefix . '-bg-color: {{VALUE}};',
			'color_stop'        => '--dragwyb-' . $prefix . '-bg-color-stop: {{VALUE}}{{UNIT}};',
			'color_b'           => '--dragwyb-' . $prefix . '-bg-color-b: {{VALUE}};',
			'color_b_stop'      => '--dragwyb-' . $prefix . '-bg-color-b-stop: {{VALUE}}{{UNIT}};',
			'gradient_angle'    => '--dragwyb-' . $prefix . '-bg-gradient-angle: {{VALUE}}{{UNIT}};',
			'gradient_position' => '--dragwyb-' . $prefix . '-bg-gradient-position: {{VALUE}};',
			'image'             => '--dragwyb-' . $prefix . '-bg-image: url("{{URL}}");',
			'position'          => '--dragwyb-' . $prefix . '-bg-position: {{VALUE}};',
			'xpos'              => '--dragwyb-' . $prefix . '-bg-xpos: {{VALUE}}{{UNIT}};',
			'ypos'              => '--dragwyb-' . $prefix . '-bg-ypos: {{VALUE}}{{UNIT}};',
			'attachment'        => '--dragwyb-' . $prefix . '-bg-attachment: {{VALUE}};',
			'repeat'            => '--dragwyb-' . $prefix . '-bg-repeat: {{VALUE}};',
			'size'              => '--dragwyb-' . $prefix . '-bg-size: {{VALUE}};',
			'bg_width'          => '--dragwyb-' . $prefix . '-bg-width: {{VALUE}}{{UNIT}};',
		);

		$fields = array();

		$fields['background'] = array(
			'label'   => __( 'Background Type', 'smart-form-builder-by-dragwyb' ),
			'type'    => Controls::CHOOSE,
			'options' => array(
				'classic'  => array(
					'title' => __( 'Classic', 'smart-form-builder-by-dragwyb' ),
					'icon'  => 'fa fa-paint-brush',
				),
				'image'    => array(
					'title' => __( 'Image', 'smart-form-builder-by-dragwyb' ),
					'icon'  => 'fa fa-image',
				),
				'gradient' => array(
					'title' => __( 'Gradient', 'smart-form-builder-by-dragwyb' ),
					'icon'  => 'fa fa-barcode',
				),
			),
			'default' => 'classic',
		);

		$fields['gradient_notice'] = array(
			'type'       => Controls::RAW_HTML,
			'raw'        => '<p class="dragwyb-alert dragwyb-alert-warning">' . __( 'Set locations and angle for each breakpoint to ensure the gradient adapts to different screen sizes.', 'smart-form-builder-by-dragwyb' ) . '</p>',
			'conditions' => array(
				$id . '_background' => array( 'gradient' ),
			),
		);

		$fields['color'] = array(
			'label'      => __( 'Color', 'smart-form-builder-by-dragwyb' ),
			'type'       => Controls::COLOR,
			'conditions' => array(
				$id . '_background' => array( 'classic', 'gradient' ),
			),
		);

		$fields['color_stop'] = array(
			'label'      => __( 'Location', 'smart-form-builder-by-dragwyb' ),
			'type'       => Controls::SLIDER,
			'units'      => array( '%', 'px' ),
			'default'    => array(
				'unit' => '%',
				'size' => 0,
			),
			'responsive' => true,
			'conditions' => array(
				$id . '_background' => array( 'gradient' ),
			),
		);

		$fields['color_b'] = array(
			'label'      => __( 'Second Color', 'smart-form-builder-by-dragwyb' ),
			'type'       => Controls::COLOR,
			'conditions' => array(
				$id . '_background' => array( 'gradient' ),
			),
		);

		$fields['color_b_stop'] = array(
			'label'      => __( 'Location', 'smart-form-builder-by-dragwyb' ),
			'type'       => Controls::SLIDER,
			'units'      => array( '%', 'px' ),
			'default'    => array(
				'unit' => '%',
				'size' => 100,
			),
			'responsive' => true,
			'conditions' => array(
				$id . '_background' => array( 'gradient' ),
			),
		);

		$fields['gradient_type'] = array(
			'label'        => __( 'Type', 'smart-form-builder-by-dragwyb' ),
			'type'         => Controls::SELECT,
			'label_inline' => true,
			'options'      => array(
				'linear' => __( 'Linear', 'smart-form-builder-by-dragwyb' ),
				'radial' => __( 'Radial', 'smart-form-builder-by-dragwyb' ),
			),
			'default'      => 'linear',
			'conditions'   => array(
				$id . '_background' => array( 'gradient' ),
			),
		);

		$fields['gradient_angle'] = array(
			'label'      => __( 'Angle', 'smart-form-builder-by-dragwyb' ),
			'type'       => Controls::SLIDER,
			'units'      => array( 'deg', 'rad', 'turn' ),
			'default'    => array(
				'unit' => 'deg',
				'size' => 180,
			),
			'range'      => array(
				'deg'  => array(
					'min' => 0,
					'max' => 360,
				),
				'rad'  => array(
					'min' => 0,
					'max' => 2 * M_PI,
				),
				'turn' => array(
					'min' => 0,
					'max' => 1,
				),
			),
			'responsive' => true,
			'conditions' => array(
				$id . '_background'    => array( 'gradient' ),
				$id . '_gradient_type' => 'linear',
			),
		);

		$fields['gradient_position'] = array(
			'label'        => __( 'Position', 'smart-form-builder-by-dragwyb' ),
			'type'         => Controls::SELECT,
			'label_inline' => true,
			'options'      => array(
				'center center' => __( 'Center Center', 'smart-form-builder-by-dragwyb' ),
				'center left'   => __( 'Center Left', 'smart-form-builder-by-dragwyb' ),
				'center right'  => __( 'Center Right', 'smart-form-builder-by-dragwyb' ),
				'top center'    => __( 'Top Center', 'smart-form-builder-by-dragwyb' ),
				'top left'      => __( 'Top Left', 'smart-form-builder-by-dragwyb' ),
				'top right'     => __( 'Top Right', 'smart-form-builder-by-dragwyb' ),
				'bottom center' => __( 'Bottom Center', 'smart-form-builder-by-dragwyb' ),
				'bottom left'   => __( 'Bottom Left', 'smart-form-builder-by-dragwyb' ),
				'bottom right'  => __( 'Bottom Right', 'smart-form-builder-by-dragwyb' ),
			),
			'default'      => 'center center',
			'responsive'   => true,
			'conditions'   => array(
				$id . '_background'    => array( 'gradient' ),
				$id . '_gradient_type' => 'radial',
			),
		);

		$fields['image'] = array(
			'label'      => __( 'Image', 'smart-form-builder-by-dragwyb' ),
			'type'       => Controls::IMAGE,
			'responsive' => true,
			'conditions' => array(
				$id . '_background' => array( 'image' ),
			),
		);

		$fields['position'] = array(
			'label'        => __( 'Position', 'smart-form-builder-by-dragwyb' ),
			'type'         => Controls::SELECT,
			'label_inline' => true,
			'default'      => 'center center',
			'responsive'   => true,
			'options'      => array(
				'center center' => __( 'Center Center', 'smart-form-builder-by-dragwyb' ),
				'center left'   => __( 'Center Left', 'smart-form-builder-by-dragwyb' ),
				'center right'  => __( 'Center Right', 'smart-form-builder-by-dragwyb' ),
				'top center'    => __( 'Top Center', 'smart-form-builder-by-dragwyb' ),
				'top left'      => __( 'Top Left', 'smart-form-builder-by-dragwyb' ),
				'top right'     => __( 'Top Right', 'smart-form-builder-by-dragwyb' ),
				'bottom center' => __( 'Bottom Center', 'smart-form-builder-by-dragwyb' ),
				'bottom left'   => __( 'Bottom Left', 'smart-form-builder-by-dragwyb' ),
				'bottom right'  => __( 'Bottom Right', 'smart-form-builder-by-dragwyb' ),
				'initial'       => __( 'Custom', 'smart-form-builder-by-dragwyb' ),
			),
			'conditions'   => array(
				$id . '_background' => array( 'image' ),
			),
		);

		$fields['xpos'] = array(
			'label'      => __( 'X Position', 'smart-form-builder-by-dragwyb' ),
			'type'       => Controls::SLIDER,
			'responsive' => true,
			'units'      => array( 'px', '%', 'em', 'vw' ),
			'default'    => array(
				'size' => 0,
			),
			'range'      => array(
				'px' => array(
					'min' => -800,
					'max' => 800,
				),
				'em' => array(
					'min' => -100,
					'max' => 100,
				),
				'%'  => array(
					'min' => -100,
					'max' => 100,
				),
				'vw' => array(
					'min' => -100,
					'max' => 100,
				),
			),
			'conditions' => array(
				$id . '_background' => array( 'image' ),
				$id . '_position'   => array( 'initial' ),
			),
		);

		$fields['ypos'] = array(
			'label'      => __( 'Y Position', 'smart-form-builder-by-dragwyb' ),
			'type'       => Controls::SLIDER,
			'responsive' => true,
			'units'      => array( 'px', '%', 'em', 'vh' ),
			'default'    => array(
				'size' => 0,
			),
			'range'      => array(
				'px' => array(
					'min' => -800,
					'max' => 800,
				),
				'em' => array(
					'min' => -100,
					'max' => 100,
				),
				'%'  => array(
					'min' => -100,
					'max' => 100,
				),
				'vh' => array(
					'min' => -100,
					'max' => 100,
				),
			),
			'conditions' => array(
				$id . '_background' => array( 'image' ),
				$id . '_position'   => array( 'initial' ),
			),
		);

		$fields['attachment'] = array(
			'label'        => __( 'Attachment', 'smart-form-builder-by-dragwyb' ),
			'type'         => Controls::SELECT,
			'label_inline' => true,
			'default'      => '',
			'options'      => array(
				''       => __( 'Default', 'smart-form-builder-by-dragwyb' ),
				'scroll' => __( 'Scroll', 'smart-form-builder-by-dragwyb' ),
				'fixed'  => __( 'Fixed', 'smart-form-builder-by-dragwyb' ),
			),
			'conditions'   => array(
				$id . '_background' => array( 'image' ),
			),
		);

		$fields['attachment_alert'] = array(
			'type'       => Controls::RAW_HTML,
			'raw'        => '<p class="dragwyb-control-field-description">' . __( 'Note: Attachment Fixed works only on desktop.', 'smart-form-builder-by-dragwyb' ) . '</p>',
			'conditions' => array(
				$id . '_background' => array( 'image' ),
				$id . '_attachment' => 'fixed',
			),
		);

		$fields['repeat'] = array(
			'label'        => __( 'Repeat', 'smart-form-builder-by-dragwyb' ),
			'type'         => Controls::SELECT,
			'label_inline' => true,
			'default'      => 'no-repeat',
			'responsive'   => true,
			'options'      => array(
				'no-repeat' => __( 'No-repeat', 'smart-form-builder-by-dragwyb' ),
				'repeat'    => __( 'Repeat', 'smart-form-builder-by-dragwyb' ),
				'repeat-x'  => __( 'Repeat-x', 'smart-form-builder-by-dragwyb' ),
				'repeat-y'  => __( 'Repeat-y', 'smart-form-builder-by-dragwyb' ),
			),
			'conditions'   => array(
				$id . '_background' => array( 'image' ),
			),
		);

		$fields['size'] = array(
			'label'        => __( 'Display Size', 'smart-form-builder-by-dragwyb' ),
			'type'         => Controls::SELECT,
			'label_inline' => true,
			'responsive'   => true,
			'default'      => 'cover',
			'options'      => array(
				'auto'    => __( 'Auto', 'smart-form-builder-by-dragwyb' ),
				'cover'   => __( 'Cover', 'smart-form-builder-by-dragwyb' ),
				'contain' => __( 'Contain', 'smart-form-builder-by-dragwyb' ),
				'initial' => __( 'Custom', 'smart-form-builder-by-dragwyb' ),
			),
			'conditions'   => array(
				$id . '_background' => array( 'image' ),
			),
		);

		$fields['bg_width'] = array(
			'label'      => __( 'Width', 'smart-form-builder-by-dragwyb' ),
			'type'       => Controls::SLIDER,
			'responsive' => true,
			'units'      => array( 'px', '%', 'em', 'vw' ),
			'range'      => array(
				'px' => array(
					'max' => 1000,
				),
			),
			'default'    => array(
				'size' => 100,
				'unit' => '%',
			),
			'conditions' => array(
				$id . '_background' => array( 'image' ),
				$id . '_size'       => array( 'initial' ),
			),
		);

		// Process fields into controls
		foreach ( $fields as $key => $meta ) {
			$config = isset( $settings[ $key ] ) ? $settings[ $key ] : array();

			$control_args = array_filter(
				$meta,
				function ( $k, $value ) {
					return $k !== 'responsive';
				},
				ARRAY_FILTER_USE_BOTH
			);

			if ( isset( $config['default'] ) ) {
				$control_args['default'] = $config['default'];
			}

			if ( ! empty( $settings['conditions'] ) ) {
				if ( isset( $control_args['conditions'] ) && is_array( $control_args['conditions'] ) ) {
					$control_args['conditions'] = array_merge( $control_args['conditions'], $settings['conditions'] );
				} else {
					$control_args['conditions'] = $settings['conditions'];
				}
			}

			if ( $meta['type'] === Controls::SLIDER ) {
				if ( isset( $config['range'] ) ) {
					$control_args['range'] = $config['range'];
				}
				if ( isset( $config['units'] ) ) {
					$control_args['units'] = $config['units'];
				}
			}

			$control_args['selectors'] = array();

			// 1. Direct CSS properties applied to $selector (if provided)
			if ( $selector && isset( $direct_selectors[ $key ] ) && ! empty( $direct_selectors[ $key ] ) ) {
				$control_args['selectors'][ $selector ] = $direct_selectors[ $key ];
			}

			// 2. CSS variables applied to $variable_selector (default {{WRAPPER}})
			if ( $variable_selector && isset( $var_selectors[ $key ] ) && ! empty( $var_selectors[ $key ] ) ) {
				if ( isset( $control_args['selectors'][ $variable_selector ] ) ) {
					$control_args['selectors'][ $variable_selector ] .= ' ' . $var_selectors[ $key ];
				} else {
					$control_args['selectors'][ $variable_selector ] = $var_selectors[ $key ];
				}
			}

			if ( isset( $meta['responsive'] ) && $meta['responsive'] ) {
				$this->add_responsive_control( $id . '_' . $key, $control_args );
			} else {
				$this->add_control( $id . '_' . $key, $control_args );
			}
		}
	}
}
