<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission;

use Dragwyb\Form_Builder\Includes\Controls\Register_Controls_Base;

abstract class Action_Base extends Register_Controls_Base
{
    abstract public function get_id(): string;
    abstract public function get_name(): string;

    /**
     * Define the specific controls for this action.
     */
    abstract protected function register_settings(): void;

    /**
     * Process the submission for this specific action.
     */
    abstract public function process_submission($form_data, $settings);

    protected function register_controls(): void
    {
        $this->register_settings();
    }
}
