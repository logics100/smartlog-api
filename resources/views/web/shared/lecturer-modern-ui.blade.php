<style id="smartlog-lecturer-modern-ui">

/* =========================================================
   SMARTLOG LECTURER PORTAL - MODERN UI
   Visual layer only. Existing routes and Blade logic remain.
   ========================================================= */

:root {
    --sl-navy: #062b63;
    --sl-deep: #06499c;
    --sl-blue: #087bea;
    --sl-cyan: #13b9ef;
    --sl-pale: #edf7ff;
    --sl-bg: #f5f8fc;
    --sl-border: #dbe7f2;
    --sl-text: #17324d;
    --sl-muted: #6b7e91;
    --sl-white: #ffffff;
}

/* ---------- REMOVE OLD PAGE SHELL ---------- */

.sidebar,
.lecturer-sidebar {
    display: none !important;
}

body {
    padding-left: 0 !important;
    margin: 0 !important;
    background: var(--sl-bg) !important;
    color: var(--sl-text) !important;
    font-family:
        Inter,
        "Segoe UI",
        Arial,
        Helvetica,
        sans-serif !important;
}

.app,
.page {
    display: block !important;
    min-height: 100vh !important;
}

.main {
    margin-left: 0 !important;
    width: 100% !important;
    min-height: 100vh !important;
}

/* Old page topbars are no longer needed.
   The shared SmartLog header is now the real portal header. */

.main > .topbar,
body > .top {
    display: none !important;
}

/* ---------- CONTENT ---------- */

.content,
.wrap {
    width: min(1400px, calc(100% - 48px)) !important;
    max-width: 1400px !important;
    margin: 0 auto !important;
    padding: 34px 0 50px !important;
}

/* Old duplicate horizontal navigation */
.wrap > .nav {
    display: none !important;
}

/* ---------- PAGE TITLES ---------- */

.page-heading,
.heading {
    margin: 0 0 26px !important;
}

.page-heading::before,
.heading::before {
    content: "SMARTLOG • LECTURER";
    display: inline-flex;
    align-items: center;
    min-height: 26px;
    padding: 5px 11px;
    margin-bottom: 11px;
    border-radius: 999px;
    background: #e8f7ff;
    color: var(--sl-blue);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .7px;
}

.page-heading h3,
.heading h1 {
    margin: 0 0 7px !important;
    color: var(--sl-navy) !important;
    font-size: clamp(25px, 3vw, 34px) !important;
    line-height: 1.1 !important;
    letter-spacing: -.7px;
}

.page-heading p,
.heading p {
    margin: 0 !important;
    max-width: 760px;
    color: var(--sl-muted) !important;
    font-size: 14px !important;
    line-height: 1.65 !important;
}

/* Clinical Logbooks heading */

.wrap > div[style*="justify-content:space-between"] {
    background:
        linear-gradient(120deg, #ffffff 0%, #f3f9ff 100%) !important;
    border: 1px solid var(--sl-border) !important;
    border-radius: 20px !important;
    padding: 24px !important;
    margin-bottom: 20px !important;
    box-shadow: 0 12px 32px rgba(6,43,99,.06) !important;
}

.wrap h2,
.wrap h3 {
    color: var(--sl-navy) !important;
}

/* ---------- SUMMARY CARDS ---------- */

.summary,
.summary-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(190px,1fr)) !important;
    gap: 16px !important;
    margin-bottom: 24px !important;
}

.summary-card {
    position: relative !important;
    overflow: hidden !important;
    min-height: 120px !important;
    padding: 21px !important;
    background: #ffffff !important;
    border: 1px solid var(--sl-border) !important;
    border-radius: 17px !important;
    box-shadow: 0 9px 26px rgba(6,43,99,.055) !important;
    transition:
        transform .18s ease,
        box-shadow .18s ease !important;
}

.summary-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    width: 5px;
    height: 100%;
    background: linear-gradient(
        180deg,
        var(--sl-cyan),
        var(--sl-blue),
        var(--sl-navy)
    );
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 13px 32px rgba(6,43,99,.10) !important;
}

