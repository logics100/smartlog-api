<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Units | SmartLog</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7f5;
            color: #26352f;
        }

        .app {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #005f3c;
            color: white;
            padding: 28px 20px;
            position: fixed;
            left: 0;
            top: 0;
        }

        .brand {
            padding: 5px 10px 28px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .brand h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .brand p {
            color: #cce8dc;
            font-size: 13px;
        }

        .role-badge {
            display: inline-block;
            margin-top: 12px;
            padding: 6px 11px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 12px;
            font-weight: bold;
        }

        .navigation {
            margin-top: 28px;
        }

        .navigation a {
            display: block;
            text-decoration: none;
            color: #e5f5ee;
            padding: 13px 14px;
            border-radius: 8px;
            margin-bottom: 7px;
            font-size: 14px;
        }

        .navigation a:hover,
        .navigation a.active {
            background: rgba(255, 255, 255, 0.14);
            color: white;
        }

        .main {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-height: 100vh;
        }

        .topbar {
            background: white;
            min-height: 76px;
            padding: 16px 30px;
            border-bottom: 1px solid #e1e8e4;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar-title h2 {
            color: #174331;
            font-size: 22px;
            margin-bottom: 4px;
        }

        .topbar-title p {
            color: #7a8982;
            font-size: 13px;
        }

        .user-area {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .user-info {
            text-align: right;
        }

        .user-info strong {
            display: block;
            color: #203d31;
            font-size: 14px;
        }

        .user-info span {
            color: #829087;
            font-size: 12px;
        }

        .logout-button {
            border: none;
            background: #edf5f1;
            color: #006b45;
            padding: 10px 14px;
            border-radius: 7px;
            font-weight: bold;
            cursor: pointer;
        }

        .content {
            padding: 30px;
        }

        .page-heading {
            margin-bottom: 25px;
        }

        .page-heading h3 {
            color: #174331;
            font-size: 24px;
            margin-bottom: 7px;
        }

        .page-heading p {
            color: #74827b;
            font-size: 14px;
            line-height: 1.5;
        }

        .unit-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .unit-card {
            background: white;
            border: 1px solid #e1e9e5;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.03);
        }

        .unit-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 18px;
        }

        .unit-code {
            display: inline-block;
            background: #e9f5ef;
            color: #006b45;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .unit-title {
            color: #174331;
            font-size: 19px;
            line-height: 1.4;
        }

        .logbook-badge {
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        .logbook-required {
            background: #e8f5ee;
            color: #006b45;
        }

        .logbook-not-required {
            background: #f0f2f1;
            color: #68746e;
        }

        .unit-details {
            border-top: 1px solid #edf1ef;
            padding-top: 16px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 7px 0;
            font-size: 13px;
        }

        .detail-label {
            color: #87928c;
        }

        .detail-value {
            color: #34473e;
            font-weight: bold;
            text-align: right;
        }

        .unit-actions {
            margin-top: 19px;
            padding-top: 17px;
            border-top: 1px solid #edf1ef;
        }

        .view-button {
            display: inline-block;
            background: #006b45;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: bold;
        }

        .view-button:hover {
            background: #005437;
        }

        .empty-state {
            background: white;
            border: 1px solid #e1e9e5;
            border-radius: 12px;
            padding: 45px 30px;
            text-align: center;
        }

        .empty-state h4 {
            color: #174331;
            font-size: 18px;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #7c8983;
            font-size: 14px;
        }

        @media (max-width: 900px) {
            .sidebar {
                width: 210px;
            }

            .main {
                margin-left: 210px;
                width: calc(100% - 210px);
            }

            .unit-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            .app {
                display: block;
            }

            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }

            .main {
                margin-left: 0;
                width: 100%;
            }

            .topbar {
                align-items: flex-start;
                gap: 20px;
                flex-direction: column;
            }

            .user-info {
                text-align: left;
            }

            .content {
                padding: 20px;
            }

            .unit-header {
                flex-direction: column;
            }
        }
    </style>

<style id="smartlog-stable-sidebar">
/* Keep Lecturer navigation in exactly the same position on every page */
.sidebar,
.lecturer-sidebar {
    width: 260px !important;
    padding: 28px 20px !important;
}

.brand,
.lecturer-brand {
    padding: 5px 10px 28px !important;
}

.navigation,
.lecturer-nav {
    margin-top: 28px !important;
}

.navigation a,
.lecturer-nav a {
    display: block !important;
    width: 100% !important;
    height: 42px !important;
    padding: 13px 14px !important;
    margin: 0 0 7px 0 !important;
    border-radius: 8px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 14px !important;
    font-weight: normal !important;
    line-height: 16px !important;
    text-decoration: none !important;
    box-sizing: border-box !important;
}

.navigation a.active,
.lecturer-nav a.active {
    font-weight: normal !important;
}

.role-badge,
.lecturer-role {
    height: 25px !important;
    line-height: 13px !important;
}

@media (max-width: 650px) {
    .sidebar,
    .lecturer-sidebar {
        width: 100% !important;
    }
}
</style>



<style id="smartlog-nav-final-fix">
.navigation a,
.lecturer-nav a {
    display: block !important;
    width: 100% !important;
    height: 42px !important;
    padding: 13px 14px !important;
    margin: 0 0 7px 0 !important;
    border-radius: 8px !important;
    background: transparent !important;
    color: #e5f5ee !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 14px !important;
    font-weight: normal !important;
    line-height: 16px !important;
    text-decoration: none !important;
    border: none !important;
}

.navigation a:hover,
.lecturer-nav a:hover {
    background: rgba(255,255,255,0.08) !important;
    color: white !important;
}

.navigation a.active,
.lecturer-nav a.active {
    background: rgba(255,255,255,0.14) !important;
    color: white !important;
    font-weight: normal !important;
}
</style>
<style id="smartlog-unified-navigation">

.smartlog-main-nav {
    margin-top: 28px !important;
    width: 100% !important;
}

.smartlog-main-nav a {
    display: flex !important;
    align-items: center !important;

    width: 100% !important;
    height: 42px !important;

    margin: 0 0 7px 0 !important;
    padding: 0 14px !important;

    box-sizing: border-box !important;

    border: 0 !important;
    border-radius: 8px !important;

    background: transparent !important;
    color: #e5f5ee !important;

    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    line-height: 1 !important;

    text-decoration: none !important;
}

.smartlog-main-nav a:hover {
    background: rgba(255,255,255,0.08) !important;
    color: #ffffff !important;
}

.smartlog-main-nav a.active {
    background: rgba(255,255,255,0.14) !important;
    color: #ffffff !important;
    font-weight: 400 !important;
}

.sidebar .smartlog-main-nav,
.lecturer-sidebar .smartlog-main-nav {
    margin-top: 28px !important;
}
</style>
@include('web.shared.professional-theme')
@include('web.shared.lecturer-modern-ui')
</head>

<body>

<div class="app">

    <aside class="sidebar">

        <div class="brand">
            <h1>SmartLog</h1>
            <p>DWU Clinical Logbook</p>

            <span class="role-badge">
                LECTURER
            </span>
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

        <header class="topbar">

            <div class="topbar-title">
                <h2>My Units</h2>
                <p>SmartLog Academic Portal</p>
            </div>

            <div class="user-area">

                <div class="user-info">
                    <strong>{{ $user->name }}</strong>
                    <span>{{ $user->dwu_id }}</span>
                </div>

                <form
                    method="POST"
                    action="{{ route('web.logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="logout-button"
                    >
                        Logout
                    </button>
                </form>

            </div>

        </header>

        <div class="content">

            <div class="page-heading">

                <h3>Your Assigned Practical Units</h3>

                <p>
                    These are the practical units currently assigned
                    to your SmartLog lecturer account.
                </p>

            </div>

            @if ($units->isEmpty())

                <div class="empty-state">

                    <h4>No Units Assigned</h4>

                    <p>
                        There are currently no practical units assigned
                        to your lecturer account.
                    </p>

                </div>

            @else

                <div class="unit-grid">

                    @foreach ($units as $unit)

                        <div class="unit-card">

                            <div class="unit-header">

                                <div>

                                    <span class="unit-code">
                                        {{ $unit->unit_code }}
                                    </span>

                                    <h4 class="unit-title">
                                        {{ $unit->unit_name }}
                                    </h4>

                                </div>

                                @if ($unit->requires_logbook)

                                    <span
                                        class="logbook-badge logbook-required"
                                    >
                                        LOGBOOK REQUIRED
                                    </span>

                                @else

                                    <span
                                        class="logbook-badge logbook-not-required"
                                    >
                                        NO LOGBOOK
                                    </span>

                                @endif

                            </div>

                            <div class="unit-details">

                                <div class="detail-row">

                                    <span class="detail-label">
                                        Department
                                    </span>

                                    <span class="detail-value">
                                        {{ $unit->department_name }}
                                    </span>

                                </div>

                                <div class="detail-row">

                                    <span class="detail-label">
                                        Year Level
                                    </span>

                                    <span class="detail-value">
                                        {{ $unit->year_name }}
                                    </span>

                                </div>

                                <div class="detail-row">

                                    <span class="detail-label">
                                        Semester
                                    </span>

                                    <span class="detail-value">
                                        {{ $unit->semester_name }}
                                    </span>

                                </div>

                            </div>

                            <div class="unit-actions">

                                <a
                                    href="{{ route('web.lecturer.unit.students', ['unitId' => $unit->id]) }}"
                                    class="view-button"
                                >
                                    View Students
                                </a>

                            </div>

                        </div>

                    @endforeach

                </div>

            @endif

        </div>

    </main>

</div>

</body>
</html>










