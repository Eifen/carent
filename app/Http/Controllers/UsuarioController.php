<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
use App\Models\UsuarioModel;
use App\Models\AuditoriaLogModel;
use App\Models\InicioModel;
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

    function dataInicialNuevoUsuario(){

      $modelo = new UsuarioModel();
      $modeloConfig = new ConfigsModel();

      $cargos = $modelo->cargos();
      $divisiones = $modelo->divisiones();
      $encryptConfig = $modeloConfig->encryptConfig();
      $estados = $modelo->estados();
      $tipoDocumentos = $modelo->tipoDocumentos();

      return [
        "cargos" => $cargos,
        "divisiones" => $divisiones,
        "encryptConfig" => $encryptConfig,
        "estados" => $estados,
        "tipoDocumentos" => $tipoDocumentos
      ];

    }

    function crearUsuario(Request $request){

      $modelo = new UsuarioModel();
      $modeloConfig = new ConfigsModel();

      $encryptConfig = $modeloConfig->encryptConfig();

      $codigoUsuario = $modeloConfig->desencriptarCryptoJS($request->input("codigoUsuario"));
      $usuario = $modelo->buscarUsuario($codigoUsuario);

      if(empty($usuario)){

        $correos = $modelo->buscarCorreos($request->input("correoPrincipal"), $request->input("correoSecundario"));

        if(!$correos["response"]){

          $fecha_nacimiento = (trim($request->input("fechaNacimiento")) == "") ? null : date("Y-m-d H:i:s", strtotime($request->input("fechaNacimiento")));
          $fecha_ingreso = (trim($request->input("fechaIngreso")) == "") ? null : date("Y-m-d H:i:s", strtotime($request->input("fechaIngreso")));

            $parametros = array(
              "nombre1" => mb_strtoupper ($request->input("nombre1")),
              "nombre2" => mb_strtoupper ($request->input("nombre2")),
              "apellido1" => mb_strtoupper($request->input("apellido1")),
              "apellido2" => mb_strtoupper($request->input("apellido2")),
              "cedula" => $request->input("cedula"),
              "fechaNacimiento" => $fecha_nacimiento,
              "codigoUsuario" => $codigoUsuario,
              "clave" => $request->input("cedula"),
              "correoPrincipal" => strtolower($request->input("correoPrincipal")),
              "correoSecundario" => strtolower($request->input("correoSecundario")),
              "telefono1" => $request->input("telefono1"),
              "telefono2" => $request->input("telefono2"),
              "parroquia" => $request->input("parroquia"),
              "division" => $request->input("division"),
              "cargo" => $request->input("cargo"),
              "fechaIngreso" => $fecha_ingreso,
              "tipoDocumento" => $request->input("tipoDocumento"),
              "keysecret" => $encryptConfig["key"]
            );

            $response = $modelo->crearUsuario($parametros);

            if($response["response"]){

              $parametros = [
                "accion" => 'Registro de Usuario Codigo: '.$codigoUsuario,
                "direccion_ip" => $request->session()->get('usuario_ip'),
                "fecha" => date("Y-m-d H:i:s"),
                "tabla" => 'tbl_usuario',
                "usuario_id" => $request->session()->get('usuario_id')
              ];

              $modeloAudit = new AuditoriaLogModel();
              $modeloAudit->logs_auditoria($parametros);

            }

        }else{

          $response = array("response" => false, "message" => "El correo principal o secundario ya se encuentra asociado a otro usuario");

        }

      }else{

        $response = array("response" => false, "message" => "Ya existe un usuario con ese código de usuario");

      }

      return $response;

    }

    function searchUsers(Request $request) {

        $model = new UsuarioModel();

        $params = [
            "data" => strtolower($request->input("data")),
            "paginate" => (int) $request->input("paginate"),
            "searchBy" => (int) $request->input("searchBy"),
            "searchFrom" => (int) $request->input("searchFrom")
        ];
        $data = $model->searchUsers($params);
        $permisoActualizar = $model->permisoActualizar(session("usuario_id"), 3);

        if(!empty($data)) {

            return [
                "pages" => $data["pages"],
                "permisoActualizar" => $permisoActualizar,
                "response" => true,
                "users" => $data["users"]
            ];

        } else {

            return [
                "message" => "No se encontraron resultados",
                "response" => false
            ];

        }

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
      $tipoDocumentos = $modelo->tipoDocumentos();

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
                          "estatus" => $estatus,
                          "tipoDocumentos" => $tipoDocumentos);

      }else{

        $response = array("response" => false, "message" => "No se encontraron resultados");

      }

      return $response;

    }

    function modificarUsuario(Request $request){

      $modelo = new UsuarioModel();
      $modeloConfig = new ConfigsModel();

      $fecha_nacimiento = (trim($request->input("fechaNacimiento")) == "") ? null : date("Y-m-d H:i:s", strtotime($request->input("fechaNacimiento")));
      $fecha_ingreso = (trim($request->input("fechaIngreso")) == "") ? null : date("Y-m-d H:i:s", strtotime($request->input("fechaIngreso")));
      $fecha_egreso = (trim($request->input("fechaEngreso")) == "") ? null : date("Y-m-d H:i:s", strtotime($request->input("fechaEngreso")));

      $parametros = array(
        "idUsuario" => $request->input("idUsuario"),
        "nombre1" => mb_strtoupper($request->input("nombre1")),
        "nombre2" => mb_strtoupper($request->input("nombre2")),
        "apellido1" => mb_strtoupper($request->input("apellido1")),
        "apellido2" => mb_strtoupper($request->input("apellido2")),
        "cedula" => $request->input("cedula"),
        "fechaNacimiento" => $fecha_nacimiento,
        "correoPrincipal" => strtolower($request->input("correoPrincipal")),
        "correoSecundario" => strtolower($request->input("correoSecundario")),
        "telefono1" => $request->input("telefono1"),
        "telefono2" => $request->input("telefono2"),
        "parroquia" => $request->input("parroquia"),
        "division" => $request->input("division"),
        "cargo" => $request->input("cargo"),
        "estatus" => $request->input("estatus"),
        "codigoUsuario" => $modeloConfig->desencriptarCryptoJS($request->input("codigoUsuario")),
        "usuario_id" => $request->session()->get('usuario_id'),
        "fecha" => date("Y-m-d H:i:s"),
        "direccion_ip" => $request->session()->get('usuario_ip'),
        "fechaIngreso" => $fecha_ingreso,
        "fechaEgreso" => $fecha_egreso,
        "tipoDocumento" => $request->input("tipoDocumento"),
        "idUsuarioDocumentoIdentidad" => $request->input("idUsuarioDocumentoIdentidad")
      );

      $response = $modelo->modificarUsuario($parametros);

      if($response["response"]){

        $parametros = [
          "accion" => 'Modificacion del Usuario Codigo: '.$parametros["codigoUsuario"],
          "direccion_ip" => $request->session()->get('usuario_ip'),
          "fecha" => date("Y-m-d H:i:s"),
          "tabla" => 'tbl_usuario',
          "usuario_id" => $request->session()->get('usuario_id')
        ];

        $modeloAudit = new AuditoriaLogModel();
        $modeloAudit->logs_auditoria($parametros);

      }

      return $response;

    }

    private function encriptarLaravel($valor){

      $encrypted = Crypt::encryptString($valor);
      return $encrypted;

    }

    function detalleMenu(Request $request){

      $modelo = new UsuarioModel();
      $modelo_menu = new ConfigsModel();
      $id_usuario = (int) $request->input("idUsuario");
      $datosUsuario = $modelo->divisionUsu($id_usuario);
      $infoMenuUsuario = $modelo_menu->menUsuario($id_usuario);
      $infoMenus = $modelo->detalleMenu($id_usuario);
      $response = array("response" => true, "infoMenUsu" => $infoMenuUsuario, "id_usuario" => $id_usuario, "datosUsuario" => $datosUsuario, "infoMenus" => $infoMenus);
      return $response;

    }

    function menuUsuario(Request $request){

      $modelo= new UsuarioModel();
      $id_usuario = (int)$request->input("id_usuario");
      $id_menu = $request->input("id_menu");
      $C = $request->input("C");
      $R = $request->input("R");
      $U = $request->input("U");
      $D = $request->input("D");
      $menusUsuario = $modelo->menuUsuario($id_usuario,$id_menu,$C,$R,$U,$D);
      if(!empty($menusUsuario)){
        $infoMenus = $modelo->detalleMenu($id_usuario);
        $response = array("response" => true, "infoMenus" => $infoMenus);
      }else{
        $response = array("response" => false, "message" => "Error al tratar de actualizar menus");
      }
    return $response;
    }
}
