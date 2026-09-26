<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ICT Admin Dashboard | SmartLog</title>

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
            color: #ffffff;
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
            color: #ffffff;
        }

        /* MAIN */

        .main {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-height: 100vh;
        }

        .topbar {
            background: #ffffff;
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
            color: #ffffff;
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
            line-height: 1.5;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        .card {
            background: #ffffff;
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
            background: #ffffff;
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

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="brand">

            <h1>SmartLog</h1>

            <p>DWU Clinical Logbook</p>

            <span class="role-badge">
                ICT ADMIN
            </span>

        </div>


        <nav class="navigation">

            <a href="#" class="active">
                Dashboard
            </a>

            <a href="#">
                User Management
            </a>

            <a href="#">
                Departments
            </a>

            <a href="#">
                Units
            </a>

            <a href="#">
                System Overview
            </a>

        </nav>

    </aside>


    <!-- MAIN -->
    <main class="main">

        <!-- TOP BAR -->
        <header class="topbar">

            <div class="topbar-title">

                <h2>ICT Admin Dashboard</h2>

                <p>
                    SmartLog System Administration
                </p>

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


        <!-- CONTENT -->
        <div class="content">

            <section class="welcome">

                <h3>
                    Welcome, {{ $user->name }}
                </h3>

                <p>
                    Use the SmartLog ICT Administration Portal to manage
                    system users, departments, units and general SmartLog
                    configuration.
                </p>

                <div class="notice">
                    ICT Admin manages the SmartLog system and user accounts.
                    Clinical verification approval and rejection remain the
                    responsibility of the Lecturer.
                </div>

            </section>


            <!-- PLACEHOLDER SUMMARY CARDS -->
            <section class="cards">

                <div class="card">

                    <div class="card-label">
                        SmartLog Users
                    </div>

                    <div class="card-value">
                        —
                    </div>

                    <div class="card-note">
                        User data will be connected next.
                    </div>

                </div>


                <div class="card">

                    <div class="card-label">
                        Departments
                    </div>

                    <div class="card-value">
                        —
                    </div>

                    <div class="card-note">
                        Department data will be connected next.
                    </div>

                </div>


                <div class="card">

                    <div class="card-label">
                        Units
                    </div>

                    <div class="card-value">
                        —
                    </div>

                    <div class="card-note">
                        Unit data will be connected next.
                    </div>

                </div>

            </section>


            <section class="section">

                <h3>
                    System Administration
                </h3>

                <p>
                    The web portal uses the same SmartLog database as the
                    mobile application, allowing ICT administration without
                    creating a separate system.
                </p>


                <div class="feature-grid">

                    <div class="feature">

                        <strong>
                            User Management
                        </strong>

                        <span>
                            Create and manage SmartLog Student, Lecturer,
                            HOD and ICT Admin accounts.
                        </span>

                    </div>


                    <div class="feature">

                        <strong>
                            Account Status
                        </strong>

                        <span>
                            Activate or deactivate SmartLog user accounts
                            for system access.
                        </span>

                    </div>


                    <div class="feature">

                        <strong>
                            Department Management
                        </strong>

                        <span>
                            Manage the academic departments participating
                            in SmartLog.
                        </span>

                    </div>


                    <div class="feature">

                        <strong>
                            Unit Management
                        </strong>

                        <span>
                            Manage clinical and practical units used by
                            SmartLog.
                        </span>

                    </div>

                </div>

            </section>

        </div>

    </main>

</div>

</body>
</html>