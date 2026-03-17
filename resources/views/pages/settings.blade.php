<!DOCTYPE html>
<html lang="en">
<head>
  <script>if(!localStorage.getItem("lms_token")){window.location.replace("/login");}</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gracimor LMS — Settings</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.min.js" defer></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,400;0,600;0,700;1,400&family=IBM+Plex+Mono:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0E0F11;--bg2:#141618;--bg3:#1A1D21;--bg4:#21252B;
  --copper:#B87333;--copper2:#D4924A;--copper3:#E8B075;
  --teal:#2DD4BF;--teal2:#14B8A6;--green:#4ADE80;
  --red:#F87171;--amber:#FBBF24;--blue:#60A5FA;--purple:#A78BFA;
  --slate:#94A3B8;--slate2:#64748B;--white:#F1F5F9;
  --line:rgba(148,163,184,.1);--line2:rgba(148,163,184,.18);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--white);min-height:100vh;overflow-x:hidden}

.shell{display:flex;min-height:100vh}
.sidebar{width:220px;min-width:220px;background:var(--bg2);border-right:1px solid var(--line);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto}
.s-logo{padding:24px 20px 16px;border-bottom:1px solid var(--line)}
.s-brand{font-family:'Spectral',serif;font-size:20px;font-weight:700;letter-spacing:.03em}
.s-sub{font-size:9px;color:var(--slate2);text-transform:uppercase;letter-spacing:.16em;margin-top:2px}
.s-sec{font-size:9px;color:var(--slate2);padding:12px 20px 4px;text-transform:uppercase;letter-spacing:.14em}
.s-item{display:flex;align-items:center;gap:9px;padding:8.5px 20px;font-size:13px;font-weight:500;color:var(--slate);cursor:pointer;transition:all .15s;border-left:2px solid transparent;text-decoration:none}
.s-item:hover{color:var(--white);background:rgba(255,255,255,.03)}
.s-item.on{color:var(--copper3);background:rgba(184,115,51,.08);border-left-color:var(--copper)}
.si{font-size:14px;width:17px;text-align:center}
.main{flex:1;display:flex;flex-direction:column;min-width:0}

/* topbar */
.topbar{background:var(--bg2);border-bottom:1px solid var(--line);padding:0 28px;height:60px;display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:100}
.tb-title{font-family:'Spectral',serif;font-size:20px;font-weight:700;letter-spacing:.02em}
.tb-title em{color:var(--copper3);font-style:italic}
.tb-tabs{display:flex;gap:2px;background:rgba(255,255,255,.04);border-radius:8px;padding:3px}
.ttab{padding:5px 13px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;color:var(--slate);background:transparent;border:none;font-family:'DM Sans',sans-serif;transition:all .15s;white-space:nowrap}
.ttab.on{background:var(--copper);color:#1a0a00}
.sep{flex:1}
.tbtn{padding:7px 14px;border-radius:7px;font-size:12.5px;font-weight:600;cursor:pointer;transition:all .2s;border:none;font-family:'DM Sans',sans-serif;display:flex;align-items:center;gap:6px}
.tbtn.copper{background:linear-gradient(135deg,var(--copper),var(--copper2));color:#1a0a00;box-shadow:0 4px 14px rgba(184,115,51,.3)}
.tbtn.copper:hover{transform:translateY(-1px)}
.tbtn.ghost{background:transparent;color:var(--slate);border:1px solid var(--line2)}
.tbtn.ghost:hover{color:var(--white);border-color:var(--slate)}
.tbtn.danger{background:rgba(248,113,113,.1);color:var(--red);border:1px solid rgba(248,113,113,.22)}
.tbtn.danger:hover{background:rgba(248,113,113,.2)}
.save-ind{display:flex;align-items:center;gap:7px;font-size:12px;color:var(--slate)}
.save-dot{width:7px;height:7px;border-radius:50%;background:var(--amber);animation:blink 2s infinite}
.save-dot.ok{background:var(--green);animation:none}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.35}}

/* content */
.content{padding:24px 28px;flex:1}

