<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission;

use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;

class After_Submission extends Toolbar_Base
{
    private static $instance = null;
    protected $toolbar_settings = null;
    private $registered_actions = [];

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
        $this->get_registered_actions();
    }

    public function get_registered_actions(): array
    {
        if (empty($this->registered_actions)) {
            $this->register_action(new Actions\Save_Submissions_Action());
            $this->register_action(new Actions\Success_Message_Action());
            $this->register_action(new Actions\Error_Message_Action());
            $this->register_action(new Actions\Redirect_Action());
            $this->register_action(new Actions\Email_Action());
            $this->register_action(new Actions\User_Email_Action());

            do_action('dragwyb/form_builder/after_submission/register', $this);
        }
        return $this->registered_actions;
    }

    protected function get_settings(): array
    {
        $actions_data = $this->get_registered_actions();
        $form_id = absint($this->get_form_id());
        $data = array();

        $data['label'] = sprintf(esc_html__('%s Settings', 'smart-form-builder-by-dragwyb'), sanitize_text_field($this->get_name()));
        $actions = [];

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

    public function register_action(Action_Base $action): void
    {
        $this->registered_actions[$action->get_id()] = $action;
    }

    protected function get_setting_instance(): string
    {
        return Settings::class;
    }

    protected function update_toolbar(): void {}
}
