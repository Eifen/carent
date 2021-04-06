<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\UltimaCargaModel;

use App\Http\Controllers\Controller;

class UltimaCargaController extends Controller
{

    function dataRepUltimaCarga(){

      $modelo = new UltimaCargaModel();

      $paginar = 200;
      $divisiones = $modelo->divisiones();
      $cargos = $modelo->cargos();
      $ultimaCarga = $modelo ->ultimaCarga($paginar, $divisiones, $cargos);      
      $paginas = $modelo->pagCantidadUltimaCarga( $paginar, $divisiones, $cargos);      
      return [
        "divisiones" => $divisiones,
        "ultimaCarga" => $ultimaCarga,
        "cargos" => $cargos,
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true,
      ];

    }

    function buscarUltimaCarga(Request $request){

      $modelo = new UltimaCargaModel();

      $division = $modelo->divisiones();
      $cargos = $modelo->cargos();
      $paginar = $request->input("paginar");
      $desde = $request->input("desde");    
      $divisiones = ($request->input("divisiones") == null) ? $division : $request->input("divisiones");
      $cargos = ($request->input("cargos") == null) ? $cargos : $request->input("cargos");
      $empleado = $request->input("empleado");

      $ultimaCarga = $modelo->ultimaCarga($paginar, $divisiones, $cargos, $empleado, $desde);
      $paginas = $modelo->pagCantidadUltimaCarga( $paginar, $divisiones, $cargos); 

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
