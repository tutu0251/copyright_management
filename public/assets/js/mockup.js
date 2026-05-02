/**
 * Copyright Management mockup — theme, modal, toast, tabs, Chart.js
 * TODO: Replace toast/modal triggers with HTMX or app router when backend exists.
 */
(function () {
    'use strict';

    function cssVar(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim() || '#9ca3af';
    }

    function getTheme() {
        return document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
    }

    function setTheme(mode) {
        var m = mode === 'light' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', m);
        try {
            localStorage.setItem('cm_mock_theme', m);
        } catch (e) {}
    }

    function toast(title, message) {
        var host = document.getElementById('ui-toast-host');
        if (!host) return;
        var el = document.createElement('div');
        el.className = 'ui-toast';
        el.innerHTML =
            '<div class="ui-toast__label"></div><div class="ui-toast__msg"></div>';
        el.querySelector('.ui-toast__label').textContent = title;
        el.querySelector('.ui-toast__msg').textContent = message;
        host.appendChild(el);
        setTimeout(function () {
            el.style.opacity = '0';
            el.style.transform = 'translateY(6px)';
            el.style.transition = 'opacity .25s ease, transform .25s ease';
            setTimeout(function () {
                el.remove();
            }, 280);
        }, 3200);
    }

    function openModal(title, bodyHtml) {
        var root = document.getElementById('ui-modal');
        if (!root) return;
        var t = document.getElementById('ui-modal-title');
        var b = document.getElementById('ui-modal-body');
        if (t) t.textContent = title;
        if (b) b.innerHTML = bodyHtml;
        root.hidden = false;
        root.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        var root = document.getElementById('ui-modal');
        if (!root) return;
        root.hidden = true;
        root.setAttribute('aria-hidden', 'true');
    }

    function wireModal() {
        var root = document.getElementById('ui-modal');
        if (!root) return;
        root.addEventListener('click', function (e) {
            var t = e.target;
            if (t && t.closest && t.closest('[data-modal-close]')) closeModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !root.hidden) closeModal();
        });
    }

    function wireTabs() {
        document.querySelectorAll('.ui-tabs').forEach(function (wrap) {
            var tabs = wrap.querySelectorAll('.ui-tabs__tab');
            var panels = wrap.querySelectorAll('.ui-tabs__panel');
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var id = tab.getAttribute('data-tab');
                    tabs.forEach(function (x) {
                        x.classList.toggle('is-active', x === tab);
                        x.setAttribute('aria-selected', x === tab ? 'true' : 'false');
                    });
                    panels.forEach(function (p) {
                        p.classList.toggle('is-active', p.getAttribute('data-tab-panel') === id);
                    });
                });
            });
        });
    }

    var chartRegistry = [];

    function destroyCharts() {
        chartRegistry.forEach(function (c) {
            try {
                c.destroy();
            } catch (e) {}
        });
        chartRegistry = [];
    }

    function axisCommon() {
        return {
            ticks: { color: cssVar('--chart-tick') },
            grid: { color: cssVar('--chart-grid') },
        };
    }

    function chartCommon() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: cssVar('--chart-legend'),
                        boxWidth: 10,
                        padding: 12,
                        usePointStyle: true,
                    },
                },
            },
        };
    }

    function initChartsFromPayload() {
        var el = document.getElementById('chart-payload');
        if (!el || typeof Chart === 'undefined') return;
        var payload;
        try {
            payload = JSON.parse(el.textContent || '{}');
        } catch (e) {
            return;
        }
        destroyCharts();

        var labels = payload.labels || [];
        var reg = payload.registeredWorks || [];
        var lic = payload.activeLicenses || [];
        var inf = payload.infringement || {};
        var infDet = inf.detected || [];
        var infRes = inf.resolved || [];
        var rev = payload.revenue || [];
        var revLabels = rev.map(function (r) {
            return r.month;
        });
        var revAmt = rev.map(function (r) {
            return r.amount;
        });

        var tick = cssVar('--chart-tick');
        var grid = cssVar('--chart-grid');
        var leg = cssVar('--chart-legend');
        var axis = {
            ticks: { color: tick },
            grid: { color: grid },
        };
        var common = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: leg, boxWidth: 10, padding: 12, usePointStyle: true },
                },
            },
        };

        function pushChart(id, cfg) {
            var node = document.getElementById(id);
            if (!node) return;
            chartRegistry.push(new Chart(node, cfg));
        }

        pushChart('chartWorksGrowth', {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'New registrations',
                        data: reg,
                        borderColor: '#818cf8',
                        backgroundColor: 'rgba(129, 140, 248, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#a5b4fc',
                    },
                ],
            },
            options: {
                ...common,
                scales: {
                    x: axis,
                    y: { ...axis, beginAtZero: true, ticks: { ...axis.ticks, precision: 0 } },
                },
            },
        });

        pushChart('chartLicenseActivity', {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Active licenses',
                        data: lic,
                        backgroundColor: 'rgba(52, 211, 153, 0.55)',
                        borderColor: 'rgba(52, 211, 153, 0.95)',
                        borderWidth: 1,
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                ...common,
                scales: {
                    x: axis,
                    y: { ...axis, beginAtZero: true, ticks: { ...axis.ticks, precision: 0 } },
                },
            },
        });

        pushChart('chartInfringement', {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Detected',
                        data: infDet,
                        borderColor: '#fbbf24',
                        backgroundColor: 'rgba(251, 191, 36, 0.08)',
                        fill: false,
                        tension: 0.35,
                        pointRadius: 3,
                    },
                    {
                        label: 'Resolved',
                        data: infRes,
                        borderColor: '#34d399',
                        backgroundColor: 'rgba(52, 211, 153, 0.08)',
                        fill: false,
                        tension: 0.35,
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                ...common,
                scales: {
                    x: axis,
                    y: { ...axis, beginAtZero: true, ticks: { ...axis.ticks, precision: 0 } },
                },
            },
        });

        pushChart('chartRevenue', {
            type: 'line',
            data: {
                labels: revLabels,
                datasets: [
                    {
                        label: 'Revenue (USD)',
                        data: revAmt,
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.2)',
                        fill: true,
                        tension: 0.32,
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                ...common,
                scales: {
                    x: axis,
                    y: {
                        ...axis,
                        beginAtZero: false,
                        ticks: {
                            color: tick,
                            callback: function (v) {
                                return '$' + v / 1000 + 'k';
                            },
                        },
                    },
                },
            },
        });
    }

    function initReportsMiniCharts() {
        var el = document.getElementById('chart-payload');
        if (!el || typeof Chart === 'undefined') return;
        var payload;
        try {
            payload = JSON.parse(el.textContent || '{}');
        } catch (e) {
            return;
        }
        destroyCharts();
        var labels = payload.labels || [];
        var reg = payload.registeredWorks || [];
        var rev = payload.revenue || [];
        var revLabels = rev.map(function (r) {
            return r.month;
        });
        var revAmt = rev.map(function (r) {
            return r.amount;
        });
        var axis = axisCommon();
        var common = chartCommon();

        function pushChart(id, cfg) {
            var node = document.getElementById(id);
            if (!node) return;
            chartRegistry.push(new Chart(node, cfg));
        }

        pushChart('repChartWorks', {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Registrations',
                        data: reg,
                        backgroundColor: 'rgba(99, 102, 241, 0.55)',
                        borderRadius: 6,
                    },
                ],
            },
            options: {
                ...common,
                scales: {
                    x: axis,
                    y: { ...axis, beginAtZero: true, ticks: { ...axis.ticks, precision: 0 } },
                },
            },
        });

        pushChart('repChartRevenue', {
            type: 'line',
            data: {
                labels: revLabels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: revAmt,
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.15)',
                        fill: true,
                        tension: 0.3,
                    },
                ],
            },
            options: {
                ...common,
                scales: {
                    x: axis,
                    y: {
                        ...axis,
                        ticks: {
                            color: cssVar('--chart-tick'),
                            callback: function (v) {
                                return '$' + v / 1000 + 'k';
                            },
                        },
                    },
                },
            },
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        wireModal();
        wireTabs();

        var themeBtn = document.getElementById('theme-toggle');
        if (themeBtn) {
            themeBtn.addEventListener('click', function () {
                setTheme(getTheme() === 'dark' ? 'light' : 'dark');
                destroyCharts();
                var page = document.body.getAttribute('data-page') || '';
                if (page === 'dashboard' && document.getElementById('chart-payload')) initChartsFromPayload();
                if (page === 'reports' && document.getElementById('chart-payload')) initReportsMiniCharts();
            });
        }

        var notif = document.getElementById('btn-notifications');
        if (notif) {
            notif.addEventListener('click', function () {
                toast('Notifications', 'No new alerts — mock inbox is empty.');
            });
        }

        var logout = document.getElementById('btn-logout-mock');
        if (logout) {
            logout.addEventListener('click', function () {
                toast('Session', 'Logout will call POST /auth/logout when wired.');
            });
        }

        document.querySelectorAll('[data-open-modal]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var key = btn.getAttribute('data-open-modal') || '';
                var titles = {
                    register: 'Register work',
                    license: 'Create license',
                    usage: 'Report usage',
                };
                var bodies = {
                    register: '<p class="muted">Form posts to <code>/api/works</code> later.</p>',
                    license: '<p class="muted">License wizard will open here.</p>',
                    usage: '<p class="muted">Usage ingestion pipeline — mock only.</p>',
                };
                openModal(titles[key] || 'Action', bodies[key] || '<p class="muted">No description.</p>');
            });
        });

        var page = document.body.getAttribute('data-page') || '';
        if (page === 'dashboard' && document.getElementById('chartWorksGrowth')) {
            initChartsFromPayload();
        }
        if (page === 'reports' && document.getElementById('repChartWorks')) {
            initReportsMiniCharts();
        }
    });
})();
