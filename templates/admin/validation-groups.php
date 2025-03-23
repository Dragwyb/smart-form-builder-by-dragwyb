<?php if (!defined('ABSPATH')) exit; ?>

<div class="dragwyb-validation-groups">
    <h3><?php esc_html_e('Validation Groups', 'dragwyb-form-builder'); ?></h3>
    
    <div class="dragwyb-validation-groups-list">
        <?php foreach ($validation_groups as $key => $group): ?>
            <div class="dragwyb-validation-group">
                <label>
                    <input type="checkbox" 
                           name="validation_groups[]" 
                           value="<?php echo esc_attr($key); ?>"
                           <?php checked(in_array($key, $active_groups)); ?>>
                    <?php echo esc_html($group['name']); ?>
                </label>
                <span class="description">
                    <?php echo esc_html(implode(', ', $group['rules'])); ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="dragwyb-add-validation-group">
        <h4><?php esc_html_e('Add Custom Validation Group', 'dragwyb-form-builder'); ?></h4>
        
        <div class="dragwyb-group-form">
            <input type="text" 
                   id="new-group-name" 
                   placeholder="<?php esc_attr_e('Group Name', 'dragwyb-form-builder'); ?>">
            
            <select id="new-group-rules" multiple>
                <?php foreach ($available_rules as $rule => $label): ?>
                    <option value="<?php echo esc_attr($rule); ?>">
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="button" class="button" id="add-validation-group">
                <?php esc_html_e('Add Group', 'dragwyb-form-builder'); ?>
            </button>
        </div>
    </div>
</div>

<style>
.dragwyb-validation-groups {
    margin: 20px 0;
    padding: 15px;
    background: #fff;
    border: 1px solid #ddd;
}

.dragwyb-validation-groups-list {
    margin-bottom: 20px;
}

.dragwyb-validation-group {
    margin-bottom: 10px;
    padding: 8px;
    background: #f9f9f9;
    border: 1px solid #eee;
}

.dragwyb-validation-group .description {
    display: block;
    margin-left: 24px;
    color: #666;
    font-style: italic;
}

.dragwyb-group-form {
    display: flex;
    gap: 10px;
    align-items: flex-start;
}

.dragwyb-group-form select {
    min-width: 200px;
    min-height: 100px;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#add-validation-group').on('click', function() {
        const name = $('#new-group-name').val();
        const rules = $('#new-group-rules').val();

        if (!name || !rules.length) {
            alert(dragwybAdmin.strings.validation_group_required);
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'dragwyb_add_validation_group',
                nonce: dragwybAdmin.nonce,
                name: name,
                rules: rules
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});
</script> 