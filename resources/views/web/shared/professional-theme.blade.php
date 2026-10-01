@php
    /*
     * SmartLog Official Logo
     * ICT Admin can replace this from Appearance & Images.
     */
    $smartlogLogoDirectory =
        public_path('images/smartlog/custom');

    $smartlogLogoPointer =
        $smartlogLogoDirectory .
        DIRECTORY_SEPARATOR .
        'smartlog-logo.txt';

    $smartlogOfficialLogo = null;

    if (file_exists($smartlogLogoPointer)) {

        $smartlogLogoFilename =
            trim(file_get_contents($smartlogLogoPointer));

        $smartlogLogoFullPath =
            $smartlogLogoDirectory .
            DIRECTORY_SEPARATOR .
            $smartlogLogoFilename;

        if (
            $smartlogLogoFilename !== '' &&
            file_exists($smartlogLogoFullPath)
        ) {
            $smartlogOfficialLogo =
                asset(
                    'images/smartlog/custom/' .
                    $smartlogLogoFilename
                ) .
                '?v=' .
                filemtime($smartlogLogoFullPath);
        }
    }

    /*
     * Compatibility if a PNG exists without pointer file.
     */
    if (!$smartlogOfficialLogo) {

        $smartlogDirectLogo =
            $smartlogLogoDirectory .
            DIRECTORY_SEPARATOR .
            'smartlog-logo.png';

        if (file_exists($smartlogDirectLogo)) {
            $smartlogOfficialLogo =
                asset(
                    'images/smartlog/custom/smartlog-logo.png'
                ) .
                '?v=' .
                filemtime($smartlogDirectLogo);
        }
    }
@endphp
<style id="smartlog-bsp-theme">
:root {
    --sl-green: #006b45;
    --sl-green-2: #007a4d;
    --sl-deep: #004d35;
    --sl-mint: #e9f5ef;
    --sl-bg: #f5f7f6;
    --sl-text: #15362b;
    --sl-muted: #667970;
    --sl-border: #dce6e1;
    --sl-gold: #d5aa3b;
    --sl-shadow: 0 8px 24px rgba(22,55,42,.08);
}

* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    margin: 0 !important;
    padding-top: 72px !important;
    background: var(--sl-bg) !important;
    color: var(--sl-text) !important;
    font-family: Arial, Helvetica, sans-serif !important;
    -webkit-font-smoothing: antialiased;
}

/* Hide the old portal navigation.
   It remains in the HTML so SmartLog can reuse its real links/forms. */
.sidebar,
.side,
.lecturer-sidebar {
    display: none !important;
}

.main {
    margin-left: 0 !important;
    width: 100% !important;
    min-height: calc(100vh - 72px) !important;
    background: var(--sl-bg) !important;
}

.topbar,
.top {
    display: none !important;
}

.content {
    width: min(1240px, calc(100% - 40px)) !important;
    margin: 0 auto !important;
    padding: 34px 0 50px !important;
}

/* =========================================================
   SMARTLOG TOP HEADER
   ========================================================= */

.smartlog-header {
    position: fixed;
    inset: 0 0 auto;
    height: 72px;
    z-index: 5000;

    display: flex;
    align-items: center;

    background: linear-gradient(90deg, var(--sl-deep), var(--sl-green-2));
    color: #fff;

    box-shadow: 0 2px 12px rgba(0,0,0,.15);
}

