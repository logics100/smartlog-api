<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Verifications | SmartLog</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7f5;
            color: #1f2937;
        }

        .page {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background: #0b5d3b;
            color: white;
            padding: 28px 20px;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
        }

        .brand h1 {
            margin: 0 0 5px;
            font-size: 27px;
        }

        .brand p {
            margin: 0;
            color: #d7eee3;
            font-size: 13px;
        }

        .role-badge {
            display: inline-block;
            margin-top: 12px;
            padding: 6px 11px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.15);
            font-size: 12px;
            font-weight: bold;
        }

        .nav {
            margin-top: 35px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .nav a {
            color: #e8f5ee;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 14px;
        }

        .nav a:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .nav a.active {
            background: white;
            color: #0b5d3b;
            font-weight: bold;
        }

        .main {
            margin-left: 250px;
            width: calc(100% - 250px);
        }

        .topbar {
            min-height: 74px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar h2 {
            margin: 0 0 4px;
            color: #0b5d3b;
            font-size: 21px;
        }

        .topbar p {
            margin: 0;
            color: #6b7280;
            font-size: 13px;
        }

        .user-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            text-align: right;
        }

        .user-info strong {
            display: block;
            font-size: 14px;
        }

        .user-info span {
            color: #6b7280;
            font-size: 12px;
        }

        .logout-button {
            border: 0;
            padding: 9px 13px;
            border-radius: 7px;
            background: #f1f5f3;
            color: #0b5d3b;
            cursor: pointer;
            font-weight: bold;
        }

        .content {
            padding: 30px;
        }

        .heading h1 {
            margin: 0 0 7px;
            font-size: 27px;
        }

        .heading p {
            margin: 0;
            color: #6b7280;
        }

        .notice {
            margin: 22px 0;
            padding: 15px;
            background: #eef8f2;
            border-left: 4px solid #0b5d3b;
            border-radius: 7px;
            color: #315a47;
            font-size: 14px;
            line-height: 1.5;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .summary-card {
            background: white;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .summary-card span {
            color: #6b7280;
            font-size: 13px;
        }

        .summary-card strong {
            display: block;
            margin-top: 8px;
            color: #0b5d3b;
            font-size: 29px;
        }

        .card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .card-header h3 {
            margin: 0 0 5px;
        }

        .card-header p {
            margin: 0;
            color: #6b7280;
            font-size: 13px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
        }

        th {
            padding: 13px 15px;
            text-align: left;
            background: #f8faf9;
            color: #4b5563;
            font-size: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #edf0ee;
            font-size: 14px;
            vertical-align: middle;
        }

        .name {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .small {
            color: #6b7280;
            font-size: 12px;
        }

        .badge {
            display: inline-block;
            padding: 6px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        .clinical {
            background: #e8f4ff;
            color: #185a8d;
        }

        .attendance {
            background: #f3edff;
            color: #6843a3;
        }

        .pending {
            background: #fff4d6;
            color: #8a6100;
        }

        .review {
            display: inline-block;
            padding: 9px 13px;
            border-radius: 7px;
            background: #0b5d3b;
            color: white;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
        }

        .empty {
            padding: 50px 20px;
            text-align: center;
        }

        .empty h3 {
            color: #0b5d3b;
        }

        .empty p {
            color: #6b7280;
        }

        .alert {
            margin: 20px 0;
            padding: 14px;
            border-radius: 8px;
        }

        .success {
            background: #e9f8ef;
            color: #17603a;
        }

        .error {
            background: #fff0f0;
            color: #9b2c2c;
        }

        @media (max-width: 800px) {
            .sidebar {
                position: relative;
                width: 100%;
            }

            .page {
                display: block;
            }

            .main {
                margin-left: 0;
                width: 100%;
            }

            .summary {
                grid-template-columns: 1fr;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
            }

            .user-info {
                text-align: left;
            }
        }
    </style>
</head>

<body>

<div class="page">

    <aside class="sidebar">

        <div class="brand">
            <h1>SmartLog</h1>
            <p>DWU Smart Clinical Logbook</p>
            <span class="role-badge">LECTURER</span>
        </div>

        <nav class="nav">

            <a href="{{ route('web.lecturer.dashboard') }}">
                Dashboard
            </a>

            <a href="{{ route('web.lecturer.units') }}">
                My Units
            </a>

            <a href="{{ route('web.lecturer.students') }}">
                Students
            </a>

            <a
                href="{{ route('web.lecturer.verifications') }}"
                class="active"
            >
                Pending Verifications
            </a>

            <a href="#">
                Student Progress
            </a>

        </nav>

    </aside>


    <main class="main">

        <header class="topbar">

            <div>
                <h2>Lecturer Portal</h2>
                <p>Supervisor verification review</p>
            </div>

            <div class="user-area">

                <div class="user-info">
                    <strong>{{ $user->name }}</strong>
                    <span>{{ $user->dwu_id ?? $user->email }}</span>
                </div>

                <form method="POST" action="{{ route('web.logout') }}">
                    @csrf

                    <button type="submit" class="logout-button">
                        Logout
                    </button>
                </form>

            </div>

        </header>


        <section class="content">

            <div class="heading">
                <h1>Pending Verifications</h1>

                <p>
                    Review supervisor verification requests from students
                    in your assigned units.
                </p>
            </div>


            @if (session('success'))
                <div class="alert success">
                    {{ session('success') }}
                </div>
            @endif


            @if (session('error'))
                <div class="alert error">
                    {{ session('error') }}
                </div>
            @endif


            <div class="notice">
                Face comparison, supervisor signature and captured evidence
                are supporting information only. The lecturer makes the
                final approval or rejection decision.
            </div>


            <div class="summary">

                <div class="summary-card">
                    <span>Total Pending</span>
                    <strong>{{ $pendingCount }}</strong>
                </div>

                <div class="summary-card">
                    <span>Clinical Entries</span>
                    <strong>{{ $clinicalCount }}</strong>
                </div>

                <div class="summary-card">
                    <span>Attendance Records</span>
                    <strong>{{ $attendanceCount }}</strong>
                </div>

            </div>


            <div class="card">

                <div class="card-header">
                    <h3>Waiting for Lecturer Review</h3>

                    <p>
                        Only pending requests from your assigned units
                        are displayed.
                    </p>
                </div>


                @if ($verifications->isEmpty())

                    <div class="empty">
                        <h3>No Pending Verifications</h3>

                        <p>
                            There are currently no verification requests
                            waiting for your review.
                        </p>
                    </div>

                @else

                    <div class="table-wrapper">

                        <table>

                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Unit</th>
                                    <th>Type</th>
                                    <th>Activity / Attendance</th>
                                    <th>Facility</th>
                                    <th>Supervisor</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($verifications as $verification)

                                    @php
                                        $isClinical =
                                            $verification->verification_type ===
                                            'CLINICAL_ENTRY';

                                        $supervisorName =
                                            $verification->registered_supervisor_name
                                            ?? $verification->captured_supervisor_name
                                            ?? 'Not recorded';
                                    @endphp

                                    <tr>

                                        <td>
                                            <div class="name">
                                                {{ $verification->student_name }}
                                            </div>

                                            <div class="small">
                                                {{ $verification->student_dwu_id }}
                                            </div>
                                        </td>


                                        <td>
                                            <div class="name">
                                                {{ $verification->unit_code }}
                                            </div>

                                            <div class="small">
                                                {{ $verification->unit_name }}
                                            </div>
                                        </td>


                                        <td>

                                            @if ($isClinical)

                                                <span class="badge clinical">
                                                    CLINICAL ENTRY
                                                </span>

                                            @else

                                                <span class="badge attendance">
                                                    ATTENDANCE
                                                </span>

                                            @endif

                                        </td>


                                        <td>

                                            @if ($isClinical)

                                                <div class="name">
                                                    {{
                                                        $verification->procedure_name
                                                        ?: 'Clinical activity'
                                                    }}
                                                </div>

                                                <div class="small">
                                                    {{
                                                        $verification->activity_date
                                                        ?: 'Date not recorded'
                                                    }}
                                                </div>

                                            @else

                                                <div class="name">
                                                    {{
                                                        $verification->clinical_unit
                                                        ?: 'Clinical attendance'
                                                    }}
                                                </div>

                                                <div class="small">
                                                    {{
                                                        $verification->attendance_date
                                                        ?: 'Date not recorded'
                                                    }}
                                                </div>

                                            @endif

                                        </td>


                                        <td>
                                            {{
                                                $verification->facility_name
                                                ?: 'Not recorded'
                                            }}
                                        </td>


                                        <td>

                                            <div class="name">
                                                {{ $supervisorName }}
                                            </div>

                                            @if (
                                                $verification
                                                    ->supervisor_registration_number
                                            )

                                                <div class="small">
                                                    Reg:
                                                    {{
                                                        $verification
                                                            ->supervisor_registration_number
                                                    }}
                                                </div>

                                            @elseif (
                                                $verification
                                                    ->supervisor_profession
                                            )

                                                <div class="small">
                                                    {{
                                                        $verification
                                                            ->supervisor_profession
                                                    }}
                                                </div>

                                            @endif

                                        </td>


                                        <td>
                                            <span class="badge pending">
                                                PENDING REVIEW
                                            </span>
                                        </td>


                                        <td>

                                            <a
                                                class="review"
                                                href="{{
                                                    route(
                                                        'web.lecturer.verification.review',
                                                        [
                                                            'verificationId' =>
                                                                $verification
                                                                    ->verification_id
                                                        ]
                                                    )
                                                }}"
                                            >
                                                Review
                                            </a>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @endif

            </div>

        </section>

    </main>

</div>

</body>
</html>