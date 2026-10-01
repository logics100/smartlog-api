<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $unit->unit_code }} Students | SmartLog</title>

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

        .logout-button:hover {
            background: #dfece6;
        }

        .content {
            padding: 30px;
        }

        .back-link {
            display: inline-block;
            color: #006b45;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .unit-header {
            background: linear-gradient(135deg, #007a4d, #005f3c);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .unit-code {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .unit-header h3 {
            font-size: 23px;
            margin-bottom: 8px;
        }

        .unit-header p {
            color: #dff4e9;
            font-size: 13px;
            line-height: 1.6;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .summary-card {
            background: white;
            border: 1px solid #e1e9e5;
            border-radius: 10px;
            padding: 18px;
        }

        .summary-label {
            color: #839089;
            font-size: 12px;
            margin-bottom: 7px;
        }

        .summary-value {
            color: #174331;
            font-size: 15px;
            font-weight: bold;
        }

        .students-section {
            background: white;
            border: 1px solid #e1e9e5;
            border-radius: 12px;
            overflow: hidden;
        }

        .section-heading {
            padding: 20px 22px;
            border-bottom: 1px solid #e8eeeb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .section-heading h3 {
            color: #174331;
            font-size: 18px;
        }

        .student-count {
            background: #e8f5ee;
            color: #006b45;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th {
            background: #f7faf8;
            color: #63736b;
            text-align: left;
            padding: 14px 16px;
            font-size: 12px;
            border-bottom: 1px solid #e2e9e5;
        }

        td {
            padding: 15px 16px;
            border-bottom: 1px solid #edf1ef;
            font-size: 13px;
            color: #34473e;
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fafcfb;
        }

        .student-name {
            font-weight: bold;
            color: #174331;
        }

        .student-email {
            color: #7b8982;
            font-size: 12px;
            margin-top: 4px;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-active {
            background: #e5f5ec;
            color: #087445;
        }

        .status-other {
            background: #f0f2f1;
            color: #68756f;
        }

        .progress-container {
            min-width: 130px;
        }

        .progress-top {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 11px;
        }

        .progress-bar {
            width: 100%;
            height: 7px;
            background: #e8eeeb;
            border-radius: 20px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #007a4d;
            border-radius: 20px;
        }

        .no-logbook {
            color: #89958f;
            font-size: 12px;
        }

        .empty-state {
            padding: 45px 25px;
            text-align: center;
        }

        .empty-state h4 {
            color: #174331;
            font-size: 18px;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #7d8983;
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .sidebar {
                width: 210px;
            }

            .main {
                margin-left: 210px;
                width: calc(100% - 210px);
            }

            .summary {
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
                flex-direction: column;
                align-items: flex-start;
                gap: 18px;
            }

            .user-info {
                text-align: left;
            }

            .content {
                padding: 20px;
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

                <h2>Unit Students</h2>

                <p>SmartLog Academic Portal</p>

            </div>

            <div class="user-area">

                <div class="user-info">

                    <strong>
                        {{ $user->name }}
                    </strong>

                    <span>
                        {{ $user->dwu_id }}
                    </span>

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

            <a
                href="{{ route('web.lecturer.units') }}"
                class="back-link"
            >
                â† Back to My Units
            </a>


            <section class="unit-header">

                <span class="unit-code">
                    {{ $unit->unit_code }}
                </span>

                <h3>
                    {{ $unit->unit_name }}
                </h3>

                <p>
                    View students currently enrolled in this
                    SmartLog practical unit and their logbook progress.
                </p>

            </section>


            <section class="summary">

                <div class="summary-card">

                    <div class="summary-label">
                        Department
                    </div>

                    <div class="summary-value">
                        {{ $unit->department_name ?? 'Not Available' }}
                    </div>

                </div>


                <div class="summary-card">

                    <div class="summary-label">
                        Year Level
                    </div>

                    <div class="summary-value">
                        {{ $unit->year_name ?? 'Not Available' }}
                    </div>

                </div>


                <div class="summary-card">

                    <div class="summary-label">
                        Semester
                    </div>

                    <div class="summary-value">
                        {{ $unit->semester_name ?? 'Not Available' }}
                    </div>

                </div>

            </section>


            <section class="students-section">

                <div class="section-heading">

                    <h3>
                        Enrolled Students
                    </h3>

                    <span class="student-count">
                        {{ $students->count() }} Students
                    </span>

                </div>


                @if ($students->isEmpty())

                    <div class="empty-state">

                        <h4>No Students Enrolled</h4>

                        <p>
                            There are currently no students enrolled
                            in this practical unit.
                        </p>

                    </div>

                @else

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>
                                    <th>DWU ID</th>
                                    <th>Student</th>
                                    <th>Enrolment</th>
                                    <th>Logbook</th>
                                    <th>Progress</th>
                                </tr>

                            </thead>

                            <tbody>

                                @foreach ($students as $student)

                                    @php
                                        $progress = (float) (
                                            $student->completion_percentage ?? 0
                                        );

                                        $progress = max(
                                            0,
                                            min(100, $progress)
                                        );
                                    @endphp

                                    <tr>

                                        <td>
                                            <strong>
                                                {{ $student->dwu_id }}
                                            </strong>
                                        </td>

                                        <td>

                                            <div class="student-name">
                                                {{ $student->name }}
                                            </div>

                                            <div class="student-email">
                                                {{ $student->email }}
                                            </div>

                                        </td>

                                        <td>

                                            @if (
                                                strtoupper(
                                                    (string) $student->enrollment_status
                                                ) === 'ACTIVE'
                                            )

                                                <span
                                                    class="status status-active"
                                                >
                                                    ACTIVE
                                                </span>

                                            @else

                                                <span
                                                    class="status status-other"
                                                >
                                                    {{ $student->enrollment_status ?? 'UNKNOWN' }}
                                                </span>

                                            @endif

                                        </td>

                                        <td>

                                            @if ($student->student_logbook_id)

                                                <span
                                                    class="status
                                                    {{
                                                        strtoupper(
                                                            (string) $student->logbook_status
                                                        ) === 'ACTIVE'
                                                            ? 'status-active'
                                                            : 'status-other'
                                                    }}"
                                                >
                                                    {{ $student->logbook_status ?? 'ASSIGNED' }}
                                                </span>

                                            @else

                                                <span class="no-logbook">
                                                    No logbook
                                                </span>

                                            @endif

                                        </td>

                                        <td>

                                            @if ($student->student_logbook_id)

                                                <div class="progress-container">

                                                    <div class="progress-top">

                                                        <span>
                                                            Completion
                                                        </span>

                                                        <strong>
                                                            {{ number_format($progress, 0) }}%
                                                        </strong>

                                                    </div>

                                                    <div class="progress-bar">

                                                        <div
                                                            class="progress-fill"
                                                            style="width: {{ $progress }}%;"
                                                        ></div>

                                                    </div>

                                                </div>

                                            @else

                                                <span class="no-logbook">
                                                    Not available
                                                </span>

                                            @endif

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @endif

            </section>

        </div>

    </main>

</div>

</body>
</html>










