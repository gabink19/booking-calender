<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class AuthSession
{
    public function handle($request, Closure $next)
    {
        if (!Session::has('user_id') ) {
            return redirect()->route('login');
        }
        if (Session::has('is_admin')) {
            return redirect()->route('admin.dashboard');
        }
        return $next($request);
    }
}