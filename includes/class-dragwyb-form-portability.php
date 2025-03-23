<?php
declare(strict_types=1);

class Dragwyb_Form_Portability {
    private const EXPORT_VERSION = '1.0.0';
    private const ALLOWED_MIME_TYPES = [
        'application/json',
        'text/json',
        'text/plain'
    ];

    public function __construct() {
        add_action('admin_init', [$this, 'handle_export']);
        add_action('admin_init', [$this, 'handle_import']);
        add_action('wp_ajax_dragwyb_export_form', [$this, 'ajax_export_form']);
        add_action('wp_ajax_dragwyb_import_form', [$this, 'ajax_import_form']);
    }

    /**
     * Handle form export
     */
    public function handle_export(): void {
        if (!isset($_POST['dragwyb_export_forms'])) {
            return;
        }

        try {
            check_admin_referer('dragwyb_export_forms', 'nonce');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $form_ids = array_map('absint', $_POST['form_ids'] ?? []);
            if (empty($form_ids)) {
                throw new Exception(__('No forms selected for export.', 'dragwyb-form-builder'));
            }

            $export_data = $this->generate_export_data($form_ids);
            $this->download_export_file($export_data);

        } catch (Exception $e) {
            add_settings_error(
                'dragwyb_export',
                'export_error',
                $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * Handle form import
     */
    public function handle_import(): void {
        if (!isset($_POST['dragwyb_import_forms'])) {
            return;
        }

        try {
            check_admin_referer('dragwyb_import_forms', 'nonce');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception(__('No file uploaded or upload error occurred.', 'dragwyb-form-builder'));
            }

            $file = $_FILES['import_file'];
            $this->validate_import_file($file);

            $import_data = json_decode(file_get_contents($file['tmp_name']), true);
            $this->validate_import_data($import_data);

            $imported_forms = $this->import_forms($import_data);

            add_settings_error(
                'dragwyb_import',
                'import_success',
                sprintf(
                    __('Successfully imported %d forms.', 'dragwyb-form-builder'),
                    count($imported_forms)
                ),
                'success'
            );

        } catch (Exception $e) {
            add_settings_error(
                'dragwyb_import',
                'import_error',
                $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * Generate export data
     */
    private function generate_export_data(array $form_ids): array {
        $forms = [];
        foreach ($form_ids as $form_id) {
            $form = get_post($form_id);
            if (!$form || $form->post_type !== 'dragwyb_form') {
                continue;
            }

            $forms[] = [
                'title' => $form->post_title,
                'settings' => get_post_meta($form_id, '_form_settings', true),
                'fields' => get_post_meta($form_id, '_form_fields', true),
                'styles' => get_post_meta($form_id, '_form_styles', true),
                'conditional_logic' => get_post_meta($form_id, '_form_conditional_logic', true),
                'validation_rules' => get_post_meta($form_id, '_form_validation_rules', true),
                'notifications' => get_post_meta($form_id, '_form_notifications', true),
                'confirmations' => get_post_meta($form_id, '_form_confirmations', true)
            ];
        }

        return [
            'version' => self::EXPORT_VERSION,
            'generated' => current_time('mysql'),
            'forms' => $forms
        ];
    }

    /**
     * Download export file
     */
    private function download_export_file(array $export_data): void {
        $filename = 'dragwyb-forms-' . date('Y-m-d') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo wp_json_encode($export_data, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Validate import file
     */
    private function validate_import_file(array $file): void {
        $mime_type = mime_content_type($file['tmp_name']);
        
        if (!in_array($mime_type, self::ALLOWED_MIME_TYPES, true)) {
            throw new Exception(__('Invalid file type. Only JSON files are allowed.', 'dragwyb-form-builder'));
        }

        if ($file['size'] > wp_max_upload_size()) {
            throw new Exception(__('File size exceeds maximum upload limit.', 'dragwyb-form-builder'));
        }
    }

    /**
     * Validate import data
     */
    private function validate_import_data(array $data): void {
        if (!isset($data['version'], $data['forms']) || !is_array($data['forms'])) {
            throw new Exception(__('Invalid import file format.', 'dragwyb-form-builder'));
        }

        if (version_compare($data['version'], self::EXPORT_VERSION, '>')) {
            throw new Exception(__('Import file version is not supported.', 'dragwyb-form-builder'));
        }
    }

    /**
     * Import forms
     */
    private function import_forms(array $import_data): array {
        $imported_forms = [];

        foreach ($import_data['forms'] as $form_data) {
            $form_id = wp_insert_post([
                'post_title' => $form_data['title'],
                'post_type' => 'dragwyb_form',
                'post_status' => 'publish'
            ]);

            if (is_wp_error($form_id)) {
                continue;
            }

            // Import form meta data
            update_post_meta($form_id, '_form_settings', $form_data['settings']);
            update_post_meta($form_id, '_form_fields', $form_data['fields']);
            update_post_meta($form_id, '_form_styles', $form_data['styles']);
            update_post_meta($form_id, '_form_conditional_logic', $form_data['conditional_logic']);
            update_post_meta($form_id, '_form_validation_rules', $form_data['validation_rules']);
            update_post_meta($form_id, '_form_notifications', $form_data['notifications']);
            update_post_meta($form_id, '_form_confirmations', $form_data['confirmations']);

            $imported_forms[] = $form_id;
        }

        return $imported_forms;
    }

    /**
     * AJAX export form
     */
    public function ajax_export_form(): void {
        try {
            check_ajax_referer('dragwyb_form_export');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $form_id = absint($_POST['form_id'] ?? 0);
            if (!$form_id) {
                throw new Exception(__('Invalid form ID.', 'dragwyb-form-builder'));
            }

            $export_data = $this->generate_export_data([$form_id]);
            
            wp_send_json_success([
                'data' => $export_data,
                'filename' => 'dragwyb-form-' . $form_id . '-' . date('Y-m-d') . '.json'
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX import form
     */
    public function ajax_import_form(): void {
        try {
            check_ajax_referer('dragwyb_form_import');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception(__('No file uploaded or upload error occurred.', 'dragwyb-form-builder'));
            }

            $file = $_FILES['file'];
            $this->validate_import_file($file);

            $import_data = json_decode(file_get_contents($file['tmp_name']), true);
            $this->validate_import_data($import_data);

            $imported_forms = $this->import_forms($import_data);

            wp_send_json_success([
                'message' => sprintf(
                    __('Successfully imported %d forms.', 'dragwyb-form-builder'),
                    count($imported_forms)
                ),
                'forms' => $imported_forms
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }
} 