<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\After_Submission\Action_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;

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

    public function process_submission($form_data, $settings)
    {
        // Logic to save submission to the database
    }
}