.summary-label,
.summary-card .label,
.summary-card > span {
    color: var(--sl-muted) !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: .45px;
}

.summary-value,
.summary-card .value,
.summary-card > strong {
    display: block !important;
    margin-top: 9px !important;
    color: var(--sl-blue) !important;
    font-size: 30px !important;
    line-height: 1 !important;
    font-weight: 800 !important;
}

/* ---------- UNIT CARDS ---------- */

.unit-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(310px,1fr)) !important;
    gap: 18px !important;
}

.unit-card {
    position: relative;
    background: #ffffff !important;
    border: 1px solid var(--sl-border) !important;
    border-radius: 18px !important;
    padding: 23px !important;
    box-shadow: 0 10px 28px rgba(6,43,99,.055) !important;
    transition:
        transform .18s ease,
        box-shadow .18s ease !important;
}

.unit-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 38px rgba(6,43,99,.10) !important;
}

.unit-code {
    background: var(--sl-pale) !important;
    color: var(--sl-blue) !important;
    border-radius: 999px !important;
    padding: 6px 11px !important;
}

.unit-title {
    color: var(--sl-navy) !important;
}

.unit-details,
.unit-actions {
    border-color: #e9f0f7 !important;
}

.detail-label {
    color: var(--sl-muted) !important;
}

.detail-value {
    color: var(--sl-text) !important;
}

/* ---------- MAIN CONTENT CARDS ---------- */

.students-section,
.section-card,
.card {
    background: #ffffff !important;
    border: 1px solid var(--sl-border) !important;
    border-radius: 18px !important;
    overflow: hidden !important;
    box-shadow: 0 10px 30px rgba(6,43,99,.055) !important;
}

.section-heading,
.section-header,
.card-header {
    padding: 20px 22px !important;
    background:
        linear-gradient(110deg,#ffffff,#f5faff) !important;
    border-bottom: 1px solid var(--sl-border) !important;
}

.section-heading h3,
.section-header h3,
.card-header h3 {
    color: var(--sl-navy) !important;
}

.section-header p,
.card-header p {
    color: var(--sl-muted) !important;
}

/* ---------- TABLES ---------- */

.table-wrapper,
.table-wrap {
    overflow-x: auto !important;
    background: #ffffff;
}

table {
    width: 100% !important;
    border-collapse: collapse !important;
}

th {
    padding: 14px 16px !important;
    background: #f4f9fe !important;
    color: #55708a !important;
    border-bottom: 1px solid var(--sl-border) !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: .45px !important;
    white-space: nowrap;
}

td {
    padding: 15px 16px !important;
    color: #29435c !important;
    border-bottom: 1px solid #edf2f7 !important;
    vertical-align: middle !important;
}

tbody tr {
    transition: background .15s ease;
}

tbody tr:hover {
    background: #f8fbff !important;
}

tbody tr:last-child td {
    border-bottom: 0 !important;
}

.student-name,
.name {
    color: var(--sl-navy) !important;
    font-weight: 750 !important;
}

.email,
.student-email,
.unit-name,
.small,
.muted {
    color: var(--sl-muted) !important;
}

/* ---------- BUTTONS ---------- */

.view-button,
.view-progress-btn,
.review,
.btn:not(.secondary) {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 38px !important;
    padding: 9px 14px !important;

    background:
        linear-gradient(
            120deg,
            var(--sl-deep),
            var(--sl-blue)
        ) !important;

    color: #ffffff !important;
    border: 0 !important;
    border-radius: 10px !important;
    text-decoration: none !important;
    font-size: 12px !important;
    font-weight: 750 !important;

    box-shadow: 0 5px 14px rgba(8,123,234,.16);

    transition:
        transform .16s ease,
        box-shadow .16s ease !important;
}

.view-button:hover,
.view-progress-btn:hover,
.review:hover,
.btn:not(.secondary):hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(8,123,234,.24);
}

