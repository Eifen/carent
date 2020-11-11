<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\EmpleadosModel;

use App\Http\Controllers\Controller;

class EmpleadosController extends Controller
{

    function dataRepEmpleados(){

      $modelo = new EmpleadosModel();

      $supervisa = $modelo->supervisaA(session("cargo_id"), session("division_id"), session("usuario_id"));

      $paginar = [
        "desde" => 0,
        "paginar" => 200
      ];
      $filtros = [
        "cargos" => $supervisa["cargos"],
        "codigo" => null,
        "divisiones" => $supervisa["divisiones"],
        "empleado" => null,
        "estatus" => null,
        "fecha_ingreso" => null,
        "fecha_egreso" => null,
        "id_usuario" => session("usuario_id"),
        "supervisa" => $supervisa["supervisa"],
        "supervisaTodo" => $supervisa["supervisaTodo"]
      ];

      $cargos = $supervisa["cargos"];
      $divisiones = $supervisa["divisiones"];
      $estatus = $modelo->estatusEmpleado();
      $empleados = $modelo->repoEmpleados($paginar, $filtros);
      $paginas = $modelo->pagEmpleados($paginar, $filtros);
      $totales = $modelo->totalesEmpleados($filtros);

      return [
        "cargos" => $cargos,
        "divisiones" => $divisiones,
        "estatus" => $estatus,
        "empleados" => $empleados,
        "paginas" => $paginas,
        "paginar" => $paginar["paginar"],
        "response" => true,
        "totales" => $totales
      ];

    }

    function buscarEmpleados(Request $request){

      $modelo = new EmpleadosModel();

      $supervisa = $modelo->supervisaA(session("cargo_id"), session("division_id"), session("usuario_id"));

      $paginar = [
        "desde" => $request->input("desde"),
        "paginar" => $request->input("paginar")
      ];
      $filtros = [
        "cargos" => ($request->input("cargos") == null) ? $supervisa["cargos"] : $request->input("cargos"),
        "codigo" => $request->input("codigo"),
        "divisiones" => ($request->input("divisiones") == null) ? $supervisa["divisiones"] : $request->input("divisiones"),
        "empleado" => $request->input("empleado"),
        "estatus" => $request->input("estatus"),
        "fecha_ingreso" => $request->input("fechaIngreso"),
        "fecha_egreso" => $request->input("fechaEgreso"),
        "id_usuario" => session("usuario_id"),
        "supervisa" => $supervisa["supervisa"],
        "supervisaTodo" => $supervisa["supervisaTodo"]
      ];

      $empleados = $modelo->repoEmpleados($paginar, $filtros);
      $paginas = $modelo->pagEmpleados($paginar, $filtros);
      $totales = $modelo->totalesEmpleados($filtros);

      return array("empleados" => $empleados, "paginas" => $paginas, "totales" => $totales);

    }

}
