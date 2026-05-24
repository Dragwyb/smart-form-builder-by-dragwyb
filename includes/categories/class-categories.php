<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Categories;

use Dragwyb\Form_Builder\Includes\Categories\Register\Register_Categories;
use Dragwyb\Form_Builder\Includes\Categories\Categories\Category_Base;

class Categories {

	const STANDARD_FIELDS = 'standard-fields';
	const ADVANCED_FIELDS = 'advanced-fields';
	const STRUCTURE       = 'structure';

	private static $instance = null;
	private $categories      = array();

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->init();
	}

	private function init(): void {
		$this->load_categories();
	}

	private function load_categories(): void {
		$this->register_categories();
	}

	private function register_categories(): void {
		$register         = Register_Categories::instance();
		$this->categories = $register->get_categories();
	}

	public function get_categories(): array {
		return $this->categories;
	}

	public function get_category( $id ): ?Category_Base {
		return $this->categories[ $id ] ?? null;
	}
}
