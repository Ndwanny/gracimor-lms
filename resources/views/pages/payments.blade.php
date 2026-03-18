<!DOCTYPE html>
<html lang="en">
<head>
  <script>if(!localStorage.getItem("lms_token")){window.location.replace("/login");}</script>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gracimor LMS — Payments</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&family=Playfair+Display:wght@700&family=Cormorant+Garamond:wght@600;700&display=swap" rel="stylesheet">
  <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js"></script>
  <style>
    :root {
      --navy:#0D1B2A; --navy-mid:#112236; --navy-card:#16293D; --navy-line:#1E3450; --navy-hover:#1A304A;
      --teal:#0B8FAC; --teal-lt:#13AECF; --teal-dk:#076E86;
      --amber:#F5A623; --amber-lt:#FFBE55;
      --green:#22C55E; --green-dk:#15803D;
      --red:#EF4444; --purple:#818CF8;
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
    .nav-bdg{margin-left:auto;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px}
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
    .btn-p{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--teal);color:#fff;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;white-space:nowrap}
    .btn-p:hover{background:var(--teal-lt)}
    .btn-g{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:transparent;color:var(--slate-lt);border:1px solid var(--navy-line);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:all .15s;white-space:nowrap}
    .btn-g:hover{background:var(--navy-line);color:var(--white)}
    .btn-green{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--green);color:#fff;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s;white-space:nowrap}
    .btn-green:hover{background:var(--green-dk)}
    .btn-sm{font-size:11px !important;padding:5px 10px !important}
    .btn-xs{font-size:10px !important;padding:4px 8px !important}

    /* ── Stats ── */
    .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
    .m-stat{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:10px;padding:16px 18px;display:flex;align-items:center;gap:14px;transition:all .15s}
    .m-stat:hover{border-color:rgba(11,143,172,.35);transform:translateY(-1px)}
    .m-stat-ic{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0}
    .m-stat-val{font-family:'DM Mono',monospace;font-size:22px;font-weight:500;color:var(--white)}
    .m-stat-lbl{font-size:10px;color:var(--slate);margin-top:2px;text-transform:uppercase;letter-spacing:.08em;font-weight:600}
    .m-stat-sub{font-size:11px;color:var(--slate);margin-top:4px}

    /* ── Filter ── */
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
    .dtbl th{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--slate);text-align:left;padding:12px 18px;border-bottom:1px solid var(--navy-line);background:var(--navy-mid);white-space:nowrap}
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
    .b-full{background:rgba(34,197,94,.12);color:var(--green);border:1px solid rgba(34,197,94,.25)} .b-full::before{background:var(--green)}
    .b-partial{background:rgba(245,166,35,.12);color:var(--amber);border:1px solid rgba(245,166,35,.25)} .b-partial::before{background:var(--amber)}
    .b-penalty{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.25)} .b-penalty::before{background:var(--red)}
    .b-settle{background:rgba(129,140,248,.12);color:var(--purple);border:1px solid rgba(129,140,248,.25)} .b-settle::before{background:var(--purple)}
    .b-waiver{background:rgba(148,163,184,.12);color:var(--slate);border:1px solid rgba(148,163,184,.2)} .b-waiver::before{background:var(--slate)}

    /* ── Utils ── */
    .mono{font-family:'DM Mono',monospace}
    .tc{color:var(--teal-lt)} .ta{color:var(--amber)} .tg{color:var(--green)} .tr{color:var(--red)} .ts{color:var(--slate)} .tw{color:var(--white)} .tp{color:var(--purple)}
    .sm{font-size:12px} .xs{font-size:11px} .f6{font-weight:600} .f7{font-weight:700}
    .flex{display:flex} .aic{align-items:center} .jb{justify-content:space-between} .col{flex-direction:column}
    .g6{gap:6px} .g8{gap:8px} .g10{gap:10px} .g12{gap:12px} .g16{gap:16px} .g20{gap:20px}
    .mt4{margin-top:4px} .mt8{margin-top:8px} .mt12{margin-top:12px} .mt16{margin-top:16px} .mt20{margin-top:20px}
    .mb8{margin-bottom:8px} .mb12{margin-bottom:12px} .mb16{margin-bottom:16px} .mb20{margin-bottom:20px}

    /* ── Record Payment Form ── */
    .pay-layout{display:grid;grid-template-columns:1fr 400px;gap:20px}
    .card{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:14px;overflow:hidden}
    .card-hd{padding:14px 18px;background:var(--navy-mid);border-bottom:1px solid var(--navy-line);display:flex;align-items:center;justify-content:space-between}
    .card-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--slate)}
    .card-body{padding:18px}

    /* Form elements */
    .fsec{background:var(--navy-card);border:1px solid var(--navy-line);border-radius:12px;overflow:hidden;margin-bottom:20px}
    .fsec-hd{padding:14px 20px;background:var(--navy-mid);border-bottom:1px solid var(--navy-line);display:flex;align-items:center;gap:10px}
    .fsec-ic{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:13px}
    .fsec-title{font-size:13px;font-weight:700;color:var(--white)}
    .field{display:flex;flex-direction:column;gap:6px}
    .field.span2{grid-column:span 2}
    label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--slate)}
    .req{color:var(--red);margin-left:2px}
    .finput,.fsel,.ftxtarea{background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:8px;padding:10px 14px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .15s;width:100%}
    .finput:focus,.fsel:focus{border-color:var(--teal)}
    .finput::placeholder{color:var(--slate)}
    .finput.large{font-family:'DM Mono',monospace;font-size:24px;font-weight:500;padding:14px 18px}
    .fhint{font-size:11px;color:var(--slate)}
    .form-actions{display:flex;gap:10px;justify-content:flex-end;padding:16px 20px;background:var(--navy-mid);border-top:1px solid var(--navy-line)}

    /* Breakdown panel */
    .breakdown{background:linear-gradient(135deg,rgba(11,143,172,.1),rgba(11,143,172,.04));border:1px solid rgba(11,143,172,.25);border-radius:12px;padding:20px;position:sticky;top:90px}
    .breakdown-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--teal-lt);margin-bottom:16px}
    .bk-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(30,52,80,.5)}
    .bk-row:last-child{border-bottom:none}
    .bk-lbl{font-size:12px;color:var(--slate)}
    .bk-val{font-family:'DM Mono',monospace;font-size:13px;font-weight:500;color:var(--white)}
    .bk-val.credit{color:var(--green)}
    .bk-val.debit{color:var(--amber)}
    .bk-val.balance{color:var(--teal-lt);font-size:18px;font-weight:600}
    .bk-val.penalty{color:var(--red)}
    .bk-divider{height:1px;background:var(--navy-line);margin:8px 0}

    /* Loan search result */
    .loan-opt{display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;transition:background .1s;border-bottom:1px solid rgba(30,52,80,.4)}
    .loan-opt:last-child{border-bottom:none}
    .loan-opt:hover{background:var(--navy-hover)}
    .l-av{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0}

    /* Selected loan card */
    .sel-loan{background:rgba(11,143,172,.08);border:1px solid rgba(11,143,172,.25);border-radius:10px;padding:16px;margin-bottom:16px}
    .sel-loan-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px;padding-top:12px;border-top:1px solid rgba(11,143,172,.2)}
    .sel-loan-item-lbl{font-size:10px;color:rgba(227,242,253,.5);text-transform:uppercase;letter-spacing:.08em;font-weight:700}
    .sel-loan-item-val{font-family:'DM Mono',monospace;font-size:14px;font-weight:500;color:var(--white);margin-top:3px}

    /* Instalment selector */
    .inst-card{border:2px solid var(--navy-line);border-radius:10px;padding:12px 14px;cursor:pointer;transition:all .15s;margin-bottom:8px}
    .inst-card:hover{border-color:rgba(11,143,172,.4)}
    .inst-card.selected{border-color:var(--teal);background:rgba(11,143,172,.08)}
    .inst-card.overdue-card{border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.04)}
    .inst-card.overdue-card.selected{border-color:var(--red);background:rgba(239,68,68,.1)}

    /* Payment type pills */
    .type-pills{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:0}
    .type-pill{border:2px solid var(--navy-line);border-radius:8px;padding:10px 8px;cursor:pointer;transition:all .15s;text-align:center}
    .type-pill:hover{border-color:rgba(11,143,172,.4)}
    .type-pill.sel-pill{border-color:var(--teal);background:rgba(11,143,172,.08)}
    .type-pill-ic{font-size:20px;margin-bottom:4px}
    .type-pill-lbl{font-size:11px;font-weight:700;color:var(--white)}

    /* ── RECEIPT ── */
    .receipt-overlay{position:fixed;inset:0;background:rgba(4,10,18,.85);backdrop-filter:blur(8px);z-index:100;display:flex;align-items:center;justify-content:center;padding:20px;overflow-y:auto}
    .receipt-shell{background:#fff;border-radius:4px;width:100%;max-width:600px;overflow:hidden;box-shadow:0 40px 80px rgba(0,0,0,.6);animation:receiptIn .4s cubic-bezier(.175,.885,.32,1.275)}
    @keyframes receiptIn{from{opacity:0;transform:scale(.92) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}

    /* Receipt content — designed as a real paper document */
    .receipt-paper{background:#fff;color:#1a1a2e;font-family:'DM Sans',sans-serif}

    .rcpt-header{background:linear-gradient(135deg,#0D1B2A,#1B4F8A);padding:28px 32px;color:#fff;position:relative;overflow:hidden}
    .rcpt-header::before{content:'';position:absolute;top:-40px;right:-40px;width:160px;height:160px;border-radius:50%;background:rgba(11,143,172,.15)}
    .rcpt-header::after{content:'';position:absolute;bottom:-50px;left:-30px;width:120px;height:120px;border-radius:50%;background:rgba(245,166,35,.08)}
    .rcpt-org{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:#fff;letter-spacing:.02em;position:relative;z-index:1}
    .rcpt-org-sub{font-size:11px;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.18em;margin-top:2px;position:relative;z-index:1}
    .rcpt-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(34,197,94,.25);border:1px solid rgba(34,197,94,.4);border-radius:99px;padding:4px 14px;font-size:11px;font-weight:700;color:#6EE7B7;margin-top:12px;text-transform:uppercase;letter-spacing:.1em;position:relative;z-index:1}
    .rcpt-badge::before{content:'';width:6px;height:6px;border-radius:50%;background:#6EE7B7}
    .rcpt-num{margin-left:auto;text-align:right;position:relative;z-index:1}
    .rcpt-num-val{font-family:'DM Mono',monospace;font-size:18px;font-weight:500;color:#fff}
    .rcpt-num-lbl{font-size:10px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.1em;margin-top:2px}

    .rcpt-body{padding:28px 32px}

    .rcpt-amount-box{background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border:2px solid #bae6fd;border-radius:12px;padding:20px 24px;margin-bottom:24px;text-align:center}
    .rcpt-amt-lbl{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.15em;color:#0369a1;margin-bottom:6px}
    .rcpt-amt-val{font-family:'Cormorant Garamond',serif;font-size:48px;font-weight:700;color:#0c4a6e;line-height:1}
    .rcpt-amt-sub{font-size:12px;color:#0369a1;margin-top:6px}

    /* Receipt info table */
    .rcpt-section-title{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.18em;color:#64748b;margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid #e2e8f0}
    .rcpt-row{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #f1f5f9}
    .rcpt-row:last-child{border-bottom:none}
    .rcpt-row-lbl{font-size:12px;color:#64748b;font-weight:500}
    .rcpt-row-val{font-size:12.5px;color:#0f172a;font-weight:600;text-align:right}
    .rcpt-row-val.mono{font-family:'DM Mono',monospace}

    /* Breakdown table in receipt */
    .rcpt-bk-table{width:100%;border-collapse:collapse;font-size:12px}
    .rcpt-bk-table th{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#94a3b8;text-align:left;padding:8px 10px;background:#f8fafc;border:1px solid #e2e8f0}
    .rcpt-bk-table td{padding:8px 10px;border:1px solid #f1f5f9;color:#334155;font-family:'DM Mono',monospace}
    .rcpt-bk-table tr:last-child td{font-weight:700;background:#f0fdf4;color:#15803d}

    .rcpt-footer{background:#f8fafc;border-top:2px dashed #e2e8f0;padding:20px 32px}
    .rcpt-verified{display:flex;align-items:center;gap:10px;margin-bottom:12px}
    .rcpt-verified-ic{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#0B8FAC,#076E86);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
    .rcpt-verified-txt{font-size:11px;color:#475569;line-height:1.5}
    .rcpt-qr{width:60px;height:60px;background:#0D1B2A;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#0B8FAC;font-size:10px;font-weight:700;letter-spacing:.05em;margin-left:auto}
    .rcpt-small{font-size:10px;color:#94a3b8;text-align:center;margin-top:12px}

    /* Receipt controls */
    .rcpt-controls{background:var(--navy-mid);padding:14px 24px;display:flex;align-items:center;gap:10px;border-top:1px solid var(--navy-line)}

    /* Watermark on void receipts */
    .rcpt-watermark{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-30deg);font-size:80px;font-weight:900;color:rgba(239,68,68,.08);pointer-events:none;white-space:nowrap;text-transform:uppercase;letter-spacing:.1em}

    /* Success animation */
    @keyframes popIn{0%{transform:scale(0) rotate(-15deg)}60%{transform:scale(1.15) rotate(3deg)}100%{transform:scale(1) rotate(0)}}
    .success-ic{animation:popIn .5s cubic-bezier(.175,.885,.32,1.275) both;font-size:56px;margin-bottom:16px;display:block;text-align:center}

    /* Progress bar */
    .prog-bar{height:5px;background:var(--navy-line);border-radius:99px;overflow:hidden}
    .prog-fill{height:100%;border-radius:99px;transition:width .6s ease}

    /* Method tabs */
    .method-tabs{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:0}
    .method-tab{border:2px solid var(--navy-line);border-radius:8px;padding:9px 6px;cursor:pointer;transition:all .15s;text-align:center;font-size:11px;font-weight:700;color:var(--slate);background:none}
    .method-tab:hover{border-color:rgba(11,143,172,.4);color:var(--white)}
    .method-tab.sel-m{border-color:var(--teal);background:rgba(11,143,172,.1);color:var(--teal-lt)}
    .method-tab-ic{font-size:18px;display:block;margin-bottom:4px}

    /* Info rows */
    .info-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(30,52,80,.5)}
    .info-row:last-child{border-bottom:none}
    .info-lbl{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--slate)}
    .info-val{font-size:12.5px;color:var(--white);text-align:right}
    .info-val.mono{font-family:'DM Mono',monospace}

    /* Toast */
    @keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
    .anim{animation:fadeUp .35s ease both}
    .d1{animation-delay:.05s}.d2{animation-delay:.10s}.d3{animation-delay:.15s}.d4{animation-delay:.20s}
    .toast-msg{position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:999;box-shadow:0 8px 24px rgba(0,0,0,.4);animation:fadeUp .3s ease}
    [x-cloak]{display:none!important}
    ::-webkit-scrollbar{width:5px} ::-webkit-scrollbar-track{background:transparent} ::-webkit-scrollbar-thumb{background:var(--navy-line);border-radius:99px}

    /* Daily summary bar */
    .day-bar{background:linear-gradient(135deg,rgba(34,197,94,.12),rgba(34,197,94,.04));border:1px solid rgba(34,197,94,.2);border-radius:10px;padding:14px 20px;display:flex;align-items:center;gap:24px;margin-bottom:24px}
    .day-bar-item{text-align:center}
    .day-bar-val{font-family:'DM Mono',monospace;font-size:20px;font-weight:500;color:var(--white)}
    .day-bar-lbl{font-size:10px;color:var(--slate);text-transform:uppercase;letter-spacing:.08em;font-weight:600;margin-top:3px}
    .day-bar-div{width:1px;background:rgba(34,197,94,.2);height:36px}
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

/* ══════════════════════════════════════════════════════════════════════════
   PRINT / SAVE AS PDF
   Isolates the receipt paper so only it prints, preserving exact layout.
══════════════════════════════════════════════════════════════════════════ */
@media print {
  @page {
    size: A4 portrait;
    margin: 12mm 15mm;
  }

  /* Hide everything, then selectively show only the receipt */
  body * { visibility: hidden !important; }
  .receipt-paper, .receipt-paper * { visibility: visible !important; }

  /* Position receipt at top of page, full width */
  .receipt-paper {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    overflow: visible !important;
  }

  /* Preserve all background colours and gradients */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }

  /* Remove overlay chrome */
  .rcpt-controls { display: none !important; }

  /* Keep table borders visible */
  .rcpt-bk-table, .rcpt-bk-table th, .rcpt-bk-table td {
    border: 1px solid #e2e8f0 !important;
  }

  /* No page break inside receipt sections */
  .rcpt-header, .rcpt-amount-box, .rcpt-body, .rcpt-footer {
    page-break-inside: avoid;
    break-inside: avoid;
  }
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
      <a class="nav-item" href="/loans"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Loans</a>
      <a class="nav-item active" href="/payments"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>Payments</a>
      <a class="nav-item" href="/collateral"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Collateral</a>
    </div>
    <div class="nav-sect">
      <div class="nav-lbl">Schedule</div>
      <a class="nav-item" href="/calendar"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Calendar<span class="nav-bdg" style="background:var(--amber);color:#000" x-show="(stats.due_today?.count ?? 0) > 0" x-text="stats.due_today?.count ?? 0">0</span></a>
      <a class="nav-item" href="/overdue"><svg class="nav-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Overdue<span class="nav-bdg" style="background:var(--red);color:#fff" x-show="(stats.overdue?.total_loans ?? 0) > 0" x-text="stats.overdue?.total_loans ?? 0">0</span></a>
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
    <header class="topbar">
      <div>
        <div class="page-title" x-show="view==='list'">Payments</div>
        <div x-show="view==='record'" style="display:none"><div class="breadcrumb"><span @click="view='list'">Payments</span> &nbsp;/&nbsp; <span style="color:var(--white)">Record Payment</span></div></div>
      </div>
      <div class="tb-right">
        <template x-if="view==='list'">
          <div class="flex aic g8">
            <button class="btn-g" @click="showToast('Exporting payments to CSV…')"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Export CSV</button>
            <button class="btn-g" @click="showToast('Generating daily report…')"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Daily Report</button>
            <button class="btn-p" @click="view='record';resetForm()">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Record Payment
            </button>
          </div>
        </template>
        <template x-if="view==='record'">
          <button class="btn-g" @click="view='list'">✕ Cancel</button>
        </template>
      </div>
    </header>

    <div class="content">

      <!-- ═══════════ PAYMENTS LIST ═══════════ -->
      <div x-show="view==='list'" x-transition>

        <!-- Today's collection bar -->
        <div class="day-bar anim">
          <div class="day-bar-item">
            <div class="day-bar-val" x-text="_fmtK(stats.payments?.today_total ?? 0)">K —</div>
            <div class="day-bar-lbl">Collected Today</div>
          </div>
          <div class="day-bar-div"></div>
          <div class="day-bar-item">
            <div class="day-bar-val" style="color:var(--amber)" x-text="_fmtK(Math.max(0, (stats.due_today?.expected ?? 0) - (stats.payments?.today_total ?? 0)))">K —</div>
            <div class="day-bar-lbl">Still Expected</div>
          </div>
          <div class="day-bar-div"></div>
          <div class="day-bar-item">
            <div class="day-bar-val" x-text="stats.payments?.today_count ?? 0">—</div>
            <div class="day-bar-lbl">Payments Today</div>
          </div>
          <div class="day-bar-div"></div>
          <div class="day-bar-item">
            <div class="day-bar-val" style="color:var(--purple)" x-text="_fmtK(stats.payments?.month_total ?? 0)">K —</div>
            <div class="day-bar-lbl">Month Total</div>
          </div>
          <div class="day-bar-div"></div>
          <div class="day-bar-item">
            <div class="day-bar-val" x-text="stats.payments?.total_count ?? 0">—</div>
            <div class="day-bar-lbl">Total Receipts</div>
          </div>
          <div style="margin-left:auto">
            <div class="xs ts mb8">Monthly target progress</div>
            <div class="prog-bar" style="width:200px;height:8px">
              <div class="prog-fill" :style="`width:${Math.min(100,Math.round((stats.payments?.month_total??0)/400000*100))}%;background:var(--green)`"></div>
            </div>
            <div class="xs ts mt4" x-text="Math.min(100,Math.round((stats.payments?.month_total??0)/400000*100))+'% of K 400,000 target'">—% of K 400,000 target</div>
          </div>
        </div>

        <!-- Stats -->
        <div class="stats-row anim d1">
          <div class="m-stat">
            <div class="m-stat-ic" style="background:rgba(34,197,94,.15)">💵</div>
            <div>
              <div class="m-stat-val" x-text="stats.payments?.instalment ?? '-'"></div>
              <div class="m-stat-lbl">Instalment Payments</div>
              <div class="m-stat-sub">This month</div>
            </div>
          </div>
          <div class="m-stat">
            <div class="m-stat-ic" style="background:rgba(245,166,35,.15)">💰</div>
            <div>
              <div class="m-stat-val" x-text="stats.payments?.partial ?? '-'"></div>
              <div class="m-stat-lbl">Partial Payments</div>
              <div class="m-stat-sub">This month</div>
            </div>
          </div>
          <div class="m-stat">
            <div class="m-stat-ic" style="background:rgba(129,140,248,.15)">🎉</div>
            <div>
              <div class="m-stat-val" x-text="stats.payments?.early_settlement ?? '-'"></div>
              <div class="m-stat-lbl">Early Settlements</div>
              <div class="m-stat-sub">This month</div>
            </div>
          </div>
          <div class="m-stat">
            <div class="m-stat-ic" style="background:rgba(239,68,68,.15)">⚠️</div>
            <div>
              <div class="m-stat-val" x-text="stats.payments?.penalty ?? '-'"></div>
              <div class="m-stat-lbl">Penalty Payments</div>
              <div class="m-stat-sub">This month</div>
            </div>
          </div>
        </div>

        <!-- Filter bar -->
        <div class="filter-bar anim d2">
          <div class="srch-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input class="srch" type="text" placeholder="Search receipt, borrower, loan number…" x-model="q" @input="doFilter()">
          </div>
          <select class="sel" x-model="fType" @change="doFilter()">
            <option value="">All Types</option>
            <option value="instalment">Full Instalment</option>
            <option value="partial">Partial</option>
            <option value="early_settlement">Early Settlement</option>
            <option value="penalty">Penalty</option>
          </select>
          <select class="sel" x-model="fMethod" @change="doFilter()">
            <option value="">All Methods</option>
            <option value="Cash">Cash</option>
            <option value="Mobile Money">Mobile Money</option>
            <option value="Bank Transfer">Bank Transfer</option>
          </select>
          <input class="sel" type="date" x-model="fDate" @change="doFilter()" style="color:var(--text)">
        </div>

        <!-- Table -->
        <div class="tbl-wrap anim d3">
          <table class="dtbl">
            <thead>
              <tr>
                <th>Receipt</th>
                <th>Borrower / Loan</th>
                <th>Type</th>
                <th>Method</th>
                <th>Principal</th>
                <th>Interest</th>
                <th>Penalty</th>
                <th>Total Paid</th>
                <th>Principal Bal. After</th>
                <th>Date & Time</th>
                <th>Officer</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <template x-if="rows.length === 0">
                <tr><td colspan="12" style="text-align:center;padding:40px;color:var(--slate)">No payments found. Try adjusting your filters or record a new payment.</td></tr>
              </template>
              <template x-for="p in pagedRows" :key="p.id">
                <tr @click="openReceipt(p)">
                  <td class="mono sm tc" x-text="p.rc"></td>
                  <td>
                    <div class="flex aic g10">
                      <div class="l-av" :style="`background:linear-gradient(135deg,${p.c1},${p.c2})`" x-text="p.ini" style="width:30px;height:30px;font-size:11px"></div>
                      <div>
                        <div class="f6 tw" style="font-size:13px" x-text="p.borrower"></div>
                        <div class="xs ts mono" x-text="p.loan"></div>
                      </div>
                    </div>
                  </td>
                  <td><span class="badge" :class="`b-${p.typeClass}`" x-text="p.typeLabel"></span></td>
                  <td>
                    <div class="flex aic g6">
                      <span x-text="p.methodIc" style="font-size:14px"></span>
                      <span class="xs ts" x-text="p.method"></span>
                    </div>
                  </td>
                  <td class="mono sm" x-text="p.prin||'—'"></td>
                  <td class="mono sm tc" x-text="p.int||'—'"></td>
                  <td class="mono sm" :class="p.pen&&p.pen!=='—'?'tr':'ts'" x-text="p.pen||'—'"></td>
                  <td class="mono f6 tg" x-text="p.total"></td>
                  <td class="mono sm" :class="p.bal==='K 0'?'ts':'ta'" x-text="p.bal"></td>
                  <td class="xs ts" x-text="p.dt"></td>
                  <td class="xs ts" x-text="p.officer"></td>
                  <td>
                    <div class="flex g6">
                      <button class="btn-g btn-xs" @click.stop="openReceipt(p)">🧾 View</button>
                      <button class="btn-g btn-xs" @click.stop="printReceipt(p)">🖨️</button>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
          <div class="pgn">
            <div class="pgn-info">Showing <strong x-text="Math.min(page*perPage,rows.length)"></strong> of <strong x-text="rows.length"></strong> payments</div>
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

      <!-- ═══════════ RECORD PAYMENT FORM ═══════════ -->
      <div x-show="view==='record'" x-transition style="display:none">
        <div class="pay-layout">

          <!-- Left: form -->
          <div>

            <!-- Step 1: Select Loan -->
            <div class="fsec anim">
              <div class="fsec-hd">
                <div class="fsec-ic" style="background:rgba(11,143,172,.15)">🔍</div>
                <div class="fsec-title">Step 1 — Find Loan</div>
                <div class="badge b-full" style="margin-left:auto" x-show="f.loan">✓ Selected</div>
              </div>
              <div style="padding:18px">
                <div class="field mb16">
                  <label>Search by Borrower Name or Loan Number <span class="req">*</span></label>
                  <input class="finput" type="text" placeholder="e.g. Bwalya Mwanza or LN-20260032" x-model="f.loanQ" @input="searchLoans()">
                </div>

                <!-- Search results -->
                <div x-show="loanResults.length>0 && !f.loan" style="background:var(--navy-mid);border:1px solid var(--navy-line);border-radius:10px;overflow:hidden;margin-bottom:0">
                  <template x-for="l in loanResults" :key="l.id">
                    <div class="loan-opt" @click="selectLoan(l)">
                      <div class="l-av" :style="`background:linear-gradient(135deg,${l.c1},${l.c2})`" x-text="l.ini"></div>
                      <div style="flex:1">
                        <div class="f6 tw sm" x-text="l.borrower"></div>
                        <div class="xs ts mono mt4" x-text="l.num + ' · ' + l.product"></div>
                      </div>
                      <div style="text-align:right">
                        <div class="mono sm ta" x-text="l.outstanding"></div>
                        <div class="xs ts mt4">outstanding</div>
                      </div>
                      <span class="badge xs" :class="`b-${l.schedStatus}`" x-text="l.schedStatus==='overdue'?'Overdue':'Due'"></span>
                    </div>
                  </template>
                </div>

                <!-- Selected loan card -->
                <div class="sel-loan" x-show="f.loan">
                  <div class="flex aic jb">
                    <div class="flex aic g12">
                      <div class="l-av" :style="`background:linear-gradient(135deg,${f.loan?.c1},${f.loan?.c2})`" x-text="f.loan?.ini" style="width:40px;height:40px;font-size:14px"></div>
                      <div>
                        <div class="f7 tw" x-text="f.loan?.borrower"></div>
                        <div class="xs ts mono mt4" x-text="f.loan?.num + ' · ' + f.loan?.product"></div>
                      </div>
                    </div>
                    <button class="xs ts" style="background:none;border:none;cursor:pointer" @click="f.loan=null;f.loanQ='';f.instalment=null;f.amount='';recalc()">Change</button>
                  </div>
                  <div class="sel-loan-grid">
                    <div><div class="sel-loan-item-lbl">Outstanding</div><div class="sel-loan-item-val ta" x-text="f.loan?.outstanding"></div></div>
                    <div><div class="sel-loan-item-lbl">Monthly Instalment</div><div class="sel-loan-item-val" x-text="f.loan?.monthly"></div></div>
                    <div><div class="sel-loan-item-lbl">Penalty Balance</div><div class="sel-loan-item-val" :class="f.loan?.penaltyBal!=='K 0'?'tr':'ts'" x-text="f.loan?.penaltyBal"></div></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 2: Select Instalment -->
            <div class="fsec anim d1" x-show="f.loan">
              <div class="fsec-hd">
                <div class="fsec-ic" style="background:rgba(245,166,35,.15)">📅</div>
                <div class="fsec-title">Step 2 — Select Instalment</div>
              </div>
              <div style="padding:18px">
                <template x-for="inst in (f.loan?.schedule||[])" :key="inst.n">
                  <div class="inst-card" :class="{'selected':f.instalment?.n===inst.n, 'overdue-card':inst.status==='overdue'}" @click="selectInstalment(inst)">
                    <div class="flex aic jb">
                      <div>
                        <div class="flex aic g8 mb4">
                          <div class="f6 tw sm">Instalment #<span x-text="inst.n"></span></div>
                          <span class="badge xs" :class="inst.status==='overdue'?'b-penalty':'b-partial'" x-text="inst.status==='overdue'?'OVERDUE':'DUE'"></span>
                        </div>
                        <div class="xs ts" x-text="'Due: ' + inst.due"></div>
                      </div>
                      <div style="text-align:right">
                        <div class="mono f6 ta" x-text="inst.totalWithPen||inst.total"></div>
                        <div class="xs ts mt4" x-text="inst.penAmt?'incl. K'+inst.penAmt+' penalty':inst.balance?'K '+inst.balance+' remaining':''"></div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <!-- Step 3: Payment details -->
            <div class="fsec anim d2" x-show="f.loan">
              <div class="fsec-hd">
                <div class="fsec-ic" style="background:rgba(34,197,94,.15)">💳</div>
                <div class="fsec-title">Step 3 — Payment Details</div>
              </div>
              <div style="padding:18px">

                <!-- Payment type -->
                <div class="field mb16">
                  <label>Payment Type <span class="req">*</span></label>
                  <div class="type-pills">
                    <div class="type-pill" :class="{'sel-pill':f.payType==='full'}" @click="f.payType='full';setFullAmount()">
                      <div class="type-pill-ic">💵</div>
                      <div class="type-pill-lbl">Full Instalment</div>
                    </div>
                    <div class="type-pill" :class="{'sel-pill':f.payType==='partial'}" @click="f.payType='partial';f.amount='';recalc()">
                      <div class="type-pill-ic">💰</div>
                      <div class="type-pill-lbl">Partial Payment</div>
                    </div>
                    <div class="type-pill" :class="{'sel-pill':f.payType==='settle'}" @click="f.payType='settle';setSettleAmount()">
                      <div class="type-pill-ic">🎉</div>
                      <div class="type-pill-lbl">Early Settlement</div>
                    </div>
                  </div>
                </div>

                <!-- Amount field -->
                <div class="field mb16">
                  <label>Amount Received (K) <span class="req">*</span></label>
                  <input class="finput large" type="number" placeholder="0.00" x-model="f.amount" @input="recalc()" :readonly="f.payType==='full'||f.payType==='settle'">
                  <div class="fhint" x-show="f.payType==='partial' && f.instalment">
                    Instalment due: <span x-text="f.instalment?.total"></span> · Enter any amount up to the full instalment
                  </div>
                  <div class="fhint" x-show="f.payType==='full'">Amount auto-set to the full instalment due</div>
                  <div class="fhint" x-show="f.payType==='settle'" style="color:var(--purple)">Full outstanding balance — no discount applied</div>
                </div>

                <!-- Payment method -->
                <div class="field mb16">
                  <label>Payment Method <span class="req">*</span></label>
                  <div class="method-tabs">
                    <button class="method-tab" :class="{'sel-m':f.method==='Cash'}" @click="f.method='Cash'"><span class="method-tab-ic">💵</span>Cash</button>
                    <button class="method-tab" :class="{'sel-m':f.method==='Mobile Money'}" @click="f.method='Mobile Money'"><span class="method-tab-ic">📱</span>Mobile Money</button>
                    <button class="method-tab" :class="{'sel-m':f.method==='Bank Transfer'}" @click="f.method='Bank Transfer'"><span class="method-tab-ic">🏦</span>Bank Transfer</button>
                    <button class="method-tab" :class="{'sel-m':f.method==='Cheque'}" @click="f.method='Cheque'"><span class="method-tab-ic">📝</span>Cheque</button>
                  </div>
                </div>

                <!-- Method-specific reference fields -->
                <div class="field mb16" x-show="f.method==='Mobile Money'">
                  <label>Mobile Money Reference <span class="req">*</span></label>
                  <input class="finput mono" type="text" placeholder="e.g. AHB7X291KZ" x-model="f.reference">
                  <div class="fhint">Transaction reference from Airtel / MTN / Zamtel Money</div>
                </div>
                <div class="field mb16" x-show="f.method==='Bank Transfer'">
                  <label>Bank Reference / Narration <span class="req">*</span></label>
                  <input class="finput mono" type="text" placeholder="Bank transaction reference" x-model="f.reference">
                </div>
                <div class="field mb16" x-show="f.method==='Cheque'">
                  <label>Cheque Number <span class="req">*</span></label>
                  <input class="finput mono" type="text" placeholder="Cheque number" x-model="f.reference">
                </div>

                <!-- Date -->
                <div class="field mb16">
                  <label>Payment Date <span class="req">*</span></label>
                  <input class="finput" type="date" x-model="f.date">
                  <div class="fhint">Use today's date unless recording a backdated payment</div>
                </div>

                <!-- Notes -->
                <div class="field">
                  <label>Notes (Optional)</label>
                  <input class="finput" type="text" placeholder="Any relevant notes about this payment…" x-model="f.notes">
                </div>
              </div>
              <div class="form-actions">
                <button class="btn-g" @click="view='list'">Cancel</button>
                <button class="btn-green"
                  :disabled="!f.loan||!f.amount||!f.method||!f.payType"
                  :style="!f.loan||!f.amount||!f.method||!f.payType?'opacity:.5;cursor:not-allowed':''"
                  @click="submitPayment()">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  Record & Generate Receipt
                </button>
              </div>
            </div>

          </div>

          <!-- Right: live breakdown -->
          <div>
            <div class="breakdown anim d2">
              <div class="breakdown-title">⚡ Live Payment Breakdown</div>

              <div x-show="!f.loan || !f.amount || !parseFloat(f.amount)" style="text-align:center;padding:20px;color:var(--slate);font-size:12px">
                Select a loan and enter an amount to see the live breakdown
              </div>

              <template x-if="f.loan && f.amount && parseFloat(f.amount)>0">
                <div>
                  <div class="bk-row"><div class="bk-lbl">Amount Received</div><div class="bk-val credit" x-text="'K ' + Number(f.amount).toLocaleString()"></div></div>
                  <div class="bk-divider"></div>

                  <div class="bk-row"><div class="bk-lbl">→ Applied to Penalty</div><div class="bk-val penalty" x-text="'K ' + breakdown.towardsPenalty.toLocaleString()"></div></div>
                  <div class="bk-row"><div class="bk-lbl">→ Applied to Interest</div><div class="bk-val" x-text="'K ' + breakdown.towardsInterest.toLocaleString()"></div></div>
                  <div class="bk-row"><div class="bk-lbl">→ Applied to Principal</div><div class="bk-val" x-text="'K ' + breakdown.towardsPrincipal.toLocaleString()"></div></div>
                  <div class="bk-divider"></div>

                  <div class="bk-row"><div class="bk-lbl">Instalment Remaining</div><div class="bk-val debit" x-text="breakdown.instalRemaining>0 ? 'K ' + breakdown.instalRemaining.toLocaleString() : 'K 0 ✓'"></div></div>
                  <div class="bk-divider"></div>

                  <div class="bk-row" style="padding-top:8px"><div class="bk-lbl f7 tw">New Outstanding Balance</div><div class="bk-val balance" x-text="'K ' + breakdown.newBalance.toLocaleString()"></div></div>

                  <!-- Partial progress -->
                  <div x-show="f.payType==='partial' && f.instalment" style="margin-top:14px">
                    <div class="flex jb aic mb8">
                      <div class="xs ts">Instalment coverage</div>
                      <div class="xs f6" :class="breakdown.pct>=100?'tg':'ta'" x-text="Math.min(breakdown.pct,100) + '%'"></div>
                    </div>
                    <div class="prog-bar" style="height:8px">
                      <div class="prog-fill" :style="`width:${Math.min(breakdown.pct,100)}%;background:${breakdown.pct>=100?'var(--green)':'var(--amber)'}`"></div>
                    </div>
                    <div class="xs ts mt8" x-show="breakdown.pct<100">Partial — instalment will remain open</div>
                    <div class="xs tg mt8" x-show="breakdown.pct>=100">✓ Fully covers this instalment</div>
                  </div>

                  <!-- Settlement info -->
                  <div x-show="f.payType==='settle'" style="margin-top:14px;padding:12px;background:rgba(129,140,248,.1);border:1px solid rgba(129,140,248,.2);border-radius:8px">
                    <div class="xs tp f6 mb4">Full Settlement</div>
                    <div class="xs ts mt4">Loan will be <strong style="color:var(--white)">closed</strong> upon payment. No discounts applied.</div>
                  </div>

                  <!-- Receipt preview hint -->
                  <div style="margin-top:16px;padding:10px 12px;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.15);border-radius:8px">
                    <div class="xs tg f6">✓ A PDF receipt will be generated automatically</div>
                    <div class="xs ts mt4">Receipt #: <span class="mono">RCP-<span x-text="nextRcptNum"></span></span></div>
                  </div>
                </div>
              </template>

              <!-- Loan summary (always visible when loan selected) -->
              <div x-show="f.loan" style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(30,52,80,.5)">
                <div class="breakdown-title" style="margin-bottom:12px">Loan Summary</div>
                <div class="bk-row"><div class="bk-lbl">Borrower</div><div class="bk-val sm" style="font-family:'DM Sans',sans-serif" x-text="f.loan?.borrower"></div></div>
                <div class="bk-row"><div class="bk-lbl">Loan</div><div class="bk-val mono sm tc" x-text="f.loan?.num"></div></div>
                <div class="bk-row"><div class="bk-lbl">Interest Rate</div><div class="bk-val sm" x-text="f.loan?.rate"></div></div>
                <div class="bk-row"><div class="bk-lbl">Instalments</div><div class="bk-val sm" x-text="f.loan?.instProgress"></div></div>
                <div class="bk-row" style="padding-bottom:0;border-bottom:none"><div class="bk-lbl">Outstanding</div><div class="bk-val sm ta" x-text="f.loan?.outstanding"></div></div>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div><!-- /content -->
  </main>

  <!-- ═══════════ RECEIPT MODAL ═══════════ -->
  <div class="receipt-overlay" x-show="showReceipt" x-transition style="display:none" @click.self="showReceipt=false">
    <div style="width:100%;max-width:640px">
      <!-- Controls above receipt -->
      <div class="rcpt-controls" style="border-radius:12px 12px 0 0;margin-bottom:0">
        <div class="xs ts">Receipt Preview</div>
        <div style="margin-left:auto;display:flex;gap:8px">
          <button class="btn-g btn-sm" @click="window.print()" title="Opens print dialog — choose 'Save as PDF' to download">⬇ Save as PDF / Print</button>
          <button class="btn-g btn-sm" @click="showReceipt=false">✕ Close</button>
        </div>
      </div>

      <!-- The actual receipt -->
      <div class="receipt-shell">
        <template x-if="activeReceipt">
          <div class="receipt-paper">

            <!-- Header -->
            <div class="rcpt-header">
              <div class="flex aic jb">
                <div>
                  <div class="rcpt-org">Gracimor Loans</div>
                  <div class="rcpt-org-sub">Management System · Lusaka, Zambia</div>
                  <div class="rcpt-badge">✓ Official Payment Receipt</div>
                </div>
                <div class="rcpt-num">
                  <div class="rcpt-num-val" x-text="activeReceipt.rc"></div>
                  <div class="rcpt-num-lbl">Receipt Number</div>
                  <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:6px" x-text="activeReceipt.dt"></div>
                </div>
              </div>
            </div>

            <div class="rcpt-body">

              <!-- Big amount box -->
              <div class="rcpt-amount-box">
                <div class="rcpt-amt-lbl">Amount Paid</div>
                <div class="rcpt-amt-val" x-text="activeReceipt.total"></div>
                <div class="rcpt-amt-sub" x-text="activeReceipt.amountWords"></div>
              </div>

              <!-- Two-column info -->
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:20px">
                <!-- Borrower info -->
                <div>
                  <div class="rcpt-section-title">Borrower Information</div>
                  <div class="rcpt-row"><div class="rcpt-row-lbl">Full Name</div><div class="rcpt-row-val" x-text="activeReceipt.borrower"></div></div>
                  <div class="rcpt-row"><div class="rcpt-row-lbl">Borrower ID</div><div class="rcpt-row-val mono" x-text="activeReceipt.bnum||'—'"></div></div>
                  <div class="rcpt-row"><div class="rcpt-row-lbl">Loan Number</div><div class="rcpt-row-val mono" x-text="activeReceipt.loan"></div></div>
                  <div class="rcpt-row" style="border-bottom:none"><div class="rcpt-row-lbl">Instalment</div><div class="rcpt-row-val" x-text="activeReceipt.instalment||'—'"></div></div>
                </div>
                <!-- Payment info -->
                <div>
                  <div class="rcpt-section-title">Payment Information</div>
                  <div class="rcpt-row"><div class="rcpt-row-lbl">Date & Time</div><div class="rcpt-row-val" x-text="activeReceipt.dt"></div></div>
                  <div class="rcpt-row"><div class="rcpt-row-lbl">Method</div><div class="rcpt-row-val"><span x-text="activeReceipt.methodIc"></span> <span x-text="activeReceipt.method"></span></div></div>
                  <div class="rcpt-row" x-show="activeReceipt.ref"><div class="rcpt-row-lbl">Reference</div><div class="rcpt-row-val mono" x-text="activeReceipt.ref||'—'"></div></div>
                  <div class="rcpt-row" style="border-bottom:none"><div class="rcpt-row-lbl">Recorded By</div><div class="rcpt-row-val" x-text="activeReceipt.officer"></div></div>
                </div>
              </div>

              <!-- Payment breakdown table -->
              <div style="margin-bottom:20px">
                <div class="rcpt-section-title">Payment Breakdown</div>
                <table class="rcpt-bk-table">
                  <thead>
                    <tr><th>Description</th><th style="text-align:right">Amount (K)</th></tr>
                  </thead>
                  <tbody>
                    <template x-if="activeReceipt.prin&&activeReceipt.prin!=='—'">
                      <tr><td>Principal Repaid</td><td style="text-align:right" x-text="activeReceipt.prin"></td></tr>
                    </template>
                    <template x-if="activeReceipt.int&&activeReceipt.int!=='—'">
                      <tr><td>Interest Paid</td><td style="text-align:right" x-text="activeReceipt.int"></td></tr>
                    </template>
                    <template x-if="activeReceipt.pen&&activeReceipt.pen!=='—'">
                      <tr><td style="color:#ef4444">Penalty Paid</td><td style="text-align:right;color:#ef4444" x-text="activeReceipt.pen"></td></tr>
                    </template>
                    <tr style="background:#f0fdf4">
                      <td style="font-weight:700;color:#15803d">Total Received</td>
                      <td style="text-align:right;font-weight:700;color:#15803d" x-text="activeReceipt.total"></td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Balance info -->
              <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:12px 16px;margin-bottom:20px">
                <table style="width:100%;border-collapse:collapse;table-layout:fixed">
                  <tr>
                    <td style="width:33.33%;vertical-align:top;padding:0">
                      <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#9a3412;margin-bottom:4px">Total Balance Before</div>
                      <div style="font-family:'DM Mono',monospace;font-size:15px;font-weight:600;color:#7c2d12" x-text="activeReceipt.balBefore||'—'"></div>
                      <div style="font-size:9px;color:#c2410c;margin-top:2px">outstanding</div>
                    </td>
                    <td style="width:33.33%;vertical-align:top;padding:0;text-align:center">
                      <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#9a3412;margin-bottom:4px">Payment Applied</div>
                      <div style="font-family:'DM Mono',monospace;font-size:15px;font-weight:600;color:#166534" x-text="'− ' + activeReceipt.total"></div>
                      <div style="font-size:9px;color:#c2410c;margin-top:2px">principal + interest</div>
                    </td>
                    <td style="width:33.33%;vertical-align:top;padding:0;text-align:right">
                      <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#9a3412;margin-bottom:4px">Total Balance After</div>
                      <div style="font-family:'DM Mono',monospace;font-size:15px;font-weight:600;color:#7c2d12" x-text="activeReceipt.bal==='K 0'?'K 0 (Settled)':activeReceipt.bal"></div>
                      <div style="font-size:9px;color:#c2410c;margin-top:2px">outstanding</div>
                    </td>
                  </tr>
                </table>
              </div>

              <!-- Type badge -->
              <div style="text-align:center;margin-bottom:4px">
                <span x-show="activeReceipt.type==='early_settlement'" style="display:inline-flex;align-items:center;gap:6px;padding:6px 16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:99px;font-size:12px;font-weight:700;color:#166534">🎉 EARLY SETTLEMENT — LOAN CLOSED</span>
                <span x-show="activeReceipt.type==='instalment'" style="display:inline-flex;align-items:center;gap:6px;padding:6px 16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:99px;font-size:12px;font-weight:700;color:#166534">✓ FULL INSTALMENT PAID</span>
                <span x-show="activeReceipt.type==='partial'" style="display:inline-flex;align-items:center;gap:6px;padding:6px 16px;background:#fffbeb;border:1px solid #fde68a;border-radius:99px;font-size:12px;font-weight:700;color:#92400e">⚡ PARTIAL PAYMENT RECORDED</span>
              </div>
            </div>

            <!-- Footer -->
            <div class="rcpt-footer">
              <div class="rcpt-verified flex aic g12">
                <div class="rcpt-verified-ic">✓</div>
                <div class="rcpt-verified-txt">
                  This receipt was generated by Gracimor Loans Management System and is valid proof of payment.<br>
                  For queries contact: +260 211 XXX XXX · <a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="3d54535b527d5a4f5c4e4e58505c135e52134750">[email&#160;protected]</a>
                </div>
                <div class="rcpt-qr">QR<br>CODE</div>
              </div>
              <div class="rcpt-small">
                Gracimor Loans · Lusaka, Zambia · This is a computer-generated receipt and requires no signature.
              </div>
            </div>

          </div>
        </template>
      </div>
    </div>
  </div>

  <!-- Success overlay (after submitting payment) -->
  <div class="receipt-overlay" x-show="showSuccess" x-transition style="display:none">
    <div style="background:var(--navy-card);border:1px solid var(--navy-line);border-radius:20px;padding:clamp(24px,5vw,48px);text-align:center;max-width:460px;width:calc(100% - 32px);box-sizing:border-box">
      <span class="success-ic">✅</span>
      <div style="font-family:'Playfair Display',serif;font-size:clamp(18px,4vw,24px);color:var(--white);margin-bottom:8px">Payment Recorded!</div>
      <div class="sm ts mb20">The payment has been saved and the loan balance updated in real-time.</div>
      <div style="background:var(--navy-mid);border-radius:10px;padding:16px;margin-bottom:20px">
        <div class="mono f6 tc" style="font-size:clamp(14px,4vw,18px)" x-text="'Receipt: ' + successData.rc"></div>
        <div class="xs ts mt4" x-text="successData.borrower + ' · ' + successData.loan"></div>
        <div class="mono tg f7 mt8" x-text="successData.amount + ' received'"></div>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center">
        <button class="btn-g" @click="showSuccess=false;view='list'">← Back to Payments</button>
        <button class="btn-p" @click="showSuccess=false;openReceipt(lastPayment)">🧾 View Receipt</button>
        <button class="btn-green" @click="showSuccess=false;resetForm()">+ Record Another</button>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div class="toast-msg" x-show="toast" x-transition style="display:none;background:var(--green);color:#fff" x-text="toastMsg"></div>

  <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script>
  function app() {
      return {
      view: 'list',
      q: '', fType: '', fMethod: '', fDate: '',
      showReceipt: false, activeReceipt: null,
      showSuccess: false, lastPayment: null, successData: { rc:'', borrower:'', loan:'', amount:'' },
      toast: false, toastMsg: '',
      stats: { payments: { instalment:'-', partial:'-', early_settlement:'-', penalty:'-', month_total:0, today_total:0, today_count:0, total_count:0 }, due_today: { count:0, expected:0 } },
      page: 1, perPage: 10,
      nextRcptNum: '00001',
      loanResults: [],
      rows: [],
      all: [],

      f: {
        loanQ: '', loan: null, instalment: null,
        payType: '', amount: '', method: 'Cash',
        reference: '', date: new Date().toISOString().split('T')[0], notes: ''
      },

      breakdown: { towardsPenalty:0, towardsInterest:0, towardsPrincipal:0, instalRemaining:0, newBalance:0, pct:0 },

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
        const p = (name || '?').trim().split(/\s+/);
        return (p[0][0] + (p[1] ? p[1][0] : '')).toUpperCase();
      },
      _fmtK(v) {
        const n = parseFloat(v) || 0;
        return 'K ' + n.toLocaleString('en-ZM', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
      },
      _fmtDate(d) {
        if (!d) return '—';
        return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
      },
      _mapPayment(p) {
        const loan = p.loan || {};
        const borrower = loan.borrower || {};
        const nameParts = [borrower.first_name, borrower.last_name].filter(Boolean);
        const name = nameParts.length ? nameParts.join(' ') : '—';
        const [c1, c2] = this._avatarColor(name);
        const methIc  = { cash:'💵', bank_transfer:'🏦', mobile_money:'📱', cheque:'📋' };
        const typeLbl = { instalment:'Instalment', partial:'Partial', early_settlement:'Settlement', penalty:'Penalty' };
        const typeCls = { instalment:'full', partial:'partial', early_settlement:'settle', penalty:'penalty' };
        const penAmt  = parseFloat(p.towards_penalty) || 0;
        const rawType = p.payment_type || 'instalment';
        return {
          id:        p.id,
          rc:        p.receipt_number || ('RCP-' + String(p.id).padStart(5,'0')),
          borrower:  name,
          bnum:      borrower.borrower_number || '—',
          loan:      loan.loan_number || '—',
          ini:       this._initials(name !== '—' ? name : '?'),
          c1, c2,
          type:      rawType,
          typeClass: typeCls[rawType] || 'full',
          typeLabel: typeLbl[rawType] || rawType,
          method:    (p.payment_method || '').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()),
          methodIc:  methIc[p.payment_method] || '💰',
          prin:      this._fmtK(p.towards_principal),
          int:       this._fmtK(p.towards_interest),
          pen:       penAmt > 0 ? this._fmtK(penAmt) : '—',
          total:     this._fmtK(p.amount_received),
          bal:       p.balance_after != null ? this._fmtK(p.balance_after) : '—',
          balBefore: p.balance_before != null ? this._fmtK(p.balance_before) : '—',
          dt:        this._fmtDate(p.payment_date),
          officer:   p.recorded_by?.name || '—',
          ref:       p.reference || '',
          rawDate:   (p.payment_date || '').split('T')[0],
          instalment: '—',
          amountWords: '',
        };
      },

      get pagedRows() { const s=(this.page-1)*this.perPage; return this.rows.slice(s,s+this.perPage); },
      get totalPages() { return Math.max(1,Math.ceil(this.rows.length/this.perPage)); },
      showToast(msg) { this.toastMsg=msg; this.toast=true; setTimeout(()=>this.toast=false,3000); },

      async init(){
        await Promise.all([this.loadStats(), this.loadPayments()]);
      },

      async loadPayments() {
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch('/api/payments?per_page=200', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
          });
          if (!res.ok) return;
          const data = await res.json();
          this.all = (data.data || []).map(p => this._mapPayment(p));
          this.doFilter();
        } catch (e) { console.error('Load payments error:', e); }
      },
      async loadStats() {
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch('/api/stats', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
          if (res.ok) this.stats = await res.json();
        } catch {}
      },

      doFilter() {
        const q = this.q.toLowerCase();
        this.rows = this.all.filter(p => {
          const mq = !q || p.borrower.toLowerCase().includes(q) || p.rc.toLowerCase().includes(q) || p.loan.toLowerCase().includes(q);
          const mt = !this.fType   || p.type === this.fType;
          const mm = !this.fMethod || p.method.toLowerCase().replace(/ /g,'_') === this.fMethod;
          const md = !this.fDate   || p.rawDate === this.fDate;
          return mq && mt && mm && md;
        });
        this.page = 1;
      },

      openReceipt(p) { this.activeReceipt = p; this.showReceipt = true; },
      async printReceipt(p) {
        this.activeReceipt = p;
        this.showReceipt = true;
        // Wait for Alpine to render the receipt DOM before printing
        await this.$nextTick();
        setTimeout(() => window.print(), 200);
      },

      async searchLoans() {
        const q = this.f.loanQ.trim();
        if (!q || q.length < 2) { this.loanResults = []; return; }
        const token = localStorage.getItem('lms_token');
        try {
          const res = await fetch(`/api/loans?search=${encodeURIComponent(q)}&status=active,approved&per_page=6`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
          });
          if (!res.ok) { this.loanResults = []; return; }
          const data = await res.json();
          this.loanResults = (data.data || []).map(l => {
            const bname = (l.borrower?.first_name || '') + ' ' + (l.borrower?.last_name || '');
            const [c1, c2] = this._avatarColor(bname.trim());
            const bal = l.loan_balance;
            return {
              id: l.id,
              num: l.loan_number,
              borrower: bname.trim(),
              bnum: l.borrower?.borrower_number || '',
              ini: this._initials(bname.trim()),
              c1, c2,
              product: l.loan_product?.name || '—',
              outstanding: bal?.total_outstanding ? this._fmtK(bal.total_outstanding) : 'K 0',
              monthly: l.monthly_instalment ? this._fmtK(l.monthly_instalment) : '—',
              penaltyBal: bal?.penalty_outstanding ? this._fmtK(bal.penalty_outstanding) : 'K 0',
              instProgress: '—',
              schedStatus: 'due',
              schedule: [],
            };
          });
        } catch { this.loanResults = []; }
      },

      selectLoan(l) {
        this.f.loan = l;
        this.f.loanQ = l.borrower;
        this.loanResults = [];
        this.f.instalment = null;
        this.f.amount = '';
        this.recalc();
      },

      selectInstalment(inst) {
        this.f.instalment = inst;
        if (this.f.payType === 'full') this.setFullAmount();
        this.recalc();
      },

      setFullAmount() {
        if (!this.f.instalment) return;
        const amt = this.f.instalment.totalWithPen || this.f.instalment.total;
        this.f.amount = String(this.f.instalment.prin + this.f.instalment.int + this.f.instalment.pen);
        this.recalc();
      },

      setSettleAmount() {
        // Early settlement requires date-based rate re-tiering — use the Loans page calculator.
        if (this.f.loan?.id) {
          window.location.href = '/loans?settle=' + this.f.loan.id;
        } else {
          this.showToast('Select a loan first, then use the Loans page to perform early settlement with the correct re-tiered amount.');
        }
      },

      recalc() {
        const amt = parseFloat(this.f.amount) || 0;
        if (!amt || !this.f.loan) {
          this.breakdown = { towardsPenalty:0, towardsInterest:0, towardsPrincipal:0, instalRemaining:0, newBalance:0, pct:0 };
          return;
        }

        const penBal = parseInt(this.f.loan.penaltyBal.replace(/\D/g,'')) || 0;
        const outstanding = parseInt(this.f.loan.outstanding.replace(/\D/g,'')) || 0;

        const inst = this.f.instalment;
        let towardsPenalty = Math.min(amt, penBal);
        let remaining = amt - towardsPenalty;
        let towardsInterest = inst ? Math.min(remaining, inst.int) : 0;
        let towardsPrincipal = remaining - towardsInterest;
        let instalTotal = inst ? (inst.prin + inst.int + inst.pen) : 0;
        let instalRemaining = Math.max(0, instalTotal - amt);
        let newBalance = Math.max(0, outstanding - (towardsPrincipal + (inst ? 0 : remaining)));
        let pct = instalTotal ? Math.round((amt / instalTotal) * 100) : 0;

        this.breakdown = {
          towardsPenalty: Math.round(towardsPenalty),
          towardsInterest: Math.round(towardsInterest),
          towardsPrincipal: Math.round(towardsPrincipal),
          instalRemaining: Math.round(instalRemaining),
          newBalance: Math.max(0, Math.round(outstanding - amt)),
          pct
        };
      },

      async submitPayment() {
        if (!this.f.loan || !this.f.amount || !this.f.payType) {
          this.showToast('Please fill in all required fields.');
          return;
        }
        const token = localStorage.getItem('lms_token');
        const methodMap = { 'Cash': 'cash', 'Mobile Money': 'mobile_money', 'Bank Transfer': 'bank_transfer', 'Cheque': 'cheque' };
        const payload = {
          loan_id:        this.f.loan.id,
          amount:         parseFloat(this.f.amount),
          payment_method: methodMap[this.f.method] || 'cash',
          payment_date:   this.f.date,
          reference:      this.f.reference || null,
          notes:          this.f.notes || null,
        };
        try {
          const res = await fetch('/api/payments', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
          });
          const data = await res.json();
          if (!res.ok) {
            const msgs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Payment failed.');
            this.showToast('Error: ' + msgs);
            return;
          }
          // Capture display data before resetForm() clears the form
          this.successData = {
            rc:       data.receipt || '—',
            borrower: this.f.loan?.borrower || '—',
            loan:     this.f.loan?.num || '—',
            amount:   'K ' + Number(this.f.amount || 0).toLocaleString(),
          };
          await Promise.all([this.loadPayments(), this.loadStats()]);
          this.lastPayment = this.all[0] || null;
          this.showSuccess = true;
          this.resetForm();
          this.view = 'list';
        } catch (e) {
          this.showToast('Network error — payment not saved.');
        }
      },

      resetForm() {
        this.f = { loanQ:'', loan:null, instalment:null, payType:'', amount:'', method:'Cash', reference:'', date: new Date().toISOString().split('T')[0], notes:'' };
        this.breakdown = { towardsPenalty:0, towardsInterest:0, towardsPrincipal:0, instalRemaining:0, newBalance:0, pct:0 };
        this.loanResults = [];
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
