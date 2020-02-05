<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CreaModel extends Model
{
  function buscarRegistros($opcionBusqueda){

    switch ((int) $opcionBusqueda) {
      case 1:
        $usuarios = DB::select('SELECT id,
                                    descripcion,
                                    id_estatus
                         FROM tbl_division ');
      break;
      case 2:
        $usuarios = DB::select('SELECT id,
                                    descripcion,
                                    id_estatus
                         FROM tbl_cargo_empleado ');
      break;
    }
    if(count($usuarios) > 0){
      return $usuarios;

    }else{
      return array();
    }
  }

  function detalleRegistros($id_Registro, $opcionBusqueda){

    switch ((int) $opcionBusqueda) {
      case 1:
      $info = DB::select('SELECT id,
                                    descripcion,
                                    id_estatus
                         FROM tbl_division 
                         WHERE id='.$id_Registro.'');
      break;
      case 2:
        $info = DB::select('SELECT id,
                                    descripcion,
                                    id_estatus
                         FROM tbl_cargo_empleado
                         WHERE id='.$id_Registro.' ');
      break;
    }
    if(count($info) > 0){
      return $info[0];
    }else{
      return array();
    }
  }

  function buscarCargo($cargo){

    $cargo = DB::select('SELECT descripcion
                             FROM tbl_cargo_empleado
                             WHERE descripcion = "'.$cargo.'"');
     if(count($cargo) > 0){
      return array("response" => false, "message" => "No se encontraron coincidencias");
    }else{
        return array("response" => true, "message" => "Ya se encuentran registrados los correos");
    }
  }// Fin buscarCargo

  function buscarDivision($division){

    $division = DB::select('SELECT descripcion
                             FROM tbl_division
                             WHERE descripcion = "'.$division.'"');
     if(count($division) > 0){
      return array("response" => false, "message" => "No se encontraron coincidencias");
    }else{
        return array("response" => true, "message" => "Ya se encuentran registrados los correos");
    }
  }// Fin buscarCargo

  function crearDivision($parametros){

    DB::beginTransaction();

     $data = array("descripcion" => $parametros["nuevaDivision"],
                   "id_estatus" => 1);
      $contacto = DB::table('tbl_division')->insert($data);
      if($contacto){
        DB::commit();
        return array("response" => true, "message" => "division Creada con Éxito.");
      }else{
       DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de crear el Campo.");
      }
  }

  function crearCargo($parametros){

    DB::beginTransaction();

     $data = array("descripcion" => $parametros["nuevoCargo"],
                  "id_estatus" => 1);
      $contacto = DB::table('tbl_cargo_empleado')->insert($data);
      if($contacto){
      DB::commit();
      return array("response" => true, "message" => "Cargo Creado con Éxito.");
    }else{
      DB::rollBack();
      return array("response" => false, "message" => "Error al tratar de crear el Campo.");
    }
  }
}
