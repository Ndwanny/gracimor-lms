<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gracimor LMS — Dashboard</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

  <!-- Chart.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

  <!-- Alpine.js -->
  <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js"></script>

  <style>
    :root {
      --navy:      #0D1B2A;
      --navy-mid:  #112236;
      --navy-card: #16293D;
      --navy-line: #1E3450;
      --teal:      #0B8FAC;
      --teal-lt:   #13AECF;
      --amber:     #F5A623;
      --amber-lt:  #FFBE55;
      --green:     #22C55E;
      --red:       #EF4444;
      --slate:     #94A3B8;
      --slate-lt:  #CBD5E1;
      --white:     #F0F6FF;
      --text:      #E2EAF4;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--navy);
      color: var(--text);
      min-height: 100vh;
      display: flex;
    }

    /* ── Sidebar ── */
    .sidebar {
      width: 240px;
      min-height: 100vh;
      background: var(--navy-mid);
      border-right: 1px solid var(--navy-line);
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0; bottom: 0;
      z-index: 50;
    }

    .sidebar-logo {
      padding: 28px 24px 20px;
      border-bottom: 1px solid var(--navy-line);
    }

    .logo-mark {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      color: var(--white);
      letter-spacing: 0.02em;
    }

    .logo-sub {
      font-size: 10px;
      font-weight: 500;
      color: var(--teal);
      letter-spacing: 0.15em;
      text-transform: uppercase;
      margin-top: 2px;
    }

    .nav-section {
      padding: 20px 12px 8px;
    }

    .nav-label {
      font-size: 9px;
      font-weight: 700;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--slate);
      padding: 0 12px;
      margin-bottom: 6px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 12px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 13.5px;
      font-weight: 500;
      color: var(--slate-lt);
      transition: all 0.15s ease;
      text-decoration: none;
      margin-bottom: 1px;
    }

    .nav-item:hover { background: var(--navy-line); color: var(--white); }
    .nav-item.active {
      background: linear-gradient(135deg, rgba(11,143,172,0.25), rgba(11,143,172,0.1));
      color: var(--teal-lt);
      border: 1px solid rgba(11,143,172,0.3);
    }

    .nav-icon { width: 16px; height: 16px; opacity: 0.9; flex-shrink: 0; }

    .nav-badge {
      margin-left: auto;
      background: var(--red);
      color: #fff;
      font-size: 10px;
      font-weight: 700;
      padding: 1px 6px;
      border-radius: 99px;
    }

    .sidebar-footer {
      margin-top: auto;
      padding: 16px 12px;
      border-top: 1px solid var(--navy-line);
    }

    .user-pill {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 10px;
      border-radius: 8px;
      background: var(--navy-line);
    }

    .user-avatar {
      width: 32px; height: 32px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--teal), var(--amber));
      display: flex; align-items: center; justify-content: center;
      font-size: 12px; font-weight: 700; color: #fff;
      flex-shrink: 0;
    }

    .user-name { font-size: 13px; font-weight: 600; color: var(--white); }
    .user-role { font-size: 10px; color: var(--teal); font-weight: 500; text-transform: uppercase; letter-spacing: 0.08em; }

    /* ── Main ── */
    .main {
      margin-left: 240px;
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* ── Topbar ── */
    .topbar {
      height: 64px;
      background: var(--navy-mid);
      border-bottom: 1px solid var(--navy-line);
      display: flex;
      align-items: center;
      padding: 0 28px;
      gap: 16px;
      position: sticky;
      top: 0;
      z-index: 40;
    }

    .topbar-title {
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      color: var(--white);
    }

    .topbar-date {
      font-size: 12px;
      color: var(--slate);
      background: var(--navy-line);
      padding: 4px 12px;
      border-radius: 20px;
    }

    .topbar-right { margin-left: auto; display: flex; align-items: center; gap: 12px; }

    .btn-primary {
      display: flex; align-items: center; gap: 6px;
      padding: 8px 16px;
      background: var(--teal);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.15s;
    }
    .btn-primary:hover { background: var(--teal-lt); }

    .btn-ghost {
      display: flex; align-items: center; gap: 6px;
      padding: 8px 14px;
      background: transparent;
      color: var(--slate-lt);
      border: 1px solid var(--navy-line);
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.15s;
    }
    .btn-ghost:hover { background: var(--navy-line); color: var(--white); }

    .notif-btn {
      position: relative;
      width: 36px; height: 36px;
      background: var(--navy-line);
      border: none;
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      color: var(--slate-lt);
    }
    .notif-dot {
      position: absolute;
      top: 6px; right: 6px;
      width: 8px; height: 8px;
      background: var(--amber);
      border-radius: 50%;
      border: 2px solid var(--navy-mid);
    }
    .notif-panel {
      position: absolute; top: calc(100% + 10px); right: 0;
      width: 320px; background: var(--navy-card);
      border: 1px solid var(--navy-line); border-radius: 12px;
      box-shadow: 0 12px 40px rgba(0,0,0,.5); z-index: 200; overflow: hidden;
    }
    .notif-panel-hd {
      padding: 14px 16px; border-bottom: 1px solid var(--navy-line);
      display: flex; align-items: center; justify-content: space-between;
      font-weight: 700; font-size: 13px; color: var(--white);
    }
    .notif-item {
      display: flex; gap: 12px; padding: 12px 16px;
      border-bottom: 1px solid rgba(255,255,255,.04); cursor: default;
      transition: background .15s;
    }
    .notif-item:hover { background: rgba(255,255,255,.03); }
    .notif-item:last-child { border-bottom: none; }
    .notif-ico { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
    .notif-body { flex: 1; min-width: 0; }
    .notif-msg { font-size: 12.5px; color: var(--text); line-height: 1.45; }
    .notif-time { font-size: 11px; color: var(--slate); margin-top: 3px; }
    .notif-unread { width: 6px; height: 6px; border-radius: 50%; background: var(--teal); flex-shrink: 0; margin-top: 5px; }

    /* ── Content ── */
    .content { padding: 28px; flex: 1; }

    /* ── Summary Strip ── */
    .summary-strip {
      background: linear-gradient(135deg, var(--teal) 0%, #076E86 60%, #054F62 100%);
      border-radius: 14px;
      padding: 20px 28px;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 0;
      position: relative;
      overflow: hidden;
    }

    .summary-strip::before {
      content: '';
      position: absolute;
      right: -60px; top: -60px;
      width: 220px; height: 220px;
      border-radius: 50%;
      background: rgba(255,255,255,0.04);
    }
    .summary-strip::after {
      content: '';
      position: absolute;
      right: 40px; bottom: -80px;
      width: 160px; height: 160px;
      border-radius: 50%;
      background: rgba(255,255,255,0.04);
    }

    .strip-greeting { flex: 1; }
    .strip-hello { font-size: 13px; color: rgba(255,255,255,0.7); font-weight: 400; }
    .strip-name { font-family: 'Playfair Display', serif; font-size: 26px; color: #fff; margin-top: 2px; }
    .strip-sub { font-size: 12px; color: rgba(255,255,255,0.65); margin-top: 4px; }

    .strip-stats {
      display: flex;
      gap: 1px;
      background: rgba(255,255,255,0.1);
      border-radius: 10px;
      overflow: hidden;
    }

    .strip-stat {
      padding: 14px 28px;
      background: rgba(255,255,255,0.06);
      text-align: center;
      transition: background 0.15s;
    }
    .strip-stat:hover { background: rgba(255,255,255,0.12); }

    .strip-stat-val {
      font-family: 'DM Mono', monospace;
      font-size: 20px;
      font-weight: 500;
      color: #fff;
    }

    .strip-stat-label {
      font-size: 10px;
      color: rgba(255,255,255,0.65);
      text-transform: uppercase;
      letter-spacing: 0.1em;
      margin-top: 3px;
    }

    /* ── Grid ── */
    .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
    .grid-2-1 { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 24px; }
    .grid-1-1 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }

    /* ── Cards ── */
    .card {
      background: var(--navy-card);
      border: 1px solid var(--navy-line);
      border-radius: 12px;
      overflow: hidden;
    }

    .card-header {
      padding: 16px 20px 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid var(--navy-line);
    }

    .card-title {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      color: var(--slate);
    }

    .card-body { padding: 20px; }

    /* ── Stat Cards ── */
    .stat-card {
      background: var(--navy-card);
      border: 1px solid var(--navy-line);
      border-radius: 12px;
      padding: 20px;
      position: relative;
      overflow: hidden;
      cursor: default;
      transition: transform 0.15s, border-color 0.15s;
    }
    .stat-card:hover { transform: translateY(-2px); border-color: rgba(11,143,172,0.4); }

    .stat-card::after {
      content: '';
      position: absolute;
      top: 0; right: 0;
      width: 80px; height: 80px;
      border-radius: 0 12px 0 80px;
    }

    .stat-card.teal::after   { background: rgba(11,143,172,0.08); }
    .stat-card.amber::after  { background: rgba(245,166,35,0.08); }
    .stat-card.green::after  { background: rgba(34,197,94,0.08); }
    .stat-card.red::after    { background: rgba(239,68,68,0.08); }

    .stat-icon {
      width: 40px; height: 40px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 14px;
      font-size: 18px;
    }

    .stat-icon.teal  { background: rgba(11,143,172,0.15); color: var(--teal-lt); }
    .stat-icon.amber { background: rgba(245,166,35,0.15); color: var(--amber); }
    .stat-icon.green { background: rgba(34,197,94,0.15);  color: var(--green); }
    .stat-icon.red   { background: rgba(239,68,68,0.15);  color: var(--red); }

    .stat-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: var(--slate); margin-bottom: 6px; }

    .stat-value {
      font-family: 'DM Mono', monospace;
      font-size: 26px;
      font-weight: 500;
      color: var(--white);
      line-height: 1;
    }

    .stat-change {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      margin-top: 8px;
      font-size: 11px;
      font-weight: 600;
    }
    .stat-change.up   { color: var(--green); }
    .stat-change.down { color: var(--red); }

    .stat-sub { font-size: 11px; color: var(--slate); margin-top: 6px; }

    /* ── Progress bar ── */
    .progress-bar {
      height: 4px;
      background: var(--navy-line);
      border-radius: 99px;
      overflow: hidden;
      margin-top: 12px;
    }
    .progress-fill {
      height: 100%;
      border-radius: 99px;
      transition: width 1s ease;
    }

    /* ── Table ── */
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      color: var(--slate);
      text-align: left;
      padding: 10px 16px;
      border-bottom: 1px solid var(--navy-line);
      background: var(--navy-mid);
    }
    .data-table td {
      padding: 12px 16px;
      font-size: 13px;
      border-bottom: 1px solid rgba(30,52,80,0.5);
      vertical-align: middle;
    }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tr:hover td { background: rgba(255,255,255,0.02); }

    /* ── Badges ── */
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 3px 9px;
      border-radius: 99px;
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }
    .badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }

    .badge-active  { background: rgba(34,197,94,0.12);  color: var(--green); border: 1px solid rgba(34,197,94,0.25); }
    .badge-active::before { background: var(--green); }

    .badge-overdue { background: rgba(239,68,68,0.12);  color: var(--red);   border: 1px solid rgba(239,68,68,0.25); }
    .badge-overdue::before { background: var(--red); }

    .badge-pending { background: rgba(245,166,35,0.12); color: var(--amber); border: 1px solid rgba(245,166,35,0.25); }
    .badge-pending::before { background: var(--amber); }

    .badge-paid    { background: rgba(11,143,172,0.12); color: var(--teal-lt); border: 1px solid rgba(11,143,172,0.25); }
    .badge-paid::before { background: var(--teal-lt); }

    .badge-partial { background: rgba(139,92,246,0.12); color: #A78BFA;       border: 1px solid rgba(139,92,246,0.25); }
    .badge-partial::before { background: #A78BFA; }

    /* ── Due list items ── */
    .due-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 11px 0;
      border-bottom: 1px solid rgba(30,52,80,0.5);
    }
    .due-item:last-child { border-bottom: none; }

    .due-avatar {
      width: 34px; height: 34px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 12px; font-weight: 700; color: #fff;
      flex-shrink: 0;
    }

    .due-name { font-size: 13px; font-weight: 600; color: var(--white); }
    .due-loan { font-size: 11px; color: var(--slate); font-family: 'DM Mono', monospace; }
    .due-amount { margin-left: auto; font-family: 'DM Mono', monospace; font-size: 14px; font-weight: 500; color: var(--white); }
    .due-days { font-size: 10px; color: var(--red); font-weight: 600; margin-left: auto; white-space: nowrap; }

    /* ── Calendar mini ── */
    .mini-cal { font-size: 12px; }
    .mini-cal-header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 12px;
    }
    .mini-cal-month { font-weight: 700; font-size: 14px; color: var(--white); }
    .mini-cal-nav { background: var(--navy-line); border: none; color: var(--slate-lt); width: 26px; height: 26px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; }

    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; text-align: center; }
    .cal-day-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--slate); padding: 4px 0; }

    .cal-day {
      aspect-ratio: 1;
      display: flex; align-items: center; justify-content: center;
      border-radius: 6px;
      font-size: 12px;
      cursor: pointer;
      transition: all 0.1s;
      position: relative;
    }
    .cal-day:hover { background: var(--navy-line); color: var(--white); }
    .cal-day.other-month { color: var(--navy-line); }
    .cal-day.today { background: var(--teal); color: #fff; font-weight: 700; }
    .cal-day.has-due::after {
      content: '';
      position: absolute;
      bottom: 3px;
      width: 4px; height: 4px;
      border-radius: 50%;
      background: var(--amber);
    }
    .cal-day.has-overdue::after { background: var(--red); }
    .cal-day.selected { background: var(--navy-line); outline: 1px solid var(--teal); }

    /* ── Collection progress ── */
    .collection-ring {
      position: relative;
      width: 100px; height: 100px;
      flex-shrink: 0;
    }

    /* ── Activity feed ── */
    .activity-item {
      display: flex;
      gap: 12px;
      padding: 10px 0;
      border-bottom: 1px solid rgba(30,52,80,0.5);
    }
    .activity-item:last-child { border-bottom: none; }

    .activity-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      margin-top: 5px;
      flex-shrink: 0;
    }

    .activity-text { font-size: 12.5px; color: var(--slate-lt); line-height: 1.5; }
    .activity-text strong { color: var(--white); }
    .activity-time { font-size: 10px; color: var(--slate); margin-top: 2px; }

    /* ── Scrollbar ── */
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--navy-line); border-radius: 99px; }

    /* ── Animate in ── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(12px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .animate { animation: fadeUp 0.4s ease both; }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.10s; }
    .delay-3 { animation-delay: 0.15s; }
    .delay-4 { animation-delay: 0.20s; }
    .delay-5 { animation-delay: 0.25s; }

    /* ── Tabs ── */
    .tabs { display: flex; gap: 2px; background: var(--navy-line); padding: 3px; border-radius: 8px; }
    .tab {
      padding: 6px 14px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.15s;
      color: var(--slate);
      border: none;
      background: none;
      font-family: 'DM Sans', sans-serif;
    }
    .tab.active { background: var(--navy-card); color: var(--white); }

    /* ── Alert bar ── */
    .alert-bar {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 16px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 13px;
      border-left: 3px solid;
    }
    .alert-bar.warning { background: rgba(245,166,35,0.1); border-color: var(--amber); color: var(--amber-lt); }
    .alert-bar.danger  { background: rgba(239,68,68,0.1);  border-color: var(--red);   color: #FCA5A5; }

    /* ── Mono text ── */
    .mono { font-family: 'DM Mono', monospace; }

    .text-teal   { color: var(--teal-lt); }
    .text-amber  { color: var(--amber); }
    .text-green  { color: var(--green); }
    .text-red    { color: var(--red); }
    .text-slate  { color: var(--slate); }
    .text-white  { color: var(--white); }
    .text-sm     { font-size: 12px; }
    .text-xs     { font-size: 11px; }
    .fw-600      { font-weight: 600; }
    .fw-700      { font-weight: 700; }

    .flex        { display: flex; }
    .items-center{ align-items: center; }
    .justify-between { justify-content: space-between; }
    .gap-8       { gap: 8px; }
    .gap-12      { gap: 12px; }
    .mt-4        { margin-top: 4px; }
    .mt-8        { margin-top: 8px; }
    .mt-12       { margin-top: 12px; }
    .mt-16       { margin-top: 16px; }
    .mb-12       { margin-bottom: 12px; }
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

<body x-data="dashboard()">

  <!-- ─── SIDEBAR ─── -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-mark">Gracimor</div>
      <div class="logo-sub">Loans Management</div>
    </div>

    <div class="nav-section">
      <div class="nav-label">Main</div>

      <a class="nav-item active" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
          <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        Dashboard
      </a>

      <a class="nav-item" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        Borrowers
      </a>

      <a class="nav-item" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
        </svg>
        Loans
        <span class="nav-badge" x-show="(stats.loans?.pending ?? 0) > 0" x-text="stats.loans?.pending ?? 0">0</span>
      </a>

      <a class="nav-item" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
        Payments
      </a>

      <a class="nav-item" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        Collateral
      </a>
    </div>

    <div class="nav-section">
      <div class="nav-label">Schedule</div>

      <a class="nav-item" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        Calendar
        <span class="nav-badge" style="background:var(--amber);color:#000" x-show="(stats.due_today?.count ?? 0) > 0" x-text="stats.due_today?.count ?? 0">0</span>
      </a>

      <a class="nav-item" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
        Overdue
        <span class="nav-badge" x-show="(stats.overdue?.total_loans ?? 0) > 0" x-text="stats.overdue?.total_loans ?? 0">0</span>
      </a>
    </div>

    <div class="nav-section">
      <div class="nav-label">Reports</div>

      <a class="nav-item" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
          <line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        Reports
      </a>

      <a class="nav-item" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 19.07a10 10 0 0 1 0-14.14"/>
        </svg>
        Settings
      </a>
    </div>

    <div class="sidebar-footer">
      <div class="user-pill">
        <div class="user-avatar">CK</div>
        <div>
          <div class="user-name">Charles K.</div>
          <div class="user-role">CEO</div>
        </div>
        <svg style="margin-left:auto;opacity:.4" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
      </div>
    </div>
  </aside>

  <!-- ─── MAIN ─── -->
  <main class="main">

    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-title">Dashboard</div>
      <div class="topbar-date" x-text="currentDate"></div>

      <div class="topbar-right">
        <!-- Search -->
        <div style="position:relative">
          <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);opacity:.4" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" placeholder="Search borrowers, loans…" style="background:var(--navy-line);border:1px solid rgba(255,255,255,0.06);border-radius:8px;padding:8px 12px 8px 32px;color:var(--slate-lt);font-family:'DM Sans',sans-serif;font-size:12px;width:220px;outline:none;">
        </div>

        <div style="position:relative" @click.away="showNotifs=false">
          <button class="notif-btn" @click="showNotifs=!showNotifs">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <div class="notif-dot" x-show="notifications.some(n=>n.unread)"></div>
          </button>
          <div class="notif-panel" x-show="showNotifs" x-transition style="display:none">
            <div class="notif-panel-hd">
              <span>Notifications</span>
              <button @click="notifications.forEach(n=>n.unread=false);showNotifs=false" style="background:none;border:none;color:var(--teal);font-size:11px;cursor:pointer;font-family:inherit">Mark all read</button>
            </div>
            <template x-for="n in notifications" :key="n.id">
              <div class="notif-item" @click="n.unread=false">
                <div class="notif-ico" :style="`background:${n.bg}`" x-text="n.icon"></div>
                <div class="notif-body">
                  <div class="notif-msg" x-html="n.msg"></div>
                  <div class="notif-time" x-text="n.time"></div>
                </div>
                <div class="notif-unread" x-show="n.unread"></div>
              </div>
            </template>
          </div>
        </div>

        <button class="btn-primary" @click="window.location.href='/loans'">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Loan
        </button>
      </div>
    </header>

    <!-- Content -->
    <div class="content">

      <!-- Alert bar -->
      <div class="alert-bar danger animate" x-show="(stats.overdue?.total_loans ?? 0) > 0">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <strong x-text="(stats.overdue?.total_loans ?? 0) + ' loan' + ((stats.overdue?.total_loans ?? 0) === 1 ? '' : 's') + ' overdue'">— loans overdue</strong>
        &mdash; <span x-text="fmtK(stats.overdue?.total_arrears ?? 0)">K —</span> in outstanding arrears.
        <a href="/overdue" style="color:var(--red);text-decoration:underline;margin-left:6px">View overdue clients →</a>
      </div>

      <div class="alert-bar warning animate delay-1" style="margin-bottom:24px" x-show="(stats.due_today?.count ?? 0) > 0">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <strong x-text="(stats.due_today?.count ?? 0) + ' repayment' + ((stats.due_today?.count ?? 0) === 1 ? '' : 's') + ' due today'">— due today</strong>
        &mdash; <span x-text="fmtK(stats.due_today?.expected ?? 0)">K —</span> expected.
        <a href="/payments" style="color:var(--amber);text-decoration:underline;margin-left:6px">View due today →</a>
      </div>

      <!-- Welcome strip -->
      <div class="summary-strip animate delay-1">
        <div class="strip-greeting">
          <div class="strip-hello" x-text="timeGreeting"></div>
          <div class="strip-name" x-text="greetName"></div>
          <div class="strip-sub" x-text="'Here\'s your portfolio overview for today, ' + currentDate"></div>
        </div>
        <div class="strip-stats">
          <div class="strip-stat">
            <div class="strip-stat-val" x-text="fmtK(stats.portfolio_value)">K —</div>
            <div class="strip-stat-label">Total Portfolio</div>
          </div>
          <div class="strip-stat">
            <div class="strip-stat-val" x-text="stats.loans?.active ?? '-'">—</div>
            <div class="strip-stat-label">Active Loans</div>
          </div>
          <div class="strip-stat">
            <div class="strip-stat-val" x-text="stats.payments?.month_count ? Math.round(stats.payments.month_total/(stats.payments.month_count||1)/1000)+'%' : '—'">—</div>
            <div class="strip-stat-label">Avg Collection/Loan</div>
          </div>
          <div class="strip-stat" style="border-radius:0 10px 10px 0">
            <div class="strip-stat-val" style="color:#FCA5A5" x-text="stats.loans?.overdue ?? '-'">—</div>
            <div class="strip-stat-label">Overdue</div>
          </div>
        </div>
      </div>

      <!-- Stat cards -->
      <div class="grid-4">
        <div class="stat-card teal animate delay-1">
          <div class="stat-icon teal">💰</div>
          <div class="stat-label">Today's Collections</div>
          <div class="stat-value" x-text="fmtK(stats.payments?.today_total ?? 0)">K —</div>
          <div class="stat-change up">Live from database</div>
          <div class="progress-bar mt-12">
            <div class="progress-fill" style="background:var(--teal)" :style="`width:${Math.min(100,Math.round((stats.payments?.today_total||0)/500000*100))}%`"></div>
          </div>
          <div class="stat-sub mt-4" x-text="fmtK(stats.payments?.month_total ?? 0) + ' collected this month'">—</div>
        </div>

        <div class="stat-card amber animate delay-2">
          <div class="stat-icon amber">📅</div>
          <div class="stat-label">Active Loans</div>
          <div class="stat-value" x-text="stats.loans?.active ?? '-'">—</div>
          <div class="stat-change up">Currently running</div>
          <div class="progress-bar mt-12">
            <div class="progress-fill" style="background:var(--amber)" :style="`width:${Math.min(100,Math.round((stats.loans?.active||0)/(stats.loans?.total||1)*100))}%`"></div>
          </div>
          <div class="stat-sub mt-4" x-text="(stats.loans?.total ?? '-') + ' total loans'">—</div>
        </div>

        <div class="stat-card green animate delay-3">
          <div class="stat-icon green">✅</div>
          <div class="stat-label">Total Collected (Month)</div>
          <div class="stat-value" x-text="fmtK(stats.payments?.month_total ?? 0)">K —</div>
          <div class="stat-change up">This calendar month</div>
          <div class="progress-bar mt-12">
            <div class="progress-fill" style="background:var(--green)" :style="`width:${Math.min(100,Math.round((stats.payments?.month_total||0)/(stats.portfolio_value||1)*100))}%`"></div>
          </div>
          <div class="stat-sub mt-4" x-text="(stats.payments?.month_count ?? 0) + ' payments recorded'">—</div>
        </div>

        <div class="stat-card red animate delay-4">
          <div class="stat-icon red">⚠️</div>
          <div class="stat-label">Overdue Balance</div>
          <div class="stat-value" x-text="fmtK(stats.overdue?.total_arrears ?? 0)">K —</div>
          <div class="stat-change down" x-text="(stats.loans?.overdue ?? '-') + ' loans in arrears'">—</div>
          <div class="progress-bar mt-12">
            <div class="progress-fill" style="background:var(--red)" :style="`width:${Math.min(100,Math.round((stats.overdue?.total_arrears||0)/(stats.portfolio_value||1)*100))}%`"></div>
          </div>
          <div class="stat-sub mt-4" x-text="'Penalties: ' + fmtK(stats.overdue?.penalties_outstanding ?? 0)">—</div>
        </div>
      </div>

      <!-- Charts row -->
      <div class="grid-2-1">

        <!-- Collection trend chart -->
        <div class="card animate delay-2">
          <div class="card-header">
            <span class="card-title" x-text="chartPeriod==='week'?'Daily Collections vs Expected':chartPeriod==='month'?'Weekly Collections vs Expected':'Monthly Collections vs Expected'"></span>
            <div class="tabs">
              <button class="tab" :class="chartPeriod==='week'&&'active'" @click="setChartPeriod('week')">Week</button>
              <button class="tab" :class="chartPeriod==='month'&&'active'" @click="setChartPeriod('month')">Month</button>
              <button class="tab" :class="chartPeriod==='quarter'&&'active'" @click="setChartPeriod('quarter')">Quarter</button>
            </div>
          </div>
          <div class="card-body" style="padding-top:16px">
            <canvas id="collectionChart" height="200"></canvas>
          </div>
        </div>

        <!-- Portfolio breakdown -->
        <div class="card animate delay-3">
          <div class="card-header">
            <span class="card-title">Portfolio Breakdown</span>
            <button class="btn-ghost" style="font-size:11px;padding:5px 10px">Export</button>
          </div>
          <div class="card-body">
            <canvas id="portfolioChart" height="180"></canvas>
            <div style="margin-top:16px;display:flex;flex-direction:column;gap:8px">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-8"><span style="width:10px;height:10px;border-radius:2px;background:var(--teal);display:inline-block"></span><span class="text-sm text-slate">Active</span></div>
                <span class="mono text-sm text-white">K 1.89M</span>
              </div>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-8"><span style="width:10px;height:10px;border-radius:2px;background:var(--amber);display:inline-block"></span><span class="text-sm text-slate">Pending Approval</span></div>
                <span class="mono text-sm text-white">K 340K</span>
              </div>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-8"><span style="width:10px;height:10px;border-radius:2px;background:var(--red);display:inline-block"></span><span class="text-sm text-slate">Overdue</span></div>
                <span class="mono text-sm text-white" x-text="fmtK(stats.overdue?.total_arrears ?? 0)">K —</span>
              </div>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-8"><span style="width:10px;height:10px;border-radius:2px;background:#6366F1;display:inline-block"></span><span class="text-sm text-slate">Closed (Month)</span></div>
                <span class="mono text-sm text-white">K 145K</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Due today + Overdue + Mini calendar -->
      <div class="grid-3">

        <!-- Due Today -->
        <div class="card animate delay-2">
          <div class="card-header">
            <span class="card-title">Due Today</span>
            <span class="badge badge-pending" x-text="(stats.due_today?.count ?? 0) + ' client' + ((stats.due_today?.count ?? 0) === 1 ? '' : 's')">— clients</span>
          </div>
          <div class="card-body" style="padding-top:8px;padding-bottom:8px">
            <template x-for="client in dueToday" :key="client.id">
              <div class="due-item">
                <div class="due-avatar" :style="`background: linear-gradient(135deg, ${client.color1}, ${client.color2})`" x-text="client.initials"></div>
                <div>
                  <div class="due-name" x-text="client.name"></div>
                  <div class="due-loan" x-text="client.loan"></div>
                </div>
                <div style="margin-left:auto;text-align:right">
                  <div class="due-amount" x-text="client.amount"></div>
                  <div x-show="client.partial" class="badge badge-partial mt-4">Part-paid</div>
                  <div x-show="!client.partial" class="badge badge-pending mt-4">Unpaid</div>
                </div>
              </div>
            </template>
            <button class="btn-ghost" style="width:100%;margin-top:12px;justify-content:center" @click="window.location.href='/payments'" x-text="'View All ' + (stats.due_today?.count ?? 0) + ' →'">View All →</button>
          </div>
        </div>

        <!-- Overdue Clients -->
        <div class="card animate delay-3">
          <div class="card-header">
            <span class="card-title">Overdue Clients</span>
            <span class="badge badge-overdue" x-text="(stats.overdue?.total_loans ?? 0) + ' client' + ((stats.overdue?.total_loans ?? 0) === 1 ? '' : 's')">— clients</span>
          </div>
          <div class="card-body" style="padding-top:8px;padding-bottom:8px">
            <template x-for="client in overdueClients" :key="client.id">
              <div class="due-item">
                <div class="due-avatar" :style="`background: linear-gradient(135deg, #EF4444, #B91C1C)`" x-text="client.initials"></div>
                <div>
                  <div class="due-name" x-text="client.name"></div>
                  <div class="due-loan mono" x-text="client.loan" style="font-size:10px;color:var(--slate)"></div>
                  <div class="text-xs mt-4" style="color:var(--slate)">Last paid: <span x-text="client.lastPaid" style="color:var(--slate-lt)"></span></div>
                </div>
                <div style="margin-left:auto;text-align:right">
                  <div class="due-amount text-red" x-text="client.amount"></div>
                  <div class="due-days" x-text="client.days + ' days overdue'"></div>
                </div>
              </div>
            </template>

            <div style="margin-top:16px;padding:10px;background:rgba(239,68,68,0.06);border-radius:8px;border:1px solid rgba(239,68,68,0.15)">
              <div class="text-xs text-slate">Total penalties accrued</div>
              <div class="mono fw-700" style="color:var(--red);font-size:18px;margin-top:2px" x-text="fmtK(stats.overdue?.penalties_outstanding ?? 0)">K —</div>
              <div class="text-xs text-slate mt-4">5% penalty applied automatically</div>
            </div>
          </div>
        </div>

        <!-- Mini Calendar -->
        <div class="card animate delay-4">
          <div class="card-header">
            <span class="card-title">Repayment Calendar</span>
          </div>
          <div class="card-body">
            <div class="mini-cal">
              <div class="mini-cal-header">
                <button class="mini-cal-nav" @click="prevMonth">‹</button>
                <div class="mini-cal-month" x-text="calMonthLabel"></div>
                <button class="mini-cal-nav" @click="nextMonth">›</button>
              </div>
              <div class="cal-grid">
                <template x-for="d in ['S','M','T','W','T','F','S']">
                  <div class="cal-day-label" x-text="d"></div>
                </template>
                <template x-for="day in calDays" :key="day.key">
                  <div class="cal-day"
                    :class="{
                      'other-month': !day.currentMonth,
                      'today': day.isToday,
                      'has-due': day.hasDue,
                      'has-overdue': day.hasOverdue,
                      'selected': day.selected
                    }"
                    @click="selectDay(day)"
                    x-text="day.num">
                  </div>
                </template>
              </div>

              <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--navy-line)">
                <div class="flex items-center gap-8 mb-12">
                  <div class="flex items-center gap-8"><span style="width:8px;height:8px;border-radius:50%;background:var(--amber);display:inline-block"></span><span class="text-xs text-slate">Repayments due</span></div>
                  <div class="flex items-center gap-8" style="margin-left:auto"><span style="width:8px;height:8px;border-radius:50%;background:var(--red);display:inline-block"></span><span class="text-xs text-slate">Overdue</span></div>
                </div>

                <!-- Selected day info -->
                <div x-show="selectedDayInfo" style="background:var(--navy-line);border-radius:8px;padding:10px">
                  <div class="text-xs text-slate" x-text="selectedDayInfo?.label"></div>
                  <div class="mono fw-600 text-white mt-4" x-text="selectedDayInfo?.amount"></div>
                  <div class="text-xs text-slate mt-4" x-text="selectedDayInfo?.clients + ' client(s) due'"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity + Pending Approvals -->
      <div class="grid-1-1">

        <!-- Recent Transactions -->
        <div class="card animate delay-2">
          <div class="card-header">
            <span class="card-title">Recent Payments</span>
            <button class="btn-ghost" style="font-size:11px;padding:5px 10px" @click="window.location.href='/overdue'">View All</button>
          </div>
          <div style="overflow-x:auto">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Receipt</th>
                  <th>Borrower</th>
                  <th>Type</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                <template x-for="row in recentPayments" :key="row.receipt">
                  <tr>
                    <td class="mono text-teal" style="font-size:12px" x-text="row.receipt"></td>
                    <td>
                      <div class="fw-600 text-white" x-text="row.name" style="font-size:13px"></div>
                      <div class="text-xs text-slate mono" x-text="row.loan"></div>
                    </td>
                    <td>
                      <span class="badge" :class="row.typeBadge" x-text="row.type"></span>
                    </td>
                    <td class="mono fw-600" x-text="row.amount" style="font-size:13px"></td>
                    <td><span class="badge badge-paid">Recorded</span></td>
                    <td class="text-slate text-xs" x-text="row.time"></td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pending Approvals + Activity -->
        <div style="display:flex;flex-direction:column;gap:16px">

          <!-- Pending approvals -->
          <div class="card animate delay-3">
            <div class="card-header">
              <span class="card-title">Pending Approvals</span>
              <span class="badge badge-pending">4 loans</span>
            </div>
            <div class="card-body" style="padding-top:8px;padding-bottom:8px">
              <template x-for="loan in pendingApprovals" :key="loan.id">
                <div class="due-item">
                  <div>
                    <div class="fw-600 text-white" style="font-size:13px" x-text="loan.name"></div>
                    <div class="text-xs mt-4" style="color:var(--slate)">
                      <span class="mono" x-text="loan.amount"></span>
                      &nbsp;·&nbsp;
                      <span x-text="loan.collateral"></span>
                      &nbsp;·&nbsp;
                      <span x-text="loan.days + ' days ago'"></span>
                    </div>
                  </div>
                  <div style="margin-left:auto;display:flex;gap:6px">
                    <button class="btn-ghost" style="font-size:11px;padding:5px 9px" @click="window.location.href='/loans'">Review</button>
                    <button class="btn-primary" style="font-size:11px;padding:5px 9px" @click="window.location.href='/loans'">Approve</button>
                  </div>
                </div>
              </template>
            </div>
          </div>

          <!-- Activity feed -->
          <div class="card animate delay-4">
            <div class="card-header">
              <span class="card-title">Activity Feed</span>
            </div>
            <div class="card-body" style="padding-top:8px;padding-bottom:8px">
              <template x-for="item in activityFeed" :key="item.id">
                <div class="activity-item">
                  <div class="activity-dot" :style="`background: ${item.color}`"></div>
                  <div>
                    <div class="activity-text" x-html="item.text"></div>
                    <div class="activity-time" x-text="item.time"></div>
                  </div>
                </div>
              </template>
            </div>
          </div>

        </div>
      </div>

    </div><!-- /content -->
  </main><!-- /main -->

  <script>
  let _collChart = null; // module-level to avoid Alpine proxy conflict

  function dashboard() {
    return {
      currentDate: '',
      timeGreeting: 'Good morning,',
      greetName: 'Manager',
      showNotifs: false,
      chartPeriod: 'week',
      stats: { loans:{active:'-',overdue:'-'}, payments:{today_total:0,month_total:0}, overdue:{total_loans:0,total_arrears:0,penalties_outstanding:0}, due_today:{count:0,expected:0}, portfolio_value:0 },
      notifications: [],

      // ─── Due Today ───
      dueToday: [],
      overdueClients: [],
      recentPayments: [],
      pendingApprovals: [],
      activityFeed: [],

      // ─── Calendar ───
      calYear: new Date().getFullYear(),
      calMonth: new Date().getMonth(),
      selectedDay: null,
      selectedDayInfo: null,

      dueDates: [3,7,10,12,14,16,19,21,22,24,26,28],
      overdueDates: [5,8,13],

      get calMonthLabel() {
        return new Date(this.calYear, this.calMonth, 1)
          .toLocaleString('default', { month: 'long', year: 'numeric' });
      },

      get calDays() {
        const days = [];
        const firstDay = new Date(this.calYear, this.calMonth, 1).getDay();
        const daysInMonth = new Date(this.calYear, this.calMonth + 1, 0).getDate();
        const daysInPrev  = new Date(this.calYear, this.calMonth, 0).getDate();
        const today = new Date(); // Feb 21 2026

        // Prev month fill
        for (let i = firstDay - 1; i >= 0; i--) {
          days.push({ key:`p${i}`, num: daysInPrev - i, currentMonth: false, isToday: false, hasDue: false, hasOverdue: false, selected: false });
        }
        // Current month
        for (let d = 1; d <= daysInMonth; d++) {
          const now = new Date(); const isToday = (d === now.getDate() && this.calMonth === now.getMonth() && this.calYear === now.getFullYear());
          days.push({
            key: `c${d}`, num: d, currentMonth: true,
            isToday,
            hasDue: this.dueDates.includes(d),
            hasOverdue: this.overdueDates.includes(d),
            selected: this.selectedDay === d,
            dayNum: d
          });
        }
        // Next month fill
        const remaining = 42 - days.length;
        for (let d = 1; d <= remaining; d++) {
          days.push({ key:`n${d}`, num: d, currentMonth: false, isToday: false, hasDue: false, hasOverdue: false, selected: false });
        }
        return days;
      },

      selectDay(day) {
        if (!day.currentMonth) return;
        this.selectedDay = day.dayNum;
        const counts = { 21:7, 22:3, 24:5, 26:4, 28:2, 7:6, 10:4, 12:5, 14:3 };
        const amounts = { 21:'K 38,200', 22:'K 14,500', 24:'K 22,800', 26:'K 18,400', 28:'K 9,600', 7:'K 28,900', 10:'K 19,200', 12:'K 33,400', 14:'K 11,800' };
        const n = counts[day.dayNum] || 0;
        this.selectedDayInfo = n ? {
          label: `Feb ${day.dayNum}, 2026`,
          amount: amounts[day.dayNum] || 'K 0',
          clients: n
        } : { label: `Feb ${day.dayNum}, 2026`, amount: 'K 0', clients: 0 };
      },

      prevMonth() {
        if (this.calMonth === 0) { this.calMonth = 11; this.calYear--; }
        else this.calMonth--;
        this.selectedDay = null;
        this.selectedDayInfo = null;
      },

      nextMonth() {
        if (this.calMonth === 11) { this.calMonth = 0; this.calYear++; }
        else this.calMonth++;
        this.selectedDay = null;
        this.selectedDayInfo = null;
      },

      // ─── Init ───
      async init() {
        const now = new Date();
        this.currentDate = now.toLocaleDateString('en-GB', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
        const h = now.getHours();
        this.timeGreeting = h < 12 ? 'Good morning,' : h < 17 ? 'Good afternoon,' : 'Good evening,';
        try { const u = JSON.parse(localStorage.getItem('lms_user')||'{}'); if (u.name) this.greetName = u.name; } catch(e) {}
        this.$nextTick(() => { this.initCharts(); });
        await Promise.all([this.loadStats(), this.loadWidgets()]);
      },

      async loadStats() {
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch('/api/stats', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
          if (res.ok) this.stats = await res.json();
        } catch {}
      },

      _dbAvatar(name) {
        const palette = [['#0B8FAC','#076E86'],['#6366F1','#4338CA'],['#10B981','#059669'],['#F59E0B','#D97706'],['#EC4899','#BE185D'],['#34D399','#059669'],['#F472B6','#DB2777'],['#818CF8','#6366F1']];
        let h = 0; for (let i=0;i<name.length;i++) h=(h*31+name.charCodeAt(i))&0xFFFFFF;
        return palette[Math.abs(h)%palette.length];
      },
      _dbInitials(n) { const p=(n||'?').trim().split(/\s+/); return (p[0][0]+(p[1]?p[1][0]:'')).toUpperCase(); },
      _dbFmtK(v) { const n=parseFloat(v)||0; return 'K '+n.toLocaleString('en-ZM',{minimumFractionDigits:0,maximumFractionDigits:2}); },
      _dbFmtDate(d) { if(!d) return '—'; const dt=new Date(d); const now=new Date(); const diff=Math.floor((now-dt)/86400000); if(diff===0) return dt.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'}); if(diff===1) return 'Yesterday'; return dt.toLocaleDateString('en-GB',{day:'2-digit',month:'short'}); },

      async loadWidgets() {
        const token = localStorage.getItem('lms_token');
        const h = { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' };
        try {
          const [payRes, ovRes, pendRes, activeRes] = await Promise.all([
            fetch('/api/payments?per_page=5', { headers: h }),
            fetch('/api/overdue/loans?per_page=5', { headers: h }),
            fetch('/api/loans?status=pending&per_page=5', { headers: h }),
            fetch('/api/loans?status=active&per_page=5', { headers: h }),
          ]);

          if (payRes.ok) {
            const pd = await payRes.json();
            this.recentPayments = (pd.data || []).map(p => {
              const b = p.loan?.borrower || {};
              const name = (b.first_name||'')+ ' '+(b.last_name||'');
              const typeBdg = { instalment:'badge-paid', partial:'badge-partial', early_settlement:'badge-active', penalty:'badge-overdue' };
              return { receipt: p.receipt_number||'', name: name.trim(), loan: p.loan?.loan_number||'', type: (p.payment_type||'').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()), typeBadge: typeBdg[p.payment_type]||'badge-paid', amount: this._dbFmtK(p.amount_received), time: this._dbFmtDate(p.payment_date) };
            });
            this.activityFeed = (pd.data || []).map((p, i) => {
              const b = p.loan?.borrower || {};
              const name = (b.first_name||'')+' '+(b.last_name||'');
              const typeColors = { instalment:'#22C55E', partial:'#F5A623', early_settlement:'#22C55E', penalty:'#EF4444' };
              return { id: p.id, text: `<strong>${name.trim()}</strong> — ${(p.payment_type||'').replace(/_/g,' ')} payment of ${this._dbFmtK(p.amount_received)} (${p.loan?.loan_number||''})`, color: typeColors[p.payment_type]||'#0B8FAC', time: this._dbFmtDate(p.payment_date) };
            });
          }

          if (ovRes.ok) {
            const od = await ovRes.json();
            this.overdueClients = (od.data || []).map(l => {
              const b = l.borrower || {};
              const name = (b.first_name||'')+' '+(b.last_name||'');
              return { id: l.id, name: name.trim(), loan: l.loan_number, amount: this._dbFmtK(l.loan_balance?.total_outstanding), days: parseInt(l.days_overdue)||0, initials: this._dbInitials(name.trim()), lastPaid: '—' };
            });
          }

          if (pendRes.ok) {
            const pend = await pendRes.json();
            this.pendingApprovals = (pend.data || []).map(l => {
              const b = l.borrower || {};
              const name = (b.first_name||'')+' '+(b.last_name||'');
              const daysPending = Math.floor((new Date()-new Date(l.created_at))/86400000);
              return { id: l.id, name: name.trim(), amount: this._dbFmtK(l.principal_amount), collateral: l.collateral_asset?.asset_type ? l.collateral_asset.asset_type.charAt(0).toUpperCase()+l.collateral_asset.asset_type.slice(1) : '—', days: daysPending };
            });
          }
          if (activeRes.ok) {
            const ad = await activeRes.json();
            this.dueToday = (ad.data || []).map(l => {
              const b = l.borrower || {};
              const name = (b.first_name||'')+' '+(b.last_name||'');
              const [color1, color2] = this._dbAvatar(name.trim());
              return { id: l.id, name: name.trim(), loan: l.loan_number, amount: l.monthly_instalment ? this._dbFmtK(l.monthly_instalment) : '—', initials: this._dbInitials(name.trim()), partial: false, color1, color2 };
            });
          }

          // Build notifications from overdue + recent payments
          const notifs = [];
          this.overdueClients.slice(0, 2).forEach((c, i) => {
            notifs.push({ id: 'ov'+i, icon: '⚠️', bg: 'rgba(239,68,68,.15)', msg: `<strong>${c.name}</strong> is ${c.days} days overdue — ${c.amount} outstanding.`, time: 'Today', unread: true });
          });
          this.recentPayments.slice(0, 2).forEach((p, i) => {
            notifs.push({ id: 'py'+i, icon: '💰', bg: 'rgba(11,143,172,.15)', msg: `<strong>${p.amount}</strong> ${p.type} payment from ${p.name} (${p.loan}).`, time: p.time, unread: i === 0 });
          });
          this.notifications = notifs;
        } catch(e) { console.error('Dashboard widget error:', e); }
      },

      fmtK(n) {
        if (!n || n === 0) return 'K 0';
        if (n >= 1000000) return 'K ' + (n / 1000000).toFixed(1) + 'M';
        if (n >= 1000) return 'K ' + (n / 1000).toFixed(0) + 'K';
        return 'K ' + Number(n).toLocaleString();
      },

      _chartData: {
        week:    { labels:['Mon','Tue','Wed','Thu','Fri','Sat'], exp:[42000,38500,51000,29000,45000,38200], col:[39800,36000,48500,31200,41000,14800] },
        month:   { labels:['Wk 1','Wk 2','Wk 3','Wk 4'],       exp:[180000,210000,195000,240000],         col:[168000,198000,184000,218000] },
        quarter: { labels:['Jan','Feb','Mar'],                   exp:[720000,680000,740000],                col:[685000,632000,0] },
      },

      setChartPeriod(period) {
        this.chartPeriod = period;
        if (!_collChart) return;
        const d = this._chartData[period];
        _collChart.data.labels = d.labels;
        _collChart.data.datasets[0].data = d.exp;
        _collChart.data.datasets[1].data = d.col;
        _collChart.update();
      },

      initCharts() {
        // ── Collection Chart ──
        const chartOpts = {
          responsive: true, maintainAspectRatio: true,
          plugins: {
            legend: { labels: { color: '#94A3B8', font: { family: 'DM Sans', size: 11 }, boxWidth: 10, padding: 16 } },
            tooltip: {
              backgroundColor: '#112236', borderColor: '#1E3450', borderWidth: 1,
              titleColor: '#E2EAF4', bodyColor: '#94A3B8',
              callbacks: { label: ctx => ` K ${ctx.raw.toLocaleString()}` }
            }
          },
          scales: {
            x: { grid: { color: 'rgba(30,52,80,0.6)' }, ticks: { color: '#607D8B', font: { size: 11 } } },
            y: { grid: { color: 'rgba(30,52,80,0.6)' }, ticks: { color: '#607D8B', font: { size: 11 }, callback: v => 'K '+(v/1000).toFixed(0)+'K' } }
          }
        };
        const d = this._chartData.week;
        const ctx1 = document.getElementById('collectionChart').getContext('2d');
        _collChart = new Chart(ctx1, {
          type: 'bar',
          data: {
            labels: d.labels,
            datasets: [
              { label:'Expected', data:d.exp, backgroundColor:'rgba(30,52,80,0.8)',   borderRadius:6, borderSkipped:false },
              { label:'Collected',data:d.col, backgroundColor:'rgba(11,143,172,0.85)',borderRadius:6, borderSkipped:false }
            ]
          },
          options: chartOpts
        });

        // ── Portfolio Donut ──
        const ctx2 = document.getElementById('portfolioChart').getContext('2d');
        new Chart(ctx2, {
          type: 'doughnut',
          data: {
            labels: ['Active', 'Pending', 'Overdue', 'Closed'],
            datasets: [{
              data: [1890000, 340000, 24500, 145000],
              backgroundColor: ['#0B8FAC', '#F5A623', '#EF4444', '#6366F1'],
              borderColor: '#16293D',
              borderWidth: 3,
              hoverOffset: 6,
            }]
          },
          options: {
            responsive: true,
            cutout: '70%',
            plugins: {
              legend: { display: false },
              tooltip: {
                backgroundColor: '#112236',
                borderColor: '#1E3450',
                borderWidth: 1,
                titleColor: '#E2EAF4',
                bodyColor: '#94A3B8',
                callbacks: {
                  label: ctx => ` K ${ctx.raw.toLocaleString()}`
                }
              }
            }
          }
        });
      }
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
<?php /**PATH C:\Users\HP\Documents\dev\lms\lms\resources\views/pages/dashboard.blade.php ENDPATH**/ ?>