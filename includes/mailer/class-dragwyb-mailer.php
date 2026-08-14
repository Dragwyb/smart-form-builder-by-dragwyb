<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Mailer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Admin\Settings\Settings_Manager;

class Dragwyb_Mailer {

	/**
	 * Singleton instance.
	 *
	 * @var Dragwyb_Mailer|null
	 */
	private static ?self $instance = null;

	/**
	 * Flag indicating whether a Smart Form Builder email is currently being sent.
	 *
	 * @var bool
	 */
	private static bool $sending_plugin_email = false;

	/**
	 * Get singleton instance.
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
	private function __construct() {
		add_action( 'phpmailer_init', array( $this, 'configure_phpmailer' ) );
		add_action( 'wp_ajax_dragwyb_smtp_test', array( $this, 'handle_ajax_test_email' ) );
	}

	/**
	 * Check if SMTP is enabled and configured.
	 *
	 * @return bool
	 */
	public static function is_configured(): bool {
		$enabled = Settings_Manager::instance()->get_setting( 'smtp', 'smtp_enabled', false );
		$host    = Settings_Manager::instance()->get_setting( 'smtp', 'smtp_host', '' );

		return ( true === $enabled || 'yes' === $enabled ) && ! empty( $host );
	}

	/**
	 * Send an email through wp_mail with SMTP scoping applied.
	 *
	 * @param string       $to
	 * @param string       $subject
	 * @param string       $message
	 * @param string|array $headers
	 * @param array        $attachments
	 * @return bool
	 */
	public static function send( string $to, string $subject, string $message, $headers = '', array $attachments = array() ): bool {
		self::$sending_plugin_email = true;
		$result                     = wp_mail( $to, $subject, $message, $headers, $attachments );
		self::$sending_plugin_email = false;
		return $result;
	}

	/**
	 * Configure PHPMailer for SMTP delivery.
	 *
	 * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer
	 * @return void
	 */
	public function configure_phpmailer( $phpmailer ): void {
		if ( ! self::is_configured() ) {
			return;
		}

		$apply_to_all = Settings_Manager::instance()->get_setting( 'smtp', 'smtp_apply_to_all', false );
		if ( ! self::$sending_plugin_email && true !== $apply_to_all && 'yes' !== $apply_to_all ) {
			return;
		}

		$settings = Settings_Manager::instance()->get_settings( 'smtp' );

		$host       = isset( $settings['smtp_host']['value'] ) ? (string) $settings['smtp_host']['value'] : '';
		$port       = isset( $settings['smtp_port']['value'] ) ? absint( $settings['smtp_port']['value'] ) : 587;
		$encryption = isset( $settings['smtp_encryption']['value'] ) ? (string) $settings['smtp_encryption']['value'] : 'tls';
		$auth       = isset( $settings['smtp_auth']['value'] ) ? (bool) $settings['smtp_auth']['value'] : true;
		$username   = isset( $settings['smtp_username']['value'] ) ? (string) $settings['smtp_username']['value'] : '';
		$from_email = isset( $settings['smtp_from_email']['value'] ) ? (string) $settings['smtp_from_email']['value'] : '';
		$from_name  = isset( $settings['smtp_from_name']['value'] ) ? (string) $settings['smtp_from_name']['value'] : '';

		$phpmailer->isSMTP();
		$phpmailer->Host = $host;
		$phpmailer->Port = $port > 0 ? $port : 587;

		if ( 'tls' === $encryption ) {
			$phpmailer->SMTPSecure = 'tls';
		} elseif ( 'ssl' === $encryption ) {
			$phpmailer->SMTPSecure = 'ssl';
		} else {
			$phpmailer->SMTPSecure  = '';
			$phpmailer->SMTPAutoTLS = false;
		}

		if ( $auth ) {
			$phpmailer->SMTPAuth = true;
			$phpmailer->Username = $username;
			$phpmailer->Password = self::get_password();
		} else {
			$phpmailer->SMTPAuth = false;
		}

		if ( ! empty( $from_email ) && is_email( $from_email ) ) {
			$phpmailer->setFrom( $from_email, ! empty( $from_name ) ? $from_name : $phpmailer->FromName, false );
		}
	}

	/**
	 * Retrieve stored password or constant override.
	 *
	 * @return string
	 */
	public static function get_password(): string {
		if ( defined( 'DRAGWYB_SMTP_PASSWORD' ) ) {
			return (string) DRAGWYB_SMTP_PASSWORD;
		}
		return (string) Settings_Manager::instance()->get_setting( 'smtp', 'smtp_password', '' );
	}

