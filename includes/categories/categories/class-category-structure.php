<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Categories\Categories;

class Category_Structure extends Category_Base {

	protected function init(): void {
		$this->id   = 'structure';
		$this->name = __( 'Structure', 'smart-form-builder-by-dragwyb' );
		$this->icon = 'fas fa-columns';
	}
}
