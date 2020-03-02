<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
use App\Models\CreaModel;
use Illuminate\Http\RedirectResponse;

class CreaController extends Controller
{
  function crearCargo(Request $request){

      $modelo = new CreaModel();
      $cargo = $modelo->buscarCargo($request->input("nuevoCargo"));
        if($cargo["response"]){
            $parametros = array(
              "nuevoCargo" => $request->input("nuevoCargo"),
            );
            $response = $modelo->crearCargo($parametros);
        }else{
          $response = array("response" => false, "message" => "El cargo ya se encuentra ");
        }
      return $response;
    }

  function crearDivision(Request $request){

    $modelo = new CreaModel();
    $division = $modelo->buscarDivision($request->input("nuevaDivision"));
      if($division["response"]){
           $parametros = array(
            "nuevaDivision" => $request->input("nuevaDivision"),
          );
          $response = $modelo->crearDivision($parametros);
      }else{
        $response = array("response" => false, "message" => "la division ya se encuentra ");
      }
    return $response;
  }

  function buscarRegistro(Request $request){

    $modelo = new CreaModel();
    $buscarPor = (int) $request->input("buscarPor");
    $registros = $modelo->buscarRegistros($buscarPor);
    if(!empty($registros)){
      $response = array("response" => true, "registros" => $registros);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultadoos");
    }
    return $response;
  }

  function detalleRegistro(Request $request){

  $modelo = new CreaModel();
  $buscarPor = (int) $request->input("buscarPor");
  $id_Registro = (int) $request->input("idRegistro");
  $infoRegistro = $modelo->detalleRegistros($id_Registro, $buscarPor);
  if(!empty($infoRegistro)){
    $response = array("response" => true, "info" => $infoRegistro);
  }else{
    $response = array("response" => false, "message" => "No se encontraron resultados");
  }
    return $response;
  }
}