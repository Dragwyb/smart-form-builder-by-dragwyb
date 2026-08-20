<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Rest_Routes;

use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbars;
use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Form_Submission_Handler
 *
 * Handles validation and submission errors for the form builder.
 *
 * @package Dragwyb\Form_Builder\Includes\Rest_Routes
 */
class Form_Submission_Handler {


	/**
	 * Stores the form id.
	 *
	 * @var int
	 */
	private int $form_id;

	/**
	 * Stores the raw form data.
	 *
	 * @var array
	 */
	private array $raw_data;

	/**
	 * Stores the sanitized data.
	 *
	 * @var array
	 */
	private array $sanitized_data = array();

	/**
	 * Stores the form configuration.
	 *
	 * @var array
	 */
	private array $form_config = array();

	/**
	 * Stores the list of errors.
	 *
	 * @var \WP_Error
	 */
	private \WP_Error $errors;

	/**
	 * Stores the form return data.
	 *
	 * @var array
	 */
	private array $form_return_data = array();

	/**
	 * Stores field configuration and metadata keyed by the field's original key.
	 *
	 * Each entry holds:
	 *   - 'field_type': the field type string for quick access.
	 *
	 * Used by update_submission_entry() to retrieve field type and re-run
	 * sanitization without having to re-parse the full form config.
	 *
	 * @var array<string, array{field_type: string}>
	 */
	private array $fields_data = array();

	/**
	 * Frontend Render instance.
	 *
	 * @var Frontend_Render
	 */
	private Frontend_Render $frontend;

	/**
	 * Toolbars instance.
	 *
	 * @var Toolbars
	 */
	private Toolbars $toolbars;

	/**
	 * Constructor.
	 *
	 * @param int             $form_id   The form ID.
	 * @param array           $form_data The raw form data to process.
	 * @param Frontend_Render $frontend  The frontend render instance.
	 * @param Toolbars        $toolbars  The toolbars instance.
	 */
	public function __construct( int $form_id, array $form_data, Frontend_Render $frontend, Toolbars $toolbars ) {
		$this->form_id  = absint( $form_id );
		$this->raw_data = wp_unslash( $form_data );
		$this->frontend = $frontend;
		$this->toolbars = $toolbars;
		$this->errors   = new \WP_Error();
	}

	/**
	 * Execute the form validation and submission workflow.
	 *
	 * @return void
	 */
	public function handle(): void {
		if ( ! defined( 'DRAGWYB_FORM_SUBMISSION_REQUEST' ) ) {
			return;
		}

		$this->generate_form_config( $this->raw_data );
		$this->validate_honeypot( $this->raw_data );
		$this->validate_fields( $this->raw_data );

		$this->process_submission();
	}

	/**
	 * Process the complete form submission workflow.
	 *
	 * @return void
	 */
	private function process_submission(): void {
		$this->frontend->init( $this->form_id );
		$after_submission_toolbar_config = $this->toolbars->get_toolbar( 'after-submission' );

		if ( ! isset( $after_submission_toolbar_config ) || ! $after_submission_toolbar_config instanceof Toolbar_Base ) {
			return;
		}

		unset(
			$this->sanitized_data['session_uid'],
			$this->sanitized_data['session_id'],
			$this->sanitized_data['user_id'],
			$this->sanitized_data['user_session']
		);

		if ( ! $this->has_errors() ) {
			do_action( 'Dragwyb/Form/Submission/Before_Processing', $this->sanitized_data, $this->form_config, $this );
		}

		$after_submission_toolbar_config->process_submission( $this->form_id, $this->sanitized_data, $this->form_config, $this );

		if ( ! $this->has_errors() ) {
			do_action( 'Dragwyb/Form/Submission/After_Processing', $this->sanitized_data, $this->form_config, $this );
		}
	}

	private function generate_form_config( array $form_data ): void {
		$this->frontend->init( $this->form_id );
		$toolbar_types = $this->toolbars->get_toolbar_types();

		$this->form_config = array();

		foreach ( $toolbar_types as $toolbar_type ) {
			if ( $toolbar_type === 'fields' ) {
				$this->set_fields_config( $this->frontend->get_fields_values(), $form_data );
			} else {
				$this->form_config[ $toolbar_type ] = $this->frontend->get_toolbars_values( $toolbar_type );
			}
		}
	}

