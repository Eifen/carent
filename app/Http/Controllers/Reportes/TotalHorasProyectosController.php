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
      $totalHorasProyectos = $modelo->repoTotalHorasProy($paginar);
      $paginas = $modelo->pagCantidadTotalHorasProy($paginar);

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
      $proyecto = $request->input("proyecto");
      $estatus = $request->input("estatus");
      $totalHorasProyectos = $modelo->repoTotalHorasProy($paginar, $desde, $proyecto, $id_usuario, $cliente, $estatus);      
      $paginas = $modelo->pagCantidadTotalHorasProy($paginar, $proyecto, $id_usuario, $cliente, $estatus);


      return array("totalHorasProyectos" => $totalHorasProyectos, "paginas" => $paginas);

    }

}
