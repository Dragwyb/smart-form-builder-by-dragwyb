<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Gdpr extends Field_Base {

	public function __construct() {
		parent::__construct();
	}

	protected function init(): void {
		$this->type     = 'gdpr';
		$this->name     = __( 'GDPR Consent', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'fas fa-shield-alt';
		$this->category = 'advanced-fields';
		$this->keywords = array( 'gdpr', 'consent', 'privacy', 'agreement', 'checkbox', 'terms' );
	}

	protected function register_field_controls(): void {
		/**
		 * TAB: CONTENT - General Settings
		 */
		$this->start_section(
			'section_content_general',
			array(
				'label' => __( 'General Settings', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::ContentTab,
			)
		);

		$this->add_control(
			'label',
			array(
				'type'  => Controls::TEXT,
				'label' => __( 'Field Label', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'label_icon',
			array(
				'type'  => Controls::ICON,
				'label' => __( 'Label Icon', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'consent_text',
			array(
				'type'        => Controls::TEXTAREA,
				'label'       => __( 'Consent Text', 'smart-form-builder-by-dragwyb' ),
				'default'     => __( 'I consent to this website storing my submitted information so they can respond to my inquiry.', 'smart-form-builder-by-dragwyb' ),
				'rows'        => 3,
				'description' => __( 'The text displayed next to the consent checkbox.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'privacy_policy_url',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Privacy Policy URL', 'smart-form-builder-by-dragwyb' ),
				'default'     => '',
				'placeholder' => 'https://example.com/privacy-policy',
				'description' => __( 'Enter your Privacy Policy page URL to automatically link the text.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'help_text',
			array(
				'type'        => Controls::TEXTAREA,
				'label'       => __( 'Instructional Text', 'smart-form-builder-by-dragwyb' ),
				'rows'        => 2,
				'description' => __( 'A short hint displayed below the GDPR consent field.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'required',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Is Required?', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_section();

		/**
		 * TAB: STYLE - Label Appearance
		 */
		$this->start_section(
			'section_style_label',
			array(
				'label' => __( 'Label Appearance', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::StyleTab,
			)
		);

		$this->add_control(
			'label_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-label-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'label_spacing',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Bottom Margin', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-label-spacing: {{VALUE}}{{UNIT}};' ),
			)
		);

		$this->end_section();

		/**
		 * TAB: STYLE - GDPR Checkbox & Consent Style
		 */
		$this->start_section(
			'section_style_gdpr_toggle',
			array(
				'label' => __( 'Consent & Checkbox Style', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::StyleTab,
			)
		);

		$this->add_control(
			'option_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Consent Text Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-option-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			'option_typography',
			array(
				'type'     => Controls::GROUP_TYPOGRAPHY,
				'label'    => __( 'Typography', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}}',
				'prefix'   => 'option',
			)
		);

		$this->add_control(
			'link_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Privacy Link Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-gdpr-link-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'toggle_size',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Checkbox Size', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 50,
					),
				),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-size: {{VALUE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'toggle_primary_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Primary Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-primary-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'toggle_border_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Border Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-border-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'toggle_spacing',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Spacing', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-spacing: {{VALUE}}{{UNIT}};' ),
			)
		);

		$this->end_section();

		/**
		 * TAB: STYLE - Required Mark Style
		 */
		$this->start_section(
			'section_style_required',
			array(
				'label' => __( 'Required Asterisk Style', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::StyleTab,
			)
		);

		$this->add_control(
			'required_mark_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Required Asterisk Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-asterisk-color: {{VALUE}};' ),
			)
		);

		$this->end_section();
	}

	protected function render_field() {
		$settings = $this->get_field_settings();
		$id       = $this->get_the_id();
		$field_id = $this->field_key_exist( $settings, 'field_id', '' );
		if ( empty( $field_id ) ) {
			$field_id = $id;
		}

		$label              = $this->field_key_exist( $settings, 'label', '' );
		$consent_text       = $this->field_key_exist( $settings, 'consent_text', __( 'I consent to this website storing my submitted information so they can respond to my inquiry.', 'smart-form-builder-by-dragwyb' ) );
		$privacy_policy_url = $this->field_key_exist( $settings, 'privacy_policy_url', '' );
		$help               = $this->field_key_exist( $settings, 'help_text', '' );
		$required           = $this->field_key_exist( $settings, 'required', 'yes' );
		$classes            = $this->field_key_exist( $settings, 'css_classes', '' );

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'    => $this->field_wrapper_id( $id ),
				'class' => $this->field_wrapper_class( $classes, $settings ) . ' dragwyb-no-float',
			)
		);

		$formatted_consent = esc_html( $consent_text );
		if ( ! empty( $privacy_policy_url ) ) {
			$link_html = '<a href="' . esc_url( $privacy_policy_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Privacy Policy', 'smart-form-builder-by-dragwyb' ) . '</a>';
			if ( stripos( $consent_text, 'Privacy Policy' ) !== false ) {
				$formatted_consent = str_ireplace( 'Privacy Policy', $link_html, esc_html( $consent_text ) );
			}
		}

		$input_attrs = array(
			'type'  => 'checkbox',
			'id'    => $field_id,
			'name'  => $field_id,
			'value' => 'yes',
		);

		if ( 'yes' === $required ) {
			$input_attrs['required'] = 'required';
		}

		$this->add_field_attributes( 'input', $input_attrs );

		?>
		<div <?php $this->render_field_attributes( 'wrapper' ); ?>>
			<div class="dragwyb-input-group">
				<?php $this->render_field_label( $field_id, $label, 'yes' === $required, $settings ); ?>
				<div class="dragwyb-options-container">
					<label class="dragwyb-option-item dragwyb-gdpr-item" for="<?php echo esc_attr( $field_id ); ?>">
						<input <?php $this->render_field_attributes( 'input' ); ?> />
						<span class="dragwyb-radio-label"><?php echo wp_kses_post( $formatted_consent ); ?></span>
					</label>
				</div>
			</div>
			<?php if ( ! empty( $help ) ) : ?>
				<div class="dragwyb-field-help"><?php echo esc_html( $help ); ?></div>
			<?php endif; ?>
		</div>
		<?php
	}

	public function validate( $value, $field_id, $form_config, Form_Submission_Handler $error_handler ): void {
		if ( ! isset( $form_config['fields'][ $field_id ] ) ) {
			$error_handler->add_error( $field_id, __( 'Invalid field.', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		$field_attr = isset( $form_config['fields'][ $field_id ]['attributes'] ) ? $form_config['fields'][ $field_id ]['attributes'] : array();
		$required   = isset( $field_attr['required'] ) ? $field_attr['required'] : 'yes';

		if ( 'yes' === $required && ( empty( $value ) || 'yes' !== $value ) ) {
			$error_handler->add_error( $field_id, __( 'You must accept the terms and privacy policy to submit.', 'smart-form-builder-by-dragwyb' ) );
		}
	}

	public function sanitize( $value = null ) {
		if ( $value ) {
			return sanitize_text_field( (string) $value );
		}

		return null;
	}
}
