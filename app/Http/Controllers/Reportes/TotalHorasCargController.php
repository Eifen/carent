<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\TotalHorasCargModel as ModeloHoras;

use App\Http\Controllers\Controller;

class TotalHorasCargController extends Controller
{

    function dataRepTotalHorasCarg(){
        $modelo = new ModeloHoras();
        //Agrupamos la data
        $paginar = 50;
        $divisiones = ModeloHoras::Divisiones();
        $fecha_desde = date("Y-m-01");
        $fecha_hasta = date("Y-m-d");
        $paginas = 1;
        $maximo_horas = $modelo->GetHorasTotales($fecha_desde,$fecha_hasta);
        return [
            "divisiones" => $divisiones,
            "fecha_desde" => $fecha_desde,
            "fecha_hasta" => $fecha_hasta,
            "paginas" => $paginas,
            "paginar" => $paginar,
            "maximo_horas" => $maximo_horas,
            "response" => true];

    }

    function buscarRepTotalHorasCarg(Request $request){
      $modelo = new ModeloHoras();
      //Agrupamos la data
      $divisiones = (!is_array($request->input('divisiones')) ? ModeloHoras::Divisiones() 
                    : ModeloHoras::Divisiones($request->input('divisiones')));
      $fecha_desde = ($request->input('fecha_desde') === null ? date('Y-m-01') : date($request->input('fecha_desde')));
      $fecha_hasta = ($request->input('fecha_hasta') === null ? date('Y-m-d') : date($request->input('fecha_hasta')));
      $paginar = $request->input('paginar');
      $paginas = 1;
      //Filtramos el procedimiento. Debe existir minimo una division o en todo caso un nombre
      $totales = $modelo->ReporteActualCargabilidad($fecha_desde, $fecha_hasta, $divisiones,$request->input('empleado'));
      $maximo_horas = $modelo->GetHorasTotales($fecha_desde,$fecha_hasta)["horas"];
      return [
          "divisiones" => $divisiones,
          "totales" => (isset($totales) ? $totales : []),
          "fecha_desde" => $fecha_desde,
          "fecha_hasta" => $fecha_hasta,
          "maximo_horas" => $maximo_horas,
          "paginar" => $paginar,
          "paginas" => $paginas,
          "response" => true];
    }

}
