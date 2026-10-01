@php
    /*
     * ICT Admin managed SmartLog logo.
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
     * Direct PNG fallback.
     */
    if (!$smartlogOfficialLogo) {

        $directLogo =
            $smartlogLogoDirectory .
            DIRECTORY_SEPARATOR .
            'smartlog-logo.png';

        if (file_exists($directLogo)) {

            $smartlogOfficialLogo =
                asset(
                    'images/smartlog/custom/smartlog-logo.png'
                ) .
                '?v=' .
                filemtime($directLogo);
        }
    }
@endphp
@php
    $smartlogHeroDirectory =
        public_path('images/smartlog/custom');

    $smartlogHeroPointer =
        $smartlogHeroDirectory .
        DIRECTORY_SEPARATOR .
        'public-hero.txt';

    $smartlogPublicHero =
        asset('images/smartlog/clinical-team.jpg');

    if (file_exists($smartlogHeroPointer)) {

        $smartlogHeroFilename =
            trim(
                file_get_contents(
                    $smartlogHeroPointer
                )
            );

        $smartlogHeroFullPath =
            $smartlogHeroDirectory .
            DIRECTORY_SEPARATOR .
            $smartlogHeroFilename;

        if (
            $smartlogHeroFilename !== '' &&
            file_exists($smartlogHeroFullPath)
        ) {
            $smartlogPublicHero =
                asset(
                    'images/smartlog/custom/' .
                    $smartlogHeroFilename
                ) .
                '?v=' .
                filemtime($smartlogHeroFullPath);
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>SmartLog | DWU Clinical Logbook System</title>

<style>
:root {
    --green: #006b45;
    --deep: #004b35;
    --green2: #078256;
    --light: #f7f6f1;
    --text: #173e31;
    --muted: #60756c;
    --border: #dce6e1;
    --gold: #d7ad3f;
}

* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    color: var(--text);
    background: #fff;
}

button,
input {
    font: inherit;
}

/* =========================================================
   HEADER
   ========================================================= */

.site-header {
    position: sticky;
    top: 0;
    z-index: 1000;

    height: 72px;

    display: flex;
    align-items: center;

    background: var(--deep);
    color: #fff;

    box-shadow: 0 2px 10px rgba(0,0,0,.12);
}

.header-inner {
    width: min(1280px, calc(100% - 44px));
    margin: auto;

    display: flex;
    align-items: center;
    gap: 34px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 11px;

    margin-right: auto;

    color: #fff;
    text-decoration: none;
}

.logo {
    width: 40px;
    height: 40px;

    display: grid;
    place-items: center;

    border: 2px solid rgba(255,255,255,.85);
    border-radius: 50%;

    font-size: 14px;
    font-weight: 900;
}

.brand strong {
    display: block;
    font-size: 20px;
    line-height: 1;
}

.brand small {
    display: block;
    margin-top: 4px;

    font-size: 9px;
    color: rgba(255,255,255,.78);
}

.desktop-nav {
    display: flex;
    align-items: center;
    gap: 29px;
}

.desktop-nav a {
    color: rgba(255,255,255,.91);
    text-decoration: none;

    font-size: 13px;
    font-weight: 500;

    transition: color .15s;
}

.desktop-nav a:hover {
    color: #fff;
}

.login-button {
    min-width: 82px;
    height: 39px;

    padding: 0 18px;

    border: 1px solid rgba(255,255,255,.55);
    border-radius: 20px;

    background: #fff;
    color: var(--deep);

    font-size: 12px;
    font-weight: 800;

    cursor: pointer;

    transition: .16s ease;
}

.login-button:hover {
    background: #eaf7f0;
}

.mobile-menu-button {
    display: none;

    width: 40px;
    height: 40px;

    border: 1px solid rgba(255,255,255,.35);
    border-radius: 7px;

    background: transparent;
    color: #fff;

    font-size: 22px;
    cursor: pointer;
}

/* =========================================================
   MOBILE MENU
   ========================================================= */

.mobile-menu {
    display: none;

    position: fixed;
    z-index: 950;

    top: 64px;
    left: 0;
    right: 0;

    padding: 14px 20px 20px;

    background: #fff;

    box-shadow: 0 12px 24px rgba(0,0,0,.16);
}

.mobile-menu.open {
    display: block;
}

.mobile-menu a {
    display: block;

    padding: 14px 4px;

    border-bottom: 1px solid #e7eeea;

    color: var(--text);
    text-decoration: none;

    font-size: 13px;
    font-weight: 700;
}

.mobile-login {
    width: 100%;
    margin-top: 14px;

    border-radius: 7px;
}

/* =========================================================
   HERO — BSP-INSPIRED SPLIT DESIGN
   ========================================================= */

.hero {
    min-height: 515px;

    display: grid;
    grid-template-columns: 1fr 1fr;

    background: var(--light);
}

.hero-copy {
    display: flex;
    align-items: center;

    padding: 65px max(50px, calc((100vw - 1280px) / 2 + 22px));
}

.hero-copy-inner {
    max-width: 560px;
}

.hero-label {
    display: flex;
    align-items: center;
    gap: 10px;

    margin-bottom: 19px;

    color: var(--green);

    font-size: 11px;
    font-weight: 800;

    letter-spacing: .7px;
    text-transform: uppercase;
}

.hero-label::before {
    content: '';

    width: 28px;
    height: 2px;

    background: var(--gold);
}

.hero h1 {
    margin: 0 0 22px;

    color: var(--deep);

    font-family: Georgia, "Times New Roman", serif;

    font-size: clamp(42px, 4.2vw, 65px);
    font-weight: 500;

    line-height: 1.02;
    letter-spacing: -1.8px;
}

.hero h1 em {
    display: block;

    color: var(--green);

    font-weight: 400;
}

.hero-description {
    max-width: 520px;

    margin: 0 0 29px;

    color: #345b4d;

    font-size: 17px;
    line-height: 1.55;
}

.learn-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;

    min-height: 43px;

    padding: 0 20px;

    border: 0;
    border-radius: 22px;

    background: var(--green2);
    color: #fff;

    text-decoration: none;

    font-size: 12px;
    font-weight: 800;

    transition: .16s ease;
}

