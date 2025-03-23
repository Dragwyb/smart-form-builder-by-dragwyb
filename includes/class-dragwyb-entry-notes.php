<?php
declare(strict_types=1);

class Dragwyb_Entry_Notes {
    /**
     * Initialize notes system
     */
    public function __construct() {
        add_action('wp_ajax_dragwyb_save_entry_note', [$this, 'save_note']);
        add_action('wp_ajax_dragwyb_delete_entry_note', [$this, 'delete_note']);
    }

    /**
     * Save note
     */
    public function save_note(): void {
        try {
            check_ajax_referer('dragwyb_entry_manager');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $entry_id = absint($_POST['entry_id']);
            $content = sanitize_textarea_field($_POST['content']);

            if (!$content) {
                throw new Exception(__('Note content is required.', 'dragwyb-form-builder'));
            }

            $note = [
                'id' => uniqid(),
                'content' => $content,
                'author' => get_current_user_id(),
                'date' => current_time('mysql'),
            ];

            $notes = get_post_meta($entry_id, '_entry_notes', true) ?: [];
            $notes[] = $note;

            update_post_meta($entry_id, '_entry_notes', $notes);

            wp_send_json_success([
                'message' => __('Note added successfully.', 'dragwyb-form-builder'),
                'note' => $this->format_note($note),
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete note
     */
    public function delete_note(): void {
        try {
            check_ajax_referer('dragwyb_entry_manager');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $entry_id = absint($_POST['entry_id']);
            $note_id = sanitize_text_field($_POST['note_id']);

            $notes = get_post_meta($entry_id, '_entry_notes', true) ?: [];
            $notes = array_filter($notes, fn($note) => $note['id'] !== $note_id);

            update_post_meta($entry_id, '_entry_notes', array_values($notes));

            wp_send_json_success([
                'message' => __('Note deleted successfully.', 'dragwyb-form-builder'),
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format note for display
     */
    private function format_note(array $note): array {
        $user = get_user_by('id', $note['author']);
        
        return [
            'id' => $note['id'],
            'content' => $note['content'],
            'author' => $user ? $user->display_name : __('Unknown', 'dragwyb-form-builder'),
            'date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($note['date'])),
        ];
    }
} 