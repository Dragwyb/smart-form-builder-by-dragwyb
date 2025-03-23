<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get form data from URL
$form_data = [];
if (isset($_GET['form_data'])) {
    $form_data = json_decode(base64_decode($_GET['form_data']), true);
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        body {
            margin: 20px;
            background: #fff;
        }
        .dragwyb-form {
            max-width: 600px;
            margin: 0 auto;
        }
        .dragwyb-field {
            margin-bottom: 20px;
        }
        .dragwyb-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .dragwyb-field input[type="text"],
        .dragwyb-field input[type="email"],
        .dragwyb-field input[type="number"],
        .dragwyb-field textarea,
        .dragwyb-field select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .dragwyb-field-required label:after {
            content: " *";
            color: #dc3232;
        }
    </style>
</head>
<body>
    <div class="dragwyb-form">
        <form method="post">
            <?php
            if (!empty($form_data['fields'])) {
                foreach ($form_data['fields'] as $field) {
                    $required = !empty($field['required']);
                    $field_id = 'field_' . uniqid();
                    ?>
                    <div class="dragwyb-field <?php echo $required ? 'dragwyb-field-required' : ''; ?>">
                        <label for="<?php echo esc_attr($field_id); ?>">
                            <?php echo esc_html($field['label']); ?>
                        </label>
                        <?php
                        switch ($field['type']) {
                            case 'text':
                                ?>
                                <input type="text"
                                       id="<?php echo esc_attr($field_id); ?>"
                                       placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                                       <?php echo $required ? 'required' : ''; ?>>
                                <?php
                                break;

                            case 'textarea':
                                ?>
                                <textarea id="<?php echo esc_attr($field_id); ?>"
                                          placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                                          <?php echo $required ? 'required' : ''; ?>></textarea>
                                <?php
                                break;

                            // Add more field types here
                        }
                        ?>
                    </div>
                    <?php
                }
            }
            ?>
            <div class="dragwyb-field">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Submit', 'dragwyb-form-builder'); ?>
                </button>
            </div>
        </form>
    </div>
    <?php wp_footer(); ?>
</body>
</html> 