.learn-button:hover {
    background: var(--deep);
}

.hero-image {
    position: relative;

    min-height: 515px;

    overflow: hidden;

    background:
        url('{{ $smartlogPublicHero }}')
        center center / cover no-repeat;
}

.hero-image::before {
    content: '';

    position: absolute;
    inset: 0;

    background:
        linear-gradient(
            90deg,
            rgba(247,246,241,.08),
            transparent 30%
        );
}

/* BSP-like curved transition */

.hero-image::after {
    content: '';

    position: absolute;

    left: -62px;
    top: 0;
    bottom: 0;

    width: 105px;

    background: var(--light);

    border-radius: 0 52% 52% 0;
}

/* =========================================================
   ABOUT
   ========================================================= */

.section {
    padding: 76px 22px;
}

.section-inner {
    width: min(1180px, 100%);
    margin: auto;
}

.section-label {
    color: var(--green);

    font-size: 11px;
    font-weight: 800;

    text-transform: uppercase;
    letter-spacing: .8px;
}

.section h2 {
    max-width: 700px;

    margin: 10px 0 18px;

    color: var(--deep);

    font-family: Georgia, "Times New Roman", serif;

    font-size: 39px;
    font-weight: 500;
}

.section-intro {
    max-width: 760px;

    margin: 0;

    color: var(--muted);

    font-size: 15px;
    line-height: 1.75;
}

/* =========================================================
   HOW IT WORKS
   ========================================================= */

.how {
    background: #f4f8f6;
}

.steps {
    display: grid;
    grid-template-columns: repeat(4, 1fr);

    gap: 18px;

    margin-top: 36px;
}

.step {
    padding: 25px 22px;

    border: 1px solid var(--border);
    border-radius: 10px;

    background: #fff;
}

.step-number {
    width: 35px;
    height: 35px;

    display: grid;
    place-items: center;

    margin-bottom: 17px;

    border-radius: 50%;

    background: var(--green);
    color: #fff;

    font-size: 12px;
    font-weight: 800;
}

.step h3 {
    margin: 0 0 9px;

    color: var(--deep);
    font-size: 15px;
}

.step p {
    margin: 0;

    color: var(--muted);

    font-size: 12px;
    line-height: 1.6;
}

