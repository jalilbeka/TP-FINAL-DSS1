/* ============================================================
   DSS University — Chart.js Visualizations
   ============================================================ */

const chartInstances = {};

/**
 * Resolve CSS custom property values from the current theme.
 */
function getThemeColors() {
  const style = getComputedStyle(document.documentElement);
  const get = (prop) => style.getPropertyValue(prop).trim();
  return {
    chart1: get('--chart-1') || '#6366f1',
    chart2: get('--chart-2') || '#06b6d4',
    chart3: get('--chart-3') || '#f59e0b',
    chart4: get('--chart-4') || '#ec4899',
    textPrimary: get('--text-primary') || '#f0f0f5',
    textSecondary: get('--text-secondary') || '#a0a0b8',
    textMuted: get('--text-muted') || '#6a6a80',
    borderDefault: get('--border-default') || '#2a2a3e',
    bgSecondary: get('--bg-secondary') || '#12121a',
  };
}

const chartColors = ['--chart-1', '--chart-2', '--chart-3', '--chart-4'];

function getChartColorArray() {
  const style = getComputedStyle(document.documentElement);
  return chartColors.map((c) => style.getPropertyValue(c).trim() || '#6366f1');
}

/**
 * Destroy existing chart instance if present.
 */
function destroyChart(key) {
  if (chartInstances[key]) {
    chartInstances[key].destroy();
    delete chartInstances[key];
  }
}

/**
 * Render a vertical bar chart of overall scores per specialization.
 *
 * @param {string|HTMLElement} container - Canvas element or its ID
 * @param {Object} data - { labels: string[], scores: number[] }
 */
function renderBarChart(container, data) {
  const canvas = typeof container === 'string' ? document.getElementById(container) : container;
  if (!canvas) return;

  destroyChart('bar');

  const colors = getChartColorArray();
  const theme = getThemeColors();

  const ctx = canvas.getContext('2d');
  chartInstances['bar'] = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.labels,
      datasets: [
        {
          label: i18n.t('results.score'),
          data: data.scores,
          backgroundColor: data.scores.map((_, i) => colors[i % colors.length] + 'cc'),
          borderColor: data.scores.map((_, i) => colors[i % colors.length]),
          borderWidth: 2,
          borderRadius: 6,
          borderSkipped: false,
          maxBarThickness: 64,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          backgroundColor: theme.bgSecondary,
          titleColor: theme.textPrimary,
          bodyColor: theme.textSecondary,
          borderColor: theme.borderDefault,
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
          callbacks: {
            label(ctx) {
              return `${i18n.t('results.score')}: ${ctx.parsed.y.toFixed(4)}`;
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
            color: theme.textSecondary,
            font: { family: "'Inter'", size: 12, weight: 500 },
          },
          border: {
            color: theme.borderDefault,
          },
        },
        y: {
          beginAtZero: true,
          max: 1,
          grid: {
            color: theme.borderDefault + '40',
            drawBorder: false,
          },
          ticks: {
            color: theme.textMuted,
            font: { family: "'JetBrains Mono'", size: 11 },
            stepSize: 0.2,
          },
          border: {
            display: false,
          },
        },
      },
    },
  });
}

/**
 * Render a radar chart comparing criteria scores across specializations.
 *
 * @param {string|HTMLElement} container - Canvas element or its ID
 * @param {Object} data - { criteriaLabels: string[], datasets: [{ label, values }] }
 */
function renderRadarChart(container, data) {
  const canvas = typeof container === 'string' ? document.getElementById(container) : container;
  if (!canvas) return;

  destroyChart('radar');

  const colors = getChartColorArray();
  const theme = getThemeColors();

  const datasets = data.datasets.map((ds, i) => ({
    label: ds.label,
    data: ds.values,
    backgroundColor: colors[i % colors.length] + '20',
    borderColor: colors[i % colors.length],
    borderWidth: 2,
    pointBackgroundColor: colors[i % colors.length],
    pointBorderColor: colors[i % colors.length],
    pointRadius: 4,
    pointHoverRadius: 6,
  }));

  const ctx = canvas.getContext('2d');
  chartInstances['radar'] = new Chart(ctx, {
    type: 'radar',
    data: {
      labels: data.criteriaLabels,
      datasets,
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: theme.textSecondary,
            font: { family: "'Inter'", size: 12 },
            padding: 16,
            usePointStyle: true,
            pointStyle: 'circle',
          },
        },
        tooltip: {
          backgroundColor: theme.bgSecondary,
          titleColor: theme.textPrimary,
          bodyColor: theme.textSecondary,
          borderColor: theme.borderDefault,
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        },
      },
      scales: {
        r: {
          beginAtZero: true,
          max: 10,
          ticks: {
            stepSize: 2,
            color: theme.textMuted,
            font: { family: "'JetBrains Mono'", size: 10 },
            backdropColor: 'transparent',
          },
          grid: {
            color: theme.borderDefault + '50',
          },
          angleLines: {
            color: theme.borderDefault + '50',
          },
          pointLabels: {
            color: theme.textSecondary,
            font: { family: "'Inter'", size: 11, weight: 500 },
          },
        },
      },
    },
  });
}

/**
 * Re-render all existing charts (e.g. on theme change).
 */
function refreshCharts() {
  Object.values(chartInstances).forEach((chart) => {
    chart.update();
  });
}

// Make available globally
window.charts = { renderBarChart, renderRadarChart, refreshCharts, destroyChart };
