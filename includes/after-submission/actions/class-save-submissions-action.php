<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\After_Submission\Action_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Save_Submissions_Action extends Action_Base
{
    public function get_id(): string
    {
        return 'save_submissions';
    }

    public function get_name(): string
    {
        return __('Save Submissions', 'smart-form-builder-by-dragwyb');
    }

    protected function register_settings(): void
    {
        $this->start_section('database_section_save_submissions', [
            'label' => __('Save Submissions', 'smart-form-builder-by-dragwyb'),
            'conditions' => [
                'after_submissions' => 'save_submissions'
            ]
        ]);

        $this->add_control('save_to_db_save_submissions', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Save Submissions to Database', 'smart-form-builder-by-dragwyb'),
            'default' => 'no',
            'description' => __('View entries in WP Dashboard > Dragwyb > Submissions', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->end_section();
    }

    public function process_submission($form_id, $form_data, $form_config, Form_Submission_Handler $form_submission)
    {
        $settings = $form_config['after-submission'] ?? [];
        $save_to_db = $settings['save_to_db_save_submissions'] ?? 'no';

        if ($save_to_db === 'yes') {
            $post_id = wp_insert_post([
                'post_type'   => 'dragwyb_submission',
                'post_parent' => $form_id,
                'post_status' => 'publish',
                'post_title'  => sprintf(__('Submission for Form #%d', 'smart-form-builder-by-dragwyb'), $form_id),
            ], true);

            var_dump($post_id);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, '_dragwyb_submission_data', wp_slash(wp_json_encode($form_data)));
            } else {
                $form_submission->add_error('save_submissions', $post_id->get_error_message());
            }
        }
    }
}
