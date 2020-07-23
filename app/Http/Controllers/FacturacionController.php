<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\FacturacionModel;

class FacturacionController extends Controller
{

    function formIngresosGastos(){

      return view('facturacion/ingresosGastos');

    }

    function dataInicialIngresosGastos(){

      $modelo = new FacturacionModel();

      $divisiones = $modelo->divisiones();
      $estatus = $modelo->estatusProyectos();

      return [
        "divisiones" => $divisiones,
        "estatus" => $estatus
      ];

    }

}
