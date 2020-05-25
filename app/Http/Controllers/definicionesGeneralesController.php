<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\DefinicionesGeneralesModel;
use Illuminate\Http\RedirectResponse;

class DefinicionesGeneralesController extends Controller
{

    function dataInicialHorasNoCargables(){

      $modelo = new DefinicionesGeneralesModel();
      $paginar = 10;
      $conceptos = $modelo->conceptosHorasNoCargables($paginar);
      $estatus = $modelo->estatusHorasNoCargables();
      $cantidadPaginas = $modelo->cantidadPaginasConceptosHorasNoCargables($paginar);

      return [
        "conceptos" => $conceptos,
        "estatus" => $estatus,
        "numero_paginas" => $cantidadPaginas,
        "paginar" => $paginar
      ];

    }

    function buscarConceptoHorasNoCargables(Request $request){

      $modelo = new DefinicionesGeneralesModel();
      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $concepto = $request->input("concepto");
      $estatus = $request->input("estatus");
      $conceptos = $modelo->conceptosHorasNoCargables($paginar, $desde, $concepto, $estatus);
      $cantidadPaginas = $modelo->cantidadPaginasConceptosHorasNoCargables($paginar, $concepto, $estatus);

      return array("conceptos" => $conceptos, "paginas" => $cantidadPaginas);

    }

    function crearConceptoNoCargable(Request $request){

      $modelo = new DefinicionesGeneralesModel();
      $concepto = $request->input("concepto");

      return $modelo->crearConceptoNoCargable($concepto);

    }

    function modificarConceptoNoCargable(Request $request){

      $modelo = new DefinicionesGeneralesModel();
      $concepto = $request->input("concepto");
      $id = $request->input("id");
      $id_estatus = $request->input("id_estatus");

      return $modelo->modificarConceptoNoCargable($id,$concepto,$id_estatus);

    }

}
