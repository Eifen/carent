<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\InicioModel;

class InicioController extends Controller
{

    function inicio(Request $request){

      $data = array();
      return view('inicio', $data);

    }

    function menUsuario(Request $request){

      $modelo = new InicioModel();
      $menus = $modelo->menUsuario(session("usuario_id"));

      return $menus;

    }

}
