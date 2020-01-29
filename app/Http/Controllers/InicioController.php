<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
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

    function guardarNuevaClave(Request $request){

      $modelo = new InicioModel();

      $claveActual = $modelo->contraseñaActualUsuario($request->session()->get('usuario_id'));
      $claveActual = $this->desencriptarLaravel($claveActual->clave);

      if(!empty($claveActual)){

        $claveActualForm = $this->desencriptarCryptoJS($request->input("claveActual"));
        $nuevaClave = $this->encriptarLaravel($this->desencriptarCryptoJS($request->input("nuevaClave")));

        if($claveActual === $claveActualForm){

          $response = $modelo->actualizarContraseña($request->session()->get('usuario_id'), $nuevaClave);

        }else{

          $response = array("response" => false, "message" => "La contraseña actual es inválida!");

        }

      }else{

        $response = array("response" => false, "message" => "Ocurrio un error al tratar de actualizar la contraseña, por favor intente nuevamente!");

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
