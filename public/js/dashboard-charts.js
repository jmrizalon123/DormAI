(function () {
    function readTheme() {
        var root = document.querySelector('.da-app') || document.documentElement;
        var styles = getComputedStyle(root);

        return {
            muted: styles.getPropertyValue('--da-text-muted').trim() || '#94a3b8',
            border: styles.getPropertyValue('--da-border').trim() || '#e2e8f0',
            surface: styles.getPropertyValue('--da-surface').trim() || '#ffffff',
        };
    }

    function hexToRgba(hex, alpha) {
        var normalized = hex.replace('#', '');
        if (normalized.length === 3) {
            normalized = normalized
                .split('')
                .map(function (c) {
                    return c + c;
                })
                .join('');
        }
        var num = parseInt(normalized, 16);
        var r = (num >> 16) & 255;
        var g = (num >> 8) & 255;
        var b = num & 255;

        return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
    }

    function sparkOptions(theme) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 650, easing: 'easeOutQuart' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: theme.surface,
                    titleColor: theme.muted,
                    bodyColor: theme.muted,
                    borderColor: theme.border,
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false,
                },
            },
            scales: {
                x: {
                    display: false,
                    grid: { display: false },
                },
                y: {
                    display: false,
                    grid: { display: false },
                },
            },
        };
    }

    function featuredOptions(theme) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 750, easing: 'easeOutQuart' },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 10,
                        boxHeight: 10,
                        usePointStyle: true,
                        color: theme.muted,
                        font: { size: 11, weight: '600' },
                    },
                },
                tooltip: {
                    backgroundColor: theme.surface,
                    titleColor: theme.muted,
                    bodyColor: theme.muted,
                    borderColor: theme.border,
                    borderWidth: 1,
                    padding: 12,
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: theme.muted,
                        font: { size: 11 },
                        maxRotation: 0,
                    },
                    border: { display: false },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: theme.border, drawTicks: false },
                    ticks: {
                        color: theme.muted,
                        font: { size: 11 },
                        padding: 8,
                    },
                    border: { display: false },
                },
            },
        };
    }

    function buildSparkChart(canvas, config, theme) {
        var color = config.color || '#2563eb';
        var type = config.type === 'bar' ? 'bar' : 'line';

        var dataset = {
            data: config.data || [],
            borderColor: color,
            backgroundColor: type === 'bar' ? hexToRgba(color, 0.72) : hexToRgba(color, 0.14),
            borderWidth: type === 'line' ? 2 : 0,
            fill: type === 'line',
            tension: 0.38,
            pointRadius: 0,
            pointHoverRadius: 3,
            borderRadius: type === 'bar' ? 5 : 0,
            maxBarThickness: 22,
        };

        return new Chart(canvas, {
            type: type,
            data: {
                labels: config.labels || [],
                datasets: [dataset],
            },
            options: sparkOptions(theme),
        });
    }

    function buildFeaturedChart(canvas, payload, theme) {
        return new Chart(canvas, {
            type: payload.type || 'line',
            data: {
                labels: payload.labels || [],
                datasets: payload.datasets || [],
            },
            options: featuredOptions(theme),
        });
    }

    function initDashboardCharts() {
        if (typeof Chart === 'undefined') {
            return;
        }

        var theme = readTheme();
        var instances = [];

        document.querySelectorAll('canvas.da-stat-chart[data-da-chart]').forEach(function (canvas) {
            try {
                var config = JSON.parse(canvas.getAttribute('data-da-chart'));
                instances.push(buildSparkChart(canvas, config, theme));
            } catch (e) {
                /* ignore malformed config */
            }
        });

        document.querySelectorAll('canvas.da-dashboard-chart[data-da-featured]').forEach(function (canvas) {
            try {
                var payload = JSON.parse(canvas.getAttribute('data-da-featured'));
                instances.push(buildFeaturedChart(canvas, payload, theme));
            } catch (e) {
                /* ignore malformed config */
            }
        });

        document.addEventListener('dormai:theme-changed', function () {
            var nextTheme = readTheme();
            instances.forEach(function (chart) {
                if (!chart.options.plugins || !chart.options.plugins.legend) {
                    return;
                }
                chart.options.plugins.legend.labels.color = nextTheme.muted;
                if (chart.options.scales && chart.options.scales.x) {
                    chart.options.scales.x.ticks.color = nextTheme.muted;
                }
                if (chart.options.scales && chart.options.scales.y) {
                    chart.options.scales.y.ticks.color = nextTheme.muted;
                    chart.options.scales.y.grid.color = nextTheme.border;
                }
                chart.update('none');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardCharts);
    } else {
        initDashboardCharts();
    }
})();
