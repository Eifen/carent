<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\FacturacionModel;

class FacturacionController extends Controller
{

    function formIngresosGastos(){

      return view('facturacion/ingresosGastos');

    }

    function dataInicialIngresosGastos(){

      

    }

    function login(Request $request){

      $codigoUsuario = $this->desencriptarCryptoJS($request->input("codigoUsuario"));
      $claveForm = $this->desencriptarCryptoJS($request->input("clave"));
      $fecha = date("Y-m-d H:i:s");
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
      $modelo = new LoginModel();
      $usuario = $modelo->buscarUsuario($codigoUsuario,$fecha,$direccion);
      $loginDenegado = $modelo->estatusLoginDenegado($usuario->id_estatus);

      if(!empty($usuario)){

        if(!$loginDenegado){

          $claveDB = $usuario->clave;
          $claveDB = $this->desencriptarLaravel($claveDB);

          if($claveDB === $claveForm){

            //Se crean las variables de sessión
            $request->session()->put('usuario_id', $usuario->id);
            $request->session()->put('division_id', $usuario->id_division);
            $request->session()->put('cargo_id', $usuario->id_cargo);
            $request->session()->put('direccion', $direccion);

            $response = array("login" => true, "message" => "Bienvenido!, espere unos segundo mientras mientras es redireccionado.");

          }else{

            $response = array("login" => false, "message" => "Contraseña inválida");

          }

        }else{

          $response = array("login" => false, "message" => "El usuario está en estatus <b>".$usuario->estatus."</b>");

        }

      }else{

        $response = array("login" => false, "message" => "El usuario no existe");

      }// Fin !empty($usuario)

      return $response;

    }

}
