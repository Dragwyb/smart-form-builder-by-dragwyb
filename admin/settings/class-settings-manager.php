<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dragwyb\Form_Builder\Includes\Modules\Modules;

/**
 * Class Settings_Manager
 *
 * Manages plugin settings and provides utility methods to access them securely.
 */
class Settings_Manager {


	/**
	 * Check if settings are initialized
	 *
	 * @var bool
	 */
	private bool $is_settings_initialized = false;

	/**
	 * Singleton instance.
	 *
	 * @var Settings_Manager|null
	 */
	private static $instance = null;

	/**
	 * Cached settings array to prevent multiple database queries.
	 *
	 * @var array|null
	 */
	private ?array $settings = null;

	/**
	 * Retrieve the singleton instance of the class.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct( bool $mask = true ) {
		$this->set_option_settings( $mask );
	}

	private function set_option_settings( bool $mask = true ): void {

		if ( $this->is_settings_initialized || ( isset( $this->settings ) && ! empty( $this->settings ) ) ) {
			return;
		}

		$dragwyb_default_settings = array(
			'integrations'   => array(
				'recaptcha_v2_site_key'   => array(
					'label'   => sprintf( '%s Site Key', 'reCAPTCHA v2' ),
					'type'    => 'string',
					'default' => '',
					'mask'    => true,
				),
				'recaptcha_v2_secret_key' => array(
					'label'   => sprintf( '%s Secret Key', 'reCAPTCHA v2' ),
					'type'    => 'string',
					'default' => '',
					'mask'    => true,
				),
				'recaptcha_v3_site_key'   => array(
					'label'   => sprintf( '%s Site Key', 'reCAPTCHA v3' ),
					'type'    => 'string',
					'default' => '',
					'mask'    => true,
				),
				'recaptcha_v3_secret_key' => array(
					'label'   => sprintf( '%s Secret Key', 'reCAPTCHA v3' ),
					'type'    => 'string',
					'default' => '',
					'mask'    => true,
				),
				'hcaptcha_site_key'       => array(
					'label'   => sprintf( '%s Site Key', 'hCaptcha' ),
					'type'    => 'string',
					'default' => '',
					'mask'    => true,
				),
				'hcaptcha_secret_key'     => array(
					'label'   => sprintf( '%s Secret Key', 'hCaptcha' ),
					'type'    => 'string',
					'default' => '',
					'mask'    => true,
				),
			),
			'performance'    => array(
				'load_svg_icons'   => array(
					'label'        => __( 'Load SVG Icons', 'smart-form-builder-by-dragwyb' ),
					'description'  => __( 'Enable this if want to load SVG icons instead of Font Awesome icons (recommended).', 'smart-form-builder-by-dragwyb' ),
					'type'         => 'string',
					'default'      => 'no',
					'valid_values' => array( 'yes', 'no' ),
				),
				'load_default_css' => array(
					'label'        => __( 'Load Default CSS', 'smart-form-builder-by-dragwyb' ),
					'description'  => __( 'Disable this if you dont want to load default CSS for fields and forms (not recommended).', 'smart-form-builder-by-dragwyb' ),
					'type'         => 'string',
					'default'      => 'yes',
					'valid_values' => array( 'yes', 'no' ),
				),
			),
			'fields_manager' => array(),
			'smtp'           => array(
				'smtp_enabled'      => array(
					'label'   => __( 'Enable SMTP', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'bool',
					'default' => false,
				),
				'smtp_host'         => array(
					'label'   => __( 'SMTP Host', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'string',
					'default' => '',
				),
				'smtp_port'         => array(
					'label'   => __( 'SMTP Port', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'number',
					'default' => 587,
				),
				'smtp_encryption'   => array(
					'label'        => __( 'Encryption', 'smart-form-builder-by-dragwyb' ),
					'type'         => 'string',
					'default'      => 'tls',
					'valid_values' => array( 'tls', 'ssl', 'none' ),
				),
				'smtp_auth'         => array(
					'label'   => __( 'SMTP Authentication', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'bool',
					'default' => true,
				),
				'smtp_username'     => array(
					'label'   => __( 'SMTP Username', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'string',
					'default' => '',
				),
				'smtp_password'     => array(
					'label'   => __( 'SMTP Password', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'string',
					'default' => '',
					'mask'    => true,
				),
				'smtp_from_email'   => array(
					'label'   => __( 'From Email', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'string',
					'default' => '',
				),
				'smtp_from_name'    => array(
					'label'   => __( 'From Name', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'string',
					'default' => '',
				),
				'smtp_apply_to_all' => array(
					'label'   => __( 'Apply to all WordPress emails', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'bool',
					'default' => false,
				),
			),
			'gdpr_privacy'   => array(
				'tracking_enabled'          => array(
					'label'   => __( 'Enable Visitor Tracking', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'bool',
					'default' => true,
				),
				'track_admins'              => array(
					'label'   => __( 'Track Logged-in Administrators', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'bool',
					'default' => false,
				),
				'session_timeout'           => array(
					'label'   => __( 'Session Timeout (minutes)', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'number',
					'default' => 30,
				),
				'cookie_duration'           => array(
					'label'   => __( 'Cookie Duration (days)', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'number',
					'default' => 730,
				),
				'gdpr_anonymize_ip'         => array(
					'label'   => __( 'Anonymize IP Addresses', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'bool',
					'default' => false,
				),
				'gdpr_respect_dnt'          => array(
					'label'   => __( 'Respect Do Not Track (DNT)', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'bool',
					'default' => false,
				),
				'gdpr_disable_user_cookies' => array(
					'label'   => __( 'Disable Tracking Cookies', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'bool',
					'default' => false,
				),
				'gdpr_disable_user_details' => array(
					'label'   => __( 'Disable User Details Collection', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'bool',
					'default' => false,
				),
				'gdpr_data_retention_days'  => array(
					'label'   => __( 'Auto-delete tracking data after (days)', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'number',
					'default' => 0,
				),
				'gdpr_retain_entries'       => array(
					'label'   => __( 'Retain Form Entries When Cleaning', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'bool',
					'default' => true,
				),
				'remove_data_on_uninstall'  => array(
					'label'   => __( 'Remove ALL Data on Uninstall', 'smart-form-builder-by-dragwyb' ),
					'type'    => 'bool',
					'default' => false,
				),
			),
		);

		$dragwyb_settings = get_option( 'dragwyb_form_settings', array() );
		$updated_settings = array();

		if ( ! $this->is_settings_initialized ) {
			$this->is_settings_initialized = true;

			if ( ! isset( $dragwyb_default_settings['fields_manager'] ) || empty( $dragwyb_default_settings['fields_manager'] ) ) {
				$this_field_settings = Modules::instance()->get_registered_fields();

				foreach ( $this_field_settings as $field_type => $field_data ) {
					if ( in_array( $field_type, array( 'row', 'button' ) ) ) {
						continue;
					}
					$dragwyb_default_settings['fields_manager'][ $field_type ] = array_merge(
						$field_data,
						array(
							'default' => true,
							'type'    => 'bool',
						)
					);
				}
			}

			if ( empty( $dragwyb_settings ) ) {
				$this->settings = $dragwyb_default_settings;
				return;
			}

			foreach ( $dragwyb_settings as $dragwyb_setting_type => $dragwyb_settings_data ) {
				if ( ! isset( $dragwyb_default_settings[ $dragwyb_setting_type ] ) || isset( $updated_settings[ $dragwyb_setting_type ] ) || ! is_array( $dragwyb_settings_data ) ) {
					continue;
				}

				$updated_settings[ $dragwyb_setting_type ] = $dragwyb_default_settings[ $dragwyb_setting_type ];

				foreach ( $dragwyb_settings[ $dragwyb_setting_type ] as $key => $value ) {
					if ( ! isset( $value ) || ! isset( $dragwyb_default_settings[ $dragwyb_setting_type ][ $key ] ) || ! isset( $dragwyb_default_settings[ $dragwyb_setting_type ][ $key ]['type'] ) ) {
						continue;
					}

					if ( isset( $dragwyb_default_settings[ $dragwyb_setting_type ][ $key ]['valid_values'] ) && ! in_array( $value, $dragwyb_default_settings[ $dragwyb_setting_type ][ $key ]['valid_values'] ) ) {
						continue;
					}

					if ( isset( $dragwyb_default_settings[ $dragwyb_setting_type ][ $key ]['valid_values'] ) && in_array( $value, $dragwyb_default_settings[ $dragwyb_setting_type ][ $key ]['valid_values'] ) ) {
						$updated_settings[ $dragwyb_setting_type ][ $key ]['value'] = $value;
						continue;
					}

					$default_setting = $dragwyb_default_settings[ $dragwyb_setting_type ][ $key ];

					if ( 'bool' === $default_setting['type'] ) {
						$updated_settings[ $dragwyb_setting_type ][ $key ]['value'] = (bool) $value;
					} else {
						$drawyb_setting_value = sanitize_text_field( $value );

						if ( $mask && isset( $default_setting['mask'] ) && true === $default_setting['mask'] ) {
							$drawyb_setting_value = self::mask_api_key( $drawyb_setting_value );
						}

						$updated_settings[ $dragwyb_setting_type ][ $key ]['value'] = $drawyb_setting_value;
					}
				}
			}
		}

		$this->settings = $updated_settings;
	}

	/**
	 * Get all plugin settings from the database.
	 *
	 * @return array All form settings.
	 */
	final public function get_all_settings(): array {
		return (array) $this->settings;
	}

