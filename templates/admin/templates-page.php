<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Form Templates', 'dragwyb-form-builder'); ?></h1>

    <div class="dragwyb-templates-grid">
        <?php foreach ($templates as $template): ?>
            <?php
            $template_data = get_post_meta($template->ID, '_template_data', true);
            $field_count = is_array($template_data) ? count($template_data) : 0;
            ?>
            <div class="dragwyb-template-card">
                <h2><?php echo esc_html($template->post_title); ?></h2>
                <div class="dragwyb-template-meta">
                    <?php
                    printf(
                        _n('%s field', '%s fields', $field_count, 'dragwyb-form-builder'),
                        number_format_i18n($field_count)
                    );
                    ?>
                </div>
                <div class="dragwyb-template-actions">
                    <button type="button" 
                            class="button button-primary dragwyb-use-template" 
                            data-template-id="<?php echo esc_attr($template->ID); ?>">
                        <?php esc_html_e('Use Template', 'dragwyb-form-builder'); ?>
                    </button>
                    <button type="button" 
                            class="button dragwyb-preview-template" 
                            data-template-id="<?php echo esc_attr($template->ID); ?>">
                        <?php esc_html_e('Preview', 'dragwyb-form-builder'); ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.dragwyb-templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.dragwyb-template-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
}

.dragwyb-template-card h2 {
    margin: 0 0 10px;
    font-size: 1.2em;
}

.dragwyb-template-meta {
    color: #666;
    margin-bottom: 15px;
}

.dragwyb-template-actions {
    display: flex;
    gap: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.dragwyb-use-template').on('click', function() {
        const templateId = $(this).data('template-id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'dragwyb_load_template',
                nonce: dragwybFormBuilder.nonce,
                template_id: templateId
            },
            success: function(response) {
                if (response.success) {
                    // Redirect to new form page with template data
                    const url = new URL(window.location.href);
                    url.searchParams.set('post_type', 'dragwyb_form');
                    url.searchParams.set('template_data', JSON.stringify(response.data.template_data));
                    window.location.href = url.toString();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    $('.dragwyb-preview-template').on('click', function() {
        const templateId = $(this).data('template-id');
        // Implement template preview logic
    });
});
</script> 