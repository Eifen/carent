<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\totalHorasAsigModel;

use App\Http\Controllers\Controller;

class totalHorasAsigController extends Controller
{

    function dataRepTotalHorasAsig(){

      $modelo = new totalHorasAsigModel();

      $paginar = 200;
      $divisiones = $modelo->divisiones();
      $fecha_desde = date("Y-01-01");
      $fecha_hasta = date("Y-m-d");      
      $horas_asignadas = $modelo ->horasAsignadas($paginar, $fecha_desde, $fecha_hasta, $divisiones);      
      $paginas = 1;      
      return [
        "divisiones" => $divisiones,
        "horas_asignadas" => $horas_asignadas,
        "fecha_desde" => $fecha_desde,
        "fecha_hasta" => $fecha_hasta,
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true,
      ];

    }

    function buscarTotalHorasAsig(Request $request){

      $modelo = new totalHorasAsigModel();

      $division = $modelo->divisiones();
      $paginar = $request->input("paginar");
      $paginas = 1;
      $desde = $request->input("desde");
      $fecha_desde = $request->input("fecha_desde");
      $fecha_hasta = $request->input("fecha_hasta");
      if ($fecha_desde === null) {
        $fecha_desde = date("Y-01-01");
        $fecha_hasta = date("Y-m-d"); 
      }else if ($fecha_hasta === null) {
        $fecha_hasta = date("Y-m-d"); 
      }     
      $divisiones = ($request->input("divisiones") == null) ? $division : $request->input("divisiones");
      $empleado = $request->input("empleado");


      $horas_asignadas = $modelo->horasAsignadas($paginar, $fecha_desde, $fecha_hasta, $divisiones, $empleado, $desde);

      return [
        "divisiones" => $divisiones,
        "horas_asignadas" => $horas_asignadas,
        "fecha_desde" => $fecha_desde,
        "fecha_hasta" => $fecha_hasta,
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true,
      ];

    }

}
