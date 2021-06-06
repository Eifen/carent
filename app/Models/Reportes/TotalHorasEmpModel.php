<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class TotalHorasEmpModel extends Model
{

    function divisiones(){

      $divisiones = DB::select('SELECT d.id AS id,
                                       d.descripcion
                                FROM tbl_division d
                                ORDER BY d.descripcion ASC');

      return $divisiones;

    }// Fin divisiones

    function empleados($id_division){

      $sql = DB::select('SELECT * FROM(
                           SELECT u.id,
                                  CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre
                           FROM tbl_usuario u
                           WHERE u.id_division = ?
                         )t
                         ORDER BY nombre ASC', [$id_division]);

      return $sql;

    }// Fin empleados

    function horas_cargables($parametros){

      $sql = DB::select('SELECT p.id AS id_proyecto,
                                p.descripcion AS proyecto_concepto,
                                TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(hc.horas_trabajadas))),"%H:%i") AS horas
                         FROM tbl_proyecto_analista pa,
                              tbl_usuario u,
                              tbl_proyecto p,
                              tbl_horas_cargables hc
                         WHERE pa.id_analista = u.id
                         AND pa.id_proyecto = p.id
                         AND hc.id_proy_analista = pa.id
                         AND u.id = '.$parametros["id_usuario"].'
                         AND hc.fecha BETWEEN "'.$parametros["fecha_desde"].'" AND "'.$parametros["fecha_hasta"].'"
                         GROUP BY p.id, p.descripcion');

      return $sql;

    }

    function horas_no_cargables($parametros){

      $sql = DB::select('SELECT chnc.descripcion AS proyecto_concepto,
                                CONCAT(
                                  IF(
                                     LENGTH(FLOOR(SUM(TIME_TO_SEC(TIMEDIFF(hnc.fecha_hasta, hnc.fecha_desde)))/3600)) = 1,
                                     CONCAT("0",FLOOR(SUM(TIME_TO_SEC(TIMEDIFF(hnc.fecha_hasta, hnc.fecha_desde)))/3600)),
                                     FLOOR(SUM(TIME_TO_SEC(TIMEDIFF(hnc.fecha_hasta, hnc.fecha_desde)))/3600)
                                    ),
                                  ":",
                                  IF(
                                     LENGTH(FLOOR(SUM(TIME_TO_SEC(TIMEDIFF(hnc.fecha_hasta, hnc.fecha_desde)))/60)%60) = 1,
                                     CONCAT("0",FLOOR(SUM(TIME_TO_SEC(TIMEDIFF(hnc.fecha_hasta, hnc.fecha_desde)))/60)%60),
                                     FLOOR(SUM(TIME_TO_SEC(TIMEDIFF(hnc.fecha_hasta, hnc.fecha_desde)))/60)%60
                                    )
                                ) horas
                         FROM tbl_horas_no_cargables hnc,
                              tbl_concepto_horas_no_cargables chnc
                         WHERE hnc.id_concepto = chnc.id
                         AND hnc.id_usuario = '.$parametros["id_usuario"].'
                         AND hnc.id_estatus IN(1,2)
                         AND hnc.fecha_desde BETWEEN "'.$parametros["fecha_desde"].' 00:00:00" AND "'.$parametros["fecha_hasta"].' 23:59:00"
                         AND hnc.fecha_hasta BETWEEN "'.$parametros["fecha_desde"].' 00:00:00" AND "'.$parametros["fecha_hasta"].' 23:59:00"
                         GROUP BY chnc.descripcion');

      return $sql;

    }

    function total_horas($parametros){

      $sql1 = DB::select('SELECT CONCAT(
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
                                ) horas
                          FROM tbl_proyecto_analista pa,
                               tbl_usuario u,
                               tbl_proyecto p,
                               tbl_horas_cargables hc
                          WHERE pa.id_analista = u.id
                          AND pa.id_proyecto = p.id
                          AND hc.id_proy_analista = pa.id
                          AND u.id = '.$parametros["id_usuario"].'
                          AND hc.fecha BETWEEN "'.$parametros["fecha_desde"].'" AND "'.$parametros["fecha_hasta"].'"');

       $sql2 = DB::select('SELECT CONCAT(
                                    IF(
                                       LENGTH(FLOOR(SUM(TIME_TO_SEC(TIMEDIFF(hnc.fecha_hasta, hnc.fecha_desde)))/3600)) = 1,
                                       CONCAT("0",FLOOR(SUM(TIME_TO_SEC(TIMEDIFF(hnc.fecha_hasta, hnc.fecha_desde)))/3600)),
                                       FLOOR(SUM(TIME_TO_SEC(TIMEDIFF(hnc.fecha_hasta, hnc.fecha_desde)))/3600)
                                      ),
                                    ":",
                                    IF(
                                       LENGTH(FLOOR(SUM(TIME_TO_SEC(TIMEDIFF(hnc.fecha_hasta, hnc.fecha_desde)))/60)%60) = 1,
                                       CONCAT("0",FLOOR(SUM(TIME_TO_SEC(TIMEDIFF(hnc.fecha_hasta, hnc.fecha_desde)))/60)%60),
                                       FLOOR(SUM(TIME_TO_SEC(TIMEDIFF(hnc.fecha_hasta, hnc.fecha_desde)))/60)%60
                                      )
                                  ) horas
                           FROM tbl_horas_no_cargables hnc,
                               tbl_concepto_horas_no_cargables chnc
                           WHERE hnc.id_concepto = chnc.id
                           AND hnc.id_usuario = '.$parametros["id_usuario"].'
                           AND hnc.id_estatus IN(1,2)
                           AND hnc.fecha_desde BETWEEN "'.$parametros["fecha_desde"].' 00:00:00" AND "'.$parametros["fecha_hasta"].' 23:59:00"
                           AND hnc.fecha_hasta BETWEEN "'.$parametros["fecha_desde"].' 00:00:00" AND "'.$parametros["fecha_hasta"].' 23:59:00"');

      $sql1 = ($sql1[0]->horas === null) ? "00:00" : $sql1[0]->horas;
      $sql2 = ($sql2[0]->horas === null) ? "00:00" : $sql2[0]->horas;

      return [
        "horas_cargables" => $sql1,
        "horas_no_cargables" => $sql2,
      ];

    }

    function sin_cargar_horas_cargables(){

      $fecha = date("Y-m-d");

      $sql = DB::select('SELECT id,
                                nombre,
                                correo,
                                IFNULL(fecha, "Nunca ha cargado") AS fecha,
                                division
                         FROM(

                           SELECT u.id,
                                  CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                                  cu.correo_principal AS correo,
                                  (
                                       SELECT DATE_FORMAT(hc3.fecha, "%d/%m/%Y")
                                       FROM tbl_horas_cargables hc3,
                                            tbl_proyecto_analista pa3
                                       WHERE hc3.id_proy_analista = pa3.id
                                       AND pa3.id_analista = u.id
                                       ORDER BY hc3.fecha DESC
                                       LIMIT 1
                                  ) AS fecha,
                                  d.descripcion AS division
                             FROM tbl_usuario u,
                                  tbl_contacto_usuario cu,
                                  tbl_division d
                             WHERE u.id = cu.id_usuario
                             AND u.id_division = d.id
                             AND u.id NOT IN(

                                SELECT u2.id
                                FROM tbl_horas_cargables hc,
                                     tbl_proyecto_analista pa,
                                     tbl_usuario u2
                                WHERE hc.id_proy_analista = pa.id
                                AND pa.id_analista = u2.id
                                AND hc.fecha >= DATE_SUB("'.$fecha.'", INTERVAL 5 DAY)
                                GROUP BY u2.id

                             )
                             AND u.id_estatus = 1
                             ORDER BY u.nombre_1, u.nombre_2, u.apellido_1, u.apellido_2
                            )t');

      return $sql;

    }

    function destinatarios_notificaciones_tarea_programada($id_tarea_programada){

      $sql = DB::select('SELECT cu.correo_principal AS correo
                         FROM tbl_usuario u,
                              tbl_contacto_usuario cu,
                              tbl_notificacion_tarea_programada_usuario ntpu
                         WHERE u.id = cu.id_usuario
                         AND u.id = ntpu.id_usuario
                         AND ntpu.id_tarea_programada = ?', [$id_tarea_programada]);

      $correos = [];

      foreach ($sql as $dato) {
        array_push($correos, $dato->correo);
      }

      return $correos;

    }

    function configuracion_tarea_programada($id_tarea_programada){

      $sql = DB::select('SELECT ctp.valor AS dias
                         FROM tbl_configuracion_tarea_programada ctp
                         WHERE ctp.id = 1
                         AND ctp.id = ? ', [$id_tarea_programada]);

      return $sql[0];

    }

}
