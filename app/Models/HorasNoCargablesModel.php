<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class HorasNoCargablesModel extends Model
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

    /*
      Función que valida si el usuario puede cargar o ver las Horas No Cargables
      ya sea para visualizar o crear un nuevo registro
    */
    function puedeCargarVer($id_division, $id_cargo){

      // Se evalua el cargo del usuario
      if($id_cargo !== NULL){

        /*
           Hacemos una consulta para verificar si la razón por la cual no tiene
           división asociada es porque puede ver todas con su cargo
        */
        $sql = DB::select('SELECT (
                                   SELECT COUNT(1) - 1
                                   FROM tbl_cargo_empleado ce
                                  )
                                  -
                                  (
                                   SELECT COUNT(1)
                                   FROM tbl_cargo_supervisa cs
                                   WHERE cs.id_cargo_supervisor = '.$id_cargo.'
                                  ) sin_supervisar');

        if($sql[0]->sin_supervisar === 0){
          $id_division = 0;
        }

        // Se evalua la división del usuario
        if($id_division !== NULL){

          return array("error" => false, "division" => $id_division);

        }else{

          return array("error" => true, "mensaje" => "No posee una división asociada!");

        }

      }else{

        return array("error" => true, "mensaje" => "No posee un cargo válido!");

      }

    }// Fin puedeCargarVer

    function conceptos(){

      $conceptos = DB::select('SELECT c.id,
                                      c.descripcion
                               FROM tbl_concepto_horas_no_cargables c
                               ORDER BY c.descripcion ASC');

      return $conceptos;

    }// Fin conceptos

    function divisiones($division){

      if($division == 0){
        $sql_condicion = "";
      }else{
        $sql_condicion = " WHERE d.id = ".$division;
      }

      $divisiones = DB::select('SELECT d.id,
                                       d.descripcion
                               FROM tbl_division d
                               '.$sql_condicion.'
                               ORDER BY d.descripcion ASC');

      return $divisiones;

    }// Fin divisiones

    function empleados($division, $id_usuario, $id_cargo){

      if($division == 0){
        $sql_division = "";
      }else{
        $sql_division = " AND u.id_division = ".$division;
      }

      $sql_supervisados = 'SELECT cs.id_cargo
                                  FROM tbl_cargo_supervisa cs
                                  WHERE cs.id_cargo_supervisor = '.$id_cargo;
      $supervisados = DB::select($sql_supervisados);

      if(count($supervisados) > 0){

        $empleados =  DB::select('SELECT * FROM(
                                    SELECT u.id,
                                           CONCAT(u.nombre_1," ",u.apellido_1) nombre,
                                           u.codigo
                                    FROM tbl_usuario u
                                    WHERE u.id_cargo IN ('.$sql_supervisados.')
                                    '.$sql_division.'

                                    UNION

                                    SELECT u.id,
                                           CONCAT(u.nombre_1," ",u.apellido_1) nombre,
                                           u.codigo
                                    FROM tbl_usuario u
                                    WHERE u.id = '.$id_usuario.') t
                                  ORDER BY nombre ASC');

      }else{

        $empleados =  DB::select('SELECT u.id,
                                         CONCAT(u.nombre_1," ",u.apellido_1) nombre,
                                         u.codigo
                                  FROM tbl_usuario u
                                  WHERE u.id = '.$id_usuario);

      }

      return $empleados;

    }// Fin empleados

}