	/**
	 * Check if password is fixed via constant.
	 *
	 * @return bool
	 */
	public static function password_is_constant(): bool {
		return defined( 'DRAGWYB_SMTP_PASSWORD' );
	}

	/**
	 * Send a test email and return diagnostic result array.
	 *
	 * @param string $to
	 * @return array
	 */
	public static function send_test_email( string $to ): array {
		if ( ! is_email( $to ) ) {
			return array(
				'success' => false,
				'message' => __( 'Please enter a valid email address.', 'smart-form-builder-by-dragwyb' ),
			);
		}

		if ( ! self::is_configured() ) {
			return array(
				'success' => false,
				'message' => __( 'Please enable SMTP and provide a Host first.', 'smart-form-builder-by-dragwyb' ),
			);
		}

		$error_message = '';
		$capture_error = function ( $wp_error ) use ( &$error_message ) {
			if ( is_wp_error( $wp_error ) ) {
				$error_message = $wp_error->get_error_message();
			}
		};
		add_action( 'wp_mail_failed', $capture_error );

		$smtp_log   = array();
		$debug_hook = function ( $phpmailer ) use ( &$smtp_log ) {
			$phpmailer->SMTPDebug   = 2;
			$phpmailer->Debugoutput = function ( $str ) use ( &$smtp_log ) {
				$smtp_log[] = trim( (string) $str );
			};
		};
		add_action( 'phpmailer_init', $debug_hook, 999 );

		$subject = __( 'Smart Form Builder SMTP Test', 'smart-form-builder-by-dragwyb' );
		$body    = sprintf(
			/* translators: %s: Site URL */
			__( "Success! This test email was sent through your SMTP server by Smart Form Builder on %s.\n\nYour form notification emails will be delivered the same way.", 'smart-form-builder-by-dragwyb' ),
			home_url()
		);

		$sent = self::send( $to, $subject, $body );

		remove_action( 'wp_mail_failed', $capture_error );
		remove_action( 'phpmailer_init', $debug_hook, 999 );

		if ( $sent ) {
			return array(
				'success' => true,
				'message' => __( 'Test email sent successfully. Please check your inbox.', 'smart-form-builder-by-dragwyb' ),
			);
		}

		$server_lines = array();
		foreach ( $smtp_log as $line ) {
			if ( preg_match( '/SERVER -> CLIENT: (5\d\d[\s-].*)/', $line, $m ) ) {
				$server_lines[] = trim( $m[1] );
			}
		}
		$server_lines = array_slice( array_unique( $server_lines ), -2 );
		$detail       = ! empty( $server_lines ) ? ' — ' . implode( ' | ', $server_lines ) : '';

		$hint       = '';
		$transcript = implode( "\n", $smtp_log );
		if ( false !== stripos( $transcript, 'BadCredentials' ) || false !== stripos( $transcript, 'support.google.com' ) ) {
			$hint = ' ' . __( 'Gmail requires an App Password (Google Account → Security → 2-Step Verification → App passwords).', 'smart-form-builder-by-dragwyb' );
		} elseif ( false !== stripos( $transcript, 'SmtpClientAuthentication is disabled' ) ) {
			$hint = ' ' . __( 'Microsoft 365 has SMTP AUTH disabled. An admin must enable "Authenticated SMTP" in M365 admin center.', 'smart-form-builder-by-dragwyb' );
		} elseif ( false !== stripos( $transcript, '535' ) ) {
			$hint = ' ' . __( 'The server rejected username/password. Verify your credentials.', 'smart-form-builder-by-dragwyb' );
		}

		return array(
			'success' => false,
			'message' => ( $error_message ? sprintf( __( 'Send failed: %s', 'smart-form-builder-by-dragwyb' ), $error_message ) : __( 'Send failed.', 'smart-form-builder-by-dragwyb' ) ) . $detail . $hint,
		);
	}

	/**
	 * AJAX endpoint handler for sending test emails from settings UI.
	 */
	public function handle_ajax_test_email(): void {
		check_ajax_referer( 'dragwyb_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$to  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$res = self::send_test_email( $to );
		if ( $res['success'] ) {
			wp_send_json_success( array( 'message' => $res['message'] ) );
		} else {
			wp_send_json_error( array( 'message' => $res['message'] ) );
		}
	}
}
