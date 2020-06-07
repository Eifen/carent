<?php

namespace App\Http\Middleware;

use Closure;

class UsuarioSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
     public function handle($request, Closure $next)
     {

         if(session()->has("usuario_id")) {
             return redirect('/inicio');
         }
         return $next($request);
     }
}
