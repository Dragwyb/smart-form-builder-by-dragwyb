<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

use Dragwyb\Form_Builder\Includes\Controls\Fonts\Fonts_Helper;

class Control_Fonts extends Control_Base
{
    private $exclude_fonts = array();
    private $group_fonts = array();

    protected function init(): void
    {
        $this->type = 'fonts';
        $this->name = __('Font Family', 'dragwyb-form-builder');
    }

    protected function register_settings()
    {
        return [
            'name'    => 'string',
            'label'   => 'string',
            'default' => 'string',
            'exclude_fonts' => 'custom',
            'groups' => 'custom',
            'options' => 'custom', // Allow passing specific font groups if needed
            'label_inline' => 'boolean',
        ];
    }

    protected function default_setting(): array
    {
        return [
            'default' => 'Roboto', // Default Font
            'options' => $this->get_fonts_list(),
            'label_inline' => true,
        ];
    }

    private function get_fonts_list(): array
    {
        $groups = $this->get_fonts_group();

        $font_list = Fonts_Helper::get_fonts_by_groups($groups);

        if (isset($this->exclude_fonts) && is_array($this->exclude_fonts) && count($this->exclude_fonts) > 0) {
            foreach ($this->exclude_fonts as $font) {
                unset($font_list[$font]);
            }
        }

        return isset($font_list) && is_array($font_list) && count($font_list) > 0 ? $font_list : [];
    }

    private function get_fonts_group(): array
    {
        if (isset($this->group_fonts) && is_array($this->group_fonts) && count($this->group_fonts) > 0) {
            $this->group_fonts = array_map(function ($group) {
                return $this->string_sanitize($group);
            }, $this->group_fonts);

            return $this->group_fonts;
        }

        $font_list = Fonts_Helper::get_font_groups();
        $group = [];
        foreach ($font_list as $key => $value) {
            $group[] = $this->string_sanitize($key);
        }

        return isset($group) && is_array($group) && count($group) > 0 ? $group : [];
    }

    protected function sanitize_control($value)
    {
        // Font names can contain spaces (e.g., "Open Sans")
        return sanitize_text_field($value);
    }

    protected function exclude_fonts_setting_sanitize(array $fonts): array
    {
        $this->exclude_fonts = array();
        $data = &$this->exclude_fonts;

        if (isset($fonts) && count($fonts) > 0) {
            foreach ($fonts as $font) {
                $data[] = $this->string_sanitize($font);
            }
        }

        return isset($data) && is_array($data) && count($data) > 0 ? $data : [];
    }

    protected function groups_setting_sanitize(array $groups): array
    {
        $this->group_fonts = array();
        $data = &$this->group_fonts;

        if (isset($groups) && count($groups) > 0) {
            foreach ($groups as $group) {
                $data[] = $this->string_sanitize($group);
            }
        }

        return isset($data) && is_array($data) && count($data) > 0 ? $data : [];
    }

    protected function options_setting_sanitize(array $options): array
    {
        $data = array();

        if (isset($options) && count($options) > 0) {
            foreach ($options as $font => $type) {
                $data[$this->string_sanitize($font)] = $this->string_sanitize($type);
            }
        }

        return isset($data) && is_array($data) && count($data) > 0 ? $data : [];
    }
}
