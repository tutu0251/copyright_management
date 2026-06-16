import React, { useEffect, useRef } from 'react';
import { Chart, registerables } from 'chart.js';
import { useTheme } from '../context/ThemeContext';

Chart.register(...registerables);

// Read live design-system tokens so charts follow the active theme.
function readTokens() {
  const s = getComputedStyle(document.documentElement);
  const get = (name, fallback) => s.getPropertyValue(name).trim() || fallback;
  return {
    tick: get('--chart-tick', '#9ca3af'),
    grid: get('--chart-grid', 'rgba(148,163,184,0.12)'),
    legend: get('--chart-legend', '#cbd5e1'),
    surface: get('--surface', '#1e293b'),
  };
}

export const CHART_PALETTE = ['#6366f1', '#22d3ee', '#34d399', '#fbbf24', '#fb7185', '#a78bfa', '#38bdf8', '#f97316'];

/**
 * Declarative Chart.js wrapper that rebuilds when `build` deps or theme change.
 * @param {(tokens) => import('chart.js').ChartConfiguration} build
 */
export function ChartCard({ title, hint, height = 248, build, deps = [] }) {
  const canvasRef = useRef(null);
  const chartRef = useRef(null);
  const { theme } = useTheme();

  useEffect(() => {
    if (!canvasRef.current) return undefined;
    const tokens = readTokens();
    chartRef.current?.destroy();
    chartRef.current = new Chart(canvasRef.current, build(tokens));
    return () => {
      chartRef.current?.destroy();
      chartRef.current = null;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [theme, ...deps]);

  return (
    <div className="card chart-card">
      {title && <h2 className="card__title">{title}</h2>}
      {hint && <p className="chart-card__hint">{hint}</p>}
      <div className="chart-canvas-wrap" style={{ height }}>
        <canvas ref={canvasRef} />
      </div>
    </div>
  );
}

// Shared option helpers so every chart picks up themed axes/legend.
export function themedScales(tokens) {
  return {
    x: { ticks: { color: tokens.tick }, grid: { color: tokens.grid } },
    y: { ticks: { color: tokens.tick }, grid: { color: tokens.grid }, beginAtZero: true },
  };
}

export function themedLegend(tokens, position = 'bottom') {
  return { position, labels: { color: tokens.legend, usePointStyle: true, padding: 16 } };
}
