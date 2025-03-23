<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap dragwyb-analytics-dashboard">
    <h1><?php esc_html_e('Form Analytics', 'dragwyb-form-builder'); ?></h1>

    <div class="analytics-filters">
        <select id="form-selector">
            <option value="0"><?php esc_html_e('All Forms', 'dragwyb-form-builder'); ?></option>
            <?php foreach ($forms as $form): ?>
                <option value="<?php echo esc_attr($form->ID); ?>">
                    <?php echo esc_html($form->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select id="date-range">
            <option value="7days"><?php esc_html_e('Last 7 Days', 'dragwyb-form-builder'); ?></option>
            <option value="30days" selected><?php esc_html_e('Last 30 Days', 'dragwyb-form-builder'); ?></option>
            <option value="90days"><?php esc_html_e('Last 90 Days', 'dragwyb-form-builder'); ?></option>
            <option value="year"><?php esc_html_e('Last Year', 'dragwyb-form-builder'); ?></option>
            <option value="custom"><?php esc_html_e('Custom Range', 'dragwyb-form-builder'); ?></option>
        </select>

        <div class="custom-date-range" style="display: none;">
            <input type="date" id="start-date">
            <input type="date" id="end-date">
        </div>

        <button type="button" class="button button-primary" id="update-analytics">
            <?php esc_html_e('Update', 'dragwyb-form-builder'); ?>
        </button>
    </div>

    <div class="analytics-grid">
        <!-- Overview Cards -->
        <div class="analytics-card">
            <h3><?php esc_html_e('Total Views', 'dragwyb-form-builder'); ?></h3>
            <div class="card-value" id="total-views">0</div>
        </div>

        <div class="analytics-card">
            <h3><?php esc_html_e('Submissions', 'dragwyb-form-builder'); ?></h3>
            <div class="card-value" id="total-submissions">0</div>
        </div>

        <div class="analytics-card">
            <h3><?php esc_html_e('Conversion Rate', 'dragwyb-form-builder'); ?></h3>
            <div class="card-value" id="conversion-rate">0%</div>
        </div>

        <div class="analytics-card">
            <h3><?php esc_html_e('Avg. Completion Time', 'dragwyb-form-builder'); ?></h3>
            <div class="card-value" id="avg-completion-time">0s</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="analytics-charts">
        <div class="chart-container">
            <h3><?php esc_html_e('Conversion Timeline', 'dragwyb-form-builder'); ?></h3>
            <canvas id="conversion-chart"></canvas>
        </div>

        <div class="chart-container">
            <h3><?php esc_html_e('Device Distribution', 'dragwyb-form-builder'); ?></h3>
            <canvas id="device-chart"></canvas>
        </div>
    </div>

    <!-- Field Performance -->
    <div class="field-performance">
        <h3><?php esc_html_e('Field Performance', 'dragwyb-form-builder'); ?></h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Field', 'dragwyb-form-builder'); ?></th>
                    <th><?php esc_html_e('Completion Rate', 'dragwyb-form-builder'); ?></th>
                    <th><?php esc_html_e('Error Rate', 'dragwyb-form-builder'); ?></th>
                    <th><?php esc_html_e('Avg. Time to Complete', 'dragwyb-form-builder'); ?></th>
                </tr>
            </thead>
            <tbody id="field-stats">
                <!-- Populated via JavaScript -->
            </tbody>
        </table>
    </div>
</div> 