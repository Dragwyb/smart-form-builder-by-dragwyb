<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Html extends Field_Base {

	public function __construct() {
		parent::__construct();
	}

	protected function init(): void {
		$this->type     = 'html';
		$this->name     = __( 'HTML', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'fas fa-code';
		$this->category = 'structure';
		$this->keywords = array( 'code', 'markup', 'custom' );
	}

	protected function register_field_controls(): void {
		$this->start_section(
			'section_content_general',
			array(
				'label' => __( 'Basic Settings', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::ContentTab,
			)
		);

		$this->add_control(
			'raw_html',
			array(
				'type'    => Controls::WYSIWYG,
				'label'   => __( 'Raw HTML', 'smart-form-builder-by-dragwyb' ),
				'default' => '<p>Enter your custom HTML here.</p>',
			)
		);

		$this->end_section();

		$this->start_section(
			'section_style_container',
			array(
				'label' => __( 'Container Style', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::StyleTab,
			)
		);

		$this->add_control(
			'background_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-html-bg-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Text Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-html-text-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'container_margin',
			array(
				'type'       => Controls::DIMENSIONS,
				'label'      => __( 'Margin', 'smart-form-builder-by-dragwyb' ),
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}}' => '--dragwyb-html-mt: {{TOP}}{{UNIT}}; --dragwyb-html-mr: {{RIGHT}}{{UNIT}}; --dragwyb-html-mb: {{BOTTOM}}{{UNIT}}; --dragwyb-html-ml: {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'container_padding',
			array(
				'type'       => Controls::DIMENSIONS,
				'label'      => __( 'Padding', 'smart-form-builder-by-dragwyb' ),
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}}' => '--dragwyb-html-pt: {{TOP}}{{UNIT}}; --dragwyb-html-pr: {{RIGHT}}{{UNIT}}; --dragwyb-html-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-html-pl: {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_section();
	}

	protected function render_field() {
		$settings = $this->get_field_settings();
		$id       = $this->get_the_id();
		$raw_html = $this->field_key_exist( $settings, 'raw_html', '' );
		$classes  = $this->field_key_exist( $settings, 'css_classes', '' );

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'    => $this->field_wrapper_id( $id ),
				'class' => $this->field_wrapper_class( $classes, $settings ),
			)
		);
		?>
		<div <?php $this->render_field_attributes( 'wrapper' ); ?>>
			<div class="dragwyb-html-content">
				<?php echo wp_kses_post( $raw_html ); ?>
			</div>
		</div>
		<?php
	}

	public function validate( $value, $field_id, $form_config, Form_Submission_Handler $error_handler ): void {
		// HTML field does not submit data.
	}

	/**
	 * Sanitize the field value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @return mixed Sanitized value.
	 */
	public function sanitize( $value = null ) {
		return '';
	}
}
