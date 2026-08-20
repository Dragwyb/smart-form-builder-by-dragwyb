/**
 * Smart Form Builder - Dashboard (Vanilla JavaScript)
 */
(function ($) {
	'use strict';

	let chartInstance = null;

	const state = {
		chartDays: 7,
		statsTimeframe: 'all',
		submissionsPage: 1,
	};

	document.addEventListener('DOMContentLoaded', function () {
		initDashboard();
	});

	function initDashboard() {
		bindEvents();
		fetchDashboardData();
	}

	function bindEvents() {
		const chartDaysSelect = document.getElementById('dragwyb-db-chart-days');
		if (chartDaysSelect) {
			chartDaysSelect.addEventListener('change', function (e) {
				state.chartDays = parseInt(e.target.value, 10) || 7;
				fetchDashboardData();
			});
		}

		const statsTimeframeSelect = document.getElementById('dragwyb-db-stats-timeframe');
		if (statsTimeframeSelect) {
			statsTimeframeSelect.addEventListener('change', function (e) {
				state.statsTimeframe = e.target.value || 'all';
				fetchDashboardData();
			});
		}
	}

	function fetchDashboardData() {
		if (typeof DragwybDashboardApp === 'undefined') {
			return;
		}

		$.ajax({
			url: DragwybDashboardApp.ajax_url,
			type: 'POST',
			data: {
				action: 'dragwyb_get_dashboard_data',
				nonce: DragwybDashboardApp.nonce,
				chart_days: state.chartDays,
				stats_timeframe: state.statsTimeframe,
				submissions_page: state.submissionsPage,
			},
			success: function (res) {
				if (res && res.success && res.data) {
					renderChart(res.data.chart);
					renderStats(res.data.stats);
					renderSubmissions(res.data.submissions, res.data.pagination);
				} else {
					console.error('Failed to load dashboard data:', res);
				}
			},
			error: function (xhr, status, error) {
				console.error('Dashboard AJAX error:', error);
			},
		});
	}

	function renderChart(chartData) {
		const canvas = document.getElementById('dragwyb-db-submissions-chart');
		if (!canvas || !chartData) {
			return;
		}

		const ctx = canvas.getContext('2d');
		if (!ctx) {
			return;
		}

		if (chartInstance && typeof chartInstance.destroy === 'function') {
			chartInstance.destroy();
		}

		if (typeof Chart !== 'undefined') {
			const gradient = ctx.createLinearGradient(0, 0, 0, 300);
			gradient.addColorStop(0, 'rgba(244, 63, 94, 0.25)');
			gradient.addColorStop(1, 'rgba(244, 63, 94, 0.00)');

			chartInstance = new Chart(ctx, {
				type: 'line',
				data: {
					labels: chartData.labels,
					datasets: [
						{
							label: 'Submissions',
							data: chartData.values,
							borderColor: '#f43f5e',
							borderWidth: 3,
							backgroundColor: gradient,
							fill: true,
							tension: 0.4, // Smooth curved Bézier line
							pointBackgroundColor: '#f43f5e',
							pointBorderColor: '#ffffff',
							pointBorderWidth: 2,
							pointRadius: 5,
							pointHoverRadius: 7,
						},
					],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: false,
						},
						tooltip: {
							backgroundColor: '#1e293b',
							titleFont: { family: 'Inter, sans-serif', size: 13 },
							bodyFont: { family: 'Inter, sans-serif', size: 14, weight: 'bold' },
							padding: 10,
							cornerRadius: 8,
							displayColors: false,
							callbacks: {
								label: function (context) {
									return context.raw + ' Submissions';
								},
							},
						},
					},
					scales: {
						x: {
							grid: {
								display: false,
							},
							ticks: {
								font: { family: 'Inter, sans-serif', size: 12 },
								color: '#64748b',
							},
						},
						y: {
							beginAtZero: true,
							suggestedMax: 60,
							grid: {
								color: 'rgba(226, 232, 240, 0.6)',
								borderDash: [5, 5],
							},
							ticks: {
								font: { family: 'Inter, sans-serif', size: 12 },
								color: '#64748b',
								stepSize: 10,
							},
						},
					},
				},
			});
		} else {
			// Fallback Canvas Renderer if Chart.js is not loaded
			renderFallbackCanvasChart(ctx, canvas, chartData);
		}
	}

	function renderFallbackCanvasChart(ctx, canvas, chartData) {
		const width = canvas.width = canvas.parentElement.clientWidth;
		const height = canvas.height = 280;

		ctx.clearRect(0, 0, width, height);

		const padding = 40;
		const labels = chartData.labels || [];
		const values = chartData.values || [];
		const maxVal = Math.max(...values, 60);

		const stepX = (width - padding * 2) / (labels.length - 1 || 1);

		// Grid lines
		ctx.strokeStyle = '#e2e8f0';
		ctx.lineWidth = 1;
		ctx.setLineDash([4, 4]);

		for (let i = 0; i <= 6; i++) {
			const y = height - padding - (i * (height - padding * 2) / 6);
			ctx.beginPath();
			ctx.moveTo(padding, y);
			ctx.lineTo(width - padding, y);
			ctx.stroke();

			ctx.fillStyle = '#64748b';
			ctx.font = '12px Inter, sans-serif';
			ctx.fillText(Math.round((maxVal / 6) * i), 10, y + 4);
		}

		ctx.setLineDash([]);

		// Gradient Fill
		const gradient = ctx.createLinearGradient(0, padding, 0, height - padding);
		gradient.addColorStop(0, 'rgba(244, 63, 94, 0.3)');
		gradient.addColorStop(1, 'rgba(244, 63, 94, 0.0)');

		ctx.beginPath();
		ctx.moveTo(padding, height - padding);

		const points = [];
		for (let i = 0; i < values.length; i++) {
			const x = padding + i * stepX;
			const y = height - padding - (values[i] / maxVal) * (height - padding * 2);
			points.push({ x, y });
			if (i === 0) {
				ctx.lineTo(x, y);
			} else {
				ctx.lineTo(x, y);
			}
		}

		ctx.lineTo(padding + (values.length - 1) * stepX, height - padding);
		ctx.closePath();
		ctx.fillStyle = gradient;
		ctx.fill();

		// Line stroke
		ctx.beginPath();
		for (let i = 0; i < points.length; i++) {
			if (i === 0) {
				ctx.moveTo(points[i].x, points[i].y);
			} else {
				ctx.lineTo(points[i].x, points[i].y);
			}
		}
		ctx.strokeStyle = '#f43f5e';
		ctx.lineWidth = 3;
		ctx.stroke();

		// Points
		points.forEach(p => {
			ctx.beginPath();
			ctx.arc(p.x, p.y, 5, 0, Math.PI * 2);
			ctx.fillStyle = '#f43f5e';
			ctx.fill();
			ctx.strokeStyle = '#ffffff';
			ctx.lineWidth = 2;
			ctx.stroke();
		});

		// X Labels
		ctx.fillStyle = '#64748b';
		ctx.font = '12px Inter, sans-serif';
		labels.forEach((lbl, i) => {
			const x = padding + i * stepX;
			ctx.fillText(lbl, x - 15, height - 10);
		});
	}

	function renderStats(stats) {
		if (!stats) return;

		animateValue('db-stat-forms', stats.total_forms || 0);
		animateValue('db-stat-submissions', stats.total_submissions || 0);
		animateValue('db-stat-visitors', stats.unique_visitors || 0);
		animateValue('db-stat-sessions', stats.total_sessions || 0);
		animateValue('db-stat-pageviews', stats.total_pageviews || 0);
	}

	function animateValue(elementId, targetValue) {
		const el = document.getElementById(elementId);
		if (!el) return;

		const startValue = parseInt(el.textContent, 10) || 0;
		if (startValue === targetValue) {
			el.textContent = targetValue.toLocaleString();
			return;
		}

		const duration = 500;
		const startTime = performance.now();

		function updateNumber(currentTime) {
			const elapsed = currentTime - startTime;
			const progress = Math.min(elapsed / duration, 1);
			const currentValue = Math.floor(startValue + (targetValue - startValue) * progress);
			el.textContent = currentValue.toLocaleString();

			if (progress < 1) {
				requestAnimationFrame(updateNumber);
			} else {
				el.textContent = targetValue.toLocaleString();
			}
		}

		requestAnimationFrame(updateNumber);
	}

	function renderSubmissions(submissions, pagination) {
		const tbody = document.getElementById('dragwyb-db-table-body');

		if (!tbody) return;

		if (!submissions || submissions.length === 0) {
			tbody.innerHTML = `
				<tr>
					<td colspan="6" class="dragwyb-empty-cell">
						<div class="dragwyb-empty-state">
							<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
							<p>No form submissions yet.</p>
						</div>
					</td>
				</tr>
			`;
			return;
		}

		let html = '';
		submissions.forEach(sub => {
			html += `
				<tr>
					<td class="dragwyb-user-cell">
						<div class="dragwyb-avatar-badge" style="background-color: ${escapeHtml(sub.avatar_bg)}; color: ${escapeHtml(sub.avatar_color)};">
							${escapeHtml(sub.initials)}
						</div>
						<span class="dragwyb-user-name">${escapeHtml(sub.name)}</span>
					</td>
					<td class="dragwyb-form-cell">${escapeHtml(sub.form_name)}</td>
					<td class="dragwyb-email-cell">${escapeHtml(sub.email)}</td>
					<td class="dragwyb-date-cell">${escapeHtml(sub.submitted_on)}</td>
					<td class="dragwyb-ip-cell">${escapeHtml(sub.ip_address)}</td>
					<td class="dragwyb-actions-cell">
						<a href="${escapeHtml(sub.view_url)}" class="dragwyb-action-view" title="View Entry">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
						</a>
					</td>
				</tr>
			`;
		});

		tbody.innerHTML = html;
	}

	function escapeHtml(str) {
		if (!str) return '';
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

})(jQuery);
