<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Students | SmartLog</title>

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
            border-bottom: 1px solid rgba(255,255,255,0.15);
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
            background: rgba(255,255,255,0.14);
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
            background: rgba(255,255,255,0.14);
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
            margin-bottom: 24px;
        }

        .page-heading h3 {
            color: #174331;
            font-size: 24px;
            margin-bottom: 7px;
        }

        .page-heading p {
            color: #74827b;
            font-size: 14px;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .summary-card {
            background: white;
            border: 1px solid #e1e9e5;
            border-radius: 11px;
            padding: 20px;
        }

        .summary-label {
            color: #7d8983;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .summary-value {
            color: #006b45;
            font-size: 27px;
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
        }

        .section-heading h3 {
            color: #174331;
            font-size: 18px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
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

        tbody tr:hover {
            background: #fafcfb;
        }

        .student-name {
            color: #174331;
            font-weight: bold;
        }

        .email {
            color: #7d8983;
            font-size: 11px;
            margin-top: 4px;
        }

        .unit-code {
            font-weight: bold;
            color: #006b45;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        .active {
            background: #e5f5ec;
            color: #087445;
        }

        .other {
            background: #f0f2f1;
            color: #68756f;
        }

        .progress-container {
            min-width: 130px;
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 6px;
        }

        .progress-bar {
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

        .view-button {
            display: inline-block;
            text-decoration: none;
            background: #006b45;
            color: white;
            padding: 8px 11px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
        }

        .empty {
            padding: 45px;
            text-align: center;
            color: #77847d;
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

        <nav class="navigation">

            <a href="{{ route('web.lecturer.dashboard') }}">
                Dashboard
            </a>

            <a href="{{ route('web.lecturer.units') }}">
                My Units
            </a>

            <a
                href="{{ route('web.lecturer.students') }}"
                class="active"
            >
                Students
            </a>

            <a href="{{ route('web.lecturer.verifications') }}">Pending Verifications</a>

            <a href="#">
                Student Progress
            </a>

        </nav>

    </aside>


    <main class="main">

        <header class="topbar">

            <div class="topbar-title">
                <h2>Students</h2>
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

                <h3>My Students</h3>

                <p>
                    Students enrolled in practical units assigned
                    to your Lecturer account.
                </p>

            </div>


            <section class="summary">

                <div class="summary-card">

                    <div class="summary-label">
                        Students
                    </div>

                    <div class="summary-value">
                        {{ $uniqueStudentsCount }}
                    </div>

                </div>


                <div class="summary-card">

                    <div class="summary-label">
                        Active Enrolments
                    </div>

                    <div class="summary-value">
                        {{ $activeEnrollmentsCount }}
                    </div>

                </div>


                <div class="summary-card">

                    <div class="summary-label">
                        Assigned Units
                    </div>

                    <div class="summary-value">
                        {{ $assignedUnitsCount }}
                    </div>

                </div>

            </section>


            <section class="students-section">

                <div class="section-heading">

                    <h3>
                        Student Enrolments
                    </h3>

                </div>


                @if ($students->isEmpty())

                    <div class="empty">

                        No students are currently enrolled
                        in your assigned units.

                    </div>

                @else

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>
                                    <th>DWU ID</th>
                                    <th>Student</th>
                                    <th>Unit</th>
                                    <th>Enrolment</th>
                                    <th>Logbook</th>
                                    <th>Progress</th>
                                    <th>Action</th>
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

                                            <div class="email">
                                                {{ $student->email }}
                                            </div>

                                        </td>

                                        <td>

                                            <div class="unit-code">
                                                {{ $student->unit_code }}
                                            </div>

                                            <div class="email">
                                                {{ $student->unit_name }}
                                            </div>

                                        </td>

                                        <td>

                                            <span
                                                class="status {{
                                                    strtoupper(
                                                        (string) $student->enrollment_status
                                                    ) === 'ACTIVE'
                                                        ? 'active'
                                                        : 'other'
                                                }}"
                                            >
                                                {{ $student->enrollment_status ?? 'UNKNOWN' }}
                                            </span>

                                        </td>

                                        <td>

                                            @if ($student->student_logbook_id)

                                                <span
                                                    class="status {{
                                                        strtoupper(
                                                            (string) $student->logbook_status
                                                        ) === 'ACTIVE'
                                                            ? 'active'
                                                            : 'other'
                                                    }}"
                                                >
                                                    {{ $student->logbook_status ?? 'ASSIGNED' }}
                                                </span>

                                            @else

                                                <span class="status other">
                                                    NO LOGBOOK
                                                </span>

                                            @endif

                                        </td>

                                        <td>

                                            @if ($student->student_logbook_id)

                                                <div class="progress-container">

                                                    <div class="progress-text">

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

                                                Not available

                                            @endif

                                        </td>

                                        <td>

                                            <a
                                                href="{{ route(
                                                    'web.lecturer.unit.students',
                                                    ['unitId' => $student->unit_id]
                                                ) }}"
                                                class="view-button"
                                            >
                                                View Unit
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
