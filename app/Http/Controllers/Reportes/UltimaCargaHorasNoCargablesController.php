<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\UltimaCargaHorasNoCargablesModel;

use App\Http\Controllers\Controller;

class UltimaCargaHorasNoCargablesController extends Controller
{

    function dataRepUltimaCargaHorasNo(){

      $modelo = new UltimaCargaHorasNoCargablesModel();

      $paginar = 200;
      $divisiones = $modelo->divisiones();
      $cargos = $modelo->cargos();
      $ultimaCarga = $modelo ->ultimaCargaHorasNo($paginar, $divisiones, $cargos);      
      $paginas = $modelo->pagCantidadUltimaCargaHorasNo($paginar, $divisiones, $cargos);      
      return [
        "divisiones" => $divisiones,
        "ultimaCarga" => $ultimaCarga,
        "cargos" => $cargos,
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true,
      ];

    }

    function buscarUltimaCargaHorasNo(Request $request){

      $modelo = new UltimaCargaHorasNoCargablesModel();

      $division = $modelo->divisiones();
      $cargos = $modelo->cargos();
      $paginar = $request->input("paginar");
      $desde = $request->input("desde");    
      $divisiones = ($request->input("divisiones") == null) ? $division : $request->input("divisiones");
      $cargos = ($request->input("cargos") == null) ? $cargos : $request->input("cargos");
      $empleado = $request->input("empleado");
      $ultimaCarga = $modelo->ultimaCargaHorasNo($paginar, $divisiones, $cargos, $empleado, $desde);
      $paginas = $modelo->pagCantidadUltimaCargaHorasNo( $paginar, $divisiones, $cargos); 

      return [
        "divisiones" => $divisiones,
        "cargos" => $cargos,
        "ultimaCarga" => $ultimaCarga,
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true,
      ];

    }

}
