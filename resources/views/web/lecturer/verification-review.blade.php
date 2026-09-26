<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Review Verification | SmartLog</title>

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

        .page {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: #0b5d3b;
            color: white;
            padding: 28px 20px;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
        }

        .brand {
            margin-bottom: 35px;
        }

        .brand h1 {
            font-size: 27px;
            margin-bottom: 5px;
        }

        .brand p {
            font-size: 13px;
            color: #d7eee3;
        }

        .role-badge {
            display: inline-block;
            margin-top: 12px;
            padding: 6px 11px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .nav {
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
            min-height: 100vh;
        }

        .topbar {
            min-height: 74px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
        }

        .topbar h2 {
            color: #0b5d3b;
            font-size: 21px;
            margin-bottom: 3px;
        }

        .topbar p {
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
            border: none;
            background: #f1f5f3;
            color: #0b5d3b;
            padding: 9px 13px;
            border-radius: 7px;
            cursor: pointer;
            font-weight: bold;
        }

        .content {
            padding: 30px;
            max-width: 1300px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0b5d3b;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }

        .page-heading {
            margin-bottom: 24px;
        }

        .page-heading h1 {
            font-size: 27px;
            margin-bottom: 7px;
        }

        .page-heading p {
            color: #6b7280;
            line-height: 1.5;
        }

        .notice {
            background: #eef8f2;
            border-left: 4px solid #0b5d3b;
            padding: 15px 17px;
            border-radius: 7px;
            margin-bottom: 24px;
            color: #315a47;
            line-height: 1.5;
            font-size: 14px;
        }

        .alert {
            margin-bottom: 20px;
            padding: 14px 16px;
            border-radius: 8px;
            font-size: 14px;
        }

        .alert-error {
            background: #fff0f0;
            color: #9b2c2c;
            border: 1px solid #f2c1c1;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.03);
        }

        .card h3 {
            color: #0b5d3b;
            font-size: 18px;
            margin-bottom: 18px;
        }

        .detail-row {
            padding: 10px 0;
            border-bottom: 1px solid #edf0ee;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            display: block;
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 600;
            line-height: 1.5;
            word-break: break-word;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-pending {
            background: #fff4d6;
            color: #8a6100;
        }

        .status-approved {
            background: #e9f8ef;
            color: #17603a;
        }

        .status-rejected {
            background: #fff0f0;
            color: #9b2c2c;
        }

        .evidence-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .evidence-box {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            background: #fafcfb;
        }

        .evidence-title {
            padding: 12px 14px;
            font-weight: bold;
            font-size: 13px;
            border-bottom: 1px solid #e5e7eb;
            background: #f8faf9;
        }

        .evidence-image {
            min-height: 230px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }

        .evidence-image img {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            border-radius: 6px;
        }

        .no-evidence {
            color: #6b7280;
            font-size: 13px;
            text-align: center;
            line-height: 1.5;
        }

        .review-card {
            margin-top: 20px;
        }

        textarea {
            width: 100%;
            min-height: 120px;
            resize: vertical;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px;
            font-family: inherit;
            font-size: 14px;
            margin-top: 8px;
        }

        textarea:focus {
            outline: none;
            border-color: #0b5d3b;
        }

        .button-row {
            display: flex;
            gap: 12px;
            margin-top: 18px;
        }

        .button {
            border: none;
            border-radius: 8px;
            padding: 11px 18px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }

        .approve {
            background: #0b5d3b;
            color: white;
        }

        .approve:hover {
            background: #084a2f;
        }

        .reject {
            background: #b42318;
            color: white;
        }

        .reject:hover {
            background: #8f1c14;
        }

        .reviewed-message {
            margin-top: 15px;
            padding: 14px;
            background: #f8faf9;
            border-radius: 8px;
            color: #4b5563;
            line-height: 1.5;
        }

        .face-note {
            margin-top: 15px;
            padding: 13px;
            border-radius: 8px;
            background: #fffbea;
            color: #715b10;
            font-size: 13px;
            line-height: 1.5;
        }

        @media (max-width: 1000px) {
            .grid,
            .evidence-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 800px) {
            .sidebar {
                width: 210px;
            }

            .main {
                margin-left: 210px;
                width: calc(100% - 210px);
            }
        }

        @media (max-width: 700px) {
            .page {
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
                gap: 15px;
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
                <p>Verification evidence review</p>
            </div>

            <div class="user-area">

                <div class="user-info">
                    <strong>{{ $user->name }}</strong>
                    <span>{{ $user->dwu_id ?? $user->email }}</span>
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


        <section class="content">

            <a
                href="{{ route('web.lecturer.verifications') }}"
                class="back-link"
            >
                ← Back to Pending Verifications
            </a>


            @if (session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif


            @if ($errors->any())
                <div class="alert alert-error">
                    {{ $errors->first() }}
                </div>
            @endif


            <div class="page-heading">

                <h1>Review Verification</h1>

                <p>
                    Review the student activity and supervisor evidence
                    before making your final decision.
                </p>

            </div>


            <div class="notice">
                Supervisor signature, face capture, face comparison and
                timestamps are supporting evidence. Face comparison does
                not automatically approve or reject this record. The
                lecturer makes the final verification decision.
            </div>


            <div class="grid">

                <div class="card">

                    <h3>Student & Unit</h3>

                    <div class="detail-row">
                        <span class="detail-label">Student</span>
                        <div class="detail-value">
                            {{ $verification->student_name }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">DWU ID</span>
                        <div class="detail-value">
                            {{ $verification->student_dwu_id }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Email</span>
                        <div class="detail-value">
                            {{ $verification->student_email ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Unit</span>
                        <div class="detail-value">
                            {{ $verification->unit_code }}
                            -
                            {{ $verification->unit_name }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Verification Type</span>
                        <div class="detail-value">
                            {{ $verification->verification_type === 'CLINICAL_ENTRY'
                                ? 'Clinical Entry'
                                : 'Attendance Record' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Verification Status</span>

                        <div class="detail-value">

                            @if ($verification->verification_status === 'MANUAL_REVIEW')

                                <span class="status status-pending">
                                    PENDING REVIEW
                                </span>

                            @elseif ($verification->verification_status === 'APPROVED')

                                <span class="status status-approved">
                                    APPROVED
                                </span>

                            @else

                                <span class="status status-rejected">
                                    {{ $verification->verification_status }}
                                </span>

                            @endif

                        </div>
                    </div>

                </div>


                <div class="card">

                    <h3>Supervisor</h3>

                    <div class="detail-row">
                        <span class="detail-label">Supervisor Name</span>

                        <div class="detail-value">
                            {{
                                $verification->registered_supervisor_name
                                ?? $verification->supervisor_name
                                ?? 'Not recorded'
                            }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Profession</span>

                        <div class="detail-value">
                            {{ $verification->supervisor_profession ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Position</span>

                        <div class="detail-value">
                            {{ $verification->supervisor_position_title ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Registration Number</span>

                        <div class="detail-value">
                            {{ $verification->supervisor_registration_number ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Registered Facility</span>

                        <div class="detail-value">
                            {{
                                $verification->registered_supervisor_facility
                                ?? $verification->facility_name
                                ?? 'Not recorded'
                            }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Verification Method</span>

                        <div class="detail-value">
                            {{ $verification->verification_method ?: 'Not recorded' }}
                        </div>
                    </div>

                </div>

            </div>


            <div class="card">

                <h3>Activity / Attendance Details</h3>

                @if ($verification->verification_type === 'CLINICAL_ENTRY')

                    <div class="detail-row">
                        <span class="detail-label">Activity Date</span>

                        <div class="detail-value">
                            {{ $verification->activity_date ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Activity Time</span>

                        <div class="detail-value">
                            {{ $verification->activity_time ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Facility</span>

                        <div class="detail-value">
                            {{ $verification->entry_facility_name ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Clinical Area</span>

                        <div class="detail-value">
                            {{ $verification->clinical_area ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Activity Details</span>

                        <div class="detail-value">
                            {{ $verification->activity_details ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Competency Level</span>

                        <div class="detail-value">
                            {{ $verification->competency_level ?: 'Not recorded' }}
                        </div>
                    </div>

                @else

                    <div class="detail-row">
                        <span class="detail-label">Attendance Date</span>

                        <div class="detail-value">
                            {{ $verification->attendance_date ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Facility</span>

                        <div class="detail-value">
                            {{ $verification->facility_name ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Clinical Unit</span>

                        <div class="detail-value">
                            {{ $verification->clinical_unit ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Start Time</span>

                        <div class="detail-value">
                            {{ $verification->start_time ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Finish Time</span>

                        <div class="detail-value">
                            {{ $verification->finish_time ?: 'Not recorded' }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Total Hours</span>

                        <div class="detail-value">
                            {{ $verification->total_hours ?? 'Not recorded' }}
                        </div>
                    </div>

                @endif

            </div>


            <div class="card" style="margin-top: 20px;">

                <h3>Verification Evidence</h3>

                <div class="evidence-grid">

                    <div class="evidence-box">

                        <div class="evidence-title">
                            Supervisor Signature
                        </div>

                        <div class="evidence-image">

                            @if ($signatureUrl)

                                <img
                                    src="{{ $signatureUrl }}"
                                    alt="Supervisor signature"
                                >

                            @else

                                <div class="no-evidence">
                                    No supervisor signature image is
                                    available for this verification.
                                </div>

                            @endif

                        </div>

                    </div>


                    <div class="evidence-box">

                        <div class="evidence-title">
                            Captured Supervisor Face
                        </div>

                        <div class="evidence-image">

                            @if ($faceCaptureUrl)

                                <img
                                    src="{{ $faceCaptureUrl }}"
                                    alt="Captured supervisor face"
                                >

                            @else

                                <div class="no-evidence">
                                    No captured face image is available
                                    for this verification.
                                </div>

                            @endif

                        </div>

                    </div>


                    <div class="evidence-box">

                        <div class="evidence-title">
                            Registered Reference Face
                        </div>

                        <div class="evidence-image">

                            @if ($referenceFaceUrl)

                                <img
                                    src="{{ $referenceFaceUrl }}"
                                    alt="Registered supervisor reference face"
                                >

                            @else

                                <div class="no-evidence">
                                    No registered reference face is
                                    available for this supervisor.
                                </div>

                            @endif

                        </div>

                    </div>

                </div>


                <div class="face-note">

                    <strong>Face comparison:</strong>

                    {{ $verification->face_comparison_decision ?: 'No comparison decision recorded' }}

                    @if ($verification->face_match_score !== null)
                        |
                        Score:
                        {{ $verification->face_match_score }}
                    @endif

                    @if ($verification->face_lbph_distance !== null)
                        |
                        LBPH Distance:
                        {{ $verification->face_lbph_distance }}
                    @endif

                    <br><br>

                    This information is supporting evidence only and
                    does not make the final approval decision.

                </div>


                <div class="grid" style="margin-top: 18px; margin-bottom: 0;">

                    <div>

                        <div class="detail-row">
                            <span class="detail-label">
                                Activity Timestamp
                            </span>

                            <div class="detail-value">
                                {{ $verification->activity_timestamp ?: 'Not recorded' }}
                            </div>
                        </div>

                    </div>


                    <div>

                        <div class="detail-row">
                            <span class="detail-label">
                                Verification Timestamp
                            </span>

                            <div class="detail-value">
                                {{ $verification->verification_timestamp ?: 'Not recorded' }}
                            </div>
                        </div>

                    </div>

                </div>

            </div>


            <div class="card review-card">

                <h3>Lecturer Decision</h3>

                @if ($verification->verification_status === 'MANUAL_REVIEW')

                    <form
                        method="POST"
                        action="{{ route(
                            'web.lecturer.verification.submit',
                            [
                                'verificationId' => $verification->id
                            ]
                        ) }}"
                    >

                        @csrf

                        <label for="comment">
                            <strong>Review Comment</strong>
                        </label>

                        <textarea
                            id="comment"
                            name="comment"
                            maxlength="1000"
                            placeholder="Enter a comment about this verification if needed."
                        >{{ old('comment') }}</textarea>


                        <div class="button-row">

                            <button
                                type="submit"
                                name="decision"
                                value="APPROVED"
                                class="button approve"
                                onclick="return confirm('Approve this verification?');"
                            >
                                Approve Verification
                            </button>


                            <button
                                type="submit"
                                name="decision"
                                value="REJECTED"
                                class="button reject"
                                onclick="return confirm('Reject this verification?');"
                            >
                                Reject Verification
                            </button>

                        </div>

                    </form>

                @else

                    <div class="reviewed-message">

                        <strong>
                            This verification has already been reviewed.
                        </strong>

                        <br><br>

                        Decision:
                        {{ $verification->verification_status }}

                        @if ($verification->reviewer_name)
                            <br>
                            Reviewed by:
                            {{ $verification->reviewer_name }}
                        @endif

                        @if ($verification->reviewed_at)
                            <br>
                            Reviewed at:
                            {{ $verification->reviewed_at }}
                        @endif

                        @if ($verification->review_comment)
                            <br><br>
                            Comment:
                            {{ $verification->review_comment }}
                        @endif

                    </div>

                @endif

            </div>

        </section>

    </main>

</div>

</body>
</html>