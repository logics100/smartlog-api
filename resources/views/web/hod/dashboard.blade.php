<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>HOD Dashboard | SmartLog</title>

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

        /* SIDEBAR */

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

        .readonly-badge {
            display: inline-block;
            margin-top: 9px;
            padding: 5px 10px;
            border-radius: 20px;
            background: #ffffff;
            color: #005f3c;
            font-size: 11px;
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

        /* MAIN */

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

        /* CONTENT */

        .content {
            padding: 30px;
        }

        .welcome {
            background: linear-gradient(135deg, #007a4d, #005f3c);
            color: white;
            padding: 30px;
            border-radius: 14px;
            margin-bottom: 28px;
        }

        .welcome h3 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .welcome p {
            color: #dcf4e9;
            line-height: 1.6;
            font-size: 14px;
        }

        .notice {
            margin-top: 18px;
            padding: 12px 14px;
            background: rgba(255, 255, 255, 0.13);
            border-radius: 8px;
            font-size: 13px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        .card {
            background: white;
            padding: 23px;
            border-radius: 12px;
            border: 1px solid #e3eae6;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.03);
        }

        .card-label {
            color: #74827b;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .card-value {
            color: #075d3d;
            font-size: 30px;
            font-weight: bold;
        }

        .card-note {
            color: #96a099;
            font-size: 12px;
            margin-top: 7px;
        }

        .section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e3eae6;
        }

        .section h3 {
            color: #174331;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .section > p {
            color: #6f7d76;
            font-size: 14px;
            line-height: 1.6;
        }

        .feature-grid {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .feature {
            background: #f6faf8;
            border: 1px solid #e2ece7;
            padding: 18px;
            border-radius: 9px;
        }

        .feature strong {
            display: block;
            color: #12603f;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .feature span {
            color: #75817b;
            font-size: 13px;
            line-height: 1.5;
        }

        @media (max-width: 900px) {
            .sidebar {
                width: 210px;
            }

            .main {
                margin-left: 210px;
                width: calc(100% - 210px);
            }

            .cards,
            .feature-grid {
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
        }
    </style>
</head>

<body>

<div class="app">

    <aside class="sidebar">

        <div class="brand">
            <h1>SmartLog</h1>

            <p>DWU Clinical Logbook</p>

            <span class="role-badge">HOD</span>
            <br>
            <span class="readonly-badge">READ ONLY</span>
        </div>

        <nav class="navigation">
            <a href="#" class="active">Dashboard</a>
            <a href="#">Department Students</a>
            <a href="#">Department Units</a>
            <a href="#">Year Levels</a>
            <a href="#">Lecturers</a>
            <a href="#">Clinical Progress</a>
        </nav>

    </aside>


    <main class="main">

        <header class="topbar">

            <div class="topbar-title">
                <h2>HOD Dashboard</h2>
                <p>Department Clinical Monitoring Portal</p>
            </div>

            <div class="user-area">

                <div class="user-info">
                    <strong>{{ $user->name }}</strong>
                    <span>{{ $user->dwu_id }}</span>
                </div>

                <form method="POST" action="{{ route('web.logout') }}">
                    @csrf

                    <button type="submit" class="logout-button">
                        Logout
                    </button>
                </form>

            </div>

        </header>


        <div class="content">

            <section class="welcome">

                <h3>Welcome, {{ $user->name }}</h3>

                <p>
                    Use the SmartLog HOD Portal to monitor clinical
                    activities and academic progress within your department.
                </p>

                <div class="notice">
                    HOD access is read-only. Clinical verification approval
                    and rejection remain the responsibility of the Lecturer.
                </div>

            </section>


            <section class="cards">

                <div class="card">
                    <div class="card-label">
                        Department Students
                    </div>

                    <div class="card-value">—</div>

                    <div class="card-note">
                        Department data will be connected next.
                    </div>
                </div>


                <div class="card">
                    <div class="card-label">
                        Department Units
                    </div>

                    <div class="card-value">—</div>

                    <div class="card-note">
                        Unit data will be connected next.
                    </div>
                </div>


                <div class="card">
                    <div class="card-label">
                        Department Lecturers
                    </div>

                    <div class="card-value">—</div>

                    <div class="card-note">
                        Lecturer data will be connected next.
                    </div>
                </div>

            </section>


            <section class="section">

                <h3>Department Monitoring</h3>

                <p>
                    Information displayed here will come from the same
                    SmartLog database used by the student mobile application
                    and Lecturer Portal.
                </p>

                <div class="feature-grid">

                    <div class="feature">
                        <strong>Student Progress</strong>
                        <span>
                            View the clinical progress of students within
                            your department.
                        </span>
                    </div>


                    <div class="feature">
                        <strong>Unit Progress</strong>
                        <span>
                            Monitor clinical logbook progress across
                            department units.
                        </span>
                    </div>


                    <div class="feature">
                        <strong>Year Level Monitoring</strong>
                        <span>
                            View students and progress information by
                            year level.
                        </span>
                    </div>


                    <div class="feature">
                        <strong>Lecturer Monitoring</strong>
                        <span>
                            View lecturers and the clinical units associated
                            with your department.
                        </span>
                    </div>

                </div>

            </section>

        </div>

    </main>

</div>

</body>
</html>