/* =========================================================
   DEPARTMENTS
   ========================================================= */

.departments {
    display: grid;
    grid-template-columns: repeat(3, 1fr);

    gap: 18px;

    margin-top: 34px;
}

.department {
    padding: 26px 23px;

    border: 1px solid var(--border);
    border-radius: 10px;

    background: #fff;

    box-shadow: 0 7px 20px rgba(23,62,49,.05);
}

.department-line {
    width: 34px;
    height: 3px;

    margin-bottom: 17px;

    background: var(--green);
}

.department h3 {
    margin: 0 0 9px;

    color: var(--deep);
    font-size: 16px;
}

.department p {
    margin: 0;

    color: var(--muted);

    font-size: 12px;
    line-height: 1.6;
}

/* =========================================================
   HELP
   ========================================================= */

.help {
    background: var(--deep);
    color: #fff;
}

.help .section-label {
    color: #7ce1b5;
}

.help h2 {
    color: #fff;
}

.help .section-intro {
    color: rgba(255,255,255,.78);
}

/* =========================================================
   FOOTER
   ========================================================= */

.footer {
    padding: 22px;

    background: #003b2a;
    color: rgba(255,255,255,.65);

    text-align: center;

    font-size: 10px;
}

/* =========================================================
   LOGIN MODAL
   ========================================================= */

.modal-backdrop {
    position: fixed;
    inset: 0;

    z-index: 9000;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 20px;

    background: rgba(0,45,30,.20);

    opacity: 0;
    visibility: hidden;

    transition:
        opacity .18s ease,
        visibility .18s ease;
}

.modal-backdrop.open {
    opacity: 1;
    visibility: visible;
}

.login-modal {
    width: min(100%, 440px);

    overflow: hidden;

    border-radius: 12px;

    background: #fff;

    box-shadow: 0 25px 70px rgba(0,0,0,.30);

    transform: translateY(14px);

    transition: transform .18s ease;
}

.modal-backdrop.open .login-modal {
    transform: translateY(0);
}

.modal-top {
    position: relative;

    padding: 23px 25px;

    background:
        linear-gradient(
            120deg,
            var(--deep),
            var(--green2)
        );

    color: #fff;
}

.modal-brand {
    display: flex;
    align-items: center;
    gap: 9px;

    margin-bottom: 19px;
}

.modal-logo {
    width: 34px;
    height: 34px;

    display: grid;
    place-items: center;

    border: 1.5px solid rgba(255,255,255,.85);
    border-radius: 50%;

    font-size: 12px;
    font-weight: 900;
}

.modal-brand strong {
    display: block;
    font-size: 15px;
}

.modal-brand small {
    display: block;

    margin-top: 3px;

    font-size: 8px;

    opacity: .8;
}

.modal-top h2 {
    margin: 0 0 5px;

    font-size: 24px;
}

.modal-top p {
    margin: 0;

    color: rgba(255,255,255,.86);

    font-size: 11px;
}

.modal-close {
    position: absolute;

    top: 14px;
    right: 14px;

    width: 34px;
    height: 34px;

    border: 1px solid rgba(255,255,255,.35);
    border-radius: 6px;

    background: rgba(255,255,255,.08);
    color: #fff;

    font-size: 20px;

    cursor: pointer;
}

.modal-body {
    padding: 25px;
}

.alert {
    margin-bottom: 16px;

    padding: 11px 13px;

    border-radius: 6px;

    font-size: 11px;
    line-height: 1.5;
}

.alert-error {
    border: 1px solid #efc9c9;

    background: #fff0f0;
    color: #8a2929;
}

.alert-success {
    border: 1px solid #c7e5d5;

    background: #eaf7f0;
    color: #17613d;
}

.form-group {
    margin-bottom: 17px;
}

.form-group label {
    display: block;

    margin-bottom: 7px;

    color: #28493d;

    font-size: 11px;
    font-weight: 800;
}

.form-group input {
    width: 100%;
    height: 46px;

    padding: 0 13px;

    border: 1px solid #c8d7cf;
    border-radius: 6px;

    color: var(--text);

    outline: 0;
}

