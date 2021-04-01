<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
use App\Models\InicioModel;
use Illuminate\Support\Facades\Session;

class InicioController extends Controller
{

    function inicio(Request $request){

      if(!session()->has("usuario_id")) {
          return redirect()->route('loginView');
      }

      return view('inicio');

    }

    function cambiarClave(Request $request){

      return view('cambiarClave');

    }

    function guardarNuevaClave(Request $request){

      $modelo = new ConfigsModel();

      $parametros = [
        session("usuario_id"),
        $modelo->desencriptarCryptoJS($request->input("claveActual")),
        $modelo->desencriptarCryptoJS($request->input("nuevaClave")),
        session("usuario_ip")
      ];

      $modelo = new InicioModel();
      $response = $modelo->guardarNuevaClave($parametros);

      if($response["response"]){

        $request->session()->put('cambiar_clave',false);

        $correoDestinatario = session("usuario_correo");

        Mail::send('emailTemplates.passChangedByUser', [], function($message) use ($correoDestinatario)  {

            $message->from('sistema.carent@crowe.com.ve', 'CARENT')->to($correoDestinatario)->subject('🚨 Se ha actualizado su contraseña en CARENT');

        });

      }

      return $response;

    }

}
