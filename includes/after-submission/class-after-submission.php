<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission;

use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;
use Dragwyb\Form_Builder\Includes\After_Submission\Register\Register_Actions;

class After_Submission extends Toolbar_Base
{
    private static $instance = null;
    private $actions = [];
    protected $toolbar_settings = null;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function get_id(): string
    {
        return 'after-submission';
    }

    protected function get_name(): string
    {
        return __('Submission', 'smart-form-builder-by-dragwyb');
    }

    protected function get_icon(): string
    {
        return 'fas fa-cog';
    }

    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    private function init(): void
    {
        $this->toolbar_settings = new Settings();

        // Load and register all action types
        $this->load_actions();
    }

    private function load_actions(): void
    {
        // Register action types
        $this->register_actions();
    }

    private function register_actions(): void
    {
        // Load action registrations
        $register = Register_Actions::instance();
        $this->actions = $register->get_actions();
    }

    public function get_actions(): array
    {
        return $this->actions;
    }

    public function get_action(string $type): ?Action_Base
    {
        return $this->actions[$type] ?? null;
    }

    protected function get_settings(): array
    {
        $actions_data = $this->get_actions();
        $form_id = absint($this->get_form_id());
        $data = array();

        $data['label'] = sprintf(esc_html__('%s Settings', 'smart-form-builder-by-dragwyb'), sanitize_text_field($this->get_name()));

        // Base toolbar settings
        $settings = $this->toolbar_settings;
        $setting_instance = $this->get_setting_instance();

        if ($settings instanceof $setting_instance) {
            $settings->set_form_id($form_id);
            $conrols = $settings->render_controls();

            if ($conrols && count($conrols) > 0) {
                $data['controls'] = $conrols;
            }
        }

        foreach ($actions_data as $key => $action) {
            $action->set_form_id($form_id);

            $conrols = $action->render_controls();

            $data['controls'] = array_merge($data['controls'], $conrols);
        }

        return $data;
    }

    protected function get_setting_instance(): string
    {
        return Settings::class;
    }

    final public function process_submission($form_id, $form_data, $form_config, Form_Submission_Handler $form_submission): void
    {
        $after_submission_config = isset($form_config['after-submission']) && is_array($form_config['after-submission']) ? $form_config['after-submission'] : array();
        $selected_after_submission_actions = isset($after_submission_config['after_submissions']) && is_array($after_submission_config['after_submissions']) ? $after_submission_config['after_submissions'] : array();

        if (!empty($selected_after_submission_actions)) {
            foreach ($selected_after_submission_actions as $selected_action) {
                $action_base = $this->get_action($selected_action);

                if ($action_base && $action_base instanceof Action_Base) {
                    $action_base->process_submission($form_id, $form_data, $form_config, $form_submission);
                }
            }
        }
    }

    protected function update_toolbar(): void {}
}
