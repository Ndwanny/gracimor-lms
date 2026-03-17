<!DOCTYPE html>
<html lang="en">
<head>
  <script>if(!localStorage.getItem("lms_token")){window.location.replace("/login");}</script>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gracimor LMS — Calendar</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js"></script>
  <style>
    :root {
      --navy:#0D1B2A; --navy-mid:#112236; --navy-card:#16293D; --navy-line:#1E3450; --navy-hover:#1A304A;
      --teal:#0B8FAC; --teal-lt:#13AECF; --teal-dk:#076E86;
      --amber:#F5A623; --amber-lt:#FFBE55;
      --green:#22C55E; --green-dk:#15803D;
      --red:#EF4444; --red-dk:#B91C1C;
      --purple:#818CF8; --indigo:#6366F1;
      --slate:#94A3B8; --slate-lt:#CBD5E1;
      --white:#F0F6FF; --text:#E2EAF4;
    }
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'DM Sans',sans-serif;background:var(--navy);color:var(--text);min-height:100vh;display:flex;overflow-x:hidden}

    /* ── Sidebar ── */
    .sidebar{width:240px;min-height:100vh;background:var(--navy-mid);border-right:1px solid var(--navy-line);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:50}
    .logo-wrap{padding:28px 24px 20px;border-bottom:1px solid var(--navy-line)}
    .logo-mark{font-family:'Playfair Display',serif;font-size:22px;color:var(--white)}
    .logo-sub{font-size:10px;font-weight:500;color:var(--teal);letter-spacing:.15em;text-transform:uppercase;margin-top:2px}
    .nav-sect{padding:20px 12px 8px}
    .nav-lbl{font-size:9px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--slate);padding:0 12px;margin-bottom:6px}
    .nav-item{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:8px;cursor:pointer;font-size:13.5px;font-weight:500;color:var(--slate-lt);transition:all .15s;text-decoration:none;margin-bottom:1px;border:1px solid transparent}
    .nav-item:hover{background:var(--navy-line);color:var(--white)}
    .nav-item.active{background:linear-gradient(135deg,rgba(11,143,172,.25),rgba(11,143,172,.1));color:var(--teal-lt);border-color:rgba(11,143,172,.3)}
    .nav-ic{width:16px;height:16px;flex-shrink:0;opacity:.85}
    .nav-bdg{margin-left:auto;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px}
    .sidebar-footer{margin-top:auto;padding:16px 12px;border-top:1px solid var(--navy-line)}
    .user-pill{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;background:var(--navy-line)}
    .u-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--teal),var(--amber));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0}
    .u-name{font-size:13px;font-weight:600;color:var(--white)}
    .u-role{font-size:10px;color:var(--teal);font-weight:500;text-transform:uppercase;letter-spacing:.08em}

    /* ── Main layout ── */
    .main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-height:100vh}
    .topbar{height:64px;background:var(--navy-mid);border-bottom:1px solid var(--navy-line);display:flex;align-items:center;padding:0 28px;gap:14px;position:sticky;top:0;z-index:40}
    .page-title{font-family:'Playfair Display',serif;font-size:20px;color:var(--white)}
    .tb-right{margin-left:auto;display:flex;align-items:center;gap:10px}

    /* Buttons */
    .btn-p{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--teal);color:#fff;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;white-space:nowrap}
    .btn-p:hover{background:var(--teal-lt)}
    .btn-g{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:transparent;color:var(--slate-lt);border:1px solid var(--navy-line);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:all .15s;white-space:nowrap}
    .btn-g:hover{background:var(--navy-line);color:var(--white)}
    .btn-g.active{background:var(--navy-line);color:var(--white);border-color:rgba(11,143,172,.4)}
    .btn-sm{font-size:11px !important;padding:5px 10px !important}

    /* Utilities */
    .mono{font-family:'DM Mono',monospace}
    .tc{color:var(--teal-lt)} .ta{color:var(--amber)} .tg{color:var(--green)} .tr{color:var(--red)} .ts{color:var(--slate)} .tw{color:var(--white)} .tp{color:var(--purple)}
    .sm{font-size:12px} .xs{font-size:11px} .f6{font-weight:600} .f7{font-weight:700}
    .flex{display:flex} .aic{align-items:center} .jb{justify-content:space-between} .jc{justify-content:center} .col{flex-direction:column}
    .g6{gap:6px} .g8{gap:8px} .g10{gap:10px} .g12{gap:12px} .g16{gap:16px}
    .mt4{margin-top:4px} .mt8{margin-top:8px} .mt12{margin-top:12px} .mt16{margin-top:16px}
    .mb8{margin-bottom:8px} .mb12{margin-bottom:12px} .mb16{margin-bottom:16px}
    .w100{width:100%}

    /* Badges */
    .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:99px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em}
    .badge::before{content:'';width:5px;height:5px;border-radius:50%;flex-shrink:0}
    .b-due{background:rgba(245,166,35,.12);color:var(--amber);border:1px solid rgba(245,166,35,.25)} .b-due::before{background:var(--amber)}
    .b-overdue{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.25)} .b-overdue::before{background:var(--red)}
    .b-paid{background:rgba(34,197,94,.12);color:var(--green);border:1px solid rgba(34,197,94,.25)} .b-paid::before{background:var(--green)}
    .b-partial{background:rgba(129,140,248,.12);color:var(--purple);border:1px solid rgba(129,140,248,.25)} .b-partial::before{background:var(--purple)}
    .b-upcoming{background:rgba(148,163,184,.1);color:var(--slate);border:1px solid rgba(148,163,184,.2)} .b-upcoming::before{background:var(--slate)}

    /* ── Calendar Layout ── */
    .cal-layout{display:grid;grid-template-columns:1fr 360px;gap:0;height:calc(100vh - 64px);overflow:hidden}

    /* Left: main calendar + week view */
    .cal-main{display:flex;flex-direction:column;overflow:hidden;border-right:1px solid var(--navy-line)}
    .cal-nav{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid var(--navy-line);background:var(--navy-mid);flex-shrink:0}
    .cal-month-title{font-family:'Playfair Display',serif;font-size:22px;color:var(--white)}
    .cal-year{font-size:14px;color:var(--slate);margin-left:8px}
    .cal-nav-btn{width:32px;height:32px;border-radius:8px;background:var(--navy-line);border:none;color:var(--slate-lt);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;transition:all .15s}
    .cal-nav-btn:hover{background:var(--navy-hover);color:var(--white)}
    .view-toggle{display:flex;gap:2px;background:var(--navy-line);padding:3px;border-radius:8px}
    .view-btn{padding:5px 12px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;color:var(--slate);background:none;border:none;font-family:'DM Sans',sans-serif;transition:all .15s}
    .view-btn.active{background:var(--navy-card);color:var(--white);box-shadow:0 1px 4px rgba(0,0,0,.3)}

    /* Day-name header */
    .cal-days-hd{display:grid;grid-template-columns:repeat(7,1fr);border-bottom:1px solid var(--navy-line);flex-shrink:0}
    .cal-day-hd{text-align:center;padding:10px 0;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--slate)}
    .cal-day-hd.weekend{color:rgba(148,163,184,.5)}

    /* Calendar grid */
    .cal-grid{flex:1;overflow-y:auto;display:grid;grid-template-columns:repeat(7,1fr);grid-auto-rows:minmax(90px,1fr);align-content:start}
    .cal-cell{border-right:1px solid rgba(30,52,80,.4);border-bottom:1px solid rgba(30,52,80,.4);padding:8px;cursor:pointer;transition:background .1s;position:relative;min-height:90px}
    .cal-cell:nth-child(7n){border-right:none}
    .cal-cell:hover{background:rgba(26,48,74,.6)}
    .cal-cell.today{background:rgba(11,143,172,.08);border-color:rgba(11,143,172,.3)}
    .cal-cell.selected{background:rgba(11,143,172,.15);border-color:rgba(11,143,172,.5)}
    .cal-cell.other-month{opacity:.35}
    .cal-cell.has-overdue{background:rgba(239,68,68,.04)}
    .cal-day-num{font-size:13px;font-weight:600;color:var(--slate-lt);margin-bottom:4px;display:flex;align-items:center;justify-content:space-between}
    .cal-day-num.today-num{color:var(--teal-lt)}
    .today-dot{width:6px;height:6px;border-radius:50%;background:var(--teal);display:inline-block}

    /* Calendar event pills */
    .cal-events{display:flex;flex-direction:column;gap:2px}
    .cal-event{border-radius:4px;padding:2px 5px;font-size:10px;font-weight:600;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:opacity .1s}
    .cal-event:hover{opacity:.85}
    .ev-due{background:rgba(245,166,35,.2);color:var(--amber-lt);border-left:2px solid var(--amber)}
    .ev-overdue{background:rgba(239,68,68,.2);color:#FCA5A5;border-left:2px solid var(--red)}
    .ev-paid{background:rgba(34,197,94,.15);color:#86EFAC;border-left:2px solid var(--green)}
    .ev-partial{background:rgba(129,140,248,.15);color:#C4B5FD;border-left:2px solid var(--purple)}
    .ev-more{background:var(--navy-line);color:var(--slate);border-radius:4px;padding:2px 5px;font-size:10px;font-weight:600;cursor:pointer}

    /* ── Week / List View ── */
    .week-view{flex:1;overflow-y:auto;padding:0}
    .week-day-group{border-bottom:1px solid var(--navy-line)}
    .week-day-hd{padding:12px 24px;background:var(--navy-mid);display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:10}
    .week-day-big{font-family:'Playfair Display',serif;font-size:28px;color:var(--white);line-height:1}
    .week-day-name{font-size:13px;color:var(--slate)}
    .week-day-today-tag{background:var(--teal);color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;text-transform:uppercase;letter-spacing:.1em}
    .week-day-summary{margin-left:auto;text-align:right}
    .week-items{padding:0 24px 12px}
    .week-item{display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid rgba(30,52,80,.4)}
    .week-item:last-child{border-bottom:none}
    .wi-av{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0}
    .wi-name{font-size:13.5px;font-weight:600;color:var(--white)}
    .wi-sub{font-size:11px;color:var(--slate);font-family:'DM Mono',monospace;margin-top:2px}
    .wi-days-overdue{font-size:11px;color:var(--red);font-weight:700;margin-top:2px}

    /* ── Right panel: Day detail ── */
    .day-panel{display:flex;flex-direction:column;overflow:hidden;background:var(--navy-card)}
    .day-panel-hd{padding:20px 20px 16px;border-bottom:1px solid var(--navy-line);flex-shrink:0}
    .day-panel-date{font-family:'Playfair Display',serif;font-size:24px;color:var(--white);line-height:1.1}
    .day-panel-sub{font-size:12px;color:var(--slate);margin-top:4px}

    /* Summary chips in day panel */
    .day-chips{display:flex;gap:6px;flex-wrap:wrap;margin-top:12px}
    .day-chip{display:flex;align-items:center;gap:5px;padding:4px 10px;border-radius:99px;font-size:11px;font-weight:700}
    .chip-due{background:rgba(245,166,35,.15);color:var(--amber);border:1px solid rgba(245,166,35,.2)}
    .chip-overdue{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.2)}
    .chip-paid{background:rgba(34,197,94,.12);color:var(--green);border:1px solid rgba(34,197,94,.2)}

    .day-scroll{flex:1;overflow-y:auto;padding:0 20px 20px}

    /* Event card in day panel */
    .day-event{background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:10px;padding:14px;margin-top:12px;cursor:pointer;transition:border-color .15s}
    .day-event:hover{border-color:rgba(11,143,172,.4)}
    .day-event.ev-type-overdue{border-left:3px solid var(--red);border-color:rgba(239,68,68,.3)}
    .day-event.ev-type-due{border-left:3px solid var(--amber)}
    .day-event.ev-type-paid{border-left:3px solid var(--green);opacity:.75}
    .day-event.ev-type-partial{border-left:3px solid var(--purple)}
    .de-name{font-size:13.5px;font-weight:600;color:var(--white)}
    .de-loan{font-size:11px;font-family:'DM Mono',monospace;color:var(--teal-lt);margin-top:3px}
    .de-amount{font-family:'DM Mono',monospace;font-size:18px;font-weight:500;color:var(--white);margin-top:8px}
    .de-meta{font-size:11px;color:var(--slate);margin-top:4px}

    /* Empty state */
    .empty-day{text-align:center;padding:40px 20px;color:var(--slate)}
    .empty-day-ic{font-size:40px;margin-bottom:12px}

    /* ── Overdue strip ── */
    .overdue-strip{background:linear-gradient(135deg,rgba(239,68,68,.15),rgba(239,68,68,.06));border-bottom:1px solid rgba(239,68,68,.2);padding:12px 24px;display:flex;align-items:center;gap:16px;flex-shrink:0}
    .overdue-count{font-family:'DM Mono',monospace;font-size:22px;font-weight:500;color:var(--red)}
    .overdue-amt{font-family:'DM Mono',monospace;font-size:14px;color:#FCA5A5;font-weight:600}

    /* ── Month summary bar ── */
    .month-bar{background:var(--navy-mid);border-bottom:1px solid var(--navy-line);padding:12px 24px;display:flex;align-items:center;gap:20px;flex-shrink:0;overflow-x:auto}
    .mb-item{display:flex;align-items:center;gap:8px;white-space:nowrap}
    .mb-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
    .mb-label{font-size:11px;color:var(--slate);font-weight:600;text-transform:uppercase;letter-spacing:.06em}
    .mb-val{font-family:'DM Mono',monospace;font-size:13px;font-weight:500;color:var(--white)}

    /* Animations */
    @keyframes fadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
    .anim{animation:fadeUp .3s ease both}
    .d1{animation-delay:.05s} .d2{animation-delay:.10s}

    /* Toast */
    .toast-msg{position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:999;box-shadow:0 8px 24px rgba(0,0,0,.4);animation:fadeUp .3s ease;background:var(--teal);color:#fff}

    ::-webkit-scrollbar{width:4px;height:4px}
    ::-webkit-scrollbar-track{background:transparent}
    ::-webkit-scrollbar-thumb{background:var(--navy-line);border-radius:99px}

    /* Legend */
    .legend{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
    .lg-item{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--slate)}
    .lg-dot{width:8px;height:8px;border-radius:2px;flex-shrink:0}

    /* Send reminders button glow */
    .btn-remind{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:rgba(245,166,35,.15);color:var(--amber);border:1px solid rgba(245,166,35,.3);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s;white-space:nowrap}
    .btn-remind:hover{background:rgba(245,166,35,.25);border-color:var(--amber)}
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
<body x-data="calApp()">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="logo-wrap"><div class="logo-mark">Gracimor</div><div class="logo-sub">Loans Management</div></div>
    <div class="nav-sect">
      <div class="nav-lbl">Main</div>
      <a class="nav-item" href="/dashboard"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>Dashboard</a>
      <a class="nav-item" href="/borrowers"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>Borrowers</a>
      <a class="nav-item" href="/loans"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Loans</a>
      <a class="nav-item" href="/payments"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>Payments</a>
      <a class="nav-item" href="/collateral"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Collateral</a>
    </div>
    <div class="nav-sect">
      <div class="nav-lbl">Schedule</div>
      <a class="nav-item active" href="/calendar"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Calendar<span class="nav-bdg" style="background:var(--amber);color:#000" x-show="(navStats.due_today?.count ?? 0) > 0" x-text="navStats.due_today?.count ?? 0">0</span></a>
      <a class="nav-item" href="/overdue"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Overdue<span class="nav-bdg" style="background:var(--red);color:#fff" x-show="(navStats.overdue?.total_loans ?? 0) > 0" x-text="navStats.overdue?.total_loans ?? 0">0</span></a>
    </div>
    <div class="nav-sect">
      <div class="nav-lbl">Reports</div>
      <a class="nav-item" href="/reports"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>Reports</a>
      <a class="nav-item" href="/settings"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 19.07a10 10 0 0 1 0-14.14"/></svg>Settings</a>
    </div>
    <div class="sidebar-footer">
      <div class="user-pill"><div class="u-av">CK</div><div><div class="u-name">Charles K.</div><div class="u-role">CEO</div></div></div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <!-- Topbar -->
    <header class="topbar">
      <div class="page-title">Due-Date Calendar</div>
      <div class="tb-right">
        <div class="legend">
          <div class="lg-item"><div class="lg-dot" style="background:var(--red)"></div>Overdue</div>
          <div class="lg-item"><div class="lg-dot" style="background:var(--amber)"></div>Due</div>
          <div class="lg-item"><div class="lg-dot" style="background:var(--green)"></div>Paid</div>
          <div class="lg-item"><div class="lg-dot" style="background:var(--purple)"></div>Partial</div>
        </div>
        <button class="btn-remind" @click="sendReminders()">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 5.55 5.55l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z"/></svg>
          Send Today's Reminders
        </button>
        <button class="btn-g" @click="goToday()">Today</button>
        <div class="view-toggle">
          <button class="view-btn" :class="{active:calView==='month'}" @click="calView='month'">Month</button>
          <button class="view-btn" :class="{active:calView==='week'}" @click="calView='week'">Week</button>
          <button class="view-btn" :class="{active:calView==='list'}" @click="calView='list'">List</button>
        </div>
      </div>
    </header>

    <!-- Calendar layout -->
    <div class="cal-layout">

      <!-- ══════ LEFT: MAIN CALENDAR AREA ══════ -->
      <div class="cal-main">

        <!-- Overdue strip -->
        <div class="overdue-strip" x-show="overdueCount>0">
          <div>⚠️</div>
          <div>
            <div class="flex aic g12">
              <div class="overdue-count" x-text="overdueCount + ' overdue'"></div>
              <div class="overdue-amt" x-text="overdueTotal + ' in arrears'"></div>
            </div>
            <div class="xs" style="color:rgba(252,165,165,.7);margin-top:2px">Penalty accruing at 5% of overdue instalment per occurrence</div>
          </div>
          <div style="margin-left:auto;display:flex;gap:8px">
            <button class="btn-sm" style="background:rgba(239,68,68,.15);color:var(--red);border:1px solid rgba(239,68,68,.3);border-radius:7px;cursor:pointer;padding:5px 12px;font-size:11px;font-weight:700;font-family:'DM Sans',sans-serif" @click="calView='list';filterOverdue=true">View All Overdue</button>
          </div>
        </div>

        <!-- Month navigation -->
        <div class="cal-nav">
          <div class="flex aic g12">
            <button class="cal-nav-btn" @click="prevMonth()">‹</button>
            <button class="cal-nav-btn" @click="nextMonth()">›</button>
            <div>
              <span class="cal-month-title" x-text="monthName"></span>
              <span class="cal-year" x-text="currentYear"></span>
            </div>
          </div>
          <div class="month-bar" style="padding:0;background:transparent;border:none;gap:16px">
            <div class="mb-item"><div class="mb-dot" style="background:var(--red)"></div><span class="mb-label">Overdue</span><span class="mb-val" x-text="monthStats.overdue"></span></div>
            <div class="mb-item"><div class="mb-dot" style="background:var(--amber)"></div><span class="mb-label">Due</span><span class="mb-val" x-text="monthStats.due"></span></div>
            <div class="mb-item"><div class="mb-dot" style="background:var(--green)"></div><span class="mb-label">Paid</span><span class="mb-val" x-text="monthStats.paid"></span></div>
            <div class="mb-item"><div class="mb-dot" style="background:var(--purple)"></div><span class="mb-label">Partial</span><span class="mb-val" x-text="monthStats.partial"></span></div>
            <div class="mb-item" style="margin-left:8px"><span class="mb-label">Expected</span><span class="mb-val ta" x-text="monthStats.expected"></span></div>
          </div>
        </div>

        <!-- MONTH VIEW -->
        <template x-if="calView==='month'">
          <div style="display:flex;flex-direction:column;flex:1;overflow:hidden">
            <div class="cal-days-hd">
              <template x-for="d in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="d">
                <div class="cal-day-hd" :class="{'weekend':d==='Sun'||d==='Sat'}" x-text="d"></div>
              </template>
            </div>
            <div class="cal-grid">
              <template x-for="cell in calCells" :key="cell.key">
                <div class="cal-cell"
                  :class="{
                    'today': cell.isToday,
                    'selected': selectedDate===cell.dateStr,
                    'other-month': !cell.inMonth,
                    'has-overdue': cell.events.some(e=>e.status==='overdue')
                  }"
                  @click="selectDay(cell)">
                  <div class="cal-day-num" :class="{'today-num':cell.isToday}">
                    <span x-text="cell.day"></span>
                    <span x-show="cell.isToday" class="today-dot"></span>
                  </div>
                  <div class="cal-events">
                    <template x-for="(ev,ei) in cell.events.slice(0,3)" :key="ei">
                      <div class="cal-event" :class="`ev-${ev.status}`"
                        @click.stop="selectDay(cell);selectedEvent=ev"
                        x-text="ev.name.split(' ')[0] + ' · K' + ev.amount">
                      </div>
                    </template>
                    <div x-show="cell.events.length>3" class="ev-more" @click.stop="selectDay(cell)" x-text="'+'+(cell.events.length-3)+' more'"></div>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </template>

        <!-- WEEK VIEW -->
        <template x-if="calView==='week'">
          <div class="week-view">
            <template x-for="wday in weekDays" :key="wday.dateStr">
              <div class="week-day-group" x-show="wday.events.length>0 || wday.isToday">
                <div class="week-day-hd">
                  <div class="week-day-big" x-text="wday.dayNum"></div>
                  <div>
                    <div class="week-day-name" x-text="wday.dayName + ', ' + wday.monthName"></div>
                    <div x-show="wday.isToday" class="week-day-today-tag mt4">Today</div>
                  </div>
                  <div class="week-day-summary" x-show="wday.events.length>0">
                    <div class="mono f6 ta" style="font-size:16px" x-text="'K ' + wday.totalAmt.toLocaleString()"></div>
                    <div class="xs ts mt4" x-text="wday.events.length + ' instalment' + (wday.events.length!==1?'s':'')"></div>
                  </div>
                </div>
                <div class="week-items">
                  <template x-for="ev in wday.events" :key="ev.id">
                    <div class="week-item" @click="selectedDate=wday.dateStr;selectedEvent=ev">
                      <div class="wi-av" :style="`background:linear-gradient(135deg,${ev.c1},${ev.c2})`" x-text="ev.ini"></div>
                      <div style="flex:1">
                        <div class="wi-name" x-text="ev.name"></div>
                        <div class="wi-sub" x-text="ev.loan + ' · Inst. #' + ev.inst"></div>
                        <div x-show="ev.status==='overdue'" class="wi-days-overdue" x-text="'⚠ ' + ev.daysOverdue + ' days overdue · Penalty: K ' + ev.penalty"></div>
                      </div>
                      <div style="text-align:right">
                        <div class="mono f6" :class="ev.status==='overdue'?'tr':'ta'" style="font-size:16px" x-text="'K ' + ev.amount.toLocaleString()"></div>
                        <span class="badge xs mt4" :class="`b-${ev.status}`" x-text="ev.status"></span>
                      </div>
                      <button class="btn-p btn-sm" style="margin-left:10px" @click.stop="window.location.href='/payments'">Record</button>
                    </div>
                  </template>
                </div>
              </div>
            </template>
            <div x-show="weekDays.every(d=>d.events.length===0)" class="empty-day" style="padding:60px">
              <div class="empty-day-ic">📅</div>
              <div class="f6 tw">No instalments this week</div>
              <div class="xs ts mt8">Navigate to another week using the month view</div>
            </div>
          </div>
        </template>

        <!-- LIST VIEW -->
        <template x-if="calView==='list'">
          <div style="flex:1;overflow-y:auto">
            <!-- Filter row -->
            <div style="padding:14px 24px;border-bottom:1px solid var(--navy-line);display:flex;align-items:center;gap:10px;background:var(--navy-mid);position:sticky;top:0;z-index:10">
              <button class="btn-g btn-sm" :class="{active:listFilter===''}" @click="listFilter='';filterOverdue=false">All</button>
              <button class="btn-g btn-sm" :class="{active:listFilter==='overdue'||filterOverdue}" @click="listFilter='overdue';filterOverdue=true" style="border-color:rgba(239,68,68,.3);color:var(--red)">⚠ Overdue (3)</button>
              <button class="btn-g btn-sm" :class="{active:listFilter==='due'}" @click="listFilter='due';filterOverdue=false" style="border-color:rgba(245,166,35,.3);color:var(--amber)">Due Soon (7)</button>
              <button class="btn-g btn-sm" :class="{active:listFilter==='paid'}" @click="listFilter='paid';filterOverdue=false" style="border-color:rgba(34,197,94,.25);color:var(--green)">Paid (45)</button>
              <button class="btn-g btn-sm" :class="{active:listFilter==='partial'}" @click="listFilter='partial';filterOverdue=false">Partial (3)</button>
              <div style="margin-left:auto;font-size:12px;color:var(--slate)" x-text="filteredListEvents.length + ' records'"></div>
            </div>

            <template x-for="ev in filteredListEvents" :key="ev.id">
              <div style="display:flex;align-items:center;gap:16px;padding:14px 24px;border-bottom:1px solid rgba(30,52,80,.4);cursor:pointer;transition:background .1s"
                @mouseover="$el.style.background='var(--navy-hover)'" @mouseleave="$el.style.background=''"
                @click="selectedDate=ev.dateStr;selectedEvent=ev">

                <!-- Status color bar -->
                <div style="width:3px;height:44px;border-radius:99px;flex-shrink:0"
                  :style="`background:${ev.status==='overdue'?'var(--red)':ev.status==='due'?'var(--amber)':ev.status==='paid'?'var(--green)':'var(--purple)'}`"></div>

                <!-- Date block -->
                <div style="width:52px;text-align:center;flex-shrink:0">
                  <div class="mono f7" :class="ev.status==='overdue'?'tr':'ta'" style="font-size:18px" x-text="ev.dayNum"></div>
                  <div class="xs ts" x-text="ev.monthShort"></div>
                </div>

                <!-- Borrower -->
                <div class="wi-av" :style="`background:linear-gradient(135deg,${ev.c1},${ev.c2})`" x-text="ev.ini" style="width:36px;height:36px;font-size:12px;flex-shrink:0"></div>
                <div style="flex:1;min-width:0">
                  <div class="f6 tw" style="font-size:13.5px" x-text="ev.name"></div>
                  <div class="xs ts mono mt4" x-text="ev.loan + ' · Inst. #' + ev.inst + ' of ' + ev.totalInst"></div>
                  <div x-show="ev.status==='overdue'" class="xs tr mt4 f6" x-text="'⚠ ' + ev.daysOverdue + ' days overdue · Penalty applied: K ' + ev.penalty"></div>
                  <div x-show="ev.status==='partial'" class="xs tp mt4" x-text="'Partial — K ' + ev.paidSoFar + ' paid of K ' + ev.amount.toLocaleString()"></div>
                </div>

                <!-- Amount -->
                <div style="text-align:right;flex-shrink:0">
                  <div class="mono f6" style="font-size:16px"
                    :class="ev.status==='overdue'?'tr':ev.status==='paid'?'tg':'ta'"
                    x-text="'K ' + ev.amount.toLocaleString()"></div>
                  <span class="badge xs mt4" :class="`b-${ev.status}`" x-text="ev.status"></span>
                </div>

                <!-- Action -->
                <button class="btn-p btn-sm" x-show="ev.status!=='paid'" @click.stop="window.location.href='/payments'">Record</button>
                <button class="btn-g btn-sm" x-show="ev.status==='paid'" @click.stop="selectedEvent=ev;toast=true;toastMsg='Opening receipt for '+ev.name+'…';setTimeout(()=>toast=false,2500)">🧾 Receipt</button>
              </div>
            </template>
          </div>
        </template>

      </div>
      <!-- /cal-main -->

      <!-- ══════ RIGHT: DAY DETAIL PANEL ══════ -->
      <div class="day-panel">
        <div class="day-panel-hd">
          <template x-if="selectedDayData">
            <div>
              <div class="day-panel-date" x-text="selectedDayData.fullDate"></div>
              <div class="day-panel-sub" x-text="selectedDayData.events.length + ' instalment' + (selectedDayData.events.length!==1?'s':'')+' · K '+(selectedDayData.totalAmt||0).toLocaleString()+' expected'"></div>
              <div class="day-chips" x-show="selectedDayData.events.length>0">
                <div class="day-chip chip-due" x-show="selectedDayData.due>0">⏰ <span x-text="selectedDayData.due"></span> Due</div>
                <div class="day-chip chip-overdue" x-show="selectedDayData.overdue>0">⚠ <span x-text="selectedDayData.overdue"></span> Overdue</div>
                <div class="day-chip chip-paid" x-show="selectedDayData.paid>0">✓ <span x-text="selectedDayData.paid"></span> Paid</div>
                <div class="day-chip" style="background:rgba(129,140,248,.12);color:var(--purple);border:1px solid rgba(129,140,248,.2)" x-show="selectedDayData.partial>0">½ <span x-text="selectedDayData.partial"></span> Partial</div>
              </div>
            </div>
          </template>
          <template x-if="!selectedDayData">
            <div>
              <div class="day-panel-date">Select a Day</div>
              <div class="day-panel-sub">Click any date to see instalment details</div>
            </div>
          </template>
        </div>

        <div class="day-scroll">
          <template x-if="selectedDayData && selectedDayData.events.length>0">
            <div>
              <template x-for="ev in selectedDayData.events" :key="ev.id">
                <div class="day-event" :class="`ev-type-${ev.status}`" :style="selectedEvent?.id===ev.id?'border-color:var(--teal-lt);box-shadow:0 0 0 1px rgba(19,174,207,.2)':''">
                  <div class="flex aic jb mb8">
                    <div class="flex aic g8">
                      <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0" :style="`background:linear-gradient(135deg,${ev.c1},${ev.c2})`" x-text="ev.ini"></div>
                      <div>
                        <div class="de-name" x-text="ev.name"></div>
                        <div class="de-loan" x-text="ev.loan"></div>
                      </div>
                    </div>
                    <span class="badge xs" :class="`b-${ev.status}`" x-text="ev.status"></span>
                  </div>

                  <div class="de-amount" x-text="'K ' + ev.amount.toLocaleString()"></div>
                  <div class="de-meta" x-text="'Instalment #' + ev.inst + ' of ' + ev.totalInst + ' · ' + ev.product"></div>

                  <!-- Overdue details -->
                  <template x-if="ev.status==='overdue'">
                    <div style="margin-top:8px;padding:8px 10px;background:rgba(239,68,68,.1);border-radius:7px">
                      <div class="xs tr f6" x-text="'⚠ ' + ev.daysOverdue + ' days overdue'"></div>
                      <div class="xs tr mt4" x-text="'Penalty applied: K ' + ev.penalty + ' (5% of instalment)'"></div>
                      <div class="xs" style="color:#FCA5A5;margin-top:2px" x-text="'Total due: K ' + (ev.amount + ev.penalty).toLocaleString()"></div>
                    </div>
                  </template>

                  <!-- Partial details -->
                  <template x-if="ev.status==='partial'">
                    <div style="margin-top:8px">
                      <div class="flex jb aic mb4">
                        <div class="xs ts">Paid so far</div>
                        <div class="xs tp f6" x-text="'K ' + ev.paidSoFar + ' / K ' + ev.amount.toLocaleString()"></div>
                      </div>
                      <div style="height:5px;background:var(--navy-line);border-radius:99px;overflow:hidden">
                        <div style="height:100%;border-radius:99px;background:var(--purple)" :style="`width:${Math.round((ev.paidSoFar/ev.amount)*100)}%`"></div>
                      </div>
                    </div>
                  </template>

                  <!-- Action buttons -->
                  <div class="flex g8 mt8" x-show="ev.status!=='paid'">
                    <button class="btn-p btn-sm w100" @click="recordPayment(ev)">+ Record Payment</button>
                    <button class="btn-g btn-sm" @click="sendSingleReminder(ev)" title="Send reminder">📨</button>
                  </div>
                  <div x-show="ev.status==='paid'" class="flex g8 mt8">
                    <button class="btn-g btn-sm w100" @click="window.location.href='/payments'">🧾 View Receipt</button>
                  </div>
                </div>
              </template>
            </div>
          </template>

          <template x-if="!selectedDayData || selectedDayData.events.length===0">
            <div class="empty-day">
              <div class="empty-day-ic">✨</div>
              <div class="f6 tw" x-text="selectedDayData?'No instalments on this day':'Select a date'"></div>
              <div class="xs ts mt8" x-text="selectedDayData?'Click another date or switch to List view to see all upcoming dues':'Click any date on the calendar'"></div>
            </div>
          </template>

          <!-- Mini upcoming list below events -->
          <template x-if="selectedDayData && selectedDayData.events.length>0">
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--navy-line)">
              <div class="xs ts f7 mb12" style="text-transform:uppercase;letter-spacing:.1em">Next 3 Upcoming</div>
              <template x-for="ev in upcomingThree" :key="ev.id">
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid rgba(30,52,80,.35)">
                  <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0" :style="`background:linear-gradient(135deg,${ev.c1},${ev.c2})`" x-text="ev.ini"></div>
                  <div style="flex:1">
                    <div class="xs f6 tw" x-text="ev.name"></div>
                    <div class="xs ts mt4 mono" x-text="ev.dateStr + ' · ' + ev.loan"></div>
                  </div>
                  <div class="xs mono ta" x-text="'K '+ev.amount.toLocaleString()"></div>
                </div>
              </template>
            </div>
          </template>

        </div>
      </div>
      <!-- /day-panel -->

    </div>
  </main>

  <!-- Toast -->
  <div class="toast-msg" x-show="toast" x-transition style="display:none" x-text="toastMsg"></div>

  <script>
  function calApp() {
    const TODAY = new Date(); TODAY.setHours(0,0,0,0);

    function fmtDate(d) { // YYYY-MM-DD from Date
      return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');
    }
    // Returns date string N days offset from TODAY
    function daysOffset(n) {
      const d = new Date(TODAY); d.setDate(d.getDate() + n); return fmtDate(d);
    }
    // Returns how many days past due (0 if not yet due)
    function computeOverdue(dateStr) {
      const due = new Date(dateStr); due.setHours(0,0,0,0);
      return Math.max(0, Math.floor((TODAY - due) / 86400000));
    }

    return {
      instalments: [],
      calView: 'month',
      currentYear: TODAY.getFullYear(),
      currentMonth: TODAY.getMonth(),
      selectedDate: fmtDate(TODAY),
      selectedEvent: null,
      listFilter: '',
      filterOverdue: false,
      navStats: { overdue: { total_loans: 0 }, loans: { pending: 0 }, due_today: { count: 0 } },
      toast: false, toastMsg: '',

      // Avatar color palette (deterministic from borrower name)
      _calPalette: [
        ['#EF4444','#B91C1C'],['#F59E0B','#D97706'],['#10B981','#059669'],
        ['#6366F1','#4338CA'],['#EC4899','#BE185D'],['#0B8FAC','#076E86'],
        ['#A78BFA','#7C3AED'],['#F472B6','#DB2777'],['#34D399','#10B981'],
        ['#60A5FA','#2563EB'],
      ],
      _calAvatar(name) {
        let h = 0;
        for (let i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) & 0xFFFFFF;
        return this._calPalette[Math.abs(h) % this._calPalette.length];
      },
      _calInitials(name) {
        const p = (name || '?').trim().split(/\s+/);
        return (p[0][0] + (p[1] ? p[1][0] : '')).toUpperCase();
      },
      eventsForDate(dateStr) {
        return this.instalments.filter(i => i.date === dateStr);
      },

      get TODAY_STR() { return fmtDate(TODAY); },

      get monthName() {
        return ['January','February','March','April','May','June','July','August','September','October','November','December'][this.currentMonth];
      },

      get overdueCount() { return this.instalments.filter(i=>i.status==='overdue').length; },
      get overdueTotal() {
        const t = this.instalments.filter(i=>i.status==='overdue').reduce((s,i)=>s+i.amount+i.penalty,0);
        return 'K ' + t.toLocaleString();
      },

      get monthStats() {
        const yr = this.currentYear, mo = this.currentMonth;
        const inMonth = this.instalments.filter(i=>{const d=new Date(i.date); return d.getFullYear()===yr && d.getMonth()===mo;});
        return {
          overdue: inMonth.filter(i=>i.status==='overdue').length,
          due: inMonth.filter(i=>i.status==='due').length,
          paid: inMonth.filter(i=>i.status==='paid').length,
          partial: inMonth.filter(i=>i.status==='partial').length,
          expected: 'K ' + inMonth.reduce((s,i)=>s+i.amount,0).toLocaleString(),
        };
      },

      get calCells() {
        const yr = this.currentYear, mo = this.currentMonth;
        const firstDay = new Date(yr, mo, 1).getDay(); // 0=Sun
        const daysInMonth = new Date(yr, mo+1, 0).getDate();
        const prevDays = new Date(yr, mo, 0).getDate();

        const cells = [];
        // prev month
        for (let i = firstDay-1; i >= 0; i--) {
          const d = new Date(yr, mo-1, prevDays-i);
          const ds = fmtDate(d);
          cells.push({ key:ds+'x', day:prevDays-i, dateStr:ds, inMonth:false, isToday:false, events:this.eventsForDate(ds), fullDate:'' });
        }
        // current month
        for (let d = 1; d <= daysInMonth; d++) {
          const dt = new Date(yr, mo, d);
          const ds = fmtDate(dt);
          const isToday = ds === this.TODAY_STR;
          cells.push({ key:ds, day:d, dateStr:ds, inMonth:true, isToday, events:this.eventsForDate(ds), fullDate: dt.toLocaleDateString('en-GB',{weekday:'long',day:'numeric',month:'long',year:'numeric'}) });
        }
        // next month fill
        const remaining = 42 - cells.length;
        for (let d = 1; d <= remaining; d++) {
          const dt = new Date(yr, mo+1, d);
          const ds = fmtDate(dt);
          cells.push({ key:ds+'y', day:d, dateStr:ds, inMonth:false, isToday:false, events:this.eventsForDate(ds), fullDate:'' });
        }
        return cells;
      },

      get selectedDayData() {
        if (!this.selectedDate) return null;
        const cell = this.calCells.find(c=>c.dateStr===this.selectedDate);
        if (!cell) return null;
        const evs = this.eventsForDate(this.selectedDate);
        const dt = new Date(this.selectedDate + 'T00:00:00');
        return {
          fullDate: dt.toLocaleDateString('en-GB',{weekday:'long',day:'numeric',month:'long',year:'numeric'}),
          events: evs,
          totalAmt: evs.reduce((s,e)=>s+e.amount,0),
          due: evs.filter(e=>e.status==='due').length,
          overdue: evs.filter(e=>e.status==='overdue').length,
          paid: evs.filter(e=>e.status==='paid').length,
          partial: evs.filter(e=>e.status==='partial').length,
        };
      },

      get weekDays() {
        // Show 7 days starting from today
        const days = [];
        for (let i = -1; i < 8; i++) {
          const d = new Date(TODAY);
          d.setDate(d.getDate() + i);
          const ds = fmtDate(d);
          const evs = this.eventsForDate(ds);
          days.push({
            dateStr: ds,
            dayNum: d.getDate(),
            dayName: d.toLocaleDateString('en-GB',{weekday:'long'}),
            monthName: d.toLocaleDateString('en-GB',{month:'long'}),
            isToday: ds === this.TODAY_STR,
            events: evs,
            totalAmt: evs.reduce((s,e)=>s+e.amount,0),
          });
        }
        return days;
      },

      get filteredListEvents() {
        const f = this.filterOverdue ? 'overdue' : this.listFilter;
        const sorted = [...this.instalments].sort((a,b)=>new Date(a.date)-new Date(b.date));
        if (!f) return sorted;
        return sorted.filter(e=>e.status===f);
      },

      get upcomingThree() {
        return this.instalments
          .filter(i=>i.status==='due' && i.date > this.selectedDate)
          .sort((a,b)=>new Date(a.date)-new Date(b.date))
          .slice(0,3)
          .map(i=>({...i, dateStr: new Date(i.date).toLocaleDateString('en-GB',{day:'2-digit',month:'short'})}));
      },

      // Computed for list view
      get allListEvents() {
        return [...this.instalments].map(i=>({
          ...i,
          dayNum: new Date(i.date+'T00:00:00').getDate(),
          monthShort: new Date(i.date+'T00:00:00').toLocaleDateString('en-GB',{month:'short'}),
        })).sort((a,b)=>new Date(a.date)-new Date(b.date));
      },

      selectDay(cell) {
        if (!cell.inMonth && this.calView==='month') {
          // Jump to that month
          const d = new Date(cell.dateStr);
          this.currentYear = d.getFullYear();
          this.currentMonth = d.getMonth();
        }
        this.selectedDate = cell.dateStr;
        this.selectedEvent = null;
      },

      prevMonth() {
        if (this.currentMonth === 0) { this.currentMonth = 11; this.currentYear--; }
        else { this.currentMonth--; }
      },
      nextMonth() {
        if (this.currentMonth === 11) { this.currentMonth = 0; this.currentYear++; }
        else { this.currentMonth++; }
      },
      goToday() {
        this.currentYear = TODAY.getFullYear();
        this.currentMonth = TODAY.getMonth();
        this.selectedDate = fmtDate(TODAY);
      },

      recordPayment(ev) {
        this.showToast('Opening payment form for ' + ev.name + ' · ' + ev.loan, 'var(--teal)');
      },
      sendSingleReminder(ev) {
        this.showToast('📨 Reminder sent to ' + ev.name + ' for K ' + ev.amount.toLocaleString(), 'var(--amber)');
      },
      sendReminders() {
        const dueToday = this.instalments.filter(i=>i.date===this.TODAY_STR && i.status!=='paid');
        const overdue  = this.instalments.filter(i=>i.status==='overdue');
        const total = dueToday.length + overdue.length;
        this.showToast('📨 Reminders sent to ' + total + ' borrowers via SMS & WhatsApp', 'var(--amber)');
      },

      showToast(msg, color) {
        this.toastMsg = msg;
        this.$el.querySelector('.toast-msg').style.background = color;
        this.toast = true;
        setTimeout(()=>{ this.toast = false; }, 3000);
      },

      async init() {
        this.selectedDate = fmtDate(TODAY);
        await Promise.all([this.loadNavStats(), this.loadSchedule()]);
      },

      async loadSchedule() {
        const token = localStorage.getItem('lms_token');
        const from  = fmtDate(new Date(TODAY.getFullYear(), TODAY.getMonth() - 1, 1));
        const to    = fmtDate(new Date(TODAY.getFullYear(), TODAY.getMonth() + 2, 0));
        try {
          const res = await fetch(`/api/calendar/schedule?from=${from}&to=${to}`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
          });
          if (!res.ok) return;
          const data = await res.json();
          this.instalments = (data || []).map(r => {
            const [c1, c2] = this._calAvatar(r.name);
            return { ...r, ini: this._calInitials(r.name), c1, c2 };
          });
        } catch (e) { console.error('Calendar load error:', e); }
      },

      async loadNavStats() {
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch('/api/stats', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
          if (res.ok) this.navStats = await res.json();
        } catch {}
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
