<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Progress Details | SmartLog</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7f5;
            color: #1f2937;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* ---------------------------------------------------------
           Sidebar
        --------------------------------------------------------- */

        .sidebar {
            width: 260px;
            background: #005f3c;
            color: white;
            padding: 28px 18px;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            overflow-y: auto;
        }

        .brand {
            margin-bottom: 35px;
        }

        .brand h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .brand p {
            font-size: 13px;
            opacity: 0.8;
        }

        .nav a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 13px 14px;
            margin-bottom: 8px;
            border-radius: 8px;
            font-size: 15px;
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
           Main
        --------------------------------------------------------- */

        .main {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-height: 100vh;
        }

        .topbar {
            min-height: 76px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 30px;
        }

        .topbar h1 {
            font-size: 22px;
            color: #111827;
        }

        .topbar-user {
            text-align: right;
        }

        .topbar-user strong {
            display: block;
            font-size: 14px;
        }

        .topbar-user span {
            font-size: 12px;
            color: #6b7280;
        }

        .content {
            padding: 30px;
        }

        /* ---------------------------------------------------------
           Back
        --------------------------------------------------------- */

        .back-row {
            margin-bottom: 20px;
        }

        .back-link {
            display: inline-block;
            text-decoration: none;
            color: #005f3c;
            font-weight: 700;
            font-size: 14px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* ---------------------------------------------------------
           Student Header
        --------------------------------------------------------- */

        .student-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 22px;
            border: 1px solid #e5e7eb;
        }

        .student-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }

        .student-name {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }

        .student-id {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 14px;
        }

        .student-details {
            display: flex;
            gap: 22px;
            flex-wrap: wrap;
        }

        .detail-item {
            font-size: 14px;
        }

        .detail-item strong {
            display: block;
            color: #374151;
            margin-bottom: 4px;
        }

        .detail-item span {
            color: #6b7280;
        }

        .status-badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-completed {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-neutral {
            background: #f3f4f6;
            color: #4b5563;
        }

        /* ---------------------------------------------------------
           Progress
        --------------------------------------------------------- */

        .progress-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 22px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 18px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: center;
            margin-bottom: 10px;
        }

        .progress-header strong {
            font-size: 16px;
        }

        .progress-percent {
            font-size: 22px;
            font-weight: 700;
            color: #005f3c;
        }

        .progress-track {
            width: 100%;
            height: 14px;
            background: #e5e7eb;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .progress-fill {
            height: 100%;
            background: #005f3c;
            border-radius: 20px;
        }

        .progress-note {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }

        /* ---------------------------------------------------------
           Summary
        --------------------------------------------------------- */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .summary-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
        }

        .summary-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .summary-value {
            font-size: 27px;
            font-weight: 700;
            color: #111827;
        }

        .summary-sub {
            font-size: 12px;
            color: #6b7280;
            margin-top: 6px;
        }

        /* ---------------------------------------------------------
           Tables
        --------------------------------------------------------- */

        .table-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 22px;
            overflow: hidden;
        }

        .table-header {
            padding: 20px 22px;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-header h2 {
            font-size: 18px;
            color: #111827;
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
            background: #f9fafb;
            color: #4b5563;
            font-size: 12px;
            text-align: left;
            padding: 13px 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid #f0f1f2;
            font-size: 13px;
            vertical-align: top;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fafcfb;
        }

        .entry-details {
            max-width: 320px;
            line-height: 1.45;
        }

        .badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-verified {
            background: #dcfce7;
            color: #166534;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-draft {
            background: #e5e7eb;
            color: #374151;
        }

        .empty-state {
            padding: 35px 20px;
            text-align: center;
            color: #6b7280;
        }

        .no-logbook {
            background: #fff7ed;
            color: #9a3412;
            border: 1px solid #fed7aa;
            padding: 16px 18px;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 14px;
        }

        /* ---------------------------------------------------------
           Responsive
        --------------------------------------------------------- */

        @media (max-width: 1100px) {
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .sidebar {
                position: relative;
                width: 100%;
            }

            .layout {
                display: block;
            }

            .main {
                margin-left: 0;
                width: 100%;
            }

            .topbar {
                padding: 16px 18px;
            }

            .content {
                padding: 20px 16px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .student-details {
                display: block;
            }

            .detail-item {
                margin-bottom: 12px;
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
</head>

<body>

<div class="layout">

    <aside class="sidebar">

        <div class="brand">
            <h2>SmartLog</h2>
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

    <main class="main">

        <header class="topbar">

            <h1>Student Progress Details</h1>

            <div class="topbar-user">
                <strong>{{ $user->name }}</strong>
                <span>Lecturer</span>
            </div>

        </header>

        <div class="content">

            <div class="back-row">

                <a
                    href="{{ route('web.lecturer.student-progress') }}"
                    class="back-link"
                >
                    &larr; Back to Student Progress
                </a>

            </div>

            <section class="student-card">

                <div class="student-card-top">

                    <div>

                        <div class="student-name">
                            {{ $student->name }}
                        </div>

                        <div class="student-id">
                            DWU ID:
                            {{ $student->dwu_id ?? 'Not available' }}
                        </div>

                        <div class="student-details">

                            <div class="detail-item">

                                <strong>Unit</strong>

                                <span>
                                    {{ $unit->unit_code }}
                                    -
                                    {{ $unit->unit_name }}
                                </span>

                            </div>

                            <div class="detail-item">

                                <strong>Email</strong>

                                <span>
                                    {{ $student->email ?? 'Not available' }}
                                </span>

                            </div>

                            <div class="detail-item">

                                <strong>Enrollment</strong>

                                <span>
                                    {{ $student->enrollment_status ?? 'N/A' }}
                                </span>

                            </div>

                        </div>

                    </div>

                    <div>

                        @if($logbook)

                            @php
                                $logbookStatus =
                                    strtoupper(
                                        (string) ($logbook->status ?? '')
                                    );
                            @endphp

                            <span
                                class="status-badge
                                {{ $logbookStatus === 'COMPLETED'
                                    ? 'status-completed'
                                    : 'status-active' }}"
                            >
                                {{ $logbook->status ?? 'ACTIVE' }}
                            </span>

                        @else

                            <span class="status-badge status-neutral">
                                NO LOGBOOK
                            </span>

                        @endif

                    </div>

                </div>

            </section>

            @if(!$logbook)

                <div class="no-logbook">
                    No logbook is currently assigned to this student
                    for this unit.
                </div>

            @else

                <section class="progress-card">

                    <div class="section-title">
                        {{ $logbook->template_name }}
                    </div>

                    <div class="progress-header">

                        <strong>Logbook Completion</strong>

                        <div class="progress-percent">
                            {{ number_format($completionPercentage, 1) }}%
                        </div>

                    </div>

                    <div class="progress-track">

                        <div
                            class="progress-fill"
                            style="width: {{ max(
                                0,
                                min(100, $completionPercentage)
                            ) }}%;"
                        ></div>

                    </div>

                    <div class="progress-note">

                        Required completion:
                        {{ number_format(
                            $minimumCompletionPercentage,
                            1
                        ) }}%

                        &nbsp;|&nbsp;

                        Progress status:
                        {{ str_replace(
                            '_',
                            ' ',
                            $completionStatus
                        ) }}

                        @if($logbook->due_date)

                            &nbsp;|&nbsp;

                            Due:
                            {{ $logbook->due_date }}

                        @endif

                    </div>

                </section>

            @endif

            <section class="summary-grid">

                <div class="summary-card">

                    <div class="summary-label">
                        Clinical Entries
                    </div>

                    <div class="summary-value">
                        {{ $summary['total_entries'] }}
                    </div>

                    <div class="summary-sub">
                        Total recorded
                    </div>

                </div>

                <div class="summary-card">

                    <div class="summary-label">
                        Verified Entries
                    </div>

                    <div class="summary-value">
                        {{ $summary['verified_entries'] }}
                    </div>

                    <div class="summary-sub">
                        Approved clinical entries
                    </div>

                </div>

                <div class="summary-card">

                    <div class="summary-label">
                        Pending Entries
                    </div>

                    <div class="summary-value">
                        {{ $summary['pending_entries'] }}
                    </div>

                    <div class="summary-sub">
                        Awaiting verification
                    </div>

                </div>

                <div class="summary-card">

                    <div class="summary-label">
                        Attendance
                    </div>

                    <div class="summary-value">
                        {{ $summary['verified_attendance_records'] }}
                    </div>

                    <div class="summary-sub">
                        {{ number_format(
                            $summary['verified_attendance_hours'],
                            1
                        ) }}
                        verified hours
                    </div>

                </div>

            </section>

            <section class="table-card">

                <div class="table-header">
                    <h2>Clinical Entries</h2>
                </div>

                @if($clinicalEntries->isEmpty())

                    <div class="empty-state">
                        No clinical entries recorded for this logbook.
                    </div>

                @else

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>
                                    <th>Date</th>
                                    <th>Facility</th>
                                    <th>Clinical Area</th>
                                    <th>Activity</th>
                                    <th>Competency</th>
                                    <th>Status</th>
                                </tr>

                            </thead>

                            <tbody>

                            @foreach($clinicalEntries as $entry)

                                @php
                                    $entryStatus =
                                        strtoupper(
                                            (string) $entry->status
                                        );

                                    $entryBadge =
                                        $entryStatus === 'VERIFIED'
                                            ? 'badge-verified'
                                            : (
                                                $entryStatus === 'REJECTED'
                                                    ? 'badge-rejected'
                                                    : (
                                                        $entryStatus === 'DRAFT'
                                                            ? 'badge-draft'
                                                            : 'badge-pending'
                                                    )
                                            );
                                @endphp

                                <tr>

                                    <td>
                                        {{ $entry->activity_date }}
                                    </td>

                                    <td>
                                        {{ $entry->facility_name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $entry->clinical_area ?? 'N/A' }}
                                    </td>

                                    <td class="entry-details">
                                        {{ $entry->activity_details ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $entry->competency_level ?? 'N/A' }}
                                    </td>

                                    <td>

                                        <span
                                            class="badge {{ $entryBadge }}"
                                        >
                                            {{ str_replace(
                                                '_',
                                                ' ',
                                                $entryStatus
                                            ) }}
                                        </span>

                                    </td>

                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>

                @endif

            </section>

            <section class="table-card">

                <div class="table-header">
                    <h2>Attendance Records</h2>
                </div>

                @if($attendanceRecords->isEmpty())

                    <div class="empty-state">
                        No attendance records recorded for this logbook.
                    </div>

                @else

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>
                                    <th>Date</th>
                                    <th>Facility</th>
                                    <th>Clinical Unit</th>
                                    <th>Start</th>
                                    <th>Finish</th>
                                    <th>Hours</th>
                                    <th>Status</th>
                                </tr>

                            </thead>

                            <tbody>

                            @foreach($attendanceRecords as $attendance)

                                @php
                                    $attendanceStatus =
                                        strtoupper(
                                            (string) $attendance->status
                                        );

                                    $attendanceBadge =
                                        $attendanceStatus === 'VERIFIED'
                                            ? 'badge-verified'
                                            : (
                                                $attendanceStatus === 'REJECTED'
                                                    ? 'badge-rejected'
                                                    : (
                                                        $attendanceStatus === 'DRAFT'
                                                            ? 'badge-draft'
                                                            : 'badge-pending'
                                                    )
                                            );
                                @endphp

                                <tr>

                                    <td>
                                        {{ $attendance->attendance_date }}
                                    </td>

                                    <td>
                                        {{ $attendance->facility_name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $attendance->clinical_unit ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $attendance->start_time ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $attendance->finish_time ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ number_format(
                                            (float) (
                                                $attendance->total_hours ?? 0
                                            ),
                                            1
                                        ) }}
                                    </td>

                                    <td>

                                        <span
                                            class="badge {{ $attendanceBadge }}"
                                        >
                                            {{ str_replace(
                                                '_',
                                                ' ',
                                                $attendanceStatus
                                            ) }}
                                        </span>

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



