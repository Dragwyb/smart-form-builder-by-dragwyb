<?php if (!defined('ABSPATH')) exit; ?>

<div class="dragwyb-style-editor">
    <div class="style-editor-header">
        <h2><?php esc_html_e('Form Style Editor', 'dragwyb-form-builder'); ?></h2>
        
        <div class="style-editor-actions">
            <select id="theme-selector">
                <option value=""><?php esc_html_e('Select Theme', 'dragwyb-form-builder'); ?></option>
                <?php foreach ($themes as $theme_name => $theme_data): ?>
                    <option value="<?php echo esc_attr($theme_name); ?>">
                        <?php echo esc_html($theme_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="button" class="button" id="save-as-theme">
                <?php esc_html_e('Save as Theme', 'dragwyb-form-builder'); ?>
            </button>

            <button type="button" class="button button-primary" id="save-styles">
                <?php esc_html_e('Save Styles', 'dragwyb-form-builder'); ?>
            </button>
        </div>
    </div>

    <div class="style-editor-sidebar">
        <div class="style-editor-sections">
            <button type="button" data-section="form" class="active">
                <?php esc_html_e('Form', 'dragwyb-form-builder'); ?>
            </button>
            <button type="button" data-section="fields">
                <?php esc_html_e('Fields', 'dragwyb-form-builder'); ?>
            </button>
            <button type="button" data-section="buttons">
                <?php esc_html_e('Buttons', 'dragwyb-form-builder'); ?>
            </button>
            <button type="button" data-section="validation">
                <?php esc_html_e('Validation', 'dragwyb-form-builder'); ?>
            </button>
            <button type="button" data-section="responsive">
                <?php esc_html_e('Responsive', 'dragwyb-form-builder'); ?>
            </button>
            <button type="button" data-section="animations">
                <?php esc_html_e('Animations', 'dragwyb-form-builder'); ?>
            </button>
        </div>

        <div class="style-editor-panels">
            <!-- Form Panel -->
            <div class="style-panel" data-panel="form">
                <div class="style-field">
                    <label><?php esc_html_e('Background Color', 'dragwyb-form-builder'); ?></label>
                    <input type="text" class="color-picker" data-style="form.background">
                </div>
                <!-- Add more form style fields -->
            </div>

            <!-- Fields Panel -->
            <div class="style-panel" data-panel="fields">
                <div class="style-field">
                    <label><?php esc_html_e('Label Color', 'dragwyb-form-builder'); ?></label>
                    <input type="text" class="color-picker" data-style="fields.label_color">
                </div>
                <!-- Add more field style fields -->
            </div>

            <!-- Buttons Panel -->
            <div class="style-panel" data-panel="buttons">
                <div class="style-field">
                    <label><?php esc_html_e('Button Background', 'dragwyb-form-builder'); ?></label>
                    <input type="text" class="color-picker" data-style="buttons.background">
                </div>
                <!-- Add more button style fields -->
            </div>

            <!-- Validation Panel -->
            <div class="style-panel" data-panel="validation">
                <div class="style-field">
                    <label><?php esc_html_e('Error Color', 'dragwyb-form-builder'); ?></label>
                    <input type="text" class="color-picker" data-style="validation.error_color">
                </div>
                <!-- Add more validation style fields -->
            </div>

            <!-- Responsive Panel -->
            <div class="style-panel" data-panel="responsive">
                <div class="style-field">
                    <label><?php esc_html_e('Mobile Breakpoint', 'dragwyb-form-builder'); ?></label>
                    <input type="text" data-style="responsive.breakpoint_mobile">
                </div>
                <!-- Add more responsive style fields -->
            </div>

            <!-- Animations Panel -->
            <div class="style-panel" data-panel="animations">
                <div class="style-field">
                    <label><?php esc_html_e('Transition Duration', 'dragwyb-form-builder'); ?></label>
                    <input type="text" data-style="animations.transition_duration">
                </div>
                <!-- Add more animation style fields -->
            </div>
        </div>
    </div>

    <div class="style-editor-preview">
        <div class="preview-header">
            <h3><?php esc_html_e('Live Preview', 'dragwyb-form-builder'); ?></h3>
            <button type="button" class="button" id="reset-preview">
                <?php esc_html_e('Reset Preview', 'dragwyb-form-builder'); ?>
            </button>
        </div>
        <div class="preview-container">
            <!-- Form preview will be loaded here -->
        </div>
    </div>
</div> 