	private function set_fields_config( array $fields, array $form_data ): void {
		// Pre-index the fields array for O(1) lookups.
		$indexed_fields = array();
		foreach ( $fields as $key => $field ) {
			$indexed_fields[ $key ] = $key;
			if ( isset( $field['attributes']['field_id'] ) ) {
				$indexed_fields[ $field['attributes']['field_id'] ] = $key;
			}
		}

		foreach ( $form_data as $field_key_index => $field_value ) {
			$field_orignal_key = sanitize_text_field( $field_value['name'] );
			$field_key         = substr( $field_orignal_key, 6 );

			$matched_key = null;
			if ( isset( $indexed_fields[ $field_key ] ) ) {
				$matched_key = $indexed_fields[ $field_key ];
			} elseif ( isset( $indexed_fields[ $field_orignal_key ] ) ) {
				$matched_key = $indexed_fields[ $field_orignal_key ];
			}

			if ( null !== $matched_key && isset( $fields[ $matched_key ] ) ) {
				$field_data = $fields[ $matched_key ];
				if ( ! isset( $field_data['type'] ) ) {
					continue;
				}

				$field_type = $field_data['type'];

				// Evaluate conditional logic.
				$condition_checker = new Condition_Field_Methods();
				$condition_checker->set_field_config( $field_data );
				if ( ! $condition_checker->is_matched( $this->raw_data ) ) {
					unset( $this->raw_data[ $field_key_index ] );
					continue;
				}

				$this->set_fields_sanitized_values( $field_type, $field_orignal_key, $field_data, $field_value['value'] );

				unset( $fields[ $matched_key ] );
			}
		}

		foreach ( $fields as $key => $field ) {
			if ( ! isset( $field['type'] ) ) {
				continue;
			}

			$custom_id = 'field_' . $key;

			$field_id = isset( $field['attributes']['field_id'] ) && $field['attributes']['field_id'] !== $custom_id ? $field['attributes']['field_id'] : $custom_id;

			$this->form_config['fields'][ $field_id ] = $field;
		}
	}

	/**
	 * Sanitize field values and set the initial sanitized data state.
	 *
	 * @param string $field_type        The field type.
	 * @param string $field_orignal_key The field original key.
	 * @param array  $field_data        The field data.
	 * @param mixed  $field_value       The field raw value.
	 * @return void
	 */
	private function set_fields_sanitized_values( string $field_type, string $field_orignal_key, array $field_data, $field_value ): void {
		$field_orignal_key = sanitize_text_field( $field_orignal_key );

		$this->form_config['fields'][ $field_orignal_key ] = $field_data;
		$field_module                                      = $this->frontend->get_module( $field_type );

		if ( ! $field_module instanceof Field_Base ) {
			return;
		}

		$this->form_config['fields'][ $field_orignal_key ]['raw_value'] = $field_value;

		$field_sanitized_value = apply_filters( 'Dragwyb/Field/Value/Sanitize/' . $field_type, $field_value );

		if ( method_exists( $field_module, 'sanitize' ) ) {
			$field_sanitized_value = $field_module->sanitize( $field_value );
		} else {
			$field_sanitized_value = is_array( $field_value ) ? array_map( 'sanitize_text_field', $field_value ) : sanitize_text_field( $field_value );
		}

		// 1. Store it in the configuration array for reference
		$this->form_config['fields'][ $field_orignal_key ]['value'] = $field_sanitized_value;

		// 2. Set the baseline submission state (This is the critical addition)
		$this->sanitized_data[ $field_orignal_key ] = $field_sanitized_value;

		// Cache field metadata so update_submission_entry() can re-sanitize later.
		$this->fields_data[ $field_orignal_key ] = array(
			'field_type' => $field_type,
		);
	}

	/**
	 * Validate fields
	 */
	private function validate_fields( array $form_data ): void {
		do_action( 'Dragwyb/Form/Before_Validation', $form_data, $this->form_config, $this );

		foreach ( $form_data as $field_value ) {
			$field_orignal_key = sanitize_text_field( $field_value['name'] );

			// 1. If a previous hook completely removed this field, skip validation.
			if ( ! array_key_exists( $field_orignal_key, $this->sanitized_data ) ) {
				continue;
			}

			if ( ! isset( $this->form_config['fields'][ $field_orignal_key ] ) ) {
				continue;
			}

			$field_data = $this->form_config['fields'][ $field_orignal_key ];
			$field_type = $field_data['type'];

			// 2. Fetch the CURRENT state of the sanitized data.
			// This ensures if an earlier hook called update_submission_entry(),
			// this hook validates the updated value, not the original raw one.
			$current_sanitized_value = $this->sanitized_data[ $field_orignal_key ];

			// 3. Fire the validation hook. If this hook calls add_error(),
			// remove_submission_entry(), etc., the $this instance handles it perfectly.
			do_action( 'Dragwyb/Field/Value/Validate/' . $field_type, $current_sanitized_value, $field_orignal_key, $this->form_config, $this );
		}

		do_action( 'Dragwyb/Form/After_Validation', $form_data, $this->form_config, $this );
	}

