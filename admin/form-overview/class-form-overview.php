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
			wp_enqueue_script( DRAGWYB_PREFIX . '-overview-assets', esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/dragwyb-oveview-assets.js' ), array( 'jquery' ), esc_attr( DRAGWYB_FORM_BUILDER_VERSION ), true );

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
			?>

			<div class="wrap dragwyb-overview-wrap">
				<!-- Header Row Card -->
				<div class="dragwyb-overview-header-card">
					<div class="dragwyb-header-left">
						<div class="dragwyb-header-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<rect width="24" height="24" rx="6" fill="#FFF0F5"/>
								<path d="M7 6H17M7 10H17M7 14H13M7 18H11" stroke="#E11D48" stroke-width="2" stroke-linecap="round"/>
							</svg>
						</div>
						<div class="dragwyb-header-text">
							<p class="dragwyb-header-title"><?php esc_html_e( 'All Forms', 'smart-form-builder-by-dragwyb' ); ?></p>
							<p class="dragwyb-header-desc"><?php esc_html_e( 'Manage and analyze your forms performance', 'smart-form-builder-by-dragwyb' ); ?></p>
						</div>
					</div>
					<div class="dragwyb-header-right">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-form-builder' ) ); ?>" class="dragwyb-btn-create">
							<span class="dashicons dashicons-plus-alt2"></span>
							<?php esc_html_e( 'Add Form', 'smart-form-builder-by-dragwyb' ); ?>
						</a>
					</div>
				</div>

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
