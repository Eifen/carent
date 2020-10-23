<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class HorasCargablesModel extends Model
{

    function cargosEmpleado(){

      $sql = DB::select('SELECT c.id,
                                c.descripcion
                         FROM tbl_cargo_empleado c
                         WHERE c.id_estatus = 1
                         ORDER BY c.descripcion ASC');

      return $sql;

    }// Fin cargosEmpleado

    function divisiones(){

      $sql = DB::select('SELECT d.id,
                                d.descripcion
                         FROM tbl_division d
                         WHERE d.id_estatus = 1
                         ORDER BY d.descripcion ASC');

      return $sql;

    }// Fin divisiones

    function repoHorasCargables($paginar, $desde = 0, $cargos = null, $cliente = null, $divisiones = null, $proyecto = null, $empleado = null){

      if($cargos !== null){

        $idsCargo = [];
        foreach ($cargos as $key => $item) {
          $item = json_decode($item);
          array_push($idsCargo,$item->id);
        }

        $idsCargo = implode(",", $idsCargo);
        $sql_cargos = "AND ce.id IN(".$idsCargo.")";

      }else{
        $sql_cargos = "";
      }

      if($cliente != null && trim($cliente) != ""){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if($divisiones !== null){

        $idsDivision = [];
        foreach ($divisiones as $key => $item) {
          $item = json_decode($item);
          array_push($idsDivision,$item->id);
        }

        $idsDivision = implode(",", $idsDivision);
        $sql_division = "AND u.id_division IN(".$idsDivision.")";

      }else{
        $sql_division = "";
      }

      if($proyecto != null && trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
      }

      if($empleado != null && trim($empleado) != ""){
        $sql_empleado = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($empleado).'%"';
      }else{
        $sql_empleado = "";
      }

      $sql = DB::select('SELECT p.id AS id_proyecto,
                                p.descripcion AS proyecto,
                                u.id_division,
                                d.descripcion AS division,
                                u.id AS id_usuario,
                                u.id_cargo,
                                ce.descripcion AS cargo,
                                CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS empleado,
                                c.id AS id_cliente,
                                c.razon_social AS cliente,
                                TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(hc.horas_trabajadas))),"%H:%i") horas_trabajadas
                         FROM tbl_horas_cargables hc,
                              tbl_proyecto_analista pa,
                              tbl_usuario u,
                              tbl_proyecto p,
                              tbl_cliente c,
                              tbl_division d,
                              tbl_cargo_empleado ce
                         WHERE hc.id_proy_analista = pa.id
                         AND pa.id_analista = u.id
                         AND pa.id_proyecto = p.id
                         AND p.id_cliente = c.id
                         AND u.id_division = d.id
                         AND u.id_cargo = ce.id
                         '.$sql_cargos.'
                         '.$sql_cliente.'
                         '.$sql_division.'
                         '.$sql_proyecto.'
                         '.$sql_empleado.'
                         GROUP BY p.id,
                                  p.descripcion,
                                  u.nombre_1,
                                  u.nombre_2,
                                  u.apellido_1,
                                  u.apellido_2,
                                  c.razon_social,
                                  c.id,
                                  u.id,
                                  u.id_division,
                                  u.id_cargo
                         LIMIT '.$desde.', '.$paginar);

      return $sql;

    }

    function pagHorasCargables($paginar, $cargos = null, $cliente = null, $divisiones = null, $proyecto = null, $empleado = null){

      if($cargos !== null){

        $idsCargo = [];
        foreach ($cargos as $key => $item) {
          $item = json_decode($item);
          array_push($idsCargo,$item->id);
        }

        $idsCargo = implode(",", $idsCargo);
        $sql_cargos = "AND ce.id IN(".$idsCargo.")";

      }else{
        $sql_cargos = "";
      }

      if($cliente != null && trim($cliente) != ""){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if($divisiones !== null){

        $idsDivision = [];
        foreach ($divisiones as $key => $item) {
          $item = json_decode($item);
          array_push($idsDivision,$item->id);
        }

        $idsDivision = implode(",", $idsDivision);
        $sql_division = "AND u.id_division IN(".$idsDivision.")";

      }else{
        $sql_division = "";
      }

      if($proyecto != null && trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
      }

      if($empleado != null && trim($empleado) != ""){
        $sql_empleado = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($empleado).'%"';
      }else{
        $sql_empleado = "";
      }

      $sql = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                         FROM(

                           SELECT p.descripcion AS proyecto,
                                  u.id_division,
                                  u.id_cargo,
                                  ce.descripcion AS cargo,
                                  c.razon_social
                           FROM tbl_horas_cargables hc,
                                tbl_proyecto_analista pa,
                                tbl_usuario u,
                                tbl_proyecto p,
                                tbl_cliente c,
                                tbl_division d,
                                tbl_cargo_empleado ce
                           WHERE hc.id_proy_analista = pa.id
                           AND pa.id_analista = u.id
                           AND pa.id_proyecto = p.id
                           AND p.id_cliente = c.id
                           AND u.id_division = d.id
                           AND u.id_cargo = ce.id
                           '.$sql_cargos.'
                           '.$sql_cliente.'
                           '.$sql_division.'
                           '.$sql_proyecto.'
                           '.$sql_empleado.'
                           GROUP BY p.descripcion,
                                    u.id_division,
                                    u.id_cargo,
                                    ce.descripcion,
                                    c.razon_social
                         )t'
                       );

      return $sql[0]->paginas;

    }

}
