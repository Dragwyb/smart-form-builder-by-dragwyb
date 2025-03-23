class DragwybAnalyticsDashboard {
    constructor() {
        this.charts = {};
        this.initialize();
    }

    initialize() {
        this.initializeEventListeners();
        this.initializeCharts();
        this.loadAnalytics();
    }

    initializeEventListeners() {
        // Date range selector
        document.getElementById('date-range').addEventListener('change', (e) => {
            const customRange = document.querySelector('.custom-date-range');
            customRange.style.display = e.target.value === 'custom' ? 'inline-block' : 'none';
        });

        // Update button
        document.getElementById('update-analytics').addEventListener('click', () => {
            this.loadAnalytics();
        });
    }

    initializeCharts() {
        // Conversion Timeline Chart
        const conversionCtx = document.getElementById('conversion-chart').getContext('2d');
        this.charts.conversion = new Chart(conversionCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Views',
                        data: [],
                        borderColor: '#2271b1',
                        fill: false
                    },
                    {
                        label: 'Submissions',
                        data: [],
                        borderColor: '#46b450',
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Device Distribution Chart
        const deviceCtx = document.getElementById('device-chart').getContext('2d');
        this.charts.device = new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Desktop', 'Mobile', 'Tablet'],
                datasets: [{
                    data: [0, 0, 0],
                    backgroundColor: ['#2271b1', '#46b450', '#ffb900']
                }]
            },
            options: {
                responsive: true
            }
        });
    }

    async loadAnalytics() {
        try {
            const formId = document.getElementById('form-selector').value;
            const dateRange = document.getElementById('date-range').value;
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;

            const response = await this.makeRequest('get_analytics', {
                form_id: formId,
                date_range: dateRange,
                start_date: startDate,
                end_date: endDate
            });

            if (response.success) {
                this.updateDashboard(response.data);
            } else {
                this.showError(response.data.message);
            }

        } catch (error) {
            this.showError(error.message);
        }
    }

    updateDashboard(data) {
        // Update overview cards
        document.getElementById('total-views').textContent = data.overview.views;
        document.getElementById('total-submissions').textContent = data.overview.submissions;
        document.getElementById('conversion-rate').textContent = `${data.overview.conversion_rate}%`;
        document.getElementById('avg-completion-time').textContent = 
            this.formatDuration(data.overview.avg_completion_time);

        // Update conversion chart
        this.updateConversionChart(data.conversion);

        // Update device chart
        this.updateDeviceChart(data.devices);

        // Update field stats
        this.updateFieldStats(data.fields);
    }

    updateConversionChart(data) {
        this.charts.conversion.data.labels = data.map(item => item.date);
        this.charts.conversion.data.datasets[0].data = data.map(item => item.views);
        this.charts.conversion.data.datasets[1].data = data.map(item => item.submissions);
        this.charts.conversion.update();
    }

    updateDeviceChart(data) {
        this.charts.device.data.datasets[0].data = [
            data.find(item => item.device_type === 'desktop')?.total || 0,
            data.find(item => item.device_type === 'mobile')?.total || 0,
            data.find(item => item.device_type === 'tablet')?.total || 0
        ];
        this.charts.device.update();
    }

    updateFieldStats(data) {
        const tbody = document.getElementById('field-stats');
        tbody.innerHTML = '';

        Object.entries(data).forEach(([field, stats]) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${field}</td>
                <td>${stats.completion_rate}%</td>
                <td>${stats.error_rate}%</td>
                <td>${this.formatDuration(stats.avg_time)}</td>
            `;
            tbody.appendChild(row);
        });
    }

    formatDuration(seconds) {
        if (seconds < 60) {
            return `${seconds}s`;
        }
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}m ${remainingSeconds}s`;
    }

    async makeRequest(action, data) {
        const formData = new FormData();
        formData.append('action', `dragwyb_${action}`);
        formData.append('nonce', dragwybAnalytics.nonce);

        for (const [key, value] of Object.entries(data)) {
            formData.append(key, value);
        }

        const response = await fetch(dragwybAnalytics.ajaxUrl, {
            method: 'POST',
            body: formData
        });

        return await response.json();
    }

    showError(message) {
        // Implementation depends on your notification system
        alert(message);
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', () => {
    new DragwybAnalyticsDashboard();
}); 