<?php

namespace App\Http\Controllers;
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
      }

      return $response;

    }

    private function encriptarLaravel($valor){

      $encrypted = Crypt::encryptString($valor);
      return $encrypted;

    }

    private function desencriptarLaravel($valor){

      $decrypted = Crypt::decryptString($valor);
      return $decrypted;

    }

    private function desencriptarCryptoJS($valor){

      $modelo = new ConfigsModel();
      $config = $modelo->encryptConfig();

      $key = pack("H*", $config["key"]);
      $iv =  pack("H*", $config["iv"]);
      $decrypted = openssl_decrypt($valor, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
      $decrypted = trim($decrypted);

      return $decrypted;

    }

}
