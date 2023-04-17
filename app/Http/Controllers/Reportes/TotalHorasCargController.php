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
        $paginar = 200;
        $divisiones = ModeloHoras::Divisiones();
        $fecha_desde = date("Y-m-01");
        $fecha_hasta = date("Y-m-d");
        $totales = $modelo->ReporteActualCargabilidad($fecha_desde, $fecha_hasta, $divisiones);
        $paginas = 1;
        return [
            "divisiones" => $divisiones,
            "totales" => $totales,
            "fecha_desde" => $fecha_desde,
            "fecha_hasta" => $fecha_hasta,
            "paginas" => $paginas,
            "paginar" => $paginar,
            "response" => true,
        ];

    }

    function buscarRepTotalHorasCarg(Request $request){

      //

    }

}
