<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="dragwyb-style-settings">
    <h2><?php esc_html_e('Form Styling', 'dragwyb-form-builder'); ?></h2>
    
    <?php foreach ($settings as $section_key => $section): ?>
        <div class="dragwyb-style-section">
            <h3><?php echo esc_html($section['label']); ?></h3>
            
            <?php foreach ($section['settings'] as $key => $setting): ?>
                <div class="dragwyb-style-field">
                    <label for="style-<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($setting['label']); ?>
                    </label>
                    
                    <?php
                    $value = $saved_settings[$key] ?? $setting['default'];
                    switch ($setting['type']):
                        case 'text':
                            ?>
                            <input type="text"
                                   id="style-<?php echo esc_attr($key); ?>"
                                   name="form_style_settings[<?php echo esc_attr($key); ?>]"
                                   value="<?php echo esc_attr($value); ?>">
                            <?php
                            break;

                        case 'select':
                            ?>
                            <select id="style-<?php echo esc_attr($key); ?>"
                                    name="form_style_settings[<?php echo esc_attr($key); ?>]">
                                <?php foreach ($setting['options'] as $option_value => $option_label): ?>
                                    <option value="<?php echo esc_attr($option_value); ?>"
                                            <?php selected($value, $option_value); ?>>
                                        <?php echo esc_html($option_label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php
                            break;

                        case 'color':
                            ?>
                            <input type="color"
                                   id="style-<?php echo esc_attr($key); ?>"
                                   name="form_style_settings[<?php echo esc_attr($key); ?>]"
                                   value="<?php echo esc_attr($value); ?>">
                            <?php
                            break;
                    endswitch;
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<div class="dragwyb-responsive-settings">
    <h3><?php esc_html_e('Responsive Settings', 'dragwyb-form-builder'); ?></h3>
    
    <div class="dragwyb-preview-devices">
        <button type="button" class="dragwyb-preview-device active" data-device="desktop">
            <span class="dashicons dashicons-desktop"></span>
        </button>
        <button type="button" class="dragwyb-preview-device" data-device="tablet">
            <span class="dashicons dashicons-tablet"></span>
        </button>
        <button type="button" class="dragwyb-preview-device" data-device="mobile">
            <span class="dashicons dashicons-smartphone"></span>
        </button>
    </div>

    <div class="dragwyb-responsive-tabs">
        <div class="dragwyb-responsive-tab" data-device="tablet">
            <h4><?php esc_html_e('Tablet Settings', 'dragwyb-form-builder'); ?></h4>
            <?php foreach ($responsive_settings['tablet'] as $key => $setting): ?>
                <div class="dragwyb-style-field">
                    <label for="style-tablet-<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($setting['label']); ?>
                    </label>
                    <input type="text"
                           id="style-tablet-<?php echo esc_attr($key); ?>"
                           name="form_style_settings[tablet_<?php echo esc_attr($key); ?>]"
                           value="<?php echo esc_attr($saved_settings['tablet_' . $key] ?? $setting['default']); ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <div class="dragwyb-responsive-tab" data-device="mobile">
            <h4><?php esc_html_e('Mobile Settings', 'dragwyb-form-builder'); ?></h4>
            <?php foreach ($responsive_settings['mobile'] as $key => $setting): ?>
                <div class="dragwyb-style-field">
                    <label for="style-mobile-<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($setting['label']); ?>
                    </label>
                    <input type="text"
                           id="style-mobile-<?php echo esc_attr($key); ?>"
                           name="form_style_settings[mobile_<?php echo esc_attr($key); ?>]"
                           value="<?php echo esc_attr($saved_settings['mobile_' . $key] ?? $setting['default']); ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="dragwyb-custom-css-section">
    <h3><?php esc_html_e('Custom CSS', 'dragwyb-form-builder'); ?></h3>
    
    <div class="dragwyb-style-field">
        <label for="dragwyb-custom-css">
            <?php esc_html_e('Additional CSS', 'dragwyb-form-builder'); ?>
        </label>
        <textarea id="dragwyb-custom-css" 
                  name="form_style_settings[custom_css]" 
                  rows="10" 
                  class="code"><?php echo esc_textarea($saved_settings['custom_css'] ?? ''); ?></textarea>
        <p class="description">
            <?php esc_html_e('Add custom CSS rules to further customize your form. Use .dragwyb-form-{id} as the base selector.', 'dragwyb-form-builder'); ?>
        </p>
    </div>
</div>

<div class="dragwyb-presets-section">
    <h3><?php esc_html_e('Style Presets', 'dragwyb-form-builder'); ?></h3>
    
    <div class="dragwyb-style-field">
        <label for="dragwyb-style-preset">
            <?php esc_html_e('Select Preset', 'dragwyb-form-builder'); ?>
        </label>
        <select id="dragwyb-style-preset">
            <option value=""><?php esc_html_e('Choose a preset...', 'dragwyb-form-builder'); ?></option>
            <?php foreach ($style_presets as $slug => $preset): ?>
                <option value="<?php echo esc_attr($slug); ?>">
                    <?php echo esc_html($preset['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="button" id="dragwyb-apply-preset">
            <?php esc_html_e('Apply Preset', 'dragwyb-form-builder'); ?>
        </button>
        <button type="button" class="button" id="dragwyb-save-preset">
            <?php esc_html_e('Save Current as Preset', 'dragwyb-form-builder'); ?>
        </button>
    </div>
</div>

<style>
.dragwyb-style-settings {
    margin: 20px 0;
}

.dragwyb-style-section {
    background: #fff;
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 20px;
}

.dragwyb-style-section h3 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.dragwyb-style-field {
    margin-bottom: 15px;
}

.dragwyb-style-field label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.dragwyb-style-field input[type="text"],
.dragwyb-style-field select {
    width: 100%;
    max-width: 300px;
}

.dragwyb-style-field input[type="color"] {
    padding: 0;
    width: 50px;
    height: 30px;
}

.dragwyb-style-preview-container {
    margin: 20px 0;
    border: 1px solid #ddd;
    background: #f8f8f8;
    padding: 20px;
}

.dragwyb-style-preview-frame {
    width: 100%;
    height: 600px;
    border: 1px solid #ddd;
    background: #fff;
    transition: width 0.3s ease;
}

.dragwyb-preview-devices {
    margin-bottom: 20px;
    text-align: right;
}

.dragwyb-preview-device {
    padding: 5px 10px;
    background: #fff;
    border: 1px solid #ddd;
    cursor: pointer;
}

.dragwyb-preview-device.active {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}

.dragwyb-responsive-tabs {
    margin-top: 20px;
}

.dragwyb-responsive-tab {
    display: none;
    padding: 15px;
    background: #fff;
    border: 1px solid #ddd;
}

.dragwyb-responsive-tab.active {
    display: block;
}

.dragwyb-custom-css-section,
.dragwyb-presets-section {
    margin-top: 20px;
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
}

#dragwyb-custom-css {
    width: 100%;
    font-family: monospace;
    resize: vertical;
}

#dragwyb-style-preset {
    min-width: 200px;
    margin-right: 10px;
}
</style> 