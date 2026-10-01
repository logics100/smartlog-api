<style id="smartlog-hod-modern-ui">

:root{
    --sl-navy:#062b63;
    --sl-deep:#06499c;
    --sl-blue:#087bea;
    --sl-cyan:#13b9ef;
    --sl-pale:#edf7ff;
    --sl-bg:#f5f8fc;
    --sl-border:#dbe7f2;
    --sl-text:#17324d;
    --sl-muted:#6b7e91;
}

/* =========================
   PAGE FOUNDATION
   ========================= */

.sidebar{
    display:none !important;
}

body{
    margin:0 !important;
    padding-left:0 !important;
    background:var(--sl-bg) !important;
    color:var(--sl-text) !important;
    font-family:Inter,"Segoe UI",Arial,Helvetica,sans-serif !important;
}

.app{
    display:block !important;
    min-height:100vh !important;
}

.main{
    margin-left:0 !important;
    width:100% !important;
    min-height:100vh !important;
}

/* shared SmartLog header replaces old page header */

.main > .topbar{
    display:none !important;
}

.content{
    width:min(1400px,calc(100% - 48px)) !important;
    max-width:1400px !important;
    margin:0 auto !important;
    padding:34px 0 50px !important;
}

/* =========================
   READ-ONLY NOTICE
   ========================= */

.notice{
    position:relative !important;
    margin:0 0 25px !important;
    padding:18px 20px 18px 54px !important;

    background:
        linear-gradient(110deg,#edf8ff,#ffffff) !important;

    border:1px solid #cce5f8 !important;
    border-left:4px solid var(--sl-blue) !important;

    border-radius:14px !important;

    color:#365d7d !important;
    line-height:1.6 !important;

    box-shadow:0 7px 22px rgba(6,43,99,.04) !important;
}

.notice::before{
    content:"i";
    position:absolute;
    left:18px;
    top:50%;
    transform:translateY(-50%);

    width:23px;
    height:23px;

    display:flex;
    align-items:center;
    justify-content:center;

    border-radius:50%;

    background:var(--sl-blue);
    color:white;

    font-weight:800;
    font-family:Georgia,serif;
}

.notice strong{
    color:var(--sl-navy) !important;
}

/* =========================
   DASHBOARD SUMMARY CARDS
   ========================= */

.cards{
    display:grid !important;
    grid-template-columns:repeat(auto-fit,minmax(210px,1fr)) !important;
    gap:17px !important;
    margin-bottom:25px !important;
}

.card{
    position:relative !important;
    overflow:hidden !important;

    background:#ffffff !important;
    border:1px solid var(--sl-border) !important;
    border-radius:18px !important;

    padding:22px !important;

    box-shadow:
        0 10px 28px rgba(6,43,99,.055) !important;

    transition:
        transform .18s ease,
        box-shadow .18s ease !important;
}

.card::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    right:0;
    height:4px;

    background:
        linear-gradient(
            90deg,
            var(--sl-navy),
            var(--sl-blue),
            var(--sl-cyan)
        );
}

.card:hover{
    transform:translateY(-2px);
    box-shadow:
        0 15px 34px rgba(6,43,99,.10) !important;
}

.card .label{
    color:var(--sl-muted) !important;

    font-size:11px !important;
    font-weight:800 !important;

    text-transform:uppercase;
    letter-spacing:.55px;

    margin-bottom:10px !important;
}

.card .value{
    color:var(--sl-blue) !important;

    font-size:30px !important;
    line-height:1 !important;

    font-weight:800 !important;
}

.card .desc{
    color:#8192a2 !important;
    margin-top:9px !important;
    line-height:1.5 !important;
}

/* =========================
   PANELS
   ========================= */

.panel{
    margin-bottom:22px !important;

    background:#ffffff !important;

    border:1px solid var(--sl-border) !important;
    border-radius:18px !important;

    padding:22px !important;

    box-shadow:
        0 10px 28px rgba(6,43,99,.05) !important;
}

.panel h3{
    margin:0 0 16px !important;

    color:var(--sl-navy) !important;

    font-size:18px !important;
    font-weight:800 !important;
}

.grid2{
    display:grid !important;
    grid-template-columns:repeat(2,minmax(0,1fr)) !important;
    gap:18px !important;
}

/* =========================
   STATS
   ========================= */

.stat{
    display:flex !important;
    justify-content:space-between !important;
    align-items:center !important;
    gap:15px !important;

    padding:12px 0 !important;

    border-bottom:1px solid #edf2f7 !important;

    font-size:14px !important;
}

.stat:last-child{
    border-bottom:0 !important;
}

.stat span{
    color:var(--sl-muted) !important;
}

.stat strong{
    color:var(--sl-navy) !important;
}

/* =========================
   PROGRESS BARS
   ========================= */

.progress{
    height:9px !important;

    margin-top:7px !important;

    background:#e4edf6 !important;

    border-radius:999px !important;

    overflow:hidden !important;
}

