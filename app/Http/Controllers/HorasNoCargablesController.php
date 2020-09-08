<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\HorasNoCargablesModel;
use App\Models\AuditoriaLogModel;
use Illuminate\Http\RedirectResponse;

class HorasNoCargablesController extends Controller
{

    function dataInicialConceptosHorasNoCargables(){

      $modelo = new HorasNoCargablesModel();
      $paginar = 50;
      $conceptos = $modelo->conceptosHorasNoCargables($paginar);
      $estatus = $modelo->estatusHorasNoCargables();
      $cantidadPaginas = $modelo->cantidadPaginasConceptosHorasNoCargables($paginar);

      return [
        "conceptos" => $conceptos,
        "estatus" => $estatus,
        "numero_paginas" => $cantidadPaginas,
        "paginar" => $paginar
      ];

    }

    function buscarConceptoHorasNoCargables(Request $request){

      $modelo = new HorasNoCargablesModel();
      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $concepto = $request->input("concepto");
      $estatus = $request->input("estatus");
      $conceptos = $modelo->conceptosHorasNoCargables($paginar, $desde, $concepto, $estatus);
      $cantidadPaginas = $modelo->cantidadPaginasConceptosHorasNoCargables($paginar, $concepto, $estatus);

      return array("conceptos" => $conceptos, "paginas" => $cantidadPaginas);

    }

    function crearConceptoNoCargable(Request $request){

      $modelo = new HorasNoCargablesModel();
      $concepto = $request->input("concepto");

      $response = $modelo->crearConceptoNoCargable($concepto);

      if($response["respuesta"]){

        $parametros = [
          "accion" => 'Registro del concepto de horas no cargables: '.$concepto,
          "direccion_ip" => $request->session()->get('direccion_ip'),
          "fecha" => date("Y-m-d H:i:s"),
          "tabla" => 'tbl_concepto_horas_no_cargables',
          "usuario_id" => $request->session()->get('usuario_id')
        ];

        $modeloAudit = new AuditoriaLogModel();
        $modeloAudit->logs_auditoria($parametros);

      }

      return $response;

    }

    function modificarConceptoNoCargable(Request $request){

      $modelo = new HorasNoCargablesModel();
      $concepto = $request->input("concepto");
      $id = $request->input("id");
      $id_estatus = $request->input("id_estatus");

      return $modelo->modificarConceptoNoCargable($id,$concepto,$id_estatus);

    }

    function dataInicialHorasNoCargables(){

      $modelo = new HorasNoCargablesModel();

      if(session("cargo_id") !== NULL){

        $paginar = 50;
        $supervisa = $modelo->supervisaA(session("cargo_id"), session("division_id"), session("usuario_id"));
        $horas = $modelo->horasCargadas($paginar, 0, session("usuario_id"), session("division_id"), $supervisa["supervisa"], $supervisa["supervisaTodo"]);
        $cantidadPaginas = $modelo->cantidadPaginasHorasCargadas($paginar, session("usuario_id"), session("division_id"), $supervisa["supervisa"], $supervisa["supervisaTodo"]);

        return [
          "conceptos" => $supervisa["conceptos"],
          "divisiones" => $supervisa["divisiones"],
          "empleados" => $supervisa["empleados"],
          "error" => false,
          "estatus" => $supervisa["estatus"],
          "numero_paginas" => $cantidadPaginas,
          "paginar" => $paginar,
          "registros" => $horas,
          "supervisor" => $supervisa["supervisa"],
          "supervisar_todo" => $supervisa["supervisaTodo"]
        ];

      }else{

        return array("error" => true, "mensaje" => "No posee un cargo válido!");

      }

    }

    function buscarHorasNoCargableCargadas(Request $request){

      $modelo = new HorasNoCargablesModel();
      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $concepto = $request->input("concepto");
      $estatus = $request->input("estatus");
      $empleado = $request->input("empleado");
      $division = $request->input("division");
      $supervisa = $request->input("supervisa");
      $supervisaTodo = $request->input("supervisaTodo");
      $horas = $modelo->horasCargadas($paginar, $desde, $empleado, $division, $supervisa, $supervisaTodo, $concepto, $estatus);
      $cantidadPaginas = $modelo->cantidadPaginasHorasCargadas($paginar, $empleado, $division, $supervisa, $supervisaTodo, $concepto, $estatus);

      return [
        "numero_paginas" => $cantidadPaginas,
        "registros" => $horas
      ];

    }

    function registrarHorasNoCargables(Request $request){

      $modelo = new HorasNoCargablesModel();

      $aprobado_por = ($request->input("estatus") == "" || $request->input("estatus") === 1) ? null : session("usuario_id");
      $fecha_aprobacion = ($request->input("estatus") == "" || $request->input("estatus") === 1) ? null : date("Y-m-d H:i:s");

      $parametrosInsert = array(
        "id_concepto" => $request->input("concepto"),
        "id_usuario" => session("usuario_id"),
        "id_division" => session("division_id"),
        "fecha_desde" => date("Y-m-d H:i:s", strtotime($request->input("fechaDesde"))),
        "fecha_hasta" => date("Y-m-d H:i:s", strtotime($request->input("fechaHasta"))),
        "observacion" => $request->input("observacion"),
        "id_estatus"  => (($request->input("estatus") == "") ? 1 : $request->input("estatus")),
        "aprobado_por" => $aprobado_por,
        "fecha_aprobacion" => $fecha_aprobacion
      );

      $resgitrarHora = $modelo->registrarHorasNoCargables($parametrosInsert);

      if($resgitrarHora["response"]){

        $parametros = [
          "accion" => 'Registro de de horas no cargables al usuario codigo: '.$resgitrarHora["analista"],
          "direccion_ip" => $request->session()->get('direccion_ip'),
          "fecha" => date("Y-m-d H:i:s"),
          "tabla" => 'tbl_horas_no_cargables',
          "usuario_id" => $request->session()->get('usuario_id')
        ];

        $modeloAudit = new AuditoriaLogModel();
        $modeloAudit->logs_auditoria($parametros);

      }

      return $resgitrarHora;

    }

    function modificarHorasNoCargables(Request $request){

      $modelo = new HorasNoCargablesModel();

      $aprobado_por = ($request->input("estatus") == 1) ? null : session("usuario_id");
      $fecha_aprobacion = ($request->input("estatus") == 1) ? null : date("Y-m-d H:i:s");
      $id = $request->input("id");

      $parametrosUpdate = array(
        "id_concepto" => $request->input("concepto"),
        "fecha_desde" => date("Y-m-d H:i:s", strtotime($request->input("fechaDesde"))),
        "fecha_hasta" => date("Y-m-d H:i:s", strtotime($request->input("fechaHasta"))),
        "observacion" => $request->input("observacion"),
        "id_estatus"  => $request->input("estatus"),
        "aprobado_por" => $aprobado_por,
        "fecha_aprobacion" => $fecha_aprobacion
      );

      $modificarHora = $modelo->modificarHorasNoCargables($parametrosUpdate, $id);

      return $modificarHora;

    }

}
