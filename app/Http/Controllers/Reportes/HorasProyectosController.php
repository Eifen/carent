<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\HorasProyectosModel;

use App\Http\Controllers\Controller;

class HorasProyectosController extends Controller
{

    function dataRepHorasProyectos(){

      $modelo = new HorasProyectosModel();

      $paginar = 200;
      $cargos = $modelo->cargos();
      $usuario_div = $modelo->divisionUsuario(session("usuario_id"));
      $divisiones = $modelo->divisiones($usuario_div,session("usuario_id"));
      $horasProyectos = $modelo->repoCantidadHorasProy(session("usuario_id"),$paginar,$divisiones, $cargos);
      $paginas = $modelo->pagCantidadHorasProy(session("usuario_id"), $paginar, $divisiones, $cargos);

      return [
        "divisiones" => $divisiones,
        "cargos" => $cargos,
        "horasProyectos" => $horasProyectos,
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true
      ];

    }

    function buscarHorasProyectos(Request $request){

      $modelo = new HorasProyectosModel();

      $usuario_div = $modelo->divisionUsuario(session("usuario_id"));
      $division = $modelo->divisiones($usuario_div,session("usuario_id"));
      $cargo = $modelo->cargos();
      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $cliente = $request->input("cliente");      
      $divisiones = ($request->input("divisiones") == null) ? $division : $request->input("divisiones");
      $cargos = ($request->input("cargos") == null) ? $cargo : $request->input("cargos");
      $empleado = $request->input("empleado");
      $proyecto = $request->input("proyecto");

      $horasProyectos = $modelo->repoCantidadHorasProy(session("usuario_id"), $paginar, $divisiones, $cargos, $desde, $proyecto, $empleado, $cliente);
      
      $paginas = $modelo->pagCantidadHorasProy(session("usuario_id"), $paginar, $divisiones, $cargos, $proyecto, $empleado, $cliente);


      return array("horasProyectos" => $horasProyectos, "paginas" => $paginas);

    }

}
