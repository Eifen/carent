<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\LoginModel;

class LoginController extends Controller
{

    function encryptConfig(Request $request){

      $modelo = new LoginModel();
      $config = $modelo->encryptConfig();
      $pass = $this->encriptarLaravel("123456");

      return $config;

    }

    function login(Request $request){

      $codigoUsuario = $this->desencriptarCryptoJS($request->input("codigoUsuario"));
      $claveForm = $this->desencriptarCryptoJS($request->input("clave"));

      $modelo = new LoginModel();
      $usuario = $modelo->buscarUsuario($codigoUsuario);
      $loginDenegado = $modelo->estatusLoginDenegado($usuario->id_estatus);

      if(!$loginDenegado){

        if(!empty($usuario)){

          $claveDB = $usuario->clave;
          $claveDB = $this->desencriptarLaravel($claveDB);

          if($claveDB === $claveForm){

            //Se crean las variables de sessión
            $request->session()->put('usuario_id', $usuario->id);
            /*session('usuario_id', $usuario->id);
            session('usuario_avatar', $usuario->avatar);
            session('usuario_correo_principal', $usuario->correo_principal);*/

            $response = array("login" => true, "message" => "Bienvenido!, espere unos segundo mientras mientras es redireccionado.");

          }else{

            $response = array("login" => false, "message" => "Contraseña inválida");

          }

        }else{

          $response = array("login" => false, "message" => "El usuario no existe");

        }

      }else{

        $response = array("login" => false, "message" => "El usuario está en estatus <b>".$usuario->estatus."</b>");

      }// Fin if(!$loginDenegado)

      return $response;

    }

    function recoverylogin(Request $request){

      $codigoUsuario = $this->desencriptarCryptoJS($request->input("codigoUsuario"));

      $modelo = new LoginModel();
      $usuario = $modelo->buscarUsuario($codigoUsuario);

      if(!empty($usuario)){

        $claveDB = $usuario->clave;
        $claveDB = $this->desencriptarLaravel($claveDB);
        $correoDestinatario = $usuario->correo_principal;

        Mail::send('emailTemplates.recoveryPassword', ["clave" => $claveDB], function($message) use ($correoDestinatario)  {

            $message->from('sistema.carent@crowe.com.ve', 'CARENT')->to($correoDestinatario)->subject('Recuperación de Contraseña');

        });

        if(Mail::failures()){

          $response = array("recovery" => false, "message" => "No se pudo enviar el correo, intente nuevamente.");

        }

        $response = array("recovery" => true, "message" => "Enviamos sus datos a su correo, por favor revise!.");

      }else{

        $response = array("recovery" => false, "message" => "El usuario no existe");

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

      $modelo = new LoginModel();
      $config = $modelo->encryptConfig();

      $key = pack("H*", $config["key"]);
      $iv =  pack("H*", $config["iv"]);
      $decrypted = openssl_decrypt($valor, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
      $decrypted = trim($decrypted);

      return $decrypted;

    }

}