.form-group input:focus {
    border-color: var(--green);

    box-shadow: 0 0 0 3px rgba(0,107,69,.10);
}

.password-wrap {
    position: relative;
}

.password-wrap input {
    padding-right: 62px;
}

.show-password {
    position: absolute;

    right: 10px;
    top: 50%;

    transform: translateY(-50%);

    border: 0;

    background: transparent;
    color: var(--green);

    font-size: 10px;
    font-weight: 800;

    cursor: pointer;
}

.sign-in {
    width: 100%;
    height: 47px;

    border: 0;
    border-radius: 6px;

    background: var(--green);
    color: #fff;

    font-size: 12px;
    font-weight: 800;

    cursor: pointer;
}

.sign-in:hover {
    background: var(--deep);
}

.student-note {
    margin-top: 18px;

    padding: 11px 13px;

    border-left: 3px solid var(--green);

    background: #f2f7f4;

    color: #607169;

    font-size: 10px;
    line-height: 1.5;
}

.student-note strong {
    color: var(--text);
}

.modal-footer {
    margin-top: 18px;

    text-align: center;

    color: #8b9891;

    font-size: 9px;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 900px) {

    .desktop-nav,
    .header-login {
        display: none;
    }

    .mobile-menu-button {
        display: grid;
        place-items: center;
    }

    .site-header {
        height: 64px;
    }

    .header-inner {
        width: calc(100% - 28px);
    }

    .hero {
        grid-template-columns: 1fr;
    }

    .hero-copy {
        min-height: 420px;

        padding: 55px 30px;
    }

    .hero-image {
        min-height: 340px;
    }

    .hero-image::after {
        display: none;
    }

    .steps {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 620px) {

    .hero-copy {
        min-height: 390px;

        padding: 44px 22px;
    }

    .hero h1 {
        font-size: 42px;
    }

    .hero-description {
        font-size: 15px;
    }

    .hero-image {
        min-height: 280px;
    }

    .section {
        padding: 55px 20px;
    }

    .section h2 {
        font-size: 32px;
    }

    .steps,
    .departments {
        grid-template-columns: 1fr;
    }

    .modal-backdrop {
        align-items: flex-end;
        padding: 0;
    }

    .login-modal {
        width: 100%;

        max-height: 94vh;
        overflow-y: auto;

        border-radius: 14px 14px 0 0;
    }
}

/* =========================================================
   SMARTLOG HERO PHOTO - NATURAL CLARITY
   Keep the original real photograph. No AI effects.
   ========================================================= */

.hero-image {
    background-image:
        url('{{ $smartlogPublicHero }}') !important;

    background-size: cover !important;

    /* Keep the group and faces naturally positioned */
    background-position: center 42% !important;

    background-repeat: no-repeat !important;

    /* Normal browser rendering */
    image-rendering: auto;

    /* Very light photographic adjustment only */
    filter:
        contrast(1.03)
        saturate(1.02) !important;
}

/* Remove any colour/gradient layer over the actual photograph */
.hero-image::before {
    display: none !important;
}

/* Keep only the white curved BSP-style divider */
.hero-image::after {
    background: #f7f6f1 !important;
}

/* Larger screens: give the photograph enough space */
@media (min-width: 1200px) {

    .hero {
        grid-template-columns:
            49% 51% !important;
    }

    .hero-image {
        background-position:
            center 40% !important;
    }
}

/* Mobile/tablet */
@media (max-width: 900px) {

    .hero-image {
        background-position:
            center 38% !important;

        filter:
            contrast(1.02)
            saturate(1.01) !important;
    }
}

</style>
</head>

<body>

<header class="site-header">

    <div class="header-inner">

        <a href="#home" class="brand">

            <span class="logo">
    @if($smartlogOfficialLogo)
        <img
            src="{{ $smartlogOfficialLogo }}"
            alt="SmartLog Logo"
            class="smartlog-managed-logo"
        >
    @else
        SL
    @endif
</span>

            <span>
                <strong>SmartLog</strong>
                <small>DWU Clinical Logbook System</small>
            </span>

        </a>

        <nav class="desktop-nav">

            <a href="#home">Home</a>

            <a href="#about">
                About
            </a>

            <a href="#how">
                How It Works
            </a>

            <a href="#departments">
                Departments
            </a>

            <a href="#help">
                Help
            </a>

        </nav>

        <button
            type="button"
            class="login-button header-login js-open-login"
        >
            Login
        </button>

        <button
            type="button"
            class="mobile-menu-button"
            id="mobileMenuButton"
            aria-label="Open menu"
        >
            ☰
        </button>

    </div>

</header>


<div
    class="mobile-menu"
    id="mobileMenu"
>

    <a href="#home">Home</a>
    <a href="#about">About</a>
    <a href="#how">How It Works</a>
    <a href="#departments">Departments</a>
    <a href="#help">Help</a>

    <button
        type="button"
        class="login-button mobile-login js-open-login"
    >
        Login
    </button>

</div>


<!-- =====================================================
     HOME
     ===================================================== -->

<section
    class="hero"
    id="home"
>

    <div class="hero-copy">

        <div class="hero-copy-inner">

            <div class="hero-label">
                Clinical Learning
            </div>

            <h1>
                Smart Clinical
                <em>Logbook System</em>
            </h1>

            <p class="hero-description">
                Record, verify, review and monitor clinical
                progress in one secure digital system.
            </p>

            <a
                href="#about"
                class="learn-button"
            >
                Learn more
                <span>→</span>
            </a>

        </div>

    </div>


    <div
        class="hero-image"
        aria-label="DWU clinical students"
    ></div>

</section>


<!-- =====================================================
     ABOUT
     ===================================================== -->

<section
    class="section"
    id="about"
>

    <div class="section-inner">

        <div class="section-label">
            About SmartLog
        </div>

        <h2>
            Supporting the clinical learning journey.
        </h2>

        <p class="section-intro">
            SmartLog is a digital clinical logbook system
            for recording student clinical activities,
            supporting supervisor verification, lecturer
            review and academic progress monitoring.
        </p>

    </div>

</section>


<!-- =====================================================
     HOW IT WORKS
     ===================================================== -->

<section
    class="section how"
    id="how"
>

    <div class="section-inner">

        <div class="section-label">
            How It Works
        </div>

        <h2>
            One connected clinical logbook process.
        </h2>

        <div class="steps">

            <article class="step">

                <div class="step-number">1</div>

                <h3>Record</h3>

                <p>
                    Students record their clinical
                    activities using SmartLog.
                </p>

            </article>


            <article class="step">

                <div class="step-number">2</div>

                <h3>Verify</h3>

                <p>
                    Clinical supervisors verify activities
                    on the student's device.
                </p>

            </article>


            <article class="step">

                <div class="step-number">3</div>

                <h3>Review</h3>

                <p>
                    Lecturers review verification evidence
                    and approve or reject activities.
                </p>

            </article>


            <article class="step">

                <div class="step-number">4</div>

                <h3>Monitor</h3>

                <p>
                    Heads of Department monitor clinical
                    progress through read-only summaries.
                </p>

            </article>

        </div>

    </div>

</section>


<!-- =====================================================
     DEPARTMENTS
     ===================================================== -->

<section
    class="section"
    id="departments"
>

    <div class="section-inner">

        <div class="section-label">
            Departments
        </div>

        <h2>
            Clinical programs supported by SmartLog.
        </h2>

        <div class="departments">

            <article class="department">

                <div class="department-line"></div>

                <h3>
                    Medicine & Surgery
                </h3>

                <p>
                    Digital clinical logbooks supporting
                    medical students during clinical
                    learning and practical activities.
                </p>

            </article>


            <article class="department">

                <div class="department-line"></div>

                <h3>
                    Rehabilitation Sciences
                </h3>

                <p>
                    Clinical placement and practical
                    activity recording for rehabilitation
                    students.
                </p>

            </article>


            <article class="department">

                <div class="department-line"></div>

                <h3>
                    Rural Health
                </h3>

                <p>
                    Clinical activity recording designed
                    to support placements including
                    low-connectivity environments.
                </p>

            </article>

        </div>

    </div>

</section>


<!-- =====================================================
     HELP
     ===================================================== -->

<section
    class="section help"
    id="help"
>

    <div class="section-inner">

        <div class="section-label">
            SmartLog Help
        </div>

        <h2>
            Access SmartLog using your authorised account.
        </h2>

        <p class="section-intro">
            Lecturers, Heads of Department and ICT
            administrators can use the web portal.
            Students use the SmartLog mobile application
            for clinical logbook activities and offline work.
        </p>

    </div>

</section>


<footer class="footer">

    SmartLog &copy; {{ date('Y') }}
    Divine Word University

</footer>


<!-- =====================================================
     LOGIN POPUP
     ===================================================== -->

<div
    class="modal-backdrop"
    id="loginModal"
    aria-hidden="true"
>

    <section
        class="login-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="loginTitle"
    >

        <div class="modal-top">

            <button
                type="button"
                class="modal-close"
                id="closeLogin"
                aria-label="Close login"
            >
                ×
            </button>

            <div class="modal-brand">

                <span class="modal-logo">
    @if($smartlogOfficialLogo)
        <img
            src="{{ $smartlogOfficialLogo }}"
            alt="SmartLog Logo"
            class="smartlog-managed-logo"
        >
    @else
        SL
    @endif
</span>

                <span>
                    <strong>SmartLog</strong>
                    <small>
                        DWU CLINICAL LOGBOOK SYSTEM
                    </small>
                </span>

            </div>

            <h2 id="loginTitle">
                Welcome back
            </h2>

            <p>
                Sign in using your DWU ID or
                registered email address.
            </p>

        </div>


        <div class="modal-body">

            @if(session('success'))

                <div class="alert alert-success">
                    {{ session('success') }}
                </div>

            @endif


            @if($errors->any())

                <div class="alert alert-error">
                    {{ $errors->first() }}
                </div>

            @endif


            <form
                method="POST"
                action="{{ route('web.login.submit') }}"
            >

                @csrf

                <div class="form-group">

                    <label for="login">
                        DWU ID or Email
                    </label>

                    <input
                        type="text"
                        id="login"
                        name="login"
                        value="{{ old('login') }}"
                        placeholder="Enter DWU ID or email"
                        autocomplete="username"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="password-wrap">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="show-password"
                            id="togglePassword"
                        >
                            Show
                        </button>

                    </div>

                </div>


                <button
                    type="submit"
                    class="sign-in"
                >
                    Sign In
                </button>

            </form>


            <div class="student-note">

                <strong>Student access</strong>
                <br>

                Students use the SmartLog mobile
                application for clinical activities,
                supervisor verification and offline work.

            </div>


            <div class="modal-footer">

                SmartLog &copy; {{ date('Y') }}
                Divine Word University

            </div>

        </div>

    </section>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal =
        document.getElementById('loginModal');

    const closeLoginButton =
        document.getElementById('closeLogin');

    const openLoginButtons =
        document.querySelectorAll('.js-open-login');

    const loginInput =
        document.getElementById('login');

    const passwordInput =
        document.getElementById('password');

    const togglePassword =
        document.getElementById('togglePassword');

    const mobileMenu =
        document.getElementById('mobileMenu');

    const mobileMenuButton =
        document.getElementById('mobileMenuButton');


    function openLogin() {

        modal.classList.add('open');

        modal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.style.overflow =
            'hidden';

        mobileMenu.classList.remove('open');

        setTimeout(function () {

            if (loginInput) {
                loginInput.focus();
            }

        }, 120);
    }


    function closeLogin() {

        modal.classList.remove('open');

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.style.overflow =
            '';
    }


    openLoginButtons.forEach(function (button) {

        button.addEventListener(
            'click',
            openLogin
        );

    });


    closeLoginButton.addEventListener(
        'click',
        closeLogin
    );


    modal.addEventListener(
        'click',
        function (event) {

            if (event.target === modal) {
                closeLogin();
            }

        }
    );


    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key === 'Escape') {

                closeLogin();

                mobileMenu.classList.remove('open');
            }

        }
    );


    togglePassword.addEventListener(
        'click',
        function () {

            const showing =
                passwordInput.type === 'text';

            passwordInput.type =
                showing
                    ? 'password'
                    : 'text';

            togglePassword.textContent =
                showing
                    ? 'Show'
                    : 'Hide';
        }
    );


    mobileMenuButton.addEventListener(
        'click',
        function () {

            mobileMenu.classList.toggle('open');

            mobileMenuButton.textContent =
                mobileMenu.classList.contains('open')
                    ? '×'
                    : '☰';
        }
    );


    document
        .querySelectorAll('.mobile-menu a')
        .forEach(function (link) {

            link.addEventListener(
                'click',
                function () {

                    mobileMenu.classList.remove('open');

                    mobileMenuButton.textContent = '☰';
                }
            );

        });


    @if($errors->any())

        openLogin();

    @endif

});
</script>




