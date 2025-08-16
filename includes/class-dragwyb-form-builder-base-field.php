<?php
declare(strict_types=1);

abstract class Dragwyb_Form_Builder_Base_Field {
    /**
     * Field type
     */
    protected string $type;

    /**
     * Field label
     */
    protected string $label;

    /**
     * Field options
     */
    protected array $options = [];

    /**
     * Constructor
     */
    public function __construct(string $type, string $label, array $options = []) {
        $this->type = $type;
        $this->label = $label;
        $this->options = wp_parse_args($options, $this->get_default_options());
    }

    /**
     * Get default field options
     */
    protected function get_default_options(): array {
        return [
            'required' => false,
            'placeholder' => '',
            'default_value' => '',
            'css_class' => '',
        ];
    }

    /**
     * Render field in frontend
     */
    abstract protected function render_field();

    /**
     * Validate field value
     */
    abstract public function validate($value): bool;

    /**
     * Sanitize field value
     */
    abstract public function sanitize($value);
} 