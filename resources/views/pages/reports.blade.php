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
              <option value="GN">Grace Nkonde — LN-20260009</option>
              <option value="BM">Bwalya Mwanza — LN-20260032</option>
              <option value="DP">Daniel Phiri — LN-20260041</option>
              <option value="CM">Charity Mutale — LN-20260018</option>
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
                <div class="rh-kpi"><div class="rh-kpi-label">Total Portfolio</div><div class="rh-kpi-value" style="color:var(--teal2)">K 2.84M</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">Active Loans</div><div class="rh-kpi-value" style="color:var(--green)">142</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">PAR 30</div><div class="rh-kpi-value" style="color:var(--amber)">9.8%</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">Collections Rate</div><div class="rh-kpi-value" style="color:var(--gold2)">94.2%</div></div>
              </div>
            </div>

            <!-- 6 KPI tiles -->
            <div class="stats-grid stagger" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
              <div class="stat-tile t-teal"><div class="st-label">Total Disbursed (YTD)</div><div class="st-value">K 4.21M</div><div class="st-sub"><span class="st-up">▲ 12%</span> vs 2025</div></div>
              <div class="stat-tile t-gold"><div class="st-label">Total Collected (YTD)</div><div class="st-value">K 1.87M</div><div class="st-sub"><span class="st-up">▲ 8%</span> vs 2025</div></div>
              <div class="stat-tile t-green"><div class="st-label">Loans Closed (YTD)</div><div class="st-value">38</div><div class="st-sub"><span class="st-up">▲ 5</span> vs last year</div></div>
              <div class="stat-tile t-red"><div class="st-label">Overdue Loans</div><div class="st-value">14</div><div class="st-sub"><span class="st-dn">▲ 2</span> this week</div></div>
              <div class="stat-tile t-amber"><div class="st-label">Penalties Outstanding</div><div class="st-value">K 48,920</div><div class="st-sub"><span class="st-dn">▲ K 3,200</span> this month</div></div>
              <div class="stat-tile t-purple"><div class="st-label">Average Loan Size</div><div class="st-value">K 29,600</div><div class="st-sub">Across 142 active loans</div></div>
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
                <div class="rec-count">2 products · 142 active loans</div>
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
                  <td>TOTALS</td><td class="r">142</td><td class="r">K 4,213,400</td>
                  <td class="r">K 2,840,200</td><td class="r">K 312,500</td>
                  <td class="r" style="color:var(--red)">14</td><td class="r">9.8%</td><td class="r">8.3 mo</td>
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
                  <span class="rh-tag">127 Receipts</span>
                </div>
              </div>
              <div class="rh-kpis">
                <div class="rh-kpi"><div class="rh-kpi-label">Collected (MTD)</div><div class="rh-kpi-value" style="color:var(--green)">K 312,500</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">Receipts Issued</div><div class="rh-kpi-value" style="color:var(--teal2)">127</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">Collection Rate</div><div class="rh-kpi-value" style="color:var(--gold2)">94.2%</div></div>
                <div class="rh-kpi"><div class="rh-kpi-label">Penalties Collected</div><div class="rh-kpi-value" style="color:var(--amber)">K 8,240</div></div>
              </div>
            </div>

            <!-- Officer performance -->
            <div class="data-panel" style="margin-bottom:20px">
              <div class="data-head">
                <div class="data-title" x-text="'Officer Performance — ' + thisMonthFull"></div>
                <div class="rec-count">3 officers</div>
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
                  <td colspan="2">TOTALS / AVERAGE</td><td class="r">142</td>
                  <td class="r">K 331,770</td><td class="r">K 312,500</td>
                  <td class="r">127</td><td class="r">94.2%</td>
                  <td class="r" style="color:var(--red)">14</td><td class="r">—</td>
                </tr></tfoot>
              </table>
            </div>

            <!-- Receipts -->
            <div class="data-panel">
              <div class="data-head">
                <div class="data-title" x-text="'Payment Receipts — ' + thisMonthFull"></div>
                <div class="rec-count">Showing 12 of 127</div>
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
                  <td colspan="5">TOTALS (127 receipts)</td>
                  <td class="r">K 233,420</td><td class="r">K 70,840</td>
                  <td class="r" style="color:var(--amber)">K 8,240</td>
                  <td class="r" style="font-size:15px">K 312,500</td>
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
                  <td>TOTALS</td><td class="r">14</td><td class="r">19</td>
                  <td class="r">K 184,680</td><td class="r">K 71,240</td>
                  <td class="r" style="color:var(--amber)">K 48,920</td>
                  <td class="r" style="font-size:15px">K 312,840</td><td class="r">100%</td>
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
              <span style="font-size:14px;font-weight:700" x-text="stmts[borrower]?.name"></span>
              <span class="mono" style="color:var(--teal2);font-size:12px" x-text="stmts[borrower]?.loanNum"></span>
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
                    <div style="font-family:'IBM Plex Mono',monospace;font-size:18px;font-weight:700;color:var(--gold2)">STM-20260042</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:4px" x-text="'Generated: ' + todayShort"></div>
                  </div>
                </div>
                <div class="stmt-doc-title">Loan Account Statement</div>
                <div class="stmt-period" x-text="'Period: ' + (stmts[borrower]?.period||'')"></div>
              </div>

              <div class="stmt-body">
                <!-- Borrower + Loan info -->
                <div class="stmt-info-grid">
                  <div>
                    <div class="stmt-sec-label">Borrower Information</div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Full Name:</span><span class="stmt-info-val" x-text="stmts[borrower]?.name"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Borrower No.:</span><span class="stmt-info-val" x-text="stmts[borrower]?.bNum"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">NRC:</span><span class="stmt-info-val" x-text="stmts[borrower]?.nrc"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Phone:</span><span class="stmt-info-val" x-text="stmts[borrower]?.phone"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Address:</span><span class="stmt-info-val" x-text="stmts[borrower]?.address"></span></div>
                  </div>
                  <div>
                    <div class="stmt-sec-label">Loan Details</div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Loan No.:</span><span class="stmt-info-val" x-text="stmts[borrower]?.loanNum"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Product:</span><span class="stmt-info-val" x-text="stmts[borrower]?.product"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Principal:</span><span class="stmt-info-val" x-text="stmts[borrower]?.principal"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Interest Rate:</span><span class="stmt-info-val" x-text="stmts[borrower]?.rate"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Term:</span><span class="stmt-info-val" x-text="stmts[borrower]?.term"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Disbursed:</span><span class="stmt-info-val" x-text="stmts[borrower]?.disbursed"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Maturity:</span><span class="stmt-info-val" x-text="stmts[borrower]?.maturity"></span></div>
                    <div class="stmt-info-row"><span class="stmt-info-key">Loan Officer:</span><span class="stmt-info-val" x-text="stmts[borrower]?.officer"></span></div>
                  </div>
                </div>

                <!-- Summary boxes -->
                <div class="stmt-sum-boxes">
                  <div class="sbox"><div class="sbox-label">Principal</div><div class="sbox-value" x-text="stmts[borrower]?.principal"></div></div>
                  <div class="sbox"><div class="sbox-label">Total Repayable</div><div class="sbox-value gold" x-text="stmts[borrower]?.totalRep"></div></div>
                  <div class="sbox"><div class="sbox-label">Total Paid</div><div class="sbox-value green" x-text="stmts[borrower]?.totalPaid"></div></div>
                  <div class="sbox"><div class="sbox-label">Balance Due</div><div class="sbox-value" :class="stmts[borrower]?.balClass" x-text="stmts[borrower]?.balance"></div></div>
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
                    <template x-for="(tx,i) in stmts[borrower]?.txs" :key="i">
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
                    <td class="r" x-text="'K '+stmts[borrower]?.tot?.pri"></td>
                    <td class="r" x-text="'K '+stmts[borrower]?.tot?.int"></td>
                    <td class="r" x-text="'K '+stmts[borrower]?.tot?.pen"></td>
                    <td class="r" x-text="'K '+stmts[borrower]?.tot?.paid"></td>
                    <td class="r" style="color:#0e7490" x-text="stmts[borrower]?.balance"></td>
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
    rt:'portfolio', period:'this_month', borrower:'GN', running:false, generated:false, toasts:[],
    thisMonthFull:'', thisMonthShort:'', lastMonthLabel:'', thisQLabel:'', lastQLabel:'',
    ytdLabel:'', lastYearLabel:'', todayShort:'', collectionPeriod:'', customFrom:'', customTo:'',

    chartBars:[
      {m:'Jul',d:64,c:52},{m:'Aug',d:80,c:68},{m:'Sep',d:73,c:60},
      {m:'Oct',d:94,c:76},{m:'Nov',d:86,c:73},{m:'Dec',d:108,c:90},
      {m:'Jan',d:100,c:86},{m:'Feb',d:88,c:82}
    ],

    productRows:[
      {name:'Vehicle-Backed Loan',loans:'98',disbursed:'2,940,200',outstanding:'1,980,400',collected:'218,750',overdue:10,par:10.2,parColor:'var(--amber)',avgTerm:8},
      {name:'Land-Backed Loan',   loans:'44',disbursed:'1,273,200',outstanding:'859,800', collected:'93,750', overdue:4, par:9.1, parColor:'var(--amber)',avgTerm:9},
    ],

    officers:[
      {name:'F. Mwala',ini:'FM',c1:'#0891b2',c2:'#06b6d4',role:'Senior Loan Officer',loans:52,due:121440,col:116580,rec:48,rate:96,ov:4,ok:true},
      {name:'C. Banda', ini:'CB',c1:'#7c3aed',c2:'#8b5cf6',role:'Loan Officer',       loans:48,due:110660,col:103020,rec:41,rate:93,ov:6,ok:false},
      {name:'N. Tembo', ini:'NT',c1:'#059669',c2:'#10b981',role:'Loan Officer',        loans:42,due:99670, col:92900, rec:38,rate:93,ov:4,ok:false},
    ],

    receipts:[
      {rcp:'RCP-00892',name:'Charity Mutale',  ini:'CM',c1:'#be185d',c2:'#ec4899',loan:'LN-20260018',type:'Full Instalment',meth:'Cash',          pri:7908,  int:1600, pen:0,   tot:9508,  date:'26 Feb 2026',off:'C. Banda'},
      {rcp:'RCP-00891',name:'Bwalya Mwanza',   ini:'BM',c1:'#0891b2',c2:'#06b6d4',loan:'LN-20260032',type:'Full Instalment',meth:'Mobile Money',  pri:10400, int:1980, pen:0,   tot:12380, date:'25 Feb 2026',off:'F. Mwala'},
      {rcp:'RCP-00890',name:'Noel Phiri',       ini:'NP',c1:'#059669',c2:'#10b981',loan:'LN-20260007',type:'Early Settlement',meth:'Bank Transfer',pri:32000, int:0,    pen:0,   tot:32000, date:'25 Feb 2026',off:'N. Tembo'},
      {rcp:'RCP-00889',name:'Daniel Phiri',     ini:'DP',c1:'#d97706',c2:'#f59e0b',loan:'LN-20260041',type:'Full Instalment',meth:'Cash',          pri:5600,  int:1120, pen:0,   tot:6720,  date:'24 Feb 2026',off:'F. Mwala'},
      {rcp:'RCP-00888',name:'Alice Mbewe',      ini:'AM',c1:'#7c3aed',c2:'#8b5cf6',loan:'LN-20260025',type:'Full Instalment',meth:'Cash',          pri:8200,  int:1560, pen:0,   tot:9760,  date:'24 Feb 2026',off:'C. Banda'},
      {rcp:'RCP-00887',name:'Joseph Chanda',    ini:'JC',c1:'#b45309',c2:'#f59e0b',loan:'LN-20260027',type:'Partial',        meth:'Mobile Money',  pri:1800,  int:480,  pen:0,   tot:2280,  date:'23 Feb 2026',off:'F. Mwala'},
      {rcp:'RCP-00886',name:'Florence Sakala',  ini:'FS',c1:'#166534',c2:'#22c55e',loan:'LN-20260013',type:'Full Instalment',meth:'Cash',          pri:9100,  int:1820, pen:0,   tot:10920, date:'22 Feb 2026',off:'N. Tembo'},
      {rcp:'RCP-00885',name:'Peter Tembo',      ini:'PT',c1:'#1e3a5f',c2:'#0ea5e9',loan:'LN-20260036',type:'Full Instalment',meth:'Bank Transfer', pri:14200, int:2840, pen:0,   tot:17040, date:'21 Feb 2026',off:'C. Banda'},
      {rcp:'RCP-00884',name:'Grace Nkonde',     ini:'GN',c1:'#dc2626',c2:'#ef4444',loan:'LN-20260009',type:'Partial',        meth:'Cash',          pri:2800,  int:700,  pen:0,   tot:3500,  date:'20 Feb 2026',off:'F. Mwala'},
      {rcp:'RCP-00883',name:'Mary Chisanga',    ini:'MC',c1:'#9f1239',c2:'#f43f5e',loan:'LN-20260020',type:'Full Instalment',meth:'Mobile Money',  pri:6600,  int:1320, pen:0,   tot:7920,  date:'19 Feb 2026',off:'N. Tembo'},
      {rcp:'RCP-00882',name:'John Mwamba',      ini:'JM',c1:'#0e7490',c2:'#06b6d4',loan:'LN-20260044',type:'Full Instalment',meth:'Cash',          pri:7400,  int:1480, pen:620, tot:9500,  date:'18 Feb 2026',off:'F. Mwala'},
      {rcp:'RCP-00881',name:'Sarah Musonda',    ini:'SM',c1:'#4f46e5',c2:'#6366f1',loan:'LN-20260015',type:'Full Instalment',meth:'Cash',          pri:11800, int:2360, pen:0,   tot:14160, date:'17 Feb 2026',off:'C. Banda'},
    ],

    parCards:[
      {lbl:'PAR 30 — Loans > 30 days overdue',val:'9.8%', clr:'var(--amber)',pct:49,sub:'K 278,300 of K 2.84M portfolio'},
      {lbl:'PAR 60 — Loans > 60 days overdue',val:'6.1%', clr:'var(--red)',  pct:31,sub:'K 173,200 of K 2.84M portfolio'},
      {lbl:'PAR 90 — Loans > 90 days overdue',val:'3.7%', clr:'#dc2626',    pct:19,sub:'K 105,100 of K 2.84M portfolio'},
      {lbl:'Default Rate — Write-off risk',   val:'1.2%', clr:'#7f1d1d',    pct:6, sub:'K 34,080 provisioned for loss'},
    ],

    agingRows:[
      {bkt:'Current (not yet due)',   loans:128,inst:0,  pri:0,      int:0,     pen:0,     tot:0,       pct:0,  clr:'var(--green)'},
      {bkt:'1 – 7 days overdue',      loans:4,  inst:4,  pri:24600,  int:9840,  pen:0,     tot:34440,   pct:11, clr:'var(--amber)'},
      {bkt:'8 – 30 days overdue',     loans:5,  inst:6,  pri:52400,  int:20960, pen:2170,  tot:75530,   pct:24, clr:'var(--amber)'},
      {bkt:'31 – 60 days overdue',    loans:3,  inst:4,  pri:44200,  int:17680, pen:11680, tot:73560,   pct:23, clr:'var(--red)'},
      {bkt:'61 – 90 days overdue',    loans:1,  inst:2,  pri:21480,  int:8592,  pen:9800,  tot:39872,   pct:13, clr:'#dc2626'},
      {bkt:'Over 90 days overdue',    loans:1,  inst:3,  pri:42000,  int:14168, pen:25270, tot:89438,   pct:29, clr:'#7f1d1d'},
    ],

    stmts:{
      GN:{
        name:'Grace Nkonde',bNum:'BRW-00009',nrc:'345621/10/1',phone:'0977 412 903',
        address:'Plot 8821, Matero, Lusaka',loanNum:'LN-20260009',
        product:'Vehicle-Backed Loan',principal:'K 50,000',rate:'28% flat tiered rate',
        term:'12 months',disbursed:'26 Aug 2025',maturity:'26 Aug 2026',
        officer:'F. Mwala',period:'26 Aug 2025 – 26 Feb 2026',
        totalRep:'K 58,560',totalPaid:'K 26,340',balance:'K 32,220',balClass:'red',
        txs:[
          {ref:'DISB-001',  date:'26 Aug 2025',desc:'Loan Disbursement',          pri:0,    int:0,    pen:0,  paid:0,    bal:58560, hl:true},
          {ref:'RCP-00712', date:'26 Sep 2025',desc:'Instalment #1 — Full',       pri:3414, int:1466, pen:0,  paid:4880, bal:53680},
          {ref:'PEN-001',   date:'27 Sep 2025',desc:'Penalty applied — Inst #1',  pri:0,    int:0,    pen:244,paid:0,    bal:53924},
          {ref:'RCP-00756', date:'26 Oct 2025',desc:'Instalment #2 — Full',       pri:3494, int:1386, pen:0,  paid:4880, bal:49044},
          {ref:'RCP-00801', date:'26 Nov 2025',desc:'Instalment #3 — Full',       pri:3574, int:1306, pen:0,  paid:4880, bal:44164},
          {ref:'RCP-00844', date:'26 Dec 2025',desc:'Instalment #4 — Full',       pri:3656, int:1224, pen:0,  paid:4880, bal:39284},
          {ref:'RCP-00860', date:'18 Jan 2026',desc:'Instalment #5 — Partial',    pri:2000, int:700,  pen:0,  paid:2700, bal:36584},
          {ref:'PEN-002',   date:'27 Jan 2026',desc:'Penalty applied — Inst #5',  pri:0,    int:0,    pen:244,paid:0,    bal:36828},
          {ref:'WVR-001',   date:'01 Feb 2026',desc:'Penalty Waiver — F. Mwala',  pri:0,    int:0,    pen:-244,paid:0,  bal:36584},
          {ref:'RCP-00884', date:'20 Feb 2026',desc:'Partial payment',            pri:2800, int:700,  pen:0,  paid:3500, bal:33084},
          {ref:'PEN-003',   date:'26 Feb 2026',desc:'Penalty applied — Inst #5-6',pri:0,    int:0,    pen:864,paid:0,   bal:33948},
        ],
        tot:{pri:'16,938',int:'6,782',pen:'864',paid:'25,720'},
      },
      BM:{
        name:'Bwalya Mwanza',bNum:'BRW-00032',nrc:'501823/67/1',phone:'0965 334 201',
        address:'Plot 2211, Kabulonga, Lusaka',loanNum:'LN-20260032',
        product:'Vehicle-Backed Loan',principal:'K 75,000',rate:'26% flat tiered rate',
        term:'12 months',disbursed:'01 Oct 2025',maturity:'01 Oct 2026',
        officer:'F. Mwala',period:'01 Oct 2025 – 26 Feb 2026',
        totalRep:'K 85,500',totalPaid:'K 61,900',balance:'K 23,600',balClass:'teal',
        txs:[
          {ref:'DISB-001',  date:'01 Oct 2025',desc:'Loan Disbursement',      pri:0,    int:0,   pen:0, paid:0,    bal:85500, hl:true},
          {ref:'RCP-00780', date:'01 Nov 2025',desc:'Instalment #1',          pri:5490, int:1625,pen:0, paid:7115, bal:78385},
          {ref:'RCP-00820', date:'01 Dec 2025',desc:'Instalment #2',          pri:5609, int:1506,pen:0, paid:7115, bal:71270},
          {ref:'RCP-00851', date:'01 Jan 2026',desc:'Instalment #3',          pri:5731, int:1384,pen:0, paid:7115, bal:64155},
          {ref:'RCP-00870', date:'01 Feb 2026',desc:'Instalment #4',          pri:5855, int:1260,pen:0, paid:7115, bal:57040},
          {ref:'RCP-00891', date:'25 Feb 2026',desc:'Instalment #5 — Early',  pri:10040,int:0,   pen:0, paid:12380,bal:44660},
        ],
        tot:{pri:'32,725',int:'5,775',pen:'0',paid:'40,840'},
      },
      DP:{
        name:'Daniel Phiri',bNum:'BRW-00041',nrc:'612934/45/1',phone:'0966 887 234',
        address:'Plot 5512, Chelston, Lusaka',loanNum:'LN-20260041',
        product:'Land-Backed Loan',principal:'K 40,000',rate:'30% flat tiered rate',
        term:'10 months',disbursed:'15 Nov 2025',maturity:'15 Sep 2026',
        officer:'C. Banda',period:'15 Nov 2025 – 26 Feb 2026',
        totalRep:'K 46,000',totalPaid:'K 20,160',balance:'K 25,840',balClass:'teal',
        txs:[
          {ref:'DISB-001',  date:'15 Nov 2025',desc:'Loan Disbursement',   pri:0,   int:0,   pen:0, paid:0,    bal:46000, hl:true},
          {ref:'RCP-00830', date:'15 Dec 2025',desc:'Instalment #1',       pri:3200,int:1000,pen:0, paid:4200, bal:41800},
          {ref:'RCP-00863', date:'15 Jan 2026',desc:'Instalment #2',       pri:3280,int:920, pen:0, paid:4200, bal:37600},
          {ref:'RCP-00889', date:'24 Feb 2026',desc:'Instalment #3',       pri:5600,int:1120,pen:0, paid:6720, bal:30880},
        ],
        tot:{pri:'12,080',int:'3,040',pen:'0',paid:'15,120'},
      },
      CM:{
        name:'Charity Mutale',bNum:'BRW-00018',nrc:'498002/33/1',phone:'0977 556 891',
        address:'Plot 991, Rhodespark, Lusaka',loanNum:'LN-20260018',
        product:'Vehicle-Backed Loan',principal:'K 55,000',rate:'27% flat tiered rate',
        term:'8 months',disbursed:'20 Sep 2025',maturity:'20 May 2026',
        officer:'C. Banda',period:'20 Sep 2025 – 26 Feb 2026',
        totalRep:'K 61,600',totalPaid:'K 47,540',balance:'K 14,060',balClass:'teal',
        txs:[
          {ref:'DISB-001',  date:'20 Sep 2025',desc:'Loan Disbursement',   pri:0,   int:0,   pen:0, paid:0,    bal:61600, hl:true},
          {ref:'RCP-00720', date:'20 Oct 2025',desc:'Instalment #1',       pri:5694,int:1238,pen:0, paid:6932, bal:54668},
          {ref:'RCP-00762', date:'20 Nov 2025',desc:'Instalment #2',       pri:5821,int:1111,pen:0, paid:6932, bal:47736},
          {ref:'RCP-00808', date:'20 Dec 2025',desc:'Instalment #3',       pri:5952,int:980, pen:0, paid:6932, bal:40804},
          {ref:'RCP-00849', date:'20 Jan 2026',desc:'Instalment #4',       pri:6084,int:848, pen:0, paid:6932, bal:33872},
          {ref:'RCP-00876', date:'20 Feb 2026',desc:'Instalment #5',       pri:6220,int:712, pen:0, paid:6932, bal:26940},
          {ref:'RCP-00892', date:'26 Feb 2026',desc:'Instalment #6',       pri:7908,int:1600,pen:0, paid:9508, bal:17432},
        ],
        tot:{pri:'37,679',int:'6,489',pen:'0',paid:'44,168'},
      },
    },

    loanBook: [],
    loanBookTotals: { loan_count:0, total_principal:0, avg_rate:0, avg_term:0, total_outstanding:0, total_monthly_pmt:0 },

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
      // Load real data from API in parallel
      await Promise.all([this.loadLoanBook(), this.loadRecentReceipts()]);
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

    runReport(){
      this.running=true;
      setTimeout(()=>{
        this.running=false;
        this.generated=true;
        this.toast('success','✓','Report generated successfully');
        this.$nextTick(()=>{
          const el=document.querySelector('.report-area');
          if(el) el.scrollIntoView({behavior:'smooth',block:'start'});
        });
      },900);
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
