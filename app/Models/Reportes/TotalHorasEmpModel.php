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
                                p.descripcion AS proyecto,
                                CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS empleado,
                                SEC_TO_TIME(SUM(TIME_TO_SEC(hc.horas_trabajadas))) AS horas_trabajadas
                         FROM tbl_proyecto_analista pa,
                              tbl_usuario u,
                              tbl_proyecto p,
                              tbl_horas_cargables hc
                         WHERE pa.id_analista = u.id
                         AND pa.id_proyecto = p.id
                         AND hc.id_proy_analista = pa.id
                         AND u.id = '.$parametros["id_usuario"].'
                         AND hc.fecha BETWEEN "'.$parametros["fecha_desde"].'" AND "'.$parametros["fecha_hasta"].'"
                         GROUP BY p.id, p.descripcion,
                                  u.nombre_1,
                                  u.nombre_2,
                                  u.apellido_1,
                                  u.apellido_2');

      return $sql;

    }

}
