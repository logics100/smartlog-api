<style id="smartlog-admin-modern-ui">

:root{
    --sl-navy:#062b63;
    --sl-deep:#06499c;
    --sl-blue:#087bea;
    --sl-cyan:#13b9ef;
    --sl-bg:#f4f8fc;
    --sl-pale:#edf7ff;
    --sl-border:#dbe7f2;
    --sl-text:#17324d;
    --sl-muted:#6b7e91;
    --sl-white:#fff;
}

/* ===== PAGE ===== */

body{
    margin:0 !important;
    padding-left:0 !important;
    background:var(--sl-bg) !important;
    color:var(--sl-text) !important;
    font-family:Inter,"Segoe UI",Arial,Helvetica,sans-serif !important;
}

.shell{
    display:block !important;
    min-height:100vh !important;
}

/* Keep old Admin sidebar in DOM for shared menu,
   but professional-theme provides visible navigation. */

.side{
    display:none !important;
}

.main{
    width:100% !important;
    margin-left:0 !important;
    min-height:100vh !important;
}

/* Old Admin topbar is replaced by shared SmartLog header */

.main > .top{
    display:none !important;
}

.content{
    width:min(1400px,calc(100% - 48px)) !important;
    max-width:1400px !important;
    margin:0 auto !important;
    padding:34px 0 55px !important;
}

/* ===== TITLES ===== */

.content > h1,
.section-title h1{
    margin:0 0 8px !important;
    color:var(--sl-navy) !important;
    font-size:clamp(26px,3vw,35px) !important;
    line-height:1.1 !important;
    letter-spacing:-.7px !important;
}

.content > h1::before,
.section-title h1::before{
    content:"SMARTLOG • ICT ADMIN";
    display:block;
    width:max-content;
    max-width:100%;
    margin-bottom:11px;
    padding:6px 11px;
    border-radius:999px;
    background:#e7f6ff;
    color:var(--sl-blue);
    font-size:10px;
    line-height:1.2;
    font-weight:800;
    letter-spacing:.65px;
}

.content > h1 + .muted{
    margin-top:0 !important;
    margin-bottom:25px !important;
    max-width:780px;
    line-height:1.6;
}

.section-title{
    display:flex !important;
    justify-content:space-between !important;
    align-items:flex-end !important;
    gap:15px !important;
    margin:29px 0 14px !important;
}

.section-title h2{
    margin:0 !important;
    color:var(--sl-navy) !important;
    font-size:20px !important;
}

.section-title > div > .muted{
    margin-top:5px;
}

/* ===== CARDS ===== */

.grid{
    display:grid !important;
    grid-template-columns:repeat(auto-fit,minmax(210px,1fr)) !important;
    gap:17px !important;
}

.card{
    position:relative;
    background:#fff !important;
    border:1px solid var(--sl-border) !important;
    border-radius:17px !important;
    padding:21px !important;
    box-shadow:0 10px 28px rgba(6,43,99,.055) !important;
}

.grid > .card{
    overflow:hidden;
    transition:transform .17s ease,box-shadow .17s ease !important;
}

.grid > .card::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    right:0;
    height:4px;
    background:linear-gradient(
        90deg,
        var(--sl-navy),
        var(--sl-blue),
        var(--sl-cyan)
    );
}

.grid > .card:hover{
    transform:translateY(-2px);
    box-shadow:0 15px 34px rgba(6,43,99,.10) !important;
}

.card h3{
    margin:4px 0 9px !important;
    color:var(--sl-navy) !important;
    font-size:17px !important;
}

.card p{
    color:var(--sl-muted);
    line-height:1.6;
}

.num{
    margin:8px 0 5px !important;
    color:var(--sl-blue) !important;
    font-size:31px !important;
    line-height:1 !important;
    font-weight:800 !important;
}

.muted{
    color:var(--sl-muted) !important;
    font-size:13px !important;
}

/* ===== BUTTONS ===== */

.btn{
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;

    min-height:39px !important;

    padding:9px 14px !important;

    border:0 !important;
    border-radius:10px !important;

    background:linear-gradient(
        120deg,
        var(--sl-deep),
        var(--sl-blue)
    ) !important;

    color:#fff !important;

    text-decoration:none !important;

    font-size:12px !important;
    font-weight:750 !important;

    cursor:pointer !important;

    box-shadow:0 5px 15px rgba(8,123,234,.16);

    transition:
        transform .16s ease,
        box-shadow .16s ease !important;
}

.btn:hover{
    transform:translateY(-1px);
    box-shadow:0 8px 20px rgba(8,123,234,.24);
}

.btn.gray{
    background:#eef5fb !important;
    color:var(--sl-deep) !important;
    border:1px solid #d0e2f2 !important;
    box-shadow:none !important;
}

.btn.small{
    min-height:32px !important;
    padding:7px 11px !important;
    font-size:11px !important;
}

/* ===== FORMS ===== */

.form-grid,
.filters{
    display:grid !important;
    grid-template-columns:repeat(auto-fit,minmax(190px,1fr)) !important;
    gap:15px !important;
}

.field{
    display:flex !important;
    flex-direction:column !important;
    gap:7px !important;
}

.field label,
.upload-form label{
    color:#48627a !important;
    font-size:11px !important;
    font-weight:800 !important;
    letter-spacing:.15px;
}

