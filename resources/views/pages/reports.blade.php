<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gracimor LMS — Reports & Statements</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.min.js" defer></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=IBM+Plex+Mono:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --navy:  #0B1623; --navy2: #0F1E30; --navy3: #162840;
  --teal:  #0B8FAC; --teal2: #13AECF;
  --gold:  #C9972B; --gold2: #E5B84A;
  --green: #22C55E; --red:   #EF4444; --amber: #F5A623; --purple: #7C6FF7;
  --slate: #8DA3BC; --slate2: #5A7494;
  --white: #FFFFFF; --paper: #F7F4EF; --paper2: #EDE9E0;
  --ink:   #1A1208; --ink2:  #3D2E10;
  --border:  rgba(141,163,188,.12); --border2: rgba(141,163,188,.22);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Sora',sans-serif;background:var(--navy);color:var(--white);min-height:100vh;overflow-x:hidden}

/* layout */
.layout{display:flex;min-height:100vh}
.sidebar{width:240px;min-width:240px;background:var(--navy2);border-right:1px solid var(--border);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto}
.sidebar-logo{padding:26px 22px 18px;border-bottom:1px solid var(--border)}
.logo-brand{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;letter-spacing:.04em}
.logo-sub{font-size:9.5px;color:var(--slate2);text-transform:uppercase;letter-spacing:.14em;margin-top:2px}
.nav-section{font-size:9px;color:var(--slate2);padding:14px 22px 5px;text-transform:uppercase;letter-spacing:.14em}
.nav-item{display:flex;align-items:center;gap:9px;padding:9px 22px;font-size:13px;font-weight:500;color:var(--slate);cursor:pointer;transition:all .15s;border-left:2px solid transparent;text-decoration:none}
.nav-item:hover{color:var(--white);background:rgba(255,255,255,.04)}
.nav-item.active{color:var(--gold2);background:rgba(201,151,43,.08);border-left-color:var(--gold)}
.ni{font-size:15px;width:18px;text-align:center}
.main{flex:1;display:flex;flex-direction:column;min-width:0}

/* topbar */
.topbar{background:var(--navy2);border-bottom:1px solid var(--border);padding:0 30px;height:62px;display:flex;align-items:center;gap:14px;position:sticky;top:0;z-index:100}
.topbar-title{font-family:'Cormorant Garamond',serif;font-size:21px;font-weight:700;letter-spacing:.02em}
.topbar-title em{color:var(--gold2);font-style:italic}
.sep{flex:1}
.tbar-btn{padding:7px 15px;border-radius:7px;font-size:12.5px;font-weight:600;cursor:pointer;transition:all .2s;border:none;font-family:'Sora',sans-serif;display:flex;align-items:center;gap:7px}
.tbar-btn.gold{background:linear-gradient(135deg,var(--gold),var(--gold2));color:var(--ink);box-shadow:0 4px 14px rgba(201,151,43,.35)}
.tbar-btn.gold:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(201,151,43,.45)}
.tbar-btn.outline{background:transparent;color:var(--slate);border:1px solid var(--border2)}
.tbar-btn.outline:hover{color:var(--white);border-color:var(--slate)}
.report-tabs{display:flex;gap:3px;background:rgba(255,255,255,.04);border-radius:9px;padding:3px}
.rtab{padding:5px 12px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;color:var(--slate);background:transparent;border:none;font-family:'Sora',sans-serif;transition:all .15s;white-space:nowrap}
.rtab.active{background:var(--gold);color:var(--ink)}

/* content */
.content{padding:24px 30px;flex:1}
.builder-layout{display:grid;grid-template-columns:290px 1fr;gap:22px;align-items:start}

/* params */
.params-panel{background:var(--navy2);border:1px solid var(--border);border-radius:14px;overflow:hidden;position:sticky;top:82px}
.params-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.params-title{font-family:'Cormorant Garamond',serif;font-size:16px;font-weight:700}
.params-body{padding:18px 20px;display:flex;flex-direction:column;gap:14px}
.form-label{display:block;font-size:10.5px;font-weight:600;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px}
.form-input,.form-select{width:100%;padding:9px 12px;background:rgba(255,255,255,.04);border:1px solid var(--border2);border-radius:8px;color:var(--white);font-size:13px;font-family:'Sora',sans-serif;transition:border-color .2s}
.form-input:focus,.form-select:focus{outline:none;border-color:var(--gold);background:rgba(201,151,43,.05)}
.form-select option{background:var(--navy2);color:var(--white)}
.date-row{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.run-btn{width:100%;padding:12px;background:linear-gradient(135deg,var(--gold),var(--gold2));color:var(--ink);border:none;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;font-family:'Sora',sans-serif;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 16px rgba(201,151,43,.3)}
.run-btn:hover{transform:translateY(-1px);box-shadow:0 6px 22px rgba(201,151,43,.4)}
.export-row{display:flex;gap:8px}
.exp-btn{flex:1;padding:8px 6px;background:transparent;border:1px solid var(--border2);border-radius:8px;color:var(--slate);font-size:11.5px;font-weight:600;cursor:pointer;font-family:'Sora',sans-serif;transition:all .15s;display:flex;align-items:center;justify-content:center;gap:5px}
.exp-btn:hover{color:var(--white);border-color:var(--slate)}
.exp-btn.xpdf:hover{color:var(--red);border-color:rgba(239,68,68,.4);background:rgba(239,68,68,.06)}
.exp-btn.xxlsx:hover{color:var(--green);border-color:rgba(34,197,94,.4);background:rgba(34,197,94,.06)}
.exp-btn.xcsv:hover{color:var(--teal2);border-color:rgba(19,174,207,.4);background:rgba(19,174,207,.06)}
.divider{height:1px;background:var(--border);margin:4px 0}
.qlink{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:7px;padding:9px 12px;color:var(--slate);font-size:12px;font-weight:500;cursor:pointer;text-align:left;font-family:'Sora',sans-serif;display:flex;align-items:center;gap:8px;transition:all .15s;width:100%}
.qlink:hover{color:var(--white);border-color:var(--border2)}

/* report area */
.report-area{display:flex;flex-direction:column;gap:20px}

/* report header card */
.rhcard{background:linear-gradient(135deg,var(--navy2),var(--navy3));border:1px solid var(--border2);border-radius:14px;padding:24px 28px;display:flex;align-items:flex-start;justify-content:space-between;position:relative;overflow:hidden}
.rhcard::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--gold),var(--gold2),var(--teal))}
.rh-title{font-family:'Cormorant Garamond',serif;font-size:28px;font-weight:700;line-height:1.1}
.rh-title em{color:var(--gold2);font-style:italic}
.rh-sub{font-size:13px;color:var(--slate);margin-top:6px}
.rh-meta{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap}
.rh-tag{padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;background:rgba(201,151,43,.1);color:var(--gold2);border:1px solid rgba(201,151,43,.2);font-family:'IBM Plex Mono',monospace}
.rh-kpis{display:grid;grid-template-columns:1fr 1fr;gap:12px;min-width:250px}
.rh-kpi{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:10px;padding:12px 16px;text-align:right}
.rh-kpi-label{font-size:10px;color:var(--slate);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px}
.rh-kpi-value{font-family:'IBM Plex Mono',monospace;font-size:20px;font-weight:700}

