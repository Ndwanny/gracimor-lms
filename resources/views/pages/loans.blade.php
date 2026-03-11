<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gracimor LMS — Loans</title>
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
    body{font-family:'DM Sans',sans-serif;background:var(--navy);color:var(--text);min-height:100vh;display:flex}

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
    .nav-bdg{margin-left:auto;color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px}
    .sidebar-footer{margin-top:auto;padding:16px 12px;border-top:1px solid var(--navy-line)}
    .user-pill{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;background:var(--navy-line)}
    .u-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--teal),var(--amber));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0}
    .u-name{font-size:13px;font-weight:600;color:var(--white)}
    .u-role{font-size:10px;color:var(--teal);font-weight:500;text-transform:uppercase;letter-spacing:.08em}

    /* ── Main ── */
    .main{margin-left:240px;flex:1;display:flex;flex-direction:column;min-height:100vh}
    .topbar{height:64px;background:var(--navy-mid);border-bottom:1px solid var(--navy-line);display:flex;align-items:center;padding:0 28px;gap:14px;position:sticky;top:0;z-index:40}
    .page-title{font-family:'Playfair Display',serif;font-size:20px;color:var(--white)}
    .breadcrumb{font-size:12px;color:var(--slate)}
    .breadcrumb span{color:var(--teal-lt);cursor:pointer}
    .breadcrumb span:hover{text-decoration:underline}
    .tb-right{margin-left:auto;display:flex;align-items:center;gap:10px}
    .content{padding:28px;flex:1}

    /* ── Buttons ── */
    .btn-p{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--teal);color:#fff;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s;white-space:nowrap}
    .btn-p:hover{background:var(--teal-lt)}
    .btn-g{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:transparent;color:var(--slate-lt);border:1px solid var(--navy-line);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:all .15s;white-space:nowrap}
    .btn-g:hover{background:var(--navy-line);color:var(--white)}
    .btn-green{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--green);color:#fff;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s;white-space:nowrap}
    .btn-green:hover{background:var(--green-dk)}
    .btn-amber{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--amber);color:#000;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:background .15s;white-space:nowrap}
    .btn-amber:hover{background:var(--amber-lt)}
    .btn-red{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:rgba(239,68,68,.15);color:var(--red);border:1px solid rgba(239,68,68,.3);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;white-space:nowrap}
    .btn-red:hover{background:rgba(239,68,68,.25)}
    .btn-sm{font-size:11px !important;padding:5px 10px !important}

    /* ── Stats ── */
    .stats-row{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px}
    .m-stat{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;transition:all .15s;cursor:pointer}
    .m-stat:hover{border-color:rgba(11,143,172,.35);transform:translateY(-1px)}
    .m-stat.sel-stat{border-color:var(--teal);background:rgba(11,143,172,.08)}
    .m-stat-ic{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
    .m-stat-val{font-family:'DM Mono',monospace;font-size:20px;font-weight:500;color:var(--white)}
    .m-stat-lbl{font-size:10px;color:var(--slate);margin-top:2px;text-transform:uppercase;letter-spacing:.08em;font-weight:600}

    /* ── Filter / search ── */
    .filter-bar{display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap}
    .srch-wrap{position:relative;flex:1;min-width:220px}
    .srch-wrap svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.4;pointer-events:none}
    .srch{width:100%;background:var(--navy-card);border:1px solid var(--navy-line);border-radius:9px;padding:10px 14px 10px 38px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .15s}
    .srch:focus{border-color:var(--teal)}
    .srch::placeholder{color:var(--slate)}
    .sel{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:9px;padding:10px 14px;color:var(--slate-lt);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;cursor:pointer}

    /* ── Table ── */
    .tbl-wrap{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:12px;overflow:hidden}
    .dtbl{width:100%;border-collapse:collapse}
    .dtbl th{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--slate);text-align:left;padding:12px 18px;border-bottom:1px solid var(--navy-line);background:var(--navy-mid);white-space:nowrap;cursor:pointer}
    .dtbl th:hover{color:var(--white)}
    .dtbl td{padding:13px 18px;font-size:13px;border-bottom:1px solid rgba(30,52,80,.5);vertical-align:middle}
    .dtbl tr:last-child td{border-bottom:none}
    .dtbl tbody tr{cursor:pointer;transition:background .1s}
    .dtbl tbody tr:hover td{background:var(--navy-hover)}
    .pgn{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-top:1px solid var(--navy-line)}
    .pgn-info{font-size:12px;color:var(--slate)}
    .pgn-btns{display:flex;gap:4px}
    .pgn-btn{width:30px;height:30px;border-radius:6px;background:var(--navy-line);border:none;color:var(--slate-lt);font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s}
    .pgn-btn:hover{background:var(--navy-hover);color:var(--white)}
    .pgn-btn.active{background:var(--teal);color:#fff}

    /* ── Badges ── */
    .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:99px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em}
    .badge::before{content:'';width:5px;height:5px;border-radius:50%;flex-shrink:0}
    .b-active{background:rgba(34,197,94,.12);color:var(--green);border:1px solid rgba(34,197,94,.25)} .b-active::before{background:var(--green)}
    .b-pending{background:rgba(245,166,35,.12);color:var(--amber);border:1px solid rgba(245,166,35,.25)} .b-pending::before{background:var(--amber)}
    .b-approved{background:rgba(11,143,172,.12);color:var(--teal-lt);border:1px solid rgba(11,143,172,.25)} .b-approved::before{background:var(--teal-lt)}
    .b-disbursed{background:rgba(129,140,248,.12);color:var(--purple);border:1px solid rgba(129,140,248,.25)} .b-disbursed::before{background:var(--purple)}
    .b-closed{background:rgba(148,163,184,.12);color:var(--slate);border:1px solid rgba(148,163,184,.2)} .b-closed::before{background:var(--slate)}
    .b-rejected{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.25)} .b-rejected::before{background:var(--red)}
    .b-defaulted{background:rgba(239,68,68,.2);color:#FCA5A5;border:1px solid rgba(239,68,68,.4)} .b-defaulted::before{background:#FCA5A5}
    .b-draft{background:rgba(148,163,184,.08);color:var(--slate);border:1px solid rgba(148,163,184,.15)} .b-draft::before{background:var(--slate)}

    /* ── Utilities ── */
    .mono{font-family:'DM Mono',monospace}
    .tc{color:var(--teal-lt)} .ta{color:var(--amber)} .tg{color:var(--green)} .tr{color:var(--red)} .ts{color:var(--slate)} .tw{color:var(--white)} .tp{color:var(--purple)}
    .sm{font-size:12px} .xs{font-size:11px} .f6{font-weight:600} .f7{font-weight:700}
    .flex{display:flex} .aic{align-items:center} .jb{justify-content:space-between} .jc{justify-content:center} .col{flex-direction:column}
    .g6{gap:6px} .g8{gap:8px} .g10{gap:10px} .g12{gap:12px} .g16{gap:16px}
    .mt4{margin-top:4px} .mt8{margin-top:8px} .mt12{margin-top:12px} .mt16{margin-top:16px} .mt20{margin-top:20px}
    .mb8{margin-bottom:8px} .mb12{margin-bottom:12px} .mb16{margin-bottom:16px} .mb20{margin-bottom:20px}
    .w100{width:100%}

    /* ── Loan detail layout ── */
    .loan-layout{display:grid;grid-template-columns:340px 1fr;gap:20px}
    .card{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:14px;overflow:hidden}
    .card-hd{padding:14px 18px;background:var(--navy-mid);border-bottom:1px solid var(--navy-line);display:flex;align-items:center;justify-content:space-between}
    .card-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--slate)}
    .card-body{padding:18px}

    /* Loan hero panel */
    .loan-hero{background:linear-gradient(160deg,#0A3352,#0D1B2A 75%);padding:24px 20px;position:relative;overflow:hidden}
    .loan-hero::before{content:'';position:absolute;top:-30px;right:-30px;width:130px;height:130px;border-radius:50%;background:rgba(11,143,172,.07)}
    .loan-hero::after{content:'';position:absolute;bottom:-40px;left:-20px;width:110px;height:110px;border-radius:50%;background:rgba(245,166,35,.05)}
    .loan-num-hero{font-family:'DM Mono',monospace;font-size:12px;color:var(--teal-lt);margin-bottom:6px;position:relative;z-index:1}
    .loan-amt-hero{font-family:'DM Mono',monospace;font-size:32px;font-weight:500;color:var(--white);margin-bottom:4px;position:relative;z-index:1}
    .loan-product-hero{font-size:12px;color:var(--slate);position:relative;z-index:1;margin-bottom:12px}

    /* Status timeline */
    .timeline{padding:0 2px}
    .tl-step{display:flex;gap:12px;position:relative;padding-bottom:20px}
    .tl-step:last-child{padding-bottom:0}
    .tl-step::before{content:'';position:absolute;left:11px;top:24px;bottom:0;width:1px;background:var(--navy-line)}
    .tl-step:last-child::before{display:none}
    .tl-dot{width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;font-weight:700;position:relative;z-index:1}
    .tl-dot.done{background:var(--teal);color:#fff}
    .tl-dot.cur{background:var(--amber);color:#000;box-shadow:0 0 0 3px rgba(245,166,35,.25)}
    .tl-dot.pend{background:var(--navy-line);color:var(--slate)}
    .tl-dot.rej{background:var(--red);color:#fff}
    .tl-label{font-size:13px;font-weight:600;color:var(--white);margin-top:2px}
    .tl-meta{font-size:11px;color:var(--slate);margin-top:2px}

    /* Info rows */
    .info-row{display:flex;justify-content:space-between;align-items:flex-start;padding:8px 0;border-bottom:1px solid rgba(30,52,80,.5)}
    .info-row:last-child{border-bottom:none}
    .info-lbl{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--slate)}
    .info-val{font-size:12.5px;color:var(--white);text-align:right;max-width:170px}
    .info-val.mono{font-family:'DM Mono',monospace}

    /* Profile tabs */
    .ptabs{display:flex;gap:0;border-bottom:1px solid var(--navy-line);padding:0 18px}
    .ptab{padding:10px 14px;font-size:13px;font-weight:600;cursor:pointer;color:var(--slate);border-bottom:2px solid transparent;transition:all .15s;background:none;border-top:none;border-left:none;border-right:none;font-family:'DM Sans',sans-serif}
    .ptab:hover{color:var(--white)}
    .ptab.active{color:var(--teal-lt);border-bottom-color:var(--teal)}

    /* Repayment schedule table */
    .sched-tbl{width:100%;border-collapse:collapse}
    .sched-tbl th{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--slate);text-align:left;padding:10px 14px;border-bottom:1px solid var(--navy-line);background:var(--navy-mid)}
    .sched-tbl td{padding:11px 14px;font-size:12.5px;border-bottom:1px solid rgba(30,52,80,.4);vertical-align:middle}
    .sched-tbl tr:last-child td{border-bottom:none}
    .sched-tbl tr.row-paid td{opacity:.55}
    .sched-tbl tr.row-overdue td{background:rgba(239,68,68,.04)}
    .sched-tbl tr.row-current td{background:rgba(245,166,35,.04)}

    /* Progress bar */
    .prog-bar{height:5px;background:var(--navy-line);border-radius:99px;overflow:hidden}
    .prog-fill{height:100%;border-radius:99px}

    /* ── Application form ── */
    .steps{display:flex;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:4px}
    .step{display:flex;align-items:center;gap:8px;flex-shrink:0}
    .step-n{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;transition:all .2s;flex-shrink:0}
    .step-n.done{background:var(--teal);color:#fff} .step-n.cur{background:var(--amber);color:#000} .step-n.pend{background:var(--navy-line);color:var(--slate)}
    .step-lbl{font-size:12px;font-weight:600;white-space:nowrap}
    .step-lbl.done{color:var(--teal-lt)} .step-lbl.cur{color:var(--amber)} .step-lbl.pend{color:var(--slate)}
    .step-line{flex:1;height:1px;background:var(--navy-line);margin:0 8px;min-width:16px}
    .step-line.done{background:var(--teal)}

    .fsec{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:12px;overflow:hidden;margin-bottom:20px}
    .fsec-hd{padding:14px 20px;background:var(--navy-mid);border-bottom:1px solid var(--navy-line);display:flex;align-items:center;gap:10px}
    .fsec-ic{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:13px}
    .fsec-title{font-size:13px;font-weight:700;color:var(--white)}
    .fsec-body{padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .field{display:flex;flex-direction:column;gap:6px}
    .field.span2{grid-column:span 2}
    label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--slate)}
    .req{color:var(--red);margin-left:2px}
    .finput,.fsel,.ftxtarea{background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:8px;padding:10px 14px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .15s;width:100%}
    .finput:focus,.fsel:focus,.ftxtarea:focus{border-color:var(--teal)}
    .finput::placeholder,.ftxtarea::placeholder{color:var(--slate)}
    .fhint{font-size:11px;color:var(--slate)}
    .form-actions{display:flex;gap:10px;justify-content:flex-end;padding:16px 20px;background:var(--navy-mid);border-top:1px solid var(--navy-line)}

    /* Loan calculator panel */
    .calc-panel{background:linear-gradient(135deg,rgba(11,143,172,.12),rgba(11,143,172,.04));border:1px solid rgba(11,143,172,.25);border-radius:12px;padding:20px;margin-bottom:20px}
    .calc-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--teal-lt);margin-bottom:14px}
    .calc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:14px}
    .calc-item{background:rgba(0,0,0,.2);border-radius:9px;padding:12px}
    .calc-item-val{font-family:'DM Mono',monospace;font-size:18px;font-weight:500;color:var(--white)}
    .calc-item-lbl{font-size:10px;color:var(--slate);text-transform:uppercase;letter-spacing:.08em;font-weight:600;margin-top:3px}

    /* Borrower search dropdown */
    .brw-result{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:8px;margin-top:4px;overflow:hidden}
    .brw-opt{display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;transition:background .1s}
    .brw-opt:hover{background:var(--navy-hover)}
    .brw-av{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0}

    /* Approval/disbursement modal overlay */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);z-index:100;display:flex;align-items:center;justify-content:center;padding:20px}
    .modal{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:16px;width:100%;max-width:520px;overflow:hidden;animation:slideUp .25s ease}
    @keyframes slideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
    .modal-hd{padding:20px 22px;background:var(--navy-mid);border-bottom:1px solid var(--navy-line);display:flex;align-items:center;justify-content:space-between}
    .modal-title{font-family:'Playfair Display',serif;font-size:18px;color:var(--white)}
    .modal-body{padding:22px}
    .modal-foot{padding:16px 22px;background:var(--navy-mid);border-top:1px solid var(--navy-line);display:flex;gap:10px;justify-content:flex-end}

    /* Settlement calculator */
    .settle-box{background:linear-gradient(135deg,rgba(34,197,94,.1),rgba(34,197,94,.04));border:1px solid rgba(34,197,94,.25);border-radius:10px;padding:16px}
    .settle-row{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid rgba(30,52,80,.5)}
    .settle-row:last-child{border-bottom:none}
    .settle-lbl{font-size:12px;color:var(--slate)}
    .settle-val{font-family:'DM Mono',monospace;font-size:13px;font-weight:500;color:var(--white)}
    .settle-val.discount{color:var(--green)}
    .settle-val.total{color:var(--amber);font-size:18px;font-weight:600}

    /* Loan row in list */
    .b-av{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0}
    .b-name{font-size:13.5px;font-weight:600;color:var(--white)}
    .b-sub{font-size:11px;color:var(--slate);font-family:'DM Mono',monospace}

    /* Animations */
    @keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
    .anim{animation:fadeUp .35s ease both}
    .d1{animation-delay:.05s} .d2{animation-delay:.10s} .d3{animation-delay:.15s} .d4{animation-delay:.20s}
    ::-webkit-scrollbar{width:5px} ::-webkit-scrollbar-track{background:transparent} ::-webkit-scrollbar-thumb{background:var(--navy-line);border-radius:99px}
    .toast-msg{position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:999;box-shadow:0 8px 24px rgba(0,0,0,.4);animation:fadeUp .3s ease}
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