.bar{
    height:100% !important;

    background:
        linear-gradient(
            90deg,
            var(--sl-deep),
            var(--sl-blue),
            var(--sl-cyan)
        ) !important;

    border-radius:999px !important;
}

/* =========================
   FILTERS
   ========================= */

.filters{
    display:flex !important;
    align-items:center !important;

    gap:10px !important;

    flex-wrap:wrap !important;

    margin-bottom:0 !important;
}

.filters input,
.filters select{
    min-width:200px !important;
    min-height:42px !important;

    padding:10px 13px !important;

    background:#ffffff !important;

    border:1px solid #cad9e7 !important;
    border-radius:10px !important;

    color:var(--sl-text) !important;

    outline:none !important;
}

.filters input:focus,
.filters select:focus{
    border-color:var(--sl-blue) !important;

    box-shadow:
        0 0 0 3px rgba(8,123,234,.10) !important;
}

/* =========================
   BUTTONS
   ========================= */

.btn{
    display:inline-flex !important;

    align-items:center !important;
    justify-content:center !important;

    min-height:39px !important;

    padding:9px 14px !important;

    border:0 !important;
    border-radius:10px !important;

    background:
        linear-gradient(
            120deg,
            var(--sl-deep),
            var(--sl-blue)
        ) !important;

    color:#ffffff !important;

    text-decoration:none !important;

    font-size:12px !important;
    font-weight:750 !important;

    cursor:pointer !important;

    box-shadow:
        0 5px 15px rgba(8,123,234,.16) !important;

    transition:
        transform .16s ease,
        box-shadow .16s ease !important;
}

.btn:hover{
    transform:translateY(-1px);

    box-shadow:
        0 8px 20px rgba(8,123,234,.24) !important;
}

.btn.secondary{
    background:var(--sl-pale) !important;

    color:var(--sl-deep) !important;

    border:1px solid #cce3f8 !important;

    box-shadow:none !important;
}

/* =========================
   TABLES
   ========================= */

.table-wrap{
    overflow-x:auto !important;

    background:#ffffff !important;

    border:1px solid var(--sl-border) !important;
    border-radius:17px !important;

    box-shadow:
        0 10px 30px rgba(6,43,99,.05) !important;
}

table{
    width:100% !important;
    border-collapse:collapse !important;
}

th{
    padding:14px 15px !important;

    background:#f3f8fd !important;

    color:#587087 !important;

    border-bottom:
        1px solid var(--sl-border) !important;

    font-size:11px !important;
    font-weight:800 !important;

    text-transform:uppercase !important;
    letter-spacing:.4px !important;

    white-space:nowrap;
}

td{
    padding:15px !important;

    border-bottom:
        1px solid #edf2f7 !important;

    color:#29435c !important;

    font-size:13px !important;

    vertical-align:middle !important;
}

tbody tr{
    transition:background .15s ease;
}

tbody tr:hover{
    background:#f8fbff !important;
}

tbody tr:last-child td{
    border-bottom:0 !important;
}

td strong{
    color:var(--sl-navy) !important;
}

.muted{
    color:var(--sl-muted) !important;
}

/* =========================
   EMPTY STATE
   ========================= */

.empty{
    padding:38px 22px !important;

    text-align:center !important;

    color:var(--sl-muted) !important;

    background:#ffffff !important;
}

/* =========================
   HOD DASHBOARD HERO EFFECT
   ========================= */

.content > .notice:first-child{
    margin-top:0 !important;
}

/* dashboard first cards feel more like overview widgets */

.content > .cards:first-of-type .card{
    min-height:125px !important;
}

/* =========================
   YEAR LEVEL CARDS
   ========================= */

.cards .card .stat{
    padding:10px 0 !important;
}

/* =========================
   FOOTER
   ========================= */

.footer{
    margin-top:35px !important;

    padding:22px 0 5px !important;

    border-top:
        1px solid var(--sl-border) !important;

    color:#8292a1 !important;

    font-size:12px !important;

    text-align:center !important;
}

/* =========================
   READ-ONLY VISUAL IDENTITY
   ========================= */

.role{
    background:#e8f6ff !important;
    color:var(--sl-deep) !important;
}

/* =========================
   RESPONSIVE
   ========================= */

@media(max-width:1000px){

    .grid2{
        grid-template-columns:1fr !important;
    }

    .cards{
        grid-template-columns:repeat(2,1fr) !important;
    }
}

@media(max-width:760px){

    .content{
        width:calc(100% - 26px) !important;
        padding:23px 0 40px !important;
    }

    .cards{
        grid-template-columns:1fr !important;
    }

    .panel{
        padding:18px !important;
    }

    .notice{
        padding:16px 16px 16px 48px !important;
    }

    .filters{
        align-items:stretch !important;
        flex-direction:column !important;
    }

    .filters input,
    .filters select,
    .filters .btn{
        width:100% !important;
        min-width:0 !important;
    }

    td,
    th{
        padding:13px 12px !important;
    }
}

</style>