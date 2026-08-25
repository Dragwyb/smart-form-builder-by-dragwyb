<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Integrations\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Plugin;
use Dragwyb\Form_Builder\Includes\Integrations\Integrations_Manager;

/**
 * Class Form_Widget
 *
 * Elementor widget for embedding Smart Form Builder forms.
 */
class Form_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name(): string {
		return 'dragwyb_form';
	}

	/**
	 * Get widget title.
	 */
	public function get_title(): string {
		return esc_html__( 'Smart Form', 'smart-form-builder-by-dragwyb' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-form-horizontal';
	}

	/**
	 * Get widget categories.
	 */
	public function get_categories(): array {
		return array( 'general', 'basic' );
	}

	/**
	 * Get widget keywords.
	 */
	public function get_keywords(): array {
		return array( 'form', 'contact', 'smart form', 'dragwyb', 'builder' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'section_form',
			array(
				'label' => esc_html__( 'Smart Form Settings', 'smart-form-builder-by-dragwyb' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$forms = Integrations_Manager::get_forms_options();

		$options = array(
			'' => esc_html__( '— Select a Form —', 'smart-form-builder-by-dragwyb' ),
		);

		if ( ! empty( $forms ) ) {
			foreach ( $forms as $id => $title ) {
				$options[ (string) $id ] = $title;
			}
		}

		$this->add_control(
			'form_id',
			array(
				'label'       => esc_html__( 'Select Form', 'smart-form-builder-by-dragwyb' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $options,
				'default'     => '',
				'description' => esc_html__( 'Select the form you want to display on this page.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Compatibility for older Elementor versions.
	 */
	protected function _register_controls(): void {
		$this->register_controls();
	}

	/**
	 * Render widget output on frontend and editor preview.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$form_id  = ! empty( $settings['form_id'] ) ? absint( $settings['form_id'] ) : 0;

		if ( ! empty( $form_id ) ) {
			// Render form using do_shortcode
			echo do_shortcode( '[dragwyb-form id="' . $form_id . '"]' );
			return;
		}

		// If no form selected and in Elementor editor mode, show the placeholder with form selector dropdown
		$is_edit_mode = Plugin::$instance->editor->is_edit_mode();
		if ( $is_edit_mode ) {
			$forms = Integrations_Manager::get_forms_options();
			?>
			<div class="dragwyb-elementor-placeholder" style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 28px 20px; text-align: center; background: #f8fafc; color: #334155; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
				<div style="font-size: 28px; line-height: 1; margin-bottom: 10px; color: #6366f1;">
					<i class="eicon-form-horizontal" aria-hidden="true"></i>
				</div>
				<h4 style="margin: 0 0 6px; font-size: 16px; font-weight: 600; color: #1e293b;">
					<?php esc_html_e( 'Smart Form Builder', 'smart-form-builder-by-dragwyb' ); ?>
				</h4>
				<p style="margin: 0 0 16px; font-size: 13px; color: #64748b;">
					<?php esc_html_e( 'Please select a form to display and preview it here.', 'smart-form-builder-by-dragwyb' ); ?>
				</p>
				<div style="max-width: 320px; margin: 0 auto;">
					<select class="dragwyb-elementor-preview-select" style="width: 100%; height: 38px; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; color: #334155; font-size: 13px; outline: none; cursor: pointer;">
						<option value=""><?php esc_html_e( '— Select a Form —', 'smart-form-builder-by-dragwyb' ); ?></option>
						<?php foreach ( $forms as $id => $title ) : ?>
							<option value="<?php echo esc_attr( (string) $id ); ?>" <?php selected( (string) $form_id, (string) $id ); ?>>
								<?php echo esc_html( $title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<?php
		}
	}
}