.smartlog-header-inner {
    width: min(1320px, calc(100% - 36px));
    margin: auto;

    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.smartlog-brand {
    display: flex;
    align-items: center;
    gap: 12px;

    color: #fff;
    text-decoration: none;
}

.smartlog-mark {
    width: 42px;
    height: 42px;

    display: grid;
    place-items: center;

    border: 2px solid rgba(255,255,255,.82);
    border-radius: 50%;

    background: rgba(255,255,255,.08);

    font-size: 18px;
    font-weight: 900;
}

.smartlog-brand strong {
    display: block;
    font-size: 21px;
    line-height: 1;
}

.smartlog-brand small {
    display: block;
    margin-top: 5px;

    font-size: 10px;
    letter-spacing: .2px;

    opacity: .86;
}

.smartlog-head-actions {
    display: flex;
    align-items: center;
    gap: 16px;
}

.smartlog-user {
    max-width: 300px;

    text-align: right;
    font-size: 12px;
    line-height: 1.35;

    color: rgba(255,255,255,.86);
}

.smartlog-user b {
    display: block;
    margin-bottom: 2px;

    color: #fff;
    font-size: 13px;
}

.smartlog-header-logout-form {
    margin: 0 !important;
    padding: 0 !important;
}

.smartlog-header-logout {
    height: 40px;
    padding: 0 16px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 1px solid rgba(255,255,255,.45) !important;
    border-radius: 8px !important;

    background: rgba(255,255,255,.08) !important;
    color: #fff !important;

    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 12px !important;
    font-weight: 700 !important;

    cursor: pointer;

    transition:
        background .16s ease,
        border-color .16s ease,
        transform .16s ease;
}

.smartlog-header-logout:hover {
    background: #fff !important;
    color: #005b3d !important;
    border-color: #fff !important;
}

.smartlog-header-logout:active {
    transform: scale(.97);
}
.smartlog-menu-button {
    width: 44px;
    height: 44px;

    display: grid;
    place-items: center;

    border: 1px solid rgba(255,255,255,.38);
    border-radius: 8px;

    background: rgba(255,255,255,.08);
    color: #fff;

    font-size: 25px;
    line-height: 1;

    cursor: pointer;

    transition:
        background .16s ease,
        transform .16s ease;
}

.smartlog-menu-button:hover {
    background: rgba(255,255,255,.16);
}

.smartlog-menu-button:active {
    transform: scale(.97);
}

/* =========================================================
   BSP-STYLE DROP-DOWN NAVIGATION
   ========================================================= */

.smartlog-drawer {
    position: fixed;

    left: 0;
    right: 0;
    top: 72px;

    z-index: 4900;

    padding: 28px 0;

    background: #fff;

    border-bottom: 1px solid var(--sl-border);

    box-shadow: 0 18px 36px rgba(0,0,0,.16);

    transform: translateY(-120%);
    opacity: 0;
    visibility: hidden;

    transition:
        transform .22s ease,
        opacity .18s ease,
        visibility .18s;
}

.smartlog-drawer.open {
    transform: translateY(0);
    opacity: 1;
    visibility: visible;
}

.smartlog-drawer-inner {
    width: min(1240px, calc(100% - 40px));
    margin: auto;
}

.smartlog-menu-title {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;

    padding-bottom: 14px;
    margin-bottom: 16px;

    border-bottom: 1px solid #e7eeea;
}

.smartlog-menu-title strong {
    font-size: 22px;
    color: var(--sl-text);
}

.smartlog-menu-title span {
    color: var(--sl-muted);
    font-size: 12px;
}

.smartlog-menu-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));

    gap: 8px 28px;
}

.smartlog-menu-grid a {
    display: flex;
    align-items: center;
    justify-content: space-between;

    min-height: 48px;

    padding: 13px 12px;

    border-bottom: 1px solid #edf2ef;

    color: var(--sl-text);

    font-size: 14px;
    font-weight: 700;

    text-decoration: none;

    transition:
        color .15s ease,
        background .15s ease;
}

.smartlog-menu-grid a::after {
    content: '›';

    color: #8aa096;

    font-size: 20px;
    font-weight: 400;
}

.smartlog-menu-grid a:hover,
.smartlog-menu-grid a.active {
    color: var(--sl-green);
    background: #f4faf7;
}

.smartlog-menu-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;

    padding-top: 20px;
    margin-top: 8px;

    border-top: 1px solid #edf2ef;
}

.smartlog-menu-account {
    color: var(--sl-muted);
    font-size: 12px;
    line-height: 1.45;
}

.smartlog-menu-account strong {
    display: block;

    color: var(--sl-text);
    font-size: 13px;
}

.smartlog-menu-footer form {
    margin: 0;
}