<!-- SMARTLOG-FINAL-BLUE-START -->
<style id="smartlog-final-blue-theme">

/* ==========================================================
   SMARTLOG FINAL BRAND COLOURS
   Navy + Royal Blue + Cyan
   ========================================================== */

:root{
    --green:#087bea !important;
    --deep:#062b63 !important;
    --green2:#086fd1 !important;

    --light:#f6f9fc !important;
    --text:#17324d !important;
    --muted:#667d91 !important;
    --border:#dbe7f2 !important;

    --gold:#13b9ef !important;

    --sl-navy:#062b63;
    --sl-deep:#06499c;
    --sl-blue:#087bea;
    --sl-cyan:#13b9ef;
    --sl-pale:#edf7ff;
}


/* ==========================================================
   HEADER
   ========================================================== */

.site-header{
    background:
        linear-gradient(
            110deg,
            #05285d 0%,
            #063d83 52%,
            #087bea 100%
        ) !important;

    box-shadow:
        0 4px 18px rgba(6,43,99,.18) !important;
}

.desktop-nav a{
    color:rgba(255,255,255,.88) !important;
}

.desktop-nav a:hover{
    color:#ffffff !important;
}

.login-button{
    border-color:rgba(255,255,255,.72) !important;

    background:#ffffff !important;
    color:#062b63 !important;

    box-shadow:
        0 4px 14px rgba(0,0,0,.10) !important;
}

