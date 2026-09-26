<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    /**
     * Show the SmartLog web login page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }

        return view('web.auth.login');
    }

    /**
     * Process web login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($credentials['login']);

        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'dwu_id';

        /*
         * Find the account first.
         *
         * This allows us to stop STUDENT accounts before Laravel
         * creates a web session for them.
         */
        $user = User::where($field, $login)->first();

        if (
            !$user ||
            !$user->is_active ||
            !Hash::check($credentials['password'], $user->password)
        ) {
            return back()
                ->withErrors([
                    'login' => 'Invalid login details or this SmartLog account is inactive.',
                ])
                ->onlyInput('login');
        }

        /*
         * Students use the SmartLog mobile application only.
         */
        if ($user->role === 'STUDENT') {
            return back()
                ->withErrors([
                    'login' => 'Student accounts use the SmartLog mobile application.',
                ])
                ->onlyInput('login');
        }

        /*
         * Only approved SmartLog web roles can continue.
         */
        if (!in_array($user->role, ['LECTURER', 'HOD', 'ICT_ADMIN'], true)) {
            return back()
                ->withErrors([
                    'login' => 'This account does not have access to the SmartLog web portal.',
                ])
                ->onlyInput('login');
        }

        /*
         * Create the normal Laravel web session.
         */
        Auth::login($user);

        $request->session()->regenerate();

        return $this->redirectByRole($user->role);
    }

    /**
     * Log the current web user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('web.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Send each web user to the correct dashboard.
     */
    private function redirectByRole(string $role)
    {
        return match ($role) {
            'LECTURER' => redirect()->route('web.lecturer.dashboard'),

            'HOD' => redirect()->route('web.hod.dashboard'),

            'ICT_ADMIN' => redirect()->route('web.admin.dashboard'),

            default => $this->logoutUnknownRole(),
        };
    }

    /**
     * Safety fallback for unsupported roles.
     */
    private function logoutUnknownRole()
    {
        Auth::logout();

        return redirect()
            ->route('web.login')
            ->withErrors([
                'login' => 'This account does not have access to the SmartLog web portal.',
            ]);
    }
}