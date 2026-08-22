<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Form_Overview;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Form_Overview' ) ) {
	class Form_Overview {

		/**
		 * Singleton instance.
		 *
		 * @var self|null
		 */
		private static $instance = null;

		public static function instance(): self {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
		public function __construct() {
			add_action( 'Dragwyb_Menu_Page', array( $this, 'render_entries' ), 1 );
		}

		public function render_entries( $screen ) {
			if ( gettype( $screen ) === 'object' && $screen( 'form-overview' ) ) {
				$this->display_post_entries();
				$this->admin_assets();
			}
		}

		public function admin_assets(): void {
			$this->enqueue_admin_assets();
		}

		private function enqueue_admin_assets(): void {
			wp_enqueue_style( 'dashicons' );
			wp_enqueue_script( DRAGWYB_PREFIX . '-overview-assets', esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/dragwyb-overview-assets.js' ), array( 'jquery' ), esc_attr( DRAGWYB_FORM_BUILDER_VERSION ), true );

			wp_localize_script(
				DRAGWYB_PREFIX . '-overview-assets',
				'DragwybOverviewPage',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				)
			);

			wp_enqueue_style(
				'dragwyb-form-editor-global',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/editor-global.css' ),
				array(),
				esc_attr( DRAGWYB_FORM_BUILDER_VERSION )
			);
		}

		public function display_post_entries(): void {
			// Ensure the class is loaded before using it
			if ( ! class_exists( List_Table::class ) ) {
				return;
			}

			// Create an instance of the List_Table
			$form_table = List_Table::get_instance();

			// Prepare and display the table
			$form_table->prepare_items();
			$logo_url    = DRAGWYB_FORM_BUILDER_URL . 'assets/img/menu-logo.svg';
			$builder_url = admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-form-builder' );
			?>

			<!-- Header Row Card -->
			<div class="dragwyb-dashboard-header">
				<div class="dragwyb-db-brand">
					<div class="dragwyb-db-logo">
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="Smart Form Builder Logo" />
					</div>
					<div class="dragwyb-header-title-meta">
						<h1 class="dragwyb-db-brand-name"><?php esc_html_e( 'All Forms', 'smart-form-builder-by-dragwyb' ); ?></h1>
						<p class="dragwyb-db-sub-title"><?php esc_html_e( 'Manage and analyze your forms performance', 'smart-form-builder-by-dragwyb' ); ?></p>
					</div>
				</div>
				<div class="dragwyb-db-header-actions">
					<a href="<?php echo esc_url( $builder_url ); ?>" class="dragwyb-btn-primary-add">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
							<line x1="12" y1="5" x2="12" y2="19"></line>
							<line x1="5" y1="12" x2="19" y2="12"></line>
						</svg>
						<?php esc_html_e( 'Add Form', 'smart-form-builder-by-dragwyb' ); ?>
					</a>
				</div>
			</div>

			<div class="wrap dragwyb-overview-wrap">

				<!-- Table Section Container -->
				<div class="dragwyb-overview-table-card">
					<ul class="subsubsub">
						<?php
						echo wp_kses(
							implode( ' | ', $form_table->get_views() ),
							array(
								'a'    => array(
									'href'  => array(),
									'class' => array(),
									'id'    => array(),
									'title' => array(),
								),
								'span' => array( 'class' => array() ),
							)
						);
						?>
					</ul>
					<form method="get">
						<input type="hidden" name="page" value="<?php echo esc_attr( DRAGWYB_PREFIX ); ?>-form-overview">
						<?php
						// phpcs:ignore WordPress.Security.NonceVerification.Recommended
						if ( ! empty( $_GET['post_status'] ) ) :
							?>
							<input type="hidden" name="post_status" value="<?php echo esc_attr( sanitize_key( wp_unslash( $_GET['post_status'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>">
						<?php endif; ?>
						<?php
						$form_table->search_box( 'search', 'search_id' );
						$form_table->display();
						?>
					</form>
				</div>
			</div>
			<?php
		}
	}
}
