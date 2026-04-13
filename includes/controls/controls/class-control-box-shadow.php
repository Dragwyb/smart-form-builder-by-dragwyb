<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Box_Shadow extends Control_Base
{

    protected function init(): void
    {
        $this->type = 'box_shadow';
        $this->name = __('Box Shadow', 'smart-form-builder-by-dragwyb');
    }

    protected function register_settings()
    {
        return [
            'name'      => 'string',
            'label'     => 'string',
            'default'   => 'custom',
            'separator' => 'string', // default, before, after
            'horizontal' => 'number',
            'vertical'   => 'number',
            'blur'       => 'number',
            'spread'     => 'number',
            'color'      => 'string',
            'inset'      => 'string', // 'inset' or empty
        ];
    }

    protected function default_setting(): array
    {
        return [
            'horizontal' => 0,
            'vertical'   => 0,
            'blur'       => 10,
            'spread'     => 0,
            'color'      => 'rgba(0,0,0,0.1)',
            'inset'      => '', // 'inset' or empty
        ];
    }

    protected function sanitize_control($value)
    {
        if (!is_array($value)) {
            return [];
        }

        return [
            'horizontal' => isset($value['horizontal']) ? $this->number_sanitize($value['horizontal']) : 0,
            'vertical'   => isset($value['vertical']) ? $this->number_sanitize($value['vertical']) : 0,
            'blur'       => isset($value['blur']) ? $this->number_sanitize($value['blur']) : 0,
            'spread'     => isset($value['spread']) ? $this->number_sanitize($value['spread']) : 0,
            'color'      => isset($value['color']) ? sanitize_text_field($value['color']) : '',
            'inset'      => isset($value['inset']) && $value['inset'] === 'inset' ? 'inset' : '',
        ];
    }
}
