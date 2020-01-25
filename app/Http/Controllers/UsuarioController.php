<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
use App\Models\UsuarioModel;
use Illuminate\Http\RedirectResponse;

class UsuarioController extends Controller
{

    function estados(){

      $modelo = new UsuarioModel();
      $estados = $modelo->estados();
      return $estados;

    }

    function municipios(Request $request){

      $id_estado = $request->input("id_estado");
      $modelo = new UsuarioModel();
      $municipios = $modelo->municipios($id_estado);
      return $municipios;

    }

    function parroquias(Request $request){

      $id_municipio = $request->input("id_municipio");
      $modelo = new UsuarioModel();
      $parroquias = $modelo->parroquias($id_municipio);
      return $parroquias;

    }

    function divisiones(){

      $modelo = new UsuarioModel();
      $divisiones = $modelo->divisiones();
      return $divisiones;

    }

    function cargos(){

      $modelo = new UsuarioModel();
      $cargos = $modelo->cargos();
      return $cargos;

    }

    function crearUsuario(Request $request){

      $modelo = new UsuarioModel();

      $codigoUsuario = $this->desencriptarCryptoJS($request->input("codigoUsuario"));
      $usuario = $modelo->buscarUsuario($codigoUsuario);

      if(empty($usuario)){

        $correos = $modelo->buscarCorreos($request->input("correoPrincipal"), $request->input("correoSecundario"));

        if(!$correos["response"]){

            $parametros = array(
              "nombre1" => mb_strtoupper ($request->input("nombre1")),
              "nombre2" => mb_strtoupper ($request->input("nombre2")),
              "apellido1" => mb_strtoupper($request->input("apellido1")),
              "apellido2" => mb_strtoupper($request->input("apellido2")),
              "cedula" => $request->input("cedula"),
              "fechaNacimiento" => $request->input("fechaNacimiento"),
              "codigoUsuario" => $codigoUsuario,
              "clave" => $this->encriptarLaravel($this->desencriptarCryptoJS($request->input("cedula"))),
              "correoPrincipal" => strtolower($request->input("correoPrincipal")),
              "correoSecundario" => strtolower($request->input("correoSecundario")),
              "telefono1" => $request->input("telefono1"),
              "telefono2" => $request->input("telefono2"),
              "parroquia" => $request->input("parroquia"),
              "division" => $request->input("division"),
              "cargo" => $request->input("cargo")
            );

            $response = $modelo->crearUsuario($parametros);

        }else{

          $response = array("response" => false, "message" => "El correo principal o secundario ya se encuentra asociado a otro usuario");

        }

      }else{

        $response = array("response" => false, "message" => "Ya existe un usuario con ese código de usuario");

      }

      return $response;

    }

    function buscarUsuarios(Request $request){

      $modelo = new UsuarioModel();

      $buscarPor = (int) $request->input("buscarPor");
      $dato = strtolower($request->input("dato"));
      $usuarios = $modelo->buscarUsuarios($buscarPor, $dato);

      if(!empty($usuarios)){

        $response = array("response" => true, "usuarios" => $usuarios);

      }else{

        $response = array("response" => false, "message" => "No se encontraron resultados");

      }

      return $response;

    }

    function detalleUsuario(Request $request){

      $modelo = new UsuarioModel();
      $id_usuario = (int) $request->input("idUsuario");

      $infoUsuario = $modelo->detalleUsuario($id_usuario);

      if(!empty($infoUsuario)){

        $response = array("response" => true, "info" => $infoUsuario);

      }else{

        $response = array("response" => false, "message" => "No se encontraron resultados");

      }

      return $response;

    }

    function formModificarUsuario($idUsuario, Request $request){

      $request->session()->put('id_usuario_mod', $idUsuario);
      return view('usuario/modificarUsuario');

    }

    function detalleUsuarioModificar(Request $request){

      $modelo = new UsuarioModel();
      $id_usuario = (int) session("id_usuario_mod");

      $infoUsuario = $modelo->detalleUsuarioModificar($id_usuario);
      $estatus = $modelo->estatusUsuario();
      $divisiones = $this->divisiones();
      $cargos = $this->cargos();
      $estados = $modelo->estados();

      if($infoUsuario->id_estado !== NULL){
        $municipios = $modelo->municipios($infoUsuario->id_estado);
        $parroquias = $modelo->parroquias($infoUsuario->id_municipio);
      }else{
        $municipios = array();
        $parroquias = array();
      }

      if(!empty($infoUsuario)){

        $response = array("response" => true,
                          "info" => $infoUsuario,
                          'divisiones' => $divisiones,
                          "cargos" => $cargos,
                          "estados" => $estados,
                          "municipios" => $municipios,
                          "parroquias" => $parroquias,
                          "estatus" => $estatus);

      }else{

        $response = array("response" => false, "message" => "No se encontraron resultados");

      }

      return $response;

    }

    function modificarUsuario(Request $request){

      $modelo = new UsuarioModel();

      $parametros = array(
        "idUsuario" => $request->input("idUsuario"),
        "nombre1" => mb_strtoupper($request->input("nombre1")),
        "nombre2" => mb_strtoupper($request->input("nombre2")),
        "apellido1" => mb_strtoupper($request->input("apellido1")),
        "apellido2" => mb_strtoupper($request->input("apellido2")),
        "cedula" => $request->input("cedula"),
        "fechaNacimiento" => $request->input("fechaNacimiento"),
        "correoPrincipal" => strtolower($request->input("correoPrincipal")),
        "correoSecundario" => strtolower($request->input("correoSecundario")),
        "telefono1" => $request->input("telefono1"),
        "telefono2" => $request->input("telefono2"),
        "parroquia" => $request->input("parroquia"),
        "division" => $request->input("division"),
        "cargo" => $request->input("cargo"),
        "estatus" => $request->input("estatus")
      );

      $response = $modelo->modificarUsuario($parametros);

      return $response;

    }

    private function encriptarLaravel($valor){

      $encrypted = Crypt::encryptString($valor);
      return $encrypted;

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
