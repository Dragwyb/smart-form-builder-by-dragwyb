class DragwybAnalytics {
    constructor() {
        this.charts = {};
        this.initializeEventListeners();
        this.loadAnalytics();
    }

    initializeEventListeners() {
        document.getElementById('form-selector').addEventListener('change', () => this.loadAnalytics());
        document.getElementById('date-range').addEventListener('change', () => this.loadAnalytics());
    }

    async loadAnalytics() {
        const formId = document.getElementById('form-selector').value;
        const dateRange = document.getElementById('date-range').value;

        try {
            const response = await fetch(dragwybAnalytics.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'dragwyb_get_analytics',
                    nonce: dragwybAnalytics.nonce,
                    form_id: formId,
                    date_range: dateRange,
                }),
            });

            const data = await response.json();

            if (data.success) {
                this.updateCharts(data.data);
            }
        } catch (error) {
            console.error('Failed to load analytics:', error);
        }
    }

    updateCharts(data) {
        this.updateSubmissionTrends(data.submission_trends);
        this.updateConversionRate(data.conversion_rate);
        this.updateFieldAnalytics(data.field_analytics);
        this.updateErrorRates(data.error_rates);
        this.updateDeviceBreakdown(data.device_breakdown);
    }

    updateSubmissionTrends(data) {
        const ctx = document.getElementById('submission-trends').getContext('2d');
        
        if (this.charts.submissionTrends) {
            this.charts.submissionTrends.destroy();
        }

        this.charts.submissionTrends = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.date),
                datasets: [{
                    label: 'Submissions',
                    data: data.map(item => item.count),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    updateConversionRate(data) {
        document.getElementById('total-views').textContent = data.views;
        document.getElementById('total-submissions').textContent = data.submissions;
        document.getElementById('conversion-rate').textContent = `${data.rate.toFixed(2)}%`;
    }

    updateFieldAnalytics(data) {
        const ctx = document.getElementById('field-completion').getContext('2d');
        
        if (this.charts.fieldCompletion) {
            this.charts.fieldCompletion.destroy();
        }

        const labels = Object.keys(data);
        const completionRates = labels.map(field => {
            const stats = data[field];
            return (stats.filled / stats.total * 100).toFixed(2);
        });

        this.charts.fieldCompletion = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Completion Rate (%)',
                    data: completionRates,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: value => `${value}%`
                        }
                    }
                }
            }
        });
    }

    updateErrorRates(data) {
        const ctx = document.getElementById('error-rates').getContext('2d');
        
        if (this.charts.errorRates) {
            this.charts.errorRates.destroy();
        }

        const labels = Object.keys(data);
        const errorCounts = Object.values(data);

        this.charts.errorRates = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Errors',
                    data: errorCounts,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    updateDeviceBreakdown(data) {
        const ctx = document.getElementById('device-breakdown').getContext('2d');
        
        if (this.charts.deviceBreakdown) {
            this.charts.deviceBreakdown.destroy();
        }

        this.charts.deviceBreakdown = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Desktop', 'Mobile', 'Tablet'],
                datasets: [{
                    data: [data.desktop, data.mobile, data.tablet],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    formatDate(date) {
        return new Date(date).toLocaleDateString();
    }
}

// Initialize analytics when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new DragwybAnalytics();
}); 