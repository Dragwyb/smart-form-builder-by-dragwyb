<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission;

use Dragwyb\Form_Builder\Includes\Controls\Register_Controls_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Settings extends Register_Controls_Base
{
    /**
     * Singleton instance
     * @var Settings|null
     */
    private static $instance = null;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function init(): void {}

    protected function register_controls(): void
    {
        add_action('Dragwyb/Editor/after_section_end/data_handling', function ($control_obj, $section_id) {
            $control_obj->remove_control('data_handling');
            $after_submissions = $control_obj->get_control('after_submissions');

            if (!isset($after_submissions['conditions'])) {
                return;
            }

            unset($after_submissions['conditions']);
            $control_obj->update_control('after_submissions', $after_submissions);
        }, 10, 2);

        $actions = After_Submission::instance()->get_actions();
        $options = [];

        foreach ($actions as $action) {
            $options[$action->get_id()] = $action->get_name();
        }

        // Data Handling
        $this->start_section('data_handling', [
            'label' => __('Data Handling', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('after_submissions', [
            'type'    => Controls::MULTISELECT,
            'label'   => __('After Submissions Actions', 'smart-form-builder-by-dragwyb'),
            'default' => [],
            'options' => $options,
        ]);

        $this->end_section();
    }
}