.login-button:hover{
    background:#eaf7ff !important;
    color:#06499c !important;
}

.mobile-menu-button{
    border-color:rgba(255,255,255,.45) !important;
}


/* ==========================================================
   MOBILE NAVIGATION
   ========================================================== */

.mobile-menu{
    border-top:3px solid #13b9ef !important;
    background:#ffffff !important;
}

.mobile-menu a{
    border-bottom-color:#e5edf5 !important;
    color:#17324d !important;
}

.mobile-menu a:hover{
    color:#087bea !important;
    background:#f5faff !important;
}


/* ==========================================================
   HERO
   ========================================================== */

.hero{
    background:
        linear-gradient(
            120deg,
            #f8fbfe 0%,
            #f2f7fc 100%
        ) !important;
}

.hero-label{
    color:#087bea !important;
}

.hero-label::before{
    background:
        linear-gradient(
            90deg,
            #087bea,
            #13b9ef
        ) !important;
}

.hero h1{
    color:#062b63 !important;
}

.hero h1 em{
    color:#087bea !important;
}

.hero-description{
    color:#405e78 !important;
}

.learn-button{
    background:
        linear-gradient(
            120deg,
            #06499c,
            #087bea
        ) !important;

    box-shadow:
        0 7px 20px rgba(8,123,234,.20) !important;
}

