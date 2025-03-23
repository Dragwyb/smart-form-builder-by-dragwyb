<?php if (!defined('ABSPATH')) exit; ?>

<div class="dragwyb-error-templates-manager">
    <h2><?php esc_html_e('Error Message Templates', 'dragwyb-form-builder'); ?></h2>
    
    <div class="dragwyb-template-list">
        <?php foreach ($templates as $key => $template): ?>
            <div class="dragwyb-template-item" data-key="<?php echo esc_attr($key); ?>">
                <div class="dragwyb-template-header">
                    <h3><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?></h3>
                    <button type="button" class="button dragwyb-edit-template">
                        <?php esc_html_e('Edit', 'dragwyb-form-builder'); ?>
                    </button>
                </div>
                
                <div class="dragwyb-template-content">
                    <p class="template-message">
                        <?php echo esc_html($template['default']); ?>
                    </p>
                    <?php if (!empty($template['variables'])): ?>
                        <p class="template-variables">
                            <?php esc_html_e('Available variables:', 'dragwyb-form-builder'); ?>
                            <?php echo esc_html('{' . implode('}, {', $template['variables']) . '}'); ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="dragwyb-template-editor" style="display: none;">
                    <textarea class="widefat" rows="3"><?php 
                        echo esc_textarea($template['default']); 
                    ?></textarea>
                    <div class="dragwyb-template-actions">
                        <button type="button" class="button button-primary dragwyb-save-template">
                            <?php esc_html_e('Save', 'dragwyb-form-builder'); ?>
                        </button>
                        <button type="button" class="button dragwyb-cancel-edit">
                            <?php esc_html_e('Cancel', 'dragwyb-form-builder'); ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.dragwyb-error-templates-manager {
    margin: 20px 0;
}

.dragwyb-template-item {
    background: #fff;
    border: 1px solid #ddd;
    margin-bottom: 10px;
    padding: 15px;
}

.dragwyb-template-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.dragwyb-template-header h3 {
    margin: 0;
}

.dragwyb-template-content {
    margin-bottom: 15px;
}

.template-variables {
    color: #666;
    font-style: italic;
    margin: 5px 0 0;
}

.dragwyb-template-editor {
    margin-top: 10px;
}

.dragwyb-template-actions {
    margin-top: 10px;
}

.dragwyb-template-actions button {
    margin-right: 5px;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.dragwyb-edit-template').on('click', function() {
        const $item = $(this).closest('.dragwyb-template-item');
        $item.find('.dragwyb-template-content').hide();
        $item.find('.dragwyb-template-editor').show();
    });

    $('.dragwyb-cancel-edit').on('click', function() {
        const $item = $(this).closest('.dragwyb-template-item');
        $item.find('.dragwyb-template-editor').hide();
        $item.find('.dragwyb-template-content').show();
    });

    $('.dragwyb-save-template').on('click', function() {
        const $item = $(this).closest('.dragwyb-template-item');
        const key = $item.data('key');
        const message = $item.find('textarea').val();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'dragwyb_save_error_template',
                nonce: dragwybAdmin.nonce,
                key: key,
                message: message
            },
            success: function(response) {
                if (response.success) {
                    $item.find('.template-message').text(message);
                    $item.find('.dragwyb-template-editor').hide();
                    $item.find('.dragwyb-template-content').show();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});
</script> 