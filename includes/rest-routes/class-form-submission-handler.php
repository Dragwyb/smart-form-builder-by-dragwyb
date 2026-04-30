<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Rest_Routes;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class Form_Submission_Handler
 *
 * Handles validation and submission errors for the form builder.
 *
 * @package Dragwyb\Form_Builder\Includes\Rest_Routes
 */
class Form_Submission_Handler
{

    /**
     * Stores the list of errors.
     *
     * @var array<string, string>
     */
    private array $errors = [];

    /**
     * Add an error for a specific field or identifier.
     *
     * @param string $id      The field ID or error identifier.
     * @param string $message The error message.
     *
     * @return void
     */
    public function add_error(string $id, string $message): void
    {
        $this->errors[$id] = $this->sanitize_error($message);
    }

    /**
     * Remove an error by its ID.
     *
     * @param string $id The field ID or error identifier.
     *
     * @return void
     */
    public function remove_error(string $id): void
    {
        if (isset($this->errors[$id])) {
            unset($this->errors[$id]);
        }
    }

    /**
     * Sanitize an error message.
     *
     * @param string $message The error message to sanitize.
     *
     * @return string The sanitized error message.
     */
    public function sanitize_error(string $message): string
    {
        return sanitize_text_field(wp_unslash($message));
    }

    /**
     * Check if there are any errors.
     *
     * @return bool True if there are errors, false otherwise.
     */
    public function has_errors(): bool
    {
        return ! empty($this->errors);
    }

    /**
     * Get all registered errors.
     *
     * @return array<string, string> The array of errors.
     */
    public function get_errors(): array
    {
        return $this->errors;
    }

    /**
     * Get a specific error by its ID.
     *
     * @param string $id The field ID or error identifier.
     *
     * @return string|null The error message, or null if not found.
     */
    public function get_error(string $id): ?string
    {
        return $this->errors[$id] ?? null;
    }

    /**
     * Clear all errors.
     *
     * @return void
     */
    public function clear_errors(): void
    {
        $this->errors = [];
    }
}
