<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Register;

use Dragwyb\Form_Builder\Includes\Helper\Helper;
use Dragwyb\Form_Builder\Includes\After_Submission\Action_Base;

class Register_Actions {

	private static $instance = null;

	private $actions = array();

	private $default_actions = array( 'email', 'error-message', 'redirect', 'save-submissions', 'success-message', 'user-email', 'error-logs' );

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->register_default_actions();

		do_action( 'Dragwyb/form_builder/after_submission/action/register', $this );
	}


	private function register_default_actions(): void {
		foreach ( $this->default_actions as $action ) {

			$dragwyb_name_space = Helper::namespace_into_dir_path( __NAMESPACE__ );
			$dir                = dirname( $dragwyb_name_space );
			$dir                = Helper::dir_path_into_namespace( $dir );

			$action = explode( '-', $action );
			$action = array_map( 'ucfirst', $action );

			$class = $dir . '\Actions\\' . esc_html( implode( '_', $action ) ) . '_Action';

			if ( class_exists( $class ) ) {
				$this->register_action( new $class() );
			}
		}
	}

	public function register_action( Action_Base $action ): void {
		$this->actions[ $action->get_id() ] = $action;
	}

	public function get_actions(): array {
		return $this->actions;
	}
}
