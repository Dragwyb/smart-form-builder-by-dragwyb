<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Form_Overview;

if (!defined("ABSPATH")) {
    die("You can't access this page");
}


if (!class_exists('Form_Overview')) {
    class Form_Overview
    {
        private static ?self $instance = null;

        public static function instance(): self
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }
        public function __construct()
        {
            add_action('Dragwyb_Menu_Page', [$this, 'render_entries'], 1);
        }

        public function render_entries($screen)
        {
            $screen('form-overview');
            if (gettype($screen) === 'object' && $screen('form-overview')) {
                $this->display_post_entries();
                $this->admin_assets();
            }
        }

        public function admin_assets(): void
        {
            $this->enqueue_admin_assets();
        }

        private function enqueue_admin_assets(): void
        {
            wp_enqueue_script(DRAGWYB_PREFIX . '-overview-assets', esc_url(DRAGWYB_FORM_BUILDER_URL . '/assets/js/dragwyb-oveview-assets.js'), array('jquery'), esc_attr(DRAGWYB_FORM_BUILDER_VERSION), true);

            wp_localize_script(DRAGWYB_PREFIX . '-overview-assets', 'DragwybOverviewPage', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
            ));
        }

        public function display_post_entries(): void
        {
            // Ensure the class is loaded before using it
            if (!class_exists(List_Table::class)) {
                return;
            }

            // Create an instance of the List_Table
            $form_table = List_Table::get_instance();

            // Prepare and display the table
            $form_table->prepare_items();

?>
            <div class="wrap">
                <h1 class="wp-heading-inline"><?php esc_html_e('Form Builder Overview', 'dragwyb-form-builder'); ?></h1>
                <ul class="subsubsub">
                    <?php echo implode(' | ', $form_table->get_views()); ?>
                </ul>
                <form method="get">
                    <!-- Necessary hidden fields for WP_List_Table -->
                    <input type="hidden" name="page" value="<?php echo esc_attr(DRAGWYB_PREFIX) ?>-form-overview">
                    <?php
                    $form_table->search_box('search', 'search_id'); // Add the search box
                    $form_table->display(); // Display the table itself
                    ?>
                </form>
            </div>
<?php
        }
    }
}
