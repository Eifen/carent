<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
use App\Models\ProyectoModel;
use App\Models\AuditoriaLogModel;
use Illuminate\Http\RedirectResponse;

class ProyectoController extends Controller
{

    function dataInicialNuevoProyecto(){

      $modelo = new ProyectoModel();
      $clientes = $modelo->clientes();
      $divisiones = $modelo->divisiones();
      $estatus = $modelo->estatusProyectos();
      $monedas = $modelo->monedas(true);

      return [
        "clientes" => $clientes,
        "divisiones" => $divisiones,
        "estatus" => $estatus,
        "monedas" => $monedas
      ];

    }

    function crearProyecto(Request $request){

      $modelo = new ProyectoModel();
      $descripcion = strtoupper($request->input("descripcion"));
      $cliente = $request->input("cliente");
      $fechaContratacion = $request->input("fechaContratacion");
      $divisiones = $request->input("divisiones");
      $estatus = $request->input("estatus");
      $id_moneda = $request->input("id_moneda");
      $monto = $request->input("monto");

      $response = $modelo->crearProyecto($descripcion,$cliente,$fechaContratacion,$divisiones,$estatus,$id_moneda,$monto);

      if($response["response"]){

        $parametros = [
          "accion" => 'Registro del proyecto: '.$descripcion.'. Cliente: '.$response["cliente"],
          "direccion_ip" => $request->session()->get('direccion_ip'),
          "fecha" => date("Y-m-d H:i:s"),
          "tabla" => 'tbl_proyecto',
          "usuario_id" => $request->session()->get('usuario_id')
        ];

        $modeloAudit = new AuditoriaLogModel();
        $modeloAudit->logs_auditoria($parametros);

      }

      return $response;

    }

    function dataInicialListadoProyectos(){

      $modelo = new ProyectoModel();
      $paginar = 10;
      $permisoActualizar = $modelo->permisoActualizar(session("usuario_id"), 10);
      $proyectos = $modelo->proyectos(session("division_id"), $paginar);
      $divisiones = $modelo->divisiones();
      $estatus = $modelo->estatusProyectos();
      $cantidadPaginas = $modelo->cantidadPaginas($paginar);

      return [
        "divisiones" => $divisiones,
        "estatus" => $estatus,
        "numero_paginas" => $cantidadPaginas,
        "paginar" => $paginar,
        "permisoActualizar" => $permisoActualizar,
        "proyectos" => $proyectos
      ];

    }

    function buscarProyectos(Request $request){

      $modelo = new ProyectoModel();
      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $cliente = $request->input("cliente");
      $divisiones = ($request->input("divisiones") == null) ? [] : $request->input("divisiones");
      $proyecto = $request->input("proyecto");
      $estatus = $request->input("estatus");
      $proyectos = $modelo->proyectos(session("division_id"), $paginar, $desde, $proyecto, $cliente, $divisiones, $estatus);
      $cantidadPaginas = $modelo->cantidadPaginas($paginar, $proyecto, $cliente, $divisiones, $estatus);

      return array("proyectos" => $proyectos, "paginas" => $cantidadPaginas);

    }

    function formModificarProyecto($idProyecto, Request $request){

      $request->session()->put('id_proyecto_mod', $idProyecto);
      return view('proyecto/modificarProyecto');

    }

    function detalleProyectoModificar(Request $request){

      $modelo = new ProyectoModel();
      $id_proyecto = (int) session("id_proyecto_mod");

      $infoProyecto = $modelo->detalleProyectoModificar($id_proyecto);
      $infoDivProyecto = $modelo->detalleDivisionProyecto($id_proyecto);
      $clientes = $modelo->clientes();
      $divisiones = $modelo->divisiones();
      $estatus = $modelo->estatusProyectos();
      $monedas = $modelo->monedas(false);

      if(!empty($infoProyecto)){

        $response = array("response" => true,
                          "info" => $infoProyecto,
                          "infodivi" => $infoDivProyecto,
                          'clientes' => $clientes,
                          'divisiones' => $divisiones,
                          "estatus" => $estatus,
                          "monedas" => $monedas);
      }else{

        $response = array("response" => false, "message" => "No se encontraron resultados");

      }

      return $response;

    }