@media print {
  body * { visibility: hidden !important; }
  #agreement-doc, #agreement-doc * { visibility: visible !important; }
  #agreement-doc {
    position: fixed !important;
    top: 0 !important; left: 0 !important;
    width: 100% !important;
    margin: 0 !important;
    border: none !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    background: #fff !important;
    color: #111 !important;
    font-size: 12px !important;
  }
  .no-print { display: none !important; }
}
</style>












</head>
<body x-data="app()">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="logo-wrap"><div class="logo-mark">Gracimor</div><div class="logo-sub">Loans Management</div></div>
    <div class="nav-sect">
      <div class="nav-lbl">Main</div>
      <a class="nav-item" href="/dashboard"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>Dashboard</a>
      <a class="nav-item" href="/borrowers"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>Borrowers</a>
      <a class="nav-item active" href="/loans"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Loans <span class="nav-bdg" style="background:var(--amber);color:#000" x-show="(stats.loans?.pending ?? 0) > 0" x-text="stats.loans?.pending ?? 0">0</span></a>
      <a class="nav-item" href="/payments"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>Payments</a>
      <a class="nav-item" href="/collateral"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Collateral</a>
    </div>
    <div class="nav-sect">
      <div class="nav-lbl">Schedule</div>
      <a class="nav-item" href="/calendar"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Calendar<span class="nav-bdg" style="background:var(--amber);color:#000;margin-left:auto" x-show="(stats.due_today?.count ?? 0) > 0" x-text="stats.due_today?.count ?? 0">0</span></a>
      <a class="nav-item" href="/overdue"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Overdue<span class="nav-bdg" style="background:var(--red);margin-left:auto" x-show="(stats.overdue?.total_loans ?? 0) > 0" x-text="stats.overdue?.total_loans ?? 0">0</span></a>
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
      <div>
        <div class="page-title" x-show="view==='list'">Loans</div>
        <div x-show="view==='detail'">
          <div class="breadcrumb"><span @click="view='list'">Loans</span> &nbsp;/&nbsp; <span style="color:var(--white)" x-text="sel?.num"></span></div>
        </div>
        <div class="page-title" x-show="view==='apply'">New Loan Application</div>
      </div>
      <div class="tb-right">
        <template x-if="view==='list'">
          <div class="flex aic g8">
            <button class="btn-g" @click="showToast('Exporting loans list to CSV…','var(--teal)')"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Export</button>
            <button class="btn-p" @click="view='apply';step=1">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>New Loan Application
            </button>
          </div>
        </template>
        <template x-if="view==='detail'">
          <div class="flex aic g8">
            <button class="btn-g" @click="view='list'">← Back</button>
            <template x-if="sel?.status==='pending'||sel?.status==='pending_approval'">
              <div class="flex aic g8">
                <button class="btn-red btn-sm" @click="modal='reject'">✕ Reject</button>
                <button class="btn-amber" @click="modal='approve'">✓ Approve Loan</button>
              </div>
            </template>
            <template x-if="sel?.status==='approved'">
              <button class="btn-green" @click="modal='disburse'">⬆ Disburse Funds</button>
            </template>
            <template x-if="sel?.status==='active'">
              <div class="flex aic g8">
                <button class="btn-g btn-sm" @click="openSettle()">Early Settlement</button>
                <button class="btn-p btn-sm" @click="window.location.href='/payments'">+ Record Payment</button>
              </div>
            </template>
            <button class="btn-g btn-sm" @click="showToast('Generating PDF…','var(--slate)')">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>PDF
            </button>
          </div>
        </template>
        <template x-if="view==='apply'">
          <button class="btn-g" @click="view='list'">✕ Cancel</button>
        </template>
      </div>
    </header>

    <div class="content">

      <!-- ═════════════ LIST ═════════════ -->
      <div x-show="view==='list'" x-transition>

        <!-- Stats -->
        <div class="stats-row anim">
          <div class="m-stat" :class="{' sel-stat':fStatus===''}" @click="fStatus='';doFilter()">
            <div class="m-stat-ic" style="background:rgba(11,143,172,.15)">📋</div>
            <div><div class="m-stat-val" x-text="stats.loans?.total ?? '-'"></div><div class="m-stat-lbl">All Loans</div></div>
          </div>
          <div class="m-stat" :class="{'sel-stat':fStatus==='active'}" @click="fStatus='active';doFilter()">
            <div class="m-stat-ic" style="background:rgba(34,197,94,.15)">✅</div>
            <div><div class="m-stat-val" x-text="stats.loans?.active ?? '-'"></div><div class="m-stat-lbl">Active</div></div>
          </div>
          <div class="m-stat" :class="{'sel-stat':fStatus==='pending'}" @click="fStatus='pending';doFilter()">
            <div class="m-stat-ic" style="background:rgba(245,166,35,.15)">⏳</div>
            <div><div class="m-stat-val" x-text="stats.loans?.pending ?? '-'"></div><div class="m-stat-lbl">Pending Approval</div></div>
          </div>
          <div class="m-stat" :class="{'sel-stat':fStatus==='overdue'}" @click="fStatus='overdue';doFilter()">
            <div class="m-stat-ic" style="background:rgba(239,68,68,.15)">⚠️</div>
            <div><div class="m-stat-val" x-text="stats.loans?.overdue ?? '-'"></div><div class="m-stat-lbl">Overdue</div></div>
          </div>
          <div class="m-stat" :class="{'sel-stat':fStatus==='closed'}" @click="fStatus='closed';doFilter()">
            <div class="m-stat-ic" style="background:rgba(148,163,184,.15)">🔒</div>
            <div><div class="m-stat-val" x-text="stats.loans?.closed ?? '-'"></div><div class="m-stat-lbl">Closed</div></div>
          </div>
        </div>

        <!-- Filter bar -->
        <div class="filter-bar anim d1">
          <div class="srch-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input class="srch" type="text" placeholder="Search loan number, borrower name, NRC…" x-model="q" @input="doFilter()">
          </div>
          <select class="sel" x-model="fStatus" @change="doFilter()">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="overdue">Overdue</option>
            <option value="closed">Closed</option>
            <option value="rejected">Rejected</option>
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
          <table class="dtbl">
            <thead>
              <tr>
                <th>Loan / Borrower</th>
                <th>Product</th>
                <th>Principal ↕</th>
                <th>Interest</th>
                <th>Term</th>
                <th>Collateral</th>
                <th>Outstanding ↕</th>
                <th>Status</th>
                <th>Applied ↕</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <template x-for="l in pagedRows" :key="l.id">
                <tr @click="open(l)">
                  <td>
                    <div class="flex aic g10">
                      <div class="b-av" :style="`background:linear-gradient(135deg,${l.c1},${l.c2})`" x-text="l.ini"></div>
                      <div>
                        <div class="b-name" x-text="l.num"></div>
                        <div class="b-sub" x-text="l.borrower"></div>
                      </div>
                    </div>
                  </td>
                  <td class="xs ts" x-text="l.product"></td>
                  <td class="mono f6 tw sm" x-text="l.principal"></td>
                  <td class="mono sm tc" x-text="l.rate"></td>
                  <td class="xs ts" x-text="l.term"></td>
                  <td>
                    <span x-show="l.coll==='vehicle'" style="font-size:11px;background:var(--navy-line);padding:2px 7px;border-radius:5px;color:var(--slate-lt)">🚗 Vehicle</span>
                    <span x-show="l.coll==='land'"    style="font-size:11px;background:var(--navy-line);padding:2px 7px;border-radius:5px;color:var(--slate-lt)">🏞️ Land</span>
                  </td>
                  <td>
                    <span x-show="l.owed" class="mono f6 sm" :style="`color:${l.status==='overdue'?'var(--red)':'var(--white)'}`" x-text="l.owed"></span>
                    <span x-show="!l.owed" class="xs ts">—</span>
                  </td>
                  <td><span class="badge" :class="`b-${l.statusClass}`" x-text="l.statusLabel"></span></td>
                  <td class="xs ts" x-text="l.date"></td>
                  <td><button class="btn-g btn-sm" @click.stop="open(l)">View →</button></td>
                </tr>
              </template>
            </tbody>
          </table>
          <div class="pgn">
            <div class="pgn-info">Showing <strong x-text="Math.min(page*perPage,rows.length)"></strong> of <strong x-text="rows.length"></strong> loans</div>
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

      <!-- ═════════════ LOAN DETAIL ═════════════ -->
      <div x-show="view==='detail'" x-transition>
        <template x-if="sel">
          <div class="loan-layout">

            <!-- Left panel -->
            <div class="flex col g16">

              <!-- Hero card -->
              <div class="card anim">
                <div class="loan-hero">
                  <div class="loan-num-hero" x-text="sel.num"></div>
                  <div class="loan-amt-hero" x-text="sel.principal"></div>
                  <div class="loan-product-hero" x-text="sel.product + ' · ' + sel.coll.charAt(0).toUpperCase()+sel.coll.slice(1)+'-Backed'"></div>
                  <span class="badge" :class="`b-${sel.statusClass||sel.status}`" x-text="sel.statusLabel||sel.status"></span>
                </div>
                <div class="card-body">
                  <div class="info-row"><div class="info-lbl">Borrower</div><div class="info-val f6" x-text="sel.borrower"></div></div>
                  <div class="info-row"><div class="info-lbl">Interest Rate</div><div class="info-val mono tc" x-text="sel.rate"></div></div>
                  <div class="info-row"><div class="info-lbl">Term</div><div class="info-val" x-text="sel.term"></div></div>
                  <div class="info-row"><div class="info-lbl">Monthly Instalment</div><div class="info-val mono" x-text="sel.monthly"></div></div>
                  <div class="info-row"><div class="info-lbl">Total Interest</div><div class="info-val mono" x-text="sel.totalInterest"></div></div>
                  <div class="info-row"><div class="info-lbl">Total Repayable</div><div class="info-val mono f6 ta" x-text="sel.totalRepay"></div></div>
                  <div class="info-row"><div class="info-lbl">Processing Fee</div><div class="info-val mono" x-text="sel.fee"></div></div>
                  <div class="info-row"><div class="info-lbl">Applied By</div><div class="info-val" x-text="sel.officer"></div></div>
                  <div class="info-row"><div class="info-lbl">Applied Date</div><div class="info-val" x-text="sel.date"></div></div>
                  <template x-if="sel.disburseDate">
                    <div class="info-row"><div class="info-lbl">Disbursed</div><div class="info-val" x-text="sel.disburseDate"></div></div>
                  </template>
                  <template x-if="sel.maturity">
                    <div class="info-row"><div class="info-lbl">Maturity Date</div><div class="info-val" x-text="sel.maturity"></div></div>
                  </template>
                </div>
              </div>

              <!-- LTV panel -->
              <div class="card anim d2">
                <div class="card-hd"><span class="card-title">Collateral & LTV</span></div>
                <div class="card-body">
                  <div class="flex aic g10 mb12">
                    <div style="width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px" :style="`background:${sel.coll==='vehicle'?'rgba(11,143,172,.15)':'rgba(34,197,94,.12)'}`" x-text="sel.coll==='vehicle'?'🚗':'🏞️'"></div>
                    <div>
                      <div class="f6 tw sm" x-text="sel.collateral"></div>
                      <div class="xs ts mt4" x-text="'Est. Value: ' + sel.collateralVal"></div>
                    </div>
                  </div>
                  <div class="flex jb aic mb8">
                    <div class="xs ts">Loan-to-Value Ratio</div>
                    <div class="mono f6 sm" :class="sel.ltv>80?'tr':'tg'" x-text="sel.ltv + '%'"></div>
                  </div>
                  <div class="prog-bar">
                    <div class="prog-fill" :style="`width:${Math.min(sel.ltv,100)}%;background:${sel.ltv>80?'var(--red)':sel.ltv>60?'var(--amber)':'var(--green)'}`"></div>
                  </div>
                  <div class="xs ts mt8">Max allowed: 80% LTV</div>
                </div>
              </div>

              <!-- Status timeline -->
              <div class="card anim d3">
                <div class="card-hd"><span class="card-title">Loan Lifecycle</span></div>
                <div class="card-body">
                  <div class="timeline">
                    <template x-for="(tl, i) in sel.timeline" :key="i">
                      <div class="tl-step">
                        <div class="tl-dot" :class="tl.state" x-text="tl.state==='done'?'✓':(tl.state==='rej'?'✕':String(i+1))"></div>
                        <div>
                          <div class="tl-label" x-text="tl.label"></div>
                          <div class="tl-meta" x-text="tl.meta"></div>
                        </div>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right panel (tabs) -->
            <div class="anim d2">
              <div class="card">
                <div class="ptabs">
                  <button class="ptab" :class="{active:ptab==='schedule'}" @click="ptab='schedule'">Repayment Schedule</button>
                  <button class="ptab" :class="{active:ptab==='payments'}" @click="ptab='payments'">Payments</button>
                  <button class="ptab" :class="{active:ptab==='penalties'}" @click="ptab='penalties'">Penalties</button>
                  <button class="ptab" :class="{active:ptab==='docs'}" @click="ptab='docs'">Documents</button>
                  <button class="ptab" :class="{active:ptab==='agreement'}" @click="ptab='agreement'">Agreement</button>
                </div>

                <div style="padding:18px">

                  <!-- Schedule tab -->
                  <div x-show="ptab==='schedule'">
                    <!-- Progress summary -->
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:18px">
                      <div style="background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:9px;padding:12px;text-align:center">
                        <div class="mono f6 tw" style="font-size:17px" x-text="sel.principal"></div><div class="xs ts mt4">Principal</div>
                      </div>
                      <div style="background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:9px;padding:12px;text-align:center">
                        <div class="mono f6 tg" style="font-size:17px" x-text="sel.paid||'K 0'"></div><div class="xs ts mt4">Paid</div>
                      </div>
                      <div style="background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:9px;padding:12px;text-align:center">
                        <div class="mono f6 ta" style="font-size:17px" x-text="sel.owed||'—'"></div><div class="xs ts mt4">Outstanding</div>
                      </div>
                      <div style="background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:9px;padding:12px;text-align:center">
                        <div class="mono f6" :class="sel.penalty&&sel.penalty!=='K 0'?'tr':'ts'" style="font-size:17px" x-text="sel.penalty||'K 0'"></div><div class="xs ts mt4">Penalties</div>
                      </div>
                    </div>

                    <!-- Progress bar -->
                    <div x-show="sel.paidPct" class="mb16">
                      <div class="flex jb aic mb8">
                        <div class="xs ts">Repayment Progress</div>
                        <div class="xs f6 tg" x-text="sel.paidPct + '% paid'"></div>
                      </div>
                      <div class="prog-bar" style="height:8px">
                        <div class="prog-fill" :style="`width:${sel.paidPct||0}%;background:var(--teal)`"></div>
                      </div>
                    </div>

                    <!-- Schedule table -->
                    <div style="overflow-x:auto;border-radius:10px;border:1px solid var(--navy-line)">
                      <table class="sched-tbl">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Due Date</th>
                            <th>Principal</th>
                            <th>Interest</th>
                            <th>Total Due</th>
                            <th>Penalty</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <template x-for="row in sel.schedule" :key="row.n">
                            <tr :class="`row-${row.cls}`">
                              <td class="mono xs ts" x-text="row.n"></td>
                              <td class="xs" :class="row.cls==='overdue'?'tr':row.cls==='current'?'ta':'ts'" x-text="row.due"></td>
                              <td class="mono xs" x-text="row.prin"></td>
                              <td class="mono xs tc" x-text="row.int"></td>
                              <td class="mono xs f6 tw" x-text="row.total"></td>
                              <td class="mono xs" :class="row.pen!=='—'?'tr':'ts'" x-text="row.pen"></td>
                              <td class="mono xs tg" x-text="row.paid"></td>
                              <td class="mono xs" :class="row.bal==='K 0'?'ts':'ta'" x-text="row.bal"></td>
                              <td><span class="badge" :class="`b-${row.cls==='paid'?'active':row.cls==='overdue'?'rejected':row.cls==='current'?'pending':'draft'}`" x-text="row.cls==='paid'?'Paid':row.cls==='overdue'?'Overdue':row.cls==='current'?'Due':'Pending'"></span></td>
                            </tr>
                          </template>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <!-- Payments tab -->
                  <div x-show="ptab==='payments'">
                    <div class="flex jb aic mb16">
                      <div class="sm ts" x-text="(sel.payments||[]).length + ' payment records'"></div>
                      <button class="btn-p btn-sm" @click="window.location.href='/payments'">+ Record Payment</button>
                    </div>
                    <template x-if="!sel.payments?.length">
                      <div style="text-align:center;padding:40px;color:var(--slate)">No payments recorded yet</div>
                    </template>
                    <template x-for="p in (sel.payments||[])" :key="p.id">
                      <div style="display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid rgba(30,52,80,.4)">
                        <div style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0" :style="`background:${p.bg}`" x-text="p.ic"></div>
                        <div>
                          <div class="mono sm tc" x-text="p.rc"></div>
                          <div class="xs ts" x-text="p.dt + ' · ' + p.mth"></div>
                        </div>
                        <span class="badge xs" :class="p.bdg" x-text="p.type" style="margin-left:auto"></span>
                        <div style="text-align:right">
                          <div class="mono f6 tg sm" x-text="p.amt"></div>
                          <div class="xs ts" x-text="'Bal: '+p.bal"></div>
                        </div>
                        <button class="btn-g btn-sm" style="margin-left:8px" @click="showToast('Opening print dialog…','var(--slate)')">🖨️</button>
                      </div>
                    </template>
                  </div>

                  <!-- Penalties tab -->
                  <div x-show="ptab==='penalties'">
                    <div class="flex jb aic mb16">
                      <div class="sm ts">Penalty records</div>
                      <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:8px;padding:8px 14px;text-align:center">
                        <div class="mono f6 tr" style="font-size:16px" x-text="sel.penalty||'K 0'"></div>
                        <div class="xs ts mt4">Total penalty balance</div>
                      </div>
                    </div>
                    <template x-if="!sel.penaltyRows?.length">
                      <div style="text-align:center;padding:40px;color:var(--slate)">No penalties applied</div>
                    </template>
                    <template x-for="pr in (sel.penaltyRows||[])" :key="pr.id">
                      <div style="display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid rgba(30,52,80,.4)">
                        <div style="width:8px;height:8px;border-radius:50%;background:var(--red);flex-shrink:0"></div>
                        <div style="flex:1">
                          <div class="sm tw f6" x-text="'Penalty on Instalment #' + pr.inst"></div>
                          <div class="xs ts mt4" x-text="'Applied: ' + pr.date + ' · ' + pr.days + ' days overdue'"></div>
                        </div>
                        <div class="mono f6 tr sm" x-text="pr.amt"></div>
                        <span class="badge" :class="pr.paid?'b-active':'b-rejected'" x-text="pr.paid?'Paid':'Unpaid'"></span>
                      </div>
                    </template>
                  </div>

                  <!-- Agreement tab -->
                  <div x-show="ptab==='agreement'" id="loan-agreement-area">
                    <div class="flex jb aic mb16 no-print">
                      <div class="sm ts">Loan Agreement — printable document</div>
                      <button class="btn-p btn-sm" @click="printAgreement()">🖨️ Save as PDF</button>
                    </div>

                    <!-- Agreement Document -->
                    <div id="agreement-doc" style="background:#fff;color:#111;font-family:Georgia,serif;padding:40px;border-radius:10px;border:1px solid var(--navy-line);line-height:1.6;font-size:13.5px">

                      <!-- Header -->
                      <div style="text-align:center;border-bottom:3px double #0B8FAC;padding-bottom:20px;margin-bottom:24px">
                        <div style="font-size:26px;font-weight:700;letter-spacing:.04em;color:#0B8FAC">GRACIMOR</div>
                        <div style="font-size:11px;letter-spacing:.2em;text-transform:uppercase;color:#444;margin-top:2px">Loans Management System · Zambia</div>
                        <div style="font-size:20px;font-weight:700;margin-top:12px;color:#111">LOAN AGREEMENT</div>
                        <div style="font-size:12px;color:#555;margin-top:4px">
                          Agreement Ref: <span style="font-family:monospace;font-weight:700" x-text="sel.num"></span>
                          &nbsp;|&nbsp; Date: <span x-text="sel.agreementDate"></span>
                        </div>
                      </div>

                      <!-- Parties -->
                      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
                        <div style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:8px;padding:16px">
                          <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#0B8FAC;margin-bottom:8px">LENDER</div>
                          <div style="font-weight:700;font-size:15px">GRACIMOR Financial Services</div>
                          <div style="font-size:12px;color:#444;margin-top:4px">Lusaka, Zambia</div>
                          <div style="font-size:12px;color:#444">Reg. No: GFS/ZM/2024</div>
                          <div style="font-size:12px;color:#444">Licensed Money Lender</div>
                        </div>
                        <div style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:8px;padding:16px">
                          <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#0B8FAC;margin-bottom:8px">BORROWER</div>
                          <div style="font-weight:700;font-size:15px" x-text="sel.borrower"></div>
                          <div style="font-size:12px;color:#444;margin-top:4px">NRC: <span x-text="sel.borrowerNrc"></span></div>
                          <div style="font-size:12px;color:#444">Phone: <span x-text="sel.borrowerPhone"></span></div>
                          <div style="font-size:12px;color:#444">Address: <span x-text="sel.borrowerAddress"></span></div>
                          <template x-if="sel.borrowerEmployer !== '—'">
                            <div style="font-size:12px;color:#444">Employer: <span x-text="sel.borrowerEmployer"></span></div>
                          </template>
                        </div>
                      </div>

                      <!-- Loan Terms Table -->
                      <div style="margin-bottom:24px">
                        <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;border-bottom:2px solid #0B8FAC;padding-bottom:6px;margin-bottom:12px;color:#0B8FAC">LOAN TERMS</div>
                        <table style="width:100%;border-collapse:collapse;font-size:13px">
                          <tbody>
                            <tr style="background:#f8f9fa">
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:600;width:35%">Loan Number</td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-family:monospace" x-text="sel.num"></td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:600;width:35%">Loan Product</td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6" x-text="sel.product"></td>
                            </tr>
                            <tr>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:600">Principal Amount</td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:700;color:#0B8FAC" x-text="sel.principal"></td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:600">Loan Term</td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6" x-text="sel.term"></td>
                            </tr>
                            <tr style="background:#f8f9fa">
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:600">Flat Interest Rate</td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6" x-text="sel.rawRate + '% (flat rate for ' + sel.rawTerm + ' month term)'"></td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:600">Monthly Instalment</td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:700" x-text="sel.monthly"></td>
                            </tr>
                            <tr>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:600">Total Interest Charged</td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;color:#c0392b" x-text="sel.totalInterest"></td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:600">Total Repayable</td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:700;color:#16a085" x-text="sel.totalRepay"></td>
                            </tr>
                            <tr style="background:#f8f9fa">
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:600">Processing Fee</td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6" x-text="sel.fee"></td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:600">Disbursement Date</td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6" x-text="sel.disburseDate || '—'"></td>
                            </tr>
                            <tr>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:600">Maturity Date</td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6" x-text="sel.maturity || '—'"></td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6;font-weight:600">Collateral</td>
                              <td style="padding:8px 12px;border:1px solid #dee2e6" x-text="sel.collateral + ' (Est. ' + sel.collateralVal + ')'"></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <!-- Repayment Schedule -->
                      <div style="margin-bottom:24px">
                        <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;border-bottom:2px solid #0B8FAC;padding-bottom:6px;margin-bottom:12px;color:#0B8FAC">REPAYMENT SCHEDULE</div>
                        <table style="width:100%;border-collapse:collapse;font-size:12px">
                          <thead>
                            <tr style="background:#0B8FAC;color:#fff">
                              <th style="padding:8px 10px;text-align:left;border:1px solid #0B8FAC">#</th>
                              <th style="padding:8px 10px;text-align:left;border:1px solid #0B8FAC">Due Date</th>
                              <th style="padding:8px 10px;text-align:right;border:1px solid #0B8FAC">Principal</th>
                              <th style="padding:8px 10px;text-align:right;border:1px solid #0B8FAC">Interest</th>
                              <th style="padding:8px 10px;text-align:right;border:1px solid #0B8FAC">Total Due</th>
                              <th style="padding:8px 10px;text-align:right;border:1px solid #0B8FAC">Balance</th>
                            </tr>
                          </thead>
                          <tbody>
                            <template x-for="(row, idx) in sel.schedule" :key="row.n">
                              <tr :style="idx%2===0?'background:#f8f9fa':''">
                                <td style="padding:7px 10px;border:1px solid #dee2e6;font-family:monospace" x-text="row.n"></td>
                                <td style="padding:7px 10px;border:1px solid #dee2e6" x-text="row.due"></td>
                                <td style="padding:7px 10px;border:1px solid #dee2e6;text-align:right;font-family:monospace" x-text="row.prin"></td>
                                <td style="padding:7px 10px;border:1px solid #dee2e6;text-align:right;font-family:monospace" x-text="row.int"></td>
                                <td style="padding:7px 10px;border:1px solid #dee2e6;text-align:right;font-family:monospace;font-weight:700" x-text="row.total"></td>
                                <td style="padding:7px 10px;border:1px solid #dee2e6;text-align:right;font-family:monospace" x-text="row.bal"></td>
                              </tr>
                            </template>
                          </tbody>
                          <tfoot>
                            <tr style="background:#e8f5e9;font-weight:700">
                              <td colspan="4" style="padding:8px 10px;border:1px solid #dee2e6;text-align:right">TOTAL REPAYABLE</td>
                              <td style="padding:8px 10px;border:1px solid #dee2e6;text-align:right;font-family:monospace;color:#16a085" x-text="sel.totalRepay"></td>
                              <td style="padding:8px 10px;border:1px solid #dee2e6"></td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>

                      <!-- Terms and Conditions -->
                      <div style="margin-bottom:24px">
                        <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;border-bottom:2px solid #0B8FAC;padding-bottom:6px;margin-bottom:12px;color:#0B8FAC">TERMS AND CONDITIONS</div>
                        <ol style="font-size:12px;color:#333;padding-left:18px;margin:0">
                          <li style="margin-bottom:6px">The Borrower agrees to repay the loan according to the repayment schedule above. All instalments are due on the specified dates.</li>
                          <li style="margin-bottom:6px">Interest is charged as a flat rate applied to the original principal for the full contracted term. The interest rate tier is determined by the loan duration (1 month: 10%, 2 months: 18%, 3 months: 28%, 4 months: 38%).</li>
                          <li style="margin-bottom:6px">A penalty of 5% of the monthly instalment shall be applied for each month an instalment remains unpaid beyond the due date.</li>
                          <li style="margin-bottom:6px">Early settlement is permitted. The interest charged on early settlement shall be based on the rate applicable to the number of months the loan was actually held, not the original contracted term.</li>
                          <li style="margin-bottom:6px">The collateral listed in this agreement is pledged as security for the loan. The Lender reserves the right to realise the collateral in the event of default.</li>
                          <li style="margin-bottom:6px">The Borrower must notify the Lender of any change in employment, address, or contact details within 7 days of such change.</li>
                          <li style="margin-bottom:6px">The Lender reserves the right to declare this loan in default and demand immediate full repayment if the Borrower fails to make two or more consecutive instalments.</li>
                          <li style="margin-bottom:6px">This agreement shall be governed by the laws of the Republic of Zambia.</li>
                        </ol>
                      </div>

                      <!-- Signatures -->
                      <div style="margin-bottom:24px">
                        <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;border-bottom:2px solid #0B8FAC;padding-bottom:6px;margin-bottom:20px;color:#0B8FAC">SIGNATURES</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px">
                          <div>
                            <div style="font-size:12px;font-weight:600;margin-bottom:4px">BORROWER</div>
                            <div style="border-bottom:1px solid #555;height:48px;margin-bottom:6px"></div>
                            <div style="font-size:12px;color:#333" x-text="sel.borrower"></div>
                            <div style="font-size:11px;color:#666">NRC: <span x-text="sel.borrowerNrc"></span></div>
                            <div style="display:flex;gap:20px;margin-top:12px">
                              <div style="flex:1">
                                <div style="font-size:11px;color:#666;margin-bottom:2px">Date</div>
                                <div style="border-bottom:1px solid #aaa;height:24px"></div>
                              </div>
                              <div style="flex:1">
                                <div style="font-size:11px;color:#666;margin-bottom:2px">Witness</div>
                                <div style="border-bottom:1px solid #aaa;height:24px"></div>
                              </div>
                            </div>
                          </div>
                          <div>
                            <div style="font-size:12px;font-weight:600;margin-bottom:4px">FOR GRACIMOR FINANCIAL SERVICES</div>
                            <div style="border-bottom:1px solid #555;height:48px;margin-bottom:6px"></div>
                            <div style="font-size:12px;color:#333" x-text="sel.officer"></div>
                            <div style="font-size:11px;color:#666">Authorised Lending Officer</div>
                            <div style="display:flex;gap:20px;margin-top:12px">
                              <div style="flex:1">
                                <div style="font-size:11px;color:#666;margin-bottom:2px">Date</div>
                                <div style="border-bottom:1px solid #aaa;height:24px"></div>
                              </div>
                              <div style="flex:1">
                                <div style="font-size:11px;color:#666;margin-bottom:2px">Official Stamp</div>
                                <div style="border-bottom:1px solid #aaa;height:24px"></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Footer -->
                      <div style="border-top:1px solid #dee2e6;padding-top:14px;text-align:center;font-size:11px;color:#888">
                        <div>This document was generated by the GRACIMOR Loans Management System.</div>
                        <div style="margin-top:2px">Agreement Ref: <span style="font-family:monospace" x-text="sel.num"></span> &nbsp;|&nbsp; Generated: <span x-text="new Date().toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'})"></span></div>
                      </div>

                    </div><!-- /agreement-doc -->
                  </div><!-- /agreement tab -->

                  <!-- Docs tab -->
                  <div x-show="ptab==='docs'">
                    <div class="flex jb aic mb16">
                      <div class="sm ts">Loan documents</div>
                      <button class="btn-p btn-sm" @click="showToast('File upload coming soon','var(--slate)')">+ Upload</button>
                    </div>
                    <template x-for="d in (sel.docs||[])" :key="d.id">
                      <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:9px;margin-bottom:8px;cursor:pointer" @mouseover="$el.style.borderColor='rgba(11,143,172,.35)'" @mouseleave="$el.style.borderColor='var(--navy-line)'">
                        <div style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0" :style="`background:${d.bg}`" x-text="d.ic"></div>
                        <div style="flex:1">
                          <div class="f6 tw sm" x-text="d.name"></div>
                          <div class="xs ts mt4" x-text="d.meta"></div>
                        </div>
                        <div class="xs tc f6">⬇ Download</div>
                      </div>
                    </template>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- ═════════════ APPLICATION FORM ═════════════ -->
      <div x-show="view==='apply'" x-transition>

        <!-- Steps -->
        <div class="steps anim">
          <div class="step">
            <div class="step-n" :class="step>1?'done':(step===1?'cur':'pend')" x-text="step>1?'✓':'1'"></div>
            <div class="step-lbl" :class="step>1?'done':(step===1?'cur':'pend')">Select Borrower</div>
          </div>
          <div class="step-line" :class="step>1?'done':''"></div>
          <div class="step">
            <div class="step-n" :class="step>2?'done':(step===2?'cur':'pend')" x-text="step>2?'✓':'2'"></div>
            <div class="step-lbl" :class="step>2?'done':(step===2?'cur':'pend')">Loan Terms</div>
          </div>
          <div class="step-line" :class="step>2?'done':''"></div>
          <div class="step">
            <div class="step-n" :class="step>3?'done':(step===3?'cur':'pend')" x-text="step>3?'✓':'3'"></div>
            <div class="step-lbl" :class="step>3?'done':(step===3?'cur':'pend')">Collateral</div>
          </div>
          <div class="step-line" :class="step>3?'done':''"></div>
          <div class="step">
            <div class="step-n" :class="step===4?'cur':'pend'">4</div>
            <div class="step-lbl" :class="step===4?'cur':'pend'">Review & Submit</div>
          </div>
        </div>

        <!-- Step 1: Select Borrower -->
        <div x-show="step===1" class="anim">
          <div class="fsec">
            <div class="fsec-hd"><div class="fsec-ic" style="background:rgba(11,143,172,.15)">👤</div><div class="fsec-title">Select Borrower</div></div>
            <div style="padding:20px">
              <div class="field mb16">
                <label>Search Borrower <span class="req">*</span></label>
                <input class="finput" type="text" placeholder="Type name, NRC, or borrower number…" x-model="f.borrowerQ" @input="searchBorrowers()">
                <div class="fhint">The borrower must already be registered in the system</div>
              </div>

              <!-- Borrower results -->
              <div x-show="borrowerResults.length>0" class="brw-result mb16">
                <template x-for="b in borrowerResults" :key="b.id">
                  <div class="brw-opt" @click="selectBorrower(b)">
                    <div class="brw-av" :style="`background:linear-gradient(135deg,${b.c1},${b.c2})`" x-text="b.ini"></div>
                    <div>
                      <div class="f6 tw sm" x-text="b.name"></div>
                      <div class="xs ts mono" x-text="b.bnum + ' · NRC: ' + b.nrc"></div>
                    </div>
                    <div style="margin-left:auto">
                      <span class="badge xs b-active">Available</span>
                    </div>
                  </div>
                </template>
              </div>

              <!-- Selected borrower card -->
              <div x-show="f.borrower" style="background:rgba(11,143,172,.08);border:1px solid rgba(11,143,172,.25);border-radius:10px;padding:16px">
                <div class="flex aic jb mb12">
                  <div class="xs tc f7" style="text-transform:uppercase;letter-spacing:.1em">Selected Borrower</div>
                  <button class="xs tc" style="background:none;border:none;cursor:pointer;color:var(--slate)" @click="f.borrower=null;f.borrowerQ='';f.collateralId=null;f.collateralVal=0;f.selectedCollateral=null;borrowerCollaterals=[];loadingCollaterals=false">Change</button>
                </div>
                <div class="flex aic g12">
                  <div class="brw-av" :style="`background:linear-gradient(135deg,${f.borrower?.c1},${f.borrower?.c2})`" x-text="f.borrower?.ini" style="width:40px;height:40px;font-size:14px"></div>
                  <div>
                    <div class="f6 tw" x-text="f.borrower?.name"></div>
                    <div class="xs ts mono mt4" x-text="f.borrower?.bnum + ' · NRC: ' + f.borrower?.nrc"></div>
                  </div>
                </div>
                <!-- Collateral options -->
                <div class="mt12">
                  <div class="xs ts mb8">Available Collateral:</div>
                  <div x-show="loadingCollaterals" class="xs ts" style="padding:8px 0">Loading collateral…</div>
                  <div x-show="!loadingCollaterals && !borrowerCollaterals.length" class="xs ts" style="padding:8px 0;color:var(--amber)">No available collateral found for this borrower.</div>
                  <div class="flex g8" style="flex-wrap:wrap">
                    <template x-for="ca in borrowerCollaterals" :key="ca.id">
                      <div style="background:var(--navy-line);border-radius:7px;padding:8px 12px;cursor:pointer;border:1px solid var(--navy-line);transition:all .15s"
                           :style="f.collateralId===ca.id?'border-color:var(--teal);background:rgba(11,143,172,.12)':''"
                           @click="f.collateralId=ca.id; f.collateralVal=ca.estimated_value; f.selectedCollateral=ca; calcLoan()">
                        <div class="xs f6 tw" x-text="(ca.asset_type==='vehicle'?'🚗 ':'🏞️ ') + ca.display_label"></div>
                        <div class="xs ts mt4" x-text="'Value: K ' + Number(ca.estimated_value||0).toLocaleString()"></div>
                      </div>
                    </template>
                  </div>
                </div>
              </div>

              <!-- No borrower yet -->
              <div x-show="!f.borrower && !borrowerResults.length" style="text-align:center;padding:30px;color:var(--slate);font-size:13px;border:2px dashed var(--navy-line);border-radius:10px">
                Start typing to search for a registered borrower
              </div>
            </div>
            <div class="form-actions">
              <button class="btn-g" @click="view='list'">Cancel</button>
              <button class="btn-p" :disabled="!f.borrower" :style="!f.borrower?'opacity:.5;cursor:not-allowed':''" @click="step=2">Next: Loan Terms →</button>
            </div>
          </div>
        </div>

        <!-- Step 2: Loan Terms -->
        <div x-show="step===2" class="anim">
          <div style="display:grid;grid-template-columns:1fr 380px;gap:20px">
            <div class="fsec">
              <div class="fsec-hd"><div class="fsec-ic" style="background:rgba(245,166,35,.15)">📊</div><div class="fsec-title">Loan Terms & Configuration</div></div>
              <div class="fsec-body">
                <div class="field">
                  <label>Loan Product <span class="req">*</span></label>
                  <select class="fsel" x-model="f.productId" @change="calcLoan()">
                    <option value="">Select product</option>
                    <template x-for="p in loanProducts" :key="p.id">
                      <option :value="p.id" x-text="p.name"></option>
                    </template>
                  </select>
                </div>
                <div class="field">
                  <label>Loan Amount (K) <span class="req">*</span></label>
                  <input class="finput" type="number" placeholder="0.00" x-model="f.amount" @input="calcLoan()">
                  <div class="fhint" x-show="f.selectedCollateral" x-text="f.selectedCollateral ? 'Max 80% LTV: K ' + Number((f.selectedCollateral.estimated_value||0)*0.8).toLocaleString(undefined,{maximumFractionDigits:0}) + ' (' + f.selectedCollateral.asset_type + ' value K ' + Number(f.selectedCollateral.estimated_value||0).toLocaleString() + ')' : ''"></div>
                </div>
                <div class="field">
                  <label>Loan Term <span class="req">*</span></label>
                  <select class="fsel" x-model="f.term" @change="calcLoan()">
                    <option value="">Select duration</option>
                    <option value="1">1 Month — 10% flat</option>
                    <option value="2">2 Months — 18% flat</option>
                    <option value="3">3 Months — 28% flat</option>
                    <option value="4">4 Months — 38% flat</option>
                  </select>
                </div>
                <div class="field" x-show="f.term">
                  <label>Interest Rate</label>
                  <div class="finput" style="background:rgba(11,143,172,.08);color:var(--teal-lt);cursor:default" x-text="f.term ? ({1:'10%',2:'18%',3:'28%',4:'38%'}[f.term] || '—') + ' flat (fixed for this term)' : '—'"></div>
                </div>
                <div class="field">
                  <label>Processing Fee (K)</label>
                  <input class="finput" type="number" placeholder="0.00" x-model="f.fee" @input="calcLoan()">
                  <div class="fhint">Fixed fee charged on disbursement</div>
                </div>
                <div class="field">
                  <label>First Repayment Date <span class="req">*</span></label>
                  <input class="finput" type="date" x-model="f.firstDate">
                </div>
                <div class="field">
                  <label>Disbursement Method</label>
                  <select class="fsel" x-model="f.disburseMethod">
                    <option>Cash</option>
                    <option>Bank Transfer</option>
                    <option>Mobile Money</option>
                    <option>Cheque</option>
                  </select>
                </div>
                <div class="field span2">
                  <label>Loan Purpose</label>
                  <textarea class="ftxtarea" placeholder="State the purpose of the loan (business expansion, medical, education…)" x-model="f.purpose"></textarea>
                </div>
              </div>
              <div class="form-actions">
                <button class="btn-g" @click="step=1">← Back</button>
                <button class="btn-p" @click="step=3">Next: Collateral Confirm →</button>
              </div>
            </div>

            <!-- Live Calculator Panel -->
            <div>
              <div class="calc-panel anim">
                <div class="calc-title">⚡ Live Loan Calculator</div>
                <div class="calc-grid">
                  <div class="calc-item">
                    <div class="calc-item-val" x-text="calc.monthly||'—'"></div>
                    <div class="calc-item-lbl">Monthly Instalment</div>
                  </div>
                  <div class="calc-item">
                    <div class="calc-item-val" x-text="calc.totalInterest||'—'"></div>
                    <div class="calc-item-lbl">Total Interest</div>
                  </div>
                  <div class="calc-item">
                    <div class="calc-item-val" x-text="calc.totalRepay||'—'"></div>
                    <div class="calc-item-lbl">Total Repayable</div>
                  </div>
                </div>
                <div x-show="calc.ltv" style="margin-bottom:12px">
                  <div class="flex jb aic mb8">
                    <div class="xs ts">LTV Ratio</div>
                    <div class="xs f7" :class="calc.ltvOk?'tg':'tr'" x-text="calc.ltv + '%' + (calc.ltvOk?' ✓':' ⚠ Exceeds 80%')"></div>
                  </div>
                  <div class="prog-bar" style="height:6px">
                    <div class="prog-fill" :style="`width:${Math.min(calc.ltv,100)}%;background:${calc.ltvOk?'var(--green)':'var(--red)'}`"></div>
                  </div>
                </div>
                <!-- Mini schedule preview -->
                <div x-show="calc.preview.length>0">
                  <div class="xs ts mb8" style="text-transform:uppercase;letter-spacing:.1em;font-weight:700">Schedule Preview</div>
                  <div style="background:rgba(0,0,0,.2);border-radius:8px;overflow:hidden">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;padding:8px 10px;background:rgba(0,0,0,.2)">
                      <div class="xs ts f7">#</div>
                      <div class="xs ts f7">Due Date</div>
                      <div class="xs ts f7">Amount</div>
                    </div>
                    <template x-for="row in calc.preview.slice(0,4)" :key="row.n">
                      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;padding:7px 10px;border-top:1px solid rgba(255,255,255,.04)">
                        <div class="xs ts" x-text="row.n"></div>
                        <div class="xs ts" x-text="row.due"></div>
                        <div class="xs mono tw" x-text="row.total"></div>
                      </div>
                    </template>
                    <div x-show="calc.preview.length>4" style="padding:7px 10px;border-top:1px solid rgba(255,255,255,.04)">
                      <div class="xs ts" x-text="'+ ' + (calc.preview.length-4) + ' more instalments…'"></div>
                    </div>
                  </div>
                </div>
                <div x-show="!calc.monthly" style="text-align:center;padding:20px;color:var(--slate);font-size:12px">
                  Fill in amount, rate, and term to see the live calculation
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 3: Collateral Confirm -->
        <div x-show="step===3" class="anim">
          <div class="fsec">
            <div class="fsec-hd"><div class="fsec-ic" style="background:rgba(34,197,94,.15)">🏛️</div><div class="fsec-title">Confirm Collateral</div></div>
            <div style="padding:20px">
              <!-- Collateral picker (shown if not yet selected) -->
              <div x-show="!f.collateralId" style="margin-bottom:16px">
                <div class="xs ts mb8" style="color:var(--amber)">⚠ Select a collateral asset to continue:</div>
                <div x-show="loadingCollaterals" class="xs ts" style="padding:8px 0">Loading collateral…</div>
                <div x-show="!loadingCollaterals && !borrowerCollaterals.length" class="xs ts" style="padding:8px 0;color:var(--red)">No available collateral found for this borrower.</div>
                <div class="flex g8" style="flex-wrap:wrap">
                  <template x-for="ca in borrowerCollaterals" :key="ca.id">
                    <div style="background:var(--navy-line);border-radius:7px;padding:8px 12px;cursor:pointer;border:1px solid var(--navy-line);transition:all .15s"
                         :style="f.collateralId===ca.id?'border-color:var(--teal);background:rgba(11,143,172,.12)':''"
                         @click="f.collateralId=ca.id; f.collateralVal=ca.estimated_value; f.selectedCollateral=ca; calcLoan()">
                      <div class="xs f6 tw" x-text="(ca.asset_type==='vehicle'?'🚗 ':'🏞️ ') + ca.display_label"></div>
                      <div class="xs ts mt4" x-text="'Value: K ' + Number(ca.estimated_value||0).toLocaleString()"></div>
                    </div>
                  </template>
                </div>
              </div>
              <div x-show="f.collateralId" style="background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:10px;padding:16px;margin-bottom:16px">
                <div class="flex aic g12 mb12">
                  <div style="width:42px;height:42px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:20px;background:rgba(11,143,172,.15)" x-text="f.selectedCollateral?.asset_type==='vehicle'?'🚗':'🏞️'"></div>
                  <div>
                    <div class="f6 tw" x-text="f.selectedCollateral?.display_label||'—'"></div>
                    <div class="xs ts mt4" x-text="f.selectedCollateral?.asset_type==='vehicle' ? [(f.selectedCollateral.vehicle_year||''), (f.selectedCollateral.vehicle_color||'')].filter(Boolean).join(' · ') : [(f.selectedCollateral?.title_deed_number ? 'Title: '+f.selectedCollateral.title_deed_number : ''), (f.selectedCollateral?.land_address||'')].filter(Boolean).join(' · ')"></div>
                  </div>
                  <span class="badge b-active" style="margin-left:auto">Available</span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
                  <div><div class="xs ts">Estimated Value</div><div class="sm mono f6 tw mt4" x-text="f.selectedCollateral ? 'K ' + Number(f.selectedCollateral.estimated_value||0).toLocaleString() : '—'"></div></div>
                  <div><div class="xs ts">Loan Amount</div><div class="sm mono f6 ta mt4" x-text="f.amount?'K '+Number(f.amount).toLocaleString():'—'"></div></div>
                  <div>
                    <div class="xs ts">LTV Ratio</div>
                    <div class="sm mono f6 mt4" :class="calc.ltvOk?'tg':'tr'" x-text="calc.ltv?calc.ltv+'%':'—'"></div>
                  </div>
                </div>
              </div><!-- /collateral detail -->
              <div x-show="f.collateralId">
                <div class="field mb16">
                  <label>Valuation Date</label>
                  <input class="finput" type="date">
                  <div class="fhint">Enter date of most recent valuation for this collateral</div>
                </div>
                <div class="field mb16">
                  <label>Valuer / Valuation Firm</label>
                  <input class="finput" type="text" placeholder="Name of valuer or firm">
                </div>
                <div style="padding:12px 14px;background:rgba(245,166,35,.08);border:1px solid rgba(245,166,35,.2);border-radius:8px">
                  <div class="xs ta f6">⚠ By proceeding, you confirm the collateral is unencumbered and available to be pledged against this loan.</div>
                </div>
              </div>
            </div>
            <div class="form-actions">
              <button class="btn-g" @click="step=2">← Back</button>
              <button class="btn-p" :disabled="!f.collateralId" :style="!f.collateralId?'opacity:.5;cursor:not-allowed':''" @click="step=4">Next: Review & Submit →</button>
            </div>
          </div>
        </div>

        <!-- Step 4: Review & Submit -->
        <div x-show="step===4" class="anim">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

            <!-- Review left -->
            <div>
              <div class="fsec">
                <div class="fsec-hd"><div class="fsec-ic" style="background:rgba(11,143,172,.15)">📋</div><div class="fsec-title">Application Summary</div></div>
                <div style="padding:18px">
                  <div style="background:var(--navy-mid);border-radius:10px;padding:14px;margin-bottom:12px">
                    <div class="xs ts mb8 f7" style="text-transform:uppercase;letter-spacing:.1em">Borrower</div>
                    <div class="flex aic g10">
                      <div class="brw-av" :style="`background:linear-gradient(135deg,${f.borrower?.c1||'#0B8FAC'},${f.borrower?.c2||'#076E86'})`" x-text="f.borrower?.ini||'?'" style="width:36px;height:36px;font-size:13px"></div>
                      <div>
                        <div class="f6 tw" x-text="f.borrower?.name||'—'"></div>
                        <div class="xs ts mono mt4" x-text="f.borrower?.bnum||'—'"></div>
                      </div>
                    </div>
                  </div>
                  <div style="background:var(--navy-mid);border-radius:10px;padding:14px;margin-bottom:12px">
                    <div class="xs ts mb10 f7" style="text-transform:uppercase;letter-spacing:.1em">Loan Details</div>
                    <div class="info-row" style="padding:5px 0"><div class="info-lbl">Amount</div><div class="info-val mono f6 ta" x-text="f.amount?'K '+Number(f.amount).toLocaleString():'—'"></div></div>
                    <div class="info-row" style="padding:5px 0"><div class="info-lbl">Interest Rate</div><div class="info-val mono tc" x-text="(f.rate||'—')+'% p.a.'"></div></div>
                    <div class="info-row" style="padding:5px 0"><div class="info-lbl">Method</div><div class="info-val" x-text="f.method==='reducing'?'Reducing Balance':'Flat Rate'"></div></div>
                    <div class="info-row" style="padding:5px 0"><div class="info-lbl">Term</div><div class="info-val" x-text="(f.term||'—')+' months'"></div></div>
                    <div class="info-row" style="padding:5px 0"><div class="info-lbl">Monthly Instalment</div><div class="info-val mono f6" x-text="calc.monthly||'—'"></div></div>
                    <div class="info-row" style="padding:5px 0"><div class="info-lbl">Total Repayable</div><div class="info-val mono f7 ta" x-text="calc.totalRepay||'—'"></div></div>
                    <div class="info-row" style="padding:5px 0;border-bottom:none"><div class="info-lbl">Processing Fee</div><div class="info-val mono" x-text="f.fee?'K '+Number(f.fee).toLocaleString():'K 0'"></div></div>
                  </div>
                  <div style="background:var(--navy-mid);border-radius:10px;padding:14px">
                    <div class="xs ts mb8 f7" style="text-transform:uppercase;letter-spacing:.1em">Collateral</div>
                    <div class="info-row" style="padding:5px 0"><div class="info-lbl">Asset</div><div class="info-val" x-text="f.selectedCollateral?.display_label||'—'"></div></div>
                    <div class="info-row" style="padding:5px 0;border-bottom:none"><div class="info-lbl">LTV</div><div class="info-val f6" :class="calc.ltvOk?'tg':'tr'" x-text="(calc.ltv||'—')+'%'"></div></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Review right — final confirm -->
            <div>
              <div class="fsec">
                <div class="fsec-hd"><div class="fsec-ic" style="background:rgba(34,197,94,.15)">✅</div><div class="fsec-title">Submit for Approval</div></div>
                <div style="padding:18px">
                  <div style="padding:16px;background:rgba(11,143,172,.06);border:1px solid rgba(11,143,172,.2);border-radius:10px;margin-bottom:16px">
                    <div class="sm f6 tw mb8">What happens next?</div>
                    <div class="xs ts" style="line-height:1.8">
                      1. Application is submitted with status <strong style="color:var(--amber)">Pending Approval</strong><br>
                      2. Manager / CEO reviews and approves or rejects<br>
                      3. On approval, funds are disbursed and the repayment schedule is generated<br>
                      4. Automated reminders will be sent to the borrower 7, 3, and 1 day(s) before each due date
                    </div>
                  </div>
                  <div class="field mb16">
                    <label>Additional Notes for Approver</label>
                    <textarea class="ftxtarea" placeholder="Any notes or context for the approving manager…"></textarea>
                  </div>
                  <div style="padding:12px 14px;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:8px">
                    <div class="xs tg f6">✓ I confirm all borrower details, loan terms, and collateral information are accurate and verified.</div>
                  </div>
                </div>
                <div class="form-actions">
                  <button class="btn-g" @click="step=3">← Back</button>
                  <button class="btn-amber" @click="submitApp()" style="min-width:180px">
                    📨 Submit for Approval
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /apply -->
    </div><!-- /content -->
  </main>

  <!-- ═════════════ MODALS ═════════════ -->

  <!-- Approve Modal -->
  <div class="modal-overlay" x-show="modal==='approve'" x-transition style="display:none" @click.self="modal=null">
    <div class="modal">
      <div class="modal-hd">
        <div class="modal-title">Approve Loan</div>
        <button class="btn-g btn-sm" @click="modal=null">✕</button>
      </div>
      <div class="modal-body">
        <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:10px;padding:14px;margin-bottom:16px">
          <div class="flex aic g8 mb8">
            <span style="font-size:20px">✅</span>
            <div class="f6 tw" x-text="sel?.num + ' — ' + sel?.borrower"></div>
          </div>
          <div class="flex g16 flex-wrap">
            <div><div class="xs ts">Amount</div><div class="mono f6 ta mt4" x-text="sel?.principal"></div></div>
            <div><div class="xs ts">Rate</div><div class="mono f6 tc mt4" x-text="sel?.rate"></div></div>
            <div><div class="xs ts">Term</div><div class="mono f6 mt4" x-text="sel?.term"></div></div>
            <div><div class="xs ts">Monthly</div><div class="mono f6 mt4" x-text="sel?.monthly"></div></div>
          </div>
        </div>
        <div class="field mb12">
          <label>Approved Amount (K)</label>
          <input class="finput" type="number" :placeholder="sel?.principal?.replace('K ','').replace(',','')" x-model="approveAmt">
          <div class="fhint">Leave blank to approve the full applied amount</div>
        </div>
        <div class="field">
          <label>Approval Notes</label>
          <textarea class="ftxtarea" placeholder="Optional approval comments…" x-model="approveNotes"></textarea>
        </div>
      </div>
      <div class="modal-foot">
        <button class="btn-g" @click="modal=null">Cancel</button>
        <button class="btn-amber" @click="doApprove()">✓ Confirm Approval</button>
      </div>
    </div>
  </div>

  <!-- Reject Modal -->
  <div class="modal-overlay" x-show="modal==='reject'" x-transition style="display:none" @click.self="modal=null">
    <div class="modal">
      <div class="modal-hd">
        <div class="modal-title">Reject Loan Application</div>
        <button class="btn-g btn-sm" @click="modal=null">✕</button>
      </div>
      <div class="modal-body">
        <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:14px;margin-bottom:16px">
          <div class="sm f6 tr mb4">⚠ This action will reject the loan application</div>
          <div class="xs ts" x-text="sel?.num + ' — ' + sel?.borrower + ' · ' + sel?.principal"></div>
        </div>
        <div class="field mb12">
          <label>Rejection Reason <span class="req">*</span></label>
          <select class="fsel" x-model="rejectReason">
            <option value="">Select reason</option>
            <option>Insufficient income / affordability</option>
            <option>Collateral value too low (LTV exceeded)</option>
            <option>Incomplete documentation</option>
            <option>Existing unpaid loans</option>
            <option>Collateral already encumbered</option>
            <option>Fraud or misrepresentation suspected</option>
            <option>Other</option>
          </select>
        </div>
        <div class="field">
          <label>Additional Notes</label>
          <textarea class="ftxtarea" placeholder="Provide detailed reason for rejection…" x-model="rejectNotes"></textarea>
        </div>
      </div>
      <div class="modal-foot">
        <button class="btn-g" @click="modal=null">Cancel</button>
        <button class="btn-red" @click="doReject()">✕ Confirm Rejection</button>
      </div>
    </div>
  </div>

  <!-- Disburse Modal -->
  <div class="modal-overlay" x-show="modal==='disburse'" x-transition style="display:none" @click.self="modal=null">
    <div class="modal">
      <div class="modal-hd">
        <div class="modal-title">Disburse Funds</div>
        <button class="btn-g btn-sm" @click="modal=null">✕</button>
      </div>
      <div class="modal-body">
        <div style="background:rgba(129,140,248,.08);border:1px solid rgba(129,140,248,.2);border-radius:10px;padding:14px;margin-bottom:16px">
          <div class="sm f6 tp mb8">Ready for disbursement</div>
          <div class="flex g16">
            <div><div class="xs ts">Loan</div><div class="mono f6 tw mt4" x-text="sel?.num"></div></div>
            <div><div class="xs ts">Borrower</div><div class="f6 tw mt4" x-text="sel?.borrower"></div></div>
            <div><div class="xs ts">Amount</div><div class="mono f6 ta mt4" x-text="sel?.principal"></div></div>
          </div>
        </div>
        <div class="fsec-body" style="padding:0;gap:12px">
          <div class="field">
            <label>Disbursement Date <span class="req">*</span></label>
            <input class="finput" type="date" x-model="disburseDate">
          </div>
          <div class="field">
            <label>Disbursement Method <span class="req">*</span></label>
            <select class="fsel" x-model="disburseMethod">
              <option>Cash</option>
              <option>Bank Transfer</option>
              <option>Mobile Money</option>
              <option>Cheque</option>
            </select>
          </div>
          <div class="field" x-show="disburseMethod!=='Cash'">
            <label>Transaction Reference</label>
            <input class="finput" type="text" placeholder="Bank / mobile money reference" x-model="disburseRef">
          </div>
          <div class="field">
            <label>Disbursement Notes</label>
            <input class="finput" type="text" placeholder="Optional notes" x-model="disburseNotes">
          </div>
        </div>
        <div style="margin-top:14px;padding:12px 14px;background:rgba(129,140,248,.08);border:1px solid rgba(129,140,248,.2);border-radius:8px">
          <div class="xs tp f6">⚡ Disbursing will generate the repayment schedule and activate automated SMS/Email reminders.</div>
        </div>
      </div>
      <div class="modal-foot">
        <button class="btn-g" @click="modal=null">Cancel</button>
        <button class="btn-green" @click="doDisburse()">⬆ Confirm Disbursement</button>
      </div>
    </div>
  </div>

  <!-- Early Settlement Modal -->
  <div class="modal-overlay" x-show="modal==='settle'" x-transition style="display:none" @click.self="modal=null">
    <div class="modal">
      <div class="modal-hd">
        <div class="modal-title">Early Settlement Calculator</div>
        <button class="btn-g btn-sm" @click="modal=null">✕</button>
      </div>
      <div class="modal-body">
        <div class="xs ts mb16">The original rate is discarded. Interest is recalculated from scratch using the rate that matches how many months the client actually held the loan.</div>
        <div class="settle-box mb16">
          <!-- Step 1: New interest calculation -->
          <div class="settle-row"><div class="settle-lbl">Principal</div><div class="settle-val" x-text="settle.principal"></div></div>
          <div class="settle-row">
            <div class="settle-lbl" x-text="'New Interest Rate (' + settle.effectiveMonths + '-month tier)'"></div>
            <div class="settle-val tc" x-text="settle.tieredRate + '%'"></div>
          </div>
          <div class="settle-row">
            <div class="settle-lbl" x-text="'New Interest (' + settle.tieredRate + '% × principal)'"></div>
            <div class="settle-val ta" x-text="settle.tieredInterest"></div>
          </div>
          <div class="settle-row" style="border-top:1px solid rgba(11,143,172,.15);margin-top:6px;padding-top:8px">
            <div class="settle-lbl f7 tw">Total to Repay (at new rate)</div>
            <div class="settle-val tw" x-text="'= ' + settle.principal + ' + ' + settle.tieredInterest"></div>
          </div>

          <!-- Step 2: Deduct what's already paid -->
          <div class="settle-row" style="border-top:1px solid rgba(11,143,172,.15);margin-top:6px;padding-top:8px">
            <div class="settle-lbl">Less: Already Paid</div>
            <div class="settle-val tg" x-text="'− ' + settle.alreadyPaid"></div>
          </div>

          <!-- Step 3: Interest discount vs original -->
          <div class="settle-row">
            <div class="settle-lbl" x-text="'Interest Saving (vs original ' + settle.originalRate + '% rate)'"></div>
            <div class="settle-val discount" x-text="'− ' + settle.discount"></div>
          </div>

          <!-- Result -->
          <div class="settle-row" style="padding-top:10px;border-top:2px solid rgba(11,143,172,.25);margin-top:4px">
            <div class="settle-lbl f7 tw">Settlement Amount Due</div>
            <div class="settle-val total" x-text="settle.amount"></div>
          </div>
        </div>
        <div style="padding:12px 14px;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:8px">
          <div class="xs tg f6" x-text="'✓ Pay ' + settle.amount + ' to close this loan early. The client saves ' + settle.discount + ' compared to the original ' + settle.originalRate + '% rate.'"></div>
        </div>

        <!-- Payment method -->
        <div class="field mt12">
          <label style="font-size:12px;color:var(--slate-lt);font-weight:600">Payment Method <span class="req">*</span></label>
          <select class="fsel" x-model="settleMethod" style="margin-top:6px">
            <option value="cash">💵 Cash</option>
            <option value="bank_transfer">🏦 Bank Transfer</option>
            <option value="mobile_money">📱 Mobile Money</option>
            <option value="cheque">📋 Cheque</option>
          </select>
        </div>
      </div>
      <div class="modal-foot">
        <button class="btn-g" @click="modal=null">Cancel</button>
        <button class="btn-green" @click="doSettle()">✓ Process Settlement</button>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div class="toast-msg" x-show="toast" x-transition style="display:none" :style="`background:${toastColor}`" x-text="toastMsg"></div>

  <script>
  function app() {
    return {
      view: 'list', ptab: 'schedule', step: 1,
      q: '', fStatus: '', fColl: '',
      sel: null, modal: null,
      approveAmt: '', approveNotes: '',
      disburseDate: '', disburseMethod: 'Cash', disburseRef: '', disburseNotes: '',
      rejectReason: '', rejectNotes: '',
      toast: false, toastMsg: '', toastColor: 'var(--green)',
      settle: {}, settleMethod: 'cash',
      stats: { loans: { total:'-', active:'-', pending:'-', overdue:'-', closed:'-' } },
      rows: [], page: 1, perPage: 8,
      borrowerResults: [],
      borrowerCollaterals: [],
      loadingCollaterals: false,
      loanProducts: [],

      f: { borrowerQ:'', borrower:null, collateralId:null, collateralVal:0, selectedCollateral:null, productId:'', amount:'', rate:28, method:'reducing', term:'', fee:'', firstDate:'', purpose:'', disburseMethod:'Cash' },
      calc: { monthly:'', totalInterest:'', totalRepay:'', ltv:null, ltvOk:true, preview:[] },

      _allLoans: [],

      _palette: [
        ['#0B8FAC','#076E86'],['#6366F1','#4338CA'],['#10B981','#059669'],
        ['#F59E0B','#D97706'],['#EC4899','#BE185D'],['#34D399','#059669'],
        ['#F472B6','#DB2777'],['#818CF8','#6366F1'],['#EF4444','#B91C1C'],
        ['#14B8A6','#0F766E'],
      ],

      _avatarColor(name) {
        let h = 0;
        for (let i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) & 0xFFFFFF;
        return this._palette[Math.abs(h) % this._palette.length];
      },

      _initials(name) {
        const p = name.trim().split(/\s+/);
        return (p[0][0] + (p[1] ? p[1][0] : '')).toUpperCase();
      },

      _fmtK(v) {
        const n = parseFloat(v) || 0;
        return 'K ' + n.toLocaleString('en-ZM', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
      },

      _fmtDate(d) {
        if (!d) return null;
        return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
      },

      _mapLoan(l) {
        const name = (l.borrower?.first_name || '') + ' ' + (l.borrower?.last_name || '');
        const [c1, c2] = this._avatarColor(name.trim());
        const coll = l.collateral_asset?.asset_type || null;
        const owed = l.loan_balance?.total_outstanding;
        const statusClassMap = { pending_approval: 'pending', pending: 'pending', approved: 'approved', active: 'active', closed: 'closed', rejected: 'rejected', defaulted: 'defaulted', draft: 'draft', overdue: 'rejected' };
        const statusLabelMap = { pending_approval: 'Pending', pending: 'Pending', approved: 'Approved', active: 'Active', closed: 'Closed', rejected: 'Rejected', defaulted: 'Defaulted', draft: 'Draft', overdue: 'Overdue' };
        return {
          id: l.id,
          num: l.loan_number,
          borrower: name.trim(),
          ini: this._initials(name.trim() || '?'),
          c1, c2,
          product: l.loan_product?.name || '—',
          coll: coll,
          owed: owed ? this._fmtK(owed) : null,
          status: l.status,
          statusClass: statusClassMap[l.status] || l.status,
          statusLabel: statusLabelMap[l.status] || l.status,
          date: this._fmtDate(l.created_at),
        };
      },

      doFilter() {
        const q = this.q.toLowerCase();
        this.rows = this._allLoans.filter(l => {
          const mq = !q || l.num.toLowerCase().includes(q) || l.borrower.toLowerCase().includes(q);
          const ms = !this.fStatus || l.status === this.fStatus || (this.fStatus === 'pending' && l.status === 'pending_approval');
          const mc = !this.fColl   || l.coll   === this.fColl;
          return mq && ms && mc;
        });
        this.page = 1;
      },

      async open(l) {
        this.ptab = 'schedule';
        this.view = 'detail';
        // Show a placeholder immediately
        this.sel = { ...l, principal: '…', schedule: [], payments: [], penaltyRows: [], timeline: [], docs: [] };
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch(`/api/loans/${l.id}`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
          });
          if (!res.ok) return;
          const d = await res.json();
          const name = (d.borrower?.first_name || '') + ' ' + (d.borrower?.last_name || '');
          const [c1, c2] = this._avatarColor(name.trim());
          const coll = d.collateral_asset?.asset_type || null;
          const bal = d.loan_balance;
          const totalPaid = bal ? (parseFloat(d.total_repayable) - parseFloat(bal.total_outstanding)) : 0;
          const paidPct = d.total_repayable > 0 ? Math.round((totalPaid / parseFloat(d.total_repayable)) * 100) : 0;

          // Build timeline from status_history
          const stateMap = { draft:'pend', pending:'pend', approved:'done', active:'done', closed:'done', rejected:'rej', defaulted:'rej' };
          const timeline = (d.status_history || []).map(h => ({
            label: (h.to_status || h.from_status || '').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()),
            state: stateMap[h.to_status] || 'done',
            meta: (h.changed_by?.name || '') + (h.created_at ? ' · ' + this._fmtDate(h.created_at) : ''),
          }));
          if (!timeline.length) {
            timeline.push({ label: 'Application Submitted', state: 'done', meta: this._fmtDate(d.created_at) });
            if (['approved','active','closed'].includes(d.status)) timeline.push({ label: 'Approved', state: 'done', meta: d.approved_at ? this._fmtDate(d.approved_at) : '' });
            if (['active','closed'].includes(d.status)) timeline.push({ label: 'Disbursed', state: 'done', meta: d.disbursed_at ? this._fmtDate(d.disbursed_at) : '' });
            if (d.status === 'active') timeline.push({ label: 'Active — Repayment in Progress', state: 'cur', meta: '' });
            if (d.status === 'closed') timeline.push({ label: 'Loan Closed', state: 'done', meta: '' });
            if (d.status === 'pending') timeline.push({ label: 'Pending Approval', state: 'cur', meta: '' });
          }

          // Build schedule rows
          const schedule = (d.loan_schedule || []).map(s => {
            const pen = parseFloat(s.penalty_amount) || 0;
            const paid = parseFloat(s.amount_paid) || 0;
            const bal = Math.max(0, parseFloat(s.total_due) - paid);
            return {
              n: s.instalment_number,
              due: this._fmtDate(s.due_date),
              dueRaw: s.due_date,          // ISO date string for settlement logic
              prin: this._fmtK(s.principal_portion),
              int: this._fmtK(s.interest_portion),
              total: this._fmtK(s.total_due),
              pen: pen > 0 ? this._fmtK(pen) : '—',
              paid: paid > 0 ? this._fmtK(paid) : 'K 0',
              bal: bal > 0 ? this._fmtK(bal) : 'K 0',
              cls: s.status === 'paid' ? 'paid' : s.status === 'overdue' ? 'overdue' : s.status === 'due' ? 'current' : 'pending',
            };
          });

          // Method icons
          const methIc = { cash:'💵', bank_transfer:'🏦', mobile_money:'📱', cheque:'📋' };
          const typeClr = { instalment:'rgba(34,197,94,.12)', partial:'rgba(245,166,35,.12)', early_settlement:'rgba(34,197,94,.15)', penalty:'rgba(239,68,68,.12)' };
          const typeBdg = { instalment:'b-active', partial:'b-pending', early_settlement:'b-active', penalty:'b-rejected' };

          const payments = (d.payments || []).map(p => ({
            id: p.id,
            rc: p.receipt_number || ('RCP-' + p.id),
            dt: this._fmtDate(p.payment_date),
            mth: (p.payment_method || '').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()),
            amt: '+' + this._fmtK(p.amount_received),
            bal: p.balance_after != null ? this._fmtK(p.balance_after) : '—',
            type: (p.payment_type || '').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()),
            bdg: typeBdg[p.payment_type] || 'b-draft',
            ic: methIc[p.payment_method] || '💰',
            bg: typeClr[p.payment_type] || 'rgba(148,163,184,.12)',
          }));

          const penaltyRows = (d.penalties || []).map(pr => ({
            id: pr.id,
            inst: pr.instalment_number || '',
            date: this._fmtDate(pr.applied_date),
            days: pr.days_overdue || 0,
            amt: this._fmtK(pr.penalty_amount),
            paid: pr.status === 'paid',
          }));

          const docs = (d.documents || []).map(doc => ({
            id: doc.id,
            name: doc.file_name || doc.original_name || 'Document',
            meta: this._fmtDate(doc.created_at) + ' · ' + (doc.file_type || 'File'),
            ic: '📄',
            bg: 'rgba(11,143,172,.12)',
          }));

          this.sel = {
            id: d.id,
            num: d.loan_number,
            borrower: name.trim(),
            ini: this._initials(name.trim() || '?'),
            c1, c2,
            status: d.status,
            product: d.loan_product?.name || '—',
            coll: coll,
            collateral: d.collateral_asset?.display_label || (coll ? coll.charAt(0).toUpperCase() + coll.slice(1) : '—'),
            collateralVal: d.collateral_asset?.estimated_value ? this._fmtK(d.collateral_asset.estimated_value) : '—',
            ltv: parseFloat(d.ltv_at_origination) || null,
            principal: this._fmtK(d.principal_amount),
            rate: (parseFloat(d.interest_rate) || 0) + '% p.a.',
            term: (d.term_months || 0) + ' months',
            monthly: d.monthly_instalment ? this._fmtK(d.monthly_instalment) : '—',
            totalInterest: d.total_interest ? this._fmtK(d.total_interest) : '—',
            totalRepay: d.total_repayable ? this._fmtK(d.total_repayable) : '—',
            fee: d.processing_fee ? this._fmtK(d.processing_fee) : '—',
            officer: d.applied_by?.name || '—',
            date: this._fmtDate(d.created_at),
            disburseDate: d.disbursed_at ? this._fmtDate(d.disbursed_at) : null,
            maturity: d.maturity_date ? this._fmtDate(d.maturity_date) : null,
            owed: bal?.total_outstanding ? this._fmtK(bal.total_outstanding) : null,
            paid: totalPaid > 0 ? this._fmtK(totalPaid) : 'K 0',
            penalty: bal?.penalty_outstanding ? this._fmtK(bal.penalty_outstanding) : 'K 0',
            paidPct,
            timeline, schedule, payments, penaltyRows, docs,
            monthsPaid:            (d.loan_balance?.instalments_paid) || (d.loan_schedule||[]).filter(s=>s.status==='paid').length,
            balPrincipalDisbursed: parseFloat(d.loan_balance?.principal_disbursed || d.principal_amount) || 0,
            balPrincipalPaid:      parseFloat(d.loan_balance?.principal_paid) || 0,
            balInterestPaid:       parseFloat(d.loan_balance?.interest_paid) || 0,
            balPenaltyPaid:        parseFloat(d.loan_balance?.penalty_paid) || 0,
            borrowerNrc:      d.borrower?.nrc_number || '—',
            borrowerPhone:    d.borrower?.phone_number || '—',
            borrowerAddress:  d.borrower?.physical_address || d.borrower?.address || '—',
            borrowerEmployer: d.borrower?.employer_name || '—',
            rawPrincipal:     parseFloat(d.principal_amount) || 0,
            rawRate:          parseFloat(d.interest_rate) || 0,
            rawTerm:          parseInt(d.term_months) || 0,
            agreementDate:    d.disbursed_at ? this._fmtDate(d.disbursed_at) : this._fmtDate(d.created_at),
          };
        } catch (e) { console.error('Loan detail error:', e); }
      },

      async searchBorrowers() {
        const q = this.f.borrowerQ.trim();
        if (!q || q.length < 2) { this.borrowerResults = []; return; }
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch(`/api/borrowers?search=${encodeURIComponent(q)}&per_page=6`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
          });
          if (!res.ok) { this.borrowerResults = []; return; }
          const data = await res.json();
          this.borrowerResults = (data.data || []).map(b => {
            const name = b.first_name + ' ' + b.last_name;
            const [c1, c2] = this._avatarColor(name);
            return { id: b.id, name, bnum: b.borrower_number, nrc: b.nrc_number || '', ini: this._initials(name), c1, c2 };
          });
        } catch { this.borrowerResults = []; }
      },

      async selectBorrower(b) {
        this.f.borrower = b;
        this.f.borrowerQ = b.name;
        this.f.collateralId = null;
        this.f.collateralVal = 0;
        this.f.selectedCollateral = null;
        this.borrowerResults = [];
        this.borrowerCollaterals = [];
        this.loadingCollaterals = true;
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch(`/api/collateral?borrower_id=${b.id}&status=available&per_page=20`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
          });
          const data = res.ok ? await res.json() : { data: [] };
          this.borrowerCollaterals = data.data || [];
        } catch {
          this.borrowerCollaterals = [];
        }
        this.loadingCollaterals = false;
      },

      calcLoan() {
        const RATES = { 1: 10, 2: 18, 3: 28, 4: 38 };
        const P = parseFloat(this.f.amount) || 0;
        const n = parseInt(this.f.term)     || 0;
        const r = RATES[n] || 0;
        const collVal = this.f.collateralVal ? parseFloat(this.f.collateralVal) : 0;
        if (!P || !n || !r) { this.calc = {monthly:'',totalInterest:'',totalRepay:'',ltv:null,ltvOk:true,preview:[]}; return; }

        // Fixed flat-rate: interest = rate% × principal (total for full term)
        const totalInterest = P * (r / 100);
        const totalRepay    = P + totalInterest;
        const monthly       = totalRepay / n;
        const ltv = collVal ? Math.round((P / collVal) * 100) : null;

        // Equal monthly instalments
        const preview = [];
        const startDate = this.f.firstDate ? new Date(this.f.firstDate) : new Date();
        for (let i = 1; i <= n; i++) {
          const d = new Date(startDate);
          d.setMonth(d.getMonth() + i - 1);
          preview.push({ n: i, due: d.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}), total: 'K ' + Math.round(monthly).toLocaleString() });
        }

        this.calc = {
          monthly: 'K ' + Math.round(monthly).toLocaleString(),
          totalInterest: 'K ' + Math.round(totalInterest).toLocaleString(),
          totalRepay: 'K ' + Math.round(totalRepay).toLocaleString(),
          ltv, ltvOk: ltv ? ltv <= 80 : true, preview
        };
      },

      async doApprove() {
        if (!this.sel) return;
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch(`/api/loans/${this.sel.id}/approve`, {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ approval_notes: this.approveNotes || null }),
          });
          const data = await res.json();
          if (!res.ok) { this.showToast('Error: ' + (data.message || 'Could not approve loan.'), 'var(--red)'); return; }
          this.modal = null;
          this.approveNotes = '';
          this.showToast('✓ Loan approved successfully! Ready for disbursement.', 'var(--green)');
          await this.loadLoans();
          if (this.sel) { this.sel.status = 'approved'; }
        } catch { this.showToast('Network error.', 'var(--red)'); }
      },

      async doReject() {
        if (!this.sel) return;
        const reason = (this.rejectReason + (this.rejectNotes ? ' — ' + this.rejectNotes : '')).trim();
        if (!reason) { this.showToast('Please provide a rejection reason.', 'var(--amber)'); return; }
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch(`/api/loans/${this.sel.id}/reject`, {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ rejection_reason: reason }),
          });
          const data = await res.json();
          if (!res.ok) { this.showToast('Error: ' + (data.message || 'Could not reject loan.'), 'var(--red)'); return; }
          this.modal = null;
          this.rejectReason = ''; this.rejectNotes = '';
          this.showToast('Loan application rejected.', 'var(--red)');
          await this.loadLoans();
          if (this.sel) { this.sel.status = 'rejected'; }
        } catch { this.showToast('Network error.', 'var(--red)'); }
      },

      async doDisburse() {
        if (!this.sel) return;
        const methodMap = { 'Cash': 'cash', 'Bank Transfer': 'bank_transfer', 'Mobile Money': 'mobile_money', 'Cheque': 'cash' };
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch(`/api/loans/${this.sel.id}/disburse`, {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({
              disbursement_method:    methodMap[this.disburseMethod] || 'cash',
              disbursement_reference: this.disburseRef || null,
              disburse_notes:         this.disburseNotes || null,
            }),
          });
          const data = await res.json();
          if (!res.ok) { this.showToast('Error: ' + (data.message || 'Could not disburse loan.'), 'var(--red)'); return; }
          this.modal = null;
          this.disburseRef = ''; this.disburseNotes = '';
          this.showToast('✓ Funds disbursed! Repayment schedule generated.', 'var(--teal)');
          await this.loadLoans();
          if (this.sel) { this.sel.status = 'active'; }
        } catch { this.showToast('Network error.', 'var(--red)'); }
      },

      openSettle() {
        if (!this.sel) return;
        const RATES = {1:10, 2:18, 3:28, 4:38};

        const principal    = this.sel.balPrincipalDisbursed || this.sel.rawPrincipal;
        const termMonths   = this.sel.rawTerm;
        const originalRate = RATES[termMonths] || this.sel.rawRate;
        const originalInterest = Math.round(principal * (originalRate / 100) * 100) / 100;

        // Effective months = the instalment period today falls in.
        // Find the first schedule row whose due date is >= today.
        // That row's instalment number = effective months (same logic as backend).
        const today = new Date().toISOString().slice(0, 10);
        const schedule = this.sel.schedule || [];
        const nextRow  = schedule.find(r => r.dueRaw && r.dueRaw >= today);
        const effectiveMonths = nextRow
          ? Math.min(nextRow.n, termMonths)
          : termMonths;   // settling after all due dates → full term

        const tieredRate       = RATES[effectiveMonths] ?? originalRate;
        const newTotalInterest = Math.round(principal * (tieredRate / 100) * 100) / 100;

        // settlement = (principal + new_interest) − total_already_paid
        const totalAlreadyPaid = (this.sel.balPrincipalPaid || 0)
                               + (this.sel.balInterestPaid  || 0)
                               + (this.sel.balPenaltyPaid   || 0);
        const settlementAmount = Math.max(0, Math.round((principal + newTotalInterest - totalAlreadyPaid) * 100) / 100);
        const interestDiscount = Math.max(0, Math.round((originalInterest - newTotalInterest) * 100) / 100);

        this.settle = {
          principal:        this._fmtK(principal),
          termMonths,
          originalRate,
          originalInterest: this._fmtK(originalInterest),
          effectiveMonths,
          tieredRate,
          tieredInterest:   this._fmtK(newTotalInterest),
          alreadyPaid:      this._fmtK(totalAlreadyPaid),
          discount:         this._fmtK(interestDiscount),
          amount:           this._fmtK(settlementAmount),
          amountRaw:        settlementAmount,
        };
        this.settleMethod = 'cash';
        this.modal = 'settle';
      },

      async doSettle() {
        if (!this.sel) return;
        if (!this.settleMethod) { this.showToast('Please select a payment method.', 'var(--amber)'); return; }
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch(`/api/loans/${this.sel.id}/early-settle`, {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({
              payment_method: this.settleMethod,
              payment_date:   new Date().toISOString().slice(0, 10),
            }),
          });
          const data = await res.json();
          if (!res.ok) { this.showToast('Error: ' + (data.message || 'Settlement failed.'), 'var(--red)'); return; }
          this.modal = null;
          this.showToast(
            '✓ Settled! Receipt: ' + (data.receipt || '—') + ' · Saving: ' + this.settle.discount,
            'var(--green)'
          );
          // Reload the loan detail to reflect closed status
          if (this.sel) await this.openLoan(this.sel);
          await this.loadLoans();
        } catch { this.showToast('Network error during settlement.', 'var(--red)'); }
      },

      async submitApp() {
        if (!this.f.borrower?.id)  { this.showToast('Please select a borrower.', 'var(--amber)'); return; }
        if (!this.f.collateralId)  { this.showToast('Please select a collateral asset.', 'var(--amber)'); return; }
        if (!this.f.amount || parseFloat(this.f.amount) < 1000) { this.showToast('Please enter a valid loan amount (min K 1,000).', 'var(--amber)'); return; }
        if (!this.f.term)          { this.showToast('Please select a loan term.', 'var(--amber)'); return; }
        if (!this.f.firstDate)     { this.showToast('Please set the first repayment date.', 'var(--amber)'); return; }
        const token = localStorage.getItem('lms_token');
        const disburseMap = { 'Cash': 'cash', 'Bank Transfer': 'bank_transfer', 'Mobile Money': 'mobile_money', 'Cheque': 'cash' };
        const payload = {
          borrower_id:          this.f.borrower?.id,
          loan_product_id:      this.f.productId,
          collateral_asset_id:  this.f.collateralId,
          principal_amount:     parseFloat(this.f.amount),
          term_months:          parseInt(this.f.term),
          first_repayment_date: this.f.firstDate,
          disbursement_method:  disburseMap[this.f.disburseMethod] || 'cash',
          loan_purpose:         this.f.purpose || null,
        };
        try {
          const res = await fetch('/api/loans', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
          });
          const data = await res.json();
          if (!res.ok) {
            const fieldErrors = data.errors ? Object.values(data.errors).flat().join(' ') : null;
            this.showToast('Error: ' + (fieldErrors || data.message || 'Submission failed.'), 'var(--red)');
            return;
          }
          this.showToast('📨 Loan application submitted! ' + (data.loan?.loan_number || '') + ' — pending manager approval.', 'var(--amber)');
          await this.loadLoans();
          setTimeout(() => { this.view = 'list'; this.step = 1; this.f = { borrowerQ:'', borrower:null, collateralId:null, collateralVal:0, selectedCollateral:null, productId:'', amount:'', rate:28, method:'reducing', term:'', fee:'', firstDate: new Date().toISOString().slice(0,10), purpose:'', disburseMethod:'Cash' }; this.borrowerCollaterals = []; }, 2400);
        } catch (e) {
          this.showToast('Network error submitting application.', 'var(--red)');
        }
      },

      printAgreement() {
        this.showToast('Opening print dialog — choose "Save as PDF"', 'var(--teal)');
        setTimeout(() => { window.print(); }, 400);
      },

      showToast(msg, color) {
        this.toastMsg = msg; this.toastColor = color; this.toast = true;
        setTimeout(() => { this.toast = false; }, 3000);
      },

      get pagedRows() { const s=(this.page-1)*this.perPage; return this.rows.slice(s,s+this.perPage); },
      get totalPages() { return Math.max(1,Math.ceil(this.rows.length/this.perPage)); },

      async loadLoans() {
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch('/api/loans?per_page=200', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
          });
          if (!res.ok) return;
          const data = await res.json();
          this._allLoans = (data.data || []).map(l => this._mapLoan(l));
          this.doFilter();
        } catch (e) { console.error('Load loans error:', e); }
      },

      async init() {
        const iso = new Date().toISOString().slice(0,10);
        this.disburseDate = iso;
        this.f.firstDate = iso;
        await Promise.all([this.loadStats(), this.loadLoans(), this.loadLoanProducts()]);
        // Auto-open settlement modal if redirected from payments page (?settle=loanId)
        const params = new URLSearchParams(window.location.search);
        const settleId = params.get('settle');
        if (settleId) {
          const target = this.loans.find(l => String(l.id) === String(settleId));
          if (target) { this.openDetail(target); this.$nextTick(() => this.openSettle()); }
          window.history.replaceState({}, '', '/loans');
        }
      },

      async loadLoanProducts() {
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch('/api/loan-products?active_only=1', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
          });
          if (res.ok) {
            const data = await res.json();
            this.loanProducts = Array.isArray(data) ? data : (data.data || []);
          }
        } catch {}
      },

      async loadStats() {
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch('/api/stats', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
          if (res.ok) this.stats = await res.json();
        } catch {}
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
