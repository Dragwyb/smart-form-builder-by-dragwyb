<?php

namespace Dragwyb\Form_Builder\Admin\Feedback;

/**
 * SMFBD_Feedback_Form class.
 *
 * Handles plugin deactivation feedback.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SMFBD_Feedback_Form' ) ) {

	/**
	 * SMFBD_Feedback_Form class.
	 */
	class SMFBD_Feedback_Form {

		/**
		 * Single instance.
		 *
		 * @var self|null
		 */
		private static $instance = null;

		/**
		 * Feedback API route.
		 *
		 * @var string
		 */
		private $route;

		/**
		 * Plugin name.
		 *
		 * @var string
		 */
		private $plugin_name;

		/**
		 * Plugin slug.
		 *
		 * @var string
		 */
		private $plugin_slug;

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		private $plugin_version;

		/**
		 * Get instance.
		 *
		 * @return self
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->route = esc_url( 'https://feedback.dragwyb.com/wp-json/wpfd/v1/feedback' );

			$this->plugin_name = 'Smart Form Builder';

			$this->plugin_slug = 'smart-form-builder-by-dragwyb';

			$this->plugin_version = defined( 'DRAGWYB_FORM_BUILDER_VERSION' ) ? DRAGWYB_FORM_BUILDER_VERSION : '1.0.0';

			add_action( 'wp_ajax_smfbd_send_feedback', array( $this, 'smfbd_send_feedback' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );

			add_action( 'admin_head', array( $this, 'display_plugin_installed_form' ) );
		}

		/**
		 * Display feedback form on Plugins screen.
		 *
		 * @return void
		 */
		public function display_plugin_installed_form() {

			$screen = get_current_screen();

			if (
				! $screen ||
				'plugins' !== $screen->id ||
				! current_user_can( 'manage_options' )
			) {
				return;
			}

			$deactivation_options = array(

				'plugin_performance_issues'     => array(
					'title'             => __(
						'Technical difficulties with the plugin',
						'smart-form-builder-by-dragwyb'
					),
					'input_placeholder' => __(
						'Please describe the technical issues you encountered.',
						'smart-form-builder-by-dragwyb'
					),
				),

				'alternative_plugin_discovered' => array(
					'title'             => __(
						'Switched to another plugin',
						'smart-form-builder-by-dragwyb'
					),
					'input_placeholder' => __(
						'Which plugin are you using instead?',
						'smart-form-builder-by-dragwyb'
					),
				),

				'configuration_challenges'      => array(
					'title'             => __(
						'Difficulty with plugin configuration',
						'smart-form-builder-by-dragwyb'
					),
					'input_placeholder' => __(
						'What configuration issue did you experience?',
						'smart-form-builder-by-dragwyb'
					),
				),

				'temporary_plugin_pause'        => array(
					'title'             => __(
						'Temporarily disabling the plugin',
						'smart-form-builder-by-dragwyb'
					),
					'input_placeholder' => '',
				),

				'other_reasons'                 => array(
					'title'             => __(
						'Other reason',
						'smart-form-builder-by-dragwyb'
					),
					'input_placeholder' => __(
						'Please tell us why you are deactivating the plugin.',
						'smart-form-builder-by-dragwyb'
					),
				),
			);
			?>

			<div
				class="smfbd-deactivate-feedback-form-wrapper smfbd-form-hide"
				data-slug="<?php echo esc_attr( $this->plugin_slug ); ?>"
			>

				<div class="smfbd-deactivate-feedback-form">

					<div class="smfbd-feedback-header">

						<div class="smfbd-feedback-header-content">

							<h2>
								<?php
								echo esc_html__(
									'Help us improve',
									'smart-form-builder-by-dragwyb'
								);
								?>
							</h2>

							<p>
								<?php
								echo esc_html__(
									'Why are you deactivating this plugin?',
									'smart-form-builder-by-dragwyb'
								);
								?>
							</p>

						</div>

						<button
							type="button"
							class="smfbd-deactivate-close"
							aria-label="<?php echo esc_attr__( 'Close feedback form', 'smart-form-builder-by-dragwyb' ); ?>"
						>
							<span class="dashicons dashicons-no-alt"></span>
						</button>

					</div>

					<form
						method="post"
						class="smfbd-feedback-form"
					>

						<?php
						wp_nonce_field(
							'smfbd_send_feedback_nonce',
							'smfbd_send_feedback_nonce'
						);
						?>

						<div class="smfbd-feedback-body">

							<div
								id="smfbd-empty-field-msg"
								class="smfbd-message"
								role="alert"
								aria-live="polite"
							>
								<?php
								echo esc_html__(
									'Please select a reason before submitting your feedback.',
									'smart-form-builder-by-dragwyb'
								);
								?>
							</div>

							<div class="smfbd-section-label">
								<?php
								echo esc_html__(
									'Select a reason',
									'smart-form-builder-by-dragwyb'
								);
								?>
							</div>

							<div class="smfbd-reasons">

								<?php foreach ( $deactivation_options as $key => $option ) : ?>

									<label class="smfbd-reason">

										<input
											type="radio"
											name="reason"
											value="<?php echo esc_attr( $key ); ?>"
											data-placeholder="<?php echo esc_attr( $option['input_placeholder'] ); ?>"
										/>

										<span
											class="smfbd-radio-ui"
											aria-hidden="true"
										></span>

										<span class="smfbd-reason-label">
											<?php echo esc_html( $option['title'] ); ?>
										</span>

									</label>

								<?php endforeach; ?>

							</div>

							<div
								class="smfbd-message-section"
								hidden
							>

								<div class="smfbd-section-label">
									<?php
									echo esc_html__(
										'Tell us more',
										'smart-form-builder-by-dragwyb'
									);
									?>

									<span class="smfbd-optional">
										<?php
										echo esc_html__(
											'(Optional)',
											'smart-form-builder-by-dragwyb'
										);
										?>
									</span>
								</div>

								<textarea
									id="smfbd-feedback-message"
									name="message"
									rows="3"
									maxlength="1000"
									placeholder="<?php echo esc_attr__( 'Tell us more about your experience...', 'smart-form-builder-by-dragwyb' ); ?>"
								></textarea>

								<div class="smfbd-character-count">
									<span class="smfbd-character-current">0</span>/1000
								</div>

							</div>

							<div class="smfbd-diagnostics">

								<label
									class="smfbd-diagnostics-label"
									for="smfbd-share-diagnostics"
								>

									<input
										type="checkbox"
										id="smfbd-share-diagnostics"
										name="share_diagnostics"
										value="1"
									/>

									<span
										class="smfbd-checkbox-ui"
										aria-hidden="true"
									></span>

									<span class="smfbd-diagnostics-content">

										<strong>
											<?php
											echo esc_html__(
												'Share technical details for debugging',
												'smart-form-builder-by-dragwyb'
											);
											?>

											<span class="smfbd-optional">
												<?php
												echo esc_html__(
													'(Optional)',
													'smart-form-builder-by-dragwyb'
												);
												?>
											</span>
										</strong>

										<small>
											<?php
											echo esc_html__(
												'If enabled, we may collect your admin email, site URL, plugin version, WordPress version, and PHP version to help diagnose issues.',
												'smart-form-builder-by-dragwyb'
											);
											?>

											<a
												href="<?php echo esc_url( 'https://dragwyb.com/privacy-policy/' ); ?>"
												target="_blank"
												rel="noopener noreferrer"
											>
												<?php
												echo esc_html__(
													'Read more',
													'smart-form-builder-by-dragwyb'
												);
												?>
											</a>
										</small>

									</span>

								</label>

							</div>

						</div>

						<div class="smfbd-feedback-footer">

							<button
								type="button"
								class="button smfbd-button-skip"
							>
								<?php
								echo esc_html__(
									'Skip & Deactivate',
									'smart-form-builder-by-dragwyb'
								);
								?>
							</button>

							<button
								type="submit"
								class="button button-primary smfbd-button-feedback"
							>

								<span class="smfbd-button-text">
									<?php
									echo esc_html__(
										'Submit Feedback',
										'smart-form-builder-by-dragwyb'
									);
									?>
								</span>

								<span
									class="smfbd-button-spinner"
									aria-hidden="true"
								></span>

							</button>

						</div>

					</form>

				</div>

			</div>

			<?php
		}

		/**
		 * Enqueue assets.
		 *
		 * @return void
		 */
		public function enqueue_styles_and_scripts() {

			$screen = get_current_screen();

			if (
				! $screen ||
				'plugins' !== $screen->id ||
				! current_user_can( 'manage_options' )
			) {
				return;
			}

			wp_enqueue_style(
				'smfbd-deactivate-styles',
				plugin_dir_url( __FILE__ ) . 'assets/css/smfbd-feedback-form.min.css',
				array(),
				$this->plugin_version
			);

			wp_enqueue_script(
				'smfbd-deactivate-scripts',
				plugin_dir_url( __FILE__ ) . 'assets/js/smfbd-feedback-form.min.js',
				array( 'jquery' ),
				$this->plugin_version,
				true
			);

			wp_localize_script(
				'smfbd-deactivate-scripts',
				'smfbdFeedbackData',
				array(
					'ajax_url'    => admin_url( 'admin-ajax.php' ),
					'plugin_slug' => $this->plugin_slug,
				)
			);
		}

		/**
		 * Process feedback.
		 *
		 * @return void
		 */
		public function smfbd_send_feedback() {

			if ( ! current_user_can( 'manage_options' ) ) {

				wp_send_json_error(
					array(
						'message' => __(
							'You do not have permission to submit feedback.',
							'smart-form-builder-by-dragwyb'
						),
					),
					403
				);
			}

			check_ajax_referer(
				'smfbd_send_feedback_nonce',
				'nonce'
			);

			$allowed_reasons = array(
				'plugin_performance_issues',
				'alternative_plugin_discovered',
				'configuration_challenges',
				'temporary_plugin_pause',
				'other_reasons',
			);

			$reason = isset( $_POST['reason'] )
				? sanitize_key( wp_unslash( $_POST['reason'] ) )
				: '';

			if ( ! in_array( $reason, $allowed_reasons, true ) ) {

				wp_send_json_error(
					array(
						'message' => __(
							'Please select a valid feedback reason.',
							'smart-form-builder-by-dragwyb'
						),
					),
					400
				);
			}

			$message = isset( $_POST['message'] )
				? sanitize_textarea_field(
					wp_unslash( $_POST['message'] )
				)
				: '';

			/*
			 * Optional technical-details consent.
			 *
			 * Only collect technical/site information when
			 * the user explicitly enables this option.
			 */
			$share_diagnostics = isset( $_POST['share_diagnostics'] )
				&& '1' === sanitize_text_field(
					wp_unslash( $_POST['share_diagnostics'] )
				);

			/*
			 * Always send basic feedback information.
			 */
			$this->feedback_data = array(
				'plugin_name'     => sanitize_text_field( $this->plugin_name ),
				'plugin_slug'     => sanitize_text_field( $this->plugin_slug ),
				'plugin_version'  => sanitize_text_field( $this->plugin_version ),
				'deactive_reason' => $reason,
				'message'         => $message,
			);

			/*
			 * Only send technical/site information when
			 * the user explicitly checks the optional checkbox.
			 */
			if ( $share_diagnostics ) {

				$this->feedback_data['email'] = sanitize_email(
					get_option( 'admin_email' )
				);

				$this->feedback_data['website_url'] = esc_url_raw(
					home_url()
				);

				$this->feedback_data['plugin_version'] = sanitize_text_field(
					$this->plugin_version
				);

				$this->feedback_data['wp_version'] = sanitize_text_field(
					get_bloginfo( 'version' )
				);

				$this->feedback_data['php_version'] = sanitize_text_field(
					PHP_VERSION
				);
			}

			$response = wp_remote_post(
				$this->route,
				array(
					'timeout' => 15,
					'body'    => $this->feedback_data,
					'headers' => array(
						'Accept' => 'application/json',
					),
				)
			);

			if ( is_wp_error( $response ) ) {

				wp_send_json_error(
					array(
						'message' => __(
							'Unable to send feedback. Please try again.',
							'smart-form-builder-by-dragwyb'
						),
					),
					500
				);
			}

			$status_code = wp_remote_retrieve_response_code( $response );

			if (
				$status_code < 200 ||
				$status_code >= 300
			) {

				wp_send_json_error(
					array(
						'message' => __(
							'The feedback server could not process your request.',
							'smart-form-builder-by-dragwyb'
						),
					),
					500
				);
			}

			wp_send_json_success(
				array(
					'message' => __(
						'Thank you for your feedback.',
						'smart-form-builder-by-dragwyb'
					),
				)
			);
		}
	}

	SMFBD_Feedback_Form::get_instance();
}