.smartlog-menu-footer button,
.smartlog-logout-button {
    min-width: 110px;

    padding: 10px 18px;

    border: 1px solid #bfd3c9 !important;
    border-radius: 7px !important;

    background: #fff !important;
    color: var(--sl-deep) !important;

    font: inherit;
    font-size: 13px !important;
    font-weight: 700 !important;

    cursor: pointer;
}

.smartlog-menu-footer button:hover,
.smartlog-logout-button:hover {
    background: #f2f8f5 !important;
    border-color: var(--sl-green) !important;
}

/* Darkened page behind open navigation */

.smartlog-overlay {
    position: fixed;
    inset: 72px 0 0;

    z-index: 4800;

    display: none;

    background: rgba(4,25,17,.30);
}

.smartlog-overlay.show {
    display: block;
}

/* =========================================================
   DASHBOARD / CONTENT
   ========================================================= */

.card,
.panel,
.section,
.table-wrap {
    background: #fff !important;

    border: 1px solid var(--sl-border) !important;
    border-radius: 10px !important;

    box-shadow: var(--sl-shadow) !important;
}

/* Lecturer clinical hero */

.welcome {
    position: relative !important;

    min-height: 205px !important;

    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;

    overflow: hidden !important;

    padding: 34px !important;

    border-radius: 12px !important;

    background:
        linear-gradient(
            90deg,
            rgba(0,66,45,.96),
            rgba(0,92,61,.72)
        ),
        url('/images/smartlog/clinical-team.jpg')
        center 42% / cover no-repeat !important;

    color: #fff !important;

    box-shadow: var(--sl-shadow);
}

.welcome::after {
    content: '';

    position: absolute;

    left: 0;
    right: 0;
    bottom: 0;

    height: 4px;

    background: var(--sl-gold);
}

.welcome > * {
    position: relative;
    z-index: 1;
}

.welcome h3 {
    color: #fff !important;
}

.welcome p {
    max-width: 690px !important;

    color: #eefaf5 !important;

    line-height: 1.65 !important;
}

/* HOD and ICT Admin photographic banner */

body.smartlog-hod .content::before,
body.smartlog-admin .content::before {
    content: '';

    display: block;

    height: 150px;

    margin-bottom: 22px;

    border-radius: 12px;

    background:
        linear-gradient(
            90deg,
            rgba(0,72,48,.88),
            rgba(0,86,57,.42)
        ),
        url('/images/smartlog/campus.jpg')
        center 48% / cover no-repeat;

    box-shadow: var(--sl-shadow);
}

/* Cards */

.cards {
    gap: 18px !important;
}

.card {
    transition:
        transform .16s ease,
        box-shadow .16s ease,
        border-color .16s ease;
}

.card:hover {
    border-color: #bfd3c9 !important;
    box-shadow: 0 10px 26px rgba(22,55,42,.09) !important;
}

/* Quick functions */

.feature,
.quick-link {
    background: #fff !important;

    border: 1px solid var(--sl-border) !important;
    border-radius: 9px !important;

    transition:
        transform .16s ease,
        box-shadow .16s ease,
        border-color .16s ease;
}

.feature:hover,
.quick-link:hover {
    transform: translateY(-2px);

    border-color: #a9c8b8 !important;

    box-shadow: 0 8px 18px rgba(20,60,43,.08) !important;
}

/* =========================================================
   BUTTONS
   ========================================================= */

.btn,
.logout-button {
    border: 1px solid var(--sl-green) !important;
    border-radius: 7px !important;

    background: var(--sl-green) !important;
    color: #fff !important;

    font-weight: 700 !important;
}

.btn:hover,
.logout-button:hover {
    background: var(--sl-deep) !important;
}

.btn.secondary,
.btn.gray {
    background: #fff !important;
    color: var(--sl-green) !important;

    border-color: #bdd1c7 !important;
}

/* =========================================================
   FORMS
   ========================================================= */

input,
select,
textarea {
    font: inherit !important;

    border: 1px solid #c9d7d0 !important;
    border-radius: 7px !important;

    background: #fff !important;
}

input:focus,
select:focus,
textarea:focus {
    outline: 0;

    border-color: var(--sl-green) !important;

    box-shadow: 0 0 0 3px rgba(0,107,69,.10) !important;
}

