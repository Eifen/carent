<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\TotalHorasEmpModel;

use App\Http\Controllers\Controller;

class TotalHorasEmpController extends Controller
{

    function dataRepTotalHorasEmp(){

      $modelo = new TotalHorasEmpModel();

      $divisiones = $modelo->divisiones();

      return [
        "divisiones" => $divisiones,
        "response" => true
      ];

    }

    function repTotalHorasEmpEmpleadosDivision(Request $request){

      $modelo = new TotalHorasEmpModel();
      $id_division = $request->input("id_division");

      $empleados = $modelo->empleados($id_division);

      return [
        "empleados" => $empleados
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
