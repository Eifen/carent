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

      $modelo = new ConfigsModel();

      $parametros = [
        $modelo->desencriptarCryptoJS($request->input("codigoUsuario")),
        $modelo->desencriptarCryptoJS($request->input("clave")),
        $this->mi_ip()
      ];

      $modelo = new LoginModel();
      $login = $modelo->login($parametros);

      if($login["response"]){

        //Se crean las variables de sessión
        $request->session()->put('cambiar_clave', $login["cambiar_clave"]);
        $request->session()->put('cargo_id', $login["id_cargo"]);
        $request->session()->put('division_id', $login["id_division"]);
        $request->session()->put('usuario_correo', $login["correo_usuario"]);
        $request->session()->put('usuario_id', $login["id_usuario"]);
        $request->session()->put('usuario_ip', $this->mi_ip());

      }

      return ["login" => $login["response"], "message" => $login["message"]];

    }

    function recoverylogin(Request $request){

      $modelo = new ConfigsModel();

      $parametros = [
        $modelo->desencriptarCryptoJS($request->input("codigoUsuario")),
        $this->mi_ip()
      ];

      $modelo = new LoginModel();
      $recoveryLogin = $modelo->recoverylogin($parametros);

      if($recoveryLogin["response"]){

        $correoDestinatario = $recoveryLogin["correo"];

        if(Mail::send('emailTemplates.recoveryPassword', ["clave" => $recoveryLogin["clave"]], function($message) use ($correoDestinatario)  {

            $message->from('sistema.carent@crowe.com.ve', 'CARENT')->to($correoDestinatario)->subject('Recuperación de Contraseña');

        })) {
          $mensaje = "Enviamos sus datos a su correo, por favor revise!.";
        } else {
          $mensaje = "No se pudo enviar el correo, intente nuevamente.";
        };

      }else{
        $mensaje = $recoveryLogin["message"];
      }

      return ["recovery" => $recoveryLogin["response"], "message" => $mensaje];

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