.learn-button:hover{
    background:
        linear-gradient(
            120deg,
            #062b63,
            #06499c
        ) !important;
}

.hero-image::after{
    background:#f6f9fc !important;
}


/* ==========================================================
   GENERAL SECTIONS
   ========================================================== */

.section-label{
    color:#087bea !important;
}

.section h2{
    color:#062b63 !important;
}

.section-intro{
    color:#667d91 !important;
}


/* ==========================================================
   HOW IT WORKS
   ========================================================== */

.how{
    background:#f3f8fd !important;
}

.step{
    border-color:#dbe7f2 !important;

    border-radius:14px !important;

    box-shadow:
        0 8px 24px rgba(6,43,99,.045) !important;
}

.step-number{
    background:
        linear-gradient(
            135deg,
            #06499c,
            #087bea
        ) !important;

    box-shadow:
        0 5px 13px rgba(8,123,234,.20) !important;
}

.step h3{
    color:#062b63 !important;
}

.step p{
    color:#667d91 !important;
}


/* ==========================================================
   DEPARTMENTS
   ========================================================== */

.department{
    border-color:#dbe7f2 !important;

    border-radius:14px !important;

    box-shadow:
        0 9px 25px rgba(6,43,99,.055) !important;
}

.department-line{
    background:
        linear-gradient(
            90deg,
            #087bea,
            #13b9ef
        ) !important;
}

