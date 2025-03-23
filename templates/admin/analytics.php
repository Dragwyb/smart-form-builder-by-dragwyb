<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap dragwyb-analytics-wrap">
    <h1><?php esc_html_e('Form Analytics', 'dragwyb-form-builder'); ?></h1>

    <div class="dragwyb-analytics-filters">
        <select id="form-selector">
            <?php
            $forms = get_posts([
                'post_type' => 'dragwyb_form',
                'posts_per_page' => -1,
            ]);

            foreach ($forms as $form) {
                echo sprintf(
                    '<option value="%d">%s</option>',
                    $form->ID,
                    esc_html($form->post_title)
                );
            }
            ?>
        </select>

        <select id="date-range">
            <option value="7days"><?php esc_html_e('Last 7 Days', 'dragwyb-form-builder'); ?></option>
            <option value="30days" selected><?php esc_html_e('Last 30 Days', 'dragwyb-form-builder'); ?></option>
            <option value="90days"><?php esc_html_e('Last 90 Days', 'dragwyb-form-builder'); ?></option>
        </select>
    </div>

    <div class="dragwyb-analytics-grid">
        <div class="dragwyb-analytics-card">
            <h3><?php esc_html_e('Submission Trends', 'dragwyb-form-builder'); ?></h3>
            <canvas id="submission-trends"></canvas>
        </div>

        <div class="dragwyb-analytics-card">
            <h3><?php esc_html_e('Conversion Rate', 'dragwyb-form-builder'); ?></h3>
            <div class="conversion-stats">
                <div class="stat-item">
                    <span class="stat-label"><?php esc_html_e('Views', 'dragwyb-form-builder'); ?></span>
                    <span class="stat-value" id="total-views">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php esc_html_e('Submissions', 'dragwyb-form-builder'); ?></span>
                    <span class="stat-value" id="total-submissions">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php esc_html_e('Conversion Rate', 'dragwyb-form-builder'); ?></span>
                    <span class="stat-value" id="conversion-rate">0%</span>
                </div>
            </div>
        </div>

        <div class="dragwyb-analytics-card">
            <h3><?php esc_html_e('Field Completion Rates', 'dragwyb-form-builder'); ?></h3>
            <canvas id="field-completion"></canvas>
        </div>

        <div class="dragwyb-analytics-card">
            <h3><?php esc_html_e('Error Rates', 'dragwyb-form-builder'); ?></h3>
            <canvas id="error-rates"></canvas>
        </div>

        <div class="dragwyb-analytics-card">
            <h3><?php esc_html_e('Device Breakdown', 'dragwyb-form-builder'); ?></h3>
            <canvas id="device-breakdown"></canvas>
        </div>
    </div>
</div>

<style>
.dragwyb-analytics-wrap {
    margin: 20px;
}

.dragwyb-analytics-filters {
    margin-bottom: 20px;
    display: flex;
    gap: 15px;
}

.dragwyb-analytics-filters select {
    min-width: 200px;
}

.dragwyb-analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.dragwyb-analytics-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
}

.dragwyb-analytics-card h3 {
    margin-top: 0;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.conversion-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    text-align: center;
}

.stat-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 4px;
}

.stat-label {
    display: block;
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

canvas {
    width: 100% !important;
    height: 300px !important;
}
</style> 