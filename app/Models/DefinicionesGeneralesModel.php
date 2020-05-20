<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class DefinicionesGeneralesModel extends Model
{

    function conceptosHorasNoCargables($paginar, $desde = 0, $concepto = "", $estatus = null){

      if(trim($concepto) != ""){
        $sql_concepto = 'AND LOWER(c.descripcion) LIKE "%'.strtolower($concepto).'%"';
      }else{
        $sql_concepto = "";
      }

      if($estatus != null){
        $sql_estatus = 'AND c.id_estatus = '.$estatus;
      }else{
        $sql_estatus = "";
      }

      $conceptos = DB::select('SELECT c.id,
                                      c.descripcion,
                                      e.descripcion AS estatus,
                                      c.id_estatus
                               FROM tbl_concepto_horas_no_cargables c,
                                    tbl_estatus e
                               WHERE c.id_estatus = e.valor
                               AND tabla = "tbl_concepto_horas_no_cargables"
                               '.$sql_concepto.'
                               '.$sql_estatus.'
                               ORDER BY c.descripcion ASC
                               LIMIT '.$desde.', '.$paginar);

      return $conceptos;

    }// Fin conceptosHorasNoCargables

    function estatusHorasNoCargables(){

      $estatus = DB::select('SELECT e.valor AS id,
                                    e.descripcion
                             FROM tbl_estatus e
                             WHERE tabla = "tbl_concepto_horas_no_cargables"
                             ORDER BY e.descripcion ASC');

      return $estatus;

    }// Fin estatusHorasNoCargables

    function cantidadPaginasConceptosHorasNoCargables($paginar, $concepto = "", $estatus = null){

      if(trim($concepto) != ""){
        $sql_concepto = 'AND LOWER(c.descripcion) LIKE "%'.strtolower($concepto).'%"';
      }else{
        $sql_concepto = "";
      }

      if($estatus != null){
        $sql_estatus = 'AND c.id_estatus = '.$estatus;
      }else{
        $sql_estatus = "";
      }

      $numConceptos = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                                  FROM tbl_concepto_horas_no_cargables c
                                  WHERE c.id IS NOT NULL
                                  '.$sql_concepto.'
                                  '.$sql_estatus);

      return $numConceptos[0]->paginas;

    }

    function crearConceptoNoCargable($concepto){

      if(DB::table('tbl_concepto_horas_no_cargables')->insert(array("descripcion" => $concepto, "id_estatus" => 1))){
        return array("respuesta" => true, "mensaje" => "Concepto creado con éxito!");
      }else{
        return array("respuesta" => false, "mensaje" => "Error al crear el concepto, intente nuevamente!");
      }

    }// Fin crearConceptoNoCargable

    function modificarConceptoNoCargable($id,$concepto,$id_estatus){

      if(DB::table('tbl_concepto_horas_no_cargables')->where("id",$id)->update(array("descripcion" => $concepto, "id_estatus" => $id_estatus))){
        return array("respuesta" => true, "mensaje" => "Concepto modificado con éxito!");
      }else{
        return array("respuesta" => false, "mensaje" => "Error al modificar el concepto, intente nuevamente!");
      }

    }// Fin modificarConceptoNoCargable

}