.department h3{
    color:#062b63 !important;
}

.department p{
    color:#667d91 !important;
}


/* ==========================================================
   HELP
   ========================================================== */

.help{
    background:
        linear-gradient(
            115deg,
            #05285d 0%,
            #063d83 62%,
            #087bea 130%
        ) !important;
}

.help .section-label{
    color:#65d5ff !important;
}

.help h2{
    color:#ffffff !important;
}

.help .section-intro{
    color:rgba(255,255,255,.80) !important;
}


/* ==========================================================
   FOOTER
   ========================================================== */

.footer{
    background:#041f49 !important;
    color:rgba(255,255,255,.68) !important;

    border-top:
        1px solid rgba(255,255,255,.08) !important;
}


/* ==========================================================
   LOGIN BACKDROP
   ========================================================== */

.modal-backdrop{
    background:
        rgba(4,31,73,.30) !important;
}


/* ==========================================================
   LOGIN POPUP
   ========================================================== */

.login-modal{
    border:
        1px solid rgba(6,73,156,.12) !important;

    border-radius:16px !important;

    box-shadow:
        0 28px 75px rgba(4,31,73,.28) !important;
}

.modal-top{
    background:
        linear-gradient(
            120deg,
            #05285d 0%,
            #06499c 60%,
            #087bea 100%
        ) !important;
}

.modal-close{
    border-color:
        rgba(255,255,255,.38) !important;

    background:
        rgba(255,255,255,.09) !important;
}

.modal-close:hover{
    background:
        rgba(255,255,255,.17) !important;
}

.modal-body{
    background:#ffffff !important;
}


/* ==========================================================
   LOGIN FORM
   ========================================================== */

.form-group label{
    color:#35536e !important;
}

.form-group input{
    border-color:#cad9e7 !important;

    border-radius:9px !important;

    color:#17324d !important;

    background:#ffffff !important;
}

.form-group input:focus{
    border-color:#087bea !important;

    box-shadow:
        0 0 0 3px rgba(8,123,234,.11) !important;
}

.show-password{
    color:#087bea !important;
}

.sign-in{
    border-radius:9px !important;

    background:
        linear-gradient(
            120deg,
            #06499c,
            #087bea
        ) !important;

    box-shadow:
        0 6px 17px rgba(8,123,234,.19) !important;
}

.sign-in:hover{
    background:
        linear-gradient(
            120deg,
            #062b63,
            #06499c
        ) !important;
}


/* ==========================================================
   STUDENT NOTE
   ========================================================== */

.student-note{
    border-left-color:#087bea !important;

    background:#f0f8ff !important;
    color:#61798e !important;

    border-radius:0 8px 8px 0 !important;
}

.student-note strong{
    color:#062b63 !important;
}


/* ==========================================================
   LOGIN ALERTS
   Keep semantic colours
   ========================================================== */

.alert-success{
    border-color:#c7e5d5 !important;
    background:#eaf7f0 !important;
    color:#17613d !important;
}

.alert-error{
    border-color:#efc9c9 !important;
    background:#fff0f0 !important;
    color:#8a2929 !important;
}


/* ==========================================================
   MANAGED SMARTLOG LOGO
   Keep circular WhatsApp-style crop
   ========================================================== */

.logo:has(img.smartlog-managed-logo),
.modal-logo:has(img.smartlog-managed-logo){

    background:#ffffff !important;

    border-color:
        rgba(255,255,255,.94) !important;

    box-shadow:
        0 3px 10px rgba(0,0,0,.16) !important;
}


/* ==========================================================
   RESPONSIVE
   ========================================================== */

@media(max-width:900px){

    .mobile-menu{
        box-shadow:
            0 16px 35px rgba(6,43,99,.16) !important;
    }
}

</style>
<!-- SMARTLOG-FINAL-BLUE-END -->
</body>
</html>










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