.field input,
.field select,
.field textarea,
.upload-form input[type="file"]{
    width:100% !important;
    min-height:42px !important;

    padding:10px 12px !important;

    border:1px solid #cad9e7 !important;
    border-radius:10px !important;

    background:#fff !important;
    color:var(--sl-text) !important;

    outline:none !important;
}

.field input:focus,
.field select:focus,
.field textarea:focus,
.upload-form input[type="file"]:focus{
    border-color:var(--sl-blue) !important;
    box-shadow:0 0 0 3px rgba(8,123,234,.10) !important;
}

/* ===== TABLES ===== */

.table-wrap{
    overflow:auto !important;
}

.card.table-wrap{
    padding:0 !important;
    overflow:auto !important;
}

.table{
    width:100% !important;
    margin:0 !important;
    border-collapse:collapse !important;
}

.table th{
    padding:14px 15px !important;
    background:#f3f8fd !important;
    color:#587087 !important;
    border-bottom:1px solid var(--sl-border) !important;

    font-size:11px !important;
    font-weight:800 !important;
    text-transform:uppercase !important;
    letter-spacing:.4px !important;
}

.table td{
    padding:15px !important;
    color:#29435c !important;
    border-bottom:1px solid #edf2f7 !important;
    vertical-align:middle !important;
}

.table tbody tr{
    transition:background .15s ease;
}

.table tbody tr:hover{
    background:#f8fbff !important;
}

.table tbody tr:last-child td{
    border-bottom:0 !important;
}

/* ===== BADGES ===== */

.badge{
    display:inline-flex !important;
    align-items:center !important;
    padding:6px 10px !important;

    border-radius:999px !important;

    background:#e7f8ef !important;
    color:#157347 !important;

    font-size:10px !important;
    font-weight:800 !important;
}

.badge.off{
    background:#fff0f0 !important;
    color:#a23a3a !important;
}

/* ===== ALERTS ===== */

.alert{
    margin-bottom:18px !important;
    padding:14px 16px !important;
    border-radius:11px !important;
    line-height:1.55;
}

.alert.ok{
    background:#eaf8f0 !important;
    color:#17603a !important;
    border:1px solid #c6ead5 !important;
}

.alert.err{
    background:#fff1f1 !important;
    color:#9b2c2c !important;
    border:1px solid #f0caca !important;
}

/* ======================================
   APPEARANCE & IMAGES
   ====================================== */

.appearance-heading{
    margin-bottom:23px !important;
}

.appearance-heading h1{
    margin:0 0 7px !important;
    color:var(--sl-navy) !important;
    font-size:clamp(27px,3vw,35px) !important;
}

.appearance-heading h1::before{
    content:"SMARTLOG • ICT ADMIN";
    display:block;
    width:max-content;
    margin-bottom:11px;
    padding:6px 11px;
    border-radius:999px;
    background:#e7f6ff;
    color:var(--sl-blue);
    font-size:10px;
    font-weight:800;
    letter-spacing:.65px;
}

.appearance-heading p{
    color:var(--sl-muted) !important;
    line-height:1.6 !important;
}

.appearance-note{
    margin-bottom:23px !important;
    padding:16px 18px !important;

    border:1px solid #cce5f8 !important;
    border-left:4px solid var(--sl-blue) !important;
    border-radius:12px !important;

    background:linear-gradient(
        110deg,
        #edf8ff,
        #fff
    ) !important;

    color:#365d7d !important;
}

.appearance-grid{
    gap:18px !important;
}

.image-card{
    border:1px solid var(--sl-border) !important;
    border-radius:17px !important;
    box-shadow:0 10px 28px rgba(6,43,99,.06) !important;
}

.image-preview{
    background:#eaf1f7 !important;
}

.image-status{
    background:rgba(6,43,99,.92) !important;
}

.image-card h2{
    color:var(--sl-navy) !important;
}

.image-description{
    color:var(--sl-muted) !important;
}

.upload-form{
    border-top:1px solid #edf2f7 !important;
}

.replace-button{
    min-height:39px !important;
    padding:0 15px !important;

    border:0 !important;
    border-radius:10px !important;

    background:linear-gradient(
        120deg,
        var(--sl-deep),
        var(--sl-blue)
    ) !important;

    color:#fff !important;
    font-weight:800 !important;

    cursor:pointer;
}

.reset-button{
    min-height:39px !important;
    padding:0 15px !important;

    border:1px solid #cfdeeb !important;
    border-radius:10px !important;

    background:#f5f9fc !important;
    color:var(--sl-deep) !important;

    font-weight:800 !important;
    cursor:pointer;
}

.alert-success{
    background:#eaf8f0 !important;
    color:#17603a !important;
    border:1px solid #c6ead5 !important;
    border-radius:11px !important;
}

.alert-error{
    background:#fff1f1 !important;
    color:#9b2c2c !important;
    border:1px solid #f0caca !important;
    border-radius:11px !important;
}

/* ===== RESPONSIVE ===== */

@media(max-width:850px){

    .content{
        width:calc(100% - 28px) !important;
        padding:24px 0 45px !important;
    }

    .section-title{
        align-items:flex-start !important;
        flex-direction:column !important;
    }

    .section-title > .btn{
        width:100%;
    }

    .appearance-grid{
        grid-template-columns:1fr !important;
    }
}

@media(max-width:600px){

    .grid{
        grid-template-columns:1fr !important;
    }

    .form-grid,
    .filters{
        grid-template-columns:1fr !important;
    }

    .content{
        width:calc(100% - 24px) !important;
    }
}

</style>