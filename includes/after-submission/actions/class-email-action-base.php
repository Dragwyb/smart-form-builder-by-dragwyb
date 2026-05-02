<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\After_Submission\Action_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;

abstract class Email_Action_Base extends Action_Base
{

    abstract protected function section_label(): string;

    protected function email_action_settings(): void
    {
        $prefix = $this->get_id();

        $this->start_section('email_section_' . $prefix, [
            'label' => $this->section_label(),
            'conditions' => [
                'after_submissions' => $prefix
            ]
        ]);

        $this->add_control('email_to_' . $prefix, [
            'type'       => Controls::TEXT,
            'label'      => __('Send To (Email)', 'smart-form-builder-by-dragwyb'),
            'default'    => get_option('admin_email'),
            'description' => __('Separate multiple emails with commas.', 'smart-form-builder-by-dragwyb'),
            'required'   => true,
        ]);

        $this->add_control('email_subject_' . $prefix, [
            'type'       => Controls::TEXT,
            'label'      => __('Subject Line', 'smart-form-builder-by-dragwyb'),
            'default'    => __('New Submission: [Form Name]', 'smart-form-builder-by-dragwyb'),
            'required'   => true,
        ]);

        $this->add_control('email_message_body_' . $prefix, [
            'type'       => Controls::TEXTAREA,
            'label'      => __('Message Body', 'smart-form-builder-by-dragwyb'),
            'default'    => '{all_fields}', // Shortcode for all data
            'description' => __('Use {all_fields} to show all data, or use field IDs like {name}.', 'smart-form-builder-by-dragwyb'),
            'required'   => true,
        ]);

        $this->end_section();
    }
}
