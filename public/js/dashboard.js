/* ============================================
   DASHBOARD.JS - Gráficos del panel principal
   Usa Chart.js (cargado vía CDN en la vista) y
   los datos embebidos en window.dashboardData.
   ============================================ */
(function () {
    'use strict';

    if (typeof Chart === 'undefined' || !window.dashboardData) {
        return;
    }

    const data = window.dashboardData;

    // Estilos base compartidos
    Chart.defaults.font.family =
        "-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
    Chart.defaults.color = '#6b7280';

    const fmtBs = (v) =>
        'Bs ' + Number(v).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // ───────────── 1. Ingresos por mes (barras) ─────────────
    const elIngresos = document.getElementById('chartIngresos');
    if (elIngresos) {
        const ctx = elIngresos.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(27, 79, 216, 0.35)');
        gradient.addColorStop(1, 'rgba(27, 79, 216, 0.02)');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.ingresos.labels,
                datasets: [{
                    label: 'Recaudado',
                    data: data.ingresos.valores,
                    backgroundColor: gradient,
                    borderColor: '#1B4FD8',
                    borderWidth: 1.5,
                    borderRadius: 8,
                    maxBarThickness: 64,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: (c) => fmtBs(c.parsed.y) },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' },
                        ticks: {
                            callback: (v) => 'Bs ' + Number(v).toLocaleString('es-BO'),
                        },
                    },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    // ───────────── 2. Alumnos por curso (barras horizontales) ─────────────
    const elCursos = document.getElementById('chartCursos');
    if (elCursos) {
        new Chart(elCursos, {
            type: 'bar',
            data: {
                labels: data.cursos.labels,
                datasets: [{
                    label: 'Alumnos',
                    data: data.cursos.valores,
                    backgroundColor: '#1565C0',
                    borderRadius: 6,
                    maxBarThickness: 28,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: (c) => c.parsed.x + ' alumno(s)' },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' },
                        ticks: { precision: 0 },
                    },
                    y: { grid: { display: false } },
                },
            },
        });
    }

    // ───────────── 3. Estado de cartera (donut) ─────────────
    const elCartera = document.getElementById('chartCartera');
    if (elCartera) {
        new Chart(elCartera, {
            type: 'doughnut',
            data: {
                labels: data.cartera.labels, // ['Al Día', 'Mora', 'Matrícula Pendiente']
                datasets: [{
                    data: data.cartera.valores,
                    backgroundColor: ['#16a34a', '#dc2626', '#ea580c'],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 16, boxWidth: 8 },
                    },
                    tooltip: {
                        callbacks: {
                            label: (c) => {
                                const total = c.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total ? Math.round((c.parsed / total) * 100) : 0;
                                return ` ${c.label}: ${c.parsed} (${pct}%)`;
                            },
                        },
                    },
                },
            },
        });
    }
})();
