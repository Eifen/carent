<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class EmpleadosModel extends Model
{

    /*
      Función que valida si supervisa a empleados o no
    */
    function supervisaA($id_cargo, $id_division, $id_usuario){

      $sql = DB::select('SELECT * FROM(
                            (
                             SELECT COUNT(1) cargos FROM tbl_cargo_empleado ce WHERE ce.id <> '.$id_cargo.'
                            ) t1,
                            (
                             SELECT COUNT(DISTINCT cs.id_cargo) supervisa
                             FROM tbl_cargo_supervisa cs
                             WHERE cs.id_cargo_supervisor = '.$id_cargo.'
                            ) t2
                        )');

      if($sql[0]->cargos === $sql[0]->supervisa){ // Supervisa a todos

        $condicion_cargos = "";
        $condicion_divisiones = "";
        $supervisor = true;
        $supervisaTodo = true;

      }else if($sql[0]->cargos > $sql[0]->supervisa && $sql[0]->supervisa > 0){ //Directores, Gerentes, etc

        $condicion_cargos = "AND c.id IN(SELECT id_cargo FROM tbl_cargo_supervisa WHERE id_cargo_supervisor = ".$id_cargo." OR id_Cargo = ".$id_cargo.")";
        $condicion_divisiones = " AND d.id = ".$id_division;
        $supervisor = true;
        $supervisaTodo = false;

      }else{

        $condicion_cargos = "AND c.id = ".$id_cargo;
        $condicion_divisiones = " AND d.id = ".$id_division;
        $supervisor = false;
        $supervisaTodo = false;

      }

      $cargos = DB::select('SELECT c.id,
                                   c.descripcion
                            FROM tbl_cargo_empleado c
                            WHERE c.id_estatus = 1
                            '.$condicion_cargos.'
                            ORDER BY c.descripcion ASC');

      $divisiones = DB::select('SELECT d.id,
                                       d.descripcion
                                FROM tbl_division d
                                WHERE d.id_estatus = 1
                                '.$condicion_divisiones.'
                                ORDER BY d.descripcion ASC');

      return [
        "cargos" => $cargos,
        "divisiones" => $divisiones,
        "supervisa" => $supervisor,
        "supervisaTodo" => $supervisaTodo
      ];

    }// Fin supervisaA

    function estatusProyectos(){

      $estatus = DB::select('SELECT e.valor AS id,
                                    e.descripcion
                             FROM tbl_estatus e
                             WHERE tabla = "tbl_proyecto"
                             ORDER BY e.descripcion ASC');

      return $estatus;

    }// Fin estatusProyectos

    function repoEmpleados($id_usuario, $paginar, $supervisa, $supervisaTodo, $divisiones, $cargos, $desde = 0, $empleado = null, $fecha_ingreso = null, $fecha_egreso = null, $estatus = null){

      if($divisiones == null){
        $sql_division = "";
      }else{

        $idsDivision = [];

        if(is_array($divisiones)){

          foreach ($divisiones as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsDivision,$item->id);
          }

        }else{

          array_push($idsDivision,$divisiones);

        }

        $idsDivision = implode(",", $idsDivision);
        $sql_division = "AND u.id_division IN(".$idsDivision.")";

      }

      if($cargos == null){
        $sql_cargos = "";
      }else{

        $idsCargo = [];

        if(is_array($cargos)){

          foreach ($cargos as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsCargo, $item->id);

          }

        }else{

          array_push($idsCargo, $cargos);

        }

        $idsCargo = implode(",", $idsCargo);
        $sql_cargos = "AND u.id_cargo IN(".$idsCargo.")";

      }

      if($supervisaTodo OR $supervisa){

        if($empleado != null && trim($empleado) != ""){
          $sql_empleado = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($empleado).'%"';
        }else{
          $sql_empleado = "";
        }

      }else{

        $sql_empleado = 'AND u.id = '.$id_usuario;

      }

      if($fecha_ingreso !== null){
        $sql_fecha_ingreso = 'AND u.fecha_ingreso = "'.$fecha_ingreso.'"';
      }else{
        $sql_fecha_ingreso = '';
      }

      if($fecha_egreso !== null){
        $sql_fecha_egreso = 'AND u.fecha_egreso = "'.$fecha_egreso.'"';
      }else{
        $sql_fecha_egreso = '';
      }

      if($estatus !== null){
        $sql_estatus = 'AND u.id_estatus = '.$estatus;
      }else{
        $sql_estatus = '';
      }

      $sql = DB::select('SELECT * FROM(
                           SELECT CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS empleado,
                                  DATE_FORMAT(u.fecha_ingreso, "%d/%m/%Y") fecha_ingreso,
                                  DATE_FORMAT(u.fecha_egreso, "%d/%m/%Y") fecha_egreso,
                                  ce.descripcion AS cargo,
                                  d.descripcion AS division,
                                  e.descripcion AS estatus,
                                  u.codigo
                          FROM tbl_usuario u,
                               tbl_cargo_empleado ce,
                               tbl_division d,
                               tbl_estatus e
                          WHERE u.id_cargo = ce.id
                          AND u.id_division = d.id
                          AND u.id_estatus = e.valor
                          AND e.tabla = "tbl_usuario"
                          '.$sql_cargos.'
                          '.$sql_division.'
                          '.$sql_empleado.'
                          '.$sql_fecha_ingreso.'
                          '.$sql_fecha_egreso.'
                          '.$sql_estatus.'
                        )t
                        ORDER BY empleado ASC
                        LIMIT '.$desde.', '.$paginar);

      return $sql;

    }

    function totalesEmpleados($id_usuario, $supervisa, $supervisaTodo, $divisiones, $cargos, $empleado = null, $fecha_ingreso = null, $fecha_egreso = null, $estatus = null){

      if($divisiones == null){
        $sql_division = "";
      }else{

        $idsDivision = [];

        if(is_array($divisiones)){

          foreach ($divisiones as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsDivision,$item->id);
          }

        }else{

          array_push($idsDivision,$divisiones);

        }

        $idsDivision = implode(",", $idsDivision);
        $sql_division = "AND u.id_division IN(".$idsDivision.")";

      }

      if($cargos == null){
        $sql_cargos = "";
      }else{

        $idsCargo = [];

        if(is_array($cargos)){

          foreach ($cargos as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsCargo, $item->id);

          }

        }else{

          array_push($idsCargo, $cargos);

        }

        $idsCargo = implode(",", $idsCargo);
        $sql_cargos = "AND u.id_cargo IN(".$idsCargo.")";

      }

      if($supervisaTodo OR $supervisa){

        if($empleado != null && trim($empleado) != ""){
          $sql_empleado = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($empleado).'%"';
        }else{
          $sql_empleado = "";
        }

      }else{

        $sql_empleado = 'AND u.id = '.$id_usuario;

      }

      if($fecha_ingreso !== null){
        $sql_fecha_ingreso = 'AND u.fecha_ingreso = "'.$fecha_ingreso.'"';
      }else{
        $sql_fecha_ingreso = '';
      }

      if($fecha_egreso !== null){
        $sql_fecha_egreso = 'AND u.fecha_egreso = "'.$fecha_egreso.'"';
      }else{
        $sql_fecha_egreso = '';
      }

      if($estatus !== null){
        $sql_estatus = 'AND u.id_estatus = '.$estatus;
      }else{
        $sql_estatus = '';
      }

      $sql = DB::select('SELECT COUNT(1) AS empleados
                         FROM tbl_usuario u,
                              tbl_cargo_empleado ce,
                              tbl_division d,
                              tbl_estatus e
                         WHERE u.id_cargo = ce.id
                         AND u.id_division = d.id
                         AND u.id_estatus = e.valor
                         AND e.tabla = "tbl_usuario"
                         '.$sql_cargos.'
                         '.$sql_division.'
                         '.$sql_empleado.'
                         '.$sql_fecha_ingreso.'
                         '.$sql_fecha_egreso.'
                         '.$sql_estatus);

      return $sql[0];

    }

    function pagEmpleados($id_usuario, $paginar, $supervisa, $supervisaTodo, $divisiones, $cargos, $empleado = null, $fecha_ingreso = null, $fecha_egreso = null, $estatus = null){

      if($divisiones == null){
        $sql_division = "";
      }else{

        $idsDivision = [];

        if(is_array($divisiones)){

          foreach ($divisiones as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsDivision,$item->id);
          }

        }else{

          array_push($idsDivision,$divisiones);

        }

        $idsDivision = implode(",", $idsDivision);
        $sql_division = "AND u.id_division IN(".$idsDivision.")";

      }

      if($cargos == null){
        $sql_cargos = "";
      }else{

        $idsCargo = [];

        if(is_array($cargos)){

          foreach ($cargos as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsCargo, $item->id);

          }

        }else{

          array_push($idsCargo, $cargos);

        }

        $idsCargo = implode(",", $idsCargo);
        $sql_cargos = "AND u.id_cargo IN(".$idsCargo.")";

      }

      if($supervisaTodo OR $supervisa){

        if($empleado != null && trim($empleado) != ""){
          $sql_empleado = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($empleado).'%"';
        }else{
          $sql_empleado = "";
        }

      }else{

        $sql_empleado = 'AND u.id = '.$id_usuario;

      }

      if($fecha_ingreso !== null){
        $sql_fecha_ingreso = 'AND u.fecha_ingreso = "'.$fecha_ingreso.'"';
      }else{
        $sql_fecha_ingreso = '';
      }

      if($fecha_egreso !== null){
        $sql_fecha_egreso = 'AND u.fecha_egreso = "'.$fecha_egreso.'"';
      }else{
        $sql_fecha_egreso = '';
      }

      if($estatus !== null){
        $sql_estatus = 'AND u.id_estatus = '.$estatus;
      }else{
        $sql_estatus = '';
      }

      $sql = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                         FROM(

                           SELECT CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS empleado,
                                  DATE_FORMAT(u.fecha_ingreso, "%d/%m/%Y") fecha_ingreso,
                                  DATE_FORMAT(u.fecha_egreso, "%d/%m/%Y") fecha_egreso,
                                  ce.descripcion AS cargo,
                                  d.descripcion AS division,
                                  e.descripcion AS estatus
                          FROM tbl_usuario u,
                               tbl_cargo_empleado ce,
                               tbl_division d,
                               tbl_estatus e
                          WHERE u.id_cargo = ce.id
                          AND u.id_division = d.id
                          AND u.id_estatus = e.valor
                          AND e.tabla = "tbl_usuario"
                          '.$sql_cargos.'
                          '.$sql_division.'
                          '.$sql_empleado.'
                          '.$sql_fecha_ingreso.'
                          '.$sql_fecha_egreso.'
                          '.$sql_estatus.'
                         )t'
                       );

      return $sql[0]->paginas;

    }

}
