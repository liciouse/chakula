<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        if ($role === 'admin' && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        if ($role === 'editor' && Auth::user()->role !== 'editor' && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        if ($role === 'author' && Auth::user()->role !== 'author' && Auth::user()->role !== 'editor' && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}