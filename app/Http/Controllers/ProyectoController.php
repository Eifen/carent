<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
use App\Models\ProyectoModel;
use Illuminate\Http\RedirectResponse;

class ProyectoController extends Controller
{

    function dataInicialNuevoProyecto(){

      $modelo = new ProyectoModel();
      $clientes = $modelo->clientes();
      $divisiones = $modelo->divisiones();
      $estatus = $modelo->estatusProyectos();

      return [
        "clientes" => $clientes,
        "divisiones" => $divisiones,
        "estatus" => $estatus
      ];

    }

    function crearProyecto(Request $request){

      $modelo = new ProyectoModel();
      $descripcion = $request->input("descripcion");
      $cliente = $request->input("cliente");
      $horas = $request->input("horas");
      $fechaContratacion = $request->input("fechaContratacion");
      $divisiones = $request->input("divisiones");
      $estatus = $request->input("estatus");

      return $modelo->crearProyecto($descripcion,$cliente,$horas,$fechaContratacion,$divisiones,$estatus);

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
              "clave" => $this->encriptarLaravel($request->input("cedula")),
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

}
