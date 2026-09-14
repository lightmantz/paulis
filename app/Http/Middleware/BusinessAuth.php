<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BusinessAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('business.login');
        }

        // Immediately bind the current business so all downstream
        // code can access it via app('currentBusiness')
        $user = Auth::guard('web')->user();
        if ($user->business_id) {
            app()->instance('currentBusiness', $user->business);
        }

        return $next($request);
    }
}