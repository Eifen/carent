<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\HorasNoCargablesModel;
use Illuminate\Http\RedirectResponse;

class HorasNoCargablesController extends Controller
{

    function dataInicialConceptosHorasNoCargables(){

      $modelo = new HorasNoCargablesModel();
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

      $modelo = new HorasNoCargablesModel();
      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $concepto = $request->input("concepto");
      $estatus = $request->input("estatus");
      $conceptos = $modelo->conceptosHorasNoCargables($paginar, $desde, $concepto, $estatus);
      $cantidadPaginas = $modelo->cantidadPaginasConceptosHorasNoCargables($paginar, $concepto, $estatus);

      return array("conceptos" => $conceptos, "paginas" => $cantidadPaginas);

    }

    function crearConceptoNoCargable(Request $request){

      $modelo = new HorasNoCargablesModel();
      $concepto = $request->input("concepto");

      return $modelo->crearConceptoNoCargable($concepto);

    }

    function modificarConceptoNoCargable(Request $request){

      $modelo = new HorasNoCargablesModel();
      $concepto = $request->input("concepto");
      $id = $request->input("id");
      $id_estatus = $request->input("id_estatus");

      return $modelo->modificarConceptoNoCargable($id,$concepto,$id_estatus);

    }

    function dataInicialHorasNoCargables(){

      $modelo = new HorasNoCargablesModel();
      $puedeCargarVer = $modelo->puedeCargarVer(session("division_id"), session("cargo_id"));

      if($puedeCargarVer["error"] === false){

        $paginar = 10;
        $conceptos = $modelo->conceptos();
        $divisiones = $modelo->divisiones($puedeCargarVer["division"]);
        $empleados = $modelo->empleados($puedeCargarVer["division"], session("usuario_id"), session("cargo_id"));
        $estatus = $modelo->estatusHorasNoCargables();
        //$cantidadPaginas = $modelo->cantidadPaginasConceptosHorasNoCargables($paginar);

        return [
          "conceptos" => $conceptos,
          "divisiones" => $divisiones,
          "empleados" => $empleados,
          "error" => false,
          "estatus" => $estatus,
          /*"numero_paginas" => $cantidadPaginas,*/
          "paginar" => $paginar
        ];

      }else{

        return $puedeCargarVer;

      }

    }

}
