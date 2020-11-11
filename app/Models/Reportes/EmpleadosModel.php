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

    function estatusEmpleado(){

      $estatus = DB::select('SELECT e.valor AS id,
                                    e.descripcion
                             FROM tbl_estatus e
                             WHERE tabla = "tbl_proyecto"
                             ORDER BY e.descripcion ASC');

      return $estatus;

    }// Fin estatusEmpleado

    function repoEmpleados($paginar, $filtros){

      if($filtros["divisiones"] == null){
        $sql_division = "";
      }else{

        $idsDivision = [];

        if(is_array($filtros["divisiones"])){

          foreach ($filtros["divisiones"] as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsDivision,$item->id);
          }

        }else{

          array_push($idsDivision,$filtros["divisiones"]);

        }

        $idsDivision = implode(",", $idsDivision);
        $sql_division = "AND u.id_division IN(".$idsDivision.")";

      }

      if($filtros["cargos"] == null){
        $sql_cargos = "";
      }else{

        $idsCargo = [];

        if(is_array($filtros["cargos"])){

          foreach ($filtros["cargos"] as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsCargo, $item->id);

          }

        }else{

          array_push($idsCargo, $filtros["cargos"]);

        }

        $idsCargo = implode(",", $idsCargo);
        $sql_cargos = "AND u.id_cargo IN(".$idsCargo.")";

      }

      if($filtros["supervisaTodo"] OR $filtros["supervisa"]){

        if($filtros["empleado"] != null && trim($filtros["empleado"]) != ""){
          $sql_empleado = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($filtros["empleado"]).'%"';
        }else{
          $sql_empleado = "";
        }

      }else{

        $sql_empleado = 'AND u.id = '.$filtros["id_usuario"];

      }

      if($filtros["fecha_ingreso"] !== null){
        $sql_fecha_ingreso = 'AND u.fecha_ingreso = "'.$filtros["fecha_ingreso"].'"';
      }else{
        $sql_fecha_ingreso = '';
      }

      if($filtros["fecha_egreso"] !== null){
        $sql_fecha_egreso = 'AND u.fecha_egreso = "'.$filtros["fecha_egreso"].'"';
      }else{
        $sql_fecha_egreso = '';
      }

      if($filtros["estatus"] !== null){
        $sql_estatus = 'AND u.id_estatus = '.$filtros["estatus"];
      }else{
        $sql_estatus = '';
      }

      if($filtros["codigo"] !== null){
        $sql_codigo = 'AND u.codigo LIKE "%'.$filtros["codigo"].'%"';
      }else{
        $sql_codigo = '';
      }

      $sql = DB::select('SELECT * FROM(
                           SELECT CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS empleado,
                                  DATE_FORMAT(u.fecha_ingreso, "%d/%m/%Y") fecha_ingreso,
                                  DATE_FORMAT(u.fecha_egreso, "%d/%m/%Y") fecha_egreso,
                                  ce.descripcion AS cargo,
                                  d.descripcion AS division,
                                  e.descripcion AS estatus,
                                  u.codigo,
                                  e.valor AS id_estatus
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
                          '.$sql_codigo.'
                        )t
                        ORDER BY empleado ASC
                        LIMIT '.$paginar["desde"].', '.$paginar["paginar"]);

      return $sql;

    }

    function totalesEmpleados($filtros){

      if($filtros["divisiones"] == null){
        $sql_division = "";
      }else{

        $idsDivision = [];

        if(is_array($filtros["divisiones"])){

          foreach ($filtros["divisiones"] as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsDivision,$item->id);
          }

        }else{

          array_push($idsDivision,$filtros["divisiones"]);

        }

        $idsDivision = implode(",", $idsDivision);
        $sql_division = "AND u.id_division IN(".$idsDivision.")";

      }

      if($filtros["cargos"] == null){
        $sql_cargos = "";
      }else{

        $idsCargo = [];

        if(is_array($filtros["cargos"])){

          foreach ($filtros["cargos"] as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsCargo, $item->id);

          }

        }else{

          array_push($idsCargo, $filtros["cargos"]);

        }

        $idsCargo = implode(",", $idsCargo);
        $sql_cargos = "AND u.id_cargo IN(".$idsCargo.")";

      }

      if($filtros["supervisaTodo"] OR $filtros["supervisa"]){

        if($filtros["empleado"] != null && trim($filtros["empleado"]) != ""){
          $sql_empleado = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($filtros["empleado"]).'%"';
        }else{
          $sql_empleado = "";
        }

      }else{

        $sql_empleado = 'AND u.id = '.$filtros["id_usuario"];

      }

      if($filtros["fecha_ingreso"] !== null){
        $sql_fecha_ingreso = 'AND u.fecha_ingreso = "'.$filtros["fecha_ingreso"].'"';
      }else{
        $sql_fecha_ingreso = '';
      }

      if($filtros["fecha_egreso"] !== null){
        $sql_fecha_egreso = 'AND u.fecha_egreso = "'.$filtros["fecha_egreso"].'"';
      }else{
        $sql_fecha_egreso = '';
      }

      if($filtros["estatus"] !== null){
        $sql_estatus = 'AND u.id_estatus = '.$filtros["estatus"];
      }else{
        $sql_estatus = '';
      }

      if($filtros["codigo"] !== null){
        $sql_codigo = 'AND u.codigo LIKE "%'.$filtros["codigo"].'%"';
      }else{
        $sql_codigo = '';
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
                         '.$sql_estatus.'
                         '.$sql_codigo);

      return $sql[0];

    }

    function pagEmpleados($paginar, $filtros){

      if($filtros["divisiones"] == null){
        $sql_division = "";
      }else{

        $idsDivision = [];

        if(is_array($filtros["divisiones"])){

          foreach ($filtros["divisiones"] as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsDivision,$item->id);
          }

        }else{

          array_push($idsDivision,$filtros["divisiones"]);

        }

        $idsDivision = implode(",", $idsDivision);
        $sql_division = "AND u.id_division IN(".$idsDivision.")";

      }

      if($filtros["cargos"] == null){
        $sql_cargos = "";
      }else{

        $idsCargo = [];

        if(is_array($filtros["cargos"])){

          foreach ($filtros["cargos"] as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsCargo, $item->id);

          }

        }else{

          array_push($idsCargo, $filtros["cargos"]);

        }

        $idsCargo = implode(",", $idsCargo);
        $sql_cargos = "AND u.id_cargo IN(".$idsCargo.")";

      }

      if($filtros["supervisaTodo"] OR $filtros["supervisa"]){

        if($filtros["empleado"] != null && trim($filtros["empleado"]) != ""){
          $sql_empleado = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($filtros["empleado"]).'%"';
        }else{
          $sql_empleado = "";
        }

      }else{

        $sql_empleado = 'AND u.id = '.$filtros["id_usuario"];

      }

      if($filtros["fecha_ingreso"] !== null){
        $sql_fecha_ingreso = 'AND u.fecha_ingreso = "'.$filtros["fecha_ingreso"].'"';
      }else{
        $sql_fecha_ingreso = '';
      }

      if($filtros["fecha_egreso"] !== null){
        $sql_fecha_egreso = 'AND u.fecha_egreso = "'.$filtros["fecha_egreso"].'"';
      }else{
        $sql_fecha_egreso = '';
      }

      if($filtros["estatus"] !== null){
        $sql_estatus = 'AND u.id_estatus = '.$filtros["estatus"];
      }else{
        $sql_estatus = '';
      }

      if($filtros["codigo"] !== null){
        $sql_codigo = 'AND u.codigo LIKE "%'.$filtros["codigo"].'%"';
      }else{
        $sql_codigo = '';
      }

      $sql = DB::select('SELECT CEILING( COUNT(1) / '.$paginar["paginar"].') paginas
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
                          '.$sql_codigo.'
                         )t'
                       );

      return $sql[0]->paginas;

    }

}