    function modificarProyecto(Request $request){

      $modelo = new ProyectoModel();
      $id_proyecto = (int) session("id_proyecto_mod");
      $idProyecto = $request->input("idProyecto");
      $descripcion = $request->input("descripcion");
      $cliente = $request->input("cliente");
      $fechaContratacion = $request->input("fechaContratacion");
      $divisiones = $request->input("divisiones");
      $divisiones_v =  $modelo->detalleDivisionProyecto($id_proyecto);
      $estatus = $request->input("estatus");

      $response = $modelo->modificarProyecto($descripcion,$cliente,$fechaContratacion,$divisiones,$estatus,$idProyecto,$divisiones_v);

      if($response["response"]){

        $parametros = [
          "accion" => 'Modificacion del proyecto: '.$descripcion.'. Cliente: '.$response["cliente"],
          "direccion_ip" => $request->session()->get('direccion_ip'),
          "fecha" => date("Y-m-d H:i:s"),
          "tabla" => 'tbl_proyecto',
          "usuario_id" => $request->session()->get('usuario_id')
        ];

        $modeloAudit = new AuditoriaLogModel();
        $modeloAudit->logs_auditoria($parametros);

      }

      return $response;

    }

    function asignarProyectos(Request $request){

      $modelo = new ProyectoModel();
      $permisoActualizar = $modelo->permisoActualizar(session("usuario_id"), 11);
      $permisoCrear = $modelo->permisoCrear(session("usuario_id"), 11);
      $id_usuario = $request->session()->get('usuario_id');
      $infoUsuario = $modelo->detalleInicioUsuario($id_usuario);
      $estatus = $modelo->estatusProyectos();

      if ($infoUsuario->id_cargo === 16 || $infoUsuario->id_cargo === 17) {
        $infoProyectos = $modelo->proyectoUDivision($id_usuario, 11);
        $permisoVer = $modelo->permisoVer(session("usuario_id"), 11);
        return [
        "estatus" => $estatus,
        "proyectos" => $infoProyectos,
        "permisoVer" => $permisoVer,
        "permisoCrear" => $permisoCrear
      ];
      }

      if ($infoUsuario->id_cargo === 15 && $permisoActualizar === "true") {
        $infoProyectos = $modelo->proyectoDDivision($infoUsuario->id_division,$id_usuario, 11);
          return [
            "estatus" => $estatus,
            "proyectos" => $infoProyectos,
            "permisoActualizar" => $permisoActualizar,
            "permisoCrear" => $permisoCrear
        ];
      }

      $infoProyectos = $modelo->proyectoUDivision($id_usuario, 11);
      return [
        "estatus" => $estatus,
        "proyectos" => $infoProyectos,
        "permisoCrear" => $permisoCrear
      ];
    }

    function buscardiviProyectos(Request $request){

      $modelo = new ProyectoModel();
      $id_usuario = $request->session()->get('usuario_id');
      $infoUsuario = $modelo->detalleInicioUsuario($id_usuario);
      $permisoActualizar = $modelo->permisoActualizar(session("usuario_id"), 11);
      $cliente = $request->input("cliente");
      $proyecto = $request->input("proyecto");
      $estatus = $request->input("estatus");
      if ($infoUsuario->id_cargo === 16 || $infoUsuario->id_cargo === 17) {
        $proyectos = $modelo->proyectosUdivi($id_usuario,11,$proyecto, $cliente, $estatus);

        return array("proyectos" => $proyectos);
      }
      if ($infoUsuario->id_cargo === 15 || $permisoActualizar === "true") {
        $proyectos = $modelo->proyectosDdivi($id_usuario,11,$infoUsuario->id_division,$proyecto, $cliente, $estatus);
        if ($estatus === "1" || empty($estatus)) {
          return array("proyectos" => $proyectos, "permisoActualizar" => $permisoActualizar, "estatus" => $estatus);
        }
        return array("proyectos" => $proyectos);
      }
      $proyectos = $modelo->proyectosUdivi($id_usuario,11,$proyecto, $cliente, $estatus);
      return array("proyectos" => $proyectos);
    }

