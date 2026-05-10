<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\After_Submission\Action_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

abstract class Email_Action_Base extends Action_Base
{

    abstract protected function section_label(): string;

    protected function email_action_settings(): void
    {
        $prefix = $this->get_id();

        $this->start_section('email_section_' . $prefix, [
            'label' => $this->section_label(),
            'conditions' => [
                'after_submissions' => $prefix
            ]
        ]);

        $this->add_control('email_to_' . $prefix, [
            'type'       => Controls::TEXT,
            'label'      => __('Send To (Email)', 'smart-form-builder-by-dragwyb'),
            'default'    => get_option('admin_email'),
            'description' => __('Separate multiple emails with commas.', 'smart-form-builder-by-dragwyb'),
            'required'   => true,
        ]);

        $this->add_control('email_subject_' . $prefix, [
            'type'       => Controls::TEXT,
            'label'      => __('Subject Line', 'smart-form-builder-by-dragwyb'),
            'default'    => __('New Submission: [Form Name]', 'smart-form-builder-by-dragwyb'),
            'required'   => true,
        ]);

        $this->add_control('email_message_body_' . $prefix, [
            'type'       => Controls::WYSIWYG,
            'label'      => __('Message Body', 'smart-form-builder-by-dragwyb'),
            'default'    => '{all_fields}', // Shortcode for all data
            'description' => __('Use {all_fields} to show all data, or use field IDs like {name}.', 'smart-form-builder-by-dragwyb'),
            'required'   => true,
        ]);

        $this->end_section();
    }

    /**
     * Replace shortcodes like {all_fields} or {field_id} with actual form data.
     *
     * @param string $text The text containing shortcodes.
     * @param array $form_data The sanitized form data.
     * @param array $form_config The form configuration.
     * @return string The parsed text.
     */
    protected function replace_shortcodes(string $text, array $form_data, array $form_config): string
    {
        if (empty($text)) {
            return $text;
        }

        // Replace {all_fields}
        if (str_contains($text, '{all_fields}')) {
            $all_fields_text = '';
            foreach ($form_data as $key => $value) {
                // Try to get field label
                $label = $key;
                if (isset($form_config['fields'][$key]['attributes']['label']) && !empty($form_config['fields'][$key]['attributes']['label'])) {
                    $label = $form_config['fields'][$key]['attributes']['label'];
                }

                $val_str = is_array($value) ? implode(', ', $value) : (string) $value;

                if (!empty($val_str)) {
                    $all_fields_text .= sprintf("<strong>%s</strong>: %s<br>\n", esc_html($label), esc_html($val_str));
                }
            }
            $text = str_replace('{all_fields}', $all_fields_text, $text);
        }

        // Replace individual {field_id}
        foreach ($form_data as $key => $value) {
            $val_str = is_array($value) ? implode(', ', $value) : (string) $value;
            $text = str_replace('{' . $key . '}', esc_html($val_str), $text);
        }

        return $text;
    }

    /**
     * Send email and catch potential SMTP errors.
     *
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param array $headers
     * @param Form_Submission_Handler $form_submission
     * @return bool
     */
    protected function send_email(string $to, string $subject, string $message, array $headers, Form_Submission_Handler $form_submission): bool
    {
        $mail_error_message = '';

        // WP 4.4+ allows catching wp_mail errors
        $error_handler = function (\WP_Error $error) use (&$mail_error_message) {
            $mail_error_message = $error->get_error_message();
        };

        add_action('wp_mail_failed', $error_handler);
        $sent = wp_mail($to, $subject, $message, $headers);
        remove_action('wp_mail_failed', $error_handler);

        if (!$sent) {
            $error_msg = !empty($mail_error_message) ? $mail_error_message : __('Email sending failed. Please check your WordPress SMTP configuration.', 'smart-form-builder-by-dragwyb');
            $form_submission->add_error($this->get_id() . '_failed', $error_msg);
        }

        return $sent;
    }
}
