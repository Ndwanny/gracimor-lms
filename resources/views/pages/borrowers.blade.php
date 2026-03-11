<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gracimor LMS — Borrowers</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=DM+Mono:wght@400;500&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js"></script>
  <style>
    :root {
      --navy:#0D1B2A; --navy-mid:#112236; --navy-card:#16293D; --navy-line:#1E3450; --navy-hover:#1A304A;
      --teal:#0B8FAC; --teal-lt:#13AECF; --amber:#F5A623; --amber-lt:#FFBE55;
      --green:#22C55E; --red:#EF4444; --purple:#818CF8;
      --slate:#94A3B8; --slate-lt:#CBD5E1; --white:#F0F6FF; --text:#E2EAF4;
    }
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'DM Sans',sans-serif;background:var(--navy);color:var(--text);min-height:100vh;display:flex}

    /* Sidebar */
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
    .nav-bdg{margin-left:auto;background:var(--red);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px}
    .nav-bdg.amber{background:var(--amber);color:#000}
    .sidebar-footer{margin-top:auto;padding:16px 12px;border-top:1px solid var(--navy-line)}
    .user-pill{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;background:var(--navy-line)}
    .u-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--teal),var(--amber));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0}
    .u-name{font-size:13px;font-weight:600;color:var(--white)}
    .u-role{font-size:10px;color:var(--teal);font-weight:500;text-transform:uppercase;letter-spacing:.08em}

    /* Main layout */
    .main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-height:100vh}
    .topbar{height:64px;background:var(--navy-mid);border-bottom:1px solid var(--navy-line);display:flex;align-items:center;padding:0 28px;gap:14px;position:sticky;top:0;z-index:40}
    .page-title{font-family:'Playfair Display',serif;font-size:20px;color:var(--white)}
    .breadcrumb{font-size:12px;color:var(--slate);line-height:1.4}
    .breadcrumb span{color:var(--teal-lt);cursor:pointer}
    .breadcrumb span:hover{text-decoration:underline}
    .tb-right{margin-left:auto;display:flex;align-items:center;gap:10px}

    /* Buttons */
    .btn-p{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--teal);color:#fff;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s;white-space:nowrap}
    .btn-p:hover{background:var(--teal-lt)}
    .btn-p.green{background:var(--green)}
    .btn-p.green:hover{background:#16a34a}
    .btn-g{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:transparent;color:var(--slate-lt);border:1px solid var(--navy-line);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:all .15s;white-space:nowrap}
    .btn-g:hover{background:var(--navy-line);color:var(--white)}
    .btn-sm{font-size:11px !important;padding:5px 10px !important}

    /* Content */
    .content{padding:28px;flex:1}

    /* Stats row */
    .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
    .m-stat{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:10px;padding:16px 18px;display:flex;align-items:center;gap:14px;transition:all .15s}
    .m-stat:hover{border-color:rgba(11,143,172,.35);transform:translateY(-1px)}
    .m-stat-ic{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0}
    .m-stat-val{font-family:'DM Mono',monospace;font-size:22px;font-weight:500;color:var(--white)}
    .m-stat-lbl{font-size:11px;color:var(--slate);margin-top:2px;text-transform:uppercase;letter-spacing:.08em;font-weight:600}

    /* Filter bar */
    .filter-bar{display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap}
    .srch-wrap{position:relative;flex:1;min-width:220px}
    .srch-wrap svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.4;pointer-events:none}
    .srch{width:100%;background:var(--navy-card);border:1px solid var(--navy-line);border-radius:9px;padding:10px 14px 10px 38px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .15s}
    .srch:focus{border-color:var(--teal)}
    .srch::placeholder{color:var(--slate)}
    .sel{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:9px;padding:10px 14px;color:var(--slate-lt);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;cursor:pointer}

    /* Table */
    .tbl-wrap{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:12px;overflow:hidden}
    .dtbl{width:100%;border-collapse:collapse}
    .dtbl th{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--slate);text-align:left;padding:12px 18px;border-bottom:1px solid var(--navy-line);background:var(--navy-mid);white-space:nowrap;cursor:pointer}
    .dtbl th:hover{color:var(--white)}
    .dtbl td{padding:13px 18px;font-size:13px;border-bottom:1px solid rgba(30,52,80,.5);vertical-align:middle}
    .dtbl tr:last-child td{border-bottom:none}
    .dtbl tbody tr{cursor:pointer;transition:background .1s}
    .dtbl tbody tr:hover td{background:var(--navy-hover)}

    /* Borrower cell */
    .b-av{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0}
    .b-name{font-size:13.5px;font-weight:600;color:var(--white)}
    .b-id{font-size:11px;color:var(--slate);font-family:'DM Mono',monospace}

    /* Badges */
    .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:99px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em}
    .badge::before{content:'';width:5px;height:5px;border-radius:50%;flex-shrink:0}
    .b-active{background:rgba(34,197,94,.12);color:var(--green);border:1px solid rgba(34,197,94,.25)}
    .b-active::before{background:var(--green)}
    .b-overdue{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.25)}
    .b-overdue::before{background:var(--red)}
    .b-pending{background:rgba(245,166,35,.12);color:var(--amber);border:1px solid rgba(245,166,35,.25)}
    .b-pending::before{background:var(--amber)}
    .b-closed{background:rgba(148,163,184,.12);color:var(--slate);border:1px solid rgba(148,163,184,.2)}
    .b-closed::before{background:var(--slate)}
    .b-blacklisted{background:rgba(239,68,68,.2);color:#FCA5A5;border:1px solid rgba(239,68,68,.4)}
    .b-blacklisted::before{background:#FCA5A5}
    .b-pledged{background:rgba(11,143,172,.12);color:var(--teal-lt);border:1px solid rgba(11,143,172,.25)}
    .b-pledged::before{background:var(--teal-lt)}

    /* Pagination */
    .pgn{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-top:1px solid var(--navy-line)}
    .pgn-info{font-size:12px;color:var(--slate)}
    .pgn-btns{display:flex;gap:4px}
    .pgn-btn{width:30px;height:30px;border-radius:6px;background:var(--navy-line);border:none;color:var(--slate-lt);font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s}
    .pgn-btn:hover{background:var(--navy-hover);color:var(--white)}
    .pgn-btn.active{background:var(--teal);color:#fff}

    /* ── Profile layout ── */
    .prof-layout{display:grid;grid-template-columns:300px 1fr;gap:20px}
    .card{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:14px;overflow:hidden}
    .prof-hero{background:linear-gradient(160deg,#0A3352,#0D1B2A 70%);padding:28px 20px;text-align:center;position:relative;overflow:hidden}
    .prof-hero::before{content:'';position:absolute;top:-30px;right:-30px;width:120px;height:120px;border-radius:50%;background:rgba(11,143,172,.07)}
    .prof-hero::after{content:'';position:absolute;bottom:-40px;left:-20px;width:100px;height:100px;border-radius:50%;background:rgba(245,166,35,.05)}
    .prof-av{width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700;color:#fff;margin:0 auto 12px;border:3px solid rgba(11,143,172,.4);position:relative;z-index:1}
    .prof-name{font-family:'Playfair Display',serif;font-size:18px;color:var(--white);margin-bottom:3px;position:relative;z-index:1}
    .prof-num{font-family:'DM Mono',monospace;font-size:11px;color:var(--teal-lt);margin-bottom:10px;position:relative;z-index:1}
    .prof-body{padding:18px}
    .info-row{display:flex;justify-content:space-between;align-items:flex-start;padding:9px 0;border-bottom:1px solid rgba(30,52,80,.5)}
    .info-row:last-child{border-bottom:none}
    .info-lbl{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--slate)}
    .info-val{font-size:12.5px;color:var(--white);text-align:right;max-width:160px;word-break:break-word}
    .info-val.mono{font-family:'DM Mono',monospace}

    /* Profile tabs */
    .ptabs{display:flex;gap:0;border-bottom:1px solid var(--navy-line);padding:0 18px}
    .ptab{padding:10px 14px;font-size:13px;font-weight:600;cursor:pointer;color:var(--slate);border-bottom:2px solid transparent;transition:all .15s;background:none;border-top:none;border-left:none;border-right:none;font-family:'DM Sans',sans-serif}
    .ptab:hover{color:var(--white)}
    .ptab.active{color:var(--teal-lt);border-bottom-color:var(--teal)}

    /* Loan card in profile */
    .loan-card{background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:10px;padding:14px 16px;margin-bottom:10px;cursor:pointer;transition:border-color .15s}
    .loan-card:hover{border-color:rgba(11,143,172,.4)}
    .loan-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px}
    .loan-num{font-family:'DM Mono',monospace;font-size:12px;color:var(--teal-lt)}
    .loan-amt{font-family:'DM Mono',monospace;font-size:19px;font-weight:500;color:var(--white)}
    .loan-meta{display:flex;gap:16px;flex-wrap:wrap}
    .loan-meta-it{font-size:11px;color:var(--slate)}
    .loan-meta-it span{color:var(--slate-lt);font-weight:600}
    .prog-bar{height:5px;background:var(--navy-line);border-radius:99px;overflow:hidden;margin-top:10px}
    .prog-fill{height:100%;border-radius:99px}

    /* Payment row */
    .pay-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid rgba(30,52,80,.5)}
    .pay-row:last-child{border-bottom:none}
    .pay-ic{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0}
    .pay-receipt{font-family:'DM Mono',monospace;font-size:12px;color:var(--teal-lt)}
    .pay-date{font-size:11px;color:var(--slate)}
    .pay-amt{margin-left:auto;font-family:'DM Mono',monospace;font-size:13px;font-weight:500;color:var(--green)}
    .pay-bal{font-size:11px;color:var(--slate);text-align:right}

    /* Document row */
    .doc-item{display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:9px;margin-bottom:8px;cursor:pointer;transition:border-color .15s}
    .doc-item:hover{border-color:rgba(11,143,172,.35)}
    .doc-ic{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
    .doc-name{font-size:13px;font-weight:600;color:var(--white)}
    .doc-meta{font-size:11px;color:var(--slate)}

    /* Collateral card in profile */
    .coll-item{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(30,52,80,.5)}
    .coll-item:last-child{border-bottom:none}
    .coll-ic{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}

    /* ── Register form ── */
    .steps{display:flex;align-items:center;margin-bottom:24px}
    .step{display:flex;align-items:center;gap:8px;flex-shrink:0}
    .step-n{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;transition:all .2s;flex-shrink:0}
    .step-n.done{background:var(--teal);color:#fff}
    .step-n.cur{background:var(--amber);color:#000}
    .step-n.pend{background:var(--navy-line);color:var(--slate)}
    .step-lbl{font-size:12px;font-weight:600;white-space:nowrap}
    .step-lbl.done{color:var(--teal-lt)} .step-lbl.cur{color:var(--amber)} .step-lbl.pend{color:var(--slate)}
    .step-line{flex:1;height:1px;background:var(--navy-line);margin:0 10px}
    .step-line.done{background:var(--teal)}

    .fsec{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:12px;overflow:hidden;margin-bottom:20px}
    .fsec-hd{padding:14px 20px;background:var(--navy-mid);border-bottom:1px solid var(--navy-line);display:flex;align-items:center;gap:10px}
    .fsec-ic{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:13px}
    .fsec-title{font-size:13px;font-weight:700;color:var(--white)}
    .fsec-body{padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .fsec-body.full{grid-template-columns:1fr}

    .field{display:flex;flex-direction:column;gap:6px}
    .field.span2{grid-column:span 2}
    label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--slate)}
    .req{color:var(--red);margin-left:2px}
    .finput,.fsel,.ftxtarea{background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:8px;padding:10px 14px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .15s;width:100%}
    .finput:focus,.fsel:focus,.ftxtarea:focus{border-color:var(--teal)}
    .finput::placeholder,.ftxtarea::placeholder{color:var(--slate)}
    .ftxtarea{resize:vertical;min-height:80px}
    .fhint{font-size:11px;color:var(--slate)}

    .upload-zone{border:2px dashed var(--navy-line);border-radius:10px;padding:20px;text-align:center;cursor:pointer;transition:all .15s}
    .upload-zone:hover{border-color:var(--teal);background:rgba(11,143,172,.04)}

    .coll-types{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
    .coll-type{border:2px solid var(--navy-line);border-radius:10px;padding:16px;cursor:pointer;transition:all .15s;text-align:center}
    .coll-type:hover{border-color:rgba(11,143,172,.4)}
    .coll-type.sel{border-color:var(--teal);background:rgba(11,143,172,.08)}
    .coll-type-ic{font-size:26px;margin-bottom:8px}
    .coll-type-lbl{font-size:13px;font-weight:700;color:var(--white)}
    .coll-type-sub{font-size:11px;color:var(--slate);margin-top:2px}

    .form-actions{display:flex;gap:10px;justify-content:flex-end;padding:16px 20px;background:var(--navy-mid);border-top:1px solid var(--navy-line)}

    /* Review section box */
    .rev-box{background:var(--navy-mid);border-radius:10px;padding:14px;margin-bottom:12px}
    .rev-box-title{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--slate);margin-bottom:10px}

    /* Mono, color utils */
    .mono{font-family:'DM Mono',monospace}
    .tc{color:var(--teal-lt)} .ta{color:var(--amber)} .tg{color:var(--green)} .tr{color:var(--red)} .ts{color:var(--slate)} .tw{color:var(--white)}
    .sm{font-size:12px} .xs{font-size:11px} .f6{font-weight:600} .f7{font-weight:700}
    .flex{display:flex} .aic{align-items:center} .jb{justify-content:space-between}
    .g8{gap:8px} .g12{gap:12px}
    .mt4{margin-top:4px} .mt8{margin-top:8px} .mt12{margin-top:12px} .mt16{margin-top:16px}
    .mb8{margin-bottom:8px} .mb12{margin-bottom:12px} .mb16{margin-bottom:16px}

    /* Anim */
    @keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
    .anim{animation:fadeUp .35s ease both}
    .d1{animation-delay:.05s} .d2{animation-delay:.10s} .d3{animation-delay:.15s} .d4{animation-delay:.20s}

    ::-webkit-scrollbar{width:5px} ::-webkit-scrollbar-track{background:transparent} ::-webkit-scrollbar-thumb{background:var(--navy-line);border-radius:99px}

    .toast-msg{position:fixed;bottom:24px;right:24px;background:var(--green);color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:999;box-shadow:0 8px 24px rgba(0,0,0,.4);animation:fadeUp .3s ease}

    .coll-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;background:var(--navy-line);color:var(--slate-lt)}
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

  /* ══════════════════════════════════════════════════════════════════
     BORROWER PROFILE VIEW — Mobile responsive
  ══════════════════════════════════════════════════════════════════ */

  /* 1. Two-column profile layout → single column */
  .prof-layout {
    grid-template-columns: 1fr !important;
    gap: 12px !important;
  }

  /* 2. Profile tabs: horizontal scroll (4 tabs overflow narrow screens) */
  .ptabs {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
    flex-wrap: nowrap !important;
    padding: 0 12px !important;
    scrollbar-width: none;
  }
  .ptabs::-webkit-scrollbar { display: none; }
  .ptab {
    white-space: nowrap !important;
    flex-shrink: 0 !important;
    padding: 10px 12px !important;
    font-size: 12px !important;
  }

  /* 3. Profile hero: compact on mobile */
  .prof-hero { padding: 20px 16px !important; }
  .prof-av   { width: 60px !important; height: 60px !important; font-size: 22px !important; margin-bottom: 10px !important; }
  .prof-name { font-size: 16px !important; }
  .prof-body { padding: 14px !important; }

  /* 4. Topbar profile actions: wrap tightly */
  .tb-right .flex.aic { flex-wrap: wrap !important; gap: 6px !important; }
  .tb-right .btn-g,
  .tb-right .btn-p { font-size: 12px !important; padding: 7px 11px !important; }

  /* 5. Loan summary 3-col stat strip → 3-col still but smaller, or 1-col on very small */
  .loan-summary-strip {
    grid-template-columns: 1fr 1fr 1fr !important;
    gap: 8px !important;
  }
  .loan-summary-strip > div { padding: 10px 8px !important; }
  .loan-summary-strip .mono { font-size: 14px !important; }

  /* 6. Document item: stack buttons on narrow screens */
  .doc-item {
    flex-wrap: wrap !important;
    gap: 8px !important;
  }
  .doc-item > div:last-child {
    width: 100% !important;
    margin-left: 0 !important;
    display: flex !important;
    gap: 8px !important;
  }
  .doc-item > div:last-child .btn-g,
  .doc-item > div:last-child .btn-p { flex: 1 !important; justify-content: center !important; }

  /* 7. Payment row: reduce gap and font on mobile */
  .pay-row { gap: 8px !important; }
  .pay-amt { font-size: 12px !important; }
  .pay-receipt { font-size: 11px !important; }

  /* 8. Collateral item: ensure it doesn't overflow */
  .coll-item { gap: 10px !important; }
  .coll-item .badge { flex-shrink: 0 !important; }

  /* 9. Loan card in profile */
  .loan-card { padding: 12px !important; }
  .loan-amt  { font-size: 16px !important; }
  .loan-meta { gap: 8px !important; }

  /* 10. Section headers with buttons: prevent overflow */
  .fsec-hd { flex-wrap: wrap !important; gap: 8px !important; padding: 12px 14px !important; }

  /* 11. Info row: give value more room on mobile */
  .info-val { max-width: 55% !important; font-size: 12px !important; }
  .info-lbl { font-size: 9px !important; }

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

  /* Profile view on very small phones */
  .loan-summary-strip {
    grid-template-columns: 1fr !important;
    gap: 6px !important;
  }
  .prof-name { font-size: 15px !important; }
  .loan-amt  { font-size: 14px !important; }
  .doc-item  { padding: 8px 10px !important; }

  /* Payment badge wraps to new line */
  .pay-row { flex-wrap: wrap !important; }
  .pay-row .badge { margin-left: 44px !important; } /* align under receipt text */
}
</style>












</head>
<body x-data="app()">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="logo-wrap">
      <div class="logo-mark">Gracimor</div>
      <div class="logo-sub">Loans Management</div>
    </div>
    <div class="nav-sect">
      <div class="nav-lbl">Main</div>
      <a class="nav-item" href="/dashboard">
        <svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Dashboard
      </a>
      <a class="nav-item active" href="/borrowers">
        <svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Borrowers
      </a>
      <a class="nav-item" href="/loans">
        <svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Loans <span class="nav-bdg" x-show="(stats.loans?.pending ?? 0) > 0" x-text="stats.loans?.pending ?? 0">0</span>
      </a>
      <a class="nav-item" href="/payments">
        <svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        Payments
      </a>
      <a class="nav-item" href="/collateral">
        <svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Collateral
      </a>
    </div>
    <div class="nav-sect">
      <div class="nav-lbl">Schedule</div>
      <a class="nav-item" href="/calendar">
        <svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Calendar <span class="nav-bdg amber" x-show="(stats.due_today?.count ?? 0) > 0" x-text="stats.due_today?.count ?? 0">0</span>
      </a>
      <a class="nav-item" href="/overdue">
        <svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Overdue <span class="nav-bdg" x-show="(stats.overdue?.total_loans ?? 0) > 0" x-text="stats.overdue?.total_loans ?? 0">0</span>
      </a>
    </div>
    <div class="nav-sect">
      <div class="nav-lbl">Reports</div>
      <a class="nav-item" href="/reports"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>Reports</a>
      <a class="nav-item" href="/settings"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 19.07a10 10 0 0 1 0-14.14"/></svg>Settings</a>
    </div>
    <div class="sidebar-footer">
      <div class="user-pill">
        <div class="u-av">CK</div>
        <div><div class="u-name">Charles K.</div><div class="u-role">CEO</div></div>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <!-- Topbar -->
    <header class="topbar">
      <div>
        <div class="page-title" x-show="view==='list'">Borrowers</div>
        <div x-show="view==='profile'">
          <div class="breadcrumb">
            <span @click="view='list'">Borrowers</span>
            &nbsp;/&nbsp;
            <span style="color:var(--white)" x-text="sel?.name"></span>
          </div>
        </div>
        <div class="page-title" x-show="view==='reg'">Register New Borrower</div>
      </div>
      <div class="tb-right">
        <!-- List actions -->
        <template x-if="view==='list'">
          <div class="flex aic g8">
            <button class="btn-g" @click="showToast('Exporting borrowers list to CSV…')">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Export
            </button>
            <button class="btn-p" @click="view='reg';step=1">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Register Borrower
            </button>
          </div>
        </template>
        <!-- Profile actions -->
        <template x-if="view==='profile'">
          <div class="flex aic g8">
            <button class="btn-g" @click="view='list'">← Back</button>
            <button class="btn-g" @click="openEdit()">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Edit
            </button>
            <button class="btn-p" @click="window.location.href='/loans'">+ New Loan</button>
          </div>
        </template>
        <!-- Register actions -->
        <template x-if="view==='reg'">
          <button class="btn-g" @click="view='list'">✕ Cancel</button>
        </template>
      </div>
    </header>

    <div class="content">

      <!-- ═══════════════ LIST VIEW ═══════════════ -->
      <div x-show="view==='list'" x-transition>

        <!-- Stats -->
        <div class="stats-row anim">
          <div class="m-stat">
            <div class="m-stat-ic" style="background:rgba(11,143,172,.15)">👥</div>
            <div><div class="m-stat-val" x-text="stats.borrowers.total"></div><div class="m-stat-lbl">Total Borrowers</div></div>
          </div>
          <div class="m-stat">
            <div class="m-stat-ic" style="background:rgba(34,197,94,.15)">✅</div>
            <div><div class="m-stat-val" x-text="stats.borrowers.with_active"></div><div class="m-stat-lbl">Active</div></div>
          </div>
          <div class="m-stat">
            <div class="m-stat-ic" style="background:rgba(239,68,68,.15)">⚠️</div>
            <div><div class="m-stat-val" x-text="stats.borrowers.with_overdue"></div><div class="m-stat-lbl">Overdue</div></div>
          </div>
          <div class="m-stat">
            <div class="m-stat-ic" style="background:rgba(148,163,184,.15)">📁</div>
            <div><div class="m-stat-val" x-text="stats.borrowers.no_active_loan"></div><div class="m-stat-lbl">No Active Loan</div></div>
          </div>
        </div>

        <!-- Filters -->
        <div class="filter-bar anim d1">
          <div class="srch-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input class="srch" type="text" placeholder="Search name, NRC, phone, loan number…" x-model="q" @input="doFilter()">
          </div>
          <select class="sel" x-model="fStatus" @change="doFilter()">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="overdue">Overdue</option>
            <option value="closed">Closed</option>
          </select>
          <select class="sel" x-model="fColl" @change="doFilter()">
            <option value="">All Collateral</option>
            <option value="vehicle">Vehicle</option>
            <option value="land">Land</option>
          </select>
          <select class="sel">
            <option>All Officers</option>
            <option>Mary Phiri</option>
            <option>John Banda</option>
          </select>
        </div>

        <!-- Table -->
        <div class="tbl-wrap anim d2">
          <div x-show="loading" style="text-align:center;padding:32px;color:var(--slate);font-size:13px">Loading borrowers…</div>
          <table class="dtbl" x-show="!loading">
            <thead>
              <tr>
                <th>Borrower</th>
                <th>NRC / ID ↕</th>
                <th>Phone</th>
                <th>Active Loan ↕</th>
                <th>Collateral</th>
                <th>Outstanding ↕</th>
                <th>Status</th>
                <th>Registered</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <template x-for="b in pagedRows" :key="b.id">
                <tr @click="open(b)">
                  <td>
                    <div class="flex aic g12">
                      <div class="b-av" :style="`background:linear-gradient(135deg,${b.c1},${b.c2})`" x-text="b.ini"></div>
                      <div>
                        <div class="b-name" x-text="b.name"></div>
                        <div class="b-id" x-text="b.bnum"></div>
                      </div>
                    </div>
                  </td>
                  <td class="mono sm ts" x-text="b.nrc"></td>
                  <td class="mono sm" x-text="b.phone"></td>
                  <td>
                    <span x-show="b.loan" class="mono sm tc" x-text="b.loan"></span>
                    <span x-show="!b.loan" class="xs ts">—</span>
                  </td>
                  <td>
                    <span x-show="b.coll==='vehicle'" class="coll-badge">🚗 Vehicle</span>
                    <span x-show="b.coll==='land'" class="coll-badge">🏞️ Land</span>
                    <span x-show="!b.coll" class="xs ts">—</span>
                  </td>
                  <td>
                    <span x-show="b.owed" class="mono f6 tw sm" x-text="b.owed"></span>
                    <span x-show="!b.owed" class="xs ts">—</span>
                  </td>
                  <td><span class="badge" :class="`b-${b.status}`" x-text="b.status"></span></td>
                  <td class="xs ts" x-text="b.reg"></td>
                  <td><button class="btn-g btn-sm" @click.stop="open(b)">View →</button></td>
                </tr>
              </template>
            </tbody>
          </table>
          <div class="pgn">
            <div class="pgn-info">Showing <strong x-text="Math.min(page*perPage,rows.length)"></strong> of <strong x-text="rows.length"></strong> borrowers</div>
            <div class="pgn-btns">
              <button class="pgn-btn" @click="page=Math.max(1,page-1)" :disabled="page===1">‹</button>
              <template x-for="p in totalPages" :key="p">
                <button class="pgn-btn" :class="page===p&&'active'" @click="page=p" x-text="p"></button>
              </template>
              <button class="pgn-btn" @click="page=Math.min(totalPages,page+1)" :disabled="page===totalPages">›</button>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════ PROFILE VIEW ═══════════════ -->
      <div x-show="view==='profile'" x-transition>
        <template x-if="sel">
          <div class="prof-layout">

            <!-- Left panel -->
            <div>
              <!-- Identity card -->
              <div class="card anim">
                <div class="prof-hero">
                  <div class="prof-av" :style="`background:linear-gradient(135deg,${sel.c1},${sel.c2})`" x-text="sel.ini"></div>
                  <div class="prof-name" x-text="sel.name"></div>
                  <div class="prof-num" x-text="sel.bnum"></div>
                  <span class="badge" :class="`b-${sel.status}`" x-text="sel.status"></span>
                </div>
                <div class="prof-body">
                  <div class="info-row"><div class="info-lbl">NRC / ID</div><div class="info-val mono" x-text="sel.nrc"></div></div>
                  <div class="info-row"><div class="info-lbl">Phone</div><div class="info-val" x-text="sel.phone"></div></div>
                  <div class="info-row"><div class="info-lbl">Email</div><div class="info-val" x-text="sel.email||'—'"></div></div>
                  <div class="info-row"><div class="info-lbl">Employment</div><div class="info-val" x-text="sel.emp"></div></div>
                  <div class="info-row"><div class="info-lbl">Monthly Income</div><div class="info-val mono" x-text="sel.income"></div></div>
                  <div class="info-row"><div class="info-lbl">Address</div><div class="info-val" x-text="sel.addr"></div></div>
                  <div class="info-row"><div class="info-lbl">Registered</div><div class="info-val" x-text="sel.reg"></div></div>
                  <div class="info-row"><div class="info-lbl">Officer</div><div class="info-val" x-text="sel.officer"></div></div>
                </div>
              </div>

              <!-- Collateral -->
              <div class="card mt16 anim d2">
                <div class="fsec-hd" style="border-bottom:1px solid var(--navy-line)">
                  <div class="fsec-ic" style="background:rgba(11,143,172,.15)">🏛️</div>
                  <div class="fsec-title">Collateral Assets</div>
                  <button class="btn-p btn-sm" style="margin-left:auto" @click="openAddColl()">+ Add Collateral</button>
                </div>
                <div style="padding:14px">
                  <div x-show="selLoading" style="text-align:center;padding:16px;color:var(--slate);font-size:12px">Loading…</div>
                  <template x-if="!selLoading && selDetail">
                    <div>
                      <template x-for="ca in (selDetail.collateral_assets||[])" :key="ca.id">
                        <div class="coll-item">
                          <div class="coll-ic" :style="`background:${ca.asset_type==='vehicle'?'rgba(11,143,172,.15)':'rgba(34,197,94,.12)'}`"
                               x-text="ca.asset_type==='vehicle'?'🚗':'🏞️'"></div>
                          <div style="flex:1">
                            <div class="f6 tw sm" x-text="ca.asset_type==='vehicle'
                              ? (ca.vehicle_make||'') + ' ' + (ca.vehicle_model||'') + (ca.vehicle_registration ? ' — ' + ca.vehicle_registration : '')
                              : 'Plot ' + (ca.plot_number||'—') + (ca.land_address ? ', ' + ca.land_address : '')">
                            </div>
                            <div class="xs ts mt4" x-text="(ca.vehicle_year ? ca.vehicle_year + ' · ' : '') + 'Value: K ' + Number(ca.estimated_value||0).toLocaleString()"></div>
                          </div>
                          <span class="badge" :class="ca.status==='pledged'?'b-pledged':'b-pending'" x-text="ca.status||'available'"></span>
                        </div>
                      </template>
                      <div x-show="(selDetail.collateral_assets||[]).length===0"
                           style="text-align:center;padding:20px;color:var(--slate);font-size:12px">No collateral assets on file.</div>
                    </div>
                  </template>
                  <div x-show="!selLoading && !selDetail" style="text-align:center;padding:20px;color:var(--slate);font-size:12px">No collateral assets on file.</div>
                </div>
              </div>
            </div>

            <!-- Right panel -->
            <div class="anim d2">
              <div class="card">
                <div class="ptabs">
                  <button class="ptab" :class="{active:ptab==='loans'}" @click="ptab='loans'" x-text="`Loans (${selDetail?.loans?.length ?? 0})`">Loans</button>
                  <button class="ptab" :class="{active:ptab==='payments'}" @click="ptab='payments'" x-text="`Payments (${(selDetail?.loans||[]).reduce((s,l)=>s+(l.payments?.length||0),0)})`">Payments</button>
                  <button class="ptab" :class="{active:ptab==='docs'}" @click="ptab='docs'" x-text="`Documents (${selDetail?.documents?.length ?? 0})`">Documents</button>
                  <button class="ptab" :class="{active:ptab==='guar'}" @click="ptab='guar'" x-text="`Guarantors (${selDetail?.guarantors?.length ?? 0})`">Guarantors</button>
                </div>

                <div style="padding:18px">

                  <!-- Loans tab -->
                  <div x-show="ptab==='loans'">
                    <div x-show="selLoading" style="text-align:center;padding:32px;color:var(--slate);font-size:13px">Loading…</div>
                    <template x-if="!selLoading && selDetail">
                      <div>
                        <div class="loan-summary-strip" style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px">
                          <div style="background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:9px;padding:12px;text-align:center">
                            <div class="mono f6 tw" style="font-size:18px" x-text="'K ' + (selDetail.loans||[]).reduce((s,l)=>s+Number(l.loan_balance?.principal_disbursed||l.principal_amount||0),0).toLocaleString()"></div>
                            <div class="xs ts mt4">Total Disbursed</div>
                          </div>
                          <div style="background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:9px;padding:12px;text-align:center">
                            <div class="mono f6 tg" style="font-size:18px" x-text="'K ' + (selDetail.loans||[]).reduce((s,l)=>s+Math.max(0,Number(l.total_repayable||0)-Number(l.loan_balance?.total_outstanding||0)),0).toLocaleString()"></div>
                            <div class="xs ts mt4">Total Repaid</div>
                          </div>
                          <div style="background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:9px;padding:12px;text-align:center">
                            <div class="mono f6 ta" style="font-size:18px" x-text="'K ' + (selDetail.loans||[]).reduce((s,l)=>s+Number(l.loan_balance?.total_outstanding||0),0).toLocaleString()"></div>
                            <div class="xs ts mt4">Outstanding</div>
                          </div>
                        </div>
                        <template x-for="loan in (selDetail.loans||[])" :key="loan.id">
                          <div class="loan-card" :style="loan.status==='closed'?'opacity:.7':''">
                            <div class="loan-top">
                              <div>
                                <div class="loan-num" x-text="loan.loan_number"></div>
                                <div class="xs ts mt4" x-text="(loan.loan_product?.name||'') + (loan.status ? ' · ' + loan.status : '')"></div>
                              </div>
                              <div style="text-align:right">
                                <div class="loan-amt" x-text="'K ' + Number(loan.principal_amount||0).toLocaleString()"></div>
                                <span class="badge mt4" :class="'b-' + loan.status" x-text="loan.status"></span>
                              </div>
                            </div>
                            <div class="loan-meta">
                              <div class="loan-meta-it" x-show="loan.disbursement_date">Disbursed: <span x-text="loan.disbursement_date ? new Date(loan.disbursement_date).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : '—'"></span></div>
                              <div class="loan-meta-it" x-show="loan.loan_balance?.instalments_total">Schedule: <span x-text="(loan.loan_balance?.instalments_paid||0) + ' of ' + (loan.loan_balance?.instalments_total||0) + ' paid'"></span></div>
                              <div class="loan-meta-it" x-show="loan.loan_balance?.total_outstanding > 0">Outstanding: <span x-text="'K ' + Number(loan.loan_balance?.total_outstanding||0).toLocaleString()"></span></div>
                            </div>
                            <div class="prog-bar" x-show="loan.loan_balance?.instalments_total">
                              <div class="prog-fill" :style="`width:${Math.min(100,Math.round(((loan.loan_balance?.instalments_paid||0)/(loan.loan_balance?.instalments_total||1))*100))}%;background:${loan.status==='closed'?'var(--slate)':'var(--teal)'}`"></div>
                            </div>
                            <div class="flex jb aic mt8">
                              <div class="xs ts" x-text="'K ' + Number(loan.loan_balance?.total_outstanding||0).toLocaleString() + ' remaining'"></div>
                              <button class="btn-g btn-sm" @click="window.location.href='/loans'">View Schedule →</button>
                            </div>
                          </div>
                        </template>
                        <div x-show="!selDetail.loans || selDetail.loans.length===0" style="text-align:center;padding:32px;color:var(--slate);font-size:13px">No loans on record for this borrower.</div>
                      </div>
                    </template>
                  </div>

                  <!-- Payments tab -->
                  <div x-show="ptab==='payments'">
                    <div x-show="selLoading" style="text-align:center;padding:32px;color:var(--slate);font-size:13px">Loading…</div>
                    <template x-if="!selLoading && selDetail">
                      <div>
                        <div class="flex jb aic mb16">
                          <div class="sm ts" x-text="(selDetail.loans||[]).reduce((s,l)=>s+(l.payments?.length||0),0) + ' payment records'"></div>
                        </div>
                        <template x-for="loan in (selDetail.loans||[])">
                          <template x-for="py in (loan.payments||[])" :key="py.id">
                            <div class="pay-row">
                              <div class="pay-ic" style="background:rgba(34,197,94,.12)">💵</div>
                              <div>
                                <div class="pay-receipt" x-text="py.receipt_number||'—'"></div>
                                <div class="pay-date" x-text="(py.payment_date ? new Date(py.payment_date).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : '—') + ' · ' + (py.payment_method||'—')"></div>
                              </div>
                              <div style="margin-left:auto;text-align:right">
                                <div class="pay-amt" x-text="'+K ' + Number(py.amount_received||0).toLocaleString()"></div>
                                <div class="pay-bal" x-text="'Bal: K ' + Number(py.balance_after||0).toLocaleString()"></div>
                              </div>
                              <span class="badge xs b-active" x-text="py.payment_type||'Payment'" style="margin-left:10px"></span>
                            </div>
                          </template>
                        </template>
                        <div x-show="(selDetail.loans||[]).reduce((s,l)=>s+(l.payments?.length||0),0)===0" style="text-align:center;padding:32px;color:var(--slate);font-size:13px">No payment records yet.</div>
                      </div>
                    </template>
                  </div>

                  <!-- Documents tab -->
                  <div x-show="ptab==='docs'">
                    <div x-show="selLoading" style="text-align:center;padding:32px;color:var(--slate);font-size:13px">Loading…</div>
                    <template x-if="!selLoading && selDetail">
                      <div>
                        <div class="flex jb aic mb16">
                          <div class="sm ts" x-text="(selDetail.documents?.length||0) + ' documents attached'"></div>
                          <button class="btn-p" style="font-size:12px;padding:7px 13px" @click="openUploadModal()">+ Upload</button>
                        </div>
                        <template x-for="d in (selDetail.documents||[])" :key="d.id">
                          <div class="doc-item">
                            <div class="doc-ic" :style="`background:${d.document_type==='national_id'?'rgba(11,143,172,.15)':d.document_type==='payslip'||d.document_type==='bank_statement'?'rgba(129,140,248,.15)':d.document_type==='vehicle_logbook'||d.document_type==='vehicle_photos'?'rgba(245,166,35,.15)':'rgba(34,197,94,.15)'}`"
                                 x-text="d.document_type==='national_id'?'🪪':d.document_type==='payslip'?'📄':d.document_type==='vehicle_logbook'||d.document_type==='vehicle_photos'?'🚗':d.document_type==='land_title_deed'?'🏞️':'📎'"></div>
                            <div>
                              <div class="doc-name" x-text="d.display_name || d.file_name || d.document_type"></div>
                              <div class="doc-meta" x-text="(d.created_at ? 'Uploaded ' + new Date(d.created_at).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : '') + (d.mime_type ? ' · ' + (d.mime_type.split('/')[1]||'').toUpperCase() : '') + (d.file_size_bytes ? ' · ' + Math.round(d.file_size_bytes/1024) + ' KB' : '')"></div>
                            </div>
                            <div style="margin-left:auto;display:flex;gap:6px;flex-shrink:0">
                              <button class="btn-g btn-sm" @click.stop="viewDoc(selDetail.id, d.id)" title="Open in browser">👁 View</button>
                              <button class="btn-p btn-sm" @click.stop="downloadDoc(selDetail.id, d.id, d.file_name||d.display_name)" title="Download">⬇ Save</button>
                            </div>
                          </div>
                        </template>
                        <div x-show="!selDetail.documents || selDetail.documents.length===0" style="text-align:center;padding:32px;color:var(--slate);font-size:13px">No documents on file.</div>
                      </div>
                    </template>
                  </div>

                  <!-- Guarantors tab -->
                  <div x-show="ptab==='guar'">
                    <div x-show="selLoading" style="text-align:center;padding:32px;color:var(--slate);font-size:13px">Loading…</div>
                    <template x-if="!selLoading && selDetail">
                      <div>
                        <div class="flex jb aic mb16">
                          <div class="sm ts" x-text="(selDetail.guarantors?.length||0) + ' guarantors on file'"></div>
                          <button class="btn-g btn-sm" @click="showToast('Guarantor form coming soon')">+ Add Guarantor</button>
                        </div>
                        <template x-for="g in (selDetail.guarantors||[])" :key="g.id">
                          <div class="loan-card">
                            <div class="flex aic g12">
                              <div class="b-av" style="background:linear-gradient(135deg,#6366F1,#4338CA);width:44px;height:44px;font-size:15px"
                                   x-text="(g.full_name||'?').split(' ').map(p=>p[0]).join('').toUpperCase().slice(0,2)"></div>
                              <div>
                                <div class="f6 tw" x-text="g.full_name"></div>
                                <div class="b-id mt4" x-text="'NRC: ' + (g.nrc_number||'—')"></div>
                              </div>
                              <span class="badge b-active" style="margin-left:auto" x-text="g.status||'active'"></span>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px;padding-top:12px;border-top:1px solid rgba(30,52,80,.5)">
                              <div><div class="xs ts">Relationship</div><div class="sm tw mt4" x-text="g.relationship||'—'"></div></div>
                              <div><div class="xs ts">Phone</div><div class="sm mono mt4" x-text="g.phone||'—'"></div></div>
                              <div><div class="xs ts">Loan</div><div class="sm mono mt4 tc" x-text="g.loan?.loan_number||'—'"></div></div>
                            </div>
                          </div>
                        </template>
                        <div x-show="!selDetail.guarantors || selDetail.guarantors.length===0" style="text-align:center;padding:32px;color:var(--slate);font-size:13px">No guarantors on file.</div>
                      </div>
                    </template>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- ═══════════════ REGISTER VIEW ═══════════════ -->
      <div x-show="view==='reg'" x-transition>

        <!-- Steps -->
        <div class="steps anim">
          <div class="step">
            <div class="step-n" :class="step>1?'done':(step===1?'cur':'pend')" x-text="step>1?'✓':'1'"></div>
            <div class="step-lbl" :class="step>1?'done':(step===1?'cur':'pend')">Personal Info</div>
          </div>
          <div class="step-line" :class="step>1?'done':''"></div>
          <div class="step">
            <div class="step-n" :class="step>2?'done':(step===2?'cur':'pend')" x-text="step>2?'✓':'2'"></div>
            <div class="step-lbl" :class="step>2?'done':(step===2?'cur':'pend')">Employment</div>
          </div>
          <div class="step-line" :class="step>2?'done':''"></div>
          <div class="step">
            <div class="step-n" :class="step>3?'done':(step===3?'cur':'pend')" x-text="step>3?'✓':'3'"></div>
            <div class="step-lbl" :class="step>3?'done':(step===3?'cur':'pend')">Collateral</div>
          </div>
          <div class="step-line" :class="step>3?'done':''"></div>
          <div class="step">
            <div class="step-n" :class="step===4?'cur':'pend'">4</div>
            <div class="step-lbl" :class="step===4?'cur':'pend'">Documents & Review</div>
          </div>
        </div>

        <!-- Step 1 -->
        <div x-show="step===1" class="anim">
          <div class="fsec">
            <div class="fsec-hd"><div class="fsec-ic" style="background:rgba(11,143,172,.15)">👤</div><div class="fsec-title">Personal Information</div></div>
            <div class="fsec-body">
              <div class="field"><label>First Name <span class="req">*</span></label><input class="finput" type="text" placeholder="e.g. Bwalya" x-model="f.fn"></div>
              <div class="field"><label>Last Name <span class="req">*</span></label><input class="finput" type="text" placeholder="e.g. Mwanza" x-model="f.ln"></div>
              <div class="field"><label>NRC / National ID <span class="req">*</span></label><input class="finput" type="text" placeholder="123456/78/9" x-model="f.nrc"><div class="fhint">Must be unique in the system</div></div>
              <div class="field"><label>Date of Birth</label><input class="finput" type="date" x-model="f.dob"></div>
              <div class="field"><label>Gender</label><select class="fsel" x-model="f.gender"><option value="">Select</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></div>
              <div class="field"><label>Primary Phone <span class="req">*</span></label><input class="finput" type="tel" placeholder="+260 97X XXX XXX" x-model="f.ph"><div class="fhint">SMS & WhatsApp reminders sent here</div></div>
              <div class="field"><label>Secondary Phone</label><input class="finput" type="tel" placeholder="+260 96X XXX XXX" x-model="f.ph2"></div>
              <div class="field"><label>Email Address</label><input class="finput" type="email" placeholder="email@example.com" x-model="f.email"><div class="fhint">Used for email reminders (optional)</div></div>
              <div class="field span2"><label>Residential Address <span class="req">*</span></label><textarea class="ftxtarea" placeholder="Plot number, street, area, city…" x-model="f.addr"></textarea></div>
              <div class="field"><label>City / Town</label><input class="finput" type="text" placeholder="e.g. Lusaka" x-model="f.city"></div>
            </div>
            <div class="form-actions">
              <button class="btn-g" @click="view='list'">Cancel</button>
              <button class="btn-p" @click="step=2">Next: Employment →</button>
            </div>
          </div>
        </div>

        <!-- Step 2 -->
        <div x-show="step===2" class="anim">
          <div class="fsec">
            <div class="fsec-hd"><div class="fsec-ic" style="background:rgba(245,166,35,.15)">💼</div><div class="fsec-title">Employment & Income</div></div>
            <div class="fsec-body">
              <div class="field"><label>Employment Status <span class="req">*</span></label><select class="fsel" x-model="f.emp"><option value="">Select</option><option value="employed">Employed</option><option value="self_employed">Self-Employed / Business Owner</option><option value="unemployed">Unemployed</option><option value="retired">Retired</option></select></div>
              <div class="field"><label>Employer / Business Name</label><input class="finput" type="text" placeholder="Company or business name" x-model="f.employer"></div>
              <div class="field"><label>Job Title / Position</label><input class="finput" type="text" placeholder="e.g. Manager, Trader" x-model="f.job"></div>
              <div class="field"><label>Monthly Income (K) <span class="req">*</span></label><input class="finput" type="number" placeholder="0.00" x-model="f.income"><div class="fhint">Gross monthly income in Kwacha</div></div>
              <div class="field"><label>Work Phone</label><input class="finput" type="tel" placeholder="+260 21X XXXXXX"></div>
              <div class="field"><label>Work Address</label><input class="finput" type="text" placeholder="Business / employer address"></div>
              <div class="field span2"><label>Internal Notes</label><textarea class="ftxtarea" placeholder="Any relevant notes about this borrower's financial situation…" x-model="f.notes"></textarea></div>
            </div>
            <div class="form-actions">
              <button class="btn-g" @click="step=1">← Back</button>
              <button class="btn-p" @click="step=3">Next: Collateral →</button>
            </div>
          </div>
        </div>

        <!-- Step 3 -->
        <div x-show="step===3" class="anim">
          <div class="fsec">
            <div class="fsec-hd"><div class="fsec-ic" style="background:rgba(34,197,94,.15)">🏛️</div><div class="fsec-title">Collateral Registration</div></div>
            <div style="padding:20px">
              <div class="sm ts mb16">Select the type of collateral the borrower is offering to secure the loan:</div>
              <div class="coll-types">
                <div class="coll-type" :class="{sel:f.coll==='vehicle'}" @click="f.coll='vehicle'">
                  <div class="coll-type-ic">🚗</div>
                  <div class="coll-type-lbl">Motor Vehicle</div>
                  <div class="coll-type-sub">Car, truck, bus, motorbike</div>
                </div>
                <div class="coll-type" :class="{sel:f.coll==='land'}" @click="f.coll='land'">
                  <div class="coll-type-ic">🏞️</div>
                  <div class="coll-type-lbl">Land / Property</div>
                  <div class="coll-type-sub">Plot, farm, commercial property</div>
                </div>
              </div>

              <!-- Vehicle fields -->
              <div x-show="f.coll==='vehicle'" class="anim" style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field"><label>Registration Number <span class="req">*</span></label><input class="finput" type="text" placeholder="ZMB 4521C" x-model="f.vReg"></div>
                <div class="field"><label>Make <span class="req">*</span></label><input class="finput" type="text" placeholder="Toyota" x-model="f.vMake"></div>
                <div class="field"><label>Model <span class="req">*</span></label><input class="finput" type="text" placeholder="Hilux" x-model="f.vModel"></div>
                <div class="field"><label>Year <span class="req">*</span></label><input class="finput" type="number" placeholder="2019" min="1990" max="2026" x-model="f.vYear"></div>
                <div class="field"><label>Engine Number</label><input class="finput" type="text" placeholder="Engine number" x-model="f.vEngine"></div>
                <div class="field"><label>Chassis / VIN</label><input class="finput" type="text" placeholder="Chassis number" x-model="f.vChassis"></div>
                <div class="field"><label>Estimated Value (K) <span class="req">*</span></label><input class="finput" type="number" placeholder="0.00" x-model="f.vValue"></div>
                <div class="field"><label>Insurance Expiry</label><input class="finput" type="date" x-model="f.vInsExpiry"></div>
                <div class="field"><label>Insurance Company</label><input class="finput" type="text" placeholder="Insurer name" x-model="f.vInsurer"></div>
                <div class="field"><label>Color</label><input class="finput" type="text" placeholder="e.g. White" x-model="f.vColor"></div>
              </div>

              <!-- Land fields -->
              <div x-show="f.coll==='land'" class="anim" style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field"><label>Plot Number <span class="req">*</span></label><input class="finput" type="text" placeholder="Plot 4821" x-model="f.lPlot"></div>
                <div class="field"><label>Title Deed Number</label><input class="finput" type="text" placeholder="Title deed / certificate no." x-model="f.lDeed"></div>
                <div class="field" style="grid-column:span 2"><label>Location / Address <span class="req">*</span></label><input class="finput" type="text" placeholder="Full physical address of the land" x-model="f.lAddress"></div>
                <div class="field"><label>Size (m²)</label><input class="finput" type="number" placeholder="e.g. 800" x-model="f.lSize"></div>
                <div class="field"><label>Ownership Type</label><select class="fsel"><option>Freehold</option><option>Leasehold</option><option>Customary</option></select></div>
                <div class="field"><label>Estimated Value (K) <span class="req">*</span></label><input class="finput" type="number" placeholder="0.00" x-model="f.lValue"></div>
                <div class="field"><label>Land Use</label><select class="fsel"><option>Residential</option><option>Commercial</option><option>Agricultural</option></select></div>
                <div class="field"><label>GPS Latitude</label><input class="finput" type="text" placeholder="-15.4166"></div>
                <div class="field"><label>GPS Longitude</label><input class="finput" type="text" placeholder="28.2833"></div>
              </div>

              <div x-show="!f.coll" style="text-align:center;padding:30px 0;color:var(--slate);font-size:13px">
                ↑ Please select a collateral type above to continue
              </div>
            </div>
            <div class="form-actions">
              <button class="btn-g" @click="step=2">← Back</button>
              <button class="btn-p" @click="step=4">Next: Documents & Review →</button>
            </div>
          </div>
        </div>

        <!-- Step 4 -->
        <div x-show="step===4" class="anim">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

            <!-- Upload docs -->
            <div class="fsec">
              <div class="fsec-hd"><div class="fsec-ic" style="background:rgba(129,140,248,.15)">📎</div><div class="fsec-title">Upload Documents</div></div>
              <div style="padding:20px;display:flex;flex-direction:column;gap:14px">
                <div>
                  <label style="display:block;margin-bottom:8px">National ID / NRC Copy <span class="req">*</span></label>
                  <label class="upload-zone" style="display:block;cursor:pointer">
                    <input type="file" accept=".pdf,.jpg,.jpeg,.png" style="display:none" @change="f.docNrc=$event.target.files[0]">
                    <div x-show="!f.docNrc"><div style="font-size:26px">🪪</div><div class="f6 tw sm mt8">Drop file or click to browse</div><div class="xs ts mt4">PDF, JPG, PNG · Max 5MB</div></div>
                    <div x-show="f.docNrc"><div style="font-size:22px">✅</div><div class="f6 tg sm mt8" x-text="f.docNrc.name"></div><div class="xs ts mt4">Click to change</div></div>
                  </label>
                </div>
                <div>
                  <label style="display:block;margin-bottom:8px">Proof of Income / Payslip</label>
                  <label class="upload-zone" style="display:block;cursor:pointer">
                    <input type="file" accept=".pdf,.jpg,.jpeg,.png" style="display:none" @change="f.docPayslip=$event.target.files[0]">
                    <div x-show="!f.docPayslip"><div style="font-size:26px">📄</div><div class="f6 tw sm mt8">Drop file or click to browse</div><div class="xs ts mt4">PDF, JPG, PNG · Max 5MB</div></div>
                    <div x-show="f.docPayslip"><div style="font-size:22px">✅</div><div class="f6 tg sm mt8" x-text="f.docPayslip.name"></div><div class="xs ts mt4">Click to change</div></div>
                  </label>
                </div>
                <div>
                  <label style="display:block;margin-bottom:8px" x-text="f.coll==='vehicle'?'Vehicle Logbook / Registration':'Title Deed / Certificate'">Collateral Document</label>
                  <label class="upload-zone" style="display:block;cursor:pointer">
                    <input type="file" accept=".pdf,.jpg,.jpeg,.png" style="display:none" @change="f.docColl=$event.target.files[0]">
                    <div x-show="!f.docColl"><div style="font-size:26px" x-text="f.coll==='vehicle'?'🚗':'📜'">📜</div><div class="f6 tw sm mt8">Drop file or click to browse</div><div class="xs ts mt4">PDF, JPG, PNG · Max 10MB</div></div>
                    <div x-show="f.docColl"><div style="font-size:22px">✅</div><div class="f6 tg sm mt8" x-text="f.docColl.name"></div><div class="xs ts mt4">Click to change</div></div>
                  </label>
                </div>
                <div>
                  <label style="display:block;margin-bottom:8px">Borrower Photo (Optional)</label>
                  <label class="upload-zone" style="display:block;cursor:pointer">
                    <input type="file" accept=".jpg,.jpeg,.png" style="display:none" @change="f.docPhoto=$event.target.files[0]">
                    <div x-show="!f.docPhoto"><div style="font-size:26px">📷</div><div class="f6 tw sm mt8">Drop file or click to browse</div><div class="xs ts mt4">JPG, PNG · Max 3MB</div></div>
                    <div x-show="f.docPhoto"><div style="font-size:22px">✅</div><div class="f6 tg sm mt8" x-text="f.docPhoto.name"></div><div class="xs ts mt4">Click to change</div></div>
                  </label>
                </div>
              </div>
            </div>

            <!-- Review -->
            <div>
              <div class="fsec">
                <div class="fsec-hd"><div class="fsec-ic" style="background:rgba(34,197,94,.15)">✅</div><div class="fsec-title">Review & Confirm</div></div>
                <div style="padding:18px">
                  <div class="rev-box">
                    <div class="rev-box-title">Personal</div>
                    <div class="info-row" style="padding:5px 0"><div class="info-lbl">Full Name</div><div class="info-val" x-text="(f.fn||'—')+' '+(f.ln||'')"></div></div>
                    <div class="info-row" style="padding:5px 0"><div class="info-lbl">NRC</div><div class="info-val mono" x-text="f.nrc||'—'"></div></div>
                    <div class="info-row" style="padding:5px 0"><div class="info-lbl">Phone</div><div class="info-val" x-text="f.ph||'—'"></div></div>
                    <div class="info-row" style="padding:5px 0;border-bottom:none"><div class="info-lbl">City</div><div class="info-val" x-text="f.city||'—'"></div></div>
                  </div>
                  <div class="rev-box">
                    <div class="rev-box-title">Employment</div>
                    <div class="info-row" style="padding:5px 0"><div class="info-lbl">Status</div><div class="info-val" x-text="f.emp||'—'"></div></div>
                    <div class="info-row" style="padding:5px 0;border-bottom:none"><div class="info-lbl">Monthly Income</div><div class="info-val mono" x-text="f.income?'K '+Number(f.income).toLocaleString():'—'"></div></div>
                  </div>
                  <div class="rev-box">
                    <div class="rev-box-title">Collateral</div>
                    <div class="info-row" style="padding:5px 0;border-bottom:none">
                      <div class="info-lbl">Type</div>
                      <div class="info-val">
                        <span x-show="f.coll==='vehicle'">🚗 Motor Vehicle</span>
                        <span x-show="f.coll==='land'">🏞️ Land / Property</span>
                        <span x-show="!f.coll" class="ts">Not selected</span>
                      </div>
                    </div>
                  </div>
                  <div style="padding:10px 12px;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:8px;margin-top:4px">
                    <div class="xs tg f6">✓ By submitting, you confirm all details are accurate and KYC-verified.</div>
                  </div>
                </div>
                <div class="form-actions">
                  <button class="btn-g" @click="step=3">← Back</button>
                  <button class="btn-p green" @click="submit()" style="min-width:170px">✓ Register Borrower</button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /register -->
    </div><!-- /content -->
  </main>

  <!-- ═══════ ADD COLLATERAL MODAL ═══════ -->
  <div x-show="showAddColl" style="display:none;position:fixed;inset:0;z-index:200;display:flex;align-items:center;justify-content:center;padding:16px"
       x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.55)" @click="showAddColl=false"></div>
    <div style="position:relative;background:var(--navy-card);border:1px solid var(--navy-line);border-radius:14px;width:100%;max-width:600px;max-height:90vh;overflow-y:auto;z-index:1">
      <div style="padding:18px 22px;border-bottom:1px solid var(--navy-line);display:flex;align-items:center;justify-content:space-between">
        <div style="font-size:15px;font-weight:700;color:var(--white)">Add Collateral Asset</div>
        <button @click="showAddColl=false" style="background:none;border:none;color:var(--slate);font-size:18px;cursor:pointer;line-height:1">✕</button>
      </div>
      <div style="padding:20px 22px">
        <!-- Type picker -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
          <div class="coll-type" :class="{sel:addColl.asset_type==='vehicle'}" @click="addColl.asset_type='vehicle'">
            <div class="coll-type-ic">🚗</div>
            <div class="coll-type-lbl">Motor Vehicle</div>
            <div class="coll-type-sub">Car, truck, bus, motorbike</div>
          </div>
          <div class="coll-type" :class="{sel:addColl.asset_type==='land'}" @click="addColl.asset_type='land'">
            <div class="coll-type-ic">🏞️</div>
            <div class="coll-type-lbl">Land / Property</div>
            <div class="coll-type-sub">Plot, farm, commercial property</div>
          </div>
        </div>
        <!-- Vehicle fields -->
        <div x-show="addColl.asset_type==='vehicle'" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div class="field"><label>Registration No. <span class="req">*</span></label><input class="finput" type="text" placeholder="ZMB 4521C" x-model="addColl.vReg"></div>
          <div class="field"><label>Make</label><input class="finput" type="text" placeholder="Toyota" x-model="addColl.vMake"></div>
          <div class="field"><label>Model</label><input class="finput" type="text" placeholder="Hilux" x-model="addColl.vModel"></div>
          <div class="field"><label>Year</label><input class="finput" type="number" placeholder="2019" min="1950" max="2027" x-model="addColl.vYear"></div>
          <div class="field"><label>Color</label><input class="finput" type="text" placeholder="White" x-model="addColl.vColor"></div>
          <div class="field"><label>Estimated Value (K)</label><input class="finput" type="number" placeholder="0.00" x-model="addColl.vValue"></div>
          <div class="field"><label>Engine Number</label><input class="finput" type="text" placeholder="Engine no." x-model="addColl.vEngine"></div>
          <div class="field"><label>Chassis / VIN</label><input class="finput" type="text" placeholder="Chassis no." x-model="addColl.vChassis"></div>
          <div class="field"><label>Insurance Expiry</label><input class="finput" type="date" x-model="addColl.vInsExpiry"></div>
          <div class="field"><label>Insurance Company</label><input class="finput" type="text" placeholder="Insurer" x-model="addColl.vInsurer"></div>
        </div>
        <!-- Land fields -->
        <div x-show="addColl.asset_type==='land'" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div class="field"><label>Plot Number <span class="req">*</span></label><input class="finput" type="text" placeholder="Plot 4821" x-model="addColl.lPlot"></div>
          <div class="field"><label>Title Deed Number</label><input class="finput" type="text" placeholder="Title deed no." x-model="addColl.lDeed"></div>
          <div class="field" style="grid-column:span 2"><label>Location / Address</label><input class="finput" type="text" placeholder="Full physical address" x-model="addColl.lAddress"></div>
          <div class="field"><label>Size (m²)</label><input class="finput" type="number" placeholder="e.g. 800" x-model="addColl.lSize"></div>
          <div class="field"><label>Estimated Value (K)</label><input class="finput" type="number" placeholder="0.00" x-model="addColl.lValue"></div>
        </div>
      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--navy-line);display:flex;justify-content:flex-end;gap:10px">
        <button class="btn-g" @click="showAddColl=false">Cancel</button>
        <button class="btn-p" @click="submitAddColl()" :disabled="addCollSaving" x-text="addCollSaving?'Saving…':'Add Collateral'"></button>
      </div>
    </div>
  </div>

  <!-- ═══════ EDIT BORROWER MODAL ═══════ -->
  <div x-show="showEdit" style="display:none;position:fixed;inset:0;z-index:200;display:flex;align-items:center;justify-content:center;padding:16px"
       x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.55)" @click="showEdit=false"></div>
    <div style="position:relative;background:var(--navy-card);border:1px solid var(--navy-line);border-radius:14px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;z-index:1">
      <div style="padding:20px 24px;border-bottom:1px solid var(--navy-line);display:flex;align-items:center;justify-content:space-between">
        <div style="font-size:15px;font-weight:700;color:var(--white)">Edit Borrower Details</div>
        <button @click="showEdit=false" style="background:none;border:none;color:var(--slate);font-size:18px;cursor:pointer;line-height:1">✕</button>
      </div>
      <div style="padding:20px 24px;display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="field"><label>First Name <span class="req">*</span></label><input class="finput" type="text" x-model="editData.first_name"></div>
        <div class="field"><label>Last Name <span class="req">*</span></label><input class="finput" type="text" x-model="editData.last_name"></div>
        <div class="field"><label>NRC Number <span class="req">*</span></label><input class="finput" type="text" x-model="editData.nrc_number"></div>
        <div class="field"><label>Date of Birth</label><input class="finput" type="date" x-model="editData.date_of_birth"></div>
        <div class="field"><label>Gender</label>
          <select class="fsel" x-model="editData.gender">
            <option value="">Select…</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="field"><label>Primary Phone <span class="req">*</span></label><input class="finput" type="text" x-model="editData.phone_primary"></div>
        <div class="field"><label>Secondary Phone</label><input class="finput" type="text" x-model="editData.phone_secondary"></div>
        <div class="field"><label>Email</label><input class="finput" type="email" x-model="editData.email"></div>
        <div class="field" style="grid-column:span 2"><label>Residential Address <span class="req">*</span></label><input class="finput" type="text" x-model="editData.residential_address"></div>
        <div class="field"><label>City / Town <span class="req">*</span></label><input class="finput" type="text" x-model="editData.city_town"></div>
        <div class="field"><label>Employment Status <span class="req">*</span></label>
          <select class="fsel" x-model="editData.employment_status">
            <option value="">Select…</option>
            <option value="employed">Employed</option>
            <option value="self_employed">Self-Employed</option>
            <option value="unemployed">Unemployed</option>
            <option value="retired">Retired</option>
          </select>
        </div>
        <div class="field"><label>Employer Name</label><input class="finput" type="text" x-model="editData.employer_name"></div>
        <div class="field"><label>Job Title</label><input class="finput" type="text" x-model="editData.job_title"></div>
        <div class="field"><label>Monthly Income (K)</label><input class="finput" type="number" x-model="editData.monthly_income"></div>
        <div class="field" style="grid-column:span 2"><label>Internal Notes</label><textarea class="finput" rows="2" style="resize:vertical" x-model="editData.internal_notes"></textarea></div>
      </div>
      <div style="padding:14px 24px;border-top:1px solid var(--navy-line);display:flex;justify-content:flex-end;gap:10px">
        <button class="btn-g" @click="showEdit=false">Cancel</button>
        <button class="btn-p" @click="saveEdit()" :disabled="editSaving" x-text="editSaving?'Saving…':'Save Changes'"></button>
      </div>
    </div>
  </div>

  <!-- ═══════ UPLOAD DOCUMENT MODAL ═══════ -->
  <div x-show="showUploadModal" style="display:none;position:fixed;inset:0;z-index:200;display:flex;align-items:center;justify-content:center;padding:16px"
       x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.55)" @click="showUploadModal=false"></div>
    <div style="position:relative;background:var(--navy-card);border:1px solid var(--navy-line);border-radius:14px;width:100%;max-width:440px;z-index:1">
      <div style="padding:18px 22px;border-bottom:1px solid var(--navy-line);display:flex;align-items:center;justify-content:space-between">
        <div style="font-size:15px;font-weight:700;color:var(--white)">Upload Document</div>
        <button @click="showUploadModal=false" style="background:none;border:none;color:var(--slate);font-size:18px;cursor:pointer;line-height:1">✕</button>
      </div>
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">
        <div class="field">
          <label>Document Type <span class="req">*</span></label>
          <select class="fsel" x-model="uploadType">
            <option value="national_id">National ID</option>
            <option value="payslip">Payslip</option>
            <option value="bank_statement">Bank Statement</option>
            <option value="vehicle_logbook">Vehicle Logbook</option>
            <option value="vehicle_photos">Vehicle Photos</option>
            <option value="land_title_deed">Land Title Deed</option>
            <option value="valuation_report">Valuation Report</option>
            <option value="loan_agreement">Loan Agreement</option>
            <option value="guarantor_id">Guarantor ID</option>
            <option value="proof_of_residence">Proof of Residence</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="field">
          <label>File <span class="req">*</span></label>
          <label class="upload-zone" style="display:block;cursor:pointer">
            <input type="file" accept=".pdf,.jpg,.jpeg,.png" style="display:none" @change="uploadFile=$event.target.files[0]">
            <div x-show="!uploadFile" style="padding:18px;text-align:center">
              <div style="font-size:28px">📎</div>
              <div style="font-size:12px;color:var(--slate-lt);margin-top:6px">Click to browse · PDF, JPG, PNG · Max 10MB</div>
            </div>
            <div x-show="uploadFile" style="padding:14px;text-align:center">
              <div style="font-size:22px">✅</div>
              <div style="font-size:12px;color:var(--green);margin-top:6px" x-text="uploadFile?.name"></div>
              <div style="font-size:11px;color:var(--slate);margin-top:2px">Click to change</div>
            </div>
          </label>
        </div>
      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--navy-line);display:flex;justify-content:flex-end;gap:10px">
        <button class="btn-g" @click="showUploadModal=false">Cancel</button>
        <button class="btn-p" @click="submitUpload()" :disabled="!uploadFile||uploadSaving" x-text="uploadSaving?'Uploading…':'Upload Document'"></button>
      </div>
    </div>
  </div>

  <!-- Toast notification -->
  <div class="toast-msg" x-show="toast" x-transition style="display:none" x-text="toastMsg"></div>

  <script>
  function app() {
    return {
      view: 'list',
      ptab: 'loans',
      step: 1,
      q: '', fStatus: '', fColl: '',
      sel: null,
      toast: false, toastMsg: '',
      rows: [], page: 1, perPage: 8, loading: false,
      selLoading: false, selDetail: null,
      stats: { borrowers: { total:'-', with_active:'-', with_overdue:'-', no_active_loan:'-' } },
      showEdit: false, editSaving: false,
      editData: {},
      showUploadModal: false, uploadFile: null, uploadType: 'other', uploadSaving: false,
      showAddColl: false, addCollSaving: false,
      addColl: { asset_type:'vehicle', vReg:'',vMake:'',vModel:'',vYear:'',vColor:'',vEngine:'',vChassis:'',vInsExpiry:'',vInsurer:'',vValue:'',lPlot:'',lDeed:'',lAddress:'',lSize:'',lValue:'' },

      f: { fn:'',ln:'',nrc:'',dob:'',gender:'',ph:'',ph2:'',email:'',addr:'',city:'',emp:'',employer:'',job:'',income:'',notes:'',coll:'',
           vReg:'',vMake:'',vModel:'',vYear:'',vColor:'',vEngine:'',vChassis:'',vInsExpiry:'',vInsurer:'',vValue:'',
           lPlot:'',lDeed:'',lAddress:'',lSize:'',lValue:'',
           docNrc:null,docPayslip:null,docColl:null,docPhoto:null },

      all: [
        { id:1,  name:'Bwalya Mwanza',    bnum:'BRW-00201', nrc:'123456/78/1', phone:'+260 977 100 201', email:'bwalya@email.com', emp:'Employed',      income:'K 8,500',  addr:'Plot 22, Chelstone, Lusaka',    coll:'vehicle', loan:'LN-20260032', owed:'K 27,300', status:'active',  reg:'12 Nov 2024', officer:'Mary Phiri',  ini:'BM', c1:'#0B8FAC', c2:'#076E86' },
        { id:2,  name:'Charity Mutale',   bnum:'BRW-00198', nrc:'234567/89/2', phone:'+260 966 200 198', email:'',               emp:'Self-Employed',  income:'K 12,000', addr:'Plot 8, Kabulonga, Lusaka',     coll:'land',    loan:'LN-20260018', owed:'K 44,200', status:'active',  reg:'03 Oct 2024', officer:'John Banda',  ini:'CM', c1:'#6366F1', c2:'#4338CA' },
        { id:3,  name:'Daniel Phiri',     bnum:'BRW-00185', nrc:'345678/90/3', phone:'+260 955 300 185', email:'daniel@mail.zm', emp:'Employed',       income:'K 6,200',  addr:'House 14, Emmasdale, Lusaka',   coll:'vehicle', loan:'LN-20260041', owed:'K 19,800', status:'active',  reg:'19 Aug 2024', officer:'Mary Phiri',  ini:'DP', c1:'#10B981', c2:'#059669' },
        { id:4,  name:'Grace Nkonde',     bnum:'BRW-00172', nrc:'456789/01/4', phone:'+260 977 400 172', email:'',               emp:'Business Owner', income:'K 15,000', addr:'Plot 55, Woodlands, Lusaka',    coll:'land',    loan:'LN-20260009', owed:'K 8,400',  status:'overdue', reg:'05 Jun 2024', officer:'John Banda',  ini:'GN', c1:'#EF4444', c2:'#B91C1C' },
        { id:5,  name:'Henry Zulu',       bnum:'BRW-00165', nrc:'567890/12/5', phone:'+260 966 500 165', email:'henry@zulu.zm',  emp:'Employed',       income:'K 9,800',  addr:'Plot 7, Kalundu, Lusaka',       coll:'vehicle', loan:'LN-20260014', owed:'K 11,200', status:'overdue', reg:'14 Apr 2024', officer:'Mary Phiri',  ini:'HZ', c1:'#F59E0B', c2:'#D97706' },
        { id:6,  name:'Irene Mumba',      bnum:'BRW-00158', nrc:'678901/23/6', phone:'+260 955 600 158', email:'',               emp:'Self-Employed',  income:'K 7,400',  addr:'House 33, Olympia Park, Lusaka',coll:'land',    loan:'LN-20260021', owed:'K 4,900',  status:'overdue', reg:'28 Feb 2024', officer:'John Banda',  ini:'IM', c1:'#EC4899', c2:'#BE185D' },
        { id:7,  name:'James Kamanga',    bnum:'BRW-00144', nrc:'789012/34/7', phone:'+260 977 700 144', email:'jkamanga@co.zm', emp:'Employed',       income:'K 14,000', addr:'Plot 92, Ibex Hill, Lusaka',    coll:'vehicle', loan:null,          owed:null,       status:'closed',  reg:'10 Jan 2024', officer:'Mary Phiri',  ini:'JK', c1:'#64748B', c2:'#475569' },
        { id:8,  name:'Loveness Chola',   bnum:'BRW-00139', nrc:'890123/45/8', phone:'+260 966 800 139', email:'',               emp:'Business Owner', income:'K 18,500', addr:'Plot 14, Roma, Lusaka',          coll:'land',    loan:'LN-20260048', owed:'K 68,000', status:'active',  reg:'22 Dec 2023', officer:'John Banda',  ini:'LC', c1:'#A78BFA', c2:'#7C3AED' },
        { id:9,  name:'Moses Banda',      bnum:'BRW-00131', nrc:'901234/56/9', phone:'+260 955 900 131', email:'moses@banda.zm', emp:'Employed',       income:'K 11,200', addr:'House 6, Avondale, Lusaka',     coll:'vehicle', loan:null,          owed:null,       status:'closed',  reg:'09 Nov 2023', officer:'Mary Phiri',  ini:'MB', c1:'#34D399', c2:'#059669' },
        { id:10, name:'Noel Phiri',       bnum:'BRW-00128', nrc:'012345/67/0', phone:'+260 977 000 128', email:'',               emp:'Self-Employed',  income:'K 8,900',  addr:'Plot 38, Northmead, Lusaka',    coll:'land',    loan:'LN-20260063', owed:'K 49,500', status:'active',  reg:'02 Oct 2023', officer:'John Banda',  ini:'NP', c1:'#F472B6', c2:'#DB2777' },
      ],

      pays: [
        { id:1, rc:'RCP-00891', dt:'21 Feb 2026', mth:'Cash',          amt:'+K 4,200', bal:'K 27,300', type:'Installment', bdg:'b-active',  ic:'💵', bg:'rgba(34,197,94,.12)' },
        { id:2, rc:'RCP-00876', dt:'21 Jan 2026', mth:'Mobile Money',  amt:'+K 4,200', bal:'K 31,500', type:'Installment', bdg:'b-active',  ic:'📱', bg:'rgba(11,143,172,.12)' },
        { id:3, rc:'RCP-00861', dt:'21 Dec 2025', mth:'Bank Transfer', amt:'+K 4,200', bal:'K 35,700', type:'Installment', bdg:'b-active',  ic:'🏦', bg:'rgba(129,140,248,.12)' },
        { id:4, rc:'RCP-00845', dt:'10 Dec 2025', mth:'Cash',          amt:'+K 1,500', bal:'K 39,900', type:'Partial',     bdg:'b-pending', ic:'💰', bg:'rgba(245,166,35,.12)' },
        { id:5, rc:'RCP-00831', dt:'21 Nov 2025', mth:'Cash',          amt:'+K 2,700', bal:'K 41,400', type:'Partial',     bdg:'b-pending', ic:'💰', bg:'rgba(245,166,35,.12)' },
      ],

      docs: [
        { id:1, name:'National ID — Bwalya Mwanza',  meta:'Uploaded 12 Nov 2024 · PDF · 420 KB', ic:'🪪', bg:'rgba(11,143,172,.15)' },
        { id:2, name:'Payslip — December 2025',       meta:'Uploaded 15 Dec 2025 · PDF · 1.2 MB', ic:'📄', bg:'rgba(129,140,248,.15)' },
        { id:3, name:'Vehicle Logbook — ZMB 4521C',  meta:'Uploaded 12 Nov 2024 · PDF · 2.1 MB', ic:'🚗', bg:'rgba(245,166,35,.15)' },
        { id:4, name:'Vehicle Photos (x4)',            meta:'Uploaded 12 Nov 2024 · ZIP · 8.4 MB', ic:'📷', bg:'rgba(34,197,94,.15)' },
        { id:5, name:'Loan Agreement — LN-20260032', meta:'Generated 15 Dec 2025 · PDF · 340 KB', ic:'📝', bg:'rgba(239,68,68,.15)' },
        { id:6, name:'Valuation Report — Vehicle',    meta:'Uploaded 10 Dec 2025 · PDF · 1.8 MB', ic:'📊', bg:'rgba(236,72,153,.15)' },
      ],

      async init() {
        await Promise.all([this.loadStats(), this.loadBorrowers()]);
      },

      async loadStats() {
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch('/api/stats', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
          if (res.ok) this.stats = await res.json();
        } catch {}
      },

      async loadBorrowers() {
        this.loading = true;
        const token = localStorage.getItem('lms_token');
        const palette = [
          ['#0B8FAC','#076E86'],['#6366F1','#4338CA'],['#10B981','#059669'],
          ['#EF4444','#B91C1C'],['#F59E0B','#D97706'],['#EC4899','#BE185D'],
          ['#64748B','#475569'],['#A78BFA','#7C3AED'],['#34D399','#059669'],['#F472B6','#DB2777'],
        ];
        try {
          const res = await fetch('/api/borrowers?per_page=200', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
          });
          if (res.ok) {
            const data = await res.json();
            this.all = (data.data || []).map(b => {
              const clr = palette[b.id % palette.length];
              const parts = [b.first_name, b.last_name].filter(Boolean);
              const ini = parts.map(p => p[0]).join('').toUpperCase();
              const activeLoan = (b.loans || [])[0];
              let status = 'inactive';
              if (activeLoan) status = activeLoan.status === 'overdue' ? 'overdue' : 'active';
              else if ((b.active_loans_count || 0) > 0) status = 'active';
              return {
                id: b.id,
                name: parts.join(' '),
                bnum: b.borrower_number,
                nrc: b.nrc_number,
                phone: b.phone_primary,
                email: b.email || '',
                emp: b.employment_status || '—',
                income: b.monthly_income ? 'K ' + Number(b.monthly_income).toLocaleString() : '—',
                addr: b.residential_address || '—',
                loan: activeLoan?.loan_number || null,
                owed: activeLoan?.loan_balance?.total_outstanding
                  ? 'K ' + Number(activeLoan.loan_balance.total_outstanding).toLocaleString() : null,
                coll: null,
                status,
                reg: b.created_at
                  ? new Date(b.created_at).toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'})
                  : '—',
                officer: b.assigned_officer?.name || '—',
                ini, c1: clr[0], c2: clr[1],
              };
            });
            this.rows = [...this.all];
            this.page = 1;
          }
        } catch(e) { console.error('loadBorrowers:', e); }
        this.loading = false;
      },

      get pagedRows() { const s=(this.page-1)*this.perPage; return this.rows.slice(s,s+this.perPage); },
      get totalPages() { return Math.max(1,Math.ceil(this.rows.length/this.perPage)); },

      doFilter() {
        const q = this.q.toLowerCase();
        this.rows = this.all.filter(b => {
          const mq = !q || b.name.toLowerCase().includes(q) || b.nrc.includes(q) || b.phone.includes(q) || (b.loan||'').toLowerCase().includes(q) || b.bnum.toLowerCase().includes(q);
          const ms = !this.fStatus || b.status === this.fStatus;
          const mc = !this.fColl   || b.coll   === this.fColl;
          return mq && ms && mc;
        });
        this.page = 1;
      },

      showToast(msg) { this.toastMsg = msg; this.toast = true; setTimeout(()=>this.toast=false, 3000); },

      async open(b) {
        this.sel = b; this.ptab = 'loans'; this.view = 'profile';
        this.selDetail = null; this.selLoading = true;
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch('/api/borrowers/' + b.id, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
          });
          if (res.ok) this.selDetail = await res.json();
        } catch(e) { console.error(e); }
        this.selLoading = false;
      },

      openAddColl() {
        this.addColl = { asset_type:'vehicle', vReg:'',vMake:'',vModel:'',vYear:'',vColor:'',vEngine:'',vChassis:'',vInsExpiry:'',vInsurer:'',vValue:'',lPlot:'',lDeed:'',lAddress:'',lSize:'',lValue:'' };
        this.showAddColl = true;
      },

      async submitAddColl() {
        if (this.addCollSaving) return;
        this.addCollSaving = true;
        const token = localStorage.getItem('lms_token');
        const today = new Date().toISOString().split('T')[0];
        const payload = { borrower_id: this.selDetail.id, asset_type: this.addColl.asset_type, valuation_date: today };
        if (this.addColl.asset_type === 'vehicle') {
          Object.assign(payload, {
            vehicle_registration: this.addColl.vReg      || null,
            vehicle_make:         this.addColl.vMake     || null,
            vehicle_model:        this.addColl.vModel    || null,
            vehicle_year:         this.addColl.vYear     ? parseInt(this.addColl.vYear) : null,
            vehicle_color:        this.addColl.vColor    || null,
            engine_number:        this.addColl.vEngine   || null,
            chassis_vin:          this.addColl.vChassis  || null,
            insurance_expiry:     this.addColl.vInsExpiry || null,
            insurance_company:    this.addColl.vInsurer  || null,
            estimated_value:      this.addColl.vValue    ? parseFloat(this.addColl.vValue) : null,
          });
        } else {
          Object.assign(payload, {
            plot_number:       this.addColl.lPlot    || null,
            title_deed_number: this.addColl.lDeed    || null,
            land_address:      this.addColl.lAddress || null,
            land_size_sqm:     this.addColl.lSize    ? parseFloat(this.addColl.lSize) : null,
            estimated_value:   this.addColl.lValue   ? parseFloat(this.addColl.lValue) : null,
          });
        }
        try {
          const res = await fetch('/api/collateral', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
          });
          const data = await res.json();
          if (!res.ok) {
            const msg = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Failed to add collateral.');
            this.showToast('✗ ' + msg);
          } else {
            this.showToast('✓ Collateral asset added.');
            this.showAddColl = false;
            await this.open({ id: this.selDetail.id });
          }
        } catch { this.showToast('✗ Network error.'); }
        this.addCollSaving = false;
      },

      openEdit() {
        if (!this.selDetail) return;
        this.editData = {
          first_name:          this.selDetail.first_name          || '',
          last_name:           this.selDetail.last_name           || '',
          nrc_number:          this.selDetail.nrc_number          || '',
          date_of_birth:       this.selDetail.date_of_birth       || '',
          gender:              this.selDetail.gender              || '',
          phone_primary:       this.selDetail.phone_primary       || '',
          phone_secondary:     this.selDetail.phone_secondary     || '',
          email:               this.selDetail.email               || '',
          residential_address: this.selDetail.residential_address || '',
          city_town:           this.selDetail.city_town           || '',
          employment_status:   this.selDetail.employment_status   || '',
          employer_name:       this.selDetail.employer_name       || '',
          job_title:           this.selDetail.job_title           || '',
          monthly_income:      this.selDetail.monthly_income      || '',
          internal_notes:      this.selDetail.internal_notes      || '',
        };
        this.showEdit = true;
      },

      async saveEdit() {
        if (this.editSaving) return;
        this.editSaving = true;
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch(`/api/borrowers/${this.selDetail.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
            body: JSON.stringify(this.editData),
          });
          const data = await res.json();
          if (!res.ok) {
            const msg = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Update failed.');
            this.showToast('✗ ' + msg);
          } else {
            this.showToast('✓ Borrower details updated.');
            this.showEdit = false;
            await this.open({ id: this.selDetail.id });
          }
        } catch { this.showToast('✗ Network error.'); }
        this.editSaving = false;
      },

      openUploadModal() {
        this.uploadFile = null;
        this.uploadType = 'other';
        this.showUploadModal = true;
      },

      async submitUpload() {
        if (!this.uploadFile || this.uploadSaving) return;
        this.uploadSaving = true;
        const token = localStorage.getItem('lms_token');
        try {
          const fd = new FormData();
          fd.append('file', this.uploadFile);
          fd.append('document_type', this.uploadType);
          const res = await fetch(`/api/borrowers/${this.selDetail.id}/documents`, {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
            body: fd,
          });
          const data = await res.json();
          if (!res.ok) {
            const msg = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Upload failed.');
            this.showToast('✗ ' + msg);
          } else {
            this.showToast('✓ Document uploaded.');
            this.showUploadModal = false;
            await this.open({ id: this.selDetail.id });
          }
        } catch { this.showToast('✗ Network error.'); }
        this.uploadSaving = false;
      },

      async downloadDoc(borrowerId, docId, fileName) {
        const token = localStorage.getItem('lms_token');
        try {
          const resp = await fetch(`/api/borrowers/${borrowerId}/documents/${docId}/download`, {
            headers: { 'Authorization': 'Bearer ' + token }
          });
          if (!resp.ok) { this.showToast('✗ Download failed.'); return; }
          const blob = await resp.blob();
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url; a.download = fileName; a.click();
          URL.revokeObjectURL(url);
        } catch { this.showToast('✗ Network error.'); }
      },

      async viewDoc(borrowerId, docId) {
        const token = localStorage.getItem('lms_token');
        try {
          const resp = await fetch(`/api/borrowers/${borrowerId}/documents/${docId}/view`, {
            headers: { 'Authorization': 'Bearer ' + token }
          });
          if (!resp.ok) { this.showToast('✗ Could not open document.'); return; }
          const blob = await resp.blob();
          const url = URL.createObjectURL(blob);
          window.open(url, '_blank');
        } catch { this.showToast('✗ Network error.'); }
      },

      async submit() {
        const token = localStorage.getItem('lms_token');
        const payload = {
          first_name:          this.f.fn,
          last_name:           this.f.ln,
          nrc_number:          this.f.nrc,
          date_of_birth:       this.f.dob,
          gender:              this.f.gender,
          phone_primary:       this.f.ph,
          phone_secondary:     this.f.ph2  || null,
          email:               this.f.email || null,
          residential_address: this.f.addr,
          city_town:           this.f.city,
          employment_status:   this.f.emp,
          employer_name:       this.f.employer || null,
          job_title:           this.f.job      || null,
          monthly_income:      this.f.income   || null,
          internal_notes:      this.f.notes    || null,
        };
        try {
          const res  = await fetch('/api/borrowers', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
          });
          const data = await res.json();
          if (!res.ok) {
            const msg = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Registration failed.');
            this.showToast('✗ ' + msg);
            return;
          }
          const bid = data.borrower.id;

          // Save collateral asset if collateral type was selected
          if (this.f.coll) {
            const today = new Date().toISOString().split('T')[0];
            const collPayload = { borrower_id: bid, asset_type: this.f.coll, valuation_date: today };
            if (this.f.coll === 'vehicle') {
              Object.assign(collPayload, {
                vehicle_registration: this.f.vReg      || null,
                vehicle_make:         this.f.vMake     || null,
                vehicle_model:        this.f.vModel    || null,
                vehicle_year:         this.f.vYear     ? parseInt(this.f.vYear) : null,
                vehicle_color:        this.f.vColor    || null,
                engine_number:        this.f.vEngine   || null,
                chassis_vin:          this.f.vChassis  || null,
                insurance_expiry:     this.f.vInsExpiry || null,
                insurance_company:    this.f.vInsurer  || null,
                estimated_value:      this.f.vValue    ? parseFloat(this.f.vValue) : null,
              });
            } else {
              Object.assign(collPayload, {
                plot_number:       this.f.lPlot    || null,
                title_deed_number: this.f.lDeed    || null,
                land_address:      this.f.lAddress || null,
                land_size_sqm:     this.f.lSize    ? parseFloat(this.f.lSize) : null,
                estimated_value:   this.f.lValue   ? parseFloat(this.f.lValue) : null,
              });
            }
            const collRes = await fetch('/api/collateral', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
              body: JSON.stringify(collPayload),
            });
            if (!collRes.ok) {
              const ce = await collRes.json();
              const cm = ce.errors ? Object.values(ce.errors)[0][0] : (ce.message || 'Collateral not saved.');
              this.showToast('⚠ Borrower registered but collateral failed: ' + cm);
            }
          }

          const docQueue = [
            { file: this.f.docNrc,     type: 'national_id'     },
            { file: this.f.docPayslip, type: 'payslip'         },
            { file: this.f.docColl,    type: this.f.coll==='vehicle' ? 'vehicle_logbook' : 'land_title_deed' },
            { file: this.f.docPhoto,   type: 'other'           },
          ];
          for (const d of docQueue) {
            if (!d.file) continue;
            const fd = new FormData();
            fd.append('file', d.file);
            fd.append('document_type', d.type);
            await fetch(`/api/borrowers/${bid}/documents`, {
              method: 'POST',
              headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
              body: fd,
            });
          }
          this.showToast('✓ Borrower registered! ' + data.borrower.borrower_number + ' created.');
          this.view = 'list';
          this.step = 1;
          this.f = { fn:'',ln:'',nrc:'',dob:'',gender:'',ph:'',ph2:'',email:'',addr:'',city:'',emp:'',employer:'',job:'',income:'',notes:'',coll:'',docNrc:null,docPayslip:null,docColl:null,docPhoto:null };
          await this.loadBorrowers();
        } catch (err) {
          this.showToast('✗ Network error. Please try again.');
        }
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