/* =========================================================
   TABLES
   ========================================================= */

.table-wrap {
    overflow-x: auto !important;
}

th {
    background: #f6f9f7 !important;
    color: #4d6258 !important;
}

tbody tr {
    transition: background .12s ease;
}

tbody tr:hover {
    background: #fafcfb;
}

/* =========================================================
   PROGRESS
   ========================================================= */

.progress {
    background: #e5ece8 !important;
}

.bar {
    background: var(--sl-green) !important;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 900px) {

    .smartlog-menu-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 760px) {

    body {
        padding-top: 64px !important;
    }

    .main {
        min-height: calc(100vh - 64px) !important;
    }

    .smartlog-header {
        height: 64px;
    }

    .smartlog-header-inner {
        width: calc(100% - 24px);
    }

    .smartlog-mark {
        width: 36px;
        height: 36px;

        font-size: 15px;
    }

    .smartlog-brand strong {
        font-size: 18px;
    }

    .smartlog-brand small {
        margin-top: 4px;
        font-size: 9px;
    }

    .smartlog-user {
        display: none;
    }

    .smartlog-head-actions {
        gap: 8px;
    }

    .smartlog-header-logout {
        height: 38px;
        padding: 0 11px;

        font-size: 11px !important;
    }

    .smartlog-header-logout-form {
    margin: 0 !important;
    padding: 0 !important;
}

.smartlog-header-logout {
    height: 40px;
    padding: 0 16px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 1px solid rgba(255,255,255,.45) !important;
    border-radius: 8px !important;

    background: rgba(255,255,255,.08) !important;
    color: #fff !important;

    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 12px !important;
    font-weight: 700 !important;

    cursor: pointer;

    transition:
        background .16s ease,
        border-color .16s ease,
        transform .16s ease;
}

.smartlog-header-logout:hover {
    background: #fff !important;
    color: #005b3d !important;
    border-color: #fff !important;
}

.smartlog-header-logout:active {
    transform: scale(.97);
}
.smartlog-menu-button {
        width: 40px;
        height: 40px;
    }

    .smartlog-drawer {
        top: 64px;

        max-height: calc(100vh - 64px);

        overflow-y: auto;

        padding: 18px 0;
    }

    .smartlog-overlay {
        inset: 64px 0 0;
    }

    .smartlog-drawer-inner {
        width: calc(100% - 28px);
    }

    .smartlog-menu-title {
        align-items: flex-start;
        flex-direction: column;

        gap: 5px;
    }

    .smartlog-menu-title strong {
        font-size: 20px;
    }

    .smartlog-menu-grid {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .smartlog-menu-grid a {
        min-height: 50px;
    }

    .smartlog-menu-footer {
        align-items: stretch;
        flex-direction: column;
    }

    .smartlog-menu-footer form,
    .smartlog-menu-footer button {
        width: 100%;
    }

    .content {
        width: calc(100% - 28px) !important;

        padding: 20px 0 36px !important;
    }

    .cards,
    .grid,
    .grid2,
    .feature-grid,
    .form-grid,
    .filters {
        grid-template-columns: 1fr !important;
    }

    .welcome {
        min-height: 230px !important;

        padding: 24px !important;

        background-position: 62% center !important;
    }

    .card,
    .panel,
    .section {
        padding: 16px !important;
    }

    body.smartlog-hod .content::before,
    body.smartlog-admin .content::before {
        height: 115px;
        margin-bottom: 18px;
    }
}

@media (max-width: 420px) {

    .smartlog-brand small {
        max-width: 150px;
    }

    .welcome {
        min-height: 245px !important;
    }
}

/* SmartLog: hide duplicate logout inside navigation drawer.
   Logout remains available in the green top header. */
.smartlog-drawer .smartlog-logout-area {
    display: none !important;
}

.smartlog-drawer .smartlog-menu-footer {
    justify-content: flex-start !important;
}

</style>


<script id="smartlog-bsp-nav">
document.addEventListener('DOMContentLoaded', function () {

    const path = window.location.pathname;

    /* ---------------------------------------------------------
       Detect role
       --------------------------------------------------------- */

    if (path.includes('/hod/')) {
        document.body.classList.add('smartlog-hod');
    }

    if (path.includes('/admin/')) {
        document.body.classList.add('smartlog-admin');
    }

    if (path.includes('/lecturer/')) {
        document.body.classList.add('smartlog-lecturer');
    }

    /* ---------------------------------------------------------
       Find the existing navigation.
       We reuse the real Blade-generated links instead of
       hard-coding routes in JavaScript.
       --------------------------------------------------------- */

    const oldSide = document.querySelector(
        '.sidebar, .side, .lecturer-sidebar'
    );

    if (!oldSide) {
        return;
    }

    const oldNav = oldSide.querySelector('nav');

    /* IMPORTANT:
       Find the actual Laravel POST logout form anywhere on page.
       Lecturer dashboard stores it in the old topbar while
       HOD/Admin may store it elsewhere.
    */
    const oldLogout =
        document.querySelector('form[action*="/logout"]') ||
        oldSide.querySelector('form');

    /* ---------------------------------------------------------
       Read account information before old topbar/sidebar is hidden
       --------------------------------------------------------- */

    const oldTop = document.querySelector('.topbar, .top');

    let userText = 'SmartLog User';

    if (oldTop) {
        const candidate = oldTop.querySelector(
            '.user-area, .user-info, .user, .muted'
        );

        if (candidate && candidate.textContent.trim()) {
            userText = candidate.textContent
                .replace(/Logout/gi, '')
                .trim()
                .replace(/\s+/g, ' ');
        }
    }

    if (userText === 'SmartLog User') {

        const sidebarUser = oldSide.querySelector('.sidebar-user');

        if (sidebarUser) {
            userText = sidebarUser.textContent
                .replace(/Logout/gi, '')
                .trim()
                .replace(/\s+/g, ' ');
        }
    }

    let role = 'LECTURER';

    if (path.includes('/hod/')) {
        role = 'HOD';
    } else if (path.includes('/admin/')) {
        role = 'ICT ADMIN';
    }

    /* ---------------------------------------------------------
       Create SmartLog header
       --------------------------------------------------------- */

    const header = document.createElement('header');

    header.className = 'smartlog-header';

    header.innerHTML = `
        <div class="smartlog-header-inner">

            <a class="smartlog-brand" href="#">
                <span class="smartlog-mark">
                    @if($smartlogOfficialLogo)
                        <img
                            src="{{ $smartlogOfficialLogo }}"
                            alt="SmartLog"
                            class="smartlog-official-logo"
                        >
                    @else
                        <span class="smartlog-logo-fallback">SL</span>
                    @endif
                </span>

                <span>
                    <strong>SmartLog</strong>
                    <small>DWU Clinical Logbook System</small>
                </span>
            </a>

            <div class="smartlog-head-actions">

                <div class="smartlog-user">
                    <b>${role}</b>
                    ${escapeHtml(userText)}
                </div>

                <div class="smartlog-header-logout-area"></div>

                <button
                    class="smartlog-menu-button"
                    type="button"
                    aria-label="Open navigation"
                    aria-expanded="false"
                    title="Menu"
                >☰</button>

            </div>

        </div>
    `;

    /* ---------------------------------------------------------
       Add the real Laravel Logout form to the green header
       --------------------------------------------------------- */

    const headerLogoutArea =
        header.querySelector('.smartlog-header-logout-area');

    if (oldLogout && headerLogoutArea) {

        const headerLogoutForm =
            oldLogout.cloneNode(true);

        headerLogoutForm.className =
            'smartlog-header-logout-form';

        headerLogoutForm.removeAttribute('style');

        const headerLogoutButton =
            headerLogoutForm.querySelector('button');

        if (headerLogoutButton) {

            headerLogoutButton.textContent =
                'Logout';

            headerLogoutButton.className =
                'smartlog-header-logout';

            headerLogoutButton.type =
                'submit';
        }

        headerLogoutArea.appendChild(
            headerLogoutForm
        );
    }

    /* ---------------------------------------------------------
       Create navigation drawer
       --------------------------------------------------------- */

    const drawer = document.createElement('section');

    drawer.className = 'smartlog-drawer';

    drawer.setAttribute('aria-hidden', 'true');

    drawer.innerHTML = `
        <div class="smartlog-drawer-inner">

            <div class="smartlog-menu-title">
                <strong>Navigation</strong>
                <span>${role} Portal</span>
            </div>

            <div class="smartlog-menu-grid"></div>

            <div class="smartlog-menu-footer">

                <div class="smartlog-menu-account">
                    <strong>${role}</strong>
                    ${escapeHtml(userText)}
                </div>

                <div class="smartlog-logout-area"></div>

            </div>

        </div>
    `;

    /* ---------------------------------------------------------
       Copy existing role-specific links
       --------------------------------------------------------- */

    const grid = drawer.querySelector('.smartlog-menu-grid');

    if (oldNav) {

        oldNav.querySelectorAll('a').forEach(function (link) {

            const clone = link.cloneNode(true);

            grid.appendChild(clone);
        });
    }

    /* ---------------------------------------------------------
       Correct Laravel logout
       --------------------------------------------------------- */

    const logoutArea = drawer.querySelector(
        '.smartlog-logout-area'
    );

    if (oldLogout) {

        const logoutForm = oldLogout.cloneNode(true);

        logoutForm.removeAttribute('style');

        const logoutButton =
            logoutForm.querySelector('button');

        if (logoutButton) {

            logoutButton.textContent = 'Logout';

            logoutButton.className =
                'smartlog-logout-button';

            logoutButton.type = 'submit';
        }

        logoutArea.appendChild(logoutForm);

    } else {

        /* We deliberately do not create a fake GET logout link.
           Laravel logout is POST-only. */

        console.warn(
            'SmartLog: Laravel logout form was not found.'
        );
    }

    /* ---------------------------------------------------------
       Overlay
       --------------------------------------------------------- */

    const overlay = document.createElement('div');

    overlay.className = 'smartlog-overlay';

    /* ---------------------------------------------------------
       Insert UI
       --------------------------------------------------------- */

    document.body.prepend(overlay);
    document.body.prepend(drawer);
    document.body.prepend(header);

    /* ---------------------------------------------------------
       Navigation behaviour
       --------------------------------------------------------- */

    const menuButton =
        header.querySelector('.smartlog-menu-button');

    function setMenu(open) {

        drawer.classList.toggle('open', open);
        overlay.classList.toggle('show', open);

        drawer.setAttribute(
            'aria-hidden',
            open ? 'false' : 'true'
        );

        menuButton.textContent =
            open ? '×' : '☰';

        menuButton.setAttribute(
            'aria-expanded',
            open ? 'true' : 'false'
        );

        menuButton.setAttribute(
            'aria-label',
            open ? 'Close navigation' : 'Open navigation'
        );

        document.body.style.overflow =
            open ? 'hidden' : '';
    }

    menuButton.addEventListener(
        'click',
        function () {

            setMenu(
                !drawer.classList.contains('open')
            );
        }
    );

    overlay.addEventListener(
        'click',
        function () {
            setMenu(false);
        }
    );

    drawer.querySelectorAll(
        '.smartlog-menu-grid a'
    ).forEach(function (link) {

        link.addEventListener(
            'click',
            function () {
                setMenu(false);
            }
        );
    });

    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key === 'Escape') {
                setMenu(false);
            }
        }
    );

    /* ---------------------------------------------------------
       Escape account text inserted into HTML
       --------------------------------------------------------- */

    function escapeHtml(value) {

        const div =
            document.createElement('div');

        div.textContent =
            value || '';

        return div.innerHTML;
    }
});
</script>