	/**
	 * Get settings for a specific type or tab (e.g., 'integrations', 'performance').
	 *
	 * @param string $type    The settings type/tab key.
	 * @param mixed  $default Default value to return if not found.
	 * @return mixed
	 */
	final public function get_settings( string $type, $default = array() ) {
		$settings = $this->get_all_settings();
		return isset( $settings[ $type ] ) ? $settings[ $type ] : $default;
	}

	/**
	 * Get a specific setting value within a type/tab.
	 *
	 * @param string $type    The settings type/tab key.
	 * @param string $key     The specific setting key to retrieve.
	 * @param mixed  $default Default value to return if not found.
	 * @return mixed
	 */
	final public function get_setting( string $type, string $key, $default = '' ) {
		$type_settings = $this->get_settings( $type );
		return isset( $type_settings[ $key ]['value'] ) ? $type_settings[ $key ]['value'] : $default;
	}

	/**
	 * Safely retrieve an API key, with an option to mask it for secure display.
	 *
	 * @param string $key  The API key identifier (e.g., 'recaptcha_v3_site_key').
	 * @param bool   $mask Whether to mask the retrieved API key.
	 * @return string
	 */
	final public function get_api_key( string $key, bool $mask = false ): string {
		// Assuming API keys are stored under the 'integrations' tab based on current structure.
		$api_settings = $this->get_setting( 'integrations', $key, '' );

		if ( ! isset( $api_settings ) || empty( $api_settings ) ) {
			return '';
		}

		$api_key = $api_settings;

		if ( $mask ) {
			return self::mask_api_key( $api_key );
		}

		return $api_key;
	}

	/**
	 * Professionally mask an API key or sensitive string.
	 * Leaves the first 4 and last 4 characters visible, replacing the rest with asterisks.
	 * Adjusts the visible portion dynamically for shorter strings.
	 *
	 * @param string $api_key The sensitive string to mask.
	 * @return string
	 */
	final public static function mask_api_key( string $api_key ): string {
		$length = strlen( $api_key );

		// If the key is extremely short, just mask the entire thing
		if ( $length <= 4 ) {
			return str_repeat( '*', $length );
		}

		// If the key is shorter than typical API keys (e.g., < 12 chars), only reveal the first 2 characters
		if ( $length < 12 ) {
			return substr( $api_key, 0, 2 ) . str_repeat( '*', $length - 2 );
		}

		// Standard masking: show first 4 and last 4
		$visible_start = 4;
		$visible_end   = 4;
		$mask_length   = $length - ( $visible_start + $visible_end );

		return substr( $api_key, 0, $visible_start ) . str_repeat( '*', $mask_length ) . substr( $api_key, -$visible_end );
	}
}
