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
	private $is_settings_initialized = false;

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
	private $settings = null;

	/**
	 * Unmasked settings cache.
	 *
	 * @var array|null
	 */
	private $unmasked_settings = null;

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
					'label'       => sprintf( '%s Site Key', 'reCAPTCHA v2' ),
					'type'        => 'string',
					'placeholder' => __( 'Enter API Key', 'smart-form-builder-by-dragwyb' ),
					'default'     => '',
					'mask'        => true,
				),
				'recaptcha_v2_secret_key' => array(
					'label'       => sprintf( '%s Secret Key', 'reCAPTCHA v2' ),
					'type'        => 'string',
					'placeholder' => __( 'Enter Secret Key', 'smart-form-builder-by-dragwyb' ),
					'default'     => '',
					'mask'        => true,
				),
				'recaptcha_v3_site_key'   => array(
					'label'       => sprintf( '%s Site Key', 'reCAPTCHA v3' ),
					'type'        => 'string',
					'placeholder' => __( 'Enter API Key', 'smart-form-builder-by-dragwyb' ),
					'default'     => '',
					'mask'        => true,
				),
				'recaptcha_v3_secret_key' => array(
					'label'       => sprintf( '%s Secret Key', 'reCAPTCHA v3' ),
					'type'        => 'string',
					'placeholder' => __( 'Enter Secret Key', 'smart-form-builder-by-dragwyb' ),
					'default'     => '',
					'mask'        => true,
				),
				'hcaptcha_site_key'       => array(
					'label'       => sprintf( '%s Site Key', 'hCaptcha' ),
					'type'        => 'string',
					'placeholder' => __( 'Enter Site Key', 'smart-form-builder-by-dragwyb' ),
					'default'     => '',
					'mask'        => true,
				),
				'hcaptcha_secret_key'     => array(
					'label'       => sprintf( '%s Secret Key', 'hCaptcha' ),
					'type'        => 'string',
					'placeholder' => __( 'Enter Secret Key', 'smart-form-builder-by-dragwyb' ),
					'default'     => '',
					'mask'        => true,
				),
			),
			'performance'    => array(
				'load_svg_icons'   => array(
					'label'        => __( 'Load SVG Icons', 'smart-form-builder-by-dragwyb' ),
					'description'  => __( 'Enable this if want to load SVG icons instead of Font Awesome icons (recommended).', 'smart-form-builder-by-dragwyb' ),
					'type'         => 'bool',
					'default'      => 'no',
					'valid_values' => array( 'yes', 'no' ),
				),
				'load_default_css' => array(
					'label'        => __( 'Load Default CSS', 'smart-form-builder-by-dragwyb' ),
					'description'  => __( 'Disable this if you dont want to load default CSS for fields and forms (not recommended).', 'smart-form-builder-by-dragwyb' ),
					'type'         => 'bool',
					'default'      => 'yes',
					'valid_values' => array( 'yes', 'no' ),
				),
			),
			'fields_manager' => array(),
			'smtp'           => array(
				'smtp_enabled'      => array(
					'label'       => __( 'Enable SMTP Delivery', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'Route Smart Form Builder emails via custom SMTP server.', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'bool',
					'default'     => false,
				),
				'smtp_provider'     => array(
					'start_section' => __( 'Email Delivery', 'smart-form-builder-by-dragwyb' ),
					'condition'     => array( 'smtp_enabled' => true ),
					'label'         => __( 'Guided Setup / Preset Providers', 'smart-form-builder-by-dragwyb' ),
					'description'   => __(
						'Select your provider to pre-fill common server parameters and view instructions.',
						'smart-form-builder-by-dragwyb'
					),
					'type'          => 'select',
					'default'       => '',
					'inline'        => false,
					'options'       => array(
						array(
							'label' => 'Gmail / Google Workspace',
							'value' => 'gmail',
						),
						array(
							'label' => 'Microsoft 365 / Outlook',
							'value' => 'microsoft',
						),
						array(
							'label' => 'Brevo (Sendinblue)',
							'value' => 'brevo',
						),
						array(
							'label' => 'SendGrid',
							'value' => 'sendgrid',
						),
						array(
							'label' => 'Mailgun',
							'value' => 'mailgun',
						),
						array(
							'label' => 'Amazon SES',
							'value' => 'amazon_ses',
						),
						array(
							'label' => 'Zoho',
							'value' => 'zoho',
						),
						array(
							'label' => 'cPanel / DirectAdmin',
							'value' => 'cpanel',
						),
						array(
							'label' => 'Custom SMTP',
							'value' => 'custom',
						),
					),
				),
				'smtp_host'         => array(
					'condition'   => array( 'smtp_enabled' => true ),
					'label'       => __( 'SMTP Host', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'Configure the hostname of your SMTP server (e.g. smtp.gmail.com).', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'string',
					'placeholder' => 'e.g. smtp.example.com',
					'default'     => '',
					'example'     => array(
						'gmail'     => 'smtp.gmail.com',
						'microsoft' => 'smtp.office365.com',
						'brevo'     => 'smtp.brevo.com',
						'sendgrid'  => 'smtp.sendgrid.net',
						'mailgun'   => 'smtp.mailgun.org',
						'ses'       => 'email-smtp.us-east-1.amazonaws.com',
						'zoho'      => 'smtp.zoho.com',
					),
				),
				'smtp_port'         => array(
					'condition'   => array( 'smtp_enabled' => true ),
					'label'       => __( 'SMTP Port', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'Common ports are 587 (TLS), 465 (SSL), or 25 (None).', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'number',
					'placeholder' => '587',
					'default'     => 587,
				),
				'smtp_encryption'   => array(
					'condition'    => array( 'smtp_enabled' => true ),
					'label'        => __( 'Encryption', 'smart-form-builder-by-dragwyb' ),
					'description'  => __( 'Select security encryption type for outgoing emails.', 'smart-form-builder-by-dragwyb' ),
					'type'         => 'select',
					'default'      => 'tls',
					'valid_values' => array( 'tls', 'ssl', 'none' ),
					'options'      => array(
						array(
							'label' => 'TLS (Port 587)',
							'value' => 'tls',
						),
						array(
							'label' => 'SSL (Port 465)',
							'value' => 'ssl',
						),
						array(
							'label' => 'None (Port 25)',
							'value' => 'none',
						),
					),
				),
				'smtp_auth'         => array(
					'start_section' => __( 'SMTP Authentication', 'smart-form-builder-by-dragwyb' ),
					'condition'     => array( 'smtp_enabled' => true ),
					'label'         => __( 'Server Requires Authentication', 'smart-form-builder-by-dragwyb' ),
					'description'   => __( 'Enable if your SMTP server requires username and password.', 'smart-form-builder-by-dragwyb' ),
					'type'          => 'bool',
					'default'       => true,
				),
				'smtp_username'     => array(
					'condition'   => array(
						'smtp_enabled' => true,
						'smtp_auth'    => true,
					),
					'label'       => __( 'SMTP Username', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'Your SMTP authentication username or email.', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'string',
					'placeholder' => 'e.g. user@example.com',
					'default'     => '',
				),
				'smtp_password'     => array(
					'condition'   => array(
						'smtp_enabled' => true,
						'smtp_auth'    => true,
					),
					'label'       => __( 'SMTP Password', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'Your SMTP authentication password or app password.', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'password',
					'placeholder' => '••••••••',
					'default'     => '',
					'mask'        => true,
				),
				'smtp_from_email'   => array(
					'condition'   => array( 'smtp_enabled' => true ),
					'label'       => __( 'From Email (Optional)', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'The email address that form emails will be sent from.', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'string',
					'placeholder' => 'noreply@yourdomain.com',
					'default'     => '',
				),
				'smtp_from_name'    => array(
					'condition'   => array( 'smtp_enabled' => true ),
					'label'       => __( 'From Name (Optional)', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'The name that form emails will be sent from.', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'string',
					'placeholder' => 'My Website',
					'default'     => '',
				),
				'smtp_apply_to_all' => array(
					'condition'   => array( 'smtp_enabled' => true ),
					'label'       => __( 'Apply to all site emails', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'Routes WooCommerce, password resets, and all WordPress emails through this SMTP.', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'bool',
					'default'     => false,
				),
			),
			'gdpr_privacy'   => array(
				'tracking_enabled'          => array(
					'start_section' => __( 'Visitor Tracking Settings', 'smart-form-builder-by-dragwyb' ),
					'label'         => __( 'Enable Visitor Tracking', 'smart-form-builder-by-dragwyb' ),
					'description'   => __( 'Collect visitor analytics, pageviews, and traffic source attribution across your website.', 'smart-form-builder-by-dragwyb' ),
					'type'          => 'bool',
					'default'       => true,
				),
				'track_admins'              => array(
					'label'       => __( 'Track Logged-in Administrators', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'Include site administrators in visitor analytics and session metrics.', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'bool',
					'default'     => false,
				),
				'session_timeout'           => array(
					'label'       => __( 'Session Timeout (minutes)', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'A new session starts after this many minutes of inactivity (default: 30).', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'number',
					'min'         => 5,
					'max'         => 120,
					'default'     => 30,
				),
				'cookie_duration'           => array(
					'label'       => __( 'Cookie Duration (days)', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'How long the visitor identification cookie persists (730 = 2 years).', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'number',
					'min'         => 1,
					'max'         => 730,
					'default'     => 730,
				),
				'gdpr_anonymize_ip'         => array(
					'start_section' => __( 'Privacy & Data Anonymization', 'smart-form-builder-by-dragwyb' ),
					'label'         => __( 'Anonymize IP Addresses', 'smart-form-builder-by-dragwyb' ),
					'description'   => __( 'Removes the last octet of IPv4 addresses (e.g. 192.168.1.100 becomes 192.168.1.0) before storing.', 'smart-form-builder-by-dragwyb' ),
					'type'          => 'bool',
					'default'       => false,
				),
				'gdpr_respect_dnt'          => array(
					'label'       => __( 'Respect Do Not Track (DNT) Browser Header', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'When enabled, tracking is completely disabled for visitors whose browser sends the DNT header.', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'bool',
					'default'     => false,
				),
				'gdpr_disable_user_cookies' => array(
					'label'       => __( 'Disable Tracking Cookies', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'Prevents the plugin from setting visitor identification cookies. Tracking operates per-session without tracking returning visitors.', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'bool',
					'default'     => false,
				),
				'gdpr_disable_user_details' => array(
					'label'       => __( 'Disable User Details Collection', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'Prevents recording IP address, user agent, and browser/OS details with form submissions and pageviews.', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'bool',
					'default'     => false,
				),
				'gdpr_data_retention_days'  => array(
					'label'       => __( 'Auto-delete tracking data after (days)', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'Automatically delete visitor tracking data older than this many days (0 = keep forever).', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'number',
					'min'         => 0,
					'max'         => 3650,
					'default'     => 0,
				),
				'gdpr_retain_entries'       => array(
					'label'       => __( 'Retain Form Entries When Cleaning Tracking Data', 'smart-form-builder-by-dragwyb' ),
					'description' => __( 'When checked, auto-delete only purges analytics sessions/pageviews but keeps form submission leads.', 'smart-form-builder-by-dragwyb' ),
					'type'        => 'bool',
					'default'     => true,
				),
				'remove_data_on_uninstall'  => array(
					'start_section' => true,
					'class'         => 'dragwyb-container-danger',
					'label'         => __( 'Remove ALL Data on Plugin Uninstall', 'smart-form-builder-by-dragwyb' ),
					'description'   => __( 'Warning: Deleting the plugin will permanently wipe all forms, entries, and analytics tables. Leave unchecked to preserve data across reinstalls.', 'smart-form-builder-by-dragwyb' ),
					'type'          => 'bool',
					'default'       => false,
				),
			),
		);

		$dragwyb_settings  = get_option( 'dragwyb_form_settings', array() );
		$updated_settings  = array();
		$unmasked_settings = array();

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

				$updated_settings[ $dragwyb_setting_type ]  = $dragwyb_default_settings[ $dragwyb_setting_type ];
				$unmasked_settings[ $dragwyb_setting_type ] = array();

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
							$unmasked_settings[ $dragwyb_setting_type ][ $key ] = $drawyb_setting_value;
							$drawyb_setting_value                               = self::mask_api_key( $drawyb_setting_value );
						}

						$updated_settings[ $dragwyb_setting_type ][ $key ]['value'] = $drawyb_setting_value;
					}
				}
			}
		}

		$this->settings          = $updated_settings;
		$this->unmasked_settings = $unmasked_settings;
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

	private function get_unmasked_value( string $type, string $key, $default = '' ) {
		if ( isset( $this->unmasked_settings[ $type ][ $key ] ) && ! empty( $this->unmasked_settings[ $type ][ $key ] ) ) {
			return $this->unmasked_settings[ $type ][ $key ];
		}

		return self::get_setting( $type, $key, $default );
	}

	/**
	 * Safely retrieve an API key, with an option to mask it for secure display.
	 *
	 * @param string $tab  The settings tab key (e.g., 'integrations').
	 * @param string $key  The API key identifier (e.g., 'recaptcha_v3_site_key').
	 * @param bool   $mask Whether to mask the retrieved API key.
	 * @return string
	 */
	final public function get_api_key( string $tab = 'integration', string $key = '', bool $mask = false ): string {
		// Assuming API keys are stored under the 'integrations' tab based on current structure.

		if ( empty( $tab ) || empty( $key ) ) {
			return '';
		}

		$api_settings = $this->get_unmasked_value( $tab, $key, '' );

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
