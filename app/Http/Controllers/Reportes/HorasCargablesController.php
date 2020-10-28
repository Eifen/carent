<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\HorasCargablesModel;

use App\Http\Controllers\Controller;

class HorasCargablesController extends Controller
{

    function dataRepHorasCargables(){

      $modelo = new HorasCargablesModel();

      $paginar = 50;
      $cargos = $modelo->cargosEmpleado();
      $divisiones = $modelo->divisiones();
      $horas = $modelo->repoHorasCargables($paginar);
      $paginas = $modelo->pagHorasCargables($paginar);
      $totales = $modelo->totalesHorasCargables();

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

      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $cargos = $request->input("cargos");
      $cliente = $request->input("cliente");
      $divisiones = $request->input("divisiones");
      $empleado = $request->input("empleado");
      $fecha_desde = $request->input("fechaDesde");
      $fecha_hasta = $request->input("fechaHasta");
      $proyecto = $request->input("proyecto");
      $horas = $modelo->repoHorasCargables($paginar, $desde, $cargos, $cliente, $divisiones, $proyecto, $empleado, $fecha_desde, $fecha_hasta);
      $paginas = $modelo->pagHorasCargables($paginar, $cargos, $cliente, $divisiones, $proyecto, $empleado, $fecha_desde, $fecha_hasta);
      $totales = $modelo->totalesHorasCargables($cargos, $cliente, $divisiones, $proyecto, $empleado, $fecha_desde, $fecha_hasta);

      return array("horas" => $horas, "paginas" => $paginas, "totales" => $totales);

    }

}
