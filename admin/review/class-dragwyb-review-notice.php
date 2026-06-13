<?php

namespace Dragwyb\Form_Builder\Admin\Review;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Dragwyb_Review_Notice' ) ) {

	/**
	 * Dragwyb_Review_Notice class
	 */
	class Dragwyb_Review_Notice {

		/**
		 * The single instance of the class.
		 *
		 * @var Dragwyb_Review_Notice
		 */
		private static $instance;

		/**
		 * Returns the single instance of the class.
		 *
		 * @return Dragwyb_Review_Notice
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Dragwyb_Review_Notice constructor.
		 */
		public function __construct() {
			add_action( 'admin_notices', array( $this, 'print_admin_notice' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_action( 'wp_ajax_smfbd_review_dismiss', array( $this, 'smfbd_review_dismiss' ) );
		}

		/**
		 * Prints the admin notice.
		 */
		public function print_admin_notice() {
			$installation_date = get_option( 'dragwyb_form_builder_install_data' );

			// Fallback: set installation date if it doesn't exist
			if ( false === $installation_date ) {
				$installation_date = gmdate( 'Y-m-d H:i:s' );
				update_option( 'dragwyb_form_builder_install_data', $installation_date );
				return; // don't show the notice immediately
			}

			$installed_timestamp = strtotime( $installation_date );
			$current_timestamp   = time();

			$day_in_seconds = 86400;

			// Show notice only if 3 or more days have passed
			if ( ( $current_timestamp - $installed_timestamp ) < ( 3 * $day_in_seconds ) ) {
				// return;
			}

			printf(
				'<div class="notice notice-info is-dismissible smfbd-review-notice" style="padding: 1rem;">
			<h2 style="margin: 0px">%s</h2>
			<p>%s</p>
			<div class="smfbd-review-notice-buttons">
				<a href="' . esc_url( 'https://wordpress.org/support/plugin/smart-form-builder-by-dragwyb/reviews/' ) . '" class="smfbd-review-notice-button button" target="_blank" >%s</a>
				<button type="button" class="smfbd-review-notice-button button">%s</button>
			</div>
		</div>',
				esc_html__( 'Thank you for using Smart Form Builder.', 'smart-form-builder-by-dragwyb' ),
				sprintf( esc_html__( 'Enjoying the Ultimate Flipbox Addon for Elementor? Your feedback is invaluable in shaping the plugin\'s future.%sPlease consider leaving a review on the WordPress Plugin Directory to help others and support our growth.', 'ultimate-flipbox-addon-for-elementor' ), '<br>' ),
				esc_html__( 'Leave a Review', 'smart-form-builder-by-dragwyb' ),
				esc_html__( 'Already Review.', 'smart-form-builder-by-dragwyb' )
			);
		}

		/**
		 * Enqueues admin scripts and styles.
		 */
		public function enqueue_admin_scripts() {
			wp_enqueue_script( 'smfbd-review-script', esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/review-notice.min.js' ), array( 'jquery' ), esc_attr( DRAGWYB_FORM_BUILDER_VERSION ), true );
			wp_enqueue_style( 'smfbd-review-style', esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/review-notice.min.css' ), array(), esc_attr( DRAGWYB_FORM_BUILDER_VERSION ) );

			wp_localize_script(
				'smfbd-review-script',
				'smfbd_review_obj',
				array(
					'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ),
					'nonce'    => esc_attr( wp_create_nonce( 'smfbd-review-nonce' ) ),
				)
			);
		}

		/**
		 * Dismisses the review notice.
		 */
		public function smfbd_review_dismiss() {
			check_ajax_referer( 'smfbd-review-nonce', 'nonce' );

			if ( isset( $_POST['smfbd_review_dismiss'] ) ) {
					update_option( 'dragwyb_form_builder_already_reviewd', true );
			}

			exit;
		}
	}
}
