<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls;

use Dragwyb\Form_Builder\Includes\Controls\Register\register_controls;
use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;

class Controls {

	const CHOOSE         = 'choose';
	const COLOR          = 'color';
	const DIMENSIONS     = 'dimensions';
	const FONTS          = 'fonts';
	const GALLERY        = 'gallery';
	const IMAGE          = 'image';
	const HEADING        = 'heading';
	const ICON           = 'icon';
	const NUMBER         = 'number';
	const POPOVER_TOGGLE = 'popover-toggle';
	const RADIO          = 'radio';
	const RAW_HTML       = 'raw_html';
	const REPEATER       = 'repeater';
	const SECTION        = 'section';
	const SELECT         = 'select';
	const MULTISELECT    = 'multiselect';
	const SLIDER         = 'slider';
	const SWITCHER       = 'switcher';
	const TABS           = 'tabs';
	const TAB            = 'tab';
	const TEXT           = 'text';
	const TEXTAREA       = 'textarea';
	const WYSIWYG        = 'wysiwyg';
	const URL            = 'url';

	const GROUP_TYPOGRAPHY  = 'typography';
	const GROUP_BORDER      = 'border';
	const GROUP_BOX_SHADOW  = 'box-shadow';
	const GROUP_TEXT_SHADOW = 'text-shadow';
	const GROUP_CSS_FILTER  = 'css-filter';
	const GROUP_BACKGROUND  = 'background';

	private static $instance      = null;
	private $controls             = array();
	private array $group_controls = array();

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
		// Load and register all field types
		$this->load_controls();
	}

	private function load_controls(): void {
		// Register control
		$this->register_controls();
	}

	private function register_controls(): void {
		// Load field registrations
		$register             = Register_Controls::instance();
		$this->controls       = $register->get_controls();
		$this->group_controls = $register->get_group_controls();
	}

	public function get_controls(): array {
		return $this->controls;
	}

	public function get_control( $type ): ?Control_Base {
		return $this->controls[ $type ] ?? null;
	}

	public function get_group_controls(): array {
		return $this->group_controls;
	}

	public function get_group_control( $type ): ?Control_Base {
		return $this->group_controls[ $type ] ?? null;
	}
}