    function DetalleDivProyecto(Request $request){

    $modelo = new ProyectoModel();
    $idDproyecto = (int) $request->input("idDproyecto");
    $infoDproyecto = $modelo->DetalleDivProyecto($idDproyecto);
    $infoAproyecto = $modelo->DetalleAnaProyecto($idDproyecto);
    if(!empty($infoDproyecto)){
      $response = array("response" => true, "infoDproyecto" => $infoDproyecto, "infoAproyecto" => $infoAproyecto);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
    return $response;
  }

    function detalleAnalistaProyecto(Request $request){

      $modelo = new ProyectoModel();
      $id_proyecto = (int) $request->input("idDproyecto");
      $id_usuario = $request->session()->get('usuario_id');
      $infoUsuario = $modelo->detalleInicioUsuario($id_usuario);
      $datosProyecto = $modelo->datosProyecto($id_proyecto,$infoUsuario->id_division);
      $analistas = $modelo->analistasProyecto($id_usuario,11,$id_proyecto,$infoUsuario->id_division);
      if(!empty($datosProyecto)){
      return array("response" => true,"analistas" => $analistas, "proyecto" => $datosProyecto);
      }else{
        $response = array("response" => false, "message" => "No se encontraron resultados");
      }
      return $response;
    }

    function agregarAnalistaProy(Request $request){

      $modelo = new ProyectoModel();
      $estado = $request->input("estado");
      $idUsuario = $request->input("idUsuario");
      $idProyecto = $request->input("idDproyecto");
      $id_proyecto_division = $request->input("id_proyecto_division");
      $usuario_id = $request->session()->get('usuario_id');
      $fecha = date("Y-m-d H:i:s");
      $direccion_ip = $request->session()->get('direccion');
      $analis = $modelo->agregarAnalistaProy($estado,$idUsuario,$idProyecto,$id_proyecto_division);

      if($analis["response"]){

        $parametros = [
          "accion" => 'Asignacion del analista codigo: '.$analis["analista"].'. Al proyecto: '.$analis["proyecto"],
          "direccion_ip" => $request->session()->get('direccion_ip'),
          "fecha" => date("Y-m-d H:i:s"),
          "tabla" => 'tbl_proyecto_analista',
          "usuario_id" => $request->session()->get('usuario_id')
        ];

        $modeloAudit = new AuditoriaLogModel();
        $modeloAudit->logs_auditoria($parametros);

      }

      $id_usuario = $request->session()->get('usuario_id');
      $infoUsuario = $modelo->detalleInicioUsuario($id_usuario);
      $datosProyecto = $modelo->datosProyecto($idProyecto,$infoUsuario->id_division);
      $analistas = $modelo->analistasProyecto($id_usuario,11,$idProyecto,$infoUsuario->id_division);

      $response = array("response" => true, "analis" => $analis,"analistas" => $analistas, "proyecto" => $datosProyecto);

      return $response;

    }

    function modAnalistaProy(Request $request){

      $modelo = new ProyectoModel();
      $idAnaProy = $request->input("idAnaProy");
      $estatus = $modelo->estatusAnalistaProy($idAnaProy);
      $idProyecto = $request->input("idDproyecto");
      if ($estatus->id_estatus === 1) {
        $estado = 0;
      }
      if($estatus->id_estatus === 0) {
        $estado = 1;
      }

      $analis = $modelo->modAnalistaProy($estado,$idAnaProy,$idProyecto);

      if($analis["response"]){

        $parametros = [
          "accion" => $analis["accion"],
          "direccion_ip" => $request->session()->get('direccion_ip'),
          "fecha" => date("Y-m-d H:i:s"),
          "tabla" => 'tbl_proyecto_analista',
          "usuario_id" => $request->session()->get('usuario_id')
        ];

        $modeloAudit = new AuditoriaLogModel();
        $modeloAudit->logs_auditoria($parametros);

      }

      $id_usuario = $request->session()->get('usuario_id');
      $infoUsuario = $modelo->detalleInicioUsuario($id_usuario);
      $datosProyecto = $modelo->datosProyecto($idProyecto,$infoUsuario->id_division);
      $analistas = $modelo->analistasProyecto($id_usuario,11,$idProyecto,$infoUsuario->id_division);
      $response = array("response" => true, "analis" => $analis,"analistas" => $analistas, "proyecto" => $datosProyecto);
      return $response;

    }

    function asigHorasAnalistaProy(Request $request){

      $modelo = new ProyectoModel();
      $idAnaProy = $request->input("idAnaProy");
      $horas_asignadas = $request->input("horas_asignadas");
      $horasComparar = $request->input("horasComparar");
      $usuario_id = $request->session()->get('usuario_id');
      $analis = $modelo->modHorasAnalistaProy($horas_asignadas, $horasComparar, $idAnaProy, $idProyecto);

      if($analis["response"]){

        $modeloAudit = new AuditoriaLogModel();

        for($i = 0; $i < count($analis["horas"]); $i++){

          $parametros = [
            "accion" => $analis["horas"][$i],
            "direccion_ip" => $request->session()->get('direccion_ip'),
            "fecha" => date("Y-m-d H:i:s"),
            "tabla" => 'tbl_proyecto_analista',
            "usuario_id" => $request->session()->get('usuario_id')
          ];

          $modeloAudit->logs_auditoria($parametros);

        }

      }

      $id_usuario = $request->session()->get('usuario_id');
      $infoUsuario = $modelo->detalleInicioUsuario($id_usuario);
      $datosProyecto = $modelo->datosProyecto($idProyecto,$infoUsuario->id_division);
      $analistas = $modelo->analistasProyecto($id_usuario,11,$idProyecto,$infoUsuario->id_division);

      $response = array("response" => true, "analis" => $analis,"analistas" => $analistas, "proyecto" => $datosProyecto);
      return $response;

    }

    function formCargarHoras($idProyAnalista, Request $request){

      $request->session()->put('idProyAnalista', $idProyAnalista);
      return view('horasCargables/cargarHoras');

    }


}
