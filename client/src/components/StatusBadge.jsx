// Maps free-text status values to one of the design-system badge tones.
const TONES = {
  success: ['active', 'registered', 'resolved', 'closed', 'paid', 'authorized', 'licensed', 'approved', 'low', 'yes', 'verified', 'public domain', 'public_domain'],
  warning: ['pending', 'draft', 'partial', 'investigating', 'in review', 'in_review', 'review', 'suspected', 'medium', 'expiring', 'on hold', 'on_hold', 'unpaid'],
  danger: ['expired', 'cancelled', 'canceled', 'rejected', 'disputed', 'infringement', 'open', 'high', 'urgent', 'critical', 'overdue', 'no', 'inactive'],
};

export function toneFor(value) {
  const v = String(value ?? '').toLowerCase().trim();
  if (!v) return 'neutral';
  for (const [tone, words] of Object.entries(TONES)) {
    if (words.includes(v)) return tone;
  }
  return 'neutral';
}

function prettify(value) {
  return String(value ?? '—').replace(/[_-]+/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

export function StatusBadge({ value, tone }) {
  if (value === null || value === undefined || value === '') return <span className="muted">—</span>;
  const resolved = tone || toneFor(value);
  return <span className={`ui-badge ui-badge--${resolved}`}>{prettify(value)}</span>;
}