/* stat tiles */
.stats-grid{display:grid;gap:14px}
.stat-tile{background:var(--navy2);border:1px solid var(--border);border-radius:12px;padding:18px 20px;position:relative;overflow:hidden}
.stat-tile::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px}
.t-teal::after{background:linear-gradient(90deg,var(--teal),var(--teal2))}
.t-gold::after{background:linear-gradient(90deg,var(--gold),var(--gold2))}
.t-green::after{background:linear-gradient(90deg,#166534,var(--green))}
.t-red::after{background:linear-gradient(90deg,#991b1b,var(--red))}
.t-purple::after{background:linear-gradient(90deg,#4c1d95,var(--purple))}
.t-amber::after{background:linear-gradient(90deg,#78350f,var(--amber))}
.st-label{font-size:10px;color:var(--slate);text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px}
.st-value{font-family:'IBM Plex Mono',monospace;font-size:24px;font-weight:700}
.t-teal .st-value{color:var(--teal2)}.t-gold .st-value{color:var(--gold2)}.t-green .st-value{color:var(--green)}
.t-red .st-value{color:var(--red)}.t-purple .st-value{color:var(--purple)}.t-amber .st-value{color:var(--amber)}
.st-sub{font-size:11px;color:var(--slate);margin-top:6px}
.st-up{font-size:11px;font-weight:700;color:var(--green);font-family:'IBM Plex Mono',monospace}
.st-dn{font-size:11px;font-weight:700;color:var(--red);font-family:'IBM Plex Mono',monospace}

/* chart panel */
.chart-panel{background:var(--navy2);border:1px solid var(--border);border-radius:14px;overflow:hidden}
.chart-head{padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px}
.chart-title{font-family:'Cormorant Garamond',serif;font-size:16px;font-weight:700}
.chart-body{padding:22px}
.bar-chart{width:100%;overflow:visible}

/* data panel / table */
.data-panel{background:var(--navy2);border:1px solid var(--border);border-radius:14px;overflow:hidden}
.data-head{padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px}
.data-title{font-family:'Cormorant Garamond',serif;font-size:16px;font-weight:700}
.rec-count{font-size:12px;color:var(--slate)}
.dtable{width:100%;border-collapse:collapse}
.dtable thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid var(--border)}
.dtable th{padding:11px 16px;font-size:10.5px;font-weight:600;color:var(--slate2);text-transform:uppercase;letter-spacing:.08em;text-align:left;white-space:nowrap}
.dtable th.r{text-align:right}
.dtable td{padding:13px 16px;font-size:13px;border-bottom:1px solid var(--border);vertical-align:middle}
.dtable td.r{text-align:right}
.dtable tbody tr{transition:background .1s}.dtable tbody tr:hover{background:rgba(255,255,255,.02)}
.dtable tbody tr:last-child td{border-bottom:none}
.dtable tfoot tr{background:rgba(201,151,43,.06);border-top:2px solid rgba(201,151,43,.3)}
.dtable tfoot td{padding:12px 16px;font-size:13px;font-weight:700;color:var(--gold2)}
.mono{font-family:'IBM Plex Mono',monospace}.money{font-family:'IBM Plex Mono',monospace;font-weight:600}
.chip{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap}
.chip::before{content:'●';font-size:7px}
.chip.active{background:rgba(34,197,94,.12);color:var(--green)}
.chip.closed{background:rgba(11,143,172,.12);color:var(--teal2)}
.chip.overdue{background:rgba(239,68,68,.12);color:var(--red)}
.chip.partial{background:rgba(124,111,247,.1);color:var(--purple)}
.chip.paid{background:rgba(34,197,94,.1);color:var(--green)}
.av{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0}
.rank-num{width:26px;height:26px;border-radius:50%;background:rgba(201,151,43,.1);color:var(--gold2);font-weight:700;font-size:12px;display:flex;align-items:center;justify-content:center;font-family:'IBM Plex Mono',monospace}
.mini-bar{height:5px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden}
.mini-fill{height:100%;border-radius:3px}
.par-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.par-card{background:var(--navy3);border:1px solid var(--border);border-radius:12px;padding:18px 22px}
.par-label{font-size:10px;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px}
.par-value{font-family:'IBM Plex Mono',monospace;font-size:32px;font-weight:700}
.par-sub{font-size:12px;color:var(--slate);margin-top:4px}
.par-bar{height:8px;background:rgba(255,255,255,.06);border-radius:4px;margin-top:12px;overflow:hidden}
.par-fill{height:100%;border-radius:4px}

/* statement (paper preview) */
.stmt-preview{background:var(--paper);border-radius:12px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.5);color:var(--ink);font-family:'Sora',sans-serif}
.stmt-hdr{background:var(--navy);color:white;padding:28px 36px;position:relative;overflow:hidden}
.stmt-hdr::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--gold),var(--gold2),var(--teal))}
.stmt-logo{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;letter-spacing:.04em}
.stmt-logo span{color:var(--gold2)}
.stmt-tagline{font-size:11px;color:rgba(255,255,255,.5);margin-top:2px}
.stmt-doc-title{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--gold2);margin-top:20px}
.stmt-period{font-size:13px;color:rgba(255,255,255,.6);margin-top:3px}
.stmt-body{padding:28px 36px}
.stmt-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--paper2)}
.stmt-sec-label{font-size:9px;font-weight:700;color:var(--ink2);text-transform:uppercase;letter-spacing:.14em;margin-bottom:10px}
.stmt-info-row{display:flex;gap:8px;margin-bottom:6px;font-size:13px}
.stmt-info-key{color:var(--ink2);min-width:118px}
.stmt-info-val{font-weight:600;color:var(--ink)}
.stmt-sum-boxes{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px}
.sbox{background:var(--paper2);border-radius:8px;padding:14px;text-align:center}
.sbox-label{font-size:9px;color:var(--ink2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:5px}
.sbox-value{font-family:'IBM Plex Mono',monospace;font-size:17px;font-weight:700;color:var(--ink)}
.sbox-value.gold{color:var(--gold)}.sbox-value.green{color:#166534}.sbox-value.red{color:#991b1b}.sbox-value.teal{color:#0e7490}
.stmt-table{width:100%;border-collapse:collapse;margin-bottom:20px;font-size:12.5px}
.stmt-table th{background:var(--ink);color:var(--paper);padding:9px 12px;font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;text-align:left}
.stmt-table th.r{text-align:right}
.stmt-table td{padding:9px 12px;color:var(--ink);border-bottom:1px solid var(--paper2);vertical-align:middle}
.stmt-table td.r{text-align:right}
.stmt-table tr.hl td{background:rgba(201,151,43,.07)}
.stmt-table tfoot td{padding:11px 12px;font-weight:700;background:var(--paper2);border-top:2px solid var(--ink);font-size:13px}
.stmt-footer{background:var(--ink);color:rgba(255,255,255,.6);padding:18px 36px;font-size:11px;display:flex;align-items:center;justify-content:space-between}

/* toast */
.toast-stack{position:fixed;bottom:24px;right:24px;display:flex;flex-direction:column;gap:8px;z-index:900}
.toast{padding:12px 18px;border-radius:10px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.4);animation:tIn .3s ease;min-width:240px}
@keyframes tIn{from{opacity:0;transform:translateX(16px)}to{opacity:1;transform:translateX(0)}}
.toast.success{background:#14532d;border:1px solid #166534;color:#86efac}
.toast.info{background:#0c4a6e;border:1px solid var(--teal);color:#7dd3fc}
.toast.gold{background:#78350f;border:1px solid var(--gold);color:var(--gold2)}

.fade-up{animation:fUp .4s ease both}
@keyframes fUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.stagger>*{animation:fUp .35s ease both}
.stagger>*:nth-child(1){animation-delay:.00s}.stagger>*:nth-child(2){animation-delay:.05s}
.stagger>*:nth-child(3){animation-delay:.10s}.stagger>*:nth-child(4){animation-delay:.15s}
.stagger>*:nth-child(5){animation-delay:.20s}.stagger>*:nth-child(6){animation-delay:.25s}
::-webkit-scrollbar{width:5px;height:5px}::-webkit-scrollbar-track{background:var(--navy)}
::-webkit-scrollbar-thumb{background:rgba(141,163,188,.18);border-radius:3px}

/* ── Print / Save as PDF ──────────────────────────────────────────────── */
@media print {
  /* hide everything except the report output */
  nav.sidebar, .topbar, .params-panel, .toast-stack,
  #lms-overlay, #lms-hamburger,
  div[x-show="!generated"] { display: none !important; }

  /* reset layout — report fills the page */
  body { background: #fff !important; color: #111 !important; font-family: 'Sora', sans-serif; }
  .layout { display: block !important; }
  .main  { display: block !important; }
  .content { padding: 0 !important; }
  .builder-layout { display: block !important; }
  .report-area { display: block !important; padding: 0 !important; }

  /* report header card */
  .rhcard { background: #f5f4ef !important; border: 1px solid #ccc !important;
            color: #111 !important; break-inside: avoid; margin-bottom: 16px !important; }
  .rhcard::before { background: #c9972b !important; }
  .rh-title, .rh-title em { color: #111 !important; }
  .rh-sub  { color: #555 !important; }
  .rh-tag  { background: #eee !important; color: #333 !important; border-color: #ccc !important; }
  .rh-kpi  { background: #f0efe8 !important; border-color: #ccc !important; }
  .rh-kpi-label { color: #666 !important; }
  .rh-kpi-value { color: #111 !important; }

  /* stat tiles */
  .stat-tile { background: #f5f4ef !important; border: 1px solid #ccc !important; break-inside: avoid; }
  .st-label  { color: #555 !important; }
  .st-value  { color: #111 !important; }
  .st-sub, .st-up, .st-dn { color: #444 !important; }

  /* data panel / tables */
  .data-panel, .atbl-wrap, .chart-panel { background: #fff !important; border: 1px solid #ccc !important; break-inside: avoid; }
  .dtable th, .atbl th { background: #e8e4d8 !important; color: #111 !important; }
  .dtable td, .atbl td { color: #111 !important; border-color: #ddd !important; }

  /* loan statement: already light-on-paper, just ensure page breaks */
  .stmt-paper { break-inside: avoid; }

  /* page margins */
  @page { margin: 18mm 14mm; }
}
</style>

  <style id="lms-responsive">
/* ══════════════════════════════════════════════════════════════════════════
   LMS Mobile Responsive  v3
   Breakpoints: 768px (tablet/phone)  |  480px (small phone)
══════════════════════════════════════════════════════════════════════════ */

/* Prevent the viewport-level horizontal scrollbar on html AND body */
html { overflow-x: hidden; max-width: 100vw; }
body { overflow-x: hidden; max-width: 100vw; }

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

  /* ── Reports page: rhcard — stack and unclip ─────────────────────────── */
  /* align-items:flex-start on desktop stops children from stretching;
     on mobile we must override to stretch so both halves fill the card width */
  .rhcard {
    flex-direction: column !important;
    align-items: stretch !important;   /* children fill full card width */
    gap: 16px !important;
    overflow: visible !important;      /* release overflow:hidden so nothing is clipped */
    width: 100% !important;
    box-sizing: border-box !important;
  }
  .rhcard > div:first-child { width: 100% !important; }

  .rh-kpis {
    min-width: unset !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    grid-template-columns: 1fr 1fr !important;
  }
  .rh-kpi-value { font-size: 16px !important; }

  /* Report tabs: horizontal scroll strip in topbar */
  .report-tabs {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
    flex-wrap: nowrap !important;
    scrollbar-width: none;
    max-width: calc(100vw - 80px);
  }
  .report-tabs::-webkit-scrollbar { display: none; }
  .rtab { white-space: nowrap !important; flex-shrink: 0 !important; }

  /* stats-grid: ensure it never exceeds the viewport width */
  .stats-grid {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
  }

  /* ── Grid/flex min-width fix ─────────────────────────────────────────────
     Grid and flex items default to min-width:auto, which means they refuse
     to shrink below their content's natural width even inside a 1fr column.
     Setting min-width:0 lets them shrink to fit the viewport correctly.
     This is the actual cause of content overflowing on the right. ───────── */
  .builder-layout > *,
  .report-area > *,
  .report-area > * > * { min-width: 0 !important; }

  /* ── Do NOT put overflow-x:hidden on .main or .content ───────────────────
     overflow-x:hidden on a parent element clips child scroll containers,
     making data-panel's scrollbar invisible and unusable.
     html+body overflow-x:hidden (already set above) is enough to prevent
     the page-level scrollbar without clipping child scroll containers. ───── */

  /* ── Data panel: scroll container for the Product/Loan Book tables ───────
     The card becomes the scroll container; its own width is constrained by
     the grid (1fr = viewport - padding). The table scrolls inside it.
     min-width:0 prevents the card from pushing the grid track wider. ───── */
  .data-panel {
    overflow-x: auto !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
  }
  /* Tables need a minimum width so all columns are readable before scrolling */
  .dtable { min-width: 680px !important; }

  /* ── Chart: let the SVG scale naturally via viewBox — no fixed width ──── */
  /* DO NOT set overflow:visible on .chart-panel — that would let the SVG
     bleed outside the card and cause the page-level scrollbar.
     The SVG has viewBox="0 0 700 190" and width:100% so it scales down
     proportionally to fit the container without overflowing anything. */
  .chart-body {
    padding: 12px !important;
  }
  .bar-chart {
    width: 100% !important;   /* scale to container, no fixed px */
    height: auto !important;
    min-width: unset !important;
    min-height: 110px !important; /* keep bars tall enough to read */
  }

  /* Statement: stack the header logo/number row */
  .stmt-hdr-row {
    flex-wrap: wrap !important;
    gap: 12px !important;
  }
  .stmt-hdr-row > div:last-child { text-align: left !important; }

  /* Statement controls bar: wrap buttons */
  .stmt-ctrls {
    flex-wrap: wrap !important;
    gap: 8px !important;
  }
  .stmt-ctrls .tbar-btn { flex: 1 1 auto !important; justify-content: center !important; }

  /* Statement footer: stack */
  .stmt-footer {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 8px !important;
    padding: 14px 20px !important;
  }

  /* Statement table: scroll */
  .stmt-preview { overflow-x: auto !important; }
  .stmt-table   { min-width: 480px !important; font-size: 11.5px !important; }
  .stmt-table th, .stmt-table td { padding: 7px 8px !important; }
  .stmt-body    { padding: 16px 14px !important; }
  .stmt-hdr     { padding: 18px 16px !important; }

  /* Params panel: slightly taller so Quick Reports are reachable */
  .params-panel { max-height: 380px !important; }

  /* Export buttons: stack */
  .export-row   { flex-wrap: wrap !important; }
  .exp-btn      { flex: 1 1 auto !important; }
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

  /* Reports: single-column KPI tiles, smaller PAR value */
  .rh-kpis    { grid-template-columns: 1fr 1fr !important; }
  .rh-title   { font-size: 20px !important; }
  .par-value  { font-size: 22px !important; }
  .sbox-value { font-size: 14px !important; }

  /* Topbar: hide export button label on tiny screens */
  .tbar-btn.gold span:last-child { display: none; }
  .report-tabs { max-width: calc(100vw - 60px); }
}
</style>












</head>
<body x-data="app()" x-init="init()">
<div class="layout">

<!-- SIDEBAR -->
<nav class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-brand">GRACIMOR</div>
    <div class="logo-sub">Loans Management</div>
  </div>
  <div style="padding:12px 0;flex:1">
    <div class="nav-section">Core</div>
    <a class="nav-item" href="/dashboard"><span class="ni">⊞</span> Dashboard</a>
    <a class="nav-item" href="/borrowers"><span class="ni">👥</span> Borrowers</a>
    <a class="nav-item" href="/loans"><span class="ni">📋</span> Loans</a>
    <a class="nav-item" href="/payments"><span class="ni">💳</span> Payments</a>
    <a class="nav-item" href="/calendar"><span class="ni">📅</span> Calendar</a>
    <div class="nav-section">Collections</div>
    <a class="nav-item" href="/overdue"><span class="ni">⚠️</span> Overdue &amp; Penalties</a>
    <div class="nav-section">Analytics</div>
    <a class="nav-item active" href="/reports"><span class="ni">📊</span> Reports &amp; Statements</a>
    <div class="nav-section">System</div>
    <a class="nav-item" href="/settings"><span class="ni">⚙️</span> Settings</a>
  </div>
</nav>

<!-- MAIN -->
<main class="main">
  <div class="topbar">
    <div class="topbar-title">Reports &amp; <em>Statements</em></div>
    <div class="report-tabs">
      <button class="rtab" :class="rt==='portfolio'  && 'active'" @click="rt='portfolio';  generated=true">Portfolio</button>
      <button class="rtab" :class="rt==='collection' && 'active'" @click="rt='collection'; generated=true">Collections</button>
      <button class="rtab" :class="rt==='aging'      && 'active'" @click="rt='aging';      generated=true">Aging &amp; PAR</button>
      <button class="rtab" :class="rt==='statement'  && 'active'" @click="rt='statement';  generated=true">Loan Statement</button>
      <button class="rtab" :class="rt==='loanbook'   && 'active'" @click="rt='loanbook';   generated=true">Loan Book</button>
    </div>
    <div class="sep"></div>
    <button class="tbar-btn outline" @click="exportPDF()" x-show="generated">🖨 Print</button>
    <button class="tbar-btn gold"    @click="exportPDF()" x-show="generated">📥 Export PDF</button>
  </div>

  <div class="content">
    <div class="builder-layout">

      <!-- PARAMS -->
      <aside class="params-panel">
        <div class="params-head">
          <span style="font-size:16px">⚙️</span>
          <div class="params-title">Report Parameters</div>
        </div>
        <div class="params-body">
          <div>
            <label class="form-label">Report Type</label>
            <select class="form-select" x-model="rt">
              <option value="portfolio">Portfolio Summary</option>
              <option value="collection">Collections Report</option>
              <option value="aging">Aging &amp; PAR Analysis</option>
              <option value="statement">Loan Statement</option>
              <option value="loanbook">Loan Book</option>
            </select>
          </div>

          <div>
            <label class="form-label">Period</label>
            <select class="form-select" x-model="period">
              <option value="this_month" x-text="'This Month — ' + thisMonthShort"></option>
              <option value="last_month" x-text="'Last Month — ' + lastMonthLabel"></option>
              <option value="this_q" x-text="thisQLabel"></option>
              <option value="last_q" x-text="lastQLabel"></option>
              <option value="ytd" x-text="ytdLabel"></option>
              <option value="last_year" x-text="lastYearLabel"></option>
              <option value="custom">Custom Range…</option>
            </select>
          </div>

          <div x-show="period==='custom'">
            <div class="date-row">
              <div><label class="form-label">From</label><input type="date" class="form-input" :value="customFrom"></div>
              <div><label class="form-label">To</label><input type="date" class="form-input" :value="customTo"></div>
            </div>
          </div>

          <div x-show="rt==='statement'">
            <label class="form-label">Borrower / Loan</label>
            <select class="form-select" x-model="borrower">
              <option value="">— Select a loan —</option>
              <template x-for="l in loanList" :key="l.id">
                <option :value="l.id" x-text="l.label"></option>
              </template>
            </select>
          </div>

          <div x-show="rt!=='statement'">
            <label class="form-label">Loan Officer</label>
            <select class="form-select">
              <option>All Officers</option>
              <option>F. Mwala</option>
              <option>C. Banda</option>
              <option>N. Tembo</option>
            </select>
          </div>

          <div x-show="rt==='portfolio' || rt==='loanbook'">
            <label class="form-label">Loan Status</label>
            <select class="form-select">
              <option>All Statuses</option>
              <option>Active Only</option>
              <option>Closed Only</option>
              <option>Active + Overdue</option>
            </select>
          </div>

          <div class="divider"></div>

          <button class="run-btn" @click="runReport()">
            <span x-text="running ? '⏳  Generating…' : '▶  Generate Report'"></span>
          </button>

          <div>
            <label class="form-label">Export As</label>
            <div class="export-row">
              <button class="exp-btn xpdf"  @click="exportPDF()">📄 PDF</button>
              <button class="exp-btn xxlsx" @click="toast('info','📊','XLSX export requires server-side processing')">📊 XLSX</button>
              <button class="exp-btn xcsv"  @click="toast('info','📋','CSV export requires server-side processing')">📋 CSV</button>
            </div>
          </div>

          <div class="divider"></div>
          <div>
            <div class="form-label" style="margin-bottom:10px">Quick Reports</div>
            <div style="display:flex;flex-direction:column;gap:6px">
              <button class="qlink" @click="rt='portfolio';  generated=true">📊 Monthly Portfolio Summary</button>
              <button class="qlink" @click="rt='collection'; generated=true">💳 Today's Collections</button>
              <button class="qlink" @click="rt='aging';      generated=true">⚠️ PAR 30 / 60 / 90</button>
              <button class="qlink" @click="rt='statement';  generated=true">📄 Borrower Statement</button>
              <button class="qlink" @click="rt='loanbook';   generated=true">📚 Full Loan Book</button>
            </div>
          </div>
        </div>
      </aside>

      <!-- EMPTY STATE (shown before first generate) -->
      <div x-show="!generated" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 24px;text-align:center;background:var(--navy2);border:1px solid var(--border);border-radius:14px;gap:16px">
        <div style="font-size:48px;opacity:.35">📊</div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:700;color:var(--slate)">No report generated yet</div>
        <div style="font-size:13px;color:var(--slate2);max-width:320px;line-height:1.6">Select a report type and period from the panel on the left, then click <strong style="color:var(--gold2)">Generate Report</strong> to view results.</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-top:8px">
          <button class="qlink" @click="rt='portfolio';  generated=true" style="font-size:12px">📊 Portfolio Summary</button>
          <button class="qlink" @click="rt='collection'; generated=true" style="font-size:12px">💳 Collections</button>
          <button class="qlink" @click="rt='aging';      generated=true" style="font-size:12px">⚠️ Aging &amp; PAR</button>
          <button class="qlink" @click="rt='statement';  generated=true" style="font-size:12px">📄 Statement</button>
          <button class="qlink" @click="rt='loanbook';   generated=true" style="font-size:12px">📚 Loan Book</button>
        </div>
      </div>

      <!-- REPORT OUTPUT -->
      <div class="report-area fade-up" x-show="generated">

        <!-- ████ PORTFOLIO ████ -->
        <template x-if="rt==='portfolio'">
          <div>
            <div class="rhcard" style="margin-bottom:20px">
              <div>
                <div class="rh-title">Portfolio <em>Summary</em> Report</div>
                <div class="rh-sub" x-text="'Consolidated performance — all active loans · ' + thisMonthFull"></div>
                <div class="rh-meta">
                  <span class="rh-tag" x-text="thisMonthShort"></span>
                  <span class="rh-tag">All Officers</span>
                  <span class="rh-tag" x-text="'Generated ' + todayShort"></span>
                </div>
              </div>
              <div class="rh-kpis">
                <div class="rh-kpi"><div class="rh-kpi-label">Total Portfolio</div><div class="rh-kpi-value" style="color:var(--teal2)" x-text="_fmtK(portfolioKpis.totalOutstanding)">K —</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">Active Loans</div><div class="rh-kpi-value" style="color:var(--green)" x-text="portfolioKpis.activeLoans">—</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">PAR 30</div><div class="rh-kpi-value" style="color:var(--amber)" x-text="portfolioKpis.par30 + '%'">—</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">Collections Rate</div><div class="rh-kpi-value" style="color:var(--gold2)" x-text="portfolioKpis.collectionsRate + '%'">—</div></div>
              </div>
            </div>

            <!-- 6 KPI tiles -->
            <div class="stats-grid stagger" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
              <div class="stat-tile t-teal"><div class="st-label">Total Disbursed (Period)</div><div class="st-value" x-text="_fmtK(portfolioKpis.disbursedPeriod)">K —</div><div class="st-sub" x-text="collectionPeriod"></div></div>
              <div class="stat-tile t-gold"><div class="st-label">Total Collected (Period)</div><div class="st-value" x-text="_fmtK(portfolioKpis.collectedPeriod)">K —</div><div class="st-sub" x-text="collectionPeriod"></div></div>
              <div class="stat-tile t-green"><div class="st-label">Loans Closed (Period)</div><div class="st-value" x-text="portfolioKpis.closedPeriod">—</div><div class="st-sub" x-text="collectionPeriod"></div></div>
              <div class="stat-tile t-red"><div class="st-label">Overdue Loans</div><div class="st-value" x-text="portfolioKpis.overdueLoans">—</div><div class="st-sub">Currently overdue</div></div>
              <div class="stat-tile t-amber"><div class="st-label">Penalties Outstanding</div><div class="st-value" x-text="_fmtK(portfolioKpis.penaltiesOutstanding)">K —</div><div class="st-sub">Across all active loans</div></div>
              <div class="stat-tile t-purple"><div class="st-label">Average Loan Size</div><div class="st-value" x-text="_fmtK(portfolioKpis.avgLoanSize)">K —</div><div class="st-sub" x-text="'Across ' + portfolioKpis.activeLoans + ' active loans'"></div></div>
            </div>

            <!-- Disbursements chart -->
            <div class="chart-panel" style="margin-bottom:20px">
              <div class="chart-head">
                <div class="chart-title">Monthly Disbursements &amp; Collections</div>
                <div style="display:flex;gap:16px;align-items:center;margin-left:auto">
                  <span style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--slate)"><span style="width:10px;height:10px;border-radius:2px;background:var(--teal);display:inline-block"></span>Disbursed</span>
                  <span style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--slate)"><span style="width:10px;height:10px;border-radius:2px;background:var(--gold);display:inline-block"></span>Collected</span>
                </div>
              </div>
              <div class="chart-body">
                <svg class="bar-chart" height="190" viewBox="0 0 700 190">
                  <!-- grid -->
                  <line x1="40" y1="10" x2="680" y2="10" stroke="rgba(141,163,188,.07)" stroke-width="1" stroke-dasharray="4,4"/>
                  <line x1="40" y1="48" x2="680" y2="48" stroke="rgba(141,163,188,.07)" stroke-width="1" stroke-dasharray="4,4"/>
                  <line x1="40" y1="86" x2="680" y2="86" stroke="rgba(141,163,188,.07)" stroke-width="1" stroke-dasharray="4,4"/>
                  <line x1="40" y1="124" x2="680" y2="124" stroke="rgba(141,163,188,.07)" stroke-width="1" stroke-dasharray="4,4"/>
                  <line x1="40" y1="150" x2="680" y2="150" stroke="rgba(141,163,188,.14)" stroke-width="1"/>
                  <text x="35" y="13" fill="#5A7494" font-size="9" text-anchor="end">500K</text>
                  <text x="35" y="51" fill="#5A7494" font-size="9" text-anchor="end">375K</text>
                  <text x="35" y="89" fill="#5A7494" font-size="9" text-anchor="end">250K</text>
                  <text x="35" y="127" fill="#5A7494" font-size="9" text-anchor="end">125K</text>
                  <!-- bars for 8 months -->
                  <template x-for="(b,i) in chartBars" :key="i">
                    <g :transform="`translate(${48+i*80},0)`">
                      <rect :y="150-b.d" x="0" width="26" :height="b.d" fill="var(--teal)" rx="3" opacity=".85"/>
                      <rect :y="150-b.c" x="30" width="26" :height="b.c" fill="var(--gold)" rx="3" opacity=".85"/>
                      <text x="28" y="164" fill="#5A7494" font-size="9" text-anchor="middle" x-text="b.m"></text>
                    </g>
                  </template>
                </svg>
              </div>
            </div>

            <!-- Product table -->
            <div class="data-panel">
              <div class="data-head">
                <div class="data-title">Portfolio by Loan Product</div>
                <div class="rec-count" x-text="productRows.length+' product'+(productRows.length===1?'':'s')+' · '+portfolioKpis.activeLoans+' active loans'"></div>
              </div>
              <table class="dtable">
                <thead><tr>
                  <th>Product</th><th class="r">Loans</th><th class="r">Disbursed</th>
                  <th class="r">Outstanding</th><th class="r">Collected (MTD)</th>
                  <th class="r">Overdue</th><th class="r">PAR 30</th><th class="r">Avg Term</th>
                </tr></thead>
                <tbody>
                  <template x-for="r in productRows" :key="r.name">
                    <tr>
                      <td><strong x-text="r.name"></strong></td>
                      <td class="r mono" x-text="r.loans"></td>
                      <td class="r money" style="color:var(--teal2)" x-text="'K '+r.disbursed"></td>
                      <td class="r money" style="color:var(--amber)" x-text="'K '+r.outstanding"></td>
                      <td class="r money" style="color:var(--green)" x-text="'K '+r.collected"></td>
                      <td class="r mono" :style="r.overdue>0?'color:var(--red)':'color:var(--green)'" x-text="r.overdue"></td>
                      <td class="r"><span class="mono" :style="`color:${r.parColor}`" x-text="r.par+'%'"></span></td>
                      <td class="r mono" style="color:var(--slate)" x-text="r.avgTerm+' mo'"></td>
                    </tr>
                  </template>
                </tbody>
                <tfoot><tr>
                  <td>TOTALS</td>
                  <td class="r" x-text="productTotals.loans"></td>
                  <td class="r" x-text="'K '+productTotals.disbursed"></td>
                  <td class="r" x-text="'K '+productTotals.outstanding"></td>
                  <td class="r" x-text="'K '+productTotals.collected"></td>
                  <td class="r" style="color:var(--red)" x-text="productTotals.overdue"></td>
                  <td class="r" x-text="productTotals.par+'%'"></td>
                  <td class="r" x-text="productTotals.avgTerm ? productTotals.avgTerm+' mo' : '—'"></td>
                </tr></tfoot>
              </table>
            </div>
          </div>
        </template>

        <!-- ████ COLLECTIONS ████ -->
        <template x-if="rt==='collection'">
          <div>
            <div class="rhcard" style="margin-bottom:20px">
              <div>
                <div class="rh-title">Collections <em>Report</em></div>
                <div class="rh-sub" x-text="'Payment receipts and officer performance — ' + thisMonthFull"></div>
                <div class="rh-meta">
                  <span class="rh-tag" x-text="collectionPeriod"></span>
                  <span class="rh-tag" x-text="collectionKpis.receiptCount + ' Receipts'"></span>
                </div>
              </div>
              <div class="rh-kpis">
                <div class="rh-kpi"><div class="rh-kpi-label">Collected (Period)</div><div class="rh-kpi-value" style="color:var(--green)" x-text="_fmtK(collectionKpis.totalCollected)">K —</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">Receipts Issued</div><div class="rh-kpi-value" style="color:var(--teal2)" x-text="collectionKpis.receiptCount">—</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">Collection Rate</div><div class="rh-kpi-value" style="color:var(--gold2)" x-text="collectionKpis.collectionsRate + '%'">—</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">Penalties Collected</div><div class="rh-kpi-value" style="color:var(--amber)" x-text="_fmtK(collectionKpis.penaltiesCollected)">K —</div></div>
              </div>
            </div>

            <!-- Officer performance -->
            <div class="data-panel" style="margin-bottom:20px">
              <div class="data-head">
                <div class="data-title" x-text="'Officer Performance — ' + thisMonthFull"></div>
                <div class="rec-count" x-text="officers.length + ' officer' + (officers.length===1?'':'s')"></div>
              </div>
              <table class="dtable">
                <thead><tr>
                  <th>#</th><th>Officer</th><th class="r">Loans</th>
                  <th class="r">Due (MTD)</th><th class="r">Collected</th>
                  <th class="r">Receipts</th><th class="r">Rate</th>
                  <th class="r">Overdue</th><th class="r">Target</th>
                </tr></thead>
                <tbody>
                  <template x-for="(o,i) in officers" :key="o.name">
                    <tr>
                      <td><div class="rank-num" x-text="i+1"></div></td>
                      <td>
                        <div style="display:flex;align-items:center;gap:10px">
                          <div class="av" :style="`background:linear-gradient(135deg,${o.c1},${o.c2})`" x-text="o.ini"></div>
                          <div><div style="font-weight:600" x-text="o.name"></div><div style="font-size:11px;color:var(--slate)" x-text="o.role"></div></div>
                        </div>
                      </td>
                      <td class="r mono" x-text="o.loans"></td>
                      <td class="r money" style="color:var(--slate)" x-text="'K '+o.due.toLocaleString()"></td>
                      <td class="r money" style="color:var(--green)" x-text="'K '+o.col.toLocaleString()"></td>
                      <td class="r mono" x-text="o.rec"></td>
                      <td class="r">
                        <div style="display:flex;align-items:center;gap:8px;justify-content:flex-end">
                          <div class="mini-bar" style="width:56px"><div class="mini-fill" :style="`width:${o.rate}%;background:${o.rate>=95?'var(--green)':o.rate>=85?'var(--gold)':'var(--red)'}`"></div></div>
                          <span class="mono" :style="`color:${o.rate>=95?'var(--green)':o.rate>=85?'var(--gold)':'var(--red)'}`" x-text="o.rate+'%'"></span>
                        </div>
                      </td>
                      <td class="r mono" :style="o.ov>0?'color:var(--red)':'color:var(--green)'" x-text="o.ov"></td>
                      <td class="r"><span :style="`color:${o.ok?'var(--green)':'var(--red)'}`" x-text="o.ok?'✓ On Track':'✗ Behind'"></span></td>
                    </tr>
                  </template>
                </tbody>
                <tfoot><tr>
                  <td colspan="2">TOTALS / AVERAGE</td>
                  <td class="r" x-text="collectionTotals.loans"></td>
                  <td class="r">—</td>
                  <td class="r money" x-text="_fmtK(collectionTotals.col)"></td>
                  <td class="r" x-text="collectionTotals.rec"></td>
                  <td class="r">—</td>
                  <td class="r" :style="collectionTotals.ov>0?'color:var(--red)':'color:var(--green)'" x-text="collectionTotals.ov"></td>
                  <td class="r">—</td>
                </tr></tfoot>
              </table>
            </div>

            <!-- Receipts -->
            <div class="data-panel">
              <div class="data-head">
                <div class="data-title" x-text="'Payment Receipts — ' + thisMonthFull"></div>
                <div class="rec-count" x-text="'Showing '+receipts.length+' of '+collectionKpis.receiptCount"></div>
              </div>
              <table class="dtable">
                <thead><tr>
                  <th>Receipt</th><th>Borrower</th><th>Loan No.</th><th>Type</th>
                  <th>Method</th><th class="r">Principal</th><th class="r">Interest</th>
                  <th class="r">Penalty</th><th class="r">Total</th><th>Date</th><th>Officer</th>
                </tr></thead>
                <tbody>
                  <template x-for="r in receipts" :key="r.rcp">
                    <tr>
                      <td class="mono" style="color:var(--teal2);font-size:12px" x-text="r.rcp"></td>
                      <td>
                        <div style="display:flex;align-items:center;gap:8px">
                          <div class="av" style="width:28px;height:28px;font-size:10px" :style="`background:linear-gradient(135deg,${r.c1},${r.c2})`" x-text="r.ini"></div>
                          <span style="font-size:13px;font-weight:500" x-text="r.name"></span>
                        </div>
                      </td>
                      <td class="mono" style="font-size:12px;color:var(--slate)" x-text="r.loan"></td>
                      <td><span class="chip" :class="r.type==='Early Settlement'?'closed':r.type==='Partial'?'partial':'paid'" x-text="r.type"></span></td>
                      <td style="font-size:12px;color:var(--slate)" x-text="r.meth"></td>
                      <td class="r money" style="color:var(--slate)" x-text="'K '+r.pri.toLocaleString()"></td>
                      <td class="r money" style="color:var(--teal2)" x-text="'K '+r.int.toLocaleString()"></td>
                      <td class="r money" :style="r.pen>0?'color:var(--red)':'color:var(--slate)'" x-text="r.pen>0?'K '+r.pen.toLocaleString():'—'"></td>
                      <td class="r money" style="color:var(--green);font-size:14px" x-text="'K '+r.tot.toLocaleString()"></td>
                      <td class="mono" style="font-size:11.5px;color:var(--slate)" x-text="r.date"></td>
                      <td style="font-size:12.5px;color:var(--slate)" x-text="r.off"></td>
                    </tr>
                  </template>
                </tbody>
                <tfoot><tr>
                  <td colspan="5" x-text="'TOTALS ('+collectionKpis.receiptCount+' receipts)'">TOTALS</td>
                  <td class="r" x-text="_fmtK(collectionTotals.pri)"></td>
                  <td class="r" x-text="_fmtK(collectionTotals.int)"></td>
                  <td class="r" :style="collectionTotals.pen>0?'color:var(--amber)':''" x-text="collectionTotals.pen>0?_fmtK(collectionTotals.pen):'—'"></td>
                  <td class="r money" style="font-size:15px" x-text="_fmtK(collectionTotals.tot)"></td>
                  <td colspan="2"></td>
                </tr></tfoot>
              </table>
            </div>
          </div>
        </template>

        <!-- ████ AGING & PAR ████ -->
        <template x-if="rt==='aging'">
          <div>
            <div class="rhcard" style="margin-bottom:20px">
              <div>
                <div class="rh-title">Aging &amp; <em>PAR Analysis</em></div>
                <div class="rh-sub">Portfolio at Risk ratios and instalment aging breakdown</div>
                <div class="rh-meta"><span class="rh-tag" x-text="'As of ' + todayShort"></span></div>
              </div>
              <div class="rh-kpis">
                <div class="rh-kpi"><div class="rh-kpi-label">PAR 30</div><div class="rh-kpi-value" style="color:var(--amber)">9.8%</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">PAR 60</div><div class="rh-kpi-value" style="color:var(--red)">6.1%</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">PAR 90</div><div class="rh-kpi-value" style="color:var(--red)">3.7%</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">At-Risk Loans</div><div class="rh-kpi-value" style="color:var(--red)">14</div></div>
              </div>
            </div>

            <!-- PAR cards -->
            <div class="par-grid" style="margin-bottom:20px">
              <template x-for="p in parCards" :key="p.lbl">
                <div class="par-card">
                  <div class="par-label" x-text="p.lbl"></div>
                  <div class="par-value" :style="`color:${p.clr}`" x-text="p.val"></div>
                  <div class="par-sub" x-text="p.sub"></div>
                  <div class="par-bar"><div class="par-fill" :style="`width:${p.pct}%;background:${p.clr}`"></div></div>
                </div>
              </template>
            </div>

            <!-- Aging table -->
            <div class="data-panel" style="margin-bottom:20px">
              <div class="data-head">
                <div class="data-title">Instalment Aging Breakdown</div>
                <div class="rec-count">By days past due</div>
              </div>
              <table class="dtable">
                <thead><tr>
                  <th>Aging Bucket</th><th class="r">Loans</th><th class="r">Instalments</th>
                  <th class="r">Principal Due</th><th class="r">Interest Due</th>
                  <th class="r">Penalties</th><th class="r">Total Arrears</th><th class="r">% Portfolio</th>
                </tr></thead>
                <tbody>
                  <template x-for="r in agingRows" :key="r.bkt">
                    <tr>
                      <td style="font-weight:600" :style="`color:${r.clr}`" x-text="r.bkt"></td>
                      <td class="r mono" x-text="r.loans"></td>
                      <td class="r mono" x-text="r.inst"></td>
                      <td class="r money" :style="`color:${r.clr}`" x-text="'K '+r.pri.toLocaleString()"></td>
                      <td class="r money" style="color:var(--slate)" x-text="'K '+r.int.toLocaleString()"></td>
                      <td class="r money" :style="r.pen>0?'color:var(--red)':'color:var(--slate)'" x-text="r.pen>0?'K '+r.pen.toLocaleString():'—'"></td>
                      <td class="r money" style="color:var(--gold2);font-size:14px" x-text="'K '+r.tot.toLocaleString()"></td>
                      <td class="r">
                        <div style="display:flex;align-items:center;gap:8px;justify-content:flex-end">
                          <div class="mini-bar" style="width:46px"><div class="mini-fill" :style="`width:${r.pct*4.5}%;background:${r.clr}`"></div></div>
                          <span class="mono" :style="`color:${r.clr}`" x-text="r.pct+'%'"></span>
                        </div>
                      </td>
                    </tr>
                  </template>
                </tbody>
                <tfoot><tr>
                  <td>TOTALS</td>
                  <td class="r" x-text="agingTotals.loans"></td>
                  <td class="r" x-text="agingTotals.inst"></td>
                  <td class="r" x-text="'K '+agingTotals.pri.toLocaleString('en-ZM',{maximumFractionDigits:0})"></td>
                  <td class="r" x-text="'K '+agingTotals.int.toLocaleString('en-ZM',{maximumFractionDigits:0})"></td>
                  <td class="r" style="color:var(--amber)" x-text="'K '+agingTotals.pen.toLocaleString('en-ZM',{maximumFractionDigits:0})"></td>
                  <td class="r" style="font-size:15px" x-text="'K '+agingTotals.tot.toLocaleString('en-ZM',{maximumFractionDigits:0})"></td>
                  <td class="r">100%</td>
                </tr></tfoot>
              </table>
            </div>

            <!-- PAR trend chart -->
            <div class="chart-panel">
              <div class="chart-head">
                <div class="chart-title">PAR 30 Trend — Last 8 Months</div>
                <div style="margin-left:auto;font-size:12px;color:var(--slate)">Portfolio at Risk % over time</div>
              </div>
              <div class="chart-body">
                <svg class="bar-chart" height="160" viewBox="0 0 700 160">
                  <line x1="40" y1="10" x2="680" y2="10" stroke="rgba(141,163,188,.06)" stroke-width="1" stroke-dasharray="4,4"/>
                  <line x1="40" y1="42" x2="680" y2="42" stroke="rgba(141,163,188,.06)" stroke-width="1" stroke-dasharray="4,4"/>
                  <line x1="40" y1="74" x2="680" y2="74" stroke="rgba(141,163,188,.06)" stroke-width="1" stroke-dasharray="4,4"/>
                  <line x1="40" y1="106" x2="680" y2="106" stroke="rgba(141,163,188,.06)" stroke-width="1" stroke-dasharray="4,4"/>
                  <line x1="40" y1="130" x2="680" y2="130" stroke="rgba(141,163,188,.13)" stroke-width="1"/>
                  <text x="35" y="13" fill="#5A7494" font-size="9" text-anchor="end">20%</text>
                  <text x="35" y="45" fill="#5A7494" font-size="9" text-anchor="end">15%</text>
                  <text x="35" y="77" fill="#5A7494" font-size="9" text-anchor="end">10%</text>
                  <text x="35" y="109" fill="#5A7494" font-size="9" text-anchor="end">5%</text>
                  <!-- gradient fill area under line -->
                  <defs>
                    <linearGradient id="parGrad" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="#F5A623" stop-opacity=".2"/>
                      <stop offset="100%" stop-color="#F5A623" stop-opacity="0"/>
                    </linearGradient>
                  </defs>
                  <polygon points="90,98 170,88 250,80 330,75 410,84 490,76 570,68 650,98 650,130 90,130"
                    fill="url(#parGrad)"/>
                  <polyline points="90,98 170,88 250,80 330,75 410,84 490,76 570,68 650,98"
                    fill="none" stroke="var(--amber)" stroke-width="2.5" stroke-linejoin="round"/>
                  <circle cx="90"  cy="98" r="4" fill="var(--amber)"/>
                  <circle cx="170" cy="88" r="4" fill="var(--amber)"/>
                  <circle cx="250" cy="80" r="4" fill="var(--amber)"/>
                  <circle cx="330" cy="75" r="4" fill="var(--amber)"/>
                  <circle cx="410" cy="84" r="4" fill="var(--amber)"/>
                  <circle cx="490" cy="76" r="4" fill="var(--amber)"/>
                  <circle cx="570" cy="68" r="4" fill="var(--amber)"/>
                  <circle cx="650" cy="98" r="6" fill="var(--red)" stroke="white" stroke-width="1.5"/>
                  <text x="650" y="63" fill="var(--red)" font-size="11" font-weight="700" text-anchor="middle">9.8%</text>
                  <text x="90"  y="144" fill="#5A7494" font-size="9" text-anchor="middle">Jul</text>
                  <text x="170" y="144" fill="#5A7494" font-size="9" text-anchor="middle">Aug</text>
                  <text x="250" y="144" fill="#5A7494" font-size="9" text-anchor="middle">Sep</text>
                  <text x="330" y="144" fill="#5A7494" font-size="9" text-anchor="middle">Oct</text>
                  <text x="410" y="144" fill="#5A7494" font-size="9" text-anchor="middle">Nov</text>
                  <text x="490" y="144" fill="#5A7494" font-size="9" text-anchor="middle">Dec</text>
                  <text x="570" y="144" fill="#5A7494" font-size="9" text-anchor="middle">Jan</text>
                  <text x="650" y="144" fill="#5A7494" font-size="9" text-anchor="middle">Feb</text>
                </svg>
              </div>
            </div>
          </div>
        </template>

        <!-- ████ LOAN STATEMENT ████ -->
        <template x-if="rt==='statement'">
          <div>
            <!-- controls -->
            <div class="stmt-ctrls" style="display:flex;align-items:center;gap:12px;margin-bottom:18px;padding:14px 18px;background:var(--navy2);border:1px solid var(--border);border-radius:12px">
              <span style="font-size:13px;color:var(--slate)">Showing statement for:</span>
              <span style="font-size:14px;font-weight:700" x-text="currentStatement?.name"></span>
              <span class="mono" style="color:var(--teal2);font-size:12px" x-text="currentStatement?.loanNum"></span>
              <div class="sep"></div>
              <button class="tbar-btn outline" @click="exportPDF()">🖨 Print</button>
              <button class="tbar-btn gold"    @click="exportPDF()">📄 Download PDF</button>
            </div>

            <!-- Paper statement -->
            <div class="stmt-preview">
              <div class="stmt-hdr">
                <div class="stmt-hdr-row" style="display:flex;justify-content:space-between;align-items:flex-start">
                  <div>
                    <div class="stmt-logo">GRASS<span>EMA</span></div>
                    <div class="stmt-tagline">Empowering communities through accessible finance</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.4);margin-top:4px">Plot 4821, Cairo Road · Lusaka, Zambia · +260 977 000 001</div>
                  </div>
                  <div style="text-align:right">
                    <div style="font-size:10px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.1em">Statement No.</div>
                    <div style="font-family:'IBM Plex Mono',monospace;font-size:18px;font-weight:700;color:var(--gold2)" x-text="currentStatement?.stmtNum || 'STM-?'"></div>
                    <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:4px" x-text="'Generated: ' + todayShort"></div>
                  </div>
                </div>
                <div class="stmt-doc-title">Loan Account Statement</div>
                <div class="stmt-period" x-text="'Period: ' + (currentStatement?.period||'')"></div>
              </div>

              <div class="stmt-body">
                <!-- Borrower + Loan info -->
                <div class="stmt-info-grid">
                  <div>
                    <div class="stmt-sec-label">Borrower Information</div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Full Name:</span><span class="stmt-info-val" x-text="currentStatement?.name"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Borrower No.:</span><span class="stmt-info-val" x-text="currentStatement?.bNum"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">NRC:</span><span class="stmt-info-val" x-text="currentStatement?.nrc"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Phone:</span><span class="stmt-info-val" x-text="currentStatement?.phone"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Address:</span><span class="stmt-info-val" x-text="currentStatement?.address"></span></div>
                  </div>
                  <div>
                    <div class="stmt-sec-label">Loan Details</div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Loan No.:</span><span class="stmt-info-val" x-text="currentStatement?.loanNum"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Product:</span><span class="stmt-info-val" x-text="currentStatement?.product"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Principal:</span><span class="stmt-info-val" x-text="currentStatement?.principal"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Interest Rate:</span><span class="stmt-info-val" x-text="currentStatement?.rate"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Term:</span><span class="stmt-info-val" x-text="currentStatement?.term"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Disbursed:</span><span class="stmt-info-val" x-text="currentStatement?.disbursed"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Maturity:</span><span class="stmt-info-val" x-text="currentStatement?.maturity"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Loan Officer:</span><span class="stmt-info-val" x-text="currentStatement?.officer"></span></div>
                  </div>
                </div>

                <!-- Summary boxes -->
                <div class="stmt-sum-boxes">
                  <div class="sbox"><div class="sbox-label">Principal</div><div class="sbox-value" x-text="currentStatement?.principal"></div></div>
                  <div class="sbox"><div class="sbox-label">Total Repayable</div><div class="sbox-value gold" x-text="currentStatement?.totalRep"></div></div>
                  <div class="sbox"><div class="sbox-label">Total Paid</div><div class="sbox-value green" x-text="currentStatement?.totalPaid"></div></div>
                  <div class="sbox"><div class="sbox-label">Balance Due</div><div class="sbox-value" :class="currentStatement?.balClass" x-text="currentStatement?.balance"></div></div>
                </div>

                <!-- Transactions -->
                <div class="stmt-sec-label">Transaction &amp; Payment History</div>
                <table class="stmt-table">
                  <thead><tr>
                    <th>#</th><th>Receipt / Ref</th><th>Date</th><th>Description</th>
                    <th class="r">Principal</th><th class="r">Interest</th>
                    <th class="r">Penalty</th><th class="r">Amount Paid</th><th class="r">Balance</th>
                  </tr></thead>
                  <tbody>
                    <template x-for="(tx,i) in currentStatement?.txs" :key="i">
                      <tr :class="tx.hl ? 'hl' : ''">
                        <td class="mono" style="color:#5A7494;font-size:11px" x-text="i+1"></td>
                        <td class="mono" style="color:#0e7490;font-size:11.5px" x-text="tx.ref"></td>
                        <td class="mono" style="font-size:11.5px" x-text="tx.date"></td>
                        <td x-text="tx.desc"></td>
                        <td class="r mono" style="font-size:12px" x-text="tx.pri ? 'K '+tx.pri.toLocaleString() : '—'"></td>
                        <td class="r mono" style="font-size:12px" x-text="tx.int ? 'K '+tx.int.toLocaleString() : '—'"></td>
                        <td class="r mono" style="font-size:12px;color:#991b1b" x-text="tx.pen ? 'K '+tx.pen.toLocaleString() : '—'"></td>
                        <td class="r mono" style="font-size:13px;font-weight:700;color:#166534" x-text="tx.paid ? 'K '+tx.paid.toLocaleString() : '—'"></td>
                        <td class="r mono" style="font-size:12px" x-text="'K '+tx.bal.toLocaleString()"></td>
                      </tr>
                    </template>
                  </tbody>
                  <tfoot><tr>
                    <td colspan="4">TOTALS</td>
                    <td class="r" x-text="'K '+currentStatement?.tot?.pri"></td>
                    <td class="r" x-text="'K '+currentStatement?.tot?.int"></td>
                    <td class="r" x-text="'K '+currentStatement?.tot?.pen"></td>
                    <td class="r" x-text="'K '+currentStatement?.tot?.paid"></td>
                    <td class="r" style="color:#0e7490" x-text="currentStatement?.balance"></td>
                  </tr></tfoot>
                </table>

                <div style="background:var(--paper2);border-radius:8px;padding:14px 18px;font-size:12px;color:var(--ink2);line-height:1.6;margin-top:16px">
                  <strong>Important Notice:</strong> This statement is generated by Gracimor LMS and is accurate as of the date shown. For queries contact your loan officer or visit our Lusaka branch. This is a computer-generated document.
                </div>
              </div>

              <div class="stmt-footer">
                <span>Gracimor Loans · Lusaka, Zambia · www.gracimor.co.zm</span>
                <span>Regulated by the Bank of Zambia · Licence No. NBZ/2024/441</span>
                <span>Page 1 of 1</span>
              </div>
            </div>
          </div>
        </template>

        <!-- ████ LOAN BOOK ████ -->
        <template x-if="rt==='loanbook'">
          <div>
            <div class="rhcard" style="margin-bottom:20px">
              <div>
                <div class="rh-title">Active <em>Loan Book</em></div>
                <div class="rh-sub" x-text="'Complete portfolio register — as of ' + todayShort"></div>
                <div class="rh-meta">
                  <span class="rh-tag">142 Active Loans</span>
                  <span class="rh-tag">K 2.84M Outstanding</span>
                </div>
              </div>
              <div class="rh-kpis">
                <div class="rh-kpi"><div class="rh-kpi-label">Vehicle-Backed</div><div class="rh-kpi-value" style="color:var(--teal2)">98</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">Land-Backed</div><div class="rh-kpi-value" style="color:var(--gold2)">44</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">Avg Interest Rate</div><div class="rh-kpi-value" style="color:var(--purple)">27.4%</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">Avg Remaining Term</div><div class="rh-kpi-value" style="color:var(--green)">5.2 mo</div></div>
              </div>
            </div>

            <div class="data-panel">
              <div class="data-head">
                <div class="data-title">Loan Book Register</div>
                <div class="rec-count" x-text="loanBook.length ? loanBook.length+' records' : 'Loading…'"></div>
              </div>
              <table class="dtable">
                <thead><tr>
                  <th>Loan No.</th><th>Borrower</th><th>Product</th><th>Collateral Ref</th>
                  <th class="r">Principal</th><th class="r">Rate</th><th>Term</th>
                  <th>Disbursed</th><th>Maturity</th>
                  <th class="r">Outstanding</th><th class="r">Monthly PMT</th>
                  <th>Status</th><th>Officer</th>
                </tr></thead>
                <tbody>
                  <template x-for="l in loanBook" :key="l.num">
                    <tr>
                      <td class="mono" style="color:var(--teal2);font-size:12px" x-text="l.num"></td>
                      <td>
                        <div style="display:flex;align-items:center;gap:8px">
                          <div class="av" style="width:28px;height:28px;font-size:10px" :style="`background:linear-gradient(135deg,${l.c1},${l.c2})`" x-text="l.ini"></div>
                          <span style="font-size:13px;font-weight:500" x-text="l.name"></span>
                        </div>
                      </td>
                      <td style="font-size:12px;color:var(--slate)" x-text="l.prod"></td>
                      <td class="mono" style="font-size:11.5px;color:var(--slate2)" x-text="l.col"></td>
                      <td class="r money" style="color:var(--white)" x-text="'K '+l.pri.toLocaleString()"></td>
                      <td class="r mono" style="color:var(--purple)" x-text="l.rate+'%'"></td>
                      <td class="mono" style="font-size:12px" x-text="l.term+' mo'"></td>
                      <td class="mono" style="font-size:11.5px;color:var(--slate)" x-text="l.dis"></td>
                      <td class="mono" style="font-size:11.5px;color:var(--slate)" x-text="l.mat"></td>
                      <td class="r money" :style="`color:${l.out/l.pri>.5?'var(--amber)':'var(--teal2)'}`" x-text="'K '+l.out.toLocaleString()"></td>
                      <td class="r money" style="color:var(--gold2)" x-text="'K '+l.pmt.toLocaleString()"></td>
                      <td><span class="chip" :class="l.status" x-text="l.status.charAt(0).toUpperCase()+l.status.slice(1)"></span></td>
                      <td style="font-size:12px;color:var(--slate)" x-text="l.off"></td>
                    </tr>
                  </template>
                </tbody>
                <tfoot><tr>
                  <td colspan="4" x-text="'TOTALS ('+loanBookTotals.loan_count+' loans)'">TOTALS</td>
                  <td class="r" x-text="'K '+(loanBookTotals.total_principal||0).toLocaleString()"></td>
                  <td class="r" x-text="(loanBookTotals.avg_rate||0).toFixed(1)+'% avg'"></td>
                  <td x-text="(loanBookTotals.avg_term||0).toFixed(1)+' mo avg'"></td>
                  <td colspan="2"></td>
                  <td class="r" style="font-size:14px" x-text="'K '+(loanBookTotals.total_outstanding||0).toLocaleString()"></td>
                  <td class="r" x-text="'K '+(loanBookTotals.total_monthly_pmt||0).toLocaleString()"></td>
                  <td colspan="2"></td>
                </tr></tfoot>
              </table>
            </div>
          </div>
        </template>

      </div><!-- /report-area -->
    </div><!-- /builder-layout -->
  </div><!-- /content -->
</main>
</div>

<!-- TOASTS -->
<div class="toast-stack">
  <template x-for="t in toasts" :key="t.id">
    <div class="toast" :class="t.type"><span x-text="t.icon"></span><span x-text="t.msg"></span></div>
  </template>
</div>

<script>
function app(){
  return {
    rt:'portfolio', period:'this_month', borrower:'', running:false, generated:false, toasts:[],
    thisMonthFull:'', thisMonthShort:'', lastMonthLabel:'', thisQLabel:'', lastQLabel:'',
    ytdLabel:'', lastYearLabel:'', todayShort:'', collectionPeriod:'', customFrom:'', customTo:'',

    chartBars: [],
    productRows: [],
    officers: [],
    receipts: [],
    parCards: [
      {lbl:'PAR 30 — Loans > 30 days overdue', val:'0%', clr:'var(--amber)', pct:0, sub:'No data'},
      {lbl:'PAR 60 — Loans > 60 days overdue', val:'0%', clr:'var(--red)',   pct:0, sub:'No data'},
      {lbl:'PAR 90 — Loans > 90 days overdue', val:'0%', clr:'#dc2626',      pct:0, sub:'No data'},
      {lbl:'Default Rate — Write-off risk',    val:'0%', clr:'#7f1d1d',      pct:0, sub:'No data'},
    ],
    agingRows: [],
    currentStatement: null,
    loanBook: [],
    loanBookTotals: { loan_count:0, total_principal:0, avg_rate:0, avg_term:0, total_outstanding:0, total_monthly_pmt:0 },
    loanList: [],
    portfolioKpis: { totalOutstanding:0, activeLoans:0, par30:0, par60:0, par90:0, par30Amt:0, par60Amt:0, par90Amt:0, disbursedPeriod:0, collectedPeriod:0, closedPeriod:0, overdueLoans:0, penaltiesOutstanding:0, avgLoanSize:0, collectionsRate:0 },
    productTotals: { loans:0, disbursed:'0', outstanding:'0', collected:'0', overdue:0, par:0, avgTerm:0 },
    agingTotals: { loans:0, inst:0, pri:0, int:0, pen:0, tot:0 },
    collectionKpis: { totalCollected:0, receiptCount:0, collectionsRate:0, penaltiesCollected:0 },
    collectionTotals: { loans:0, col:0, rec:0, ov:0, pri:0, int:0, pen:0, tot:0 },

    async init(){
      const now = new Date();
      const M = ['January','February','March','April','May','June','July','August','September','October','November','December'];
      const S = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      const pad = n => String(n).padStart(2,'0');
      const mo = now.getMonth(), yr = now.getFullYear();
      this.thisMonthFull = M[mo] + ' ' + yr;
      this.thisMonthShort = S[mo] + ' ' + yr;
      const lm = new Date(yr, mo-1, 1);
      this.lastMonthLabel = S[lm.getMonth()] + ' ' + lm.getFullYear();
      const q = Math.floor(mo/3), qs = q*3;
      this.thisQLabel = 'Q'+(q+1)+' '+yr+' ('+S[qs]+'–'+S[qs+2]+')';
      const pq = q===0?3:q-1, pqy = q===0?yr-1:yr, pqs=pq*3;
      this.lastQLabel = 'Q'+(pq+1)+' '+pqy+' ('+S[pqs]+'–'+S[pqs+2]+')';
      this.ytdLabel = 'Year to Date '+yr;
      this.lastYearLabel = 'Full Year '+(yr-1);
      this.todayShort = now.getDate()+' '+S[mo]+' '+yr;
      this.collectionPeriod = '01–'+now.getDate()+' '+S[mo]+' '+yr;
      this.customFrom = yr+'-'+pad(mo+1)+'-01';
      this.customTo = yr+'-'+pad(mo+1)+'-'+pad(now.getDate());
      await Promise.all([this.loadLoanBook(), this.loadRecentReceipts(), this.loadLoanList()]);
    },

    _rptAvatar(name) {
      const pal = [
        ['#7c3aed','#8b5cf6'],['#059669','#10b981'],['#dc2626','#ef4444'],
        ['#0891b2','#06b6d4'],['#d97706','#f59e0b'],['#be185d','#ec4899'],
        ['#4f46e5','#6366f1'],['#166534','#22c55e'],['#1e3a5f','#0ea5e9'],
        ['#9f1239','#f43f5e'],
      ];
      let h = 0;
      for (let i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) & 0xFFFFFF;
      return pal[Math.abs(h) % pal.length];
    },
    _rptInitials(name) {
      const p = (name || '?').trim().split(/\s+/);
      return (p[0][0] + (p[1] ? p[1][0] : '')).toUpperCase();
    },
    _rptFmtDate(d) {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
    },

    async loadLoanBook() {
      const token = localStorage.getItem('lms_token');
      try {
        const res = await fetch('/api/reports/loan-book?per_page=200', {
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const data = await res.json();
        this.loanBookTotals = data.totals || this.loanBookTotals;
        this.loanBook = (data.loans?.data || []).map(l => {
          const name = ((l.borrower?.first_name || '') + ' ' + (l.borrower?.last_name || '')).trim();
          const [c1, c2] = this._rptAvatar(name);
          const col = l.collateral_asset
            ? (l.collateral_asset.vehicle_registration || l.collateral_asset.asset_type || '—')
            : '—';
          return {
            num:    l.loan_number,
            name,
            ini:    this._rptInitials(name),
            c1, c2,
            prod:   l.loan_product?.name || '—',
            col,
            pri:    parseFloat(l.principal_amount) || 0,
            rate:   parseFloat(l.interest_rate) || 0,
            term:   parseInt(l.term_months) || 0,
            dis:    this._rptFmtDate(l.disbursed_at),
            mat:    this._rptFmtDate(l.maturity_date),
            out:    parseFloat(l.loan_balance?.total_outstanding) || 0,
            pmt:    parseFloat(l.monthly_instalment) || 0,
            status: l.status,
            off:    l.applied_by?.name || '—',
          };
        });
      } catch (e) { console.error('Loan book load error:', e); }
    },

    async loadRecentReceipts() {
      const token = localStorage.getItem('lms_token');
      try {
        const res = await fetch('/api/payments?per_page=20', {
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const data = await res.json();
        const methIc = { cash:'💵', bank_transfer:'🏦', mobile_money:'📱', cheque:'📋' };
        const typeMap = { instalment:'Full Instalment', partial:'Partial', early_settlement:'Early Settlement', penalty:'Penalty' };
        this.receipts = (data.data || []).map(p => {
          const b = p.loan?.borrower || {};
          const name = ((b.first_name || '') + ' ' + (b.last_name || '')).trim();
          const [c1, c2] = this._rptAvatar(name || '?');
          return {
            rcp:  p.receipt_number || ('RCP-' + String(p.id).padStart(5,'0')),
            name, ini: this._rptInitials(name || '?'), c1, c2,
            loan: p.loan?.loan_number || '—',
            type: typeMap[p.payment_type] || p.payment_type,
            meth: (p.payment_method || '').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()),
            methIc: methIc[p.payment_method] || '💰',
            pri:  parseFloat(p.towards_principal) || 0,
            int:  parseFloat(p.towards_interest) || 0,
            pen:  parseFloat(p.towards_penalty) || 0,
            tot:  parseFloat(p.amount_received) || 0,
            date: this._rptFmtDate(p.payment_date),
            off:  p.recorded_by?.name || '—',
          };
        });
      } catch (e) { console.error('Receipts load error:', e); }
    },

    exportPDF(){
      if(!this.generated){ this.toast('gold','⚠️','Generate a report first'); return; }
      this.toast('gold','🖨️','Opening print dialog — choose "Save as PDF"');
      setTimeout(()=>{ window.print(); }, 400);
    },

    async runReport(){
      this.running = true;
      try {
        if      (this.rt === 'portfolio')  await this.loadPortfolio();
        else if (this.rt === 'collection') await this.loadCollections();
        else if (this.rt === 'aging')      await this.loadAging();
        else if (this.rt === 'statement')  await this.loadStatement();
        else if (this.rt === 'loanbook')   await this.loadLoanBook();
        this.generated = true;
        this.toast('success','✓','Report generated successfully');
        this.$nextTick(()=>{
          const el = document.querySelector('.report-area');
          if(el) el.scrollIntoView({behavior:'smooth',block:'start'});
        });
      } catch(e) {
        this.toast('error','✗','Failed to load report: ' + e.message);
        console.error(e);
      } finally {
        this.running = false;
      }
    },

    _periodDates() {
      const now = new Date(), yr = now.getFullYear(), mo = now.getMonth();
      const pad = n => String(n).padStart(2,'0');
      const lastDay = d => new Date(d.getFullYear(), d.getMonth()+1, 0).getDate();
      if (this.period === 'last_month') {
        const lm = new Date(yr, mo-1, 1);
        return { dateFrom: `${lm.getFullYear()}-${pad(lm.getMonth()+1)}-01`, dateTo: `${lm.getFullYear()}-${pad(lm.getMonth()+1)}-${pad(lastDay(lm))}` };
      } else if (this.period === 'ytd') {
        return { dateFrom: `${yr}-01-01`, dateTo: `${yr}-${pad(mo+1)}-${pad(now.getDate())}` };
      } else if (this.period === 'custom') {
        return { dateFrom: this.customFrom, dateTo: this.customTo };
      }
      return { dateFrom: `${yr}-${pad(mo+1)}-01`, dateTo: `${yr}-${pad(mo+1)}-${pad(now.getDate())}` };
    },

    _fmtK(n) {
      const v = parseFloat(n) || 0;
      if (v >= 1000000) return 'K ' + (v/1000000).toFixed(2) + 'M';
      if (v >= 1000)    return 'K ' + Math.round(v).toLocaleString();
      return 'K ' + v.toFixed(0);
    },

    async loadLoanList() {
      const token = localStorage.getItem('lms_token');
      try {
        const res = await fetch('/api/loans?per_page=200', { headers: { 'Authorization': 'Bearer '+token, 'Accept': 'application/json' } });
        if (!res.ok) return;
        const d = await res.json();
        this.loanList = (d.data || []).map(l => {
          const b = l.borrower || {};
          const name = ((b.first_name||'') + ' ' + (b.last_name||'')).trim();
          return { id: l.id, label: name + ' — ' + l.loan_number };
        });
      } catch(e) { console.error('Loan list error:', e); }
    },

    async loadPortfolio() {
      const token = localStorage.getItem('lms_token');
      const { dateFrom, dateTo } = this._periodDates();
      const res = await fetch(`/api/reports/portfolio?date_from=${dateFrom}&date_to=${dateTo}`, { headers: { 'Authorization': 'Bearer '+token, 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('Portfolio API failed');
      const d = await res.json();
      const k = d.kpis || {}, port = Math.max(k.total_outstanding||0, 1);
      this.portfolioKpis = {
        totalOutstanding: k.total_outstanding || 0,
        activeLoans:      k.total_active_loans || 0,
        par30: k.par_30 || 0, par60: k.par_60 || 0, par90: k.par_90 || 0,
        par30Amt: k.par30_amount || 0, par60Amt: k.par60_amount || 0, par90Amt: k.par90_amount || 0,
        disbursedPeriod:     k.total_disbursed_period || 0,
        collectedPeriod:     k.total_collected_period || 0,
        closedPeriod:        k.loans_closed_in_period || 0,
        overdueLoans:        k.overdue_loans || 0,
        penaltiesOutstanding: k.penalties_outstanding || 0,
        avgLoanSize:         k.avg_loan_size || 0,
        collectionsRate:     port > 0 ? Math.round((k.total_collected_period||0)/port*1000)/10 : 0,
      };
      // Update PAR cards with real data
      this.parCards = [
        {lbl:'PAR 30 — Loans > 30 days overdue', val:k.par_30+'%',  clr:'var(--amber)', pct:Math.min(100,Math.round((k.par_30||0)*5)), sub:this._fmtK(k.par30_amount)+' of '+this._fmtK(k.total_outstanding)+' portfolio'},
        {lbl:'PAR 60 — Loans > 60 days overdue', val:k.par_60+'%',  clr:'var(--red)',   pct:Math.min(100,Math.round((k.par_60||0)*5)), sub:this._fmtK(k.par60_amount)+' of '+this._fmtK(k.total_outstanding)+' portfolio'},
        {lbl:'PAR 90 — Loans > 90 days overdue', val:k.par_90+'%',  clr:'#dc2626',      pct:Math.min(100,Math.round((k.par_90||0)*5)), sub:this._fmtK(k.par90_amount)+' of '+this._fmtK(k.total_outstanding)+' portfolio'},
        {lbl:'Default Rate — Write-off risk',    val:(k.par_90||0)+'%', clr:'#7f1d1d',  pct:Math.min(100,Math.round((k.par_90||0)*3)), sub:'Based on PAR 90 threshold'},
      ];
      // Chart bars — scale to 140px max
      const chart = d.monthly_chart || [];
      const maxVal = Math.max(...chart.map(r => Math.max(parseFloat(r.disbursed)||0, parseFloat(r.collected)||0)), 1);
      const S = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      this.chartBars = chart.map(r => ({
        m: S[parseInt((r.month||'2026-01').slice(5,7))-1] || r.month,
        d: Math.round((parseFloat(r.disbursed)||0) / maxVal * 140),
        c: Math.round((parseFloat(r.collected)||0) / maxVal * 140),
      }));
      // Product rows
      let tL=0, tD=0, tO=0, tC=0, tOv=0;
      this.productRows = (d.by_product || []).map(p => {
        tL += parseInt(p.total_loans)||0; tD += parseFloat(p.total_disbursed)||0;
        tO += parseFloat(p.outstanding)||0; tC += parseFloat(p.collected_mtd)||0;
        tOv += parseInt(p.overdue_count)||0;
        return { name:p.name, loans:p.total_loans||0,
          disbursed: (parseFloat(p.total_disbursed)||0).toLocaleString('en-ZM',{maximumFractionDigits:0}),
          outstanding: (parseFloat(p.outstanding)||0).toLocaleString('en-ZM',{maximumFractionDigits:0}),
          collected: (parseFloat(p.collected_mtd)||0).toLocaleString('en-ZM',{maximumFractionDigits:0}),
          overdue: p.overdue_count||0, par: k.par_30||0, parColor:'var(--amber)',
          avgTerm: Math.round(parseFloat(p.avg_term)||0) };
      });
      this.productTotals = { loans:tL, disbursed:tD.toLocaleString('en-ZM',{maximumFractionDigits:0}), outstanding:tO.toLocaleString('en-ZM',{maximumFractionDigits:0}), collected:tC.toLocaleString('en-ZM',{maximumFractionDigits:0}), overdue:tOv, par:k.par_30||0, avgTerm:0 };
    },

    async loadCollections() {
      const token = localStorage.getItem('lms_token');
      const { dateFrom, dateTo } = this._periodDates();
      const res = await fetch(`/api/reports/collections?date_from=${dateFrom}&date_to=${dateTo}`, { headers: { 'Authorization': 'Bearer '+token, 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('Collections API failed');
      const d = await res.json();
      const pt = d.payment_totals || {};
      const totDue = (d.officer_perf||[]).reduce((s,o)=>s+(parseFloat(o.due_amount)||0),0);
      const totCol = parseFloat(pt.total_collected)||0;
      this.collectionKpis = {
        totalCollected:    totCol,
        receiptCount:      parseInt(pt.receipt_count)||0,
        collectionsRate:   totDue > 0 ? Math.round(totCol / totDue * 100) : 0,
        penaltiesCollected:parseFloat(pt.total_penalty)||0,
      };
      this.officers = (d.officer_perf || []).map(o => {
        const name = o.name || '';
        const [c1,c2] = this._rptAvatar(name);
        const col = parseFloat(o.total_collected)||0;
        const due = parseFloat(o.due_amount)||0;
        const rate = due > 0 ? Math.round(col / due * 100) : (col > 0 ? 100 : 0);
        return { name, ini:this._rptInitials(name), c1, c2,
          role: (o.role||'loan_officer').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()),
          loans: parseInt(o.unique_loans)||0, col, due, rate,
          pri:parseFloat(o.total_principal)||0,
          int:parseFloat(o.total_interest)||0, pen:parseFloat(o.total_penalty)||0,
          rec: parseInt(o.receipt_count)||0, ov: parseInt(o.overdue_count)||0,
          ok: rate >= 90 };
      });
      // Officer tfoot totals computed from officers array
      this.collectionTotals = this.officers.reduce((a,o) => ({
        loans: a.loans+o.loans, col: a.col+o.col, rec: a.rec+o.rec, ov: a.ov+o.ov,
        pri: a.pri+(pt.total_principal?0:0), int:0, pen:0, tot:0
      }), {loans:0, col:0, rec:0, ov:0, pri:0, int:0, pen:0, tot:0});
      this.collectionTotals = {
        loans: this.officers.reduce((s,o)=>s+o.loans,0),
        col:   parseFloat(pt.total_collected)||0,
        rec:   parseInt(pt.receipt_count)||0,
        ov:    this.officers.reduce((s,o)=>s+o.ov,0),
        pri:   parseFloat(pt.total_principal)||0,
        int:   parseFloat(pt.total_interest)||0,
        pen:   parseFloat(pt.total_penalty)||0,
        tot:   parseFloat(pt.total_collected)||0,
      };
      const methIc = {cash:'💵',bank_transfer:'🏦',mobile_money:'📱',cheque:'📋'};
      const typeMap = {instalment:'Full Instalment',partial:'Partial',early_settlement:'Early Settlement',penalty:'Penalty'};
      this.receipts = (d.payments?.data||[]).map(p => {
        const b = p.loan?.borrower||{};
        const name = ((b.first_name||'')+' '+(b.last_name||'')).trim();
        const [c1,c2] = this._rptAvatar(name||'?');
        return { rcp:p.receipt_number||('RCP-'+String(p.id).padStart(5,'0')), name, ini:this._rptInitials(name||'?'), c1, c2,
          loan:p.loan?.loan_number||'—', type:typeMap[p.payment_type]||p.payment_type,
          meth:(p.payment_method||'').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()),
          methIc:methIc[p.payment_method]||'💰', pri:parseFloat(p.towards_principal)||0,
          int:parseFloat(p.towards_interest)||0, pen:parseFloat(p.towards_penalty)||0,
          tot:parseFloat(p.amount_received)||0, date:this._rptFmtDate(p.payment_date),
          off:p.recorded_by?.name||'—' };
      });
    },

    async loadAging() {
      const token = localStorage.getItem('lms_token');
      const res = await fetch('/api/reports/aging', { headers: { 'Authorization': 'Bearer '+token, 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('Aging API failed');
      const d = await res.json();
      const lblMap = {'1_7':'1 – 7 days overdue','8_30':'8 – 30 days overdue','31_60':'31 – 60 days overdue','61_90':'61 – 90 days overdue','90+':'Over 90 days overdue'};
      const clrMap = {'1_7':'var(--amber)','8_30':'var(--amber)','31_60':'var(--red)','61_90':'#dc2626','90+':'#7f1d1d'};
      const rows = d.aging || [];
      const grandTot = rows.reduce((s,r) => s+(r.total||0),0)||1;
      this.agingRows = [
        {bkt:'Current (not yet due)',loans:0,inst:0,pri:0,int:0,pen:0,tot:0,pct:0,clr:'var(--green)'},
        ...rows.map(r => ({bkt:lblMap[r.bucket]||r.bucket, loans:r.loans||0, inst:r.instalments||0,
          pri:parseFloat(r.principal_due)||0, int:parseFloat(r.interest_due)||0, pen:parseFloat(r.penalties)||0,
          tot:parseFloat(r.total)||0, pct:Math.round((r.total||0)/grandTot*100), clr:clrMap[r.bucket]||'var(--amber)'}))
      ];
      this.agingTotals = rows.reduce((a,r) => ({loans:a.loans+(r.loans||0), inst:a.inst+(r.instalments||0),
        pri:a.pri+(parseFloat(r.principal_due)||0), int:a.int+(parseFloat(r.interest_due)||0),
        pen:a.pen+(parseFloat(r.penalties)||0), tot:a.tot+(parseFloat(r.total)||0)}),
        {loans:0,inst:0,pri:0,int:0,pen:0,tot:0});
    },

    async loadStatement() {
      if (!this.borrower) { this.toast('gold','⚠️','Please select a loan'); throw new Error('No loan selected'); }
      const token = localStorage.getItem('lms_token');
      const res = await fetch(`/api/reports/statement/${this.borrower}`, { headers: { 'Authorization': 'Bearer '+token, 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('Statement API failed');
      const d = await res.json();
      const fa = n => 'K '+(parseFloat(n)||0).toLocaleString('en-ZM',{minimumFractionDigits:0,maximumFractionDigits:2});
      const txs = (d.transactions||[]);
      this.currentStatement = {
        stmtNum: d.statement_number,
        name: ((d.borrower?.first_name||'')+' '+(d.borrower?.last_name||'')).trim(),
        bNum: d.borrower?.borrower_number||'—', nrc:d.borrower?.nrc_number||'—',
        phone:d.borrower?.phone_primary||'—', address:d.borrower?.residential_address||'—',
        loanNum:d.loan?.loan_number||'—', product:d.product?.name||'—',
        principal:fa(d.summary?.principal), rate:(d.loan?.interest_rate||0)+'% flat rate',
        term:(d.loan?.term_months||0)+' months', disbursed:this._rptFmtDate(d.loan?.disbursed_at),
        maturity:this._rptFmtDate(d.loan?.maturity_date), officer:d.officer?.name||'—',
        period:this._rptFmtDate(d.loan?.disbursed_at)+' – '+this._rptFmtDate(d.loan?.maturity_date),
        totalRep:fa(d.summary?.total_repayable), totalPaid:fa(d.summary?.total_paid),
        balance:fa(d.summary?.balance_due), balClass:(d.summary?.balance_due||0)>0?'red':'teal',
        txs: txs.map(tx => ({ref:tx.ref, date:this._rptFmtDate(tx.date), desc:tx.description,
          pri:tx.principal, int:tx.interest, pen:tx.penalty, paid:tx.paid, bal:tx.balance,
          hl:tx.description==='Loan Disbursement'})),
        tot: {
          pri: txs.reduce((s,t)=>s+(t.principal||0),0).toLocaleString('en-ZM',{maximumFractionDigits:0}),
          int: txs.reduce((s,t)=>s+(t.interest||0),0).toLocaleString('en-ZM',{maximumFractionDigits:0}),
          pen: txs.reduce((s,t)=>s+(t.penalty||0),0).toLocaleString('en-ZM',{maximumFractionDigits:0}),
          paid:(parseFloat(d.summary?.total_paid)||0).toLocaleString('en-ZM',{maximumFractionDigits:0}),
        },
      };
    },

    toast(type,icon,msg){
      const id=Date.now();
      this.toasts.push({id,type,icon,msg});
      setTimeout(()=>{ this.toasts=this.toasts.filter(t=>t.id!==id); },4000);
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
