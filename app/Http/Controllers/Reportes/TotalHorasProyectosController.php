<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\TotalHorasProyectosModel;

use App\Http\Controllers\Controller;

class TotalHorasProyectosController extends Controller
{

    function dataRepTotalHorasProyectos(){

      $modelo = new TotalHorasProyectosModel();

      $paginar = 200;
      $empleados = $modelo->empleados();
      $estatus = $modelo->estatusProyectos();
      $fecha_hasta = date("Y-m-d");
      $totalHorasProyectos = $modelo->repoTotalHorasProy(session("usuario_id"), $paginar, $fecha_hasta);
      $paginas = $modelo->pagCantidadTotalHorasProy(session("usuario_id"), $paginar,$fecha_hasta);

      return [
        "empleados" => $empleados,
        "estatus" => $estatus,
        "totalHorasProyectos" => $totalHorasProyectos,
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true
      ];

    }

    function buscarTotalHorasProyectos(Request $request){

      $modelo = new TotalHorasProyectosModel();

      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $cliente = $request->input("cliente");      
      $id_usuario = $request->input("empleado");
      $id_usuario_Calidad = $request->input("empleadoC");
      $proyecto = $request->input("proyecto");
      $estatus = $request->input("estatus");
      $fecha_desde = $request->input("fecha_desde");
      $fecha_hasta = $request->input("fecha_hasta");
      if ($fecha_hasta === null) {
        $fecha_hasta = date("Y-m-d"); 
      } 
      $totalHorasProyectos = $modelo->repoTotalHorasProy(session("usuario_id"), $paginar, $fecha_hasta, $fecha_desde, $desde, $proyecto, $id_usuario, $id_usuario_Calidad,$cliente, $estatus);      
      $paginas = $modelo->pagCantidadTotalHorasProy(session("usuario_id"), $paginar, $fecha_hasta, $fecha_desde, $proyecto, $id_usuario, $id_usuario_Calidad, $cliente, $estatus);


      return array("totalHorasProyectos" => $totalHorasProyectos, "paginas" => $paginas);

    }

}
