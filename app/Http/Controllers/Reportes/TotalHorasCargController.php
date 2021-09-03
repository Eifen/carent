<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\TotalHorasCargModel;

use App\Http\Controllers\Controller;

class TotalHorasCargController extends Controller
{

    function dataRepTotalHorasCarg(){

      $modelo = new TotalHorasCargModel();

      $paginar = 200;
      $cargos = $modelo->cargos();
      $divisiones = $modelo->divisiones();
      $fecha_desde = date("Y-01-01");
      $fecha_hasta = date("Y-m-d");      
      $totales = $modelo ->horasCargadas($fecha_desde, $fecha_hasta, $divisiones, $cargos);      
      $paginas = 1;      
      return [
        "cargos" => $cargos,
        "divisiones" => $divisiones,
        "totales" => $totales["total"],
        "maximo_horas" => $totales["maximo_horas"],
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true,
      ];

    }

    function buscarRepTotalHorasCarg(Request $request){

      $modelo = new TotalHorasCargModel();

      $division = $modelo->divisiones();
      $cargo = $modelo->cargos();
      $paginar = $request->input("paginar");
      $paginas = 1;
      $fecha_desde = $request->input("fecha_desde");
      $fecha_hasta = $request->input("fecha_hasta");
      if ($fecha_desde === null) {
        $fecha_desde = date("Y-01-01");
        $fecha_hasta = date("Y-m-d"); 
      }else if ($fecha_hasta === null) {
        $fecha_hasta = date("Y-m-d"); 
      }     
      $divisiones = ($request->input("divisiones") == null) ? $division : $request->input("divisiones");
      $cargos = ($request->input("cargos") == null) ? $cargo : $request->input("cargos");
      $empleado = $request->input("empleado");


      $totales = $modelo->horasCargadas($fecha_desde, $fecha_hasta, $divisiones, $cargos, $empleado);

      return [
        "cargos" => $cargos,
        "divisiones" => $divisiones,
        "totales" => $totales["total"],
        "maximo_horas" => $totales["maximo_horas"],
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true,
      ];

    }

}
