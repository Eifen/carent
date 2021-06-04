<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\TotalHorasNoCargModel;

use App\Http\Controllers\Controller;

class TotalHorasNoCargController extends Controller
{

    function dataRepTotalNoHorasCarg(Request $request){

      $modelo = new TotalHorasNoCargModel();

      $paginar = 200;
      $cargos = $modelo->cargos();


      $usuario_id = $request->session()->get('usuario_id');
      $usuario_div = $modelo->divisionUsuario($usuario_id);
      $divisiones = $modelo->divisiones($usuario_div, $usuario_id);      
      $concepto = $modelo->concepto();
      $fecha_desde = date("Y-01-01");
      $fecha_hasta = date("Y-m-d");      
      $totales = $modelo ->horasNoCargables($fecha_desde, $fecha_hasta, $concepto, $divisiones, $cargos);      
      $paginas = 1;      
      return [
        "cargos" => $cargos,
        "divisiones" => $divisiones,
        "concepto" => $concepto,
        "totales" => $totales,
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true,
      ];

    }

    function buscarRepTotalHorasNoCarg(Request $request){

      $modelo = new TotalHorasNoCargModel();

      $usuario_id = $request->session()->get('usuario_id');
      $usuario_div = $modelo->divisionUsuario($usuario_id);
      $divisiones = $modelo->divisiones($usuario_div, $usuario_id); 
      $concepto = $modelo->concepto();
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
      $concepto = ($request->input("concepto") == null) ? $division : $request->input("concepto");   
      $divisiones = ($request->input("divisiones") == null) ? $division : $request->input("divisiones");
      $cargos = ($request->input("cargos") == null) ? $cargo : $request->input("cargos");
      $empleado = $request->input("empleado");


      $totales = $modelo->horasNoCargables($fecha_desde, $fecha_hasta, $concepto, $divisiones, $cargos, $empleado);

      return [
        "cargos" => $cargos,
        "divisiones" => $divisiones,
        "concepto" => $concepto,
        "totales" => $totales,
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true,
      ];

    }

}
