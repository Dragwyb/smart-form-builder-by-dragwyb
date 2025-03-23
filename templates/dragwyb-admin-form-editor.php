<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="dragwyb-form-builder-wrapper">
    <div class="dragwyb-form-builder-sidebar">
        <div class="dragwyb-fields-panel">
            <h3><?php esc_html_e('Available Fields', 'dragwyb-form-builder'); ?></h3>
            
            <div class="dragwyb-field-types">
                <!-- Basic Fields -->
                <div class="dragwyb-field-group">
                    <h4><?php esc_html_e('Basic Fields', 'dragwyb-form-builder'); ?></h4>
                    <div class="dragwyb-field-buttons">
                        <button type="button" class="dragwyb-add-field" data-type="text">
                            <span class="dashicons dashicons-text"></span>
                            <?php esc_html_e('Text Field', 'dragwyb-form-builder'); ?>
                        </button>
                        <button type="button" class="dragwyb-add-field" data-type="textarea">
                            <span class="dashicons dashicons-editor-paragraph"></span>
                            <?php esc_html_e('Paragraph', 'dragwyb-form-builder'); ?>
                        </button>
                        <button type="button" class="dragwyb-add-field" data-type="email">
                            <span class="dashicons dashicons-email"></span>
                            <?php esc_html_e('Email', 'dragwyb-form-builder'); ?>
                        </button>
                        <button type="button" class="dragwyb-add-field" data-type="number">
                            <span class="dashicons dashicons-calculator"></span>
                            <?php esc_html_e('Number', 'dragwyb-form-builder'); ?>
                        </button>
                    </div>
                </div>

                <!-- Choice Fields -->
                <div class="dragwyb-field-group">
                    <h4><?php esc_html_e('Choice Fields', 'dragwyb-form-builder'); ?></h4>
                    <div class="dragwyb-field-buttons">
                        <button type="button" class="dragwyb-add-field" data-type="radio">
                            <span class="dashicons dashicons-marker"></span>
                            <?php esc_html_e('Radio Buttons', 'dragwyb-form-builder'); ?>
                        </button>
                        <button type="button" class="dragwyb-add-field" data-type="checkbox">
                            <span class="dashicons dashicons-yes"></span>
                            <?php esc_html_e('Checkboxes', 'dragwyb-form-builder'); ?>
                        </button>
                        <button type="button" class="dragwyb-add-field" data-type="select">
                            <span class="dashicons dashicons-menu"></span>
                            <?php esc_html_e('Dropdown', 'dragwyb-form-builder'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dragwyb-form-builder-content">
        <div class="dragwyb-form-preview">
            <div id="dragwyb-form-fields" class="dragwyb-form-fields">
                <?php
                if (!empty($form_data['fields'])) {
                    foreach ($form_data['fields'] as $field) {
                        // Render existing fields
                        // This will be handled by JavaScript on initial load
                    }
                }
                ?>
            </div>
            <div class="dragwyb-form-empty-state <?php echo empty($form_data['fields']) ? '' : 'hidden'; ?>">
                <p><?php esc_html_e('Drag and drop fields from the left to start building your form.', 'dragwyb-form-builder'); ?></p>
            </div>
        </div>
    </div>

    <div class="dragwyb-form-builder-footer">
        <div class="dragwyb-form-actions">
            <button type="button" class="button button-primary dragwyb-save-form">
                <?php esc_html_e('Save Form', 'dragwyb-form-builder'); ?>
            </button>
            <button type="button" class="button dragwyb-preview-form">
                <?php esc_html_e('Preview', 'dragwyb-form-builder'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Form preview modal -->
<div id="dragwyb-preview-modal" class="dragwyb-modal" style="display: none;">
    <div class="dragwyb-modal-content">
        <div class="dragwyb-modal-header">
            <h2><?php esc_html_e('Form Preview', 'dragwyb-form-builder'); ?></h2>
            <button type="button" class="dragwyb-modal-close">×</button>
        </div>
        <div class="dragwyb-modal-body">
            <iframe id="dragwyb-preview-frame" src="about:blank"></iframe>
        </div>
    </div>
</div> 