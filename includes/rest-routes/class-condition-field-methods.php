<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Rest_Routes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Condition_Field_Methods
 *
 * Handles conditional logic evaluation for form submission fields.
 *
 * @package Dragwyb\Form_Builder\Includes\Rest_Routes
 */
class Condition_Field_Methods {

	/**
	 * Stores the field data configuration.
	 *
	 * @var array
	 */
	private array $field_data = array();

	/**
	 * Set the field configuration to evaluate.
	 *
	 * @param array $field_data  The settings and attributes of the current field.
	 * @return void
	 */
	public function set_field_config( array $field_data ): void {
		$this->field_data = $field_data;
	}

	/**
	 * Evaluate if the field matches its conditional logic criteria.
	 *
	 * If conditional logic is disabled, returns true.
	 * Otherwise, evaluates all conditions.
	 *
	 * @param array $submitted_data The raw or sanitized submitted form data.
	 * @return bool True if the field is matched (i.e. should be processed), false otherwise.
	 */
	public function is_matched( array $submitted_data ): bool {
		$attributes = $this->field_data['attributes'] ?? $this->field_data;

		$enable_logic = $attributes['enable_logic'] ?? 'no';
		if ( 'yes' !== $enable_logic ) {
			return true;
		}

		$logic_conditions = $attributes['logic_conditions'] ?? array();
		if ( empty( $logic_conditions ) || ! is_array( $logic_conditions ) ) {
			return true;
		}

		$conditions_met = true;
		// Default action is 'yes' (Show). In logic, condition_action can be switcher: 'yes' (Show) or 'no' (Hide).
		$action = 'yes';

		foreach ( $logic_conditions as $condition ) {
			$cond_attrs = $condition['attributes'] ?? $condition;
			if ( empty( $cond_attrs ) || empty( $cond_attrs['condition_field_id'] ) ) {
				continue;
			}

			$action = $cond_attrs['condition_action'] ?? 'hide';

			// Get the submitted value for the dependency field.
			$dep_field_id = $cond_attrs['condition_field_id'];
			$dep_value    = $this->get_submitted_field_value( $dep_field_id, $submitted_data );

			$operator        = $cond_attrs['condition_operator'] ?? 'equal';
			$condition_value = $cond_attrs['condition_value'] ?? '';

			$is_cond_met = $this->evaluate_condition( $operator, $dep_value, $condition_value );

			if ( ! $is_cond_met ) {
				$conditions_met = false;
			}
		}

		// If action is 'yes' (Show): show/match if met, hide/not match if not met.
		// If action is 'no' (Hide): hide/not match if met, show/match if not met.
		return ( 'show' === $action ) ? $conditions_met : ! $conditions_met;
	}

	/**
	 * Helper to get the submitted value for a specific field ID.
	 *
	 * @param string $field_id       The field ID to search.
	 * @param array  $submitted_data The submitted form values.
	 * @return mixed The field value or empty string.
	 */
	private function get_submitted_field_value( string $field_id, array $submitted_data ) {
		foreach ( $submitted_data as $item ) {
			if ( ! isset( $item['name'] ) ) {
				continue;
			}

			$name       = sanitize_text_field( $item['name'] );
			$clean_name = rtrim( $name, '[]' );

			if ( $clean_name === $field_id || $name === $field_id ) {
				return $item['value'] ?? '';
			}

			// Support matching field_ prefix differences.
			if ( strpos( $clean_name, 'field_' ) === 0 && substr( $clean_name, 6 ) === $field_id ) {
				return $item['value'] ?? '';
			}
			if ( strpos( $field_id, 'field_' ) === 0 && substr( $field_id, 6 ) === $clean_name ) {
				return $item['value'] ?? '';
			}
		}

		return '';
	}

	/**
	 * Evaluate condition operators.
	 *
	 * @param string $operator        The operator (equal, not_equal, contains, not_contains, greater_than, less_than).
	 * @param mixed  $field_value     The submitted field value.
	 * @param mixed  $condition_value The expected value.
	 * @return bool True if condition is met, false otherwise.
	 */
	public function evaluate_condition( string $operator, $field_value, $condition_value ): bool {
		if ( is_array( $field_value ) ) {
			$is_cond_empty = ( null === $condition_value || '' === trim( (string) $condition_value ) );
			if ( 'equal' === $operator ) {
				if ( $is_cond_empty ) {
					return empty( $field_value );
				}
				return in_array( $condition_value, $field_value, true );
			}
			if ( 'not_equal' === $operator ) {
				if ( $is_cond_empty ) {
					return ! empty( $field_value );
				}
				return ! in_array( $condition_value, $field_value, true );
			}
			if ( 'contains' === $operator ) {
				if ( $is_cond_empty ) {
					return false;
				}
				foreach ( $field_value as $val ) {
					if ( strpos( (string) $val, (string) $condition_value ) !== false ) {
						return true;
					}
				}
				return false;
			}
			if ( 'not_contains' === $operator ) {
				if ( $is_cond_empty ) {
					return true;
				}
				foreach ( $field_value as $val ) {
					if ( strpos( (string) $val, (string) $condition_value ) !== false ) {
						return false;
					}
				}
				return true;
			}
		}

		$str_field_val = ( null !== $field_value ) ? trim( (string) $field_value ) : '';
		$str_cond_val  = ( null !== $condition_value ) ? trim( (string) $condition_value ) : '';

		switch ( $operator ) {
			case 'equal':
				return $str_field_val === $str_cond_val;
			case 'not_equal':
				return $str_field_val !== $str_cond_val;
			case 'contains':
				return strpos( $str_field_val, $str_cond_val ) !== false;
			case 'not_contains':
				return strpos( $str_field_val, $str_cond_val ) === false;
			case 'greater_than':
				return (float) $str_field_val > (float) $str_cond_val;
			case 'less_than':
				return (float) $str_field_val < (float) $str_cond_val;
			default:
				return false;
		}
	}
}
