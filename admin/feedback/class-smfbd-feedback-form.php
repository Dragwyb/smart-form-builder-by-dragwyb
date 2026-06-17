<?php

namespace Dragwyb\Form_Builder\Admin\Feedback;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SMFBD_Feedback_Form' ) ) {
	/**
	 * SMFBD_Feedback_Form class.
	 *
	 * This class is responsible for handling the feedback form data and sending it when the plugin is deactivated.
	 */
	class SMFBD_Feedback_Form {

		/**
		 * Holds the single instance of the class.
		 *
		 * @var self|null
		 */
		private static $instance;
		/**
		 * Stores the feedback data to be sent.
		 *
		 * @var array
		 */
		private $feedback_data;
		/**
		 * The URL to send feedback data to.
		 *
		 * @var string
		 */
		private $route;
		/**
		 * The name of the plugin.
		 *
		 * @var string
		 */
		private $plugin_name;
		/**
		 * The slug of the plugin.
		 *
		 * @var string
		 */
		private $plugin_slug;
		/**
		 * The version of the plugin.
		 *
		 * @var string
		 */
		private $plugin_version;

		/**
		 * The instance of the class.
		 *
		 * @var self|null
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof SMFBD_Feedback_Form ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->route          = 'https://feedback.dragwyb.com/wp-json/wpfd/v1/feedback';
			$this->plugin_name    = 'Smart Form Builder';
			$this->plugin_slug    = 'smart-form-builder-by-dragwyb';
			$this->plugin_version = defined( 'DRAGWYB_FORM_BUILDER_VERSION' ) ? DRAGWYB_FORM_BUILDER_VERSION : '1.0.0';
			add_action( 'wp_ajax_smfbd_send_feedback', array( $this, 'smfbd_send_feedback' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );
			add_action( 'admin_head', array( $this, 'display_plugin_installed_form' ) );
		}


		/**
		 * Displays a custom form on the plugin installed page.
		 */
		public function display_plugin_installed_form() {

			$screen = get_current_screen();

			if ( ! current_user_can( 'manage_options' ) || 'plugins' !== $screen->id ) {
				return;
			}

			$deactivation_options = array(
				'plugin_performance_issues'     => array(
					'title'             => __( 'Technical Difficulties with the Plugin', 'smart-form-builder-by-dragwyb' ),
					'input_placeholder' => __( 'Please describe the technical issues you encountered with the plugin', 'smart-form-builder-by-dragwyb' ),
				),
				'alternative_plugin_discovered' => array(
					'title'             => __( 'Switched to a More Suitable Plugin', 'smart-form-builder-by-dragwyb' ),
					'input_placeholder' => __( 'Please specify the alternative plugin you are using instead', 'smart-form-builder-by-dragwyb' ),
				),
				'configuration_challenges'      => array(
					'title'             => __( 'Difficulty with Plugin Configuration', 'smart-form-builder-by-dragwyb' ),
					'input_placeholder' => __( 'Please explain the configuration difficulties you faced', 'smart-form-builder-by-dragwyb' ),
				),
				'temporary_plugin_pause'        => array(
					'title'             => __( 'Temporary Plugin Disablement', 'smart-form-builder-by-dragwyb' ),
					'input_placeholder' => __( 'Please state the reason for temporarily disabling the plugin', 'smart-form-builder-by-dragwyb' ),
				),
				'other_reasons'                 => array(
					'title'             => __( 'Other Reasons for Deactivation', 'smart-form-builder-by-dragwyb' ),
					'input_placeholder' => __( 'Please provide additional information about your reason for deactivating the plugin', 'smart-form-builder-by-dragwyb' ),
				),
			);

			echo '<div class="smfbd-deactivate-feedback-form-wrapper smfbd-form-hide" data-slug="' . esc_attr( $this->plugin_slug ) . '">';
			echo '<div class="smfbd-deactivate-feedback-form">';
			echo '<h2>' . esc_html__( 'Request Plugin Feedback', 'smart-form-builder-by-dragwyb' ) . '</h2>';
			echo '<span class="dashicons dashicons-no smfbd-deactivate-close"></span>';
			echo '<form method="post">';
			echo '<input type="hidden" name="action" value="smfbd_send_feedback" />';
			echo '<hr>';
			echo '<div class="form-body">';
			echo '<h4>' . esc_html__( 'Your feedback is invaluable to us. If you have a moment, kindly let us know why you are deactivating this plugin.', 'smart-form-builder-by-dragwyb' ) . '</h4>';
			echo '<div id="empty-field-msg"><p>' . esc_html__( '!Please select a reason for your feedback before submitting the form.', 'smart-form-builder-by-dragwyb' ) . '</p></div>';
			wp_nonce_field( 'smfbd_send_feedback_nonce', 'smfbd_send_feedback_nonce' );
			foreach ( $deactivation_options as $key => $option ) {
				echo '<div class="form-group">';
				echo '<input type="radio" id="' . esc_attr( $key ) . '" name="reason" value="' . esc_attr( $key ) . '">';
				echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $option['title'] ) . '</label>';
				if ( 'temporary_plugin_pause' !== $key ) {
					echo '<textarea name="message" id="message" placeholder="' . esc_attr( $option['input_placeholder'] ) . '"></textarea>';
				}
				echo '</div>';
			}
			echo '<div class="form-group">';
			echo '<input type="checkbox" id="confirm" name="confirm">';
			echo '<label for="confirm">' . wp_kses_post(
				sprintf(
					// translators: %1$s replaced with Admin Email placeholder Text, %2$s replaced with Site Url placeholder Text, %1$s replaced with Plugin Version placeholder Text, %1$s replaced with WP/PHP Versions placeholder Text
					__( 'By submitting, you agree to share your %1$s, %2$s, %3$s, and %4$s to help improve the plugin. Your data will remain private.', 'smart-form-builder-by-dragwyb' ),
					'<strong>' . esc_html__( 'Admin Email', 'smart-form-builder-by-dragwyb' ) . '</strong>',
					'<strong>' . esc_html__( 'Site URL', 'smart-form-builder-by-dragwyb' ) . '</strong>',
					'<strong>' . esc_html__( 'Plugin Version', 'smart-form-builder-by-dragwyb' ) . '</strong>',
					'<strong>' . esc_html__( 'WP/PHP Versions', 'smart-form-builder-by-dragwyb' ) . '</strong>'
				),
				'smart-form-builder-by-dragwyb'
			) . '</label>';
			echo '</div>';
			echo '</div>';
			echo '<hr>';
			echo '<div class="smfbd-button-wrapper">';
			echo '<button type="submit" class="button button-feedback">' . esc_html__( 'Submit Feedback', 'smart-form-builder-by-dragwyb' ) . '</button>';
			echo '<button type="submit" class="button button-primary">' . esc_html__( 'Skip Feedback', 'smart-form-builder-by-dragwyb' ) . '</button>';
			echo '</div>';
			echo '</form>';
			echo '</div>';
			echo '</div>';
		}

		/**
		 * Enqueues styles and scripts on the plugin installed page only.
		 */
		public function enqueue_styles_and_scripts() {
			wp_enqueue_style( 'smfbd-deactivate-styles', plugin_dir_url( __FILE__ ) . 'assets/css/smfbd-feedback-form.min.css', array(), esc_attr( $this->plugin_version ), 'all' );
			wp_enqueue_script( 'smfbd-deactivate-scripts', plugin_dir_url( __FILE__ ) . 'assets/js/smfbd-feedback-form.min.js', array( 'jquery' ), esc_attr( $this->plugin_version ), true );

			wp_localize_script(
				'smfbd-deactivate-scripts',
				'smfbdFeedbackData',
				array(
					'ajax_url'    => admin_url( 'admin-ajax.php' ),
					'pluing_slug' => esc_attr( $this->plugin_slug ),
				)
			);
		}

		/**
		 * Sends feedback data via AJAX.
		 */
		public function smfbd_send_feedback() {
			if ( isset( $_POST['action'] ) && 'smfbd_send_feedback' === $_POST['action'] ) {
				check_ajax_referer( 'smfbd_send_feedback_nonce', 'nonce' );

				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( 'Unauthorized' );
				}

				$wordpress_version = get_bloginfo( 'version' );
				$php_version       = phpversion();

				$this->feedback_data = array(
					'plugin_name'     => esc_attr( $this->plugin_name ),
					'plugin_version'  => esc_attr( $this->plugin_version ),
					'email'           => get_option( 'admin_email' ),
					'website_url'     => home_url(),
					'deactive_reason' => isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : 'N/A',
					'plugin_slug'     => esc_attr( $this->plugin_slug ),
					'wp_version'      => esc_attr( $wordpress_version ),
					'php_version'     => esc_attr( $php_version ),
				);

				$route_url = esc_url( $this->route );

				$this->feedback_data['message'] = isset( $_POST['message'] ) && ! empty( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : 'N/A';

				$response = wp_remote_get(
					$route_url,
					array(
						'body'    => $this->feedback_data,
						'timeout' => 30,
					)
				);

				if ( is_wp_error( $response ) ) {
					$error_message = $response->get_error_message();
					wp_send_json_error( $error_message );
				}

				wp_send_json_success();
			}
		}
	}

	SMFBD_Feedback_Form::get_instance();
}
