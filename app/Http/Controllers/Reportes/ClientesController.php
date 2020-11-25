<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\ClientesModel;

use App\Http\Controllers\Controller;

class ClientesController extends Controller
{

    function dataRepClientes(){

      $modelo = new ClientesModel();

      $paginar = [
        "desde" => 0,
        "paginar" => 200
      ];
      $filtros = [
        "razon_social" => null,
        "rif" => null,
        "socio" => null,
        "estatus" => null
      ];
      $estatus = $modelo->estatusClientes();
      $clientes = $modelo->repoClientes($paginar, $filtros);
      $paginas = $modelo->pagClientes($paginar, $filtros);
      $totales = $modelo->totalesClientes($filtros);

      return [
        "estatus" => $estatus,
        "clientes" => $clientes,
        "paginas" => $paginas,
        "paginar" => $paginar["paginar"],
        "response" => true,
        "totales" => $totales
      ];

    }

    function consultarClientes(Request $request){

      $modelo = new ClientesModel();

      $paginar = [
        "desde" => $request->input("desde"),
        "paginar" => $request->input("paginar")
      ];
      $filtros = [
        "razon_social" => $request->input("razonSocial"),
        "rif" => $request->input("rif"),
        "socio" => $request->input("socio"),
        "estatus" => $request->input("estatus")
      ];
      $estatus = $modelo->estatusClientes();
      $clientes = $modelo->repoClientes($paginar, $filtros);
      $paginas = $modelo->pagClientes($paginar, $filtros);
      $totales = $modelo->totalesClientes($filtros);

      return array("clientes" => $clientes, "paginas" => $paginas, "totales" => $totales);

    }

}