<style id="smartlog-whatsapp-logo">

/* ==========================================================
   SMARTLOG OFFICIAL LOGO
   Any uploaded image is visually cropped into a circle.
   ========================================================== */


/* ---------- PUBLIC LANDING HEADER ---------- */

.logo:has(img.smartlog-managed-logo) {
    width: 46px !important;
    height: 46px !important;

    min-width: 46px !important;
    max-width: 46px !important;

    min-height: 46px !important;
    max-height: 46px !important;

    flex: 0 0 46px !important;

    display: inline-block !important;

    padding: 0 !important;
    margin: 0 !important;

    border: 2px solid rgba(255,255,255,.92) !important;
    border-radius: 50% !important;

    background: #ffffff !important;

    overflow: hidden !important;

    box-sizing: border-box !important;

    box-shadow:
        0 2px 8px rgba(0,0,0,.16) !important;
}


/* ---------- LOGIN POPUP ---------- */

.modal-logo:has(img.smartlog-managed-logo) {
    width: 46px !important;
    height: 46px !important;

    min-width: 46px !important;
    max-width: 46px !important;

    min-height: 46px !important;
    max-height: 46px !important;

    flex: 0 0 46px !important;

    display: inline-block !important;

    padding: 0 !important;
    margin: 0 !important;

    border: 2px solid #e1ebe6 !important;
    border-radius: 50% !important;

    background: #ffffff !important;

    overflow: hidden !important;

    box-sizing: border-box !important;

    box-shadow:
        0 2px 8px rgba(0,0,0,.10) !important;
}


