<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\facturadoCliProyModel;

use App\Http\Controllers\Controller;

class facturadoCliProyController extends Controller
{

    function dataRepFacturadoCliProy(){

      $modelo = new facturadoCliProyModel();

      $paginar = [
        "desde" => 0,
        "paginar" => 200
      ];
      $filtros = [
        "estatus" => null,
        "proyecto" => null,
        "monedas" => null,
        "razon_social" => null,
        "rif" => null
      ];

      $estatus = $modelo->estatusProyectos();
      $monedas = $modelo->monedas();
      $registros = $modelo->repFacturadoCliProy($paginar, $filtros);
      $paginas = $modelo->pagFacturadoCliProy($paginar, $filtros);
      $totales = $modelo->totalesFacturadoCliProy($filtros);

      return [
        "estatus" => $estatus,
        "monedas" => $monedas,
        "paginas" => $paginas,
        "paginar" => $paginar["paginar"],
        "registros" => $registros,
        "response" => true,
        "totales" => $totales
      ];

    }

    function filtrarCliProy(Request $request){

      $modelo = new facturadoCliProyModel();

      $paginar = [
        "desde" => $request->input("desde"),
        "paginar" => $request->input("paginar")
      ];
      $filtros = [
        "razon_social" => $request->input("razonSocial"),
        "rif" => $request->input("rif"),
        "proyecto" => $request->input("proyecto"),
        "estatus" => $request->input("estatus"),
        "monedas" => $request->input("monedas")
      ];
      $registros = $modelo->repFacturadoCliProy($paginar, $filtros);
      $paginas = $modelo->pagFacturadoCliProy($paginar, $filtros);
      $totales = $modelo->totalesFacturadoCliProy($filtros);

      return [
        "paginas" => $paginas,
        "registros" => $registros,
        "totales" => $totales
      ];

    }

}
