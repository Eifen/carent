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
              "fechaNacimiento" => date("Y-m-d H:i:s", strtotime($request->input("fechaNacimiento"))),
              "codigoUsuario" => $codigoUsuario,
              "clave" => $this->encriptarLaravel($request->input("cedula")),
              "correoPrincipal" => strtolower($request->input("correoPrincipal")),
              "correoSecundario" => strtolower($request->input("correoSecundario")),
              "telefono1" => $request->input("telefono1"),
              "telefono2" => $request->input("telefono2"),
              "parroquia" => $request->input("parroquia"),
              "division" => $request->input("division"),
              "cargo" => $request->input("cargo"),
              "usuario_id" => $request->session()->get('usuario_id'),
              "fecha" => date("Y-m-d H:i:s"),
              "direccion_ip" => $request->session()->get('direccion'),
              "fechaIngreso" => date("Y-m-d H:i:s", strtotime($request->input("fechaIngreso")))
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
      $permisoActualizar = $modelo->permisoActualizar(session("usuario_id"), 3);

      if(!empty($usuarios)){

        $response = array("response" => true, "usuarios" => $usuarios, "permisoActualizar" => $permisoActualizar);

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
        "estatus" => $request->input("estatus"),
        "codigoUsuario" => $this->desencriptarCryptoJS($request->input("codigoUsuario")),
        "usuario_id" => $request->session()->get('usuario_id'),
        "fecha" => date("Y-m-d H:i:s"),
        "direccion_ip" => $request->session()->get('direccion'),
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

    function detalleMenu(Request $request){

      $modelo = new UsuarioModel();
      $id_usuario = (int) $request->input("idUsuario");
      $datosUsuario = $modelo->divisionUsu($id_usuario);
      $infoMenuUsuario = $modelo->detalleMenu($id_usuario);
      $response = array("response" => true, "info" => $infoMenuUsuario, "id_usuario" => $id_usuario, "datosUsuario" => $datosUsuario);
      return $response;

    }

    function agregarMenUsu(Request $request){

      $modelo= new UsuarioModel();
      $menuCr = (int) $request->input("menuCr");
      $C = (int) $request->input("C");
      $R = (int) $request->input("R");
      $U = (int) $request->input("U");
      $D = (int) $request->input("D");
      $idUsuario = (int) $request->input("idUsuario");
      $menu= $modelo->agregarMenUsu($menuCr,$C,$R,$U,$D,$idUsuario);
      if(!empty($menu)){
      $response = array("response" => true, "menUsus" => $menu);
      }else{
      $response = array("response" => false, "message" => "No se encontraron resultadoos");
      }
    return $response;
    }

    function quitarMenUsu(Request $request){

      $modelo= new UsuarioModel();
      $menuCr = (int) $request->input("menuCr");
      $idUsuario = (int) $request->input("idUsuario");
      $menu= $modelo->quitarMenUsu($menuCr, $idUsuario);
      $response = array("response" => true, "menUsus" => $menu);
      return $response;
    }

    function modificarMenUsu(Request $request){

      $modelo= new UsuarioModel();
      $menuCr = (int) $request->input("menuCr");
      $C = (int) $request->input("C");
      $R = (int) $request->input("R");
      $U = (int) $request->input("U");
      $D = (int) $request->input("D");
      $idUsuario = (int) $request->input("idUsuario");
      $menu= $modelo->modificarMenUsu($menuCr,$C,$R,$U,$D,$idUsuario);
      $response = array("response" => true, "menUsus" => $menu);
      return $response;
    }

}
