<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
use App\Models\ProyectoModel;
use Illuminate\Http\RedirectResponse;

class ProyectoController extends Controller
{

    function dataInicialNuevoProyecto(){

      $modelo = new ProyectoModel();
      $clientes = $modelo->clientes();
      $divisiones = $modelo->divisiones();
      $estatus = $modelo->estatusProyectos();

      return [
        "clientes" => $clientes,
        "divisiones" => $divisiones,
        "estatus" => $estatus
      ];

    }

    function crearProyecto(Request $request){

      $modelo = new ProyectoModel();
      $descripcion = $request->input("descripcion");
      $cliente = $request->input("cliente");
      $horas = $request->input("horas");
      $fechaContratacion = $request->input("fechaContratacion");
      $divisiones = $request->input("divisiones");
      $estatus = $request->input("estatus");

      $response = $modelo->crearProyecto($descripcion,$cliente,$horas,$fechaContratacion,$divisiones,$estatus);
      return $response;

    }

    function dataInicialListadoProyectos(){

      $modelo = new ProyectoModel();
      $paginar = 10;
      $permisoActualizar = $modelo->permisoActualizar(session("usuario_id"), 8);
      $proyectos = $modelo->proyectos(session("division_id"), $paginar);
      $divisiones = $modelo->divisiones();
      $estatus = $modelo->estatusProyectos();
      $cantidadPaginas = $modelo->cantidadPaginas($paginar);

      return [
        "divisiones" => $divisiones,
        "estatus" => $estatus,
        "numero_paginas" => $cantidadPaginas,
        "paginar" => $paginar,
        "permisoActualizar" => $permisoActualizar,
        "proyectos" => $proyectos
      ];

    }

    function buscarProyectos(Request $request){

      $modelo = new ProyectoModel();
      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $cliente = $request->input("cliente");
      $divisiones = $request->input("divisiones");
      $proyecto = $request->input("proyecto");
      $estatus = $request->input("estatus");
      $proyectos = $modelo->proyectos(session("division_id"), $paginar, $desde, $proyecto, $cliente, $divisiones, $estatus);
      $cantidadPaginas = $modelo->cantidadPaginas($paginar, $proyecto, $cliente, $divisiones, $estatus);

      return array("proyectos" => $proyectos, "paginas" => $cantidadPaginas);

    }

}
