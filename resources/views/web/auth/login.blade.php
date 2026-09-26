<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SmartLog Web Portal | DWU</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background:
                linear-gradient(
                    135deg,
                    rgba(0, 70, 45, 0.96),
                    rgba(0, 105, 65, 0.90)
                );
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .page-wrapper {
            width: 100%;
            max-width: 1000px;
        }

        .portal-card {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.25);
        }

        /* LEFT SIDE */

        .welcome-panel {
            background:
                linear-gradient(
                    145deg,
                    #006b45,
                    #004d34
                );
            color: #ffffff;
            padding: 60px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 600px;
        }

        .smartlog-badge {
            display: inline-block;
            width: fit-content;
            padding: 8px 16px;
            margin-bottom: 28px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.10);
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .welcome-panel h1 {
            font-size: 48px;
            line-height: 1.05;
            margin-bottom: 18px;
        }

        .welcome-panel h2 {
            font-size: 21px;
            font-weight: normal;
            line-height: 1.5;
            margin-bottom: 24px;
            color: #e8fff5;
        }

        .welcome-panel p {
            font-size: 15px;
            line-height: 1.7;
            color: #d4f3e5;
            max-width: 430px;
        }

        .role-list {
            margin-top: 35px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .role {
            padding: 9px 14px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 13px;
        }

        /* RIGHT SIDE */

        .login-panel {
            padding: 60px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 600px;
        }

        .login-panel h3 {
            color: #14372b;
            font-size: 30px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 32px;
        }

        .alert {
            padding: 13px 15px;
            border-radius: 9px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert-error {
            color: #842029;
            background: #f8d7da;
            border: 1px solid #f5c2c7;
        }

        .alert-success {
            color: #0f5132;
            background: #d1e7dd;
            border: 1px solid #badbcc;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #243c33;
            font-size: 14px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid #ccd8d2;
            border-radius: 9px;
            font-size: 15px;
            outline: none;
            transition: 0.2s ease;
            background: #ffffff;
        }

        input:focus {
            border-color: #007a4d;
            box-shadow: 0 0 0 3px rgba(0, 122, 77, 0.12);
        }

        .login-button {
            width: 100%;
            border: none;
            border-radius: 9px;
            padding: 15px;
            margin-top: 5px;
            background: #007a4d;
            color: #ffffff;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .login-button:hover {
            background: #005f3c;
        }

        .mobile-note {
            margin-top: 25px;
            padding: 14px;
            background: #f3f8f5;
            border-left: 4px solid #007a4d;
            border-radius: 5px;
            color: #53645d;
            font-size: 13px;
            line-height: 1.5;
        }

        .footer {
            margin-top: 32px;
            text-align: center;
            color: #8a9691;
            font-size: 12px;
        }

        /* RESPONSIVE */

        @media (max-width: 800px) {
            .portal-card {
                grid-template-columns: 1fr;
            }

            .welcome-panel {
                min-height: auto;
                padding: 40px 30px;
            }

            .welcome-panel h1 {
                font-size: 38px;
            }

            .login-panel {
                min-height: auto;
                padding: 40px 30px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 12px;
            }

            .welcome-panel,
            .login-panel {
                padding: 32px 22px;
            }

            .welcome-panel h1 {
                font-size: 34px;
            }

            .login-panel h3 {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>

<div class="page-wrapper">

    <div class="portal-card">

        <!-- LEFT PANEL -->
        <section class="welcome-panel">

            <div class="smartlog-badge">
                DIVINE WORD UNIVERSITY
            </div>

            <h1>SmartLog</h1>

            <h2>
                Smart Clinical Logbook
            </h2>

            <p>
                A digital clinical logbook system for managing clinical
                learning, student progress, supervisor verification and
                academic monitoring at Divine Word University.
            </p>

            <div class="role-list">
                <span class="role">Lecturer</span>
                <span class="role">HOD</span>
                <span class="role">ICT Admin</span>
            </div>

        </section>


        <!-- LOGIN PANEL -->
        <section class="login-panel">

            <h3>Web Portal Login</h3>

            <p class="subtitle">
                Sign in using your DWU ID or email address.
            </p>


            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif


            @if ($errors->any())
                <div class="alert alert-error">
                    {{ $errors->first() }}
                </div>
            @endif


            <form method="POST" action="{{ route('web.login.submit') }}">

                @csrf

                <div class="form-group">

                    <label for="login">
                        DWU ID or Email
                    </label>

                    <input
                        type="text"
                        id="login"
                        name="login"
                        value="{{ old('login') }}"
                        placeholder="Enter DWU ID or email"
                        autocomplete="username"
                        required
                        autofocus
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="login-button"
                >
                    Sign In to SmartLog
                </button>

            </form>


            <div class="mobile-note">
                <strong>Student?</strong><br>
                Student clinical activities and offline logbook work
                continue through the SmartLog mobile application.
            </div>


            <div class="footer">
                SmartLog &copy; {{ date('Y') }} Divine Word University
            </div>

        </section>

    </div>

</div>

</body>
</html>