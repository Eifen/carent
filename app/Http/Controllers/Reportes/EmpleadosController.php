<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\EmpleadosModel;

use App\Http\Controllers\Controller;

class EmpleadosController extends Controller
{

    function dataRepEmpleados(){

      $modelo = new EmpleadosModel();

      $paginar = 200;
      $supervisa = $modelo->supervisaA(session("cargo_id"), session("division_id"), session("usuario_id"));
      $cargos = $supervisa["cargos"];
      $divisiones = $supervisa["divisiones"];
      $estatus = $modelo->estatusProyectos();
      $empleados = $modelo->repoEmpleados(session("usuario_id"), $paginar, $supervisa["supervisa"], $supervisa["supervisaTodo"], $divisiones, $cargos);
      $paginas = $modelo->pagEmpleados(session("usuario_id"), $paginar, $supervisa["supervisa"], $supervisa["supervisaTodo"], $divisiones, $cargos);
      $totales = $modelo->totalesEmpleados(session("usuario_id"), $supervisa["supervisa"], $supervisa["supervisaTodo"], $divisiones, $cargos);

      return [
        "cargos" => $cargos,
        "divisiones" => $divisiones,
        "estatus" => $estatus,
        "empleados" => $empleados,
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true,
        "totales" => $totales
      ];

    }

    function buscarEmpleados(Request $request){

      $modelo = new EmpleadosModel();

      $supervisa = $modelo->supervisaA(session("cargo_id"), session("division_id"), session("usuario_id"));
      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $cargos = ($request->input("cargos") == null) ? $supervisa["cargos"] : $request->input("cargos");
      $divisiones = ($request->input("divisiones") == null) ? $supervisa["divisiones"] : $request->input("divisiones");
      $empleado = $request->input("empleado");
      $estatus = $request->input("estatus");
      $fecha_ingreso = $request->input("fechaIngreso");
      $fecha_egreso = $request->input("fechaEgreso");
      $empleados = $modelo->repoEmpleados(session("usuario_id"), $paginar, $supervisa["supervisa"], $supervisa["supervisaTodo"], $divisiones, $cargos, $desde, $empleado, $fecha_ingreso, $fecha_egreso, $estatus);
      $paginas = $modelo->pagEmpleados(session("usuario_id"), $paginar, $supervisa["supervisa"], $supervisa["supervisaTodo"], $divisiones, $cargos, $empleado, $fecha_ingreso, $fecha_egreso, $estatus);
      $totales = $modelo->totalesEmpleados(session("usuario_id"), $supervisa["supervisa"], $supervisa["supervisaTodo"], $divisiones, $cargos, $empleado, $fecha_ingreso, $fecha_egreso, $estatus);

      return array("empleados" => $empleados, "paginas" => $paginas, "totales" => $totales);

    }

}
