<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class AuthSessionAdmin
{
    public function handle($request, Closure $next)
    {
        if (!Session::has('user_id') || !Session::has('is_admin')) {
            return redirect()->route('admin.login');
        }
        return $next($request);
    }
}