/* product cards */
.prod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:18px;margin-bottom:24px}
.prod-card{background:var(--bg2);border:1px solid var(--line);border-radius:12px;overflow:hidden;transition:all .2s;position:relative;cursor:pointer}
.prod-card:hover{border-color:var(--line2);transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.3)}
.prod-card.sel{border-color:var(--copper);box-shadow:0 0 0 1px var(--copper),0 8px 24px rgba(184,115,51,.18)}
.prod-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px}
.prod-card.vehicle::before{background:linear-gradient(90deg,var(--copper),var(--copper3))}
.prod-card.land::before{background:linear-gradient(90deg,var(--teal2),var(--teal))}
.prod-card.both::before{background:linear-gradient(90deg,var(--copper),var(--teal))}
.prod-card.dim{opacity:.5}
.pc-head{padding:18px 20px 14px;border-bottom:1px solid var(--line);display:flex;align-items:flex-start;gap:12px}
.pc-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.pc-icon.vehicle{background:rgba(184,115,51,.15)}
.pc-icon.land{background:rgba(45,212,191,.1)}
.pc-icon.both{background:rgba(167,139,250,.1)}
.pc-name{font-family:'Spectral',serif;font-size:16px;font-weight:700;line-height:1.2}
.pc-code{font-family:'IBM Plex Mono',monospace;font-size:10px;color:var(--slate2);margin-top:2px}
.bge{padding:3px 8px;border-radius:5px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em}
.bge.on{background:rgba(74,222,128,.1);color:var(--green)}
.bge.off{background:rgba(148,163,184,.08);color:var(--slate)}
.pc-body{padding:16px 20px}
.pc-params{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.pp-lbl{font-size:9.5px;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:3px}
.pp-val{font-family:'IBM Plex Mono',monospace;font-size:15px;font-weight:600;color:var(--white)}
.pp-val.cop{color:var(--copper3)}.pp-val.tea{color:var(--teal)}.pp-val.grn{color:var(--green)}.pp-val.amb{color:var(--amber)}
.pc-foot{padding:12px 20px;border-top:1px solid var(--line);display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.tag{padding:3px 8px;border-radius:5px;font-size:10.5px;font-weight:600;background:rgba(255,255,255,.05);color:var(--slate);border:1px solid var(--line)}
.new-card{background:var(--bg2);border:2px dashed var(--line2);border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;min-height:220px;cursor:pointer;transition:all .2s;color:var(--slate)}
.new-card:hover{border-color:var(--copper);color:var(--copper3);background:rgba(184,115,51,.03)}

/* edit layout */
.edit-layout{display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start}
.panel{background:var(--bg2);border:1px solid var(--line);border-radius:12px;overflow:hidden}
.ph{padding:16px 22px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:10px}
.pt{font-family:'Spectral',serif;font-size:16px;font-weight:700}
.pb{padding:22px}
.unsaved-badge{background:var(--amber);color:#1a0a00;font-size:10px;font-weight:800;padding:2px 7px;border-radius:5px;font-family:'IBM Plex Mono',monospace;margin-left:auto}

/* forms */
.fg{margin-bottom:18px}
.fl{display:block;font-size:10px;font-weight:700;color:var(--slate2);text-transform:uppercase;letter-spacing:.12em;margin-bottom:7px}
.fi,.fs,.fta{width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid var(--line2);border-radius:8px;color:var(--white);font-size:13.5px;font-family:'DM Sans',sans-serif;transition:border-color .2s}
.fi:focus,.fs:focus,.fta:focus{outline:none;border-color:var(--copper);background:rgba(184,115,51,.04)}
.fs option,.fsel option{background:var(--bg2);color:var(--white)}
.fi::placeholder{color:var(--slate2)}
.fta{resize:vertical;min-height:80px}
.gr{display:grid;gap:14px}.gr2{grid-template-columns:1fr 1fr}.gr3{grid-template-columns:1fr 1fr 1fr}
.fdiv{height:1px;background:var(--line);margin:20px 0}
.fsec{font-family:'Spectral',serif;font-size:14px;font-weight:700;color:var(--copper3);margin-bottom:14px;display:flex;align-items:center;gap:8px}
.pfx{position:relative}.pfx .px{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-family:'IBM Plex Mono',monospace;font-size:13px;color:var(--slate2);pointer-events:none}.pfx .fi{padding-left:26px}
.sfx{position:relative}.sfx .sx{position:absolute;right:12px;top:50%;transform:translateY(-50%);font-family:'IBM Plex Mono',monospace;font-size:12px;color:var(--slate2);pointer-events:none}.sfx .fi{padding-right:36px}

/* toggle */
.tgl-row{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:rgba(255,255,255,.03);border:1px solid var(--line);border-radius:8px;margin-bottom:10px}
.tgl{position:relative;width:42px;height:24px;flex-shrink:0}
.tgl input{opacity:0;width:0;height:0}
.tgl-sl{position:absolute;inset:0;background:var(--bg4);border-radius:12px;cursor:pointer;transition:background .2s}
.tgl-sl::before{content:'';position:absolute;width:18px;height:18px;left:3px;bottom:3px;background:var(--slate);border-radius:50%;transition:.2s}
input:checked+.tgl-sl{background:var(--copper)}
input:checked+.tgl-sl::before{transform:translateX(18px);background:white}
.tgl-lbl{font-size:13.5px;font-weight:600}
.tgl-sub{font-size:11.5px;color:var(--slate);margin-top:2px}

/* rate preview */
.rprev{background:var(--bg3);border:1px solid var(--line);border-radius:10px;padding:16px;margin-top:14px}
.rp-lbl{font-size:10.5px;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:12px}
.rp-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.rp-item-l{font-size:10px;color:var(--slate2);margin-bottom:3px}
.rp-item-v{font-family:'IBM Plex Mono',monospace;font-size:15px;font-weight:600}

/* ibox / wbox */
.ibox{background:rgba(96,165,250,.06);border:1px solid rgba(96,165,250,.16);border-radius:8px;padding:11px 14px;font-size:13px;color:var(--blue);display:flex;gap:9px;align-items:flex-start;margin-bottom:16px}
.wbox{background:rgba(251,191,36,.05);border:1px solid rgba(251,191,36,.16);border-radius:8px;padding:11px 14px;font-size:13px;color:var(--amber);display:flex;gap:9px;align-items:flex-start;margin-bottom:16px}

/* users */
.user-card{background:var(--bg2);border:1px solid var(--line);border-radius:10px;padding:16px 20px;display:flex;align-items:center;gap:16px;transition:all .15s;margin-bottom:10px}
.user-card:hover{border-color:var(--line2)}
.uav{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0}
.role-chip{padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em}
.role-chip.superadmin{background:rgba(248,113,113,.12);color:var(--red)}
.role-chip.ceo{background:rgba(251,191,36,.1);color:var(--amber)}
.role-chip.manager{background:rgba(184,115,51,.12);color:var(--copper3)}
.role-chip.officer{background:rgba(96,165,250,.1);color:var(--blue)}
.role-chip.accountant{background:rgba(74,222,128,.1);color:var(--green)}
.pdot{width:8px;height:8px;border-radius:50%;display:inline-block}
.uact{padding:5px 11px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;border:1px solid var(--line2);background:transparent;color:var(--slate);font-family:'DM Sans',sans-serif;transition:all .15s}
.uact:hover{color:var(--white);border-color:var(--slate)}
.uact.dng{color:var(--red);border-color:rgba(248,113,113,.22)}
.uact.dng:hover{background:rgba(248,113,113,.08)}

/* perms matrix */
.pgrid{border:1px solid var(--line);border-radius:10px;overflow:hidden;margin-top:18px}
.pgrid-h{display:grid;background:rgba(255,255,255,.03);border-bottom:1px solid var(--line)}
.pgrid-hc{padding:10px 16px;font-size:10px;font-weight:700;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;text-align:center}
.pgrid-hc:first-child{text-align:left}
.pgrid-r{display:grid;border-bottom:1px solid var(--line);transition:background .1s}
.pgrid-r:last-child{border-bottom:none}
.pgrid-r:hover{background:rgba(255,255,255,.02)}
.pgrid-c{padding:11px 16px;font-size:13px;display:flex;align-items:center;justify-content:center}
.pgrid-c:first-child{justify-content:flex-start}

/* audit */
.audit-filters{display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap}
.asearch{flex:1;min-width:200px;position:relative}
.asearch .icon{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--slate);font-size:14px}
.asearch input{width:100%;padding:9px 12px 9px 34px;background:var(--bg2);border:1px solid var(--line2);border-radius:8px;color:var(--white);font-size:13px;font-family:'DM Sans',sans-serif}
.asearch input:focus{outline:none;border-color:var(--copper)}
.fsel{padding:9px 12px;background:var(--bg2);border:1px solid var(--line2);border-radius:8px;color:var(--white);font-size:13px;font-family:'DM Sans',sans-serif}
.fsel:focus{outline:none;border-color:var(--copper)}
.atbl-wrap{background:var(--bg2);border:1px solid var(--line);border-radius:12px;overflow:hidden}
.atbl-head{padding:14px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:10px}
.atbl{width:100%;border-collapse:collapse}
.atbl thead tr{background:rgba(255,255,255,.03);border-bottom:1px solid var(--line)}
.atbl th{padding:10px 16px;font-size:9.5px;font-weight:700;color:var(--slate2);text-transform:uppercase;letter-spacing:.09em;text-align:left;white-space:nowrap}
.atbl td{padding:12px 16px;font-size:13px;border-bottom:1px solid var(--line);vertical-align:middle}
.atbl tbody tr{transition:background .1s}.atbl tbody tr:hover{background:rgba(255,255,255,.02)}
.atbl tbody tr:last-child td{border-bottom:none}
.mono{font-family:'IBM Plex Mono',monospace}
.achip{padding:3px 9px;border-radius:5px;font-size:11px;font-weight:700;white-space:nowrap}
.ac-loan{background:rgba(96,165,250,.1);color:var(--blue)}
.ac-payment{background:rgba(74,222,128,.1);color:var(--green)}
.ac-borrower{background:rgba(184,115,51,.1);color:var(--copper3)}
.ac-penalty{background:rgba(251,191,36,.1);color:var(--amber)}
.ac-user{background:rgba(167,139,250,.1);color:var(--purple)}
.ac-system{background:rgba(148,163,184,.08);color:var(--slate)}

/* templates */
.tpl-layout{display:grid;grid-template-columns:260px 1fr;gap:18px}
.tpl-item{padding:12px 14px;background:var(--bg2);border:1px solid var(--line);border-radius:9px;cursor:pointer;transition:all .15s;margin-bottom:8px}
.tpl-item:hover{border-color:var(--line2)}
.tpl-item.on{border-color:var(--copper);background:rgba(184,115,51,.06)}
.tpl-name{font-size:13px;font-weight:600}
.tpl-trigger{font-size:11px;color:var(--slate);margin-top:3px;font-family:'IBM Plex Mono',monospace}
.dot-status{width:6px;height:6px;border-radius:50%;display:inline-block}
.dot-status.on{background:var(--green)}.dot-status.off{background:var(--slate2)}
.sms-prev{background:var(--bg3);border-radius:12px;padding:18px;margin-top:14px}
.sp-lbl{font-size:10px;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:10px}
.sp-bubble{background:#1a2a3a;border-radius:14px 14px 4px 14px;padding:14px 16px;font-size:13.5px;color:var(--white);line-height:1.6;border:1px solid rgba(45,212,191,.1)}
.sp-meta{display:flex;justify-content:space-between;margin-top:8px}
.sp-chars{font-size:11px;font-family:'IBM Plex Mono',monospace}
.sp-chars.ok{color:var(--green)}.sp-chars.warn{color:var(--amber)}.sp-chars.over{color:var(--red)}
.vchip{padding:4px 10px;border-radius:6px;background:rgba(45,212,191,.07);color:var(--teal);border:1px solid rgba(45,212,191,.18);font-size:11.5px;font-family:'IBM Plex Mono',monospace;cursor:pointer;transition:all .15s;display:inline-block;margin:3px}
.vchip:hover{background:rgba(45,212,191,.14);border-color:var(--teal)}

/* system settings grid */
.sys-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}

/* modal */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.72);backdrop-filter:blur(4px);z-index:400;display:flex;align-items:center;justify-content:center;padding:24px}
.modal{background:var(--bg2);border:1px solid var(--line2);border-radius:14px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 24px 60px rgba(0,0,0,.5);animation:mIn .2s ease}
@keyframes mIn{from{opacity:0;transform:scale(.96) translateY(8px)}to{opacity:1;transform:scale(1) translateY(0)}}
.modal.sm{max-width:480px}.modal.md{max-width:580px}
.mh{padding:20px 24px 16px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:12px}
.mi{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0}
.mi.cop{background:rgba(184,115,51,.14)}.mi.grn{background:rgba(74,222,128,.1)}.mi.red{background:rgba(248,113,113,.12)}.mi.blu{background:rgba(96,165,250,.1)}
.mt{font-size:16px;font-weight:700}.ms{font-size:12px;color:var(--slate);margin-top:2px}
.mc{margin-left:auto;background:transparent;border:none;color:var(--slate);cursor:pointer;font-size:18px;padding:4px}.mc:hover{color:var(--white)}
.mb{padding:22px 24px}.mf{padding:16px 24px;border-top:1px solid var(--line);display:flex;gap:10px;justify-content:flex-end}
.mbtn{padding:9px 18px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;border:none;font-family:'DM Sans',sans-serif;transition:all .2s}
.mbtn.cop{background:linear-gradient(135deg,var(--copper),var(--copper2));color:#1a0a00}
.mbtn.gst{background:transparent;border:1px solid var(--line2);color:var(--slate)}.mbtn.gst:hover{color:var(--white);border-color:var(--slate)}
.mbtn.dng{background:rgba(248,113,113,.1);color:var(--red);border:1px solid rgba(248,113,113,.22)}.mbtn.dng:hover{background:rgba(248,113,113,.2)}
.mbtn:hover:not(.gst){transform:translateY(-1px)}

/* toast */
.tstack{position:fixed;bottom:24px;right:24px;display:flex;flex-direction:column;gap:8px;z-index:900}
.toast{padding:12px 18px;border-radius:9px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.4);animation:tIn .3s ease;min-width:230px}
@keyframes tIn{from{opacity:0;transform:translateX(14px)}to{opacity:1;transform:translateX(0)}}
.toast.ok{background:#14532d;border:1px solid #166534;color:#86efac}
.toast.info{background:#0c3349;border:1px solid var(--teal2);color:var(--teal)}
.toast.warn{background:#431407;border:1px solid var(--copper);color:var(--copper3)}
.toast.err{background:#450a0a;border:1px solid var(--red);color:#fca5a5}

.fade{animation:fup .35s ease both}
@keyframes fup{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.stg>*{animation:fup .32s ease both}
.stg>*:nth-child(1){animation-delay:.00s}.stg>*:nth-child(2){animation-delay:.04s}
.stg>*:nth-child(3){animation-delay:.08s}.stg>*:nth-child(4){animation-delay:.12s}
.stg>*:nth-child(5){animation-delay:.16s}.stg>*:nth-child(6){animation-delay:.20s}
::-webkit-scrollbar{width:5px;height:5px}::-webkit-scrollbar-track{background:var(--bg)}
::-webkit-scrollbar-thumb{background:rgba(148,163,188,.15);border-radius:3px}

/* ── Agreement form print styles ─── */
@media print {
  .shell, .topbar, .tstack, #lms-overlay, #lms-hamburger { display: none !important; }
  .agr-print-area { display: block !important; padding: 0 !important; margin: 0 !important; }
  body { background: white !important; color: #111 !important; }
}
.agr-print-area { display: none; }
.agr-paper { background:#fff; color:#111; font-family:'DM Sans',sans-serif; max-width:720px; margin:0 auto; border:1px solid #ccc; border-radius:8px; overflow:hidden; }
.agr-hdr { background:#0E0F11; color:#fff; padding:28px 36px; position:relative; }
.agr-hdr::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; background:linear-gradient(90deg,var(--copper),var(--copper3),var(--teal)); }
.agr-brand { font-family:'Spectral',serif; font-size:24px; font-weight:700; }
.agr-brand span { color:var(--copper3); }
.agr-tagline { font-size:11px; color:rgba(255,255,255,.5); margin-top:2px; }
.agr-body { padding:28px 36px; }
.agr-title { font-family:'Spectral',serif; font-size:20px; font-weight:700; text-align:center; text-transform:uppercase; letter-spacing:.08em; color:#222; border-bottom:2px solid #111; padding-bottom:12px; margin-bottom:20px; }
.agr-section { font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.14em; color:#888; margin:18px 0 8px; }
.agr-row { display:flex; gap:12px; margin-bottom:8px; font-size:13px; }
.agr-key { color:#555; min-width:160px; }
.agr-val { font-weight:600; color:#111; border-bottom:1px solid #ddd; flex:1; }
.agr-sum { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin:16px 0; }
.agr-sum-box { background:#f5f5f0; border-radius:6px; padding:12px; text-align:center; }
.agr-sum-lbl { font-size:9px; text-transform:uppercase; letter-spacing:.1em; color:#888; margin-bottom:4px; }
.agr-sum-val { font-family:'IBM Plex Mono',monospace; font-size:16px; font-weight:700; color:#111; }
.agr-terms { font-size:12px; color:#444; line-height:1.7; margin:16px 0; }
.agr-sched { width:100%; border-collapse:collapse; font-size:12px; margin:12px 0; }
.agr-sched th { background:#0E0F11; color:#fff; padding:8px 12px; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:.08em; }
.agr-sched td { padding:9px 12px; border-bottom:1px solid #eee; }
.agr-sched tr:last-child td { border-bottom:none; background:#f9f8f4; font-weight:700; }
.agr-sigs { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-top:32px; }
.agr-sig-box { border-top:2px solid #111; padding-top:8px; }
.agr-sig-lbl { font-size:10px; text-transform:uppercase; letter-spacing:.1em; color:#888; }
.agr-sig-name { font-size:13px; font-weight:700; color:#111; margin-top:4px; }
.agr-footer { background:#0E0F11; color:rgba(255,255,255,.5); padding:14px 36px; font-size:10px; display:flex; justify-content:space-between; }
</style>

  <style id="lms-responsive">
/* ══════════════════════════════════════════════════════════════════════════
   LMS Mobile Responsive  v3
   Breakpoints: 768px (tablet/phone)  |  480px (small phone)
══════════════════════════════════════════════════════════════════════════ */

/* Prevent viewport-level horizontal scrollbar */
html, body { overflow-x: hidden; max-width: 100vw; }

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

  /* ════════════════════════════════════════════════════════════════════════
     Settings page — specific responsive fixes
  ════════════════════════════════════════════════════════════════════════ */

  /* ── Topbar: let it wrap across multiple lines ──────────────────────── */
  /* The topbar has: title | tabs | sep | save-ind×2 | discard | save btn  */
  /* On mobile collapse to: [☰] title on row 1, tabs scrolling on row 2,  */
  /* save indicators + action buttons on row 3.                            */
  .tb-title { font-size: 16px !important; flex-shrink: 0 !important; }
  .sep { display: none !important; }          /* remove the flex spacer     */
  .save-ind { font-size: 11px !important; }
  .tbtn { font-size: 12px !important; padding: 6px 11px !important; }

  /* ── tb-tabs: PRIMARY overflow fix — make it scroll horizontally ─────── */
  .tb-tabs {
    order: 10;                               /* push below title row        */
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
    flex-wrap: nowrap !important;
    scrollbar-width: none;
    background: rgba(255,255,255,.04);
    border-radius: 8px;
    padding: 3px;
  }
  .tb-tabs::-webkit-scrollbar { display: none; }
  .ttab { white-space: nowrap !important; flex-shrink: 0 !important; }

  /* ── Product grid: force single column (minmax(340px,1fr) overflows) ─── */
  .prod-grid { grid-template-columns: 1fr !important; }

  /* ── Product card: let foot buttons wrap ────────────────────────────── */
  .pc-foot { flex-wrap: wrap !important; }
  .pc-foot .tbtn { flex-shrink: 0 !important; }

  /* ── Edit layout: min-width fix so grid item doesn't push viewport ───── */
  .edit-layout > * { min-width: 0 !important; }

  /* ── Early settlement select: inline min-width:280px overflows ──────── */
  [style*="min-width:280px"] { min-width: unset !important; width: 100% !important; }

  /* ── User card: wrap children onto multiple lines ───────────────────── */
  .user-card {
    flex-wrap: wrap !important;
    gap: 10px !important;
    align-items: flex-start !important;
  }
  /* The name column stays full width, other cells shrink/wrap naturally   */
  .user-card > div[style*="flex:1"] { width: 100% !important; }
  /* Override the inline min-width columns so they don't force width       */
  .user-card > div[style*="min-width:100px"],
  .user-card > div[style*="min-width:90px"],
  .user-card > div[style*="min-width:110px"],
  .user-card > div[style*="min-width:70px"] {
    min-width: unset !important;
    flex: 1 1 auto !important;
  }
  /* Action buttons row: stretch across the bottom of the card             */
  .user-card > div[style*="display:flex;gap:6px"] {
    width: 100% !important;
    flex-wrap: wrap !important;
  }
  .uact { flex: 1 1 auto !important; text-align: center !important; }

  /* ── Permissions matrix: wide grid — scroll inside the panel ────────── */
  /* The panel itself is the scroll container; the grid rows keep their    */
  /* fixed column sizes so the tick marks stay aligned.                    */
  .pgrid {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
  }
  .pgrid-h,
  .pgrid-r { min-width: 480px !important; }  /* readable with 6 columns    */

  /* ── Audit table: give the wrapper a horizontal scrollbar ───────────── */
  .atbl-wrap {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
  }
  .atbl { min-width: 660px !important; font-size: 12px !important; }
  .atbl th { font-size: 9px !important; padding: 8px 10px !important; white-space: nowrap; }
  .atbl td { font-size: 12px !important; padding: 10px 10px !important; white-space: nowrap; }

  /* ── Audit filters: already flex-wrap, just make selects full-width ─── */
  .audit-filters { flex-direction: column !important; }
  .audit-filters > * { width: 100% !important; }
  .asearch { min-width: unset !important; }

  /* ── SMS template editor: delivery channels wrap ────────────────────── */
  .sp-meta { flex-wrap: wrap !important; gap: 6px !important; }

  /* ── Modals: settings uses .overlay / .modal / .mf ─────────────────── */
  .overlay { padding: 12px !important; align-items: flex-end !important; }
  .modal { width: 100% !important; max-width: 100% !important; border-radius: 12px 12px 0 0 !important; }
  .mb { padding: 16px !important; }
  .mf { flex-wrap: wrap !important; gap: 8px !important; padding: 12px 16px !important; }
  .mf .mbtn { flex: 1 1 auto !important; text-align: center !important; }
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
<body x-data="app()" x-init="init()">
<div class="shell">

<!-- SIDEBAR -->
<nav class="sidebar">
  <div class="s-logo"><div class="s-brand">GRACIMOR</div><div class="s-sub">Loans Management</div></div>
  <div style="padding:10px 0;flex:1">
    <div class="s-sec">Core</div>
    <a class="s-item" href="/dashboard"><span class="si">⊞</span> Dashboard</a>
    <a class="s-item" href="/borrowers"><span class="si">👥</span> Borrowers</a>
    <a class="s-item" href="/loans"><span class="si">📋</span> Loans</a>
    <a class="s-item" href="/payments"><span class="si">💳</span> Payments</a>
    <a class="s-item" href="/calendar"><span class="si">📅</span> Calendar</a>
    <div class="s-sec">Collections</div>
    <a class="s-item" href="/overdue"><span class="si">⚠️</span> Overdue &amp; Penalties</a>
    <div class="s-sec">Analytics</div>
    <a class="s-item" href="/reports"><span class="si">📊</span> Reports</a>
    <div class="s-sec">System</div>
    <a class="s-item on" href="/settings"><span class="si">⚙️</span> Settings</a>
  </div>
</nav>

<!-- MAIN -->
<main class="main">
  <div class="topbar">
    <div class="tb-title">System <em>Settings</em></div>
    <div class="tb-tabs">
      <button class="ttab" :class="tab==='products'  && 'on'" @click="tab='products'">Loan Products</button>
      <button class="ttab" :class="tab==='system'    && 'on'" @click="tab='system'">System</button>
      <button class="ttab" :class="tab==='users'     && 'on'" @click="tab='users'">Users</button>
      <button class="ttab" :class="tab==='audit'     && 'on'" @click="tab='audit'">Audit Log</button>
      <button class="ttab" :class="tab==='templates' && 'on'" @click="tab='templates'">SMS Templates</button>
      <button class="ttab" :class="tab==='agreement'  && 'on'" @click="tab='agreement'">Loan Agreement</button>
      <button class="ttab" :class="tab==='import'     && 'on'" @click="tab='import'">Import / Export</button>
    </div>
    <div class="sep"></div>
    <div class="save-ind" x-show="unsaved"><div class="save-dot"></div><span>Unsaved changes</span></div>
    <div class="save-ind" x-show="!unsaved"><div class="save-dot ok"></div><span>All saved</span></div>
    <button class="tbtn ghost" @click="unsaved=false;toast('info','↩️','Changes discarded')">Discard</button>
    <button class="tbtn copper" @click="saveAll()">💾 Save Changes</button>
  </div>

  <div class="content">

    <!-- ██ LOAN PRODUCTS ██ -->
    <template x-if="tab==='products'">
      <div class="fade">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
          <div>
            <div style="font-family:'Spectral',serif;font-size:18px;font-weight:700">Loan Products</div>
            <div style="font-size:13px;color:var(--slate);margin-top:2px">Configure interest rates, fees, penalties and limits for each product</div>
          </div>
          <button class="tbtn copper" @click="openNew()">+ New Product</button>
        </div>

        <!-- Product cards -->
        <div class="prod-grid stg">
          <template x-for="p in products" :key="p.id">
            <div class="prod-card" :class="[p.collType, !p.active&&'dim', selProd?.id===p.id&&'sel']" @click="selProd=JSON.parse(JSON.stringify(p))">
              <div class="pc-head">
                <div class="pc-icon" :class="p.collType" x-text="p.collType==='vehicle'?'🚗':p.collType==='land'?'🏞️':'🔑'"></div>
                <div style="flex:1">
                  <div class="pc-name" x-text="p.name"></div>
                  <div class="pc-code" x-text="p.code"></div>
                </div>
                <span class="bge" :class="p.active?'on':'off'" x-text="p.active?'Active':'Inactive'"></span>
              </div>
              <div class="pc-body">
                <div class="pc-params">
                  <div><div class="pp-lbl">Rate Tiers</div><div class="pp-val cop" style="font-size:11px">10·18·28·38% flat</div></div>
                  <div><div class="pp-lbl">Model</div><div class="pp-val" style="font-size:12px">Flat Rate</div></div>
                  <div><div class="pp-lbl">Max Term</div><div class="pp-val tea" x-text="p.maxTerm+' months'"></div></div>
                  <div><div class="pp-lbl">Max LTV</div><div class="pp-val grn" x-text="p.maxLtv+'%'"></div></div>
                  <div><div class="pp-lbl">Processing Fee</div><div class="pp-val" style="font-size:12px" x-text="p.feeFlat?'K '+p.feeFlat.toLocaleString()+' flat':p.feePct+'% of loan'"></div></div>
                  <div><div class="pp-lbl">Penalty</div><div class="pp-val amb">5% of instalment</div></div>
                </div>
              </div>
              <div class="pc-foot">
                <span class="tag" x-text="p.collType==='vehicle'?'🚗 Vehicle':p.collType==='land'?'🏞️ Land':'🔑 Both'"></span>
                <span class="tag" x-text="'Grace: '+p.grace+'d'"></span>
                <span class="tag" x-show="p.earlySettle">✓ Early Settlement</span>
                <button class="tbtn ghost" style="margin-left:auto;padding:5px 11px;font-size:12px" @click.stop="selProd=JSON.parse(JSON.stringify(p))">Edit →</button>
              </div>
            </div>
          </template>
          <div class="new-card" @click="openNew()">
            <div style="font-size:30px">＋</div>
            <div style="font-size:14px;font-weight:600">Create New Product</div>
            <div style="font-size:12px;color:var(--slate2);text-align:center;max-width:180px">Configure a new loan product with custom rates and terms</div>
          </div>
        </div>

        <!-- Edit panel -->
        <template x-if="selProd">
          <div class="edit-layout fade" style="margin-top:24px">
            <!-- LEFT: form -->
            <div class="panel">
              <div class="ph">
                <span style="font-size:18px" x-text="selProd.collType==='vehicle'?'🚗':selProd.collType==='land'?'🏞️':'🔑'"></span>
                <div class="pt" x-text="selProd.name||'New Product'"></div>
                <span class="unsaved-badge" x-show="unsaved">UNSAVED</span>
              </div>
              <div class="pb">

                <div class="fsec">⚙️ Basic Information</div>
                <div class="gr gr2">
                  <div class="fg"><label class="fl">Product Name *</label><input class="fi" x-model="selProd.name" @input="unsaved=true" placeholder="e.g. Vehicle-Backed Loan"></div>
                  <div class="fg"><label class="fl">Product Code *</label><input class="fi mono" x-model="selProd.code" @input="unsaved=true" placeholder="e.g. VBL-001"></div>
                </div>
                <div class="gr gr2">
                  <div class="fg">
                    <label class="fl">Collateral Type *</label>
                    <select class="fs" x-model="selProd.collType" @change="unsaved=true">
                      <option value="vehicle">Vehicle</option>
                      <option value="land">Land</option>
                    </select>
                  </div>
                  <div class="fg">
                    <label class="fl">Status</label>
                    <select class="fs" x-model="selProd.active" @change="unsaved=true">
                      <option :value="true">Active — available for new loans</option>
                      <option :value="false">Inactive — hidden from applications</option>
                    </select>
                  </div>
                </div>
                <div class="fg"><label class="fl">Description</label><textarea class="fta" x-model="selProd.desc" @input="unsaved=true" placeholder="Brief description for loan officers…"></textarea></div>

                <div class="fdiv"></div>
                <div class="fsec">📈 Rate Tiers — Fixed by Duration</div>
                <div class="ibox"><span>ℹ️</span><span>Rates are fixed system-wide by loan duration only. Flat interest model: interest = (rate ÷ 100) × principal. Applicable to all products.</span></div>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:16px">
                  <template x-for="t in rateTiersList" :key="t.months">
                    <div style="background:var(--bg3);border:1px solid var(--line);border-radius:9px;padding:14px;text-align:center">
                      <div style="font-size:9px;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px" x-text="t.months+' Month'+(t.months>1?'s':'')"></div>
                      <div class="mono" style="font-size:26px;font-weight:700;color:var(--copper3)" x-text="t.rate+'%'"></div>
                      <div style="font-size:10px;color:var(--slate);margin-top:4px">flat on principal</div>
                    </div>
                  </template>
                </div>

                <!-- Live preview (flat rate, selectable term) -->
                <div class="rprev">
                  <div class="rp-lbl">
                    Live Preview — K 50,000 ·
                    <select class="fs" x-model.number="selProd.previewTerm" style="display:inline-block;width:auto;padding:2px 8px;font-size:11px;margin:0 4px">
                      <option :value="1">1 month</option>
                      <option :value="2">2 months</option>
                      <option :value="3">3 months</option>
                      <option :value="4">4 months</option>
                    </select>
                  </div>
                  <div class="rp-grid">
                    <div><div class="rp-item-l">Rate Applied</div><div class="rp-item-v" style="color:var(--amber)" x-text="(rateTiers[selProd.previewTerm||4]||38)+'% flat'"></div></div>
                    <div><div class="rp-item-l">Interest Amount</div><div class="rp-item-v" style="color:var(--teal)" x-text="'K '+calcPreview(selProd.previewTerm||4).interest"></div></div>
                    <div><div class="rp-item-l">Total Repayable</div><div class="rp-item-v" style="color:var(--white)" x-text="'K '+calcPreview(selProd.previewTerm||4).total"></div></div>
                    <div><div class="rp-item-l">Monthly Instalment</div><div class="rp-item-v" style="color:var(--copper3)" x-text="'K '+calcPreview(selProd.previewTerm||4).monthly"></div></div>
                  </div>
                </div>

                <div class="fdiv"></div>
                <div class="fsec">📐 Loan Limits</div>
                <div class="gr gr3">
                  <div style="background:var(--bg3);border:1px solid var(--line);border-radius:9px;padding:14px">
                    <div style="font-size:9.5px;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px">Available Durations</div>
                    <div class="mono" style="font-size:15px;font-weight:700;color:var(--green)">1 · 2 · 3 · 4 months</div>
                    <div style="font-size:11px;color:var(--slate);margin-top:4px">All 4 durations available</div>
                  </div>
                  <div class="fg"><label class="fl">Max LTV % *</label><div class="sfx"><input class="fi" type="number" step="1" x-model="selProd.maxLtv" @input="unsaved=true"><span class="sx">%</span></div></div>
                <div class="gr gr2">
                  <div class="fg"><label class="fl">Min Loan Amount</label><div class="pfx"><span class="px">K</span><input class="fi" type="number" x-model="selProd.minAmount" @input="unsaved=true"></div></div>
                  <div class="fg"><label class="fl">Max Loan Amount</label><div class="pfx"><span class="px">K</span><input class="fi" type="number" x-model="selProd.maxAmount" @input="unsaved=true"></div></div>
                </div>

                <div class="fdiv"></div>
                <div class="fsec">💰 Fees</div>
                <div class="gr gr2">
                  <div class="fg"><label class="fl">Processing Fee — Flat (K)</label><div class="pfx"><span class="px">K</span><input class="fi" type="number" x-model="selProd.feeFlat" @input="unsaved=true" placeholder="0"></div></div>
                  <div class="fg"><label class="fl">Processing Fee — % of Principal</label><div class="sfx"><input class="fi" type="number" step="0.1" x-model="selProd.feePct" @input="unsaved=true" placeholder="0"><span class="sx">%</span></div></div>
                </div>
                <div class="ibox"><span>ℹ️</span><span>If both flat and % are set, the flat amount takes priority. Set both to 0 for no processing fee.</span></div>

                <div class="fdiv"></div>
                <div class="fsec">⚡ Penalties</div>
                <div class="ibox"><span>ℹ️</span><span>A penalty of <strong>5% of the monthly instalment</strong> is automatically added when a client misses a payment. Applied after the grace period expires.</span></div>
                <div class="gr gr2">
                  <div style="background:var(--bg3);border:1px solid var(--line);border-radius:9px;padding:14px">
                    <div style="font-size:9.5px;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px">Penalty Rate (Fixed)</div>
                    <div class="mono" style="font-size:26px;font-weight:700;color:var(--red)">5%</div>
                    <div style="font-size:11px;color:var(--slate);margin-top:4px">of missed monthly instalment</div>
                  </div>
                  <div class="fg"><label class="fl">Grace Period (days)</label><input class="fi" type="number" min="0" max="30" x-model="selProd.grace" @input="unsaved=true"><div style="font-size:11px;color:var(--slate);margin-top:5px">Days before penalty is applied after missed due date</div></div>
                </div>

                <div class="fdiv"></div>
                <div class="fsec">🔧 Features</div>
                <div class="tgl-row">
                  <div><div class="tgl-lbl">Allow Early Settlement</div><div class="tgl-sub">Borrowers may pay off full balance before maturity</div></div>
                  <label class="tgl"><input type="checkbox" x-model="selProd.earlySettle" @change="unsaved=true"><span class="tgl-sl"></span></label>
                </div>
                <div class="ibox" style="margin-top:4px" x-show="selProd.earlySettle"><span>ℹ️</span><span><strong>Auto-recalculation:</strong> When a client settles early in N months, the system automatically applies the rate for N months (e.g. 3-month loan settled in month 1 → recalculated at 10% instead of 28%). The difference is refunded or credited.</span></div>
                <div class="tgl-row">
                  <div><div class="tgl-lbl">Require Guarantor</div><div class="tgl-sub">Mandate at least one guarantor on every application</div></div>
                  <label class="tgl"><input type="checkbox" x-model="selProd.requireGuarantor" @change="unsaved=true"><span class="tgl-sl"></span></label>
                </div>
              </div>
            </div>

            <!-- RIGHT: summary + actions -->
            <div style="display:flex;flex-direction:column;gap:16px">
              <div class="panel">
                <div class="ph"><div class="pt">Product Summary</div></div>
                <div class="pb">
                  <div style="display:flex;flex-direction:column;gap:0">
                    <template x-for="row in prodSummary(selProd)" :key="row[0]">
                      <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid var(--line)">
                        <span style="font-size:12px;color:var(--slate)" x-text="row[0]"></span>
                        <span class="mono" style="font-size:13px;font-weight:600;color:var(--white)" x-text="row[1]"></span>
                      </div>
                    </template>
                  </div>
                </div>
              </div>

              <div class="panel">
                <div class="ph"><div class="pt">Usage Statistics</div></div>
                <div class="pb">
                  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div style="background:var(--bg3);border-radius:8px;padding:14px;text-align:center"><div style="font-size:9.5px;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px">Active Loans</div><div class="mono" style="font-size:26px;font-weight:700;color:var(--teal)" x-text="selProd.stats?.active||0"></div></div>
                    <div style="background:var(--bg3);border-radius:8px;padding:14px;text-align:center"><div style="font-size:9.5px;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px">Disbursed</div><div class="mono" style="font-size:15px;font-weight:700;color:var(--copper3)" x-text="'K '+(selProd.stats?.disbursed||0).toLocaleString()"></div></div>
                    <div style="background:var(--bg3);border-radius:8px;padding:14px;text-align:center"><div style="font-size:9.5px;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px">Outstanding</div><div class="mono" style="font-size:15px;font-weight:700;color:var(--amber)" x-text="'K '+(selProd.stats?.outstanding||0).toLocaleString()"></div></div>
                    <div style="background:var(--bg3);border-radius:8px;padding:14px;text-align:center"><div style="font-size:9.5px;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px">PAR 30</div><div class="mono" style="font-size:26px;font-weight:700;color:var(--amber)" x-text="(selProd.stats?.par||0)+'%'"></div></div>
                  </div>
                </div>
              </div>

              <div class="panel">
                <div class="ph"><div class="pt">Actions</div></div>
                <div class="pb" style="display:flex;flex-direction:column;gap:10px">
                  <button class="tbtn copper" style="width:100%;justify-content:center;padding:11px" @click="saveProduct()">💾 Save Product</button>
                  <button class="tbtn ghost" style="width:100%;justify-content:center" @click="duplicateProd()">📋 Duplicate Product</button>
                  <button class="tbtn ghost" style="width:100%;justify-content:center" @click="selProd.active=!selProd.active;unsaved=true" x-text="selProd.active?'🚫 Deactivate':'✓ Activate'"></button>
                  <div style="height:1px;background:var(--line);margin:2px 0"></div>
                  <button class="tbtn danger" style="width:100%;justify-content:center" @click="showDelModal=true">🗑 Delete Product</button>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </template>

    <!-- ██ SYSTEM ██ -->
    <template x-if="tab==='system'">
      <div class="fade">
        <div style="margin-bottom:20px">
          <div style="font-family:'Spectral',serif;font-size:18px;font-weight:700">System Configuration</div>
          <div style="font-size:13px;color:var(--slate);margin-top:2px">Company details, automated jobs, SMS provider and formatting</div>
        </div>
        <!-- Rate Tiers Configuration -->
        <div class="panel" style="margin-bottom:20px">
          <div class="ph"><span style="font-size:16px">📊</span><div class="pt">Loan Rate Tiers</div><span style="margin-left:auto;font-size:12px;color:var(--slate)">System-wide · fixed by duration only</span></div>
          <div class="pb">
            <div class="ibox"><span>ℹ️</span><span>These rates are the only rates used in the system. The rate applied to any loan is determined solely by the loan duration chosen at application time. No other factors apply.</span></div>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px">
              <template x-for="t in rateTiersList" :key="t.months">
                <div style="background:var(--bg3);border:1px solid var(--line);border-radius:10px;padding:18px;text-align:center;position:relative">
                  <div style="font-size:9.5px;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px" x-text="t.months+' Month'+(t.months>1?'s':'')"></div>
                  <div class="mono" style="font-size:32px;font-weight:700;color:var(--copper3)" x-text="t.rate+'%'"></div>
                  <div style="font-size:11px;color:var(--slate);margin-top:6px">flat on principal</div>
                  <div style="font-size:11px;color:var(--slate2);margin-top:4px" x-text="'e.g. K50k → K'+(50000*(1+t.rate/100)).toLocaleString()+' total'"></div>
                </div>
              </template>
            </div>
            <div class="ibox" style="margin-bottom:0"><span>⚡</span><span><strong>Penalty:</strong> 5% of the monthly instalment added automatically on each missed payment, after the product's grace period expires. &nbsp;|&nbsp; <strong>Early Settlement:</strong> Auto-recalculates using the tier rate for the actual months the loan was active.</span></div>
          </div>
        </div>

        <div class="sys-grid">
          <!-- Company -->
          <div class="panel">
            <div class="ph"><span style="font-size:16px">🏢</span><div class="pt">Company Information</div></div>
            <div class="pb">
              <div class="fg"><label class="fl">Company Name</label><input class="fi" value="Gracimor Microfinance Ltd" @input="unsaved=true"></div>
              <div class="gr gr2">
                <div class="fg"><label class="fl">Phone</label><input class="fi" value="+260 977 000 001" @input="unsaved=true"></div>
                <div class="fg"><label class="fl">Email</label><input class="fi" value="info@gracimor.co.zm" @input="unsaved=true"></div>
              </div>
              <div class="fg"><label class="fl">Physical Address</label><textarea class="fta" style="min-height:60px" @input="unsaved=true">Plot 4821, Cairo Road, Lusaka, Zambia</textarea></div>
              <div class="gr gr2">
                <div class="fg"><label class="fl">BoZ Licence No.</label><input class="fi mono" value="NBZ/2024/441" @input="unsaved=true"></div>
                <div class="fg"><label class="fl">TPIN</label><input class="fi mono" value="1004822100" @input="unsaved=true"></div>
              </div>
              <div class="fg"><label class="fl">Report Footer</label><textarea class="fta" style="min-height:50px" @input="unsaved=true">Gracimor Loans · Lusaka, Zambia · Regulated by the Bank of Zambia</textarea></div>
            </div>
          </div>

          <!-- Automated Jobs -->
          <div class="panel">
            <div class="ph"><span style="font-size:16px">⚡</span><div class="pt">Automated Jobs Schedule</div></div>
            <div class="pb">
              <div class="wbox"><span>⚠️</span><span>Times are in Zambia Standard Time (UTC+2). Changes apply after next deploy.</span></div>
              <template x-for="job in jobs" :key="job.key">
                <div style="border:1px solid var(--line);border-radius:9px;padding:14px 16px;margin-bottom:10px">
                  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <div style="flex:1">
                      <div style="font-size:13.5px;font-weight:700" x-text="job.name"></div>
                      <div style="font-size:11.5px;color:var(--slate);margin-top:2px" x-text="job.desc"></div>
                    </div>
                    <label class="tgl" style="flex-shrink:0;margin-left:12px"><input type="checkbox" x-model="job.enabled" @change="unsaved=true"><span class="tgl-sl"></span></label>
                  </div>
                  <div style="display:flex;align-items:center;gap:10px" x-show="job.enabled">
                    <div>
                      <div class="fl" style="margin-bottom:5px">Run at</div>
                      <input class="fi mono" type="time" x-model="job.time" @input="unsaved=true" style="width:120px">
                    </div>
                    <div style="flex:1">
                      <div class="fl" style="margin-bottom:5px">Last Run</div>
                      <div class="mono" style="font-size:11.5px;color:var(--slate);padding:9px 12px;background:var(--bg3);border-radius:7px;border:1px solid var(--line)" x-text="job.lastRun"></div>
                    </div>
                    <div>
                      <div class="fl" style="margin-bottom:5px">Status</div>
                      <span :style="`color:${job.lastStatus==='OK'?'var(--green)':'var(--red)'}`" class="mono" style="font-size:12px;font-weight:700" x-text="job.lastStatus"></span>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>

          <!-- SMS Provider -->
          <div class="panel">
            <div class="ph"><span style="font-size:16px">📱</span><div class="pt">SMS &amp; WhatsApp Provider</div></div>
            <div class="pb">
              <div class="fg">
                <label class="fl">Provider</label>
                <select class="fs" x-model="smsProvider" @change="unsaved=true">
                  <option value="africastalking">Africa's Talking</option>
                  <option value="twilio">Twilio</option>
                  <option value="vonage">Vonage (Nexmo)</option>
                  <option value="custom">Custom HTTP API</option>
                  <option value="disabled">Disabled — log only, no sending</option>
                </select>
              </div>
              <template x-if="smsProvider!=='disabled'">
                <div>
                  <div class="gr gr2">
                    <div class="fg"><label class="fl">API Key / Username</label><input class="fi mono" value="gracimor_prod" @input="unsaved=true"></div>
                    <div class="fg"><label class="fl">API Secret / Token</label><input class="fi mono" type="password" value="sk_live_xxxxxxxx" @input="unsaved=true"></div>
                  </div>
                  <div class="gr gr2">
                    <div class="fg"><label class="fl">Sender ID</label><input class="fi mono" value="GRACIMOR" @input="unsaved=true"></div>
                    <div class="fg"><label class="fl">Default Country Code</label><input class="fi mono" value="+260" @input="unsaved=true"></div>
                  </div>
                  <button class="tbtn ghost" style="width:100%;justify-content:center" @click="toast('info','📱','Test SMS sent to +260 977 000 001')">📱 Send Test SMS</button>
                </div>
              </template>
            </div>
          </div>

          <!-- Currency & Format -->
          <div class="panel">
            <div class="ph"><span style="font-size:16px">💱</span><div class="pt">Currency &amp; Formatting</div></div>
            <div class="pb">
              <div class="gr gr2">
                <div class="fg"><label class="fl">Currency Code</label><input class="fi mono" value="ZMW" @input="unsaved=true"></div>
                <div class="fg"><label class="fl">Currency Symbol</label><input class="fi mono" value="K" @input="unsaved=true"></div>
              </div>
              <div class="gr gr2">
                <div class="fg">
                  <label class="fl">Date Format</label>
                  <select class="fs" @change="unsaved=true">
                    <option selected x-text="dateExampleLabel"></option>
                    <option>DD/MM/YYYY</option>
                    <option>YYYY-MM-DD (ISO)</option>
                  </select>
                </div>
                <div class="fg">
                  <label class="fl">Decimal Separator</label>
                  <select class="fs" @change="unsaved=true"><option>. (period)</option><option>, (comma)</option></select>
                </div>
              </div>
              <div class="fg">
                <label class="fl">Thousands Separator</label>
                <select class="fs" @change="unsaved=true"><option>, (comma)</option><option>. (period)</option><option>space</option></select>
              </div>
              <div style="background:var(--bg3);border-radius:8px;padding:12px 14px;font-size:13px;color:var(--slate)">
                Preview: <span class="mono" style="color:var(--copper3)">K 2,840,200.00</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ██ USERS ██ -->
    <template x-if="tab==='users'">
      <div class="fade">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
          <div>
            <div style="font-family:'Spectral',serif;font-size:18px;font-weight:700">User Accounts</div>
            <div style="font-size:13px;color:var(--slate);margin-top:2px">Manage staff accounts and role-based access permissions</div>
          </div>
          <button class="tbtn copper" @click="showNewUser=true">+ Add User</button>
        </div>

        <div class="stg">
          <template x-for="u in users" :key="u.id">
            <div class="user-card">
              <div class="uav" :style="`background:linear-gradient(135deg,${u.c1},${u.c2})`" x-text="u.ini"></div>
              <div style="flex:1">
                <div style="font-size:14px;font-weight:700" x-text="u.name"></div>
                <div class="mono" style="font-size:11.5px;color:var(--slate);margin-top:2px" x-text="u.email"></div>
              </div>
              <div style="min-width:100px;text-align:center"><span class="role-chip" :class="u.role" x-text="u.role"></span></div>
              <div style="font-size:12px;color:var(--slate);min-width:90px;text-align:center">
                <div x-text="u.loans+' loans'"></div>
                <div style="color:var(--slate2);font-size:11px;margin-top:2px" x-text="u.lastLogin"></div>
              </div>
              <div style="display:flex;gap:4px;align-items:center;min-width:110px">
                <span class="pdot" :style="`background:${u.perms.approve?'var(--green)':'var(--bg4)'}`" title="Approve"></span>
                <span class="pdot" :style="`background:${u.perms.disburse?'var(--copper3)':'var(--bg4)'}`" title="Disburse"></span>
                <span class="pdot" :style="`background:${u.perms.waive?'var(--blue)':'var(--bg4)'}`" title="Waive Penalties"></span>
                <span class="pdot" :style="`background:${u.perms.report?'var(--purple)':'var(--bg4)'}`" title="Reports"></span>
                <span style="font-size:10.5px;color:var(--slate2);margin-left:4px">perms</span>
              </div>
              <div style="min-width:70px"><span :style="`color:${u.active?'var(--green)':'var(--slate2)'}`" style="font-size:12px;font-weight:700" x-text="u.active?'● Active':'● Inactive'"></span></div>
              <div style="display:flex;gap:6px">
                <button class="uact" @click="toast('info','✏️','Edit panel opening for '+u.name)">Edit</button>
                <button class="uact" @click="toast('info','🔑','Password reset sent to '+u.email)">Reset PW</button>
                <button class="uact dng" x-show="u.role!=='superadmin'" @click="toast('warn','🚫',u.name+' deactivated')">Deactivate</button>
              </div>
            </div>
          </template>
        </div>

        <!-- Permissions matrix -->
        <div class="panel" style="margin-top:24px">
          <div class="ph"><span style="font-size:16px">🔐</span><div class="pt">Role Permissions Matrix</div></div>
          <div class="pb" style="padding:0">
            <div class="pgrid">
              <div class="pgrid-h" style="grid-template-columns:210px repeat(5,1fr)">
                <div class="pgrid-hc" style="text-align:left;padding-left:16px">Permission</div>
                <div class="pgrid-hc">Super Admin</div>
                <div class="pgrid-hc">CEO</div>
                <div class="pgrid-hc">Manager</div>
                <div class="pgrid-hc">Officer</div>
                <div class="pgrid-hc">Accountant</div>
              </div>
              <template x-for="perm in perms" :key="perm.label">
                <div class="pgrid-r" style="grid-template-columns:210px repeat(5,1fr)">
                  <div class="pgrid-c" style="font-size:13px;font-weight:500;justify-content:flex-start" x-text="perm.label"></div>
                  <template x-for="v in perm.roles" :key="v+''+Math.random()">
                    <div class="pgrid-c"><span x-text="v?'✓':'—'" :style="`color:${v?'var(--green)':'rgba(148,163,184,.2)'};font-size:${v?'16px':'14px'};font-weight:700`"></span></div>
                  </template>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ██ AUDIT LOG ██ -->
    <template x-if="tab==='audit'">
      <div class="fade">
        <div class="audit-filters">
          <div class="asearch">
            <span class="icon">🔍</span>
            <input type="text" placeholder="Search user, action, entity…" x-model="auditSearch">
          </div>
          <select class="fsel" x-model="auditType">
            <option value="">All Actions</option>
            <option value="loan">Loan</option>
            <option value="payment">Payment</option>
            <option value="borrower">Borrower</option>
            <option value="penalty">Penalty</option>
            <option value="user">User</option>
            <option value="system">System</option>
          </select>
          <select class="fsel" x-model="auditUser">
            <option value="">All Users</option>
            <option>F. Mwala</option>
            <option>C. Banda</option>
            <option>N. Tembo</option>
            <option>K. Simwanza</option>
            <option>System</option>
          </select>
          <input type="date" class="fsel" :value="auditFrom">
          <input type="date" class="fsel" :value="auditTo">
        </div>

        <div class="atbl-wrap">
          <div class="atbl-head">
            <div style="font-family:'Spectral',serif;font-size:15px;font-weight:700">Audit Log</div>
            <div style="font-size:12px;color:var(--slate)" x-text="filteredAudit.length+' entries'"></div>
            <div style="margin-left:auto"><button class="tbtn ghost" style="padding:6px 12px;font-size:12px" @click="toast('info','📋','Audit log CSV exported')">📋 Export CSV</button></div>
          </div>
          <table class="atbl">
            <thead><tr>
              <th>Timestamp</th><th>User</th><th>Action</th><th>Entity</th><th>Description</th><th>IP Address</th>
            </tr></thead>
            <tbody>
              <template x-for="e in filteredAudit" :key="e.id">
                <tr>
                  <td class="mono" style="font-size:11.5px;color:var(--slate);white-space:nowrap" x-text="e.ts"></td>
                  <td>
                    <div style="display:flex;align-items:center;gap:8px">
                      <div style="width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0" :style="`background:linear-gradient(135deg,${e.c1},${e.c2})`" x-text="e.ini"></div>
                      <div>
                        <div style="font-size:13px;font-weight:600" x-text="e.user"></div>
                        <div style="font-size:11px;color:var(--slate)" x-text="e.role"></div>
                      </div>
                    </div>
                  </td>
                  <td><span class="achip" :class="'ac-'+e.type" x-text="e.action"></span></td>
                  <td class="mono" style="font-size:12px;color:var(--teal)" x-text="e.entity"></td>
                  <td style="font-size:12.5px;color:var(--slate);max-width:300px" x-text="e.desc"></td>
                  <td class="mono" style="font-size:11.5px;color:var(--slate2)" x-text="e.ip"></td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- ██ SMS TEMPLATES ██ -->
    <template x-if="tab==='templates'">
      <div class="fade">
        <div style="margin-bottom:20px">
          <div style="font-family:'Spectral',serif;font-size:18px;font-weight:700">SMS &amp; Reminder Templates</div>
          <div style="font-size:13px;color:var(--slate);margin-top:2px">Edit the automated messages sent to borrowers at each trigger point</div>
        </div>
        <div class="tpl-layout">
          <!-- Template list -->
          <div>
            <div style="font-size:9.5px;color:var(--slate2);text-transform:uppercase;letter-spacing:.12em;margin-bottom:10px">8 Templates</div>
            <template x-for="t in templates" :key="t.key">
              <div class="tpl-item" :class="selTpl?.key===t.key&&'on'" @click="selTpl=JSON.parse(JSON.stringify(t))">
                <div class="tpl-name" x-text="t.name"></div>
                <div class="tpl-trigger" x-text="t.trigger"></div>
                <div style="display:flex;align-items:center;gap:5px;margin-top:5px;font-size:11px">
                  <span class="dot-status" :class="t.enabled?'on':'off'"></span>
                  <span style="color:var(--slate)" x-text="t.enabled?'Enabled':'Disabled'"></span>
                  <span style="color:var(--slate2);margin-left:6px" x-text="t.channels.join(' · ')"></span>
                </div>
              </div>
            </template>
          </div>

          <!-- Editor -->
          <template x-if="selTpl">
            <div class="panel">
              <div class="ph">
                <div class="pt" x-text="selTpl.name"></div>
                <label class="tgl" style="margin-left:auto"><input type="checkbox" x-model="selTpl.enabled" @change="unsaved=true"><span class="tgl-sl"></span></label>
              </div>
              <div class="pb">
                <div class="ibox"><span>ℹ️</span><span>Click a variable below to insert it into the message at the cursor position.</span></div>

                <div class="fg">
                  <label class="fl">Trigger Point</label>
                  <input class="fi mono" readonly :value="selTpl.trigger" style="cursor:not-allowed;opacity:.65">
                </div>

                <div class="fg">
                  <label class="fl">Delivery Channels</label>
                  <div style="display:flex;gap:16px">
                    <template x-for="ch in ['SMS','WhatsApp','Email']" :key="ch">
                      <label style="cursor:pointer;display:flex;align-items:center;gap:7px">
                        <input type="checkbox" :checked="selTpl.channels.includes(ch)" style="accent-color:var(--copper)" @change="toggleCh(ch)">
                        <span style="font-size:13.5px;color:var(--slate)" x-text="ch"></span>
                      </label>
                    </template>
                  </div>
                </div>

                <div class="fg">
                  <label class="fl">Message Body *</label>
                  <textarea class="fta" style="min-height:100px;font-size:13.5px" x-model="selTpl.body" @input="unsaved=true" x-ref="tarea"></textarea>
                </div>

                <div style="font-size:9.5px;color:var(--slate2);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px">Insert Variable</div>
                <div>
                  <template x-for="v in tplVars" :key="v">
                    <span class="vchip" @click="insertVar(v)" x-text="'{'+v+'}'"></span>
                  </template>
                </div>

                <!-- Live preview -->
                <div class="sms-prev">
                  <div class="sp-lbl">Live Preview</div>
                  <div class="sp-bubble" x-text="previewBody(selTpl.body)"></div>
                  <div class="sp-meta">
                    <span class="sp-chars" :class="charCls(selTpl.body)" x-text="selTpl.body.length+' chars'"></span>
                    <span style="font-size:11px;color:var(--slate)" x-text="Math.ceil(selTpl.body.length/160)+' SMS segment(s)'"></span>
                  </div>
                </div>

                <div class="wbox" style="margin-top:12px" x-show="selTpl.body.length>160">
                  <span>⚠️</span><span>Message exceeds 160 characters — will use <strong x-text="Math.ceil(selTpl.body.length/160)"></strong> SMS segments, increasing send cost.</span>
                </div>

                <div style="display:flex;gap:10px;margin-top:18px">
                  <button class="tbtn copper" style="flex:1;justify-content:center" @click="saveTpl()">💾 Save Template</button>
                  <button class="tbtn ghost" @click="toast('info','📱','Test SMS sent to +260 977 000 001')">📱 Test Send</button>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </template>

    <!-- ██ LOAN AGREEMENT ██ -->
    <template x-if="tab==='agreement'">
      <div class="fade">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
          <div>
            <div style="font-family:'Spectral',serif;font-size:18px;font-weight:700">Loan Agreement Forms</div>
            <div style="font-size:13px;color:var(--slate);margin-top:2px">Generate, preview and save a signed loan agreement for any borrower</div>
          </div>
          <div style="display:flex;gap:8px">
            <button class="tbtn ghost" @click="agrReset()">↺ Clear Form</button>
            <button class="tbtn copper" @click="agrPrint()">🖨 Save as PDF</button>
          </div>
        </div>

        <div class="edit-layout">
          <!-- LEFT: Input form -->
          <div class="panel">
            <div class="ph"><span style="font-size:16px">📝</span><div class="pt">Agreement Details</div></div>
            <div class="pb">

              <div class="fsec">👤 Borrower Information</div>
              <div class="gr gr2">
                <div class="fg"><label class="fl">Full Name *</label><input class="fi" x-model="agr.borrowerName" placeholder="e.g. Grace Nkonde"></div>
                <div class="fg"><label class="fl">NRC Number *</label><input class="fi mono" x-model="agr.nrc" placeholder="e.g. 345621/10/1"></div>
              </div>
              <div class="gr gr2">
                <div class="fg"><label class="fl">Phone Number</label><input class="fi mono" x-model="agr.phone" placeholder="+260 9XX XXX XXX"></div>
                <div class="fg"><label class="fl">Date of Birth</label><input class="fi" type="date" x-model="agr.dob"></div>
              </div>
              <div class="fg"><label class="fl">Residential Address *</label><textarea class="fta" style="min-height:60px" x-model="agr.address" placeholder="Plot number, area, city…"></textarea></div>

              <div class="fdiv"></div>
              <div class="fsec">💰 Loan Details</div>
              <div class="gr gr2">
                <div class="fg"><label class="fl">Loan Number</label><input class="fi mono" x-model="agr.loanNum" placeholder="LN-20260001"></div>
                <div class="fg"><label class="fl">Agreement Date *</label><input class="fi" type="date" x-model="agr.date"></div>
              </div>
              <div class="gr gr2">
                <div class="fg"><label class="fl">Principal Amount (K) *</label><div class="pfx"><span class="px">K</span><input class="fi" type="number" min="1" x-model.number="agr.principal" placeholder="50000"></div></div>
                <div class="fg">
                  <label class="fl">Loan Duration *</label>
                  <select class="fs" x-model.number="agr.term">
                    <option :value="1">1 Month — 10% flat</option>
                    <option :value="2">2 Months — 18% flat</option>
                    <option :value="3">3 Months — 28% flat</option>
                    <option :value="4">4 Months — 38% flat</option>
                  </select>
                </div>
              </div>
              <div class="gr gr2">
                <div class="fg">
                  <label class="fl">Collateral Type *</label>
                  <select class="fs" x-model="agr.collType">
                    <option value="vehicle">Vehicle</option>
                    <option value="land">Land</option>
                  </select>
                </div>
                <div class="fg"><label class="fl">Collateral Reference</label><input class="fi mono" x-model="agr.collRef" placeholder="e.g. ZMB 4521C or Plot 8821"></div>
              </div>

              <div class="fdiv"></div>
              <div class="fsec">👔 Officer Details</div>
              <div class="gr gr2">
                <div class="fg"><label class="fl">Loan Officer *</label><input class="fi" x-model="agr.officer" placeholder="e.g. F. Mwala"></div>
                <div class="fg"><label class="fl">Branch / Office</label><input class="fi" x-model="agr.branch" placeholder="e.g. Lusaka — Cairo Road"></div>
              </div>

              <!-- Computed summary -->
              <div class="fdiv"></div>
              <div class="fsec">📊 Computed Summary</div>
              <div class="rprev">
                <div class="rp-grid">
                  <div><div class="rp-item-l">Rate Applied</div><div class="rp-item-v" style="color:var(--amber)" x-text="(rateTiers[agr.term]||0)+'% flat'"></div></div>
                  <div><div class="rp-item-l">Interest (K)</div><div class="rp-item-v" style="color:var(--teal)" x-text="agrCalc.interest.toLocaleString()"></div></div>
                  <div><div class="rp-item-l">Total Repayable</div><div class="rp-item-v" style="color:var(--white)" x-text="'K '+agrCalc.total.toLocaleString()"></div></div>
                  <div><div class="rp-item-l">Monthly Instalment</div><div class="rp-item-v" style="color:var(--copper3)" x-text="'K '+agrCalc.monthly.toLocaleString()"></div></div>
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT: Preview -->
          <div>
            <div class="panel" style="margin-bottom:16px">
              <div class="ph"><span style="font-size:16px">👁</span><div class="pt">Agreement Preview</div><span style="margin-left:auto;font-size:11px;color:var(--slate)">Scroll to review · Print to save PDF</span></div>
              <div class="pb" style="padding:0;max-height:680px;overflow-y:auto">
                <div class="agr-paper" x-ref="agrPaper">
                  <div class="agr-hdr">
                    <div class="agr-brand">GRASS<span>EMA</span> <span style="font-size:14px;font-weight:400;opacity:.6">Loans</span></div>
                    <div class="agr-tagline">Empowering communities through accessible finance · Lusaka, Zambia</div>
                    <div style="margin-top:14px;font-size:11px;opacity:.5">Agreement No: <span class="mono" x-text="agr.loanNum||'—'"></span> &nbsp;|&nbsp; Date: <span x-text="agr.date||'—'"></span></div>
                  </div>
                  <div class="agr-body" style="color:#111">
                    <div class="agr-title">Loan Agreement</div>

                    <div class="agr-section">Borrower Details</div>
                    <div class="agr-row"><span class="agr-key">Full Name:</span><span class="agr-val" x-text="agr.borrowerName||'—'"></span></div>
                    <div class="agr-row"><span class="agr-key">NRC Number:</span><span class="agr-val" x-text="agr.nrc||'—'"></span></div>
                    <div class="agr-row"><span class="agr-key">Phone:</span><span class="agr-val" x-text="agr.phone||'—'"></span></div>
                    <div class="agr-row"><span class="agr-key">Address:</span><span class="agr-val" x-text="agr.address||'—'"></span></div>

                    <div class="agr-section">Loan Details</div>
                    <div class="agr-sum">
                      <div class="agr-sum-box"><div class="agr-sum-lbl">Principal</div><div class="agr-sum-val" x-text="'K '+(agr.principal||0).toLocaleString()"></div></div>
                      <div class="agr-sum-box"><div class="agr-sum-lbl">Rate</div><div class="agr-sum-val" x-text="(rateTiers[agr.term]||0)+'% flat'"></div></div>
                      <div class="agr-sum-box"><div class="agr-sum-lbl">Total Repayable</div><div class="agr-sum-val" x-text="'K '+agrCalc.total.toLocaleString()"></div></div>
                      <div class="agr-sum-box"><div class="agr-sum-lbl">Duration</div><div class="agr-sum-val" x-text="(agr.term||1)+' Month'+(agr.term>1?'s':'')"></div></div>
                    </div>

                    <div class="agr-section">Repayment Schedule</div>
                    <table class="agr-sched">
                      <thead><tr><th>#</th><th>Due Date</th><th>Instalment (K)</th><th>Principal (K)</th><th>Interest (K)</th><th>Balance (K)</th></tr></thead>
                      <tbody>
                        <template x-for="(row,i) in agrSchedule" :key="i">
                          <tr>
                            <td x-text="i===agrSchedule.length-1?'Total':row.n"></td>
                            <td x-text="row.dueDate"></td>
                            <td x-text="row.instalment.toLocaleString()"></td>
                            <td x-text="row.principal.toLocaleString()"></td>
                            <td x-text="row.interest.toLocaleString()"></td>
                            <td x-text="row.balance.toLocaleString()"></td>
                          </tr>
                        </template>
                      </tbody>
                    </table>

                    <div class="agr-section">Collateral</div>
                    <div class="agr-row"><span class="agr-key">Type:</span><span class="agr-val" x-text="agr.collType==='vehicle'?'Motor Vehicle':'Land / Property'"></span></div>
                    <div class="agr-row"><span class="agr-key">Reference:</span><span class="agr-val" x-text="agr.collRef||'—'"></span></div>

                    <div class="agr-section">Terms &amp; Conditions</div>
                    <div class="agr-terms">
                      1. The borrower agrees to repay the total amount of <strong x-text="'K '+agrCalc.total.toLocaleString()"></strong> in <strong x-text="(agr.term||1)+' equal monthly instalment'+(agr.term>1?'s':'')"></strong> of <strong x-text="'K '+agrCalc.monthly.toLocaleString()"></strong> each.<br><br>
                      2. Instalments are due on the same day of each month, beginning one month from the disbursement date.<br><br>
                      3. <strong>Default Penalty:</strong> A penalty of 5% of the monthly instalment (<strong x-text="'K '+agrCalc.penaltyAmt.toLocaleString()"></strong>) will be automatically added to any instalment that is not paid by the due date, after the applicable grace period.<br><br>
                      4. <strong>Early Settlement:</strong> The borrower may settle the loan early. In such case, interest will be recalculated at the rate applicable to the actual number of months the loan was active, and any overpaid interest will be refunded.<br><br>
                      5. The collateral described above is pledged as security. In the event of default, Gracimor Loans reserves the right to enforce the collateral in accordance with the law.<br><br>
                      6. This agreement is governed by the laws of the Republic of Zambia and the Bank of Zambia regulations.
                    </div>

                    <div class="agr-section">Signatures</div>
                    <div class="agr-sigs">
                      <div class="agr-sig-box">
                        <div style="height:40px"></div>
                        <div class="agr-sig-lbl">Borrower Signature &amp; Date</div>
                        <div class="agr-sig-name" x-text="agr.borrowerName||'_______________________'"></div>
                      </div>
                      <div class="agr-sig-box">
                        <div style="height:40px"></div>
                        <div class="agr-sig-lbl">Loan Officer Signature &amp; Date</div>
                        <div class="agr-sig-name" x-text="agr.officer||'_______________________'"></div>
                      </div>
                      <div class="agr-sig-box">
                        <div style="height:40px"></div>
                        <div class="agr-sig-lbl">Witness Signature &amp; Date</div>
                        <div class="agr-sig-name">_______________________</div>
                      </div>
                      <div class="agr-sig-box">
                        <div style="height:40px"></div>
                        <div class="agr-sig-lbl">Manager / Authorised Signatory</div>
                        <div class="agr-sig-name">_______________________</div>
                      </div>
                    </div>
                  </div>
                  <div class="agr-footer">
                    <span>Gracimor Loans · Plot 4821, Cairo Road, Lusaka, Zambia</span>
                    <span>Licence No. NBZ/2024/441 · Regulated by Bank of Zambia</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>


    <!-- ██ IMPORT / EXPORT ██ -->
    <template x-if="tab==='import'">
      <div class="fade">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
          <div>
            <div style="font-family:'Spectral',serif;font-size:18px;font-weight:700">Import &amp; Export</div>
            <div style="font-size:13px;color:var(--slate);margin-top:2px">Migrate data from your old system · Export current data for backup or analysis</div>
          </div>
        </div>

        <!-- ── IMPORT SECTION ── -->
        <div style="font-family:'Spectral',serif;font-size:15px;font-weight:700;color:var(--copper3);margin-bottom:14px;display:flex;align-items:center;gap:8px">
          <span>📥</span> Import Data
        </div>
        <div class="ibox" style="margin-bottom:20px">
          <span>ℹ️</span>
          <div>
            <strong>Import order is mandatory:</strong>
            Borrowers → Loans → Payments · Collateral · Guarantors.
            Each row is validated and either imported or reported as an error — no partial rows are saved.
            Duplicate borrowers (same NRC) are skipped automatically.
          </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;margin-bottom:32px">

          <!-- Borrowers card -->
          <div class="panel">
            <div class="ph">
              <span style="font-size:18px">👥</span>
              <div style="flex:1">
                <div class="pt">1. Borrowers</div>
                <div style="font-size:11px;color:var(--slate);margin-top:2px">Client profiles, employment &amp; next of kin</div>
              </div>
              <a :href="API+'/import/templates/borrowers'" style="font-size:11px;color:var(--teal);text-decoration:none;display:flex;align-items:center;gap:4px">⬇ Template</a>
            </div>
            <div class="pb">
              <div class="fg">
                <label class="fl">CSV File</label>
                <input type="file" accept=".csv,.txt" class="fi" style="padding:8px 10px;cursor:pointer"
                  @change="imp.borrowers.file = $event.target.files[0]">
              </div>
              <template x-if="imp.borrowers.result">
                <div :class="imp.borrowers.result.errors.length ? 'wbox' : 'ibox'" style="margin-bottom:12px;flex-direction:column;gap:6px">
                  <div style="font-weight:700" x-text="imp.borrowers.result.errors.length ? '⚠️ Import completed with errors' : '✓ Import successful'"></div>
                  <div style="font-size:12px">
                    <span style="color:var(--green)" x-text="imp.borrowers.result.imported+' imported'"></span> ·
                    <span style="color:var(--slate)" x-text="imp.borrowers.result.skipped+' skipped'"></span> ·
                    <span style="color:var(--red)" x-text="imp.borrowers.result.errors.length+' errors'"></span>
                    of <span x-text="imp.borrowers.result.total+' rows'"></span>
                  </div>
                  <template x-if="imp.borrowers.result.errors.length">
                    <div style="max-height:100px;overflow-y:auto;font-size:11px;color:var(--red);font-family:'IBM Plex Mono',monospace">
                      <template x-for="e in imp.borrowers.result.errors" :key="e">
                        <div x-text="e"></div>
                      </template>
                    </div>
                  </template>
                </div>
              </template>
              <button class="tbtn copper" style="width:100%" :disabled="!imp.borrowers.file || imp.borrowers.loading"
                @click="runImport('borrowers')">
                <span x-text="imp.borrowers.loading ? '⏳ Importing…' : '📥 Import Borrowers'"></span>
              </button>
            </div>
          </div>

          <!-- Loans card -->
          <div class="panel">
            <div class="ph">
              <span style="font-size:18px">📋</span>
              <div style="flex:1">
                <div class="pt">2. Loans</div>
                <div style="font-size:11px;color:var(--slate);margin-top:2px">Loan records · auto-generates schedule &amp; balance for active loans</div>
              </div>
              <a :href="API+'/import/templates/loans'" style="font-size:11px;color:var(--teal);text-decoration:none;display:flex;align-items:center;gap:4px">⬇ Template</a>
            </div>
            <div class="pb">
              <div class="fg">
                <label class="fl">CSV File</label>
                <input type="file" accept=".csv,.txt" class="fi" style="padding:8px 10px;cursor:pointer"
                  @change="imp.loans.file = $event.target.files[0]">
              </div>
              <template x-if="imp.loans.result">
                <div :class="imp.loans.result.errors.length ? 'wbox' : 'ibox'" style="margin-bottom:12px;flex-direction:column;gap:6px">
                  <div style="font-weight:700" x-text="imp.loans.result.errors.length ? '⚠️ Import completed with errors' : '✓ Import successful'"></div>
                  <div style="font-size:12px">
                    <span style="color:var(--green)" x-text="imp.loans.result.imported+' imported'"></span> ·
                    <span style="color:var(--slate)" x-text="imp.loans.result.skipped+' skipped'"></span> ·
                    <span style="color:var(--red)" x-text="imp.loans.result.errors.length+' errors'"></span>
                    of <span x-text="imp.loans.result.total+' rows'"></span>
                  </div>
                  <template x-if="imp.loans.result.errors.length">
                    <div style="max-height:100px;overflow-y:auto;font-size:11px;color:var(--red);font-family:'IBM Plex Mono',monospace">
                      <template x-for="e in imp.loans.result.errors" :key="e">
                        <div x-text="e"></div>
                      </template>
                    </div>
                  </template>
                </div>
              </template>
              <button class="tbtn copper" style="width:100%" :disabled="!imp.loans.file || imp.loans.loading"
                @click="runImport('loans')">
                <span x-text="imp.loans.loading ? '⏳ Importing…' : '📥 Import Loans'"></span>
              </button>
            </div>
          </div>

          <!-- Payments card -->
          <div class="panel">
            <div class="ph">
              <span style="font-size:18px">💳</span>
              <div style="flex:1">
                <div class="pt">3. Payments</div>
                <div style="font-size:11px;color:var(--slate);margin-top:2px">Historical payment records · updates loan balances</div>
              </div>
              <a :href="API+'/import/templates/payments'" style="font-size:11px;color:var(--teal);text-decoration:none;display:flex;align-items:center;gap:4px">⬇ Template</a>
            </div>
            <div class="pb">
              <div class="fg">
                <label class="fl">CSV File</label>
                <input type="file" accept=".csv,.txt" class="fi" style="padding:8px 10px;cursor:pointer"
                  @change="imp.payments.file = $event.target.files[0]">
              </div>
              <template x-if="imp.payments.result">
                <div :class="imp.payments.result.errors.length ? 'wbox' : 'ibox'" style="margin-bottom:12px;flex-direction:column;gap:6px">
                  <div style="font-weight:700" x-text="imp.payments.result.errors.length ? '⚠️ Import completed with errors' : '✓ Import successful'"></div>
                  <div style="font-size:12px">
                    <span style="color:var(--green)" x-text="imp.payments.result.imported+' imported'"></span> ·
                    <span style="color:var(--slate)" x-text="imp.payments.result.skipped+' skipped'"></span> ·
                    <span style="color:var(--red)" x-text="imp.payments.result.errors.length+' errors'"></span>
                    of <span x-text="imp.payments.result.total+' rows'"></span>
                  </div>
                  <template x-if="imp.payments.result.errors.length">
                    <div style="max-height:100px;overflow-y:auto;font-size:11px;color:var(--red);font-family:'IBM Plex Mono',monospace">
                      <template x-for="e in imp.payments.result.errors" :key="e">
                        <div x-text="e"></div>
                      </template>
                    </div>
                  </template>
                </div>
              </template>
              <button class="tbtn copper" style="width:100%" :disabled="!imp.payments.file || imp.payments.loading"
                @click="runImport('payments')">
                <span x-text="imp.payments.loading ? '⏳ Importing…' : '📥 Import Payments'"></span>
              </button>
            </div>
          </div>

          <!-- Collateral card -->
          <div class="panel">
            <div class="ph">
              <span style="font-size:18px">🚗</span>
              <div style="flex:1">
                <div class="pt">4. Collateral Assets</div>
                <div style="font-size:11px;color:var(--slate);margin-top:2px">Vehicles &amp; land pledged as security</div>
              </div>
              <a :href="API+'/import/templates/collateral'" style="font-size:11px;color:var(--teal);text-decoration:none;display:flex;align-items:center;gap:4px">⬇ Template</a>
            </div>
            <div class="pb">
              <div class="fg">
                <label class="fl">CSV File</label>
                <input type="file" accept=".csv,.txt" class="fi" style="padding:8px 10px;cursor:pointer"
                  @change="imp.collateral.file = $event.target.files[0]">
              </div>
              <template x-if="imp.collateral.result">
                <div :class="imp.collateral.result.errors.length ? 'wbox' : 'ibox'" style="margin-bottom:12px;flex-direction:column;gap:6px">
                  <div style="font-weight:700" x-text="imp.collateral.result.errors.length ? '⚠️ Import completed with errors' : '✓ Import successful'"></div>
                  <div style="font-size:12px">
                    <span style="color:var(--green)" x-text="imp.collateral.result.imported+' imported'"></span> ·
                    <span style="color:var(--slate)" x-text="imp.collateral.result.skipped+' skipped'"></span> ·
                    <span style="color:var(--red)" x-text="imp.collateral.result.errors.length+' errors'"></span>
                    of <span x-text="imp.collateral.result.total+' rows'"></span>
                  </div>
                  <template x-if="imp.collateral.result.errors.length">
                    <div style="max-height:100px;overflow-y:auto;font-size:11px;color:var(--red);font-family:'IBM Plex Mono',monospace">
                      <template x-for="e in imp.collateral.result.errors" :key="e">
                        <div x-text="e"></div>
                      </template>
                    </div>
                  </template>
                </div>
              </template>
              <button class="tbtn copper" style="width:100%" :disabled="!imp.collateral.file || imp.collateral.loading"
                @click="runImport('collateral')">
                <span x-text="imp.collateral.loading ? '⏳ Importing…' : '📥 Import Collateral'"></span>
              </button>
            </div>
          </div>

          <!-- Guarantors card -->
          <div class="panel">
            <div class="ph">
              <span style="font-size:18px">🤝</span>
              <div style="flex:1">
                <div class="pt">5. Guarantors</div>
                <div style="font-size:11px;color:var(--slate);margin-top:2px">Loan guarantor / co-signatory records</div>
              </div>
              <a :href="API+'/import/templates/guarantors'" style="font-size:11px;color:var(--teal);text-decoration:none;display:flex;align-items:center;gap:4px">⬇ Template</a>
            </div>
            <div class="pb">
              <div class="fg">
                <label class="fl">CSV File</label>
                <input type="file" accept=".csv,.txt" class="fi" style="padding:8px 10px;cursor:pointer"
                  @change="imp.guarantors.file = $event.target.files[0]">
              </div>
              <template x-if="imp.guarantors.result">
                <div :class="imp.guarantors.result.errors.length ? 'wbox' : 'ibox'" style="margin-bottom:12px;flex-direction:column;gap:6px">
                  <div style="font-weight:700" x-text="imp.guarantors.result.errors.length ? '⚠️ Import completed with errors' : '✓ Import successful'"></div>
                  <div style="font-size:12px">
                    <span style="color:var(--green)" x-text="imp.guarantors.result.imported+' imported'"></span> ·
                    <span style="color:var(--slate)" x-text="imp.guarantors.result.skipped+' skipped'"></span> ·
                    <span style="color:var(--red)" x-text="imp.guarantors.result.errors.length+' errors'"></span>
                    of <span x-text="imp.guarantors.result.total+' rows'"></span>
                  </div>
                  <template x-if="imp.guarantors.result.errors.length">
                    <div style="max-height:100px;overflow-y:auto;font-size:11px;color:var(--red);font-family:'IBM Plex Mono',monospace">
                      <template x-for="e in imp.guarantors.result.errors" :key="e">
                        <div x-text="e"></div>
                      </template>
                    </div>
                  </template>
                </div>
              </template>
              <button class="tbtn copper" style="width:100%" :disabled="!imp.guarantors.file || imp.guarantors.loading"
                @click="runImport('guarantors')">
                <span x-text="imp.guarantors.loading ? '⏳ Importing…' : '📥 Import Guarantors'"></span>
              </button>
            </div>
          </div>

        </div>

        <!-- ── EXPORT SECTION ── -->
        <div style="font-family:'Spectral',serif;font-size:15px;font-weight:700;color:var(--copper3);margin-bottom:14px;display:flex;align-items:center;gap:8px">
          <span>📤</span> Export Data
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px">

          <div class="panel" style="display:flex;align-items:center;gap:14px;padding:16px 20px">
            <span style="font-size:22px">👥</span>
            <div style="flex:1">
              <div style="font-size:13px;font-weight:700">All Borrowers</div>
              <div style="font-size:11px;color:var(--slate);margin-top:2px">Profiles &amp; contact info</div>
            </div>
            <button class="tbtn ghost" @click="runExport('borrowers')">⬇ CSV</button>
          </div>

          <div class="panel" style="display:flex;align-items:center;gap:14px;padding:16px 20px">
            <span style="font-size:22px">📋</span>
            <div style="flex:1">
              <div style="font-size:13px;font-weight:700">All Loans</div>
              <div style="font-size:11px;color:var(--slate);margin-top:2px">Loan book with terms &amp; status</div>
            </div>
            <button class="tbtn ghost" @click="runExport('loans')">⬇ CSV</button>
          </div>

          <div class="panel" style="display:flex;align-items:center;gap:14px;padding:16px 20px">
            <span style="font-size:22px">💳</span>
            <div style="flex:1">
              <div style="font-size:13px;font-weight:700">All Payments</div>
              <div style="font-size:11px;color:var(--slate);margin-top:2px">Full payment history</div>
            </div>
            <button class="tbtn ghost" @click="runExport('payments')">⬇ CSV</button>
          </div>

        </div>

      </div>
    </template>

  </div><!-- /content -->
</main>
</div>

<!-- DELETE PRODUCT MODAL -->
<div class="overlay" x-show="showDelModal" @click.self="showDelModal=false" style="display:none">
  <div class="modal sm">
    <div class="mh"><div class="mi red">🗑</div><div><div class="mt">Delete Product</div><div class="ms" x-text="selProd?.name"></div></div><button class="mc" @click="showDelModal=false">✕</button></div>
    <div class="mb">
      <div class="wbox"><span>⚠️</span><span>Products with active loans cannot be deleted — deactivate instead to stop new applications.</span></div>
      <div style="font-size:13.5px;color:var(--slate)">Type the product name to confirm:</div>
      <input class="fi" style="margin-top:10px" placeholder="Type product name to confirm…" x-model="delConfirm">
    </div>
    <div class="mf">
      <button class="mbtn gst" @click="showDelModal=false">Cancel</button>
      <button class="mbtn dng" :disabled="delConfirm!==selProd?.name" @click="confirmDel()">🗑 Delete</button>
    </div>
  </div>
</div>

<!-- NEW USER MODAL -->
<div class="overlay" x-show="showNewUser" @click.self="showNewUser=false" style="display:none">
  <div class="modal md">
    <div class="mh"><div class="mi blu">👤</div><div><div class="mt">Add New User</div><div class="ms">Create a staff account</div></div><button class="mc" @click="showNewUser=false">✕</button></div>
    <div class="mb">
      <div class="gr gr2">
        <div class="fg"><label class="fl">First Name *</label><input class="fi" placeholder="First name"></div>
        <div class="fg"><label class="fl">Last Name *</label><input class="fi" placeholder="Last name"></div>
      </div>
      <div class="fg"><label class="fl">Email Address *</label><input class="fi mono" type="email" placeholder="officer@gracimor.co.zm"></div>
      <div class="gr gr2">
        <div class="fg">
          <label class="fl">Role *</label>
          <select class="fs"><option>— Select role —</option><option>superadmin</option><option>ceo</option><option>manager</option><option>officer</option><option>accountant</option></select>
        </div>
        <div class="fg"><label class="fl">Phone</label><input class="fi mono" placeholder="+260 9XX XXX XXX"></div>
      </div>
      <div class="fg"><label class="fl">Temporary Password *</label><input class="fi mono" type="password" placeholder="Min 8 characters"></div>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:4px">
        <input type="checkbox" checked style="accent-color:var(--copper)">
        <span style="font-size:13.5px;color:var(--slate)">Send welcome email with login instructions</span>
      </label>
    </div>
    <div class="mf">
      <button class="mbtn gst" @click="showNewUser=false">Cancel</button>
      <button class="mbtn cop" @click="showNewUser=false;toast('ok','✓','User account created successfully')">✓ Create User</button>
    </div>
  </div>
</div>

<!-- TOASTS -->
<div class="tstack">
  <template x-for="t in toasts" :key="t.id">
    <div class="toast" :class="t.type"><span x-text="t.icon"></span><span x-text="t.msg"></span></div>
  </template>
</div>

<script>
function app(){
  return {
    tab:'products', unsaved:false,
    selProd:null, showDelModal:false, delConfirm:'',
    showNewUser:false,
    smsProvider:'africastalking',
    auditSearch:'', auditType:'', auditUser:'',
    selTpl:null,
    toasts:[],
    auditFrom:'', auditTo:'', dateExampleLabel:'DD MMM YYYY',

    /* ── API base + token (for export links and import requests) ── */
    API: '/api',
    token: localStorage.getItem('lms_token') || '',

    /* ── Import state — one entry per entity type ── */
    imp: {
      borrowers:  { file:null, loading:false, result:null },
      loans:      { file:null, loading:false, result:null },
      payments:   { file:null, loading:false, result:null },
      collateral: { file:null, loading:false, result:null },
      guarantors: { file:null, loading:false, result:null },
    },

    /* ── Rate tiers (system-wide, fixed by duration) ── */
    rateTiers: { 1:10, 2:18, 3:28, 4:38 },

    /* ── Agreement form state ── */
    agr: { borrowerName:'', nrc:'', phone:'', dob:'', address:'',
           loanNum:'', date:'', principal:0, term:4,
           collType:'vehicle', collRef:'', officer:'', branch:'Lusaka — Cairo Road' },

    products:[
      {id:1,name:'Vehicle-Backed Loan',code:'VBL-001',collType:'vehicle',active:true,
       maxTerm:4,minAmount:5000,maxAmount:150000,
       maxLtv:80,feeFlat:500,feePct:0,
       penaltyPct:5,penaltyBasis:'instalment',grace:7,
       earlySettle:true,requireGuarantor:false,
       desc:'Secured personal loans backed by motor vehicle collateral.',
       stats:{active:98,disbursed:2940200,outstanding:1980400,par:10.2}},
      {id:2,name:'Land-Backed Loan',code:'LBL-001',collType:'land',active:true,
       maxTerm:4,minAmount:20000,maxAmount:500000,
       maxLtv:70,feeFlat:0,feePct:1.5,
       penaltyPct:5,penaltyBasis:'instalment',grace:7,
       earlySettle:true,requireGuarantor:true,
       desc:'Higher-value loans secured against registered land titles.',
       stats:{active:44,disbursed:1273200,outstanding:859800,par:9.1}},
    ],

    jobs:[
      {key:'overdue', name:'Update Overdue Statuses',    desc:'Transition instalments → overdue, refresh days_overdue counters', enabled:true,  time:'00:05',lastRun:'26 Feb 2026, 00:05',lastStatus:'OK'},
      {key:'penalty', name:'Apply Daily Penalties',       desc:'Apply penalty % to all overdue instalments past grace period',     enabled:true,  time:'00:10',lastRun:'26 Feb 2026, 00:10',lastStatus:'OK'},
      {key:'reminders',name:'Send Instalment Reminders',  desc:'Dispatch SMS reminders based on trigger rules (7d, 3d, 1d, due, overdue)', enabled:true,time:'08:00',lastRun:'26 Feb 2026, 08:00',lastStatus:'OK'},
      {key:'report',  name:'Daily Portfolio Report Email',desc:'Generate and email portfolio summary to CEO and Manager',           enabled:false, time:'07:00',lastRun:'—',lastStatus:'—'},
    ],

    users:[
      {id:1,name:'Admin System',       ini:'AS',c1:'#dc2626',c2:'#ef4444',role:'superadmin',email:'admin@gracimor.co.zm',      loans:0, lastLogin:'Today',        active:true, perms:{approve:true, disburse:true, waive:true, report:true}},
      {id:2,name:'E. Mwansa',          ini:'EM',c1:'#b45309',c2:'#d97706',role:'ceo',        email:'ceo@gracimor.co.zm',        loans:0, lastLogin:'25 Feb 2026', active:true, perms:{approve:true, disburse:true, waive:true, report:true}},
      {id:3,name:'K. Simwanza',        ini:'KS',c1:'#0891b2',c2:'#06b6d4',role:'manager',    email:'k.simwanza@gracimor.co.zm', loans:0, lastLogin:'Today',       active:true, perms:{approve:true, disburse:false,waive:true, report:true}},
      {id:4,name:'F. Mwala',           ini:'FM',c1:'#059669',c2:'#10b981',role:'officer',    email:'f.mwala@gracimor.co.zm',    loans:52,lastLogin:'Today',       active:true, perms:{approve:false,disburse:false,waive:false,report:true}},
      {id:5,name:'C. Banda',           ini:'CB',c1:'#7c3aed',c2:'#8b5cf6',role:'officer',    email:'c.banda@gracimor.co.zm',    loans:48,lastLogin:'Today',       active:true, perms:{approve:false,disburse:false,waive:false,report:true}},
      {id:6,name:'N. Tembo',           ini:'NT',c1:'#166534',c2:'#22c55e',role:'officer',    email:'n.tembo@gracimor.co.zm',    loans:42,lastLogin:'26 Feb 2026', active:true, perms:{approve:false,disburse:false,waive:false,report:true}},
      {id:7,name:'P. Lungu',           ini:'PL',c1:'#4f46e5',c2:'#6366f1',role:'accountant', email:'p.lungu@gracimor.co.zm',    loans:0, lastLogin:'25 Feb 2026', active:true, perms:{approve:false,disburse:false,waive:false,report:true}},
    ],

    perms:[
      {label:'Apply Loans',          roles:[true,  true,  true,  true,  false]},
      {label:'Approve Loans',        roles:[true,  true,  true,  false, false]},
      {label:'Disburse Funds',       roles:[true,  true,  false, false, false]},
      {label:'Record Payments',      roles:[true,  true,  true,  true,  true]},
      {label:'Waive Penalties',      roles:[true,  true,  true,  false, false]},
      {label:'Escalate Accounts',    roles:[true,  true,  true,  true,  false]},
      {label:'Register Borrowers',   roles:[true,  true,  true,  true,  false]},
      {label:'Verify KYC',           roles:[true,  true,  true,  true,  false]},
      {label:'View Reports',         roles:[true,  true,  true,  true,  true]},
      {label:'Export Data',          roles:[true,  true,  true,  false, true]},
      {label:'Manage Users',         roles:[true,  false, false, false, false]},
      {label:'System Settings',      roles:[true,  false, false, false, false]},
      {label:'Edit Loan Products',   roles:[true,  true,  false, false, false]},
      {label:'View Audit Log',       roles:[true,  true,  true,  false, false]},
    ],

    auditLog:[
      {id:1, ts:'26 Feb 2026  14:32:11',user:'F. Mwala',   ini:'FM',c1:'#059669',c2:'#10b981',role:'officer',   type:'payment',  action:'payment.recorded',     entity:'RCP-00892', desc:'Payment K 9,508 for LN-20260018 — Charity Mutale (Cash)',         ip:'192.168.1.22'},
      {id:2, ts:'26 Feb 2026  11:15:44',user:'K. Simwanza',ini:'KS',c1:'#0891b2',c2:'#06b6d4',role:'manager',   type:'loan',     action:'loan.approved',         entity:'LN-20260051',desc:'Loan approved K 45,000 vehicle-backed — Oliver Zulu',           ip:'192.168.1.10'},
      {id:3, ts:'26 Feb 2026  09:40:08',user:'System',     ini:'SY',c1:'#374151',c2:'#6b7280',role:'system',    type:'penalty',  action:'penalty.applied',       entity:'BATCH-0088',desc:'Daily penalties K 1,240 applied across 14 overdue instalments',  ip:'127.0.0.1'},
      {id:4, ts:'26 Feb 2026  09:10:22',user:'System',     ini:'SY',c1:'#374151',c2:'#6b7280',role:'system',    type:'system',   action:'job.completed',         entity:'ApplyPenaltiesJob',desc:'Completed in 0.84s — 19 instalments processed',          ip:'127.0.0.1'},
      {id:5, ts:'25 Feb 2026  17:55:00',user:'C. Banda',   ini:'CB',c1:'#7c3aed',c2:'#8b5cf6',role:'officer',   type:'borrower', action:'borrower.kyc_verified', entity:'BRW-00051', desc:'KYC verified — Oliver Zulu, NRC 601022/88/1',                   ip:'192.168.1.18'},
      {id:6, ts:'25 Feb 2026  16:20:33',user:'F. Mwala',   ini:'FM',c1:'#059669',c2:'#10b981',role:'officer',   type:'penalty',  action:'penalty.waived',        entity:'LN-20260009',desc:'K 660 penalty waived for Grace Nkonde — borrower hardship',    ip:'192.168.1.22'},
      {id:7, ts:'25 Feb 2026  14:44:19',user:'K. Simwanza',ini:'KS',c1:'#0891b2',c2:'#06b6d4',role:'manager',   type:'loan',     action:'loan.disbursed',        entity:'LN-20260050',desc:'K 80,000 land-backed disbursed via bank transfer — BRW-00049', ip:'192.168.1.10'},
      {id:8, ts:'25 Feb 2026  10:05:58',user:'E. Mwansa',  ini:'EM',c1:'#b45309',c2:'#d97706',role:'ceo',       type:'user',     action:'user.role_changed',     entity:'USR-00007', desc:'P. Lungu role changed from officer to accountant',              ip:'192.168.1.5'},
      {id:9, ts:'24 Feb 2026  15:33:41',user:'N. Tembo',   ini:'NT',c1:'#166534',c2:'#22c55e',role:'officer',   type:'payment',  action:'payment.recorded',      entity:'RCP-00889', desc:'Payment K 6,720 for LN-20260041 — Daniel Phiri (Cash)',          ip:'192.168.1.25'},
      {id:10,ts:'24 Feb 2026  09:02:11',user:'System',     ini:'SY',c1:'#374151',c2:'#6b7280',role:'system',    type:'system',   action:'reminders.dispatched',  entity:'SMS-0881',  desc:'14 SMS reminders sent via Africa\'s Talking',                   ip:'127.0.0.1'},
      {id:11,ts:'23 Feb 2026  11:44:55',user:'F. Mwala',   ini:'FM',c1:'#059669',c2:'#10b981',role:'officer',   type:'loan',     action:'loan.applied',          entity:'LN-20260051',desc:'Loan application K 45,000 vehicle-backed — Oliver Zulu',        ip:'192.168.1.22'},
      {id:12,ts:'23 Feb 2026  09:10:00',user:'C. Banda',   ini:'CB',c1:'#7c3aed',c2:'#8b5cf6',role:'officer',   type:'borrower', action:'borrower.created',      entity:'BRW-00051', desc:'New borrower: Oliver Zulu — NRC 601022/88/1',                   ip:'192.168.1.18'},
    ],

    templates:[
      {key:'pre7', name:'Pre-Due — 7 Days',   trigger:'pre_due_7_days',   enabled:true,  channels:['SMS'],
       body:"Dear {first_name}, your loan {loan_number} instalment of K{amount_due} is due on {due_date}. Please prepare your payment. Thank you. — Gracimor Loans"},
      {key:'pre3', name:'Pre-Due — 3 Days',   trigger:'pre_due_3_days',   enabled:true,  channels:['SMS'],
       body:"Dear {first_name}, reminder: K{amount_due} due in 3 days on {due_date} for loan {loan_number}. Pay on time to avoid penalties. — Gracimor Loans"},
      {key:'pre1', name:'Pre-Due — 1 Day',    trigger:'pre_due_1_day',    enabled:true,  channels:['SMS','WhatsApp'],
       body:"Dear {first_name}, your payment of K{amount_due} for loan {loan_number} is due TOMORROW ({due_date}). Please ensure funds are ready. — Gracimor Loans"},
      {key:'due',  name:'Due Today',           trigger:'due_today',         enabled:true,  channels:['SMS'],
       body:"Dear {first_name}, K{amount_due} for loan {loan_number} is DUE TODAY. Please pay immediately to avoid a penalty. Contact {officer_name} for assistance. — Gracimor Loans"},
      {key:'ov1',  name:'Overdue — 1 Day',    trigger:'overdue_1_day',    enabled:true,  channels:['SMS'],
       body:"Dear {first_name}, your loan {loan_number} payment of K{amount_due} is now OVERDUE by 1 day. A penalty will be applied. Pay now: {officer_phone}. — Gracimor Loans"},
      {key:'ov7',  name:'Overdue — 7 Days',   trigger:'overdue_7_days',   enabled:true,  channels:['SMS'],
       body:"URGENT: Dear {first_name}, loan {loan_number} is 7 days overdue. Total now due: K{total_due} (includes K{penalty_amount} penalty). Call {officer_name} immediately."},
      {key:'ov14', name:'Overdue — 14 Days',  trigger:'overdue_14_days',  enabled:true,  channels:['SMS'],
       body:"FINAL NOTICE: {first_name}, loan {loan_number} is 14 days overdue. Failure to pay K{total_due} within 7 days may result in legal action. — Gracimor Loans"},
      {key:'ov30', name:'Overdue — 30 Days',  trigger:'overdue_30_days',  enabled:false, channels:['SMS'],
       body:"Legal Notice: {first_name}, loan {loan_number} is 30 days overdue. K{total_due} outstanding. Formal action will commence unless payment is received. — Gracimor Loans Legal"},
    ],

    tplVars:['first_name','last_name','loan_number','amount_due','total_due','due_date','penalty_amount','days_overdue','officer_name','officer_phone','company_name'],

    // ── computed ──────────────────────────────────────────
    get filteredAudit(){
      return this.auditLog.filter(e=>{
        const s = this.auditSearch.toLowerCase();
        const matchSearch = !s || e.user.toLowerCase().includes(s) || e.action.includes(s) || e.entity.toLowerCase().includes(s) || e.desc.toLowerCase().includes(s);
        const matchType = !this.auditType || e.type===this.auditType;
        const matchUser = !this.auditUser || e.user===this.auditUser;
        return matchSearch && matchType && matchUser;
      });
    },

    get rateTiersList() {
      return [1,2,3,4].map(m => ({ months:m, rate:this.rateTiers[m] }));
    },

    get agrCalc() {
      const p = Number(this.agr.principal) || 0;
      const t = Number(this.agr.term) || 4;
      const rate = this.rateTiers[t] || 38;
      const interest  = Math.round((rate / 100) * p * 100) / 100;
      const total     = Math.round((p + interest) * 100) / 100;
      const monthly   = Math.round((total / t) * 100) / 100;
      const penaltyAmt = Math.round(monthly * 0.05 * 100) / 100;
      return { interest, total, monthly, penaltyAmt, rate, months: t };
    },

    get agrSchedule() {
      const { monthly, interest, months } = this.agrCalc;
      const p = Number(this.agr.principal) || 0;
      if (!p || !months) return [];
      const perMonthInterest = Math.round((interest / months) * 100) / 100;
      const perMonthPrincipal = Math.round((p / months) * 100) / 100;
      const baseDate = this.agr.date ? new Date(this.agr.date) : new Date();
      const S = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      const rows = [];
      let bal = p + interest;
      let totInst = 0, totPri = 0, totInt = 0;
      for (let i = 1; i <= months; i++) {
        const d = new Date(baseDate);
        d.setMonth(d.getMonth() + i);
        const dueDate = d.getDate()+' '+S[d.getMonth()]+' '+d.getFullYear();
        bal = Math.round((bal - monthly) * 100) / 100;
        if (i === months) bal = 0;
        rows.push({ n:i, dueDate, instalment:monthly, principal:perMonthPrincipal, interest:perMonthInterest, balance:bal < 0 ? 0 : bal });
        totInst += monthly; totPri += perMonthPrincipal; totInt += perMonthInterest;
      }
      rows.push({ n:'—', dueDate:'TOTAL', instalment:Math.round(totInst*100)/100, principal:Math.round(totPri*100)/100, interest:Math.round(totInt*100)/100, balance:'—' });
      return rows;
    },

    // ── methods ───────────────────────────────────────────
    init(){
      this.selTpl = JSON.parse(JSON.stringify(this.templates[0]));
      const now = new Date();
      const S = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      const pad = n => String(n).padStart(2,'0');
      const mo = now.getMonth(), yr = now.getFullYear(), day = now.getDate();
      const fmtShort = d => d.getDate()+' '+S[d.getMonth()]+' '+d.getFullYear();
      const relDate = n => { const d=new Date(now); d.setDate(d.getDate()+n); return fmtShort(d); };
      this.dateExampleLabel = 'DD MMM YYYY ('+fmtShort(now)+')';
      this.auditFrom = yr+'-'+pad(mo+1)+'-01';
      this.auditTo   = yr+'-'+pad(mo+1)+'-'+pad(day);
      // Update job lastRun to today
      this.jobs.forEach(j => { j.lastRun = fmtShort(now)+', '+j.time; });
      // Update user lastLogin (id 2, 6 → -1; id 7 → -1; others stay 'Today')
      this.users.forEach(u => {
        if (u.lastLogin==='Today') u.lastLogin='Today';
        else u.lastLogin = relDate(-1);
      });
      // Update audit log timestamps (ids 1-4 = today, 5-8 = yesterday, 9-10 = -2d, 11-12 = -3d)
      const tsMap = {1:0,2:0,3:0,4:0, 5:-1,6:-1,7:-1,8:-1, 9:-2,10:-2, 11:-3,12:-3};
      const timeMap = {1:'14:32:11',2:'11:15:44',3:'09:40:08',4:'09:10:22',5:'17:55:00',6:'16:20:33',7:'14:44:19',8:'10:05:58',9:'15:33:41',10:'09:02:11',11:'11:44:55',12:'09:10:00'};
      this.auditLog.forEach(e => {
        const off = tsMap[e.id]; const time = timeMap[e.id];
        if (off !== undefined) e.ts = relDate(off)+'  '+(time||'00:00:00');
      });
    },

    openNew(){
      this.selProd = {id:Date.now(),name:'',code:'',collType:'vehicle',active:true,
        maxTerm:4,minAmount:5000,maxAmount:150000,
        maxLtv:80,feeFlat:0,feePct:0,penaltyPct:5,penaltyBasis:'instalment',grace:7,
        earlySettle:true,requireGuarantor:false,
        desc:'',stats:{active:0,disbursed:0,outstanding:0,par:0}};
      this.unsaved=true;
    },

    calcPreview(term){
      /* Flat rate model: interest = (rate/100) * principal */
      const n = 50000;
      const t = Number(term) || 4;
      const rate = this.rateTiers[t] || 38;
      const interest  = (rate / 100) * n;
      const total     = n + interest;
      const monthly   = total / t;
      return {
        monthly:  monthly.toLocaleString('en', {minimumFractionDigits:2, maximumFractionDigits:2}),
        total:    total.toLocaleString('en', {minimumFractionDigits:2, maximumFractionDigits:2}),
        interest: interest.toLocaleString('en', {minimumFractionDigits:2, maximumFractionDigits:2}),
      };
    },

    prodSummary(p){
      return [
        ['Collateral Type',  p.collType==='vehicle'?'Motor Vehicle':'Land / Property'],
        ['Loan Range', 'K '+Number(p.minAmount).toLocaleString()+' – K '+Number(p.maxAmount).toLocaleString()],
        ['Max LTV', p.maxLtv+'%'],
        ['Available Durations', '1, 2, 3, 4 months'],
        ['Rate Tiers', '10% · 18% · 28% · 38% (flat)'],
        ['Calculation Model', 'Flat interest on principal'],
        ['Penalty', '5% of monthly instalment'],
        ['Grace Period', (p.grace||7)+' days'],
        ['Processing Fee', p.feeFlat?'K '+Number(p.feeFlat).toLocaleString()+' flat':p.feePct?p.feePct+'% of principal':'None'],
        ['Early Settlement', p.earlySettle?'Allowed (rate recalculated)':'Not allowed'],
        ['Guarantor Required', p.requireGuarantor?'Yes':'No'],
      ];
    },

    saveProduct(){
      const idx = this.products.findIndex(p=>p.id===this.selProd.id);
      if(idx>=0){ this.products[idx]=JSON.parse(JSON.stringify(this.selProd)); }
      else { this.products.push(JSON.parse(JSON.stringify(this.selProd))); }
      this.unsaved=false;
      this.toast('ok','💾','Product saved — '+this.selProd.name);
    },

    duplicateProd(){
      const copy = JSON.parse(JSON.stringify(this.selProd));
      copy.id = Date.now(); copy.name+=' (Copy)'; copy.code+='X'; copy.active=false;
      copy.stats={active:0,disbursed:0,outstanding:0,par:0};
      this.products.push(copy);
      this.selProd=copy; this.unsaved=true;
      this.toast('info','📋','Product duplicated — update the name and code');
    },

    confirmDel(){
      if(this.delConfirm!==this.selProd?.name) return;
      this.products = this.products.filter(p=>p.id!==this.selProd.id);
      this.selProd=null; this.showDelModal=false; this.delConfirm='';
      this.toast('ok','🗑','Product deleted');
    },

    toggleCh(ch){
      const i=this.selTpl.channels.indexOf(ch);
      if(i>=0) this.selTpl.channels.splice(i,1);
      else this.selTpl.channels.push(ch);
      this.unsaved=true;
    },

    insertVar(v){
      const ta=this.$refs.tarea;
      if(!ta) return;
      const s=ta.selectionStart, e=ta.selectionEnd;
      const ins='{'+v+'}';
      this.selTpl.body=this.selTpl.body.slice(0,s)+ins+this.selTpl.body.slice(e);
      this.$nextTick(()=>{ ta.selectionStart=ta.selectionEnd=s+ins.length; ta.focus(); });
      this.unsaved=true;
    },

    previewBody(body){
      return body
        .replace(/{first_name}/g,'Grace')
        .replace(/{last_name}/g,'Nkonde')
        .replace(/{loan_number}/g,'LN-20260009')
        .replace(/{amount_due}/g,'4,880')
        .replace(/{total_due}/g,'14,820')
        .replace(/{due_date}/g,'26 Feb 2026')
        .replace(/{penalty_amount}/g,'4,620')
        .replace(/{days_overdue}/g,'14')
        .replace(/{officer_name}/g,'F. Mwala')
        .replace(/{officer_phone}/g,'+260 977 412 903')
        .replace(/{company_name}/g,'Gracimor Loans');
    },

    charCls(body){ const l=body.length; return l<140?'ok':l<160?'warn':'over'; },

    saveTpl(){
      const idx=this.templates.findIndex(t=>t.key===this.selTpl.key);
      if(idx>=0) this.templates[idx]=JSON.parse(JSON.stringify(this.selTpl));
      this.unsaved=false;
      this.toast('ok','💾','Template saved — '+this.selTpl.name);
    },

    agrReset(){
      this.agr = { borrowerName:'', nrc:'', phone:'', dob:'', address:'',
                   loanNum:'', date:'', principal:0, term:4,
                   collType:'vehicle', collRef:'', officer:'', branch:'Lusaka — Cairo Road' };
    },

    agrPrint(){
      if (!this.agr.borrowerName || !this.agr.principal) {
        this.toast('warn','⚠️','Please fill in borrower name and loan amount before printing'); return;
      }
      /* Show the print area, print, then hide it */
      const paper = this.$refs.agrPaper;
      if (!paper) { window.print(); return; }
      const clone = paper.cloneNode(true);
      let printDiv = document.getElementById('agr-print-target');
      if (!printDiv) {
        printDiv = document.createElement('div');
        printDiv.id = 'agr-print-target';
        printDiv.style.cssText = 'position:fixed;inset:0;background:white;z-index:9999;overflow:auto;padding:24px;display:none';
        document.body.appendChild(printDiv);
      }
      printDiv.innerHTML = '';
      printDiv.appendChild(clone);
      printDiv.style.display = 'block';
      document.body.style.overflow = 'hidden';
      setTimeout(() => {
        window.print();
        printDiv.style.display = 'none';
        document.body.style.overflow = '';
      }, 200);
    },

    async runExport(type){
      this.toast('info','⏳','Preparing '+type+' export…');
      try {
        const r = await fetch(this.API + '/export/' + type, {
          headers: { 'Authorization': 'Bearer ' + this.token, 'Accept': 'text/csv' },
        });
        if (!r.ok) { this.toast('err','✕','Export failed: ' + r.status); return; }
        const blob = await r.blob();
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = type + '_export_' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        this.toast('ok','✓', type+' exported successfully');
      } catch(e) {
        this.toast('err','✕','Export error: ' + e.message);
      }
    },

    async runImport(type){
      const s = this.imp[type];
      if (!s.file) return;
      s.loading = true;
      s.result  = null;
      const fd  = new FormData();
      fd.append('file', s.file);
      try {
        const r = await fetch(this.API + '/import/' + type, {
          method: 'POST',
          headers: { 'Authorization': 'Bearer ' + this.token, 'Accept': 'application/json' },
          body: fd,
        });
        const data = await r.json();
        if (!r.ok) {
          s.result = { imported:0, skipped:0, errors:[data.message || 'Server error ' + r.status], total:0 };
          this.toast('err','✕', 'Import failed: ' + (data.message || r.status));
        } else {
          s.result = data;
          if (data.errors && data.errors.length) {
            this.toast('warn','⚠️', type+' import: '+data.imported+' imported, '+data.errors.length+' errors');
          } else {
            this.toast('ok','✓', type+' import: '+data.imported+' records imported successfully');
          }
        }
      } catch(e) {
        s.result = { imported:0, skipped:0, errors:['Network error — ' + e.message], total:0 };
        this.toast('err','✕','Network error during import');
      } finally {
        s.loading = false;
      }
    },

    saveAll(){
      this.unsaved=false;
      this.toast('ok','💾','All settings saved successfully');
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
