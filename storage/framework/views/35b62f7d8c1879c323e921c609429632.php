<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gracimor LMS — Overdue & Penalties</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.min.js" defer></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=IBM+Plex+Mono:wght@400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --navy:   #0D1B2A;
  --navy2:  #112236;
  --navy3:  #162d47;
  --teal:   #0B8FAC;
  --teal2:  #13AECF;
  --amber:  #F5A623;
  --amber2: #FFC145;
  --red:    #EF4444;
  --red2:   #f97316;
  --crimson:#DC2626;
  --green:  #22C55E;
  --purple: #818CF8;
  --slate:  #94A3B8;
  --slate2: #64748B;
  --white:  #FFFFFF;
  --light:  #F1F5F9;
  --border: rgba(148,163,184,0.15);
  --border2:rgba(148,163,184,0.25);
  --glass:  rgba(13,27,42,0.7);

  /* Severity colours */
  --sev1:   #F5A623;  /* 1–7 days: amber */
  --sev2:   #f97316;  /* 8–30 days: orange */
  --sev3:   #EF4444;  /* 31–60 days: red */
  --sev4:   #DC2626;  /* 61–90 days: crimson */
  --sev5:   #7f1d1d;  /* 90+ days: dark red */
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'DM Sans', sans-serif;
  background: var(--navy);
  color: var(--white);
  min-height: 100vh;
  overflow-x: hidden;
}

/* ── LAYOUT ─────────────────────────────────────────────────────────── */
.layout { display: flex; min-height: 100vh; }

/* SIDEBAR */
.sidebar {
  width: 240px; min-width: 240px;
  background: var(--navy2);
  border-right: 1px solid var(--border);
  display: flex; flex-direction: column;
  padding: 0;
  position: sticky; top: 0; height: 100vh;
  overflow-y: auto;
}

.sidebar-logo {
  padding: 28px 24px 20px;
  border-bottom: 1px solid var(--border);
}
.sidebar-logo .brand {
  font-family: 'Playfair Display', serif;
  font-size: 20px; font-weight: 900;
  color: var(--white);
  letter-spacing: 0.05em;
}
.sidebar-logo .sub {
  font-size: 11px; color: var(--slate);
  margin-top: 2px; letter-spacing: 0.08em;
  text-transform: uppercase;
}

.sidebar-nav { padding: 16px 0; flex: 1; }
.nav-section-label {
  font-size: 10px; color: var(--slate2);
  padding: 12px 24px 6px;
  letter-spacing: 0.12em; text-transform: uppercase;
}
.nav-item {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 24px;
  font-size: 13.5px; font-weight: 500;
  color: var(--slate);
  cursor: pointer; transition: all 0.15s;
  border-left: 3px solid transparent;
  text-decoration: none;
}
.nav-item:hover { color: var(--white); background: rgba(255,255,255,0.04); }
.nav-item.active {
  color: var(--red); background: rgba(239,68,68,0.08);
  border-left-color: var(--red);
}
.nav-item .icon { font-size: 16px; width: 20px; text-align: center; }
.nav-badge {
  margin-left: auto;
  background: var(--red); color: white;
  font-size: 10px; font-weight: 700;
  padding: 2px 6px; border-radius: 8px;
  font-family: 'IBM Plex Mono', monospace;
}

/* MAIN */
.main { flex: 1; display: flex; flex-direction: column; min-width: 0; }

/* TOPBAR */
.topbar {
  background: var(--navy2);
  border-bottom: 1px solid var(--border);
  padding: 0 32px;
  height: 64px;
  display: flex; align-items: center; gap: 16px;
  position: sticky; top: 0; z-index: 100;
}
.topbar-title {
  font-family: 'Playfair Display', serif;
  font-size: 22px; font-weight: 700;
  color: var(--white);
}
.topbar-title span { color: var(--red); }
.topbar-sep { flex: 1; }

.topbar-btn {
  display: flex; align-items: center; gap: 8px;
  padding: 8px 16px; border-radius: 8px;
  font-size: 13px; font-weight: 600;
  cursor: pointer; transition: all 0.2s;
  border: none; font-family: 'DM Sans', sans-serif;
}
.topbar-btn.danger {
  background: linear-gradient(135deg, var(--crimson), var(--red));
  color: white;
  box-shadow: 0 4px 14px rgba(239,68,68,0.35);
}
.topbar-btn.danger:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(239,68,68,0.45); }
.topbar-btn.outline {
  background: transparent; color: var(--slate);
  border: 1px solid var(--border2);
}
.topbar-btn.outline:hover { color: var(--white); border-color: var(--slate); }

/* VIEW TABS */
.view-tabs {
  display: flex; gap: 4px;
  background: rgba(255,255,255,0.04);
  border-radius: 10px; padding: 4px;
}
.view-tab {
  padding: 6px 14px; border-radius: 7px;
  font-size: 12.5px; font-weight: 600;
  cursor: pointer; transition: all 0.15s;
  color: var(--slate); border: none;
  background: transparent; font-family: 'DM Sans', sans-serif;
}
.view-tab.active { background: var(--red); color: white; }

/* CONTENT */
.content { padding: 28px 32px; flex: 1; }

/* ── ALERT STRIP ────────────────────────────────────────────────────── */
.alert-strip {
  background: linear-gradient(90deg, #7f1d1d, #991b1b, var(--crimson));
  border-radius: 12px;
  padding: 16px 24px;
  display: flex; align-items: center; gap: 20px;
  margin-bottom: 28px;
  position: relative; overflow: hidden;
}
.alert-strip::before {
  content: '';
  position: absolute; inset: 0;
  background: repeating-linear-gradient(
    -55deg,
    transparent, transparent 10px,
    rgba(255,255,255,0.03) 10px, rgba(255,255,255,0.03) 20px
  );
}
.alert-icon { font-size: 28px; flex-shrink: 0; position: relative; }
.alert-text { flex: 1; position: relative; }
.alert-text h3 {
  font-size: 15px; font-weight: 700; color: white;
  font-family: 'DM Sans', sans-serif;
}
.alert-text p { font-size: 13px; color: rgba(255,255,255,0.75); margin-top: 3px; }
.alert-actions { display: flex; gap: 10px; position: relative; flex-shrink: 0; }
.alert-btn {
  padding: 8px 16px; border-radius: 8px;
  font-size: 12.5px; font-weight: 700;
  cursor: pointer; transition: all 0.2s;
  border: none; font-family: 'DM Sans', sans-serif;
}
.alert-btn.white { background: white; color: var(--crimson); }
.alert-btn.ghost {
  background: rgba(255,255,255,0.15);
  color: white; border: 1px solid rgba(255,255,255,0.3);
}
.alert-btn:hover { transform: translateY(-1px); }

/* ── STATS GRID ─────────────────────────────────────────────────────── */
.stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 28px; }

