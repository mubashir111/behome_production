<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'status' => Status::ACTIVE,
        ];

        if (!Auth::guard('web')->attempt($credentials, $request->filled('remember'))) {
            return back()->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'These credentials do not match our records or account is inactive.']);
        }

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
