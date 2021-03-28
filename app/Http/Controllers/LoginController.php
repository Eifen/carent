<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
use App\Models\LoginModel;
use App\Models\AuditoriaLogModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{

    function index(Request $request){

      $modelo = new ConfigsModel();
      $config = $modelo->encryptConfig();

      Session::put('encrypt-key', $config["key"]);
      Session::put('encrypt-iv', $config["iv"]);

      return view('login', $request);

    }

    function login(Request $request){

      $parametros = [
        $this->desencriptarCryptoJS($request->input("codigoUsuario")),
        $this->desencriptarCryptoJS($request->input("clave")),
        $this->mi_ip()
      ];

      $modelo = new LoginModel();
      $login = $modelo->login($parametros);

      if($login["response"]){

        //Se crean las variables de sessión
        $request->session()->put('usuario_id', $login["id_usuario"]);
        /*$request->session()->put('division_id', $login["id_division"]);
        $request->session()->put('cargo_id', $login["id_cargo"]);*/
        $request->session()->put('usuario_ip', $this->mi_ip());
        $request->session()->put('cambiar_clave', $login["cambiar_clave"]);

      }

      return ["login" => $login["response"], "message" => $login["message"]];

    }

    function recoverylogin(Request $request){

      $parametros = [
        $this->desencriptarCryptoJS($request->input("codigoUsuario")),
        $this->mi_ip()
      ];

      $modelo = new LoginModel();
      $recoveryLogin = $modelo->recoverylogin($parametros);

      if($recoveryLogin["response"]){

        $correoDestinatario = $recoveryLogin["correo"];

        Mail::send('emailTemplates.recoveryPassword', ["clave" => $recoveryLogin["clave"]], function($message) use ($correoDestinatario)  {

            $message->from('sistema.carent@crowe.com.ve', 'CARENT')->to($correoDestinatario)->subject('Recuperación de Contraseña');

        });

        if(Mail::failures()){
          $mensaje = "No se pudo enviar el correo, intente nuevamente.";
        }else{
          $mensaje = "Enviamos sus datos a su correo, por favor revise!.";
        }

      }else{
        $mensaje = $recoveryLogin["message"];
      }

      return ["recovery" => $recoveryLogin["response"], "message" => $mensaje];

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

      $key = pack("H*", Session::get("encrypt-key"));
      $iv = pack("H*", Session::get("encrypt-iv"));

      $decrypted = openssl_decrypt($valor, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
      $decrypted = trim($decrypted);

      return $decrypted;

    }

    function logout(Request $request){

      $request->session()->flush();

      return redirect()->route('loginView');

    }

    private function mi_ip(){

      if (isset($_SERVER["HTTP_CLIENT_IP"])){
        $direccion = $_SERVER["HTTP_CLIENT_IP"];
      }elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
        $direccion = $_SERVER["HTTP_X_FORWARDED_FOR"];
      }elseif (isset($_SERVER["HTTP_X_FORWARDED"])){
        $direccion = $_SERVER["HTTP_X_FORWARDED"];
      }elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])){
        $direccion = $_SERVER["HTTP_FORWARDED_FOR"];
      }elseif (isset($_SERVER["HTTP_FORWARDED"])){
        $direccion = $_SERVER["HTTP_FORWARDED"];
      }else{
        $direccion = $_SERVER["REMOTE_ADDR"];
      }

      return $direccion;

    }

}
