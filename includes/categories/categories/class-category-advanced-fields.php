<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Categories\Categories;

class Category_Advanced_Fields extends Category_Base
{
    protected function init(): void
    {
        $this->id = 'advanced-fields';
        $this->name = __('Advanced Fields', 'dragwyb-form-builder');
        $this->icon = 'fas fa-magic';
    }
}
