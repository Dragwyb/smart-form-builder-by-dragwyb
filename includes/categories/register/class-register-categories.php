<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Categories\Register;

use Dragwyb\Form_Builder\Includes\Helper\Helper;
use Dragwyb\Form_Builder\Includes\Categories\Categories;
use Dragwyb\Form_Builder\Includes\Categories\Categories\Category_Base;

class Register_Categories {

	private static $instance = null;

	private array $categories = array();

	private array $default_categories = array(
		Categories::STANDARD_FIELDS,
		Categories::ADVANCED_FIELDS,
		Categories::STRUCTURE,
	);

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->register_default_categories();

		do_action( 'Dragwyb/register_categories', $this );
	}

	private function register_default_categories(): void {
		foreach ( $this->default_categories as $category ) {

			$dragwyb_name_space = Helper::namespace_into_dir_path( __NAMESPACE__ );
			$dir                = dirname( $dragwyb_name_space );
			$dir                = Helper::dir_path_into_namespace( $dir );

			// Convert kebab-case to CamelCase (e.g. standard-fields -> Standard_Fields)
			$class_name = $this->kebab_to_camel( $category );

			// Construct full class path: ..\Categories\Category_Standard_Fields
			$class = $dir . '\Categories\Category_' . ucfirst( $class_name );

			if ( class_exists( $class ) ) {
				$this->register_category( new $class() );
			}
		}
	}

	private function kebab_to_camel( $string ) {
		// Replace hyphens with underscores
		$string = str_replace( '-', '_', $string );
		// Capitalize words
		$string = ucwords( $string, '_' );
		return $string;
	}

	public function register_category( Category_Base $category ): void {
		$this->categories[ $category->get_id() ] = $category;
	}

	public function get_categories(): array {
		return $this->categories;
	}
}