/* ---------- PUBLIC + POPUP ACTUAL IMAGE ---------- */

.logo > img.smartlog-managed-logo,
.modal-logo > img.smartlog-managed-logo {

    position: static !important;

    display: block !important;

    width: 100% !important;
    height: 100% !important;

    min-width: 100% !important;
    min-height: 100% !important;

    max-width: none !important;
    max-height: none !important;

    padding: 0 !important;
    margin: 0 !important;

    border: 0 !important;
    border-radius: 50% !important;

    background: transparent !important;

    object-fit: cover !important;
    object-position: 50% 50% !important;

    aspect-ratio: 1 / 1 !important;
}


/* ---------- LOGGED-IN PORTALS ---------- */
/* Lecturer + HOD + ICT Admin */

.smartlog-mark:has(img.smartlog-official-logo) {

    width: 46px !important;
    height: 46px !important;

    min-width: 46px !important;
    max-width: 46px !important;

    min-height: 46px !important;
    max-height: 46px !important;

    flex: 0 0 46px !important;

    display: inline-block !important;

    padding: 0 !important;
    margin: 0 !important;

    border: 2px solid rgba(255,255,255,.92) !important;
    border-radius: 50% !important;

    background: #ffffff !important;

    overflow: hidden !important;

    box-sizing: border-box !important;

    box-shadow:
        0 2px 8px rgba(0,0,0,.16) !important;
}


