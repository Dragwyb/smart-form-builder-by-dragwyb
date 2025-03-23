<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Validation_Cache {
    private const CACHE_GROUP = 'dragwyb_validation';
    private const CACHE_EXPIRATION = 3600; // 1 hour

    /**
     * Get cached validation result
     */
    public function get_cached_result(string $cache_key): ?array {
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        return $cached !== false ? $cached : null;
    }

    /**
     * Cache validation result
     */
    public function cache_result(string $cache_key, array $result): void {
        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRATION);
    }

    /**
     * Generate cache key
     */
    public function generate_cache_key(array $field, $value): string {
        return md5(serialize([
            'field' => $field,
            'value' => $value,
            'groups' => $field['validation_groups'] ?? [],
        ]));
    }

    /**
     * Clear validation cache
     */
    public function clear_cache(string $form_id = null): void {
        if ($form_id) {
            wp_cache_delete($form_id, self::CACHE_GROUP);
        } else {
            wp_cache_flush();
        }
    }
} 