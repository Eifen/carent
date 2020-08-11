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

      $permisos = $modelo->permisosMenu(session("usuario_id"), 16);
      $divisiones = $modelo->divisiones();
      $estatus = $modelo->estatusProyectos();
      $proyectos = $modelo->proyectosFacturacion();

      return [
        "divisiones" => $divisiones,
        "estatus" => $estatus,
        "permisos" => $permisos,
        "proyectos" => $proyectos,
        "response" => true
      ];

    }

}
