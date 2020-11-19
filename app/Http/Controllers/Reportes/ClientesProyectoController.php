<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\ClientesProyectoModel;

use App\Http\Controllers\Controller;

class ClientesProyectoController extends Controller
{

    function dataRepClientesProyectos(){

      $modelo = new ClientesProyectoModel();

      $paginar = 200;
      $divisiones = $modelo->divisiones();
      $estatus = $modelo->estatusClientes();
      $empresa = $modelo->empresaClientes();
      $clientes = $modelo->repoCantidadClientes(session("usuario_id"), $paginar, $divisiones);
      $cantidadClie = $modelo->cantidadClie();
      $paginas = $modelo->pagCantidadClientes(session("usuario_id"), $paginar, $divisiones);

      return [
        "divisiones" => $divisiones,
        "estatus" => $estatus,
        "empresa" => $empresa,
        "clientes" => $clientes,
        "cantidadClie" => $cantidadClie,
        "paginas" => $paginas,
        "paginar" => $paginar,
        "response" => true
      ];

    }

    function buscarClientesProyectos(Request $request){

      $modelo = new ClientesProyectoModel();

      $division = $modelo->divisiones();
      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $cliente = $request->input("cliente");
      $divisiones = ($request->input("divisiones") == null) ? $division : $request->input("divisiones");
      $empresa = $request->input("empresa");
      $estatus = $request->input("estatus");

      $clientes = $modelo->repoCantidadClientes(session("usuario_id"), $paginar, $divisiones, $desde, $empresa, $cliente, $estatus);

      $paginas = $modelo->pagCantidadClientes(session("usuario_id"), $paginar, $divisiones, $empresa, $cliente, $estatus);


      return array("clientes" => $clientes, "paginas" => $paginas, "empresa" => $empresa);

    }

}
