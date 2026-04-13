<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Categories\Categories;

class Category_Standard_Fields extends Category_Base
{
    protected function init(): void
    {
        $this->id = 'standard-fields';
        $this->name = __('Standard Fields', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-cubes';
    }
}
