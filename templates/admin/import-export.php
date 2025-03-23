<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap dragwyb-import-export">
    <h1><?php esc_html_e('Import/Export Forms', 'dragwyb-form-builder'); ?></h1>

    <?php settings_errors(); ?>

    <div class="dragwyb-portability-section">
        <h2><?php esc_html_e('Export Forms', 'dragwyb-form-builder'); ?></h2>
        <p><?php esc_html_e('Select the forms you want to export:', 'dragwyb-form-builder'); ?></p>

        <form method="post" action="">
            <?php wp_nonce_field('dragwyb_export_forms', 'nonce'); ?>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <input type="checkbox" id="forms-select-all">
                        </td>
                        <th><?php esc_html_e('Form Name', 'dragwyb-form-builder'); ?></th>
                        <th><?php esc_html_e('Entries', 'dragwyb-form-builder'); ?></th>
                        <th><?php esc_html_e('Created', 'dragwyb-form-builder'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $form): ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="form_ids[]" value="<?php echo esc_attr($form->ID); ?>">
                            </th>
                            <td>
                                <?php echo esc_html($form->post_title); ?>
                            </td>
                            <td>
                                <?php echo esc_html($this->get_form_entries_count($form->ID)); ?>
                            </td>
                            <td>
                                <?php echo esc_html(get_the_date('', $form->ID)); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="submit">
                <button type="submit" name="dragwyb_export_forms" class="button button-primary">
                    <?php esc_html_e('Export Selected Forms', 'dragwyb-form-builder'); ?>
                </button>
            </p>
        </form>
    </div>

    <div class="dragwyb-portability-section">
        <h2><?php esc_html_e('Import Forms', 'dragwyb-form-builder'); ?></h2>
        <p><?php esc_html_e('Select a form export file to import:', 'dragwyb-form-builder'); ?></p>

        <form method="post" action="" enctype="multipart/form-data">
            <?php wp_nonce_field('dragwyb_import_forms', 'nonce'); ?>
            
            <p>
                <input type="file" name="import_file" accept=".json">
            </p>

            <p class="submit">
                <button type="submit" name="dragwyb_import_forms" class="button button-primary">
                    <?php esc_html_e('Import Forms', 'dragwyb-form-builder'); ?>
                </button>
            </p>
        </form>
    </div>

    <div class="dragwyb-portability-section">
        <h2><?php esc_html_e('Form Templates', 'dragwyb-form-builder'); ?></h2>
        <p><?php esc_html_e('Save forms as templates or create new forms from existing templates:', 'dragwyb-form-builder'); ?></p>

        <div class="dragwyb-templates-grid">
            <?php foreach ($templates as $template): ?>
                <div class="template-card">
                    <h3><?php echo esc_html($template['name']); ?></h3>
                    <p><?php echo esc_html($template['description']); ?></p>
                    <div class="template-actions">
                        <button type="button" class="button use-template" data-template-id="<?php echo esc_attr($template['id']); ?>">
                            <?php esc_html_e('Use Template', 'dragwyb-form-builder'); ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div> 