.smartlog-mark > img.smartlog-official-logo {

    position: static !important;

    display: block !important;

    width: 100% !important;
    height: 100% !important;

    min-width: 100% !important;
    min-height: 100% !important;

    max-width: none !important;
    max-height: none !important;

    padding: 0 !important;
    margin: 0 !important;

    border: 0 !important;
    border-radius: 50% !important;

    background: transparent !important;

    object-fit: cover !important;
    object-position: 50% 50% !important;

    aspect-ratio: 1 / 1 !important;
}


/* ---------- FALLBACK SL ---------- */

.logo:not(:has(img)),
.modal-logo:not(:has(img)),
.smartlog-mark:not(:has(img)) {
    border-radius: 50% !important;
}


/* ---------- MOBILE BROWSER ---------- */

@media (max-width: 760px) {

    .logo:has(img.smartlog-managed-logo),
    .modal-logo:has(img.smartlog-managed-logo),
    .smartlog-mark:has(img.smartlog-official-logo) {

        width: 42px !important;
        height: 42px !important;

        min-width: 42px !important;
        max-width: 42px !important;

        min-height: 42px !important;
        max-height: 42px !important;

        flex-basis: 42px !important;
    }
}

</style>

<style id="smartlog-final-blue-theme">

/* ==========================================================
   SMARTLOG OFFICIAL BLUE WEB THEME
   Based on the SmartLog logo identity
   ========================================================== */

:root {
    --smartlog-navy: #062b63;
    --smartlog-deep-blue: #06499c;
    --smartlog-blue: #087bea;
    --smartlog-cyan: #13b9ef;
    --smartlog-light-blue: #edf7ff;
}


