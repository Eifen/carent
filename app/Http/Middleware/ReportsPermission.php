<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;

class ReportsPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ((Session::has('userPermissions') && Session::get('userPermissions')['reportP'] != 1) || !Session::has('userPermissions')) {
            return redirect('/');
        }
        return $next($request);
    }
}
