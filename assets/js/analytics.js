(function ($) {
    'use strict';

    if (typeof DragwybAnalyticsApp === 'undefined') return;

    var cfg = DragwybAnalyticsApp;
    var chartInstances = {};

    var validTabs = ['traffic', 'pages', 'sources', 'devices', 'live'];

    $(document).ready(function () {
        initTabs();
        initDateFilter();
        var initialTab = getUrlTab() || cfg.active_tab || 'traffic';
        switchTab(initialTab);
        loadAnalyticsData(cfg.days);
    });

    window.addEventListener('popstate', function () {
        var currentTab = getUrlTab() || cfg.active_tab || 'traffic';
        switchTab(currentTab);
    });

    function getUrlTab() {
        var params = new URLSearchParams(window.location.search);
        var tab = params.get('tab');
        if (tab && validTabs.indexOf(tab) !== -1) {
            return tab;
        }
        return null;
    }

    function updateUrlTab(tab) {
        if (validTabs.indexOf(tab) === -1) return;
        if (window.history && window.history.pushState) {
            var url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.pushState({ tab: tab }, '', url.toString());
        }
    }

    function switchTab(targetTab) {
        if (validTabs.indexOf(targetTab) === -1) {
            targetTab = 'traffic';
        }
        $('.dragwyb-atab').removeClass('active');
        $('.dragwyb-atab[data-tab="' + targetTab + '"]').addClass('active');

        $('.dragwyb-atab-content').removeClass('active');
        $('#atab-' + targetTab).addClass('active');
    }

    function initTabs() {
        $('.dragwyb-atab').on('click', function () {
            var targetTab = $(this).data('tab');
            switchTab(targetTab);
            updateUrlTab(targetTab);
        });
    }

    function initDateFilter() {
        $('#dragwyb-analytics-days').on('change', function () {
            var days = parseInt($(this).val(), 10) || 30;
            var currentTab = getUrlTab() || cfg.active_tab || 'traffic';
            if (window.history && window.history.pushState) {
                var url = new URL(window.location.href);
                url.searchParams.set('days', days);
                url.searchParams.set('tab', currentTab);
                window.history.pushState({ days: days, tab: currentTab }, '', url.toString());
            }
            loadAnalyticsData(days);
        });
    }

    function loadAnalyticsData(days) {
        $.ajax({
            url: cfg.ajax_url,
            type: 'POST',
            data: {
                action: 'dragwyb_get_analytics_data',
                nonce: cfg.nonce,
                days: days
            },
            success: function (response) {
                if (response.success && response.data) {
                    renderDashboard(response.data);
                } else {
                    alert(cfg.i18n.error);
                }
            },
            error: function () {
                alert(cfg.i18n.error);
            }
        });
    }

    function renderDashboard(data) {
        // Render Stats
        if (data.stats) {
            $('#stat-submissions').text(data.stats.submissions || '0');
            $('#stat-visitors').text(data.stats.visitors || '0');
            $('#stat-sessions').text(data.stats.sessions || '0');
            $('#stat-pageviews').text(data.stats.pageviews || '0');
            $('#stat-duration').text(data.stats.avg_duration || '0m 0s');
            $('#stat-bounce').text(data.stats.bounce_rate || '0%');
        }

        // Render Combined Traffic Overview Chart
        renderLineGraph('analytics-combined-chart', data.daily_stats, [
            {
                label: 'Unique Visitors',
                key: 'visitors',
                borderColor: '#e11d48',
                gradientStart: 'rgba(225, 29, 72, 0.25)',
                gradientStop: 'rgba(225, 29, 72, 0.01)'
            },
            {
                label: 'Total Sessions',
                key: 'sessions',
                borderColor: '#2563eb',
                gradientStart: 'rgba(37, 99, 235, 0.25)',
                gradientStop: 'rgba(37, 99, 235, 0.01)'
            }
        ]);

        // Render Visitors Over Time Graph
        renderLineGraph('analytics-visitors-chart', data.daily_stats, [
            {
                label: 'Unique Visitors',
                key: 'visitors',
                borderColor: '#e11d48',
                gradientStart: 'rgba(225, 29, 72, 0.3)',
                gradientStop: 'rgba(225, 29, 72, 0.02)'
            }
        ]);

        // Render Sessions Over Time Graph
        renderLineGraph('analytics-sessions-chart', data.daily_stats, [
            {
                label: 'Total Sessions',
                key: 'sessions',
                borderColor: '#2563eb',
                gradientStart: 'rgba(37, 99, 235, 0.3)',
                gradientStop: 'rgba(37, 99, 235, 0.02)'
            }
        ]);

        // Render Tables & Sub-charts
        renderTopPagesTable(data.top_pages);
        renderSourcesChart(data.traffic_sources);
        renderSourcesTable(data.traffic_sources);
        renderDevicesChart(data.devices);
        renderLiveVisitors(data.live_visitors);
    }

    function destroyChart(id) {
        if (chartInstances[id]) {
            chartInstances[id].destroy();
            delete chartInstances[id];
        }
    }

    function renderLineGraph(canvasId, dailyStats, datasets) {
        destroyChart(canvasId);

        var canvas = document.getElementById(canvasId);
        if (!canvas) return;

        var ctx = canvas.getContext('2d');
        if (!dailyStats || !dailyStats.length) return;

        var labels = dailyStats.map(function (d) {
            return d.date_val || '';
        });

        // Use Chart.js if available
        if (typeof Chart !== 'undefined') {
            var chartDatasets = datasets.map(function (ds) {
                var gradient = ctx.createLinearGradient(0, 0, 0, 220);
                gradient.addColorStop(0, ds.gradientStart || 'rgba(67, 97, 238, 0.25)');
                gradient.addColorStop(1, ds.gradientStop || 'rgba(67, 97, 238, 0.0)');

                return {
                    label: ds.label,
                    data: dailyStats.map(function (d) { return parseInt(d[ds.key], 10) || 0; }),
                    borderColor: ds.borderColor,
                    backgroundColor: gradient,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: ds.borderColor,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: dailyStats.length === 1 ? 6 : (dailyStats.length > 20 ? 3 : 4),
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: ds.borderColor,
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 3
                };
            });

            chartInstances[canvasId] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: chartDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { size: 13, weight: '700' },
                            bodyFont: { size: 12 },
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: true
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11 },
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 12
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9', drawBorder: false },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11 },
                                precision: 0,
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        } else {
            // HTML5 Canvas Native Smooth Line Graph Fallback
            renderCanvasLineFallback(canvas, dailyStats, datasets);
        }
    }

    function renderCanvasLineFallback(canvas, dailyStats, datasets) {
        var ctx = canvas.getContext('2d');
        var rect = canvas.parentNode.getBoundingClientRect();
        var width = rect.width || 600;
        var height = 240;
        canvas.width = width;
        canvas.height = height;

        var padding = { top: 20, right: 20, bottom: 30, left: 40 };
        var chartW = width - padding.left - padding.right;
        var chartH = height - padding.top - padding.bottom;

        ctx.clearRect(0, 0, width, height);

        // Find max Y value across datasets
        var maxVal = 1;
        datasets.forEach(function (ds) {
            dailyStats.forEach(function (d) {
                var val = parseInt(d[ds.key], 10) || 0;
                if (val > maxVal) maxVal = val;
            });
        });

        // Draw Grid Y lines
        ctx.strokeStyle = '#f1f5f9';
        ctx.lineWidth = 1;
        ctx.fillStyle = '#94a3b8';
        ctx.font = '11px sans-serif';

        var steps = 4;
        for (var i = 0; i <= steps; i++) {
            var yVal = Math.round((maxVal / steps) * i);
            var yPos = padding.top + chartH - (i / steps) * chartH;
            ctx.beginPath();
            ctx.moveTo(padding.left, yPos);
            ctx.lineTo(width - padding.right, yPos);
            ctx.stroke();
            ctx.fillText(yVal, 10, yPos + 4);
        }

        // Plot Datasets
        var pointCount = dailyStats.length;
        var stepX = pointCount > 1 ? chartW / (pointCount - 1) : chartW / 2;

        datasets.forEach(function (ds) {
            var points = [];
            dailyStats.forEach(function (d, idx) {
                var val = parseInt(d[ds.key], 10) || 0;
                var x = padding.left + (pointCount > 1 ? idx * stepX : chartW / 2);
                var y = padding.top + chartH - (val / maxVal) * chartH;
                points.push({ x: x, y: y, val: val, date: d.date_val });
            });

            // Fill area
            if (points.length > 0) {
                var gradient = ctx.createLinearGradient(0, padding.top, 0, padding.top + chartH);
                gradient.addColorStop(0, ds.gradientStart || 'rgba(67, 97, 238, 0.25)');
                gradient.addColorStop(1, ds.gradientStop || 'rgba(67, 97, 238, 0.0)');

                ctx.beginPath();
                ctx.moveTo(points[0].x, padding.top + chartH);
                points.forEach(function (p) { ctx.lineTo(p.x, p.y); });
                ctx.lineTo(points[points.length - 1].x, padding.top + chartH);
                ctx.closePath();
                ctx.fillStyle = gradient;
                ctx.fill();
            }

            // Draw line
            ctx.beginPath();
            ctx.strokeStyle = ds.borderColor;
            ctx.lineWidth = 2.5;
            points.forEach(function (p, idx) {
                if (idx === 0) ctx.moveTo(p.x, p.y);
                else ctx.lineTo(p.x, p.y);
            });
            ctx.stroke();

            // Draw Points
            points.forEach(function (p) {
                ctx.beginPath();
                ctx.arc(p.x, p.y, 4, 0, 2 * Math.PI);
                ctx.fillStyle = '#ffffff';
                ctx.fill();
                ctx.lineWidth = 2;
                ctx.strokeStyle = ds.borderColor;
                ctx.stroke();
            });
        });
    }

    function formatDuration(secVal) {
        var sec = Math.round(parseFloat(secVal) || 0);
        if (sec <= 0) return '0s';

        if (sec > 1000000) {
            sec = Math.round(sec / 1000);
        }

        var h = Math.floor(sec / 3600);
        var m = Math.floor((sec % 3600) / 60);
        var s = sec % 60;

        if (h > 0) {
            return h + 'h ' + m + 'm ' + s + 's';
        }
        if (m > 0) {
            return m + 'm ' + s + 's';
        }
        return s + 's';
    }

    function renderTopPagesTable(pages) {
        var $el = $('#analytics-pages-table');
        if (!pages || !pages.length) {
            $el.html('<p class="dragwyb-no-data">No pageviews recorded yet.</p>');
            return;
        }

        var html = '<table class="dragwyb-table"><thead><tr><th>Page Title</th><th>Path</th><th>Pageviews</th><th>Unique Visitors</th><th>Avg Time</th><th>Avg Scroll</th></tr></thead><tbody>';
        pages.forEach(function (p) {
            var formattedTime = formatDuration(p.avg_time);
            var scrollPct = Math.round(parseFloat(p.avg_scroll) || 0);
            var visitors = parseInt(p.visitors, 10) || 0;
            html += '<tr>';
            html += '<td><strong>' + escapeHtml(p.page_title || 'Untitled') + '</strong></td>';
            html += '<td><code>' + escapeHtml(p.page_path || '/') + '</code></td>';
            html += '<td>' + p.views + '</td>';
            html += '<td>' + visitors + '</td>';
            html += '<td>' + formattedTime + '</td>';
            html += '<td>' + scrollPct + '%</td>';
            html += '</tr>';
        });
        html += '</tbody></table>';

        $el.html(html);
    }

    function renderDoughnutCircleChart(containerId, canvasId, items, labelKey, valueKey, colors, formatName) {
        destroyChart(canvasId);
        var $container = $('#' + containerId);
        if (!items || !items.length) {
            $container.html('<p class="dragwyb-no-data">No data available.</p>');
            return;
        }

        var total = 0;
        var highestVal = -1;
        var highestItem = items[0];

        items.forEach(function (item) {
            var val = parseInt(item[valueKey], 10) || 0;
            total += val;
            if (val > highestVal) {
                highestVal = val;
                highestItem = item;
            }
        });

        var highestPercent = total > 0 ? Math.round((highestVal / total) * 100) : 0;
        var highestName = formatName ? formatName(highestItem[labelKey]) : highestItem[labelKey];

        var html = '<div class="dragwyb-donut-chart-wrap">';
        html += '<canvas id="' + canvasId + '"></canvas>';
        html += '<div class="dragwyb-donut-center">';
        html += '<div class="dragwyb-donut-top-val">' + highestPercent + '%</div>';
        html += '<div class="dragwyb-donut-top-label" title="' + escapeHtml(highestName) + '">' + escapeHtml(highestName) + '</div>';
        html += '</div>';
        html += '</div>';

        // Bottom Legend with colored squares/circles
        html += '<div class="dragwyb-donut-legend">';
        items.forEach(function (item, idx) {
            var val = parseInt(item[valueKey], 10) || 0;
            var pct = total > 0 ? Math.round((val / total) * 100) : 0;
            var name = formatName ? formatName(item[labelKey]) : item[labelKey];
            var color = Array.isArray(colors) ? colors[idx % colors.length] : (colors[item[labelKey]] || '#10b981');
            var isHighest = item === highestItem;

            html += '<div class="dragwyb-donut-legend-item' + (isHighest ? ' highest-item' : '') + '" style="border-left: 4px solid ' + color + ';">';
            html += '<span class="dragwyb-donut-dot" style="background:' + color + ';"></span>';
            html += '<div class="dragwyb-donut-legend-info">';
            html += '<span class="dragwyb-donut-legend-name">' + escapeHtml(name) + '</span>';
            html += '<span class="dragwyb-donut-legend-meta"><strong>' + val + '</strong> (' + pct + '%)</span>';
            html += '</div>';
            html += '</div>';
        });
        html += '</div>';

        $container.html(html);

        var canvas = document.getElementById(canvasId);
        if (!canvas) return;
        var ctx = canvas.getContext('2d');

        var bgColors = items.map(function (item, idx) {
            return Array.isArray(colors) ? colors[idx % colors.length] : (colors[item[labelKey]] || '#10b981');
        });

        var chartData = items.map(function (item) {
            return parseInt(item[valueKey], 10) || 0;
        });

        if (typeof Chart !== 'undefined') {
            chartInstances[canvasId] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: items.map(function (item) { return formatName ? formatName(item[labelKey]) : item[labelKey]; }),
                    datasets: [{
                        data: chartData,
                        backgroundColor: bgColors,
                        borderWidth: items.length === 1 ? 0 : 3,
                        borderColor: items.length === 1 ? bgColors[0] : '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    var val = context.raw || 0;
                                    var pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                    return ' ' + context.label + ': ' + val + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    function renderSourcesChart(sources) {
        var colors = ['#e11d48', '#2563eb', '#9333ea', '#0284c7', '#16a34a', '#f59e0b', '#7c3aed'];
        renderDoughnutCircleChart('analytics-sources-chart', 'analytics-sources-canvas', sources, 'traffic_source', 'sessions', colors, formatSourceLabel);
    }

    function renderSourcesTable(sources) {
        var $el = $('#analytics-sources-table');
        if (!sources || !sources.length) {
            $el.html('<p class="dragwyb-no-data">No data.</p>');
            return;
        }

        var html = '<table class="dragwyb-table"><thead><tr><th>Source</th><th>Sessions</th><th>Visitors</th><th>Bounce Rate</th></tr></thead><tbody>';
        sources.forEach(function (s) {
            var br = Math.round(parseFloat(s.bounce_rate) || 0);
            html += '<tr>';
            html += '<td><strong>' + formatSourceLabel(s.traffic_source) + '</strong></td>';
            html += '<td>' + s.sessions + '</td>';
            html += '<td>' + s.visitors + '</td>';
            html += '<td>' + br + '%</td>';
            html += '</tr>';
        });
        html += '</tbody></table>';

        $el.html(html);
    }

    function renderDevicesChart(devices) {
        var deviceColors = { desktop: '#e11d48', mobile: '#2563eb', tablet: '#0284c7' };
        renderDoughnutCircleChart('analytics-devices-chart', 'analytics-devices-canvas', devices, 'device_type', 'total', deviceColors, function (name) {
            return String(name).charAt(0).toUpperCase() + String(name).slice(1);
        });
    }

    function renderLiveVisitors(live) {
        var $el = $('#analytics-live-table');
        var count = live ? live.length : 0;
        $('#live-count').text(count);

        if (!live || !live.length) {
            $el.html('<p class="dragwyb-no-data">No active visitors in the last 5 minutes.</p>');
            return;
        }

        var html = '<table class="dragwyb-table"><thead><tr><th>Visitor</th><th>Device</th><th>Active Page</th><th>IP Address</th><th>Last Seen</th></tr></thead><tbody>';
        live.forEach(function (l) {
            html += '<tr>';
            html += '<td><code>' + l.visitor_uid.substring(0, 8) + '...</code></td>';
            html += '<td style="text-transform:capitalize">' + escapeHtml(l.device_type || 'Desktop') + '</td>';
            html += '<td><code>' + escapeHtml(l.exit_page || l.entry_page || '/') + '</code></td>';
            html += '<td>' + escapeHtml(l.ip_address || 'Anonymized') + '</td>';
            html += '<td>' + l.ended_at + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table>';

        $el.html(html);
    }

    function formatSourceLabel(src) {
        switch (src) {
            case 'organic_search': return 'Organic Search (SEO)';
            case 'organic_social': return 'Organic Social Media';
            case 'paid_search': return 'Paid Search (Ads)';
            case 'paid_social': return 'Paid Social (Ads)';
            case 'email': return 'Email / Newsletter';
            case 'referral': return 'Referral Link';
            case 'direct': return 'Direct Visit';
            default: return 'Other / Unknown';
        }
    }

    function escapeHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

})(jQuery);