	/**
	 * Validate the honeypot field.
	 *
	 * @param array $form_data The raw form data.
	 * @return void
	 */
	private function validate_honeypot( array $form_data ): void {
		$advance_settings = isset( $this->form_config['advance'] ) ? $this->form_config['advance'] : array();

		if ( isset( $advance_settings['honeypot'] ) && $advance_settings['honeypot'] === 'yes' ) {
			foreach ( $form_data as $field ) {
				if ( isset( $field['name'] ) && $field['name'] === 'dragwyb_h_email' ) {
					if ( ! empty( $field['value'] ) ) {
						$this->add_error( 'honeypot', __( 'Spam detected. Form submission rejected.', 'smart-form-builder-by-dragwyb' ) );
					}
					break;
				}
			}
		}
	}

	public function get_sanitized_data(): array {
		return $this->sanitized_data;
	}

	/**
	 * Set the form return data.
	 *
	 * @param string $id The form return data ID.
	 * @param $value The form return data value.
	 * @return void
	 */
	public function set_form_return_data( string $id, $value ): void {
		$this->form_return_data[ $id ] = $value;
	}

	/**
	 * Get the form return data.
	 *
	 * @return array The form return data.
	 */
	public function get_form_return_data(): array {
		return isset( $this->form_return_data ) ? $this->form_return_data : array();
	}

	/**
	 * Add an error for a specific field or identifier.
	 *
	 * @param string $id      The field ID or error identifier.
	 * @param string $message The error message.
	 *
	 * @return void
	 */
	public function add_error( string $id, string $message ): void {
		$this->errors->add( $id, $this->sanitize_error( $message ) );
	}

	/**
	 * Remove an error by its ID.
	 *
	 * @param string $id The field ID or error identifier.
	 *
	 * @return void
	 */
	public function remove_error( string $id ): void {
		$this->errors->remove( $id );
	}

	/**
	 * Sanitize an error message.
	 *
	 * @param string $message The error message to sanitize.
	 *
	 * @return string The sanitized error message.
	 */
	public function sanitize_error( string $message ): string {
		return sanitize_text_field( wp_unslash( $message ) );
	}

	/**
	 * Check if there are any errors.
	 *
	 * @return bool True if there are errors, false otherwise.
	 */
	public function has_errors(): bool {
		return $this->errors->has_errors();
	}

	/**
	 * Get all registered errors.
	 *
	 * @return \WP_Error The WP_Error instance.
	 */
	public function get_errors(): \WP_Error {
		return $this->errors;
	}

	/**
	 * Get a specific error by its ID.
	 *
	 * @param string $id The field ID or error identifier.
	 *
	 * @return string|null The error message, or null if not found.
	 */
	public function get_error( string $id ): ?string {
		$message = $this->errors->get_error_message( $id );
		return $message !== '' ? $message : null;
	}

	/**
	 * Clear all errors.
	 *
	 * @return void
	 */
	public function clear_errors(): void {
		$this->errors = new \WP_Error();
	}

	// -------------------------------------------------------------------------
	// Submission Entry Management
	// -------------------------------------------------------------------------

	/**
	 * Remove a sanitized submission entry by its field key.
	 *
	 * Checks whether the given field ID exists in the current sanitized data.
	 * If found, the entry is removed; otherwise the call is silently ignored.
	 *
	 * @param string $id The field original key (e.g. "field_abc123") to remove.
	 * @return void
	 */
	public function remove_submission_entry( string $id ): void {
		$id = sanitize_text_field( $id );

		if ( ! isset( $this->sanitized_data[ $id ] ) ) {
			return;
		}

		unset( $this->sanitized_data[ $id ] );
	}

	/**
	 * Update a sanitized submission entry with a new value.
	 *
	 * Retrieves the field type from the cached $fields_data for the given field ID,
	 * runs the value through the field-type-specific sanitize filter
	 * ( 'Dragwyb/Field/Value/Sanitize/{field_type}' ), and stores the result
	 * in $sanitized_data.
	 *
	 * Returns false when:
	 *   - the submission ID does not exist in $sanitized_data, or
	 *   - no field metadata is found in $fields_data for that ID.
	 *
	 * @param string $id    The field original key (e.g. "field_abc123") to update.
	 * @param mixed  $value The new raw value to sanitize and store.
	 * @return bool True on success, false when the entry or its metadata is missing.
	 */
	public function update_submission_entry( string $id, $value ): bool {
		$id = sanitize_text_field( $id );

		if ( ! isset( $this->sanitized_data[ $id ] ) ) {
			return false;
		}

		if ( ! isset( $this->fields_data[ $id ]['field_type'] ) ) {
			return false;
		}

		$field_type   = $this->fields_data[ $id ]['field_type'];
		$field_module = $this->frontend->get_module( $field_type );

		$sanitized_value = apply_filters( 'Dragwyb/Field/Value/Sanitize/' . $field_type, $value );

		if ( method_exists( $field_module, 'sanitize' ) ) {
			$sanitized_value = $field_module->sanitize( $value );
		} else {
			$sanitized_value = is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : sanitize_text_field( $value );
		}

		$this->sanitized_data[ $id ] = $sanitized_value;

		return true;
	}
}
