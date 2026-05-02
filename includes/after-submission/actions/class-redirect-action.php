<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\After_Submission\Action_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Redirect_Action extends Action_Base
{
    public function get_id(): string
    {
        return 'redirect';
    }

    public function get_name(): string
    {
        return __('Redirect', 'smart-form-builder-by-dragwyb');
    }

    protected function register_settings(): void
    {
        $this->start_section('url_section_redirect', [
            'label' => __('Redirect After Submit', 'smart-form-builder-by-dragwyb'),
            'conditions' => [
                'after_submissions' => 'redirect'
            ]
        ]);

        $this->add_control('url_redirect', [
            'type'       => Controls::TEXT,
            'label'      => __('Redirect URL', 'smart-form-builder-by-dragwyb'),
            'default'    => '',
            'placeholder' => 'https://example.com/thank-you',
            'required'   => true,
        ]);

        $this->end_section();
    }

    public function process_submission($form_data, $settings)
    {
        // Handle redirection logic
    }
}