.btn.secondary {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 38px !important;
    padding: 9px 14px !important;
    background: var(--sl-pale) !important;
    color: var(--sl-deep) !important;
    border: 1px solid #cce3f8 !important;
    border-radius: 10px !important;
    text-decoration: none !important;
    font-weight: 750 !important;
}

/* ---------- BADGES ---------- */

.badge,
.status,
.logbook-badge {
    border-radius: 999px !important;
    padding: 6px 10px !important;
    font-size: 10px !important;
    font-weight: 800 !important;
    letter-spacing: .25px;
}

.active,
.badge-active,
.logbook-required {
    background: #e5f3ff !important;
    color: #0865c1 !important;
}

.badge-completed,
.badge-clear {
    background: #e7f8ef !important;
    color: #157347 !important;
}

.badge-pending,
.pending {
    background: #fff4d8 !important;
    color: #8a6200 !important;
}

.badge-none,
.other,
.logbook-not-required {
    background: #f0f3f6 !important;
    color: #687887 !important;
}

.clinical {
    background: #e6f4ff !important;
    color: #075da8 !important;
}

.attendance {
    background: #f1ebff !important;
    color: #6843a3 !important;
}

/* ---------- PROGRESS ---------- */

.progress-bar,
.progress-track {
    background: #e5edf5 !important;
    border-radius: 999px !important;
    overflow: hidden !important;
}

.progress-fill {
    background:
        linear-gradient(
            90deg,
            var(--sl-deep),
            var(--sl-blue),
            var(--sl-cyan)
        ) !important;

    border-radius: 999px !important;
}

/* ---------- VERIFICATION NOTICE ---------- */

.notice {
    margin: 20px 0 24px !important;
    padding: 16px 18px !important;

    background:
        linear-gradient(
            110deg,
            #eef8ff,
            #f8fcff
        ) !important;

    border: 1px solid #cfe7fa !important;
    border-left: 4px solid var(--sl-blue) !important;
    border-radius: 12px !important;

    color: #365d7d !important;
    line-height: 1.6 !important;
}

/* ---------- ALERTS ---------- */

.ok,
.alert.success {
    background: #eaf8f0 !important;
    color: #17603a !important;
    border: 1px solid #c6ead5 !important;
    border-radius: 11px !important;
}

.err,
.alert.error {
    background: #fff1f1 !important;
    color: #9b2c2c !important;
    border: 1px solid #f0caca !important;
    border-radius: 11px !important;
}

/* ---------- EMPTY STATES ---------- */

.empty,
.empty-state {
    padding: 50px 25px !important;
    text-align: center !important;
    background: #ffffff !important;
}

.empty h3,
.empty h4,
.empty-state h3,
.empty-state h4 {
    color: var(--sl-navy) !important;
}

.empty p,
.empty-state p {
    color: var(--sl-muted) !important;
}

/* ---------- FORM CONTROLS ---------- */

input,
select,
textarea {
    border: 1px solid #cad9e7 !important;
    border-radius: 10px !important;
    background: #ffffff !important;
    color: var(--sl-text) !important;
    outline: none !important;
}

input:focus,
select:focus,
textarea:focus {
    border-color: var(--sl-blue) !important;
    box-shadow: 0 0 0 3px rgba(8,123,234,.10) !important;
}

/* ---------- RESPONSIVE ---------- */

@media (max-width: 900px) {

    .content,
    .wrap {
        width: calc(100% - 30px) !important;
        padding-top: 24px !important;
    }

    .summary,
    .summary-grid {
        grid-template-columns: repeat(2,1fr) !important;
    }

    .unit-grid {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 600px) {

    .content,
    .wrap {
        width: calc(100% - 24px) !important;
        padding-top: 20px !important;
    }

    .summary,
    .summary-grid {
        grid-template-columns: 1fr !important;
    }

    .summary-card {
        min-height: 105px !important;
    }

    .page-heading h3,
    .heading h1 {
        font-size: 27px !important;
    }

    .wrap > div[style*="justify-content:space-between"] {
        align-items: flex-start !important;
        flex-direction: column !important;
    }

    td,
    th {
        padding: 13px 12px !important;
    }
}

</style>