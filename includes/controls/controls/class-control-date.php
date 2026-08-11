<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

use Dragwyb\Form_Builder\Includes\Helper\Helper;

class Control_Date extends Control_Base {

	protected function register_scripts(): array {
		return array( 'dragwyb-flatpickr', 'dragwyb-editor-controls' );
	}

	protected function register_style(): array {
		return array( 'dragwyb-flatpickr', 'dragwyb-editor-controls' );
	}

	protected function register_settings() {
		return array(
			'name'         => 'string',
			'label'        => 'string',
			'default'      => 'string',
			'description'  => 'string',
			'mode'         => 'string',
			'picker'       => 'string',
			'date_format'  => 'string',
			'time_24hr'    => 'boolean',
			'label_inline' => 'boolean',
		);
	}

	protected function default_setting(): array {
		return array(
			'mode'        => 'single',
			'picker'      => 'date',
			'date_format' => 'Y-m-d',
			'time_24hr'   => true,
		);
	}

	public function __construct() {
		parent::__construct();
		Helper::register_flatpickr_assets();
	}

	protected function init(): void {
		$this->type = 'date';
		$this->name = __( 'Date', 'smart-form-builder-by-dragwyb' );
	}

	private function is_time_picker( array $settings ): bool {
		return isset( $settings['picker'] ) && 'time' === $settings['picker'];
	}

	private function sanitize_single_date( string $value ): string {
		$value = sanitize_text_field( $value );
		$date  = \DateTime::createFromFormat( 'Y-m-d|', $value );

		return ( $date && $date->format( 'Y-m-d' ) === $value ) ? $value : '';
	}

	private function sanitize_single_time( string $value ): string {
		$value = sanitize_text_field( $value );
		$time  = \DateTime::createFromFormat( '!H:i', $value );

		return ( $time && $time->format( 'H:i' ) === $value ) ? $value : '';
	}

	protected function sanitize_control( $value, $settings ) {
		if ( empty( $value ) ) {
			return '';
		}

		$value = sanitize_text_field( (string) $value );

		if ( $this->is_time_picker( $settings ) ) {
			return $this->sanitize_single_time( $value );
		}

		$mode = isset( $settings['mode'] ) ? (string) $settings['mode'] : 'single';

		if ( 'multiple' === $mode ) {
			$parts = array_map( 'trim', explode( ',', $value ) );
			$valid = array();
			foreach ( $parts as $part ) {
				if ( '' === $part ) {
					continue;
				}
				$date = $this->sanitize_single_date( $part );
				if ( '' !== $date ) {
					$valid[] = $date;
				}
			}
			return implode( ', ', $valid );
		}

		if ( 'range' === $mode ) {
			$parts = preg_split( '/\s+to\s+/i', $value );
			$parts = array_values( array_filter( array_map( 'trim', (array) $parts ) ) );
			if ( 2 !== count( $parts ) ) {
				return '';
			}
			$from = $this->sanitize_single_date( $parts[0] );
			$to   = $this->sanitize_single_date( $parts[1] );
			return ( '' !== $from && '' !== $to ) ? $from . ' to ' . $to : '';
		}

		return $this->sanitize_single_date( $value );
	}
}
