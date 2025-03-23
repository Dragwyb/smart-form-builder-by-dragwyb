<?php if (!defined('ABSPATH')) exit; ?>

<div class="dragwyb-export-section">
    <h2><?php esc_html_e('Export Data', 'dragwyb-form-builder'); ?></h2>
    
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="dragwyb_export_submissions">
        <?php wp_nonce_field('dragwyb_export_submissions'); ?>

        <div class="export-options">
            <div class="export-field">
                <label for="export-format"><?php esc_html_e('Export Format:', 'dragwyb-form-builder'); ?></label>
                <select name="export_format" id="export-format">
                    <option value="csv">CSV</option>
                    <option value="json">JSON</option>
                    <option value="xlsx">Excel (XLSX)</option>
                </select>
            </div>

            <div class="export-field">
                <label for="export-form"><?php esc_html_e('Select Form:', 'dragwyb-form-builder'); ?></label>
                <select name="form_id" id="export-form">
                    <option value=""><?php esc_html_e('All Forms', 'dragwyb-form-builder'); ?></option>
                    <?php
                    $forms = get_posts([
                        'post_type' => 'dragwyb_form',
                        'posts_per_page' => -1,
                    ]);

                    foreach ($forms as $form) {
                        echo sprintf(
                            '<option value="%d">%s</option>',
                            $form->ID,
                            esc_html($form->post_title)
                        );
                    }
                    ?>
                </select>
            </div>

            <div class="export-field">
                <label for="date-start"><?php esc_html_e('Start Date:', 'dragwyb-form-builder'); ?></label>
                <input type="date" name="date_start" id="date-start">
            </div>

            <div class="export-field">
                <label for="date-end"><?php esc_html_e('End Date:', 'dragwyb-form-builder'); ?></label>
                <input type="date" name="date_end" id="date-end">
            </div>
        </div>

        <button type="submit" class="button button-primary">
            <?php esc_html_e('Export Data', 'dragwyb-form-builder'); ?>
        </button>
    </form>
</div>

<style>
.dragwyb-export-section {
    margin-top: 30px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.export-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.export-field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.export-field label {
    font-weight: 600;
}

.export-field select,
.export-field input {
    width: 100%;
}
</style> 