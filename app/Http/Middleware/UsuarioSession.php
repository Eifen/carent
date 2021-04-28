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

       $uri = $request->route()->uri();

       if(session()->has("usuario_id") && $uri == "/" && session()->get('cambiar_clave') == false) {
           return redirect('/inicio');
       }else if(session()->has("usuario_id") && session()->get('cambiar_clave') == true){
           return redirect('/cambiarClave');
       }else if(!session()->has("usuario_id") && $uri != "/"){
           return redirect('/');
       }

       return $next($request);

     }
}
