<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Progress | SmartLog</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7f6;
            color: #1f2937;
        }

        .page {
            min-height: 100vh;
            display: flex;
        }

        /* ---------------------------------------------------------
           Sidebar
        --------------------------------------------------------- */

        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: #005f3c;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            overflow-y: auto;
        }

        .brand {
            padding: 28px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .brand h1 {
            font-size: 25px;
            margin-bottom: 5px;
        }

        .brand p {
            font-size: 13px;
            opacity: 0.8;
        }

        .nav {
            padding: 20px 12px;
        }

        .nav a {
            display: block;
            color: #e7f6f3;
            text-decoration: none;
            padding: 13px 16px;
            margin-bottom: 7px;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.2s;
        }

        .nav a:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .nav a.active {
            background: white;
            color: #005f3c;
            font-weight: bold;
        }

        /* ---------------------------------------------------------
           Main content
        --------------------------------------------------------- */

        .main {
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
        }

        .topbar {
            background: white;
            padding: 20px 28px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .topbar-title h2 {
            font-size: 23px;
            color: #111827;
            margin-bottom: 4px;
        }

        .topbar-title p {
            color: #6b7280;
            font-size: 14px;
        }

        .user-box {
            text-align: right;
        }

        .user-box strong {
            display: block;
            color: #111827;
            font-size: 14px;
        }

        .user-box span {
            color: #6b7280;
            font-size: 12px;
        }

        .content {
            padding: 28px;
        }

        /* ---------------------------------------------------------
           Summary cards
        --------------------------------------------------------- */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 7px rgba(0, 0, 0, 0.04);
        }

        .summary-card .label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 9px;
        }

        .summary-card .value {
            font-size: 28px;
            font-weight: bold;
            color: #005f3c;
        }

        /* ---------------------------------------------------------
           Section
        --------------------------------------------------------- */

        .section-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 7px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .section-header {
            padding: 20px 22px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .section-header h3 {
            font-size: 18px;
            color: #111827;
        }

        .section-header p {
            margin-top: 4px;
            font-size: 13px;
            color: #6b7280;
        }

        /* ---------------------------------------------------------
           Table
        --------------------------------------------------------- */

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1250px;
        }

        th {
            background: #f9fafb;
            color: #4b5563;
            text-align: left;
            padding: 13px 14px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 15px 14px;
            border-bottom: 1px solid #edf0f2;
            vertical-align: middle;
            font-size: 13px;
        }

        tbody tr:hover {
            background: #fafdfc;
        }

        .student-name {
            font-weight: bold;
            color: #111827;
            margin-bottom: 3px;
        }

        .student-email {
            color: #6b7280;
            font-size: 12px;
        }

        .unit-code {
            color: #005f3c;
            font-weight: bold;
        }

        .unit-name {
            color: #6b7280;
            font-size: 12px;
            margin-top: 3px;
        }

        /* ---------------------------------------------------------
           Badges
        --------------------------------------------------------- */

        .badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        .badge-completed {
            background: #dcfce7;
            color: #166534;
        }

        .badge-active {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-none {
            background: #f3f4f6;
            color: #6b7280;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-clear {
            background: #dcfce7;
            color: #166534;
        }

        /* ---------------------------------------------------------
           Progress
        --------------------------------------------------------- */

        .progress-row {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 150px;
        }

        .progress-track {
            width: 100px;
            height: 8px;
            background: #e5e7eb;
            border-radius: 20px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #005f3c;
            border-radius: 20px;
        }

        .progress-value {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            min-width: 42px;
        }

        /* ---------------------------------------------------------
           Attendance
        --------------------------------------------------------- */

        .attendance-info strong {
            display: block;
            color: #111827;
            font-size: 13px;
        }

        .attendance-info span {
            color: #6b7280;
            font-size: 11px;
        }

        /* ---------------------------------------------------------
           View Progress Button
        --------------------------------------------------------- */

        .view-progress-btn {
            display: inline-block;
            padding: 8px 12px;
            background: #005f3c;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
            transition: 0.2s;
        }

        .view-progress-btn:hover {
            background: #00472d;
        }

        /* ---------------------------------------------------------
           Empty state
        --------------------------------------------------------- */

        .empty-state {
            text-align: center;
            padding: 55px 20px;
            color: #6b7280;
        }

        .empty-state h3 {
            color: #374151;
            margin-bottom: 8px;
        }

        /* ---------------------------------------------------------
           Responsive
        --------------------------------------------------------- */

        @media (max-width: 1100px) {

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 760px) {

            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }

            .page {
                display: block;
            }

            .main {
                margin-left: 0;
                width: 100%;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .user-box {
                text-align: left;
            }

            .content {
                padding: 18px;
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

<div class="page">

    <!-- =========================================================
         Sidebar
    ========================================================== -->

    <aside class="sidebar">

        <div class="brand">

            <h1>SmartLog</h1>

            <p>Lecturer Portal</p>

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

    <!-- =========================================================
         Main
    ========================================================== -->

    <main class="main">

        <header class="topbar">

            <div class="topbar-title">

                <h2>Student Progress</h2>

                <p>
                    Monitor clinical logbook progress for students
                    enrolled in your assigned units.
                </p>

            </div>

            <div class="user-box">

                <strong>{{ $user->name }}</strong>

                <span>Lecturer</span>

            </div>

        </header>

        <div class="content">

            <!-- =================================================
                 Summary
            ================================================== -->

            <div class="summary-grid">

                <div class="summary-card">

                    <div class="label">
                        Students
                    </div>

                    <div class="value">
                        {{ $uniqueStudentsCount }}
                    </div>

                </div>

                <div class="summary-card">

                    <div class="label">
                        Logbooks
                    </div>

                    <div class="value">
                        {{ $logbooksCount }}
                    </div>

                </div>

                <div class="summary-card">

                    <div class="label">
                        Completed Logbooks
                    </div>

                    <div class="value">
                        {{ $completedLogbooksCount }}
                    </div>

                </div>

                <div class="summary-card">

                    <div class="label">
                        Pending Verifications
                    </div>

                    <div class="value">
                        {{ $pendingVerificationsCount }}
                    </div>

                </div>

            </div>

            <!-- =================================================
                 Progress Table
            ================================================== -->

            <section class="section-card">

                <div class="section-header">

                    <div>

                        <h3>Clinical Logbook Progress</h3>

                        <p>
                            Progress information is shown only for
                            your assigned units.
                        </p>

                    </div>

                </div>

                @if($students->isEmpty())

                    <div class="empty-state">

                        <h3>No student progress available</h3>

                        <p>
                            No students are currently enrolled in
                            your assigned units.
                        </p>

                    </div>

                @else

                    <div class="table-wrap">

                        <table>

                            <thead>

                                <tr>

                                    <th>DWU ID</th>

                                    <th>Student</th>

                                    <th>Unit</th>

                                    <th>Logbook</th>

                                    <th>Completion</th>

                                    <th>Verified Entries</th>

                                    <th>Attendance</th>

                                    <th>Pending</th>

                                    <th>Action</th>

                                </tr>

                            </thead>

                            <tbody>

                            @foreach($students as $student)

                                @php

                                    $completion =
                                        max(
                                            0,
                                            min(
                                                100,
                                                (float) (
                                                    $student
                                                        ->completion_percentage
                                                    ?? 0
                                                )
                                            )
                                        );

                                    $logbookStatus =
                                        strtoupper(
                                            (string) (
                                                $student->logbook_status
                                                ?? ''
                                            )
                                        );

                                    $pending =
                                        (int) (
                                            $student
                                                ->pending_verifications
                                            ?? 0
                                        );

                                @endphp

                                <tr>

                                    <!-- DWU ID -->

                                    <td>

                                        <strong>
                                            {{ $student->dwu_id ?: 'â€”' }}
                                        </strong>

                                    </td>

                                    <!-- Student -->

                                    <td>

                                        <div class="student-name">
                                            {{ $student->name }}
                                        </div>

                                        <div class="student-email">
                                            {{ $student->email }}
                                        </div>

                                    </td>

                                    <!-- Unit -->

                                    <td>

                                        <div class="unit-code">
                                            {{ $student->unit_code }}
                                        </div>

                                        <div class="unit-name">
                                            {{ $student->unit_name }}
                                        </div>

                                    </td>

                                    <!-- Logbook -->

                                    <td>

                                        @if(!$student->student_logbook_id)

                                            <span class="badge badge-none">
                                                No Logbook
                                            </span>

                                        @elseif($logbookStatus === 'COMPLETED')

                                            <span class="badge badge-completed">
                                                Completed
                                            </span>

                                        @else

                                            <span class="badge badge-active">
                                                {{ $logbookStatus ?: 'Active' }}
                                            </span>

                                        @endif

                                    </td>

                                    <!-- Completion -->

                                    <td>

                                        @if($student->student_logbook_id)

                                            <div class="progress-row">

                                                <div class="progress-track">

                                                    <div
                                                        class="progress-fill"
                                                        style="width: {{ $completion }}%;"
                                                    ></div>

                                                </div>

                                                <div class="progress-value">
                                                    {{ number_format($completion, 0) }}%
                                                </div>

                                            </div>

                                        @else

                                            <span>â€”</span>

                                        @endif

                                    </td>

                                    <!-- Verified Entries -->

                                    <td>

                                        <strong>
                                            {{
                                                (int) (
                                                    $student
                                                        ->verified_entries
                                                    ?? 0
                                                )
                                            }}
                                        </strong>

                                    </td>

                                    <!-- Attendance -->

                                    <td>

                                        <div class="attendance-info">

                                            <strong>
                                                {{
                                                    (int) (
                                                        $student
                                                            ->verified_attendance_records
                                                        ?? 0
                                                    )
                                                }}
                                                verified
                                            </strong>

                                            <span>
                                                {{
                                                    number_format(
                                                        (float) (
                                                            $student
                                                                ->verified_attendance_hours
                                                            ?? 0
                                                        ),
                                                        1
                                                    )
                                                }}
                                                hours
                                            </span>

                                        </div>

                                    </td>

                                    <!-- Pending -->

                                    <td>

                                        @if($pending > 0)

                                            <span class="badge badge-pending">
                                                {{ $pending }} Pending
                                            </span>

                                        @else

                                            <span class="badge badge-clear">
                                                Clear
                                            </span>

                                        @endif

                                    </td>

                                    <!-- Action -->

                                    <td>

                                        <a
                                            href="{{ route(
                                                'web.lecturer.student-progress.show',
                                                [
                                                    'unitId' => $student->unit_id,
                                                    'studentId' => $student->student_id,
                                                ]
                                            ) }}"
                                            class="view-progress-btn"
                                        >
                                            View Progress
                                        </a>

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



