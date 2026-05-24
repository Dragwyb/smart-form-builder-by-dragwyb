<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Advance_Settings;

use Dragwyb\Form_Builder\Includes\Controls\Register_Controls_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Settings extends Register_Controls_Base {

	/**
	 * Singleton instance
	 *
	 * @var Settings|null
	 */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function init(): void {}

	protected function register_controls(): void {
		$form_id     = absint( $this->get_form_id() );
		$form_title  = sanitize_text_field( get_the_title( $form_id ) );
		$form_status = sanitize_text_field( get_post_status( $form_id ) );

		// 🔹 Form Identity
		$this->start_section(
			'form_identity',
			array(
				'label' => __( 'Form Identity', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'form_name',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Form Name', 'smart-form-builder-by-dragwyb' ),
				'default' => sanitize_text_field( $form_title ),
			)
		);

		$this->add_control(
			'form_id',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Form ID', 'smart-form-builder-by-dragwyb' ),
				'default' => sanitize_text_field( $form_id ),
			)
		);

		$this->add_control(
			'form_status',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Form Status', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'draft'   => __( 'Draft', 'smart-form-builder-by-dragwyb' ),
					'publish' => __( 'Published', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'draft',
				'label_inline' => true,
			)
		);

		$this->end_section();

		// Form Restrictions (Great for Contests)
		$this->start_section(
			'restrictions',
			array(
				'label' => __( 'Restrictions', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'limit_entries',
			array(
				'type'    => Controls::SWITCHER,
				'label'   => __( 'Limit Number of Entries', 'smart-form-builder-by-dragwyb' ),
				'default' => 'no',
			)
		);

		$this->add_control(
			'max_entries',
			array(
				'type'       => Controls::NUMBER,
				'label'      => __( 'Max Entries Allowed', 'smart-form-builder-by-dragwyb' ),
				'default'    => 100,
				'min'        => 1,
				'conditions' => array(
					'limit_entries' => true,
				),
			)
		);

		$this->add_control(
			'limit_message',
			array(
				'type'       => Controls::TEXTAREA,
				'label'      => __( 'Message when limit reached', 'smart-form-builder-by-dragwyb' ),
				'default'    => __( 'This form is no longer accepting submissions.', 'smart-form-builder-by-dragwyb' ),
				'conditions' => array(
					'limit_entries' => true,
				),
			)
		);

		$this->end_section();

		// Security & Privacy
		$this->start_section(
			'security',
			array(
				'label' => __( 'Security & Privacy', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'honeypot',
			array(
				'type'        => Controls::SWITCHER,
				'label'       => __( 'Enable Honeypot (Anti-Spam)', 'smart-form-builder-by-dragwyb' ),
				'default'     => 'no',
				'description' => __( 'Adds an invisible field to trap bots.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'recaptcha',
			array(
				'type'        => Controls::SWITCHER,
				'label'       => __( 'Enable reCAPTCHA', 'smart-form-builder-by-dragwyb' ),
				'default'     => 'no',
				'description' => __( 'Requires API Keys in Global Settings.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->end_section();
	}
}
