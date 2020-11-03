<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\HorasCargablesModel;

use App\Http\Controllers\Controller;

class HorasCargablesController extends Controller
{

    function dataRepHorasCargables(){

      $modelo = new HorasCargablesModel();

      $paginar = 200;
      $supervisa = $modelo->supervisaA(session("cargo_id"), session("division_id"), session("usuario_id"));
      $cargos = $supervisa["cargos"];
      $divisiones = $supervisa["divisiones"];
      $horas = $modelo->repoHorasCargables(session("usuario_id"), $paginar, $supervisa["supervisa"], $supervisa["supervisaTodo"], $divisiones, $cargos);
      $paginas = $modelo->pagHorasCargables(session("usuario_id"), $paginar, $supervisa["supervisa"], $supervisa["supervisaTodo"], $divisiones, $cargos);
      $totales = $modelo->totalesHorasCargables(session("usuario_id"), $supervisa["supervisa"], $supervisa["supervisaTodo"], $divisiones, $cargos);

      return [
        "cargos" => $cargos,
        "divisiones" => $divisiones,
        "horas" => $horas,
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true,
        "totales" => $totales
      ];

    }

    function buscarHorasCargables(Request $request){

      $modelo = new HorasCargablesModel();

      $supervisa = $modelo->supervisaA(session("cargo_id"), session("division_id"), session("usuario_id"));
      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $cargos = ($request->input("cargos") == null) ? $supervisa["cargos"] : $request->input("cargos");
      $cliente = $request->input("cliente");
      $divisiones = ($request->input("divisiones") == null) ? $supervisa["divisiones"] : $request->input("divisiones");
      $empleado = $request->input("empleado");
      $fecha_desde = $request->input("fechaDesde");
      $fecha_hasta = $request->input("fechaHasta");
      $proyecto = $request->input("proyecto");
      $horas = $modelo->repoHorasCargables(session("usuario_id"), $paginar, $supervisa["supervisa"], $supervisa["supervisaTodo"], $divisiones, $cargos, $desde, $cliente, $proyecto, $empleado, $fecha_desde, $fecha_hasta);
      $paginas = $modelo->pagHorasCargables(session("usuario_id"), $paginar, $supervisa["supervisa"], $supervisa["supervisaTodo"], $divisiones, $cargos, $cliente, $proyecto, $empleado, $fecha_desde, $fecha_hasta);
      $totales = $modelo->totalesHorasCargables(session("usuario_id"), $supervisa["supervisa"], $supervisa["supervisaTodo"], $divisiones, $cargos, $cliente, $proyecto, $empleado, $fecha_desde, $fecha_hasta);

      return array("horas" => $horas, "paginas" => $paginas, "totales" => $totales);

    }

}
