<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class HorasCargablesModel extends Model
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

    function repoHorasCargables($id_usuario, $paginar, $supervisa, $supervisaTodo, $divisiones, $cargos, $desde = 0, $cliente = null, $proyecto = null, $empleado = null, $fecha_desde = null, $fecha_hasta = null){

      if($supervisaTodo){
        $sql_division = "";
      }else if($divisiones == null){
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

      if($supervisaTodo){
        $sql_cargos = "";
      }else if($cargos == null){
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
        $sql_cargos = "AND ce.id IN(".$idsCargo.")";

      }

      if($cliente != null && trim($cliente) != ""){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if($proyecto != null && trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
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

      if($fecha_desde != null && $fecha_hasta != null){
        $sql_fecha = 'AND hc.fecha BETWEEN "'.$fecha_desde.'" AND "'.$fecha_hasta.'"';
      }else if($fecha_desde != null && $fecha_hasta == null){
        $sql_fecha = 'AND hc.fecha = "'.$fecha_desde.'"';
      }else{
        $sql_fecha = '';
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
                         '.$sql_fecha.'
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

    function totalesHorasCargables($id_usuario, $supervisa, $supervisaTodo, $divisiones, $cargos, $cliente = null, $proyecto = null, $empleado = null, $fecha_desde = null, $fecha_hasta = null){

      if($supervisaTodo){
        $sql_division = "";
      }else if($divisiones == null){
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

      if($supervisaTodo){
        $sql_cargos = "";
      }else if($cargos == null){
        $sql_cargos = "";
      }else{

        $idsCargo = [];

        if(is_array($cargos)){

          foreach ($cargos as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsCargo,$item->id);
          }

        }else{

          array_push($idsCargo,$cargos);

        }

        $idsCargo = implode(",", $idsCargo);
        $sql_cargos = "AND ce.id IN(".$idsCargo.")";

      }

      if($cliente != null && trim($cliente) != ""){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if($proyecto != null && trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
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

      if($fecha_desde != null && $fecha_hasta != null){
        $sql_fecha = 'AND hc.fecha BETWEEN "'.$fecha_desde.'" AND "'.$fecha_hasta.'"';
      }else if($fecha_desde != null && $fecha_hasta == null){
        $sql_fecha = 'AND hc.fecha = "'.$fecha_desde.'"';
      }else{
        $sql_fecha = '';
      }

      $sql = DB::select('SELECT CONCAT(
                                  IF(
                                     LENGTH(FLOOR(SUM(TIME_TO_SEC(hc.horas_trabajadas))/3600)) = 1,
                                     CONCAT("0",FLOOR(SUM(TIME_TO_SEC(hc.horas_trabajadas))/3600)),
                                     FLOOR(SUM(TIME_TO_SEC(hc.horas_trabajadas))/3600)
                                    ),
                                  ":",
                                  IF(
                                     LENGTH(FLOOR(SUM(TIME_TO_SEC(hc.horas_trabajadas))/60)%60) = 1,
                                     CONCAT("0",FLOOR(SUM(TIME_TO_SEC(hc.horas_trabajadas))/60)%60),
                                     FLOOR(SUM(TIME_TO_SEC(hc.horas_trabajadas))/60)%60
                                    )
                                ) horas_trabajadas
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
                         '.$sql_fecha);

      return $sql[0];

    }

    function pagHorasCargables($id_usuario, $paginar, $supervisa, $supervisaTodo, $divisiones, $cargos, $cliente = null, $proyecto = null, $empleado = null, $fecha_desde = null, $fecha_hasta = null){

      if($supervisaTodo){
        $sql_division = "";
      }else if($divisiones == null){
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

      if($supervisaTodo){
        $sql_cargos = "";
      }else if($cargos == null){
        $sql_cargos = "";
      }else{

        $idsCargo = [];

        if(is_array($cargos)){

          foreach ($cargos as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsCargo,$item->id);
          }

        }else{

          array_push($idsCargo,$cargos);

        }

        $idsCargo = implode(",", $idsCargo);
        $sql_cargos = "AND ce.id IN(".$idsCargo.")";

      }

      if($cliente != null && trim($cliente) != ""){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if($proyecto != null && trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
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

      if($fecha_desde != null && $fecha_hasta != null){
        $sql_fecha = 'AND hc.fecha BETWEEN "'.$fecha_desde.'" AND "'.$fecha_hasta.'"';
      }else if($fecha_desde != null && $fecha_hasta == null){
        $sql_fecha = 'AND hc.fecha = "'.$fecha_desde.'"';
      }else{
        $sql_fecha = '';
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
                           '.$sql_fecha.'
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
