<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\InicioModel;

class InicioController extends Controller
{

    function inicio(Request $request){

      if(!session()->has("usuario_id")) {
          return redirect()->route('loginView');
      }

      return view('inicio');

    }

    function menUsuario(Request $request){

      $modelo = new InicioModel();
      $menus = $modelo->menUsuario(session("usuario_id"));

      return $menus;

    }

    function cambiarClave(Request $request){

      if(!session()->has("usuario_id")) {
          return redirect()->route('loginView');
      }

      return view('cambiarClave');

    }

}