/* ----------------------------------------------------------
   MAIN TOP HEADER
   ---------------------------------------------------------- */

.smartlog-header {
    background:
        linear-gradient(
            110deg,
            #062b63 0%,
            #06499c 55%,
            #087bea 100%
        ) !important;

    border-bottom: 0 !important;

    box-shadow:
        0 5px 18px rgba(6, 43, 99, .18) !important;
}


/* Brand text */

.smartlog-brand,
.smartlog-brand strong,
.smartlog-brand small {
    color: #ffffff !important;
}

.smartlog-brand strong {
    font-weight: 800 !important;
}

.smartlog-brand small {
    color: #dceeff !important;
}


/* ----------------------------------------------------------
   OFFICIAL CIRCULAR LOGO
   ---------------------------------------------------------- */

.smartlog-mark {
    background: #ffffff !important;

    border: 2px solid rgba(255,255,255,.92) !important;

    box-shadow:
        0 3px 10px rgba(0,0,0,.16) !important;
}


/* ----------------------------------------------------------
   LOGOUT BUTTON
   ---------------------------------------------------------- */

.smartlog-header .smartlog-logout,
.smartlog-header button[type="submit"] {

    color: #ffffff !important;

    background:
        rgba(255,255,255,.10) !important;

    border:
        1px solid rgba(255,255,255,.45) !important;

    border-radius: 10px !important;

    transition:
        background .18s ease,
        transform .18s ease !important;
}

.smartlog-header .smartlog-logout:hover,
.smartlog-header button[type="submit"]:hover {

    background:
        rgba(255,255,255,.20) !important;

    transform: translateY(-1px);
}


/* ----------------------------------------------------------
   HAMBURGER BUTTON
   ---------------------------------------------------------- */

.smartlog-menu-button,
.smartlog-menu-toggle,
.smartlog-hamburger {

    color: #ffffff !important;

    background:
        rgba(255,255,255,.10) !important;

    border:
        1px solid rgba(255,255,255,.45) !important;

    border-radius: 10px !important;
}

.smartlog-menu-button:hover,
.smartlog-menu-toggle:hover,
.smartlog-hamburger:hover {

    background:
        rgba(255,255,255,.20) !important;
}


/* Hamburger lines */

.smartlog-menu-button span,
.smartlog-menu-toggle span,
.smartlog-hamburger span {

    background: #ffffff !important;
}


/* ----------------------------------------------------------
   DRAWER / HAMBURGER NAVIGATION
   ---------------------------------------------------------- */

.smartlog-drawer {
    background: #ffffff !important;

    border-color: #dbe8f5 !important;

    box-shadow:
        0 20px 50px rgba(6,43,99,.18) !important;
}


.smartlog-drawer a {
    color: #183653 !important;

    border-radius: 10px !important;

    transition:
        background .16s ease,
        color .16s ease !important;
}


.smartlog-drawer a:hover {

    color: #06499c !important;

    background: #edf7ff !important;
}


.smartlog-drawer a.active {

    color: #ffffff !important;

    background:
        linear-gradient(
            135deg,
            #06499c,
            #087bea
        ) !important;
}


/* ----------------------------------------------------------
   DRAWER ROLE / SMALL TEXT
   ---------------------------------------------------------- */

.smartlog-drawer small,
.smartlog-drawer .role,
.smartlog-drawer .smartlog-role {

    color: #6b7e91 !important;
}


/* ----------------------------------------------------------
   PAGE BACKGROUND
   ---------------------------------------------------------- */

body {

    background:
        #f5f8fc !important;
}


/* ----------------------------------------------------------
   GENERAL SMARTLOG LINKS
   ---------------------------------------------------------- */

a {
    text-decoration-color:
        rgba(8,123,234,.35);
}


/* ----------------------------------------------------------
   RESPONSIVE
   ---------------------------------------------------------- */

@media (max-width: 760px) {

    .smartlog-header {

        background:
            linear-gradient(
                110deg,
                #062b63,
                #075fbe
            ) !important;
    }
}

</style>
