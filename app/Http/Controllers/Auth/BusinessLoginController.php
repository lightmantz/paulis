<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessLoginController extends Controller
{
    public function show()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('business.dashboard');
        }
        return view('auth.business-login');
    }

    public function login(Request $request)
    {
        // Accept either email OR username
        $login = $request->input('login');
        $password = $request->input('password');

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::guard('web')->attempt([$field => $login, 'password' => $password], $request->boolean('remember'))) {
            $user = Auth::guard('web')->user();

            if ($user->status !== 'Active') {
                Auth::guard('web')->logout();
                return back()->withErrors(['login' => 'This account is not active.']);
            }

            $request->session()->regenerate();
            $user->update(['last_login_at' => now()]);

            return redirect()->intended(route('business.dashboard'));
        }

        return back()
            ->withErrors(['login' => 'Incorrect username or password.'])
            ->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('business.login');
    }
}