.stat-card {
  background: var(--navy2);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 20px;
  position: relative; overflow: hidden;
  transition: all 0.2s;
}
.stat-card:hover { border-color: var(--border2); transform: translateY(-1px); }
.stat-card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 3px;
}
.stat-card.red::before { background: linear-gradient(90deg, var(--crimson), var(--red)); }
.stat-card.orange::before { background: linear-gradient(90deg, var(--red2), var(--amber)); }
.stat-card.amber::before { background: linear-gradient(90deg, var(--amber), var(--amber2)); }
.stat-card.teal::before { background: linear-gradient(90deg, var(--teal), var(--teal2)); }
.stat-card.purple::before { background: linear-gradient(90deg, #6366f1, var(--purple)); }

.stat-label { font-size: 11px; color: var(--slate); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px; }
.stat-value {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 26px; font-weight: 600;
}
.stat-card.red .stat-value    { color: var(--red); }
.stat-card.orange .stat-value { color: var(--red2); }
.stat-card.amber .stat-value  { color: var(--amber); }
.stat-card.teal .stat-value   { color: var(--teal2); }
.stat-card.purple .stat-value { color: var(--purple); }

.stat-sub { font-size: 12px; color: var(--slate); margin-top: 8px; display: flex; align-items: center; gap: 6px; }
.stat-delta { font-size: 11px; font-weight: 700; font-family: 'IBM Plex Mono', monospace; }
.stat-delta.up { color: var(--red); }
.stat-delta.dn { color: var(--green); }

/* ── SEVERITY HEATMAP ───────────────────────────────────────────────── */
.heatmap-section {
  background: var(--navy2);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 20px 24px;
  margin-bottom: 28px;
}
.section-header {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 16px;
}
.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 16px; font-weight: 700; color: var(--white);
}
.section-sub { font-size: 12px; color: var(--slate); margin-left: auto; }

.heatmap-bars { display: flex; gap: 8px; align-items: flex-end; height: 80px; }
.hm-bar-wrap { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; }
.hm-bar {
  width: 100%; border-radius: 6px 6px 3px 3px;
  transition: opacity 0.2s;
  cursor: pointer;
  position: relative;
}
.hm-bar:hover { opacity: 0.85; }
.hm-bar-label { font-size: 10px; color: var(--slate); text-align: center; white-space: nowrap; }
.hm-bar-count {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 11px; font-weight: 600;
  text-align: center;
}

.severity-legend {
  display: flex; gap: 20px; margin-top: 14px; flex-wrap: wrap;
}
.sev-item { display: flex; align-items: center; gap: 6px; }
.sev-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.sev-txt { font-size: 11.5px; color: var(--slate); }

/* ── FILTERS ────────────────────────────────────────────────────────── */
.filters-bar {
  display: flex; gap: 12px; margin-bottom: 20px; align-items: center;
  flex-wrap: wrap;
}
.search-wrap {
  position: relative; flex: 1; min-width: 220px;
}
.search-wrap .icon {
  position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
  color: var(--slate); font-size: 14px;
}
.search-input {
  width: 100%; padding: 9px 12px 9px 36px;
  background: var(--navy2); border: 1px solid var(--border2);
  border-radius: 9px; color: var(--white); font-size: 13px;
  font-family: 'DM Sans', sans-serif;
  transition: border-color 0.2s;
}
.search-input:focus { outline: none; border-color: var(--teal); }
.search-input::placeholder { color: var(--slate2); }

.filter-select {
  padding: 9px 14px;
  background: var(--navy2); border: 1px solid var(--border2);
  border-radius: 9px; color: var(--white); font-size: 13px;
  font-family: 'DM Sans', sans-serif; cursor: pointer;
  transition: border-color 0.2s;
}
.filter-select:focus { outline: none; border-color: var(--teal); }

.filter-pills { display: flex; gap: 8px; }
.filter-pill {
  padding: 7px 14px; border-radius: 20px;
  font-size: 12px; font-weight: 600; cursor: pointer;
  border: 1px solid var(--border2); color: var(--slate);
  background: transparent; transition: all 0.15s;
  font-family: 'DM Sans', sans-serif;
  display: flex; align-items: center; gap: 6px;
}
.filter-pill.active-pill { color: white; border-color: transparent; }
.filter-pill.all.active-pill      { background: var(--slate2); }
.filter-pill.sev1.active-pill     { background: var(--sev1); color: var(--navy); }
.filter-pill.sev2.active-pill     { background: var(--sev2); }
.filter-pill.sev3.active-pill     { background: var(--sev3); }
.filter-pill.critical.active-pill { background: var(--sev4); }
.filter-pill:hover:not(.active-pill) { color: var(--white); border-color: var(--slate); }

.pill-count {
  background: rgba(255,255,255,0.15);
  border-radius: 10px; padding: 1px 6px;
  font-size: 10px; font-weight: 700;
  font-family: 'IBM Plex Mono', monospace;
}

/* ── OVERDUE TABLE ──────────────────────────────────────────────────── */
.table-wrap {
  background: var(--navy2);
  border: 1px solid var(--border);
  border-radius: 14px;
  overflow: hidden;
}
.table-header {
  display: flex; align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  gap: 12px;
}
.table-title { font-size: 14px; font-weight: 700; color: var(--white); }
.table-count { font-size: 12px; color: var(--slate); }
.table-actions { margin-left: auto; display: flex; gap: 8px; }

.overdue-table { width: 100%; border-collapse: collapse; }
.overdue-table thead tr {
  background: rgba(255,255,255,0.03);
  border-bottom: 1px solid var(--border);
}
.overdue-table th {
  padding: 12px 16px;
  font-size: 11px; font-weight: 600;
  color: var(--slate); text-transform: uppercase;
  letter-spacing: 0.06em; text-align: left;
  white-space: nowrap;
}
.overdue-table td {
  padding: 14px 16px;
  font-size: 13.5px;
  border-bottom: 1px solid var(--border);
  vertical-align: middle;
}
.overdue-table tbody tr {
  cursor: pointer; transition: background 0.12s;
}
.overdue-table tbody tr:hover { background: rgba(255,255,255,0.03); }
.overdue-table tbody tr.selected { background: rgba(239,68,68,0.07); border-left: 3px solid var(--red); }
.overdue-table tbody tr:last-child td { border-bottom: none; }

/* Severity badge */
.sev-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 10px; border-radius: 20px;
  font-size: 11px; font-weight: 700;
  font-family: 'IBM Plex Mono', monospace;
  white-space: nowrap;
}
.sev-badge.s1 { background: rgba(245,166,35,0.15);  color: var(--sev1); }
.sev-badge.s2 { background: rgba(249,115,22,0.15);  color: var(--sev2); }
.sev-badge.s3 { background: rgba(239,68,68,0.15);   color: var(--sev3); }
.sev-badge.s4 { background: rgba(220,38,38,0.2);    color: #fca5a5; }
.sev-badge.s5 { background: rgba(127,29,29,0.4);    color: #fca5a5; }
.sev-badge::before {
  content: '●'; font-size: 8px;
}

/* Status badge */
.status-badge {
  display: inline-flex; align-items: center;
  padding: 3px 9px; border-radius: 20px;
  font-size: 11px; font-weight: 600;
}
.status-badge.active   { background: rgba(34,197,94,0.12);  color: var(--green); }
.status-badge.defaulted{ background: rgba(127,29,29,0.3);   color: #fca5a5; }
.status-badge.critical { background: rgba(220,38,38,0.2);   color: #fca5a5; }

.mono { font-family: 'IBM Plex Mono', monospace; }
.money { font-family: 'IBM Plex Mono', monospace; font-weight: 600; }
.red-money { color: var(--red); }
.amber-money { color: var(--amber); }

/* Avatar cell */
.borrower-cell { display: flex; align-items: center; gap: 10px; }
.avatar-sm {
  width: 34px; height: 34px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 700; flex-shrink: 0;
  font-family: 'DM Sans', sans-serif;
}

/* Progress mini */
.mini-progress { display: flex; flex-direction: column; gap: 3px; min-width: 90px; }
.mini-bar { height: 4px; border-radius: 2px; background: rgba(255,255,255,0.08); overflow: hidden; }
.mini-fill { height: 100%; border-radius: 2px; }

/* Row actions */
.row-actions { display: flex; gap: 6px; }
.row-btn {
  padding: 5px 10px; border-radius: 6px;
  font-size: 11px; font-weight: 600;
  cursor: pointer; transition: all 0.15s;
  border: 1px solid var(--border2);
  background: transparent; color: var(--slate);
  font-family: 'DM Sans', sans-serif;
  white-space: nowrap;
}
.row-btn:hover { color: var(--white); border-color: var(--slate); }
.row-btn.danger { color: var(--red); border-color: rgba(239,68,68,0.3); }
.row-btn.danger:hover { background: rgba(239,68,68,0.1); }
.row-btn.amber { color: var(--amber); border-color: rgba(245,166,35,0.3); }
.row-btn.amber:hover { background: rgba(245,166,35,0.1); }

/* ── DETAIL PANEL ───────────────────────────────────────────────────── */
.detail-layout { display: grid; grid-template-columns: 1fr 380px; gap: 20px; }

.panel {
  background: var(--navy2);
  border: 1px solid var(--border);
  border-radius: 14px; overflow: hidden;
}
.panel-head {
  padding: 18px 22px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; gap: 12px;
}
.panel-title {
  font-family: 'Playfair Display', serif;
  font-size: 16px; font-weight: 700;
}
.panel-body { padding: 22px; }

/* Loan summary card */
.loan-summary-card {
  background: linear-gradient(135deg, #1a0a0a, #1f1010, #200d0d);
  border: 1px solid rgba(220,38,38,0.25);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  position: relative; overflow: hidden;
}
.loan-summary-card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, var(--crimson), var(--red), var(--red2));
}
.lsc-row { display: flex; justify-content: space-between; align-items: center; }
.lsc-name {
  font-size: 18px; font-weight: 700; color: white;
  font-family: 'DM Sans', sans-serif;
}
.lsc-num {
  font-size: 12px; color: var(--slate);
  font-family: 'IBM Plex Mono', monospace;
}
.lsc-metrics {
  display: grid; grid-template-columns: 1fr 1fr 1fr;
  gap: 16px; margin-top: 18px;
}
.lsc-metric-label { font-size: 10px; color: var(--slate); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px; }
.lsc-metric-value {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 18px; font-weight: 600;
}
.lsc-metric-value.red { color: var(--red); }
.lsc-metric-value.amber { color: var(--amber); }
.lsc-metric-value.white { color: white; }

/* Instalment rows */
.instalment-list { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
.inst-row {
  border: 1px solid var(--border);
  border-radius: 10px; padding: 14px 16px;
  display: flex; align-items: center; gap: 14px;
  transition: all 0.15s;
  cursor: pointer;
}
.inst-row:hover { border-color: var(--border2); }
.inst-row.overdue {
  border-color: rgba(239,68,68,0.25);
  background: rgba(239,68,68,0.04);
}
.inst-row.paid {
  border-color: rgba(34,197,94,0.2);
  background: rgba(34,197,94,0.03);
  opacity: 0.7;
}
.inst-num {
  width: 32px; height: 32px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-family: 'IBM Plex Mono', monospace;
  font-size: 11px; font-weight: 700;
  flex-shrink: 0;
}
.inst-num.overdue { background: rgba(239,68,68,0.15); color: var(--red); }
.inst-num.paid    { background: rgba(34,197,94,0.12); color: var(--green); }
.inst-num.pending { background: rgba(148,163,184,0.1); color: var(--slate); }
.inst-num.partial { background: rgba(245,166,35,0.12); color: var(--amber); }

.inst-info { flex: 1; }
.inst-date { font-size: 12.5px; color: var(--slate); }
.inst-amount { font-family: 'IBM Plex Mono', monospace; font-size: 14px; font-weight: 600; color: white; }

.inst-penalty-tag {
  font-size: 11px; color: var(--red);
  background: rgba(239,68,68,0.12);
  padding: 2px 8px; border-radius: 6px;
  font-family: 'IBM Plex Mono', monospace;
}
.inst-days { font-size: 11px; color: var(--red); font-weight: 700; white-space: nowrap; }
.inst-paid-tag {
  font-size: 11px; color: var(--green);
  background: rgba(34,197,94,0.1);
  padding: 2px 8px; border-radius: 6px;
}

/* Penalty history */
.penalty-log { display: flex; flex-direction: column; gap: 8px; }
.pen-item {
  border-radius: 8px; padding: 12px 14px;
  border: 1px solid var(--border);
  display: flex; align-items: center; gap: 12px;
}
.pen-item.outstanding { border-color: rgba(239,68,68,0.2); background: rgba(239,68,68,0.04); }
.pen-item.waived      { border-color: rgba(34,197,94,0.15); background: rgba(34,197,94,0.03); opacity: 0.7; }
.pen-date { font-size: 11px; color: var(--slate); font-family: 'IBM Plex Mono', monospace; min-width: 80px; }
.pen-amount { font-family: 'IBM Plex Mono', monospace; font-size: 14px; font-weight: 600; }
.pen-item.outstanding .pen-amount { color: var(--red); }
.pen-item.waived .pen-amount { color: var(--green); text-decoration: line-through; opacity: 0.7; }
.pen-status {
  margin-left: auto; font-size: 11px; font-weight: 700;
  padding: 2px 8px; border-radius: 6px;
}
.pen-status.outstanding { color: var(--red); background: rgba(239,68,68,0.1); }
.pen-status.waived { color: var(--green); background: rgba(34,197,94,0.1); }

/* Action buttons stack */
.action-stack { display: flex; flex-direction: column; gap: 10px; }
.action-btn {
  width: 100%; padding: 12px 20px;
  border-radius: 10px;
  font-size: 13.5px; font-weight: 700;
  cursor: pointer; transition: all 0.2s;
  border: none; font-family: 'DM Sans', sans-serif;
  display: flex; align-items: center; justify-content: center; gap: 10px;
}
.action-btn.primary {
  background: linear-gradient(135deg, var(--teal), var(--teal2));
  color: white; box-shadow: 0 4px 14px rgba(11,143,172,0.3);
}
.action-btn.primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(11,143,172,0.4); }
.action-btn.waive {
  background: linear-gradient(135deg, #166534, var(--green));
  color: white; box-shadow: 0 4px 14px rgba(34,197,94,0.2);
}
.action-btn.waive:hover { transform: translateY(-1px); }
.action-btn.escalate {
  background: linear-gradient(135deg, var(--crimson), var(--red));
  color: white; box-shadow: 0 4px 14px rgba(239,68,68,0.25);
}
.action-btn.escalate:hover { transform: translateY(-1px); }
.action-btn.ghost-btn {
  background: transparent; color: var(--slate);
  border: 1px solid var(--border2);
}
.action-btn.ghost-btn:hover { color: var(--white); border-color: var(--slate); }

/* ── TIMELINE ───────────────────────────────────────────────────────── */
.timeline { display: flex; flex-direction: column; gap: 0; }
.tl-item {
  display: flex; gap: 14px;
  padding-bottom: 18px;
  position: relative;
}
.tl-item:last-child { padding-bottom: 0; }
.tl-item::before {
  content: '';
  position: absolute; left: 15px; top: 28px; bottom: 0; width: 2px;
  background: var(--border); z-index: 0;
}
.tl-item:last-child::before { display: none; }
.tl-dot {
  width: 32px; height: 32px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; flex-shrink: 0; z-index: 1;
  border: 2px solid var(--border);
}
.tl-dot.red    { background: rgba(239,68,68,0.15); border-color: var(--red); }
.tl-dot.amber  { background: rgba(245,166,35,0.12); border-color: var(--amber); }
.tl-dot.green  { background: rgba(34,197,94,0.12); border-color: var(--green); }
.tl-dot.teal   { background: rgba(11,143,172,0.12); border-color: var(--teal); }
.tl-dot.purple { background: rgba(129,140,248,0.12); border-color: var(--purple); }
.tl-dot.slate  { background: rgba(148,163,184,0.08); border-color: var(--border2); }

.tl-content { flex: 1; }
.tl-title { font-size: 13.5px; font-weight: 600; color: var(--white); }
.tl-desc  { font-size: 12px; color: var(--slate); margin-top: 2px; }
.tl-date  { font-size: 11px; color: var(--slate2); font-family: 'IBM Plex Mono', monospace; margin-top: 3px; }

/* ── MODALS ─────────────────────────────────────────────────────────── */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);
  z-index: 500;
  display: flex; align-items: center; justify-content: center;
  padding: 24px;
}
.modal {
  background: var(--navy2);
  border: 1px solid var(--border2);
  border-radius: 16px;
  width: 100%; max-width: 520px;
  max-height: 90vh; overflow-y: auto;
  box-shadow: 0 24px 60px rgba(0,0,0,0.5);
  animation: modalIn 0.2s ease;
}
@keyframes modalIn {
  from { opacity: 0; transform: scale(0.96) translateY(8px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-head {
  padding: 22px 24px 18px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; gap: 12px;
}
.modal-icon {
  width: 40px; height: 40px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; flex-shrink: 0;
}
.modal-icon.red    { background: rgba(239,68,68,0.15); }
.modal-icon.green  { background: rgba(34,197,94,0.12); }
.modal-icon.amber  { background: rgba(245,166,35,0.12); }
.modal-icon.purple { background: rgba(129,140,248,0.12); }

.modal-title { font-size: 17px; font-weight: 700; color: var(--white); }
.modal-subtitle { font-size: 12.5px; color: var(--slate); margin-top: 2px; }
.modal-close {
  margin-left: auto; background: transparent; border: none;
  color: var(--slate); cursor: pointer; font-size: 18px; padding: 4px;
}
.modal-close:hover { color: var(--white); }

.modal-body { padding: 22px 24px; }
.modal-foot {
  padding: 18px 24px;
  border-top: 1px solid var(--border);
  display: flex; gap: 10px; justify-content: flex-end;
}

/* Form elements */
.form-group { margin-bottom: 18px; }
.form-label {
  display: block; font-size: 12px; font-weight: 600;
  color: var(--slate); text-transform: uppercase; letter-spacing: 0.06em;
  margin-bottom: 7px;
}
.form-input, .form-select, .form-textarea {
  width: 100%; padding: 10px 14px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--border2);
  border-radius: 9px; color: var(--white);
  font-size: 14px; font-family: 'DM Sans', sans-serif;
  transition: border-color 0.2s;
}
.form-input:focus, .form-select:focus, .form-textarea:focus {
  outline: none; border-color: var(--teal);
  background: rgba(11,143,172,0.05);
}
.form-textarea { resize: vertical; min-height: 90px; }

.info-box {
  background: rgba(11,143,172,0.08);
  border: 1px solid rgba(11,143,172,0.2);
  border-radius: 9px; padding: 12px 14px;
  font-size: 13px; color: var(--teal2);
  display: flex; gap: 10px; align-items: flex-start;
  margin-bottom: 18px;
}
.warn-box {
  background: rgba(245,166,35,0.08);
  border: 1px solid rgba(245,166,35,0.2);
  border-radius: 9px; padding: 12px 14px;
  font-size: 13px; color: var(--amber);
  display: flex; gap: 10px; align-items: flex-start;
  margin-bottom: 18px;
}

/* Confirm box */
.confirm-box {
  background: rgba(239,68,68,0.07);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: 10px; padding: 16px;
  margin-bottom: 18px;
}
.confirm-amount {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 28px; font-weight: 700;
  color: var(--red); text-align: center;
  margin: 8px 0;
}

/* Btn variants in modal */
.btn { padding: 10px 20px; border-radius: 9px; font-size: 13.5px; font-weight: 700; cursor: pointer; border: none; font-family: 'DM Sans', sans-serif; transition: all 0.2s; }
.btn.red    { background: linear-gradient(135deg, var(--crimson), var(--red)); color: white; }
.btn.green  { background: linear-gradient(135deg, #166534, var(--green)); color: white; }
.btn.amber  { background: linear-gradient(135deg, #92400e, var(--amber)); color: white; }
.btn.ghost  { background: transparent; border: 1px solid var(--border2); color: var(--slate); }
.btn:hover  { transform: translateY(-1px); }
.btn.ghost:hover { color: var(--white); border-color: var(--slate); }

/* ── TOAST ──────────────────────────────────────────────────────────── */
.toast-stack {
  position: fixed; bottom: 24px; right: 24px;
  display: flex; flex-direction: column; gap: 10px; z-index: 900;
}
.toast {
  padding: 13px 18px;
  border-radius: 10px;
  font-size: 13.5px; font-weight: 600;
  display: flex; align-items: center; gap: 10px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.4);
  animation: toastIn 0.3s ease;
  min-width: 260px;
}
@keyframes toastIn {
  from { opacity: 0; transform: translateX(20px); }
  to   { opacity: 1; transform: translateX(0); }
}
.toast.success { background: #14532d; border: 1px solid #166534; color: #86efac; }
.toast.error   { background: #450a0a; border: 1px solid var(--crimson); color: #fca5a5; }
.toast.info    { background: #0c4a6e; border: 1px solid var(--teal); color: #7dd3fc; }
.toast.warn    { background: #451a03; border: 1px solid var(--amber); color: #fde68a; }

/* ── COLLECTIONS VIEW ───────────────────────────────────────────────── */
.collection-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }

.call-card {
  background: var(--navy3);
  border: 1px solid var(--border);
  border-radius: 12px; padding: 18px;
  transition: all 0.15s; cursor: pointer;
}
.call-card:hover { border-color: var(--border2); transform: translateY(-1px); }
.call-card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.call-card-name { font-size: 15px; font-weight: 700; }
.call-card-loan { font-size: 12px; color: var(--slate); font-family: 'IBM Plex Mono', monospace; }
.call-priority {
  margin-left: auto; padding: 3px 10px; border-radius: 20px;
  font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;
}
.call-priority.p1 { background: rgba(220,38,38,0.2); color: #fca5a5; }
.call-priority.p2 { background: rgba(239,68,68,0.15); color: var(--red); }
.call-priority.p3 { background: rgba(245,166,35,0.12); color: var(--amber); }

.call-meta { display: flex; gap: 16px; font-size: 12px; color: var(--slate); }
.call-meta span { display: flex; align-items: center; gap: 4px; }

.call-notes {
  font-size: 12.5px; color: var(--slate);
  border-top: 1px solid var(--border);
  padding-top: 10px; margin-top: 10px;
  font-style: italic;
}
.call-actions { display: flex; gap: 8px; margin-top: 12px; }
.call-btn {
  flex: 1; padding: 8px; border-radius: 8px;
  font-size: 12px; font-weight: 700; cursor: pointer;
  border: 1px solid var(--border2); background: transparent;
  color: var(--slate); transition: all 0.15s;
  font-family: 'DM Sans', sans-serif;
  display: flex; align-items: center; justify-content: center; gap: 6px;
}
.call-btn:hover { color: var(--white); border-color: var(--white); }
.call-btn.green { color: var(--green); border-color: rgba(34,197,94,0.3); }
.call-btn.green:hover { background: rgba(34,197,94,0.1); }

/* ── SCROLLBAR ──────────────────────────────────────────────────────── */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: var(--navy); }
::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.2); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: rgba(148,163,184,0.35); }

/* ── ANIMATIONS ─────────────────────────────────────────────────────── */
.fade-up { animation: fadeUp 0.4s ease both; }
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}
.stagger > * { animation: fadeUp 0.35s ease both; }
.stagger > *:nth-child(1) { animation-delay: 0.0s; }
.stagger > *:nth-child(2) { animation-delay: 0.05s; }
.stagger > *:nth-child(3) { animation-delay: 0.10s; }
.stagger > *:nth-child(4) { animation-delay: 0.15s; }
.stagger > *:nth-child(5) { animation-delay: 0.20s; }

/* Pulse dot for live */
.pulse-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--red);
  animation: pulse 1.8s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50%       { opacity: 0.5; transform: scale(1.4); }
}

/* Checkbox */
.cb-row { display: flex; align-items: center; gap: 8px; cursor: pointer; }
.cb-row input[type=checkbox] { width: 15px; height: 15px; accent-color: var(--teal); cursor: pointer; }
</style>

  <style id="lms-responsive">
/* ══════════════════════════════════════════════════════════════════════════
   LMS Mobile Responsive  v3
   Breakpoints: 768px (tablet/phone)  |  480px (small phone)
══════════════════════════════════════════════════════════════════════════ */

/* body only — NOT html, so inner scroll containers still work */
body { overflow-x: hidden; }

* { box-sizing: border-box; }

/* ── Overlay backdrop ─────────────────────────────────────────────────── */
#lms-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.6);
  z-index: 149;
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
}
#lms-overlay.open { display: block; }

/* ── Hamburger (hidden on desktop) ───────────────────────────────────── */
#lms-hamburger {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px 8px;
  color: var(--white, #F0F6FF);
  border-radius: 6px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  transition: background .15s;
  margin-right: 4px;
}
#lms-hamburger:hover { background: rgba(255,255,255,.1); }

/* ══════════════════════════════════════════════════════════════════════════
   768px — Tablets and phones
══════════════════════════════════════════════════════════════════════════ */
@media (max-width: 768px) {

  #lms-hamburger { display: flex !important; }

  /* ── Sidebar: slide in from left ─────────────────────────────────── */
  .sidebar {
    transform: translateX(-100%);
    transition: transform .25s cubic-bezier(.4,0,.2,1);
    position: fixed !important;
    height: 100vh !important;
    top: 0 !important;
    z-index: 200 !important;
    overflow-y: auto;
    width: 260px !important;
  }
  .sidebar.open { transform: translateX(0); }
  .shell .sidebar { position: fixed !important; }

  /* ── Main: full width ─────────────────────────────────────────────── */
  .main  { margin-left: 0 !important; width: 100% !important; }
  .shell { flex-direction: column !important; }
  .shell .main { margin-left: 0 !important; width: 100% !important; }

  /* ── Topbar ───────────────────────────────────────────────────────── */
  .topbar {
    padding: 0 14px !important;
    height: auto !important;
    min-height: 56px !important;
    flex-wrap: wrap;
    gap: 6px;
    padding-top: 8px !important;
    padding-bottom: 8px !important;
  }
  .page-title { font-size: 15px !important; }
  .breadcrumb { font-size: 12px !important; }

  /* ── Content area ─────────────────────────────────────────────────── */
  .content { padding: 12px !important; width: 100% !important; }

  /* Every direct child of content: full width */
  .content > * { max-width: 100% !important; width: 100% !important; }

  /* ── ALL grids → single column ────────────────────────────────────── */
  .stats-row,
  .loan-layout,
  .pay-layout,
  .cal-layout,
  .builder-layout,
  .edit-layout,
  .prod-grid,
  .ov-stats,
  .sev-row,
  .sel-loan-grid,
  .type-pills,
  .method-tabs,
  /* Overdue page */
  .stats-grid,
  .detail-layout,
  .collection-grid,
  .ov-detail-grid,
  /* Reports page */
  .date-row,
  .rh-kpis,
  .par-grid,
  .stmt-info-grid,
  .stmt-sum-boxes,
  /* Settings page */
  .pc-params,
  .gr2,
  .gr3,
  .rp-grid,
  .tpl-layout,
  .sys-grid {
    grid-template-columns: 1fr !important;
    gap: 10px !important;
  }

  /* Stats: 2 col (they're small enough) */
  .stats-row { grid-template-columns: 1fr 1fr !important; }
  .ov-stats, .sev-row { grid-template-columns: 1fr 1fr !important; }
  /* Overdue stats: 2 col */
  .stats-grid { grid-template-columns: 1fr 1fr !important; }

  /* ── Dashboard grids → single column ─────────────────────────────── */
  .grid-4 { grid-template-columns: 1fr 1fr !important; gap: 10px !important; }
  .grid-3 { grid-template-columns: 1fr !important; gap: 10px !important; }
  .grid-2-1 { grid-template-columns: 1fr !important; gap: 10px !important; }
  .grid-1-1 { grid-template-columns: 1fr !important; gap: 10px !important; }

  /* ── Welcome strip: stack greeting above stats ────────────────────── */
  .summary-strip {
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 16px !important;
    padding: 16px !important;
  }
  .strip-stats {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    overflow: hidden;
  }
  .strip-stat { padding: 12px 14px !important; }
  .strip-stat-val { font-size: 16px !important; }

  /* ── Topbar search: hide on mobile (too narrow) ───────────────────── */
  .topbar > div:first-of-type input[type="text"],
  .topbar > div:first-of-type svg { }
  .topbar-right > div:first-child { display: none !important; }

  /* Inline style grid overrides */
  [style*="grid-template-columns: 300px"],
  [style*="grid-template-columns: 340px"],
  [style*="grid-template-columns: 360px"],
  [style*="grid-template-columns: 380px"],
  [style*="grid-template-columns: 290px"],
  [style*="grid-template-columns: 260px"],
  [style*="grid-template-columns: 1fr 320"],
  [style*="grid-template-columns: 1fr 360"],
  [style*="grid-template-columns: 1fr 380"],
  [style*="grid-template-columns: 1fr 400"],
  [style*="grid-template-columns:1fr 380"],
  [style*="grid-template-columns:repeat(3"],
  [style*="grid-template-columns:repeat(4"],
  [style*="grid-template-columns:repeat(5"],
  [style*="grid-template-columns: repeat(3"],
  [style*="grid-template-columns: repeat(4"],
  [style*="grid-template-columns: repeat(5"] {
    grid-template-columns: 1fr !important;
    gap: 12px !important;
  }
  /* Exception: 2-col repeat for small grids is OK */
  [style*="grid-template-columns:repeat(2"],
  [style*="grid-template-columns: repeat(2"] {
    grid-template-columns: 1fr 1fr !important;
  }

  /* ── Flex rows → wrap or column ───────────────────────────────────── */
  .filter-bar {
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 8px !important;
  }
  .filter-bar > * { width: 100% !important; min-width: unset !important; }
  .srch-wrap { min-width: unset !important; width: 100% !important; flex: unset !important; }

  .tb-right,
  .tb-actions,
  .hdr-actions { flex-wrap: wrap; gap: 6px !important; }

  .day-bar { flex-wrap: wrap !important; gap: 12px !important; }
  .day-bar-div { display: none !important; }

  /* ── All inputs, selects, buttons: full width in forms ────────────── */
  .finput, .fsel, .ftxtarea, .sel {
    width: 100% !important;
    max-width: 100% !important;
  }

  /* ── Cards and sections ───────────────────────────────────────────── */
  .card, .fsec, .tbl-wrap {
    width: 100% !important;
    max-width: 100% !important;
    border-radius: 10px !important;
  }
  .card-body { padding: 14px !important; }
  .fsec      { margin-bottom: 12px !important; }

  /* Stat cards */
  .m-stat { padding: 14px !important; }
  .m-stat-val, .m-stat-lbl { }

  /* ── Tables: scroll inside card ───────────────────────────────────── */
  .tbl-wrap {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
  }
  .dtbl, .dtable {
    min-width: 480px !important;
    width: 100%;
    font-size: 12px !important;
  }
  .dtbl th, .dtable th {
    padding: 10px 10px !important;
    font-size: 9px !important;
    white-space: nowrap;
  }
  .dtbl td, .dtable td {
    padding: 10px 10px !important;
    font-size: 12px !important;
    white-space: nowrap;
  }

  /* ── Sticky panels → static ───────────────────────────────────────── */
  .breakdown,
  .breakdown-panel,
  .day-panel {
    position: static !important;
    top: auto !important;
    width: 100% !important;
    max-width: 100% !important;
  }

  .params-panel {
    position: static !important;
    border-right: none !important;
    border-bottom: 1px solid rgba(255,255,255,.08) !important;
    width: 100% !important;
    max-height: 280px !important;
    overflow-y: auto !important;
  }

  /* ── Calendar page (full big grid) ────────────────────────────────── */
  .week-view { overflow-x: auto !important; }
  /* Mini calendar on dashboard: keep it simple, no forced min-width */
  .mini-cal { width: 100%; }
  .mini-cal .cal-grid { display: grid !important; grid-template-columns: repeat(7, 1fr) !important; gap: 2px !important; overflow-x: visible !important; }
  .mini-cal .cal-day { font-size: 11px !important; }

  /* ── data-table (dashboard recent payments) ────────────────────────── */
  .data-table th, .data-table td { white-space: nowrap; }
  div[style*="overflow-x:auto"] { overflow-x: auto !important; -webkit-overflow-scrolling: touch; }

  /* ── Pagination ───────────────────────────────────────────────────── */
  .pgn { flex-wrap: wrap !important; gap: 8px; padding: 12px !important; }
  .pgn-info { width: 100%; text-align: center; }

  /* ── Modals ───────────────────────────────────────────────────────── */
  .modal-overlay { padding: 12px !important; align-items: flex-start !important; padding-top: 60px !important; }
  .modal { width: 100% !important; max-width: 100% !important; }
  .modal-head { padding: 14px 16px 12px !important; }
  .modal-title { font-size: 14px !important; }
  .modal-subtitle { font-size: 12px !important; }
  .modal-body { padding: 16px !important; }
  .modal-foot {
    padding: 12px 16px !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
  }
  .modal-foot .btn { flex: 1 1 auto !important; text-align: center !important; min-width: 0 !important; }
  .receipt-overlay { padding: 12px !important; }
  .receipt-shell { width: 100% !important; max-width: 100% !important; }

  /* Receipt body smaller on mobile */
  .rcpt-header { padding: 20px !important; }
  .rcpt-body   { padding: 16px !important; }
  .rcpt-footer { padding: 14px !important; }
  .rcpt-amt-val { font-size: 36px !important; }

  /* ── Toast ────────────────────────────────────────────────────────── */
  .toast-msg {
    left: 12px !important;
    right: 12px !important;
    bottom: 16px !important;
    width: auto !important;
    text-align: center;
  }

  /* ── Notification panel ───────────────────────────────────────────── */
  .notif-panel {
    position: fixed !important;
    top: 64px !important;
    left: 12px !important;
    right: 12px !important;
    width: auto !important;
    max-width: calc(100vw - 24px) !important;
    max-height: 70vh !important;
    overflow-y: auto !important;
    z-index: 300 !important;
  }

  /* ── Loan/borrow detail panels ────────────────────────────────────── */
  .loan-hero { padding: 20px !important; }
  .tl-line   { display: none; }

  /* ── Overdue detail schedule ──────────────────────────────────────── */
  .ov-detail-grid { grid-template-columns: 1fr !important; }

  /* ── Register borrower form ───────────────────────────────────────── */
  .steps {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
    flex-wrap: nowrap !important;
    scrollbar-width: none;
    padding-bottom: 4px;
  }
  .steps::-webkit-scrollbar { display: none; }
  .step-lbl { font-size: 11px !important; }

  /* Form body: 2-col → 1-col */
  .fsec-body { grid-template-columns: 1fr !important; }
  .field.span2 { grid-column: span 1 !important; }

  /* Collateral type picker: keep 2-col, they're small cards */
  .coll-types { grid-template-columns: 1fr 1fr !important; }

  /* Inline 1fr 1fr grids (step 4 layout, vehicle/land fields) */
  [style*="grid-template-columns:1fr 1fr"],
  [style*="grid-template-columns: 1fr 1fr"] {
    grid-template-columns: 1fr !important;
  }

  /* Form action buttons: stack */
  .form-actions {
    flex-wrap: wrap !important;
    gap: 8px !important;
  }
  .form-actions .btn-p,
  .form-actions .btn-g {
    flex: 1 1 auto !important;
    text-align: center !important;
    min-width: 0 !important;
  }

  /* Upload zone: comfortable tap target */
  .upload-zone { padding: 16px !important; }

  /* ── View tabs: scroll horizontally ──────────────────────────────────── */
  .view-tabs {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
    flex-wrap: nowrap !important;
    padding-bottom: 2px;
    scrollbar-width: none;
  }
  .view-tabs::-webkit-scrollbar { display: none; }
  .view-tab { white-space: nowrap !important; flex-shrink: 0 !important; }

  /* ── Filters bar: stack on mobile ────────────────────────────────────── */
  .filters-bar {
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 8px !important;
  }
  .filters-bar .search-wrap { min-width: unset !important; flex: unset !important; width: 100% !important; }
  .filters-bar .filter-select { width: 100% !important; }

  /* ── Filter pills: scroll horizontally ───────────────────────────────── */
  .filter-pills {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
    flex-wrap: nowrap !important;
    scrollbar-width: none;
    padding-bottom: 2px;
  }
  .filter-pills::-webkit-scrollbar { display: none; }
  .filter-pill { white-space: nowrap !important; flex-shrink: 0 !important; }

  /* ── Overdue table: horizontal scroll ────────────────────────────────── */
  .table-wrap { overflow-x: auto !important; }
  .overdue-table { min-width: 700px !important; width: 100%; font-size: 12px !important; }
  .overdue-table th { white-space: nowrap !important; font-size: 10px !important; padding: 10px 10px !important; }
  .overdue-table td { white-space: nowrap !important; font-size: 12px !important; padding: 10px 10px !important; }

  /* ── Overdue alert strip ───────────────────────────────────────────── */
  .alert-strip {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 12px !important;
    padding: 16px !important;
  }
  .alert-actions {
    flex-wrap: wrap !important;
    gap: 8px !important;
    width: 100% !important;
  }
  .alert-btn { flex: 1 1 auto !important; text-align: center !important; }
}

/* ══════════════════════════════════════════════════════════════════════════
   480px — Small phones
══════════════════════════════════════════════════════════════════════════ */
@media (max-width: 480px) {

  /* All stats single column */
  .stats-row,
  .ov-stats,
  .sev-row,
  .grid-4 { grid-template-columns: 1fr !important; }

  .content { padding: 10px !important; }
  .m-stat  { padding: 12px !important; }
  .m-stat-val { font-size: 20px !important; }
  .page-title { font-size: 14px !important; }

  /* Tighter tables */
  .dtbl, .dtable { min-width: 420px !important; font-size: 11px !important; }
  .dtbl td, .dtable td { padding: 8px 8px !important; white-space: nowrap; }
  .dtbl th, .dtable th { padding: 8px 8px !important; font-size: 8px !important; }

  /* Buttons */
  .btn-p, .btn-g, .btn-green {
    font-size: 12px !important;
    padding: 7px 10px !important;
  }

  /* Calendar */
  .cal-day-num { font-size: 10px !important; }

  /* Very small modal */
  .modal-overlay { padding: 8px !important; padding-top: 50px !important; }
}
</style>












</head>
<body x-data="overdueApp()" x-init="init()">

<!-- SIDEBAR -->
<div class="layout">
<nav class="sidebar">
  <div class="sidebar-logo">
    <div class="brand">GRACIMOR</div>
    <div class="sub">Loans Management</div>
  </div>
  <div class="sidebar-nav">
    <div class="nav-section-label">Core</div>
    <a class="nav-item" href="/dashboard">
      <span class="icon">⊞</span> Dashboard
    </a>
    <a class="nav-item" href="/borrowers">
      <span class="icon">👥</span> Borrowers
    </a>
    <a class="nav-item" href="/loans">
      <span class="icon">📋</span> Loans
    </a>
    <a class="nav-item" href="/payments">
      <span class="icon">💳</span> Payments
    </a>
    <a class="nav-item" href="/calendar">
      <span class="icon">📅</span> Calendar
    </a>
    <div class="nav-section-label">Collections</div>
    <a class="nav-item active" href="/overdue">
      <span class="icon">⚠️</span> Overdue & Penalties
      <span class="nav-badge" x-show="(stats.overdue?.total_loans ?? 0) > 0" x-text="stats.overdue?.total_loans ?? 0">0</span>
    </a>
    <div class="nav-section-label">Reports</div>
    <a class="nav-item" href="/reports">
      <span class="icon">📊</span> Reports
    </a>
    <a class="nav-item" href="/settings">
      <span class="icon">⚙️</span> Settings
    </a>
  </div>
</nav>

<!-- MAIN -->
<main class="main">
  <!-- TOPBAR -->
  <div class="topbar">
    <div class="topbar-title">Overdue &amp; <span>Penalties</span></div>
    <div style="display:flex; align-items:center; gap:8px;">
      <div class="pulse-dot"></div>
      <span style="font-size:12px; color:var(--slate)">Live — updated 2 min ago</span>
    </div>
    <div class="topbar-sep"></div>

    <div class="view-tabs">
      <button class="view-tab" :class="view==='overview' && 'active'" @click="view='overview'">Overview</button>
      <button class="view-tab" :class="view==='loans' && 'active'" @click="view='loans'">Overdue Loans</button>
      <button class="view-tab" :class="view==='detail' && 'active'" @click="view='detail'">Loan Detail</button>
      <button class="view-tab" :class="view==='collections' && 'active'" @click="view='collections'">Collections</button>
    </div>

    <button class="topbar-btn outline" @click="showBulkWaiveModal=true">🏳 Bulk Waive</button>
    <button class="topbar-btn danger" @click="showApplyPenaltiesModal=true">⚡ Apply Penalties</button>
  </div>

  <!-- CONTENT -->
  <div class="content">

    <!-- ══ OVERVIEW VIEW ══════════════════════════════════════════════ -->
    <template x-if="view==='overview'">
      <div class="fade-up">

        <!-- Alert strip -->
        <div class="alert-strip">
          <div class="alert-icon">🚨</div>
          <div class="alert-text">
            <h3 x-text="allLoans.length + (allLoans.length===1?' loan is':' loans are') + ' overdue — K ' + (stats.overdue?.total_arrears||0).toLocaleString() + ' in total arrears'">— loans are overdue</h3>
            <p x-text="allLoans.filter(l=>l.daysOverdue>=90).length + ' loan' + (allLoans.filter(l=>l.daysOverdue>=90).length===1?'':'s') + ' have exceeded 90 days and are at risk of default. Daily penalties are accruing at K ' + dailyAccrual.toLocaleString() + '/day across the portfolio.'">Loading…</p>
          </div>
          <div class="alert-actions">
            <button class="alert-btn white" @click="view='loans'">View All Overdue</button>
            <button class="alert-btn ghost" @click="showApplyPenaltiesModal=true">Run Penalty Job</button>
          </div>
        </div>

        <!-- Stats grid -->
        <div class="stats-grid stagger">
          <div class="stat-card red">
            <div class="stat-label">Total Overdue Loans</div>
            <div class="stat-value" x-text="stats.overdue?.total_loans ?? '-'">—</div>
            <div class="stat-sub">
              <span style="font-size:11px;color:var(--slate)">loans currently overdue</span>
            </div>
          </div>
          <div class="stat-card orange">
            <div class="stat-label">Total Arrears</div>
            <div class="stat-value" x-text="fmtK(stats.overdue?.total_arrears ?? 0)">—</div>
            <div class="stat-sub" style="font-family:'IBM Plex Mono',monospace; font-size:11px; color:var(--slate)">ZMW (Kwacha)</div>
          </div>
          <div class="stat-card amber">
            <div class="stat-label">Penalties Outstanding</div>
            <div class="stat-value" x-text="fmtK(stats.overdue?.penalties_outstanding ?? 0)">—</div>
            <div class="stat-sub">
              <span style="font-size:11px;color:var(--slate)">across all loans</span>
            </div>
          </div>
          <div class="stat-card teal">
            <div class="stat-label">Collected This Month</div>
            <div class="stat-value" x-text="fmtK(stats.overdue?.month_collections ?? 0)">—</div>
            <div class="stat-sub">
              <span style="font-size:11px;color:var(--slate)">total payments received</span>
            </div>
          </div>
          <div class="stat-card purple">
            <div class="stat-label">Portfolio Value</div>
            <div class="stat-value" x-text="fmtK(stats.portfolio_value ?? 0)">—</div>
            <div class="stat-sub">
              <span style="font-size:11px;color:var(--slate)">total outstanding balance</span>
            </div>
          </div>
        </div>

        <!-- Heatmap + breakdown -->
        <div style="display:grid; grid-template-columns:1fr 380px; gap:20px; margin-bottom:28px;">
          <!-- Heatmap -->
          <div class="heatmap-section">
            <div class="section-header">
              <div class="section-title">Overdue Severity Distribution</div>
              <div class="section-sub" x-text="'All ' + allLoans.length + ' overdue loan' + (allLoans.length===1?'':'s') + ' by days overdue'">All overdue loans by days overdue</div>
            </div>
            <div class="heatmap-bars">
              <template x-for="bar in heatmapBars" :key="bar.label">
                <div class="hm-bar-wrap" @click="applyFilter(bar.filter)">
                  <div style="font-size:11px; font-family:'IBM Plex Mono',monospace; text-align:center;" :style="`color:${bar.color}`" x-text="'K'+bar.arrears.toLocaleString()"></div>
                  <div class="hm-bar"
                    :style="`height:${bar.pct}%; background:${bar.color}; opacity:${bar.active?1:0.5};`"
                    @mouseenter="bar.active=true" @mouseleave="bar.active=false">
                  </div>
                  <div class="hm-bar-count" :style="`color:${bar.color}`" x-text="bar.count + ' loans'"></div>
                  <div class="hm-bar-label" x-text="bar.label"></div>
                </div>
              </template>
            </div>
            <div class="severity-legend">
              <div class="sev-item"><div class="sev-dot" style="background:var(--sev1)"></div><span class="sev-txt">1–7 days — Warning</span></div>
              <div class="sev-item"><div class="sev-dot" style="background:var(--sev2)"></div><span class="sev-txt">8–30 days — Elevated</span></div>
              <div class="sev-item"><div class="sev-dot" style="background:var(--sev3)"></div><span class="sev-txt">31–60 days — High Risk</span></div>
              <div class="sev-item"><div class="sev-dot" style="background:var(--sev4)"></div><span class="sev-txt">61–90 days — Critical</span></div>
              <div class="sev-item"><div class="sev-dot" style="background:var(--sev5)"></div><span class="sev-txt">90+ days — Default Risk</span></div>
            </div>
          </div>

          <!-- Penalty accrual card -->
          <div class="panel">
            <div class="panel-head">
              <div class="panel-title">⚡ Daily Penalty Accrual</div>
            </div>
            <div class="panel-body">
              <div style="text-align:center; margin-bottom:20px;">
                <div style="font-size:11px; color:var(--slate); text-transform:uppercase; letter-spacing:0.1em; margin-bottom:8px;">Accruing per day</div>
                <div style="font-family:'IBM Plex Mono',monospace; font-size:40px; font-weight:700; color:var(--red)" x-text="'K ' + dailyAccrual.toLocaleString()">K —</div>
                <div style="font-size:12px; color:var(--slate); margin-top:4px;" x-text="'across ' + allLoans.length + ' overdue loan' + (allLoans.length===1?'':'s') + ' · 5% per instalment'">Loading…</div>
              </div>

              <div style="border-top:1px solid var(--border); padding-top:16px; display:flex; flex-direction:column; gap:10px;">
                <template x-for="item in penaltyAccrualItems" :key="item.loan">
                  <div style="display:flex; align-items:center; gap:10px;">
                    <div class="avatar-sm" :style="`background:linear-gradient(135deg,${item.c1},${item.c2})`" x-text="item.initials"></div>
                    <div style="flex:1; min-width:0;">
                      <div style="font-size:13px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" x-text="item.name"></div>
                      <div style="font-size:11px; color:var(--slate); font-family:'IBM Plex Mono',monospace;" x-text="item.loan"></div>
                    </div>
                    <div style="font-family:'IBM Plex Mono',monospace; font-size:13px; font-weight:600; color:var(--red);" x-text="'K '+item.daily"></div>
                  </div>
                </template>
              </div>

              <div style="margin-top:16px; padding-top:14px; border-top:1px solid var(--border);">
                <div style="font-size:12px; color:var(--slate); margin-bottom:10px;">Outstanding penalty by loan (K)</div>
                <template x-for="item in penaltyAccrualItems" :key="'bar-'+item.loan">
                  <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                    <div style="font-size:11px; color:var(--slate); font-family:'IBM Plex Mono',monospace; min-width:60px;" x-text="item.loan"></div>
                    <div style="flex:1; background:rgba(255,255,255,0.06); height:6px; border-radius:3px; overflow:hidden;">
                      <div style="height:100%; border-radius:3px; background:linear-gradient(90deg,var(--red),var(--red2));"
                           :style="`width:${item.penPct}%`"></div>
                    </div>
                    <div style="font-size:11px; font-family:'IBM Plex Mono',monospace; color:var(--amber); min-width:60px; text-align:right;" x-text="'K '+item.penalty.toLocaleString()"></div>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- Top overdue snapshot -->
        <div class="panel">
          <div class="table-header">
            <div class="table-title">🔴 Most Critical Accounts</div>
            <div class="table-count">Top 5 by total arrears + penalties</div>
            <div class="table-actions">
              <button class="topbar-btn outline" @click="view='loans'">View All →</button>
            </div>
          </div>
          <table class="overdue-table">
            <thead>
              <tr>
                <th>Borrower / Loan</th>
                <th>Days Overdue</th>
                <th>Instalment Due</th>
                <th>Penalty</th>
                <th>Total Arrears</th>
                <th>Officer</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <template x-for="loan in topOverdue" :key="loan.id">
                <tr @click="selectLoan(loan); view='detail'">
                  <td>
                    <div class="borrower-cell">
                      <div class="avatar-sm" :style="`background:linear-gradient(135deg,${loan.c1},${loan.c2})`" x-text="loan.initials"></div>
                      <div>
                        <div style="font-weight:600;" x-text="loan.name"></div>
                        <div style="font-size:11px; color:var(--slate); font-family:'IBM Plex Mono',monospace;" x-text="loan.loanNum"></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="sev-badge" :class="loan.sevClass" x-text="loan.daysOverdue + ' days'"></span>
                  </td>
                  <td class="money amber-money" x-text="'K '+loan.instalment.toLocaleString()"></td>
                  <td class="money red-money" x-text="'K '+loan.penalty.toLocaleString()"></td>
                  <td class="money red-money" style="font-size:15px;" x-text="'K '+loan.totalArrears.toLocaleString()"></td>
                  <td style="font-size:13px; color:var(--slate);" x-text="loan.officer"></td>
                  <td>
                    <div class="row-actions">
                      <button class="row-btn amber" @click.stop="openWaiveModal(loan)">Waive</button>
                      <button class="row-btn danger" @click.stop="selectLoan(loan); view='detail'">Detail</button>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- ══ LOANS VIEW ═════════════════════════════════════════════════ -->
    <template x-if="view==='loans'">
      <div class="fade-up">
        <!-- Filters -->
        <div class="filters-bar">
          <div class="search-wrap">
            <span class="icon">🔍</span>
            <input type="text" class="search-input" placeholder="Search borrower name, NRC, loan number…"
                   x-model="searchTerm">
          </div>
          <select class="filter-select" x-model="officerFilter">
            <option value="">All Officers</option>
            <option value="Mwala">F. Mwala</option>
            <option value="Banda">C. Banda</option>
            <option value="Tembo">N. Tembo</option>
          </select>
          <select class="filter-select" x-model="sortBy">
            <option value="days">Sort: Days Overdue ↓</option>
            <option value="arrears">Sort: Arrears ↓</option>
            <option value="penalty">Sort: Penalty ↓</option>
          </select>
          <div class="filter-pills">
            <button class="filter-pill all" :class="sevFilter==='all' && 'active-pill'" @click="sevFilter='all'">All <span class="pill-count" x-text="allLoans.length">0</span></button>
            <button class="filter-pill sev1" :class="sevFilter==='1' && 'active-pill'" @click="sevFilter='1'">1–7d <span class="pill-count" x-text="allLoans.filter(l=>l.daysOverdue>=1&&l.daysOverdue<=7).length">0</span></button>
            <button class="filter-pill sev2" :class="sevFilter==='2' && 'active-pill'" @click="sevFilter='2'">8–30d <span class="pill-count" x-text="allLoans.filter(l=>l.daysOverdue>=8&&l.daysOverdue<=30).length">0</span></button>
            <button class="filter-pill sev3" :class="sevFilter==='3' && 'active-pill'" @click="sevFilter='3'">31–60d <span class="pill-count" x-text="allLoans.filter(l=>l.daysOverdue>=31&&l.daysOverdue<=60).length">0</span></button>
            <button class="filter-pill critical" :class="sevFilter==='4' && 'active-pill'" @click="sevFilter='4'">61+d <span class="pill-count" x-text="allLoans.filter(l=>l.daysOverdue>=61).length">0</span></button>
          </div>
        </div>

        <!-- Table -->
        <div class="table-wrap">
          <div class="table-header">
            <div class="table-title">Overdue Loans</div>
            <div class="table-count" x-text="filteredLoans.length + ' loans'"></div>
            <div class="table-actions">
              <button class="topbar-btn outline" @click="showBulkWaiveModal=true">🏳 Bulk Waive Selected</button>
            </div>
          </div>
          <table class="overdue-table">
            <thead>
              <tr>
                <th style="width:40px"><input type="checkbox" style="accent-color:var(--teal)" @change="toggleAll($event)"></th>
                <th>Borrower</th>
                <th>Loan No.</th>
                <th>Product</th>
                <th>Severity</th>
                <th>Instalments Due</th>
                <th>Principal Due</th>
                <th>Penalty</th>
                <th>Total Arrears</th>
                <th>Last Contact</th>
                <th>Officer</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <template x-for="loan in filteredLoans" :key="loan.id">
                <tr :class="selectedLoan && selectedLoan.id===loan.id ? 'selected' : ''" @click="selectLoan(loan)">
                  <td @click.stop>
                    <input type="checkbox" style="accent-color:var(--teal)" x-model="loan.checked">
                  </td>
                  <td>
                    <div class="borrower-cell">
                      <div class="avatar-sm" :style="`background:linear-gradient(135deg,${loan.c1},${loan.c2}); font-size:11px;`" x-text="loan.initials"></div>
                      <div>
                        <div style="font-weight:600; font-size:13.5px;" x-text="loan.name"></div>
                        <div style="font-size:11px; color:var(--slate);" x-text="loan.phone"></div>
                      </div>
                    </div>
                  </td>
                  <td class="mono" style="font-size:12.5px; color:var(--teal2);" x-text="loan.loanNum"></td>
                  <td style="font-size:12.5px; color:var(--slate);" x-text="loan.product"></td>
                  <td>
                    <span class="sev-badge" :class="loan.sevClass" x-text="loan.daysOverdue+'d overdue'"></span>
                  </td>
                  <td style="text-align:center;">
                    <span style="font-family:'IBM Plex Mono',monospace; font-size:14px; font-weight:700;" :style="`color:${loan.daysOverdue > 60 ? 'var(--red)' : 'var(--amber)'}`" x-text="loan.instalmentsDue"></span>
                  </td>
                  <td class="money amber-money" x-text="'K '+loan.instalment.toLocaleString()"></td>
                  <td class="money red-money" x-text="'K '+loan.penalty.toLocaleString()"></td>
                  <td>
                    <div class="money" style="font-size:15px;" :style="`color:${loan.daysOverdue>60 ? 'var(--red)' : 'var(--amber)'}`" x-text="'K '+loan.totalArrears.toLocaleString()"></div>
                    <div class="mini-progress" style="margin-top:4px;">
                      <div class="mini-bar">
                        <div class="mini-fill" :style="`width:${loan.arrearsPct}%; background:${loan.daysOverdue>60?'var(--red)':'var(--amber)'};`"></div>
                      </div>
                      <div style="font-size:10px; color:var(--slate);" x-text="loan.arrearsPct+'% of loan'"></div>
                    </div>
                  </td>
                  <td style="font-size:12px; color:var(--slate);" x-text="loan.lastContact"></td>
                  <td style="font-size:12.5px; color:var(--slate);" x-text="loan.officer"></td>
                  <td @click.stop>
                    <div class="row-actions">
                      <button class="row-btn" @click="selectLoan(loan); view='detail'">Detail</button>
                      <button class="row-btn amber" @click="openWaiveModal(loan)">Waive</button>
                      <button class="row-btn danger" @click="openEscalateModal(loan)">Escalate</button>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- ══ DETAIL VIEW ════════════════════════════════════════════════ -->
    <template x-if="view==='detail'">
      <div class="fade-up">
        <!-- Breadcrumb -->
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:20px; font-size:13px; color:var(--slate);">
          <button @click="view='loans'" style="background:none; border:none; color:var(--slate); cursor:pointer; padding:0; font-size:13px;">← Overdue Loans</button>
          <span>/</span>
          <span style="color:var(--white)" x-text="selectedLoan ? selectedLoan.name : '—'"></span>
          <span x-show="selectedLoan" class="sev-badge" :class="selectedLoan?.sevClass" style="margin-left:6px;" x-text="selectedLoan?.daysOverdue+'d overdue'"></span>
        </div>

        <div class="detail-layout">
          <!-- LEFT: instalment breakdown -->
          <div>
            <!-- Loan summary card -->
            <div class="loan-summary-card">
              <div class="lsc-row">
                <div>
                  <div class="lsc-name" x-text="selectedLoan?.name || '—'"></div>
                  <div style="display:flex; gap:10px; margin-top:4px; align-items:center;">
                    <div class="lsc-num" x-text="selectedLoan?.loanNum || '—'"></div>
                    <span class="sev-badge s4" x-text="(selectedLoan?.daysOverdue || 0)+' days overdue'"></span>
                  </div>
                </div>
                <div style="text-align:right;">
                  <div style="font-size:11px; color:var(--slate); text-transform:uppercase; letter-spacing:0.08em;">Total Due Now</div>
                  <div style="font-family:'IBM Plex Mono',monospace; font-size:32px; font-weight:700; color:var(--red);"
                       x-text="'K '+(selectedLoan?.totalArrears || 0).toLocaleString()"></div>
                </div>
              </div>
              <div class="lsc-metrics">
                <div>
                  <div class="lsc-metric-label">Principal Arrears</div>
                  <div class="lsc-metric-value red" x-text="'K '+(selectedLoan?.instalment || 0).toLocaleString()"></div>
                </div>
                <div>
                  <div class="lsc-metric-label">Penalty Outstanding</div>
                  <div class="lsc-metric-value amber" x-text="'K '+(selectedLoan?.penalty || 0).toLocaleString()"></div>
                </div>
                <div>
                  <div class="lsc-metric-label">Instalments Due</div>
                  <div class="lsc-metric-value white" x-text="(selectedLoan?.instalmentsDue || 0)+' of '+(selectedLoan?.termMonths || 0)+' overdue'"></div>
                </div>
              </div>
            </div>

            <!-- Instalment schedule -->
            <div class="panel">
              <div class="panel-head">
                <div class="panel-title">📋 Instalment Schedule</div>
                <div style="margin-left:auto; font-size:12px; color:var(--slate);" x-text="'Loan: '+(selectedLoan?.loanNum||'LN-20260009')"></div>
              </div>
              <div class="panel-body">
                <div class="instalment-list">
                  <template x-for="row in detailSchedule" :key="row.num">
                    <div class="inst-row" :class="row.status">
                      <div class="inst-num" :class="row.status" x-text="'#'+row.num"></div>
                      <div class="inst-info">
                        <div class="inst-date" x-text="row.dueDate"></div>
                        <div class="inst-amount" x-text="'K '+row.amount.toLocaleString()"></div>
                      </div>
                      <div x-show="row.status==='overdue'" style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
                        <span class="inst-days" x-text="row.daysOver+' days overdue'"></span>
                        <span class="inst-penalty-tag" x-text="'Penalty: K '+row.penalty.toLocaleString()"></span>
                      </div>
                      <div x-show="row.status==='paid'">
                        <span class="inst-paid-tag">✓ Paid <span x-text="row.paidDate"></span></span>
                      </div>
                      <div x-show="row.status==='partial'" style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
                        <span style="font-size:11px; color:var(--amber);">Partial</span>
                        <span style="font-size:11px; color:var(--slate); font-family:'IBM Plex Mono',monospace;" x-text="'K '+row.paid+' of K '+row.amount+' paid'"></span>
                      </div>
                      <div x-show="row.status==='pending'">
                        <span style="font-size:11px; color:var(--slate);">Not yet due</span>
                      </div>
                    </div>
                  </template>
                </div>
              </div>
            </div>

            <!-- Penalty log -->
            <div class="panel" style="margin-top:20px;">
              <div class="panel-head">
                <div class="panel-title">⚡ Penalty History</div>
                <div style="margin-left:auto; display:flex; gap:8px;">
                  <span style="font-size:12px; color:var(--slate);" x-text="(selectedLoan?.penaltyCount||0)+' total · K '+(selectedLoan?.penalty||0).toLocaleString()+' outstanding'">0 total</span>
                </div>
              </div>
              <div class="panel-body">
                <div class="penalty-log">
                  <template x-for="pen in detailPenalties" :key="pen.id">
                    <div class="pen-item" :class="pen.status">
                      <div style="font-size:16px">⚡</div>
                      <div style="flex:1;">
                        <div style="font-size:13px; font-weight:600; color:var(--white);" x-text="'Instalment #'+pen.instalment+' — '+pen.daysOver+' days overdue'"></div>
                        <div style="font-size:11px; color:var(--slate);" x-text="pen.desc"></div>
                      </div>
                      <div class="pen-date" x-text="pen.date"></div>
                      <div class="pen-amount" x-text="'K '+pen.amount.toLocaleString()"></div>
                      <div class="pen-status" :class="pen.status" x-text="pen.status==='waived' ? '✓ Waived' : 'Outstanding'"></div>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT: Actions + Timeline -->
          <div>
            <!-- Action buttons -->
            <div class="panel" style="margin-bottom:20px;">
              <div class="panel-head">
                <div class="panel-title">⚡ Actions</div>
              </div>
              <div class="panel-body">
                <div class="action-stack">
                  <button class="action-btn primary" @click="showRecordModal=true">
                    💳 Record Payment
                  </button>
                  <button class="action-btn waive" @click="openWaiveModal(selectedLoan)">
                    🏳 Waive Penalties
                  </button>
                  <button class="action-btn escalate" @click="openEscalateModal(selectedLoan)">
                    🚨 Escalate to Legal
                  </button>
                  <button class="action-btn ghost-btn" @click="showContactModal=true">
                    📞 Log Contact Attempt
                  </button>
                  <button class="action-btn ghost-btn" @click="showReminderSentToast()">
                    📱 Send SMS Reminder
                  </button>
                </div>

                <!-- Quick waiver breakdown -->
                <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--border);">
                  <div style="font-size:12px; color:var(--slate); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:12px;">If Fully Waived Today</div>
                  <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span style="font-size:13px; color:var(--slate);">Penalties to waive</span>
                    <span class="mono" style="color:var(--amber);" x-text="'K '+(selectedLoan?.penalty||4620).toLocaleString()"></span>
                  </div>
                  <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span style="font-size:13px; color:var(--slate);">Remaining after waiver</span>
                    <span class="mono" style="color:var(--white);" x-text="'K '+(selectedLoan?.instalment||8780).toLocaleString()"></span>
                  </div>
                  <div style="display:flex; justify-content:space-between; padding-top:8px; border-top:1px solid var(--border);">
                    <span style="font-size:13px; font-weight:700;">New Balance</span>
                    <span class="money" style="color:var(--teal2); font-size:16px;" x-text="'K '+(selectedLoan?.instalment||8780).toLocaleString()"></span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contact + activity timeline -->
            <div class="panel">
              <div class="panel-head">
                <div class="panel-title">📋 Activity Log</div>
              </div>
              <div class="panel-body">
                <div class="timeline">
                  <template x-for="event in activityLog" :key="event.id">
                    <div class="tl-item">
                      <div class="tl-dot" :class="event.color" x-text="event.icon"></div>
                      <div class="tl-content">
                        <div class="tl-title" x-text="event.title"></div>
                        <div class="tl-desc" x-text="event.desc"></div>
                        <div class="tl-date" x-text="event.date + ' · ' + event.by"></div>
                      </div>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ══ COLLECTIONS VIEW ═══════════════════════════════════════════ -->
    <template x-if="view==='collections'">
      <div class="fade-up">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
          <div>
            <div style="font-size:18px; font-weight:700; font-family:'Playfair Display',serif;">Collections Workflow</div>
            <div style="font-size:13px; color:var(--slate); margin-top:3px;">Track follow-up calls, field visits, and escalations</div>
          </div>
          <button class="topbar-btn danger" @click="showContactModal=true">+ Log Contact</button>
        </div>

        <!-- Priority queue -->
        <div style="margin-bottom:20px;">
          <div style="font-size:14px; font-weight:700; color:var(--white); margin-bottom:14px;">
            🔴 Priority Queue — 6 Accounts Need Contact
          </div>
          <div class="collection-grid">
            <template x-for="card in collectionQueue" :key="card.id">
              <div class="call-card">
                <div class="call-card-head">
                  <div class="avatar-sm" :style="`background:linear-gradient(135deg,${card.c1},${card.c2})`" x-text="card.initials"></div>
                  <div>
                    <div class="call-card-name" x-text="card.name"></div>
                    <div class="call-card-loan" x-text="card.loan"></div>
                  </div>
                  <div class="call-priority" :class="card.priority" x-text="card.priority==='p1'?'CRITICAL':card.priority==='p2'?'HIGH':'MEDIUM'"></div>
                </div>
                <div class="call-meta">
                  <span>⏰ <strong x-text="card.daysOverdue+'d'"></strong></span>
                  <span>💰 <strong x-text="'K '+card.arrears.toLocaleString()"></strong></span>
                  <span>📞 <span x-text="card.attempts+' attempts'"></span></span>
                  <span style="color:var(--red);" x-show="card.fieldVisit">🏠 Field visit needed</span>
                </div>
                <div class="call-notes" x-show="card.lastNote" x-text="card.lastNote"></div>
                <div class="call-actions">
                  <button class="call-btn green" @click="showContactModal=true">✓ Log Call</button>
                  <button class="call-btn" @click="selectLoan(null); view='detail'">📋 View Loan</button>
                  <button class="call-btn" @click="openEscalateModal(null)">🚨 Escalate</button>
                </div>
              </div>
            </template>
          </div>
        </div>

        <!-- Recent activity -->
        <div class="panel">
          <div class="panel-head">
            <div class="panel-title">📋 Recent Collections Activity</div>
            <div style="margin-left:auto; font-size:12px; color:var(--slate);">Last 7 days</div>
          </div>
          <div class="panel-body">
            <div class="timeline">
              <template x-for="event in collectionsLog" :key="event.id">
                <div class="tl-item">
                  <div class="tl-dot" :class="event.color" x-text="event.icon"></div>
                  <div class="tl-content">
                    <div style="display:flex; align-items:center; gap:10px;">
                      <div class="tl-title" x-text="event.title"></div>
                      <span class="sev-badge" :class="event.sevClass" x-show="event.sevClass" x-text="event.sevLabel"></span>
                    </div>
                    <div class="tl-desc" x-text="event.desc"></div>
                    <div class="tl-date" x-text="event.date + ' · ' + event.by"></div>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </template>

  </div><!-- /content -->
</main>
</div>

<!-- ══ MODALS ══════════════════════════════════════════════════════════ -->

<!-- WAIVE PENALTY MODAL -->
<div class="modal-overlay" x-show="showWaiveModal" @click.self="showWaiveModal=false" style="display:none">
  <div class="modal" @click.stop>
    <div class="modal-head">
      <div class="modal-icon green">🏳</div>
      <div>
        <div class="modal-title">Waive Penalties</div>
        <div class="modal-subtitle" x-text="'Loan: ' + (waiveLoan?.loanNum || '—') + ' · ' + (waiveLoan?.name || '—')"></div>
      </div>
      <button class="modal-close" @click="showWaiveModal=false">✕</button>
    </div>
    <div class="modal-body">
      <div class="warn-box">
        <span>⚠️</span>
        <span>Waiving penalties will permanently remove them from the borrower's outstanding balance. This action is logged for audit purposes.</span>
      </div>

      <div class="confirm-box">
        <div style="font-size:13px; color:var(--slate); text-align:center;">Total penalty to be waived</div>
        <div class="confirm-amount" x-text="'K ' + (waiveLoan?.penalty || 4620).toLocaleString()"></div>
        <div style="font-size:12px; color:var(--slate); text-align:center;" x-text="'Outstanding across ' + (waiveLoan?.penaltyCount || 5) + ' penalty events'"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Waiver Scope</label>
        <select class="form-select" x-model="waiveScope">
          <option value="all">All outstanding penalties on this loan</option>
          <option value="partial">Waive specific instalment penalty only</option>
        </select>
      </div>

      <div class="form-group" x-show="waiveScope==='partial'">
        <label class="form-label">Select Instalment</label>
        <select class="form-select" x-model="waiveScheduleId">
          <option value="">— Select instalment —</option>
          <template x-for="row in waiveLoanScheduleRows" :key="row.id">
            <option :value="row.id" x-text="`Instalment #${row.num} — K ${row.penalty.toLocaleString()} penalty (${row.daysOver} days overdue)`"></option>
          </template>
          <option x-show="waiveLoanScheduleRows.length === 0" disabled>No overdue instalments with penalties</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Waiver Reason <span style="color:var(--red)">*</span></label>
        <select class="form-select" x-model="waiveReason">
          <option value="">— Select reason —</option>
          <option value="hardship">Borrower financial hardship (documented)</option>
          <option value="error">System/data entry error</option>
          <option value="goodwill">Goodwill gesture — long-term customer</option>
          <option value="health">Medical / health emergency</option>
          <option value="death">Death in family</option>
          <option value="management_decision">Management decision</option>
          <option value="other">Other (specify below)</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Additional Notes</label>
        <textarea class="form-textarea" placeholder="Provide supporting details for the waiver…" x-model="waiveNotes"></textarea>
      </div>

      <div class="info-box">
        <span>ℹ️</span>
        <span>The waiver will be recorded in the audit log under your name and cannot be undone.</span>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn ghost" @click="showWaiveModal=false">Cancel</button>
      <button class="btn green" @click="confirmWaive()" :disabled="!waiveReason || (waiveScope==='partial' && !waiveScheduleId)">🏳 Confirm Waiver</button>
    </div>
  </div>
</div>

<!-- ESCALATE MODAL -->
<div class="modal-overlay" x-show="showEscalateModal" @click.self="showEscalateModal=false" style="display:none">
  <div class="modal" @click.stop>
    <div class="modal-head">
      <div class="modal-icon red">🚨</div>
      <div>
        <div class="modal-title">Escalate to Legal / Collections</div>
        <div class="modal-subtitle" x-text="'Loan: ' + (escalateLoan?.loanNum || '—')"></div>
      </div>
      <button class="modal-close" @click="showEscalateModal=false">✕</button>
    </div>
    <div class="modal-body">
      <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:20px;">
        <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:10px; padding:14px; text-align:center;">
          <div style="font-size:10px; color:var(--slate); text-transform:uppercase; margin-bottom:4px;">Days Overdue</div>
          <div style="font-family:'IBM Plex Mono',monospace; font-size:22px; font-weight:700; color:var(--red);" x-text="escalateLoan?.daysOverdue || 0"></div>
        </div>
        <div style="background:rgba(245,166,35,0.08); border:1px solid rgba(245,166,35,0.2); border-radius:10px; padding:14px; text-align:center;">
          <div style="font-size:10px; color:var(--slate); text-transform:uppercase; margin-bottom:4px;">Total Arrears</div>
          <div style="font-family:'IBM Plex Mono',monospace; font-size:22px; font-weight:700; color:var(--amber);" x-text="'K '+(escalateLoan?.totalArrears || 0).toLocaleString()"></div>
        </div>
        <div style="background:rgba(129,140,248,0.08); border:1px solid rgba(129,140,248,0.2); border-radius:10px; padding:14px; text-align:center;">
          <div style="font-size:10px; color:var(--slate); text-transform:uppercase; margin-bottom:4px;">Contact Attempts</div>
          <div style="font-family:'IBM Plex Mono',monospace; font-size:22px; font-weight:700; color:var(--purple);" x-text="escalateLoan?.contactAttempts || 0">0</div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Escalation Type</label>
        <select class="form-select">
          <option>Legal notice — Formal letter of demand</option>
          <option>External debt collector referral</option>
          <option>Collateral repossession notice</option>
          <option>Court proceedings initiation</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Assigned To</label>
        <select class="form-select">
          <option>Legal Team — Internal</option>
          <option>Debt Recovery Ltd — External</option>
          <option>Mr. K. Simwanza — Legal Officer</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Escalation Notes</label>
        <textarea class="form-textarea" placeholder="Summarise collection history and reason for escalation…"></textarea>
      </div>

      <div class="cb-row form-group">
        <input type="checkbox" id="notifyBorrower">
        <label for="notifyBorrower" style="font-size:13.5px; color:var(--slate); cursor:pointer;">Send formal notice SMS to borrower on submission</label>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn ghost" @click="showEscalateModal=false">Cancel</button>
      <button class="btn red" @click="confirmEscalate()">🚨 Escalate Account</button>
    </div>
  </div>
</div>

<!-- LOG CONTACT MODAL -->
<div class="modal-overlay" x-show="showContactModal" @click.self="showContactModal=false" style="display:none">
  <div class="modal" @click.stop>
    <div class="modal-head">
      <div class="modal-icon teal" style="background:rgba(11,143,172,0.12);">📞</div>
      <div>
        <div class="modal-title">Log Contact Attempt</div>
        <div class="modal-subtitle">Record a call, visit, or SMS contact</div>
      </div>
      <button class="modal-close" @click="showContactModal=false">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Loan / Borrower</label>
        <select class="form-select">
          <option>Grace Nkonde — LN-20260009 (113 days overdue)</option>
          <option>Henry Zulu — LN-20260031 (89 days overdue)</option>
          <option>Irene Mumba — LN-20260019 (67 days overdue)</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Contact Method</label>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
          <template x-for="m in contactMethods" :key="m.value">
            <label style="cursor:pointer;">
              <input type="radio" name="cmethod" :value="m.value" x-model="contactMethod" style="display:none;">
              <div style="border:1px solid var(--border2); border-radius:9px; padding:12px; text-align:center; transition:all 0.15s; cursor:pointer;"
                   :style="contactMethod===m.value ? 'border-color:var(--teal); background:rgba(11,143,172,0.08);' : ''"
                   @click="contactMethod=m.value">
                <div style="font-size:20px; margin-bottom:4px;" x-text="m.icon"></div>
                <div style="font-size:12px; font-weight:600;" x-text="m.label"></div>
              </div>
            </label>
          </template>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Outcome</label>
        <select class="form-select">
          <option>Connected — Borrower committed to pay</option>
          <option>Connected — No commitment made</option>
          <option>No answer — Left voicemail</option>
          <option>Number not reachable</option>
          <option>Wrong number</option>
          <option>Field visit — Borrower at home</option>
          <option>Field visit — Not at home</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea class="form-textarea" placeholder="What was discussed? Any commitments made?"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Promise to Pay Date (if any)</label>
        <input type="date" class="form-input">
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn ghost" @click="showContactModal=false">Cancel</button>
      <button class="btn" style="background:linear-gradient(135deg,var(--teal),var(--teal2)); color:white;" @click="confirmContact()">✓ Log Contact</button>
    </div>
  </div>
</div>

<!-- APPLY PENALTIES MODAL -->
<div class="modal-overlay" x-show="showApplyPenaltiesModal" @click.self="showApplyPenaltiesModal=false" style="display:none">
  <div class="modal" @click.stop>
    <div class="modal-head">
      <div class="modal-icon red">⚡</div>
      <div>
        <div class="modal-title">Run Daily Penalty Job</div>
        <div class="modal-subtitle">Apply penalties to all overdue instalments</div>
      </div>
      <button class="modal-close" @click="showApplyPenaltiesModal=false">✕</button>
    </div>
    <div class="modal-body">
      <div class="warn-box">
        <span>⚠️</span>
        <span>This will apply a 5% penalty to all overdue instalments that are past their grace period. The system runs this automatically at midnight — only run manually if the job failed.</span>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:18px;">
        <div style="background:rgba(239,68,68,0.07); border:1px solid rgba(239,68,68,0.2); border-radius:10px; padding:16px; text-align:center;">
          <div style="font-size:11px; color:var(--slate); margin-bottom:6px;">Overdue instalments</div>
          <div style="font-family:'IBM Plex Mono',monospace; font-size:28px; font-weight:700; color:var(--red);">19</div>
        </div>
        <div style="background:rgba(245,166,35,0.07); border:1px solid rgba(245,166,35,0.2); border-radius:10px; padding:16px; text-align:center;">
          <div style="font-size:11px; color:var(--slate); margin-bottom:6px;">Penalties to apply</div>
          <div style="font-family:'IBM Plex Mono',monospace; font-size:28px; font-weight:700; color:var(--amber);">K 1,240</div>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Apply As Of Date</label>
        <input type="date" class="form-input" :value="today">
      </div>
      <div class="cb-row">
        <input type="checkbox" id="updateOverdue" checked>
        <label for="updateOverdue" style="font-size:13.5px; color:var(--slate); cursor:pointer;">First update overdue statuses (recommended)</label>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn ghost" @click="showApplyPenaltiesModal=false">Cancel</button>
      <button class="btn red" @click="confirmApplyPenalties()">⚡ Run Penalty Job</button>
    </div>
  </div>
</div>

<!-- BULK WAIVE MODAL -->
<div class="modal-overlay" x-show="showBulkWaiveModal" @click.self="showBulkWaiveModal=false" style="display:none">
  <div class="modal" @click.stop>
    <div class="modal-head">
      <div class="modal-icon green">🏳</div>
      <div>
        <div class="modal-title">Bulk Penalty Waiver</div>
        <div class="modal-subtitle">Waive penalties across multiple accounts</div>
      </div>
      <button class="modal-close" @click="showBulkWaiveModal=false">✕</button>
    </div>
    <div class="modal-body">
      <div class="info-box">
        <span>ℹ️</span>
        <span>You have <strong x-text="checkedCount"></strong> loans selected. Penalties for all selected loans will be waived.</span>
      </div>
      <div style="font-size:13px; font-weight:700; margin-bottom:10px; color:var(--white);">Selected accounts:</div>
      <div style="display:flex; flex-direction:column; gap:8px; max-height:160px; overflow-y:auto; margin-bottom:18px;">
        <template x-for="loan in allLoans.filter(l=>l.checked)" :key="loan.id">
          <div style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:rgba(255,255,255,0.04); border-radius:8px;">
            <div class="avatar-sm" :style="`background:linear-gradient(135deg,${loan.c1},${loan.c2}); width:28px; height:28px; font-size:10px;`" x-text="loan.initials"></div>
            <span style="flex:1; font-size:13px; font-weight:600;" x-text="loan.name"></span>
            <span class="mono" style="font-size:12px; color:var(--amber);" x-text="'K '+loan.penalty.toLocaleString()"></span>
          </div>
        </template>
        <div x-show="checkedCount===0" style="text-align:center; color:var(--slate); font-size:13px; padding:20px;">No loans selected. Go to Overdue Loans and check the boxes.</div>
      </div>
      <div style="display:flex; justify-content:space-between; padding:14px; background:rgba(245,166,35,0.07); border:1px solid rgba(245,166,35,0.2); border-radius:10px; margin-bottom:18px;">
        <span style="font-weight:700;">Total waiver amount</span>
        <span class="money" style="color:var(--amber); font-size:16px;" x-text="'K '+totalCheckedPenalty.toLocaleString()"></span>
      </div>
      <div class="form-group">
        <label class="form-label">Waiver Reason <span style="color:var(--red)">*</span></label>
        <select class="form-select" x-model="bulkWaiveReason">
          <option value="">— Select reason —</option>
          <option value="hardship">Financial hardship portfolio review</option>
          <option value="error">System error — penalties applied incorrectly</option>
          <option value="management_decision">Management directive</option>
          <option value="goodwill">COVID-19 / disaster relief waiver</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Authorisation Code</label>
        <input type="text" class="form-input" x-model="bulkWaiveAuthCode" placeholder="Enter CONFIRM or 6-digit PIN…">
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn ghost" @click="showBulkWaiveModal=false">Cancel</button>
      <button class="btn green" @click="confirmBulkWaive()" :disabled="!bulkWaiveReason || checkedCount===0 || !bulkWaiveAuthCode">🏳 Confirm Bulk Waiver</button>
    </div>
  </div>
</div>

<!-- TOASTS -->
<div class="toast-stack">
  <template x-for="t in toasts" :key="t.id">
    <div class="toast" :class="t.type">
      <span x-text="t.icon"></span>
      <span x-text="t.msg"></span>
    </div>
  </template>
</div>

<script>
function overdueApp() {
  return {
    view: 'overview',
    searchTerm: '',
    sevFilter: 'all',
    officerFilter: '',
    sortBy: 'days',
    selectedLoan: null,
    waiveLoan: null,
    escalateLoan: null,
    waiveScope: 'all',
    stats: { overdue: { total_loans:'-', total_arrears:0, penalties_outstanding:0, month_collections:0 } },
    waiveReason: '',
    waiveNotes: '',
    waiveScheduleId: null,
    waiveLoanScheduleRows: [],
    bulkWaiveReason: '',
    bulkWaiveAuthCode: '',
    contactMethod: 'call',
    today: new Date().toISOString().slice(0, 10),
    toasts: [],

    showWaiveModal: false,
    showEscalateModal: false,
    showContactModal: false,
    showApplyPenaltiesModal: false,
    showBulkWaiveModal: false,
    showRecordModal: false,

    contactMethods: [
      { value: 'call', icon: '📞', label: 'Phone Call' },
      { value: 'sms', icon: '💬', label: 'SMS' },
      { value: 'visit', icon: '🏠', label: 'Field Visit' },
    ],

    allLoans: [],

    heatmapBars: [],
    dailyAccrual: 0,

    penaltyAccrualItems: [],

    detailSchedule: [],

    detailPenalties: [],

    activityLog: [],

    collectionQueue: [],

    collectionsLog: [],

    // ── Computed ────────────────────────────────────────────────────
    get topOverdue() {
      return [...this.allLoans].sort((a, b) => b.totalArrears - a.totalArrears).slice(0, 5);
    },

    get filteredLoans() {
      let loans = [...this.allLoans];

      if (this.searchTerm) {
        const t = this.searchTerm.toLowerCase();
        loans = loans.filter(l =>
          l.name.toLowerCase().includes(t) ||
          l.loanNum.toLowerCase().includes(t) ||
          l.phone.includes(t)
        );
      }

      if (this.officerFilter) {
        loans = loans.filter(l => l.officer.includes(this.officerFilter));
      }

      if (this.sevFilter !== 'all') {
        const ranges = { '1': [1,7], '2': [8,30], '3': [31,60], '4': [61,999] };
        const [lo, hi] = ranges[this.sevFilter] || [0, 999];
        loans = loans.filter(l => l.daysOverdue >= lo && l.daysOverdue <= hi);
      }

      const sortFns = {
        days:    (a, b) => b.daysOverdue - a.daysOverdue,
        arrears: (a, b) => b.totalArrears - a.totalArrears,
        penalty: (a, b) => b.penalty - a.penalty,
      };
      loans.sort(sortFns[this.sortBy] || sortFns.days);

      return loans;
    },

    get checkedCount() {
      return this.allLoans.filter(l => l.checked).length;
    },

    get totalCheckedPenalty() {
      return this.allLoans.filter(l => l.checked).reduce((s, l) => s + l.penalty, 0);
    },

    // ── Methods ─────────────────────────────────────────────────────
    _ovPalette: [
      ['#dc2626','#ef4444'],['#7c3aed','#8b5cf6'],['#0891b2','#06b6d4'],
      ['#d97706','#f59e0b'],['#059669','#10b981'],['#be185d','#ec4899'],
      ['#0284c7','#0ea5e9'],['#4f46e5','#6366f1'],['#b45309','#f59e0b'],
      ['#166534','#22c55e'],
    ],
    _ovAvatar(name) {
      let h = 0;
      for (let i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) & 0xFFFFFF;
      return this._ovPalette[Math.abs(h) % this._ovPalette.length];
    },
    _ovInitials(name) {
      const p = (name || '?').trim().split(/\s+/);
      return (p[0][0] + (p[1] ? p[1][0] : '')).toUpperCase();
    },
    _ovSevClass(days) {
      if (days >= 90) return 's4';
      if (days >= 31) return 's3';
      if (days >= 8)  return 's2';
      return 's1';
    },
    _mapOverdueLoan(l) {
      const b = l.borrower || {};
      const name = (b.first_name || '') + ' ' + (b.last_name || '');
      const [c1, c2] = this._ovAvatar(name.trim());
      const bal = l.loan_balance || {};
      const days = parseInt(l.days_overdue) || 0;
      const penalty = parseFloat(l.penalties_total) || parseFloat(bal.penalty_outstanding) || 0;
      const totalArrears = parseFloat(bal.total_outstanding) || 0;
      const totalRepay = parseFloat(l.total_repayable) || 1;
      const instalment = parseFloat(l.monthly_instalment) || 0;
      return {
        id: l.id,
        name: name.trim(),
        initials: this._ovInitials(name.trim()),
        c1, c2,
        loanNum: l.loan_number,
        product: l.loan_product?.name || '—',
        phone: b.phone_primary || '—',
        daysOverdue: days,
        sevClass: this._ovSevClass(days),
        instalmentsDue: parseInt(l.overdue_instalments_count) || 0,
        termMonths: parseInt(l.term_months) || 0,
        instalment: instalment,
        // Daily penalty accrual = 5% of monthly instalment / 30 days
        dailyAccrual: Math.round(instalment * 0.05 / 30 * 100) / 100,
        penalty: penalty,
        totalArrears: totalArrears,
        arrearsPct: Math.round((totalArrears / totalRepay) * 100),
        penaltyCount: parseInt(l.outstanding_penalties_count) || 0,
        officer: l.applied_by?.name || '—',
        lastContact: '—',
        contactAttempts: parseInt(l.contact_attempts_count) || 0,
        checked: false,
      };
    },

    _buildHeatmapBars() {
      const buckets = [
        { label: '1–7 days',  filter: '1', color: 'var(--sev1)', lo: 1,  hi: 7    },
        { label: '8–30 days', filter: '2', color: 'var(--sev2)', lo: 8,  hi: 30   },
        { label: '31–60 days',filter: '3', color: 'var(--sev3)', lo: 31, hi: 60   },
        { label: '61–90 days',filter: '4', color: 'var(--sev4)', lo: 61, hi: 90   },
        { label: '90+ days',  filter: '5', color: 'var(--sev5)', lo: 91, hi: 99999},
      ];
      const bars = buckets.map(b => {
        const loans = this.allLoans.filter(l => l.daysOverdue >= b.lo && l.daysOverdue <= b.hi);
        return { label: b.label, filter: b.filter, color: b.color, count: loans.length, arrears: loans.reduce((s, l) => s + l.totalArrears, 0), pct: 0, active: false };
      });
      const maxArrears = Math.max(...bars.map(b => b.arrears), 1);
      bars.forEach(b => { b.pct = Math.round((b.arrears / maxArrears) * 100); });
      this.heatmapBars = bars;
    },

    async init() {
      await Promise.all([this.loadStats(), this.loadOverdueLoans(), this.loadCollectionQueue()]);
    },

    async loadOverdueLoans() {
      const token = localStorage.getItem('lms_token');
      try {
        const res = await fetch('/api/overdue/loans?per_page=100', {
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const data = await res.json();
        this.allLoans = (data.data || []).map(l => this._mapOverdueLoan(l));
        if (this.allLoans.length) this.selectedLoan = this.allLoans[0];
        // Top 4 penalty accrual items from highest penalty
        const maxPen = Math.max(...this.allLoans.map(l => l.penalty), 1);
        this.penaltyAccrualItems = [...this.allLoans]
          .sort((a, b) => b.penalty - a.penalty)
          .slice(0, 4)
          .map(l => ({ name: l.name, initials: l.initials, c1: l.c1, c2: l.c2, loan: l.loanNum, daily: l.dailyAccrual, penalty: l.penalty, penPct: Math.round((l.penalty / maxPen) * 100) }));
        this.dailyAccrual = Math.round(this.allLoans.reduce((s, l) => s + l.dailyAccrual, 0) * 100) / 100;
        this._buildHeatmapBars();
      } catch (e) { console.error('Load overdue error:', e); }
    },

    async loadCollectionQueue() {
      const token = localStorage.getItem('lms_token');
      try {
        const res = await fetch('/api/overdue/collections-queue', {
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const items = await res.json();
        this.collectionQueue = (items || []).slice(0, 20).map(item => {
          const l = item.loan || {};
          const b = l.borrower || {};
          const name = (b.first_name || '') + ' ' + (b.last_name || '');
          const [c1, c2] = this._ovAvatar(name.trim());
          return {
            id: l.id,
            name: name.trim(),
            initials: this._ovInitials(name.trim()),
            c1, c2,
            loan: l.loan_number || '—',
            daysOverdue: parseInt(l.days_overdue) || 0,
            arrears: parseFloat(l.total_outstanding) || 0,
            priority: item.priority || 'p3',
            attempts: item.attempt_count || 0,
            fieldVisit: item.needs_visit || false,
            lastNote: item.last_note || '',
          };
        });
      } catch (e) { console.error('Load collection queue error:', e); }
    },

    async loadStats() {
      const token = localStorage.getItem('lms_token');
      try {
        const res = await fetch('/api/stats', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
        if (res.ok) this.stats = await res.json();
      } catch {}
    },

    fmtK(n) {
      if (!n || n === 0) return 'K 0';
      if (n >= 1000000) return 'K ' + (n/1000000).toFixed(2) + 'M';
      if (n >= 1000) return 'K ' + Number(n).toLocaleString('en-ZM', {minimumFractionDigits:0,maximumFractionDigits:0});
      return 'K ' + Number(n).toFixed(2);
    },

    async selectLoan(loan) {
      this.selectedLoan = loan || this.allLoans[0] || null;
      this.detailSchedule = [];
      this.detailPenalties = [];
      this.activityLog = [];
      this.view = 'detail';
      if (!this.selectedLoan?.id) return;
      const token = localStorage.getItem('lms_token');
      try {
        const res = await fetch('/api/overdue/loans/' + this.selectedLoan.id, {
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const d = await res.json();

        // Merge in computed fields from detail endpoint
        this.selectedLoan = { ...this.selectedLoan, instalmentsDue: d.overdue_instalments_count || this.selectedLoan.instalmentsDue, termMonths: d.term_months || this.selectedLoan.termMonths, daysOverdue: d.days_overdue || this.selectedLoan.daysOverdue };

        // Build schedule rows
        const today = new Date().toISOString().slice(0, 10);
        const _daysOver = due => {
          const diff = new Date(today) - new Date(due);
          return diff > 0 ? Math.floor(diff / 86400000) : 0;
        };
        const _fmt = iso => iso ? new Date(iso).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' }) : '—';

        // Build penalty lookup: schedule_id → total outstanding penalty
        const penBySchedule = {};
        (d.penalties || []).forEach(p => {
          const sid = p.loan_schedule_id;
          if (p.status !== 'waived' && sid) {
            penBySchedule[sid] = (penBySchedule[sid] || 0) + parseFloat(p.penalty_amount || 0);
          }
        });

        this.detailSchedule = (d.loan_schedule || []).map(s => {
          const over = _daysOver(s.due_date);
          const penAmt = penBySchedule[s.id] || 0;
          return {
            num: s.instalment_number,
            dueDate: _fmt(s.due_date),
            amount: parseFloat(s.total_due) || 0,
            status: s.status || 'pending',
            daysOver: over,
            penalty: penAmt,
            paid: parseFloat(s.amount_paid) || 0,
            paidDate: s.paid_at ? _fmt(s.paid_at) : '',
          };
        });

        this.detailPenalties = (d.penalties || []).map(p => {
          const instNum = p.loan_schedule?.instalment_number || '?';
          const instAmt = parseFloat(this.selectedLoan.instalment) || 0;
          return {
            id: p.id,
            instalment: instNum,
            daysOver: _daysOver(p.applied_date),
            date: _fmt(p.applied_date),
            amount: parseFloat(p.penalty_amount) || 0,
            status: p.status || 'outstanding',
            desc: p.notes || ('System penalty — 5% of K ' + instAmt.toLocaleString()),
          };
        });
      } catch (e) { console.error('Loan detail error:', e); }
    },

    applyFilter(f) {
      this.sevFilter = f;
      this.view = 'loans';
    },

    async openWaiveModal(loan) {
      this.waiveLoan = loan || this.selectedLoan;
      this.waiveReason = '';
      this.waiveNotes = '';
      this.waiveScope = 'all';
      this.waiveScheduleId = null;
      this.waiveLoanScheduleRows = [];
      this.showWaiveModal = true;
      if (this.waiveLoan?.id) {
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch('/api/overdue/loans/' + this.waiveLoan.id, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
          });
          if (res.ok) {
            const d = await res.json();
            const penBySchedule = {};
            (d.penalties || []).forEach(p => {
              if (p.status !== 'waived' && p.loan_schedule_id) {
                penBySchedule[p.loan_schedule_id] = (penBySchedule[p.loan_schedule_id] || 0) + parseFloat(p.penalty_amount || 0);
              }
            });
            this.waiveLoanScheduleRows = (d.loan_schedule || [])
              .filter(s => s.status === 'overdue' && penBySchedule[s.id] > 0)
              .map(s => ({
                id: s.id,
                num: s.instalment_number,
                penalty: penBySchedule[s.id] || 0,
                daysOver: s.days_overdue || 0,
              }));
          }
        } catch {}
      }
    },

    openEscalateModal(loan) {
      this.escalateLoan = loan || this.selectedLoan;
      this.showEscalateModal = true;
    },

    async confirmWaive() {
      if (!this.waiveReason) return;
      if (this.waiveScope === 'partial' && !this.waiveScheduleId) return;
      const token = localStorage.getItem('lms_token');
      const body = {
        loan_id: this.waiveLoan.id,
        scope: this.waiveScope === 'partial' ? 'instalment' : 'all',
        reason: this.waiveReason,
        notes: this.waiveNotes || null,
      };
      if (this.waiveScope === 'partial') body.loan_schedule_id = this.waiveScheduleId;
      try {
        const res = await fetch('/api/penalties/waive', {
          method: 'POST',
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify(body),
        });
        const data = await res.json();
        if (!res.ok) {
          this.toast('error', '❌', data.message || 'Failed to waive penalties');
          return;
        }
        this.showWaiveModal = false;
        const amt = data.amount_waived || 0;
        this.toast('success', '🏳', `Penalties waived — K ${Number(amt).toLocaleString()} removed`);
        const loan = this.allLoans.find(l => l.id === this.waiveLoan.id);
        if (loan) {
          loan.penalty = data.remaining || 0;
          if (!loan.penalty) loan.totalArrears = Math.max(0, loan.totalArrears - amt);
        }
        if (this.selectedLoan?.id === this.waiveLoan.id) await this.selectLoan(this.selectedLoan);
      } catch {
        this.toast('error', '❌', 'Network error — could not waive penalties');
      }
    },

    confirmEscalate() {
      this.showEscalateModal = false;
      this.toast('warn', '🚨', `Account escalated to legal — ${this.escalateLoan?.loanNum}`);
    },

    confirmContact() {
      this.showContactModal = false;
      this.toast('success', '📞', 'Contact logged successfully');
    },

    confirmApplyPenalties() {
      this.showApplyPenaltiesModal = false;
      this.toast('info', '⚡', 'Penalty job triggered — penalties will be applied to all overdue instalments.');
    },

    async confirmBulkWaive() {
      if (!this.bulkWaiveReason || this.checkedCount === 0 || !this.bulkWaiveAuthCode) return;
      const token = localStorage.getItem('lms_token');
      const loanIds = this.allLoans.filter(l => l.checked).map(l => l.id);
      try {
        const res = await fetch('/api/penalties/bulk-waive', {
          method: 'POST',
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({ loan_ids: loanIds, reason: this.bulkWaiveReason, notes: null, auth_code: this.bulkWaiveAuthCode }),
        });
        const data = await res.json();
        if (!res.ok) {
          this.toast('error', '❌', data.message || 'Bulk waiver failed');
          return;
        }
        this.showBulkWaiveModal = false;
        this.allLoans.forEach(l => { if (l.checked) { l.penalty = 0; l.checked = false; } });
        this.bulkWaiveAuthCode = '';
        this.toast('success', '🏳', data.message || `Bulk waiver complete — ${data.loans_affected} loans`);
      } catch {
        this.toast('error', '❌', 'Network error — could not complete bulk waiver');
      }
    },

    showReminderSentToast() {
      this.toast('info', '📱', `SMS reminder sent to ${this.selectedLoan?.name || 'borrower'} at ${this.selectedLoan?.phone || '—'}`);
    },

    toggleAll(evt) {
      this.filteredLoans.forEach(l => l.checked = evt.target.checked);
    },

    toast(type, icon, msg) {
      const id = Date.now();
      this.toasts.push({ id, type, icon, msg });
      setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 4000);
    },
  };
}
</script>

    <script>
  /* \xe2\x94\x80\xe2\x94\x80 401 guard: redirect to login on any unauthorized API response \xe2\x94\x80\xe2\x94\x80 */
  (function() {
    var _f = window.fetch;
    window.fetch = function() {
      return _f.apply(this, arguments).then(function(r) {
        if (r.status === 401) { localStorage.clear(); window.location.href = '/login'; }
        return r;
      });
    };
  })();
  </script>

<script>
  /* ── LMS Auth Guard + Nav Links + User Pill ── */
  (function () {
    var token = localStorage.getItem('lms_token');
    if (!token) { window.location.href = '/login'; return; }

    var navRoutes = {
      'Dashboard':  '/dashboard',
      'Borrowers':  '/borrowers',
      'Loans':      '/loans',
      'Payments':   '/payments',
      'Collateral': '/collateral',
      'Calendar':   '/calendar',
      'Overdue':    '/overdue',
      'Reports':    '/reports',
      'Settings':   '/settings',
    };

    document.querySelectorAll('a.nav-item').forEach(function(a) {
      var key = a.textContent.trim().split(/\s+/)[0];
      if (navRoutes[key]) a.setAttribute('href', navRoutes[key]);
    });

    try {
      var user = JSON.parse(localStorage.getItem('lms_user') || '{}');
      if (!user.name) return;
      var parts    = user.name.trim().split(' ');
      var initials = (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
      var short    = parts[0] + (parts[1] ? ' ' + parts[1][0] + '.' : '');
      var role     = (user.role || 'user').charAt(0).toUpperCase() + (user.role || 'user').slice(1);
      var avEl     = document.querySelector('.user-avatar, .u-av');
      var nameEl   = document.querySelector('.user-name, .u-name');
      var roleEl   = document.querySelector('.user-role, .u-role');
      if (avEl)   avEl.textContent   = initials;
      if (nameEl) nameEl.textContent = short;
      if (roleEl) roleEl.textContent = role;
      var pill = document.querySelector('.user-pill');
      if (pill && !document.getElementById('lms-logout-btn')) {
        var btn       = document.createElement('a');
        btn.id        = 'lms-logout-btn';
        btn.href      = '/logout';
        btn.title     = 'Sign out';
        btn.style.cssText = 'margin-left:auto;opacity:.45;display:flex;align-items:center;';
        btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>';
        pill.appendChild(btn);
      }
    } catch (_) {}
  })();
  </script>

  <script id="lms-mobile">
/* ── LMS Mobile Navigation ── */
(function () {
  var overlay = document.createElement('div');
  overlay.id = 'lms-overlay';
  document.body.appendChild(overlay);

  var hamburger = document.createElement('button');
  hamburger.id = 'lms-hamburger';
  hamburger.setAttribute('aria-label', 'Toggle navigation');
  hamburger.innerHTML = [
    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"',
    ' stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">',
    '<line x1="3" y1="6" x2="21" y2="6"/>',
    '<line x1="3" y1="12" x2="21" y2="12"/>',
    '<line x1="3" y1="18" x2="21" y2="18"/>',
    '</svg>',
  ].join('');

  var topbar = document.querySelector('.topbar');
  if (topbar) topbar.insertBefore(hamburger, topbar.firstChild);

  function openSidebar() {
    var s = document.querySelector('.sidebar');
    if (s) s.classList.add('open');
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    var s = document.querySelector('.sidebar');
    if (s) s.classList.remove('open');
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  }

  hamburger.addEventListener('click', function () {
    var s = document.querySelector('.sidebar');
    if (s && s.classList.contains('open')) closeSidebar();
    else openSidebar();
  });

  overlay.addEventListener('click', closeSidebar);

  document.querySelectorAll('.nav-item, .s-item').forEach(function (a) {
    a.addEventListener('click', function () {
      if (window.innerWidth <= 768) closeSidebar();
    });
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth > 768) closeSidebar();
  });
})();
</script>













</body>
</html>
<?php /**PATH C:\Users\HP\Documents\dev\lms\lms\resources\views/pages/overdue.blade.php ENDPATH**/ ?>