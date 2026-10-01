<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Lecturer Dashboard | SmartLog</title>

    <style>
        :root {
            --sl-navy: #082c63;
            --sl-deep: #063b82;
            --sl-blue: #087bea;
            --sl-bright: #12b9ef;

            --sl-bg: #f5f8fc;
            --sl-surface: #ffffff;
            --sl-soft: #edf6ff;

            --sl-text: #14243b;
            --sl-muted: #68778b;
            --sl-border: #dfe8f2;

            --sl-success: #16875d;
            --sl-warning: #d58b16;

            --sl-shadow:
                0 12px 32px rgba(8, 44, 99, .08);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family:
                Inter,
                "Segoe UI",
                Arial,
                Helvetica,
                sans-serif;

            background: var(--sl-bg);
            color: var(--sl-text);
        }

        /*
        ------------------------------------------------------
        KEEP ORIGINAL SIDEBAR FOR SHARED HAMBURGER SYSTEM
        ------------------------------------------------------
        The professional-theme include reads these real links.
        It will hide the old sidebar visually.
        */

        .app {
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            min-height: 100vh;

            background: var(--sl-navy);
            color: white;

            position: fixed;
            left: 0;
            top: 0;

            padding: 28px 20px;
        }

        .brand {
            padding: 5px 10px 28px;
        }

        .brand h1 {
            margin: 0 0 5px;
            font-size: 27px;
        }

        .brand p {
            margin: 0;
            color: #d9eaff;
            font-size: 13px;
        }

        .role-badge {
            display: inline-block;

            margin-top: 12px;
            padding: 6px 11px;

            border-radius: 999px;

            background: rgba(255, 255, 255, .13);

            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
        }

        .smartlog-main-nav {
            margin-top: 24px;
        }

        .smartlog-main-nav a {
            display: block;

            color: #e8f3ff;
            text-decoration: none;

            padding: 13px 14px;
            margin-bottom: 7px;

            border-radius: 9px;

            font-size: 14px;
        }

        .smartlog-main-nav a:hover,
        .smartlog-main-nav a.active {
            background: rgba(255, 255, 255, .13);
            color: #fff;
        }

        /*
        ------------------------------------------------------
        MAIN DASHBOARD
        ------------------------------------------------------
        */

        .main {
            min-height: 100vh;
        }

        .old-topbar {
            display: none;
        }

        .dashboard-content {
            width: min(1240px, calc(100% - 48px));
            margin: 0 auto;
            padding: 34px 0 55px;
        }

        /*
        ------------------------------------------------------
        PAGE INTRO
        ------------------------------------------------------
        */

        .page-intro {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 25px;

            margin-bottom: 24px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            margin-bottom: 8px;

            color: var(--sl-blue);

            font-size: 12px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .eyebrow-dot {
            width: 8px;
            height: 8px;

            border-radius: 50%;

            background: var(--sl-bright);

            box-shadow:
                0 0 0 5px rgba(18, 185, 239, .11);
        }

        .page-intro h1 {
            margin: 0;

            color: var(--sl-navy);

            font-size: clamp(28px, 4vw, 42px);
            line-height: 1.08;
            letter-spacing: -.035em;
        }

        .page-intro p {
            margin: 8px 0 0;

            color: var(--sl-muted);

            font-size: 14px;
            line-height: 1.6;
        }

        .lecturer-chip {
            display: flex;
            align-items: center;
            gap: 11px;

            padding: 9px 13px 9px 9px;

            border: 1px solid var(--sl-border);
            border-radius: 999px;

            background: white;

            box-shadow: 0 5px 18px rgba(8, 44, 99, .05);
        }

        .lecturer-avatar {
            width: 39px;
            height: 39px;

            display: grid;
            place-items: center;

            border-radius: 50%;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    var(--sl-deep),
                    var(--sl-bright)
                );

            font-weight: 800;
        }

        .lecturer-chip strong {
            display: block;

            color: var(--sl-navy);

            font-size: 13px;
        }

        .lecturer-chip span {
            display: block;

            margin-top: 2px;

            color: var(--sl-muted);

            font-size: 11px;
        }

        /*
        ------------------------------------------------------
        HERO
        ------------------------------------------------------
        */

        .hero {
            min-height: 340px;

            display: grid;
            grid-template-columns: 1.08fr .92fr;

            overflow: hidden;

            border: 1px solid #dbe8f5;
            border-radius: 28px;

            background:
                linear-gradient(
                    135deg,
                    #ffffff 0%,
                    #f2f8ff 100%
                );

            box-shadow: var(--sl-shadow);

            margin-bottom: 24px;
        }

        .hero-copy {
            position: relative;
            z-index: 2;

            display: flex;
            flex-direction: column;
            justify-content: center;

            padding: 48px;
        }

        .hero-label {
            display: inline-flex;
            align-items: center;

            width: fit-content;

            margin-bottom: 17px;
            padding: 7px 11px;

            border-radius: 999px;

            color: var(--sl-deep);
            background: #e4f3ff;

            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .hero h2 {
            max-width: 590px;

            margin: 0;

            color: var(--sl-navy);

            font-size: clamp(31px, 4.3vw, 53px);
            line-height: 1.02;
            letter-spacing: -.045em;
        }

        .hero h2 span {
            color: var(--sl-blue);
        }

        .hero p {
            max-width: 600px;

            margin: 18px 0 25px;

            color: var(--sl-muted);

            font-size: 15px;
            line-height: 1.75;
        }

        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 11px;
        }

        .primary-button,
        .secondary-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;

            min-height: 45px;

            padding: 0 18px;

            border-radius: 12px;

            text-decoration: none;

            font-size: 13px;
            font-weight: 750;

            transition:
                transform .18s ease,
                box-shadow .18s ease,
                background .18s ease;
        }

        .primary-button {
            color: white;

            background:
                linear-gradient(
                    135deg,
                    var(--sl-deep),
                    var(--sl-blue)
                );

            box-shadow:
                0 9px 22px rgba(8, 123, 234, .20);
        }

        .secondary-button {
            color: var(--sl-deep);

            border: 1px solid #cfdfef;

            background: white;
        }

        .primary-button:hover,
        .secondary-button:hover {
            transform: translateY(-2px);
        }

        .hero-photo {
            position: relative;
            min-height: 340px;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    #dceeff,
                    #eef8ff
                );
        }

        .hero-photo::before {
            content: "";

            position: absolute;
            z-index: 2;

            top: 0;
            left: -1px;

            width: 80px;
            height: 100%;

            background: white;

            clip-path:
                polygon(
                    0 0,
                    100% 0,
                    30% 100%,
                    0 100%
                );

            opacity: .97;
        }

        .hero-photo img {
            width: 100%;
            height: 100%;
            min-height: 340px;

            display: block;

            object-fit: cover;
            object-position: center;

            filter:
                contrast(1.02)
                saturate(1.03);
        }

        .hero-photo-badge {
            position: absolute;
            z-index: 4;

            right: 20px;
            bottom: 20px;

            max-width: 230px;

            padding: 12px 14px;

            border: 1px solid rgba(255, 255, 255, .55);
            border-radius: 13px;

            color: white;

            background: rgba(6, 44, 99, .88);

            box-shadow:
                0 10px 24px rgba(0, 0, 0, .15);

            backdrop-filter: blur(6px);

            font-size: 12px;
            line-height: 1.45;
        }

        /*
        ------------------------------------------------------
        STATISTICS
        ------------------------------------------------------
        */

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;

            margin-bottom: 31px;
        }

        .stat-card {
            position: relative;

            min-height: 155px;

            overflow: hidden;

            padding: 22px;

            border: 1px solid var(--sl-border);
            border-radius: 18px;

            background: white;

            box-shadow:
                0 8px 22px rgba(8, 44, 99, .045);

            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);

            box-shadow:
                0 14px 28px rgba(8, 44, 99, .09);
        }

        .stat-card::after {
            content: "";

            position: absolute;

            right: -28px;
            top: -28px;

            width: 105px;
            height: 105px;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    rgba(8, 123, 234, .10),
                    rgba(18, 185, 239, .04)
                );
        }

        .stat-top {
            position: relative;
            z-index: 2;

            display: flex;
            justify-content: space-between;
            align-items: flex-start;

            margin-bottom: 18px;
        }

        .stat-icon {
            width: 42px;
            height: 42px;

            display: grid;
            place-items: center;

            border-radius: 12px;

            color: var(--sl-deep);
            background: var(--sl-soft);

            font-size: 18px;
            font-weight: 900;
        }

        .stat-number {
            color: var(--sl-navy);

            font-size: 35px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -.035em;
        }

        .stat-title {
            margin-bottom: 5px;

            color: var(--sl-text);

            font-size: 13px;
            font-weight: 750;
        }

        .stat-note {
            color: var(--sl-muted);

            font-size: 12px;
            line-height: 1.5;
        }

        /*
        ------------------------------------------------------
        QUICK ACTIONS
        ------------------------------------------------------
        */

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;

            margin-bottom: 16px;
        }

        .section-heading h2 {
            margin: 0;

            color: var(--sl-navy);

            font-size: 23px;
            letter-spacing: -.025em;
        }

        .section-heading p {
            margin: 5px 0 0;

            color: var(--sl-muted);

            font-size: 13px;
            line-height: 1.5;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .action-card {
            position: relative;

            min-height: 195px;

            display: flex;
            flex-direction: column;

            padding: 22px;

            overflow: hidden;

            border: 1px solid var(--sl-border);
            border-radius: 19px;

            color: inherit;
            text-decoration: none;

            background: white;

            box-shadow:
                0 8px 22px rgba(8, 44, 99, .045);

            transition:
                transform .2s ease,
                border-color .2s ease,
                box-shadow .2s ease;
        }

        .action-card:hover {
            transform: translateY(-4px);

            border-color: #bad8f5;

            box-shadow:
                0 16px 30px rgba(8, 44, 99, .09);
        }

        .action-icon {
            width: 46px;
            height: 46px;

            display: grid;
            place-items: center;

            margin-bottom: 22px;

            border-radius: 13px;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    var(--sl-deep),
                    var(--sl-blue)
                );

            box-shadow:
                0 7px 17px rgba(8, 123, 234, .18);

            font-size: 18px;
            font-weight: 850;
        }

        .action-card h3 {
            margin: 0 0 7px;

            color: var(--sl-navy);

            font-size: 16px;
        }

        .action-card p {
            margin: 0;

            color: var(--sl-muted);

            font-size: 12.5px;
            line-height: 1.55;
        }

        .action-arrow {
            margin-top: auto;
            padding-top: 17px;

            color: var(--sl-blue);

            font-size: 13px;
            font-weight: 750;
        }

        .action-card.featured {
            color: white;

            border-color: transparent;

            background:
                linear-gradient(
                    145deg,
                    var(--sl-navy),
                    var(--sl-deep)
                );
        }

        .action-card.featured h3,
        .action-card.featured p,
        .action-card.featured .action-arrow {
            color: white;
        }

        .action-card.featured p {
            color: #d7e9ff;
        }

        .action-card.featured .action-icon {
            color: var(--sl-deep);
            background: white;
            box-shadow: none;
        }

        /*
        ------------------------------------------------------
        FOOTER NOTE
        ------------------------------------------------------
        */

        .dashboard-footer {
            display: flex;
            justify-content: space-between;
            gap: 20px;

            margin-top: 32px;
            padding-top: 20px;

            border-top: 1px solid var(--sl-border);

            color: var(--sl-muted);

            font-size: 11px;
        }

        /*
        ------------------------------------------------------
        RESPONSIVE
        ------------------------------------------------------
        */

        @media (max-width: 1000px) {

            .hero {
                grid-template-columns: 1fr;
            }

            .hero-photo {
                min-height: 290px;
            }

            .hero-photo::before {
                display: none;
            }

            .hero-photo img {
                min-height: 290px;
            }

            .action-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 760px) {

            .dashboard-content {
                width: min(100% - 28px, 1240px);
                padding-top: 24px;
            }

            .page-intro {
                align-items: flex-start;
                flex-direction: column;
            }

            .lecturer-chip {
                width: 100%;
            }

            .hero {
                border-radius: 20px;
            }

            .hero-copy {
                padding: 30px 24px;
            }

            .hero h2 {
                font-size: 36px;
            }

            .stats-grid,
            .action-grid {
                grid-template-columns: 1fr;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .primary-button,
            .secondary-button {
                width: 100%;
            }

            .dashboard-footer {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {

            .dashboard-content {
                width: min(100% - 22px, 1240px);
            }

            .hero-copy {
                padding: 26px 20px;
            }

            .hero h2 {
                font-size: 31px;
            }

            .hero-photo,
            .hero-photo img {
                min-height: 235px;
            }

            .stat-card,
            .action-card {
                border-radius: 16px;
            }
        }
    </style>

    @include('web.shared.professional-theme')
</head>

<body>

<div class="app">

    {{-- =====================================================
         ORIGINAL NAVIGATION
         Kept so the shared SmartLog hamburger can use it.
         ===================================================== --}}

    <aside class="sidebar">

        <div class="brand">
            <h1>SmartLog</h1>
            <p>DWU Clinical Logbook</p>
            <span class="role-badge">LECTURER</span>
        </div>

        <nav class="smartlog-main-nav">

            <a href="{{ route('web.lecturer.dashboard') }}"
               class="{{ request()->routeIs('web.lecturer.dashboard') ? 'active' : '' }}">
                Dashboard
            </a>

            <a href="{{ route('web.lecturer.units') }}"
               class="{{ request()->routeIs('web.lecturer.units*') ? 'active' : '' }}">
                My Units
            </a>

            <a href="{{ route('web.lecturer.students') }}"
               class="{{ request()->routeIs('web.lecturer.students*') ? 'active' : '' }}">
                Students
            </a>

            <a href="{{ route('web.lecturer.student-progress') }}"
               class="{{ request()->routeIs('web.lecturer.student-progress*') ? 'active' : '' }}">
                Student Progress
            </a>

            <a href="{{ route('web.lecturer.verifications') }}"
               class="{{ request()->routeIs('web.lecturer.verifications*') ? 'active' : '' }}">
                Pending Verifications
            </a>

            <a href="{{ route('web.lecturer.logbooks') }}"
               class="{{ request()->routeIs('web.lecturer.logbooks*') ? 'active' : '' }}">
                Clinical Logbooks
            </a>

        </nav>

    </aside>


    <main class="main">

        {{-- Real logout form retained for shared header --}}
        <header class="old-topbar">

            <div>
                <strong>{{ $user->name }}</strong>
                <span>{{ $user->dwu_id }}</span>
            </div>

            <form method="POST" action="{{ route('web.logout') }}">
                @csrf
                <button type="submit">
                    Logout
                </button>
            </form>

        </header>


        <div class="dashboard-content">

            {{-- PAGE INTRO --}}
            <section class="page-intro">

                <div>

                    <div class="eyebrow">
                        <span class="eyebrow-dot"></span>
                        Lecturer Portal
                    </div>

                    <h1>Dashboard</h1>

                    <p>
                        Your clinical teaching workspace in SmartLog.
                    </p>

                </div>

                <div class="lecturer-chip">

                    <div class="lecturer-avatar">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>

                    <div>
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $user->dwu_id }} · Lecturer</span>
                    </div>

                </div>

            </section>


            {{-- HERO --}}
            <section class="hero">

                <div class="hero-copy">

                    <div class="hero-label">
                        Clinical Learning
                    </div>

                    <h2>
                        Welcome back,
                        <span>{{ $user->name }}</span>
                    </h2>

                    <p>
                        Manage your practical units, monitor student clinical
                        progress, review supervisor verification evidence and
                        manage clinical logbooks from one place.
                    </p>

                    <div class="hero-buttons">

                        <a href="{{ route('web.lecturer.student-progress') }}"
                           class="primary-button">
                            View Student Progress
                            <span>→</span>
                        </a>

                        <a href="{{ route('web.lecturer.verifications') }}"
                           class="secondary-button">
                            Review Verifications
                        </a>

                    </div>

                </div>


                <div class="hero-photo">

                    <img
                        src="{{ asset('images/smartlog/clinical-team.jpg') }}"
                        alt="DWU clinical learning">

                    <div class="hero-photo-badge">
                        Supporting practical learning, clinical evidence and
                        student progress through one digital logbook.
                    </div>

                </div>

            </section>


            {{-- LIVE STATISTICS --}}
            <section class="stats-grid">

                <div class="stat-card">

                    <div class="stat-top">

                        <div class="stat-icon">
                            U
                        </div>

                        <div class="stat-number">
                            {{ $myUnitsCount }}
                        </div>

                    </div>

                    <div class="stat-title">
                        My Units
                    </div>

                    <div class="stat-note">
                        Practical units currently assigned to your lecturer account.
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <div class="stat-icon">
                            S
                        </div>

                        <div class="stat-number">
                            {{ $enrolledStudentsCount }}
                        </div>

                    </div>

                    <div class="stat-title">
                        Enrolled Students
                    </div>

                    <div class="stat-note">
                        Active students enrolled across your assigned practical units.
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <div class="stat-icon">
                            ✓
                        </div>

                        <div class="stat-number">
                            {{ $pendingVerificationsCount }}
                        </div>

                    </div>

                    <div class="stat-title">
                        Pending Verifications
                    </div>

                    <div class="stat-note">
                        Supervisor verification records waiting for lecturer review.
                    </div>

                </div>

            </section>


            {{-- QUICK ACTIONS --}}
            <section>

                <div class="section-heading">

                    <div>
                        <h2>Quick Actions</h2>

                        <p>
                            Access the Lecturer functions you use most.
                        </p>
                    </div>

                </div>


                <div class="action-grid">

                    <a class="action-card"
                       href="{{ route('web.lecturer.units') }}">

                        <div class="action-icon">
                            U
                        </div>

                        <h3>My Units</h3>

                        <p>
                            View your assigned practical units and access
                            students enrolled in each unit.
                        </p>

                        <div class="action-arrow">
                            Open My Units →
                        </div>

                    </a>


                    <a class="action-card"
                       href="{{ route('web.lecturer.students') }}">

                        <div class="action-icon">
                            S
                        </div>

                        <h3>Students</h3>

                        <p>
                            View students enrolled in your practical units and
                            access their SmartLog records.
                        </p>

                        <div class="action-arrow">
                            View Students →
                        </div>

                    </a>


                    <a class="action-card"
                       href="{{ route('web.lecturer.student-progress') }}">

                        <div class="action-icon">
                            %
                        </div>

                        <h3>Student Progress</h3>

                        <p>
                            Monitor clinical activities, attendance and
                            completion progress for student logbooks.
                        </p>

                        <div class="action-arrow">
                            Monitor Progress →
                        </div>

                    </a>


                    <a class="action-card featured"
                       href="{{ route('web.lecturer.verifications') }}">

                        <div class="action-icon">
                            ✓
                        </div>

                        <h3>Verification Review</h3>

                        <p>
                            Review supervisor evidence and make the final
                            lecturer decision on submitted verification records.
                        </p>

                        <div class="action-arrow">
                            Review Verifications →
                        </div>

                    </a>


                    <a class="action-card"
                       href="{{ route('web.lecturer.logbooks') }}">

                        <div class="action-icon">
                            L
                        </div>

                        <h3>Clinical Logbooks</h3>

                        <p>
                            Create and manage digital clinical logbook templates
                            for your assigned practical units.
                        </p>

                        <div class="action-arrow">
                            Manage Logbooks →
                        </div>

                    </a>


                    <a class="action-card"
                       href="{{ route('web.lecturer.dashboard') }}">

                        <div class="action-icon">
                            ↻
                        </div>

                        <h3>Refresh Dashboard</h3>

                        <p>
                            Reload the dashboard to view the latest student,
                            unit and verification information.
                        </p>

                        <div class="action-arrow">
                            Refresh →
                        </div>

                    </a>

                </div>

            </section>


            <footer class="dashboard-footer">

                <span>
                    SmartLog · DWU Clinical Logbook System
                </span>

                <span>
                    Lecturer Portal
                </span>

            </footer>

        </div>

    </main>

</div>

</body>
</html>