<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => "Too many login attempts. Please try again in {$seconds} seconds."]);
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'status' => Status::ACTIVE,
        ];

        if (!Auth::guard('web')->attempt($credentials, $request->filled('remember'))) {
            RateLimiter::hit($throttleKey, 300); // 5 minute lock
            return back()->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'These credentials do not match our records or account is inactive.']);
        }

        RateLimiter::clear($throttleKey);

        $user = Auth::guard('web')->user();
        if ($user->is_guest == \App\Enums\Ask::YES) {
            Auth::guard('web')->logout();
            return back()->withErrors(['email' => 'Your account is not allowed to access admin panel.']);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
