<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
use App\Models\HorasCargadasModel;
use Illuminate\Http\RedirectResponse;

class HorasCargadasController extends Controller
{

    function datosHorasProyecto(Request $request){
      $modelo = new HorasCargadasModel;
      $idProyAnalista = (int) session("idProyAnalista");
      $permisoActualizar = $modelo->permisoActualizar(session("usuario_id"), 11);
      $permisoEliminar = $modelo->permisoEliminar(session("usuario_id"), 11);
      $permisoCrear = $modelo->permisoCrear(session("usuario_id"), 11,$idProyAnalista);
      $infoProyAnalista = $modelo->detalleProyAnalista($idProyAnalista);
      $infoHorasCargadas = $modelo->horasCargadas($idProyAnalista);

      if(!empty($infoProyAnalista)){
      	$response = array(
        				"infoProyAnalista" => $infoProyAnalista,
        				"idProyAnalista" => $idProyAnalista,
        				"infoHorasCargadas" => $infoHorasCargadas,
        				"permisoActualizar" => $permisoActualizar,
        				"permisoEliminar" => $permisoEliminar,
        				"permisoCrear" => $permisoCrear,
      				);
  	  }else{

        $response = array("response" => false, "message" => "No se encontraron resultados");

      }

      return $response;

    }

    function cargarHoras(Request $request){

      $modelo = new HorasCargadasModel();
      $idProyAnalista = (int) session("idProyAnalista");
      $fechaC = $request->input("fecha");
      $fecha = date('Y-m-d', strtotime($fechaC));
      $descripcion = mb_strtoupper($request->input("descripcion"));
      $horas_trabajadas = $request->input("horas_trabajadas");
      
      $response = $modelo->cargarHoras($idProyAnalista,$fecha,$descripcion,$horas_trabajadas);
      return $response;

    }

    function detalleModHorasCargadas(Request $request){

      $modelo = new HorasCargadasModel();
      $idHcargadas = $request->input("idHcargadas");      
      $infoModHorasCargadas = $modelo->detalleModHorasCargadas($idHcargadas);
      $response = array(
        				"infoModHorasCargadas" => $infoModHorasCargadas,
      				);
      return $response;

    }

    function ModificarHorasCargadas(Request $request){

      $modelo = new HorasCargadasModel();
      $idHoraCargada = $request->input("id");
      $fechaC = $request->input("fecha");
      $fecha = date('Y-m-d', strtotime("$fechaC -1 day"));
      $descripcion = mb_strtoupper($request->input("descripcion"));
      $horas_trabajadas = $request->input("horas_trabajadas");
      
      $response = $modelo->ModificarHorasCargadas($idHoraCargada,$fecha,$descripcion,$horas_trabajadas);
      return $response;

    }

    function detalleHorasEliminar(Request $request){

      $modelo = new HorasCargadasModel();
      $idHcargadas = $request->input("idHcargadas");      
      $infoeliHorasCargadas = $modelo->detalleEliHorasCargadas($idHcargadas);
      $response = array(
        				"infoeliHorasCargadas" => $infoeliHorasCargadas,
      				);
      return $response;

    }

    function EliminarHorasCargadas(Request $request){

      $modelo = new HorasCargadasModel();
      $idHoraCargada = $request->input("id");      
      $response = $modelo->EliminarHorasCargadas($idHoraCargada);
      return $response;

    }


}
