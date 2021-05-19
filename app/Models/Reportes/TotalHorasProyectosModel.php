<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class TotalHorasProyectosModel extends Model
{

  function empleados(){

      $sql = DB::select('SELECT * FROM(
                           SELECT u.id,
                                  CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre
                           FROM tbl_usuario u
                           WHERE u.id_cargo IN(16,17)
                         )t
                         ORDER BY nombre ASC');

      return $sql;

    }// Fin empleados

    function estatusProyectos(){

      $estatus = DB::select('SELECT e.valor AS id,
                                    e.descripcion
                             FROM tbl_estatus e
                             WHERE tabla = "tbl_proyecto"
                             ORDER BY e.descripcion ASC');

      return $estatus;

    }// Fin estatusProyectos
   
    function repoTotalHorasProy($id_usuario, $paginar, $fecha_hasta, $fecha_desde = null, $desde = 0, $proyecto = null, $empleado = null, $empleadoC = null, $cliente = null, $estatus = null){

      if($id_usuario == 1 || $id_usuario == 140 || $id_usuario == 144 || $id_usuario == 146 || $id_usuario == 154){
        $sql_asignado = "";
      }else{
        $sql_asignado = ' AND (p.id_socio = '.$id_usuario.' OR p.id_socio_calidad = '.$id_usuario.' OR p.id_gerente = '.$id_usuario.' OR p.id = (SELECT a.id_proyecto FROM tbl_proyecto_analista a WHERE p.id = a.id_proyecto AND a.id_analista = '.$id_usuario.') OR p.id = (SELECT pd.id_proyecto FROM tbl_proyecto_divisiones pd WHERE p.id = pd.id_proyecto AND pd.id_gerente = '.$id_usuario.'))';
      }

      if($fecha_desde !== null){
        $sql_fecha = 'AND p.fecha_contratacion BETWEEN "'.$fecha_desde.'" AND "'.$fecha_hasta.'"';
      }else{
        $sql_fecha = "";
      }


      if($proyecto != null && trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
      }

      if($cliente != null && trim($cliente) != ""){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if($empleado !== null){
        $sql_empleado = 'AND p.id_socio = '.$empleado;
      }else{
        $sql_empleado = "";
      }

      if($empleadoC !== null){
        $sql_empleadoC = 'AND p.id_socio_calidad = '.$empleadoC;
      }else{
        $sql_empleadoC = "";
      }

      if($estatus !== null){
        $sql_estatus = 'AND c.id_estatus = '.$estatus;
      }else{
        $sql_estatus = "";
      }

      $sql = DB::select('SELECT p.id AS id_proyecto,
                                p.descripcion AS proyecto,
                                c.razon_social AS cliente,
                                CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS empleado,
                                (SELECT SUM(pd.horas_contratadas) FROM tbl_proyecto_divisiones pd WHERE p.id = pd.id_proyecto) horas_contratadas,
                                (SELECT CASE 
                                  WHEN SUM(cast(time_to_sec(hc.horas_trabajadas) / (60 * 60) as decimal(10, 1))) > 0 THEN SUM(cast(time_to_sec(hc.horas_trabajadas) / (60 * 60) as decimal(10, 1)))
                                  ELSE 0
                                  END AS horas_cargables
                                  FROM tbl_horas_cargables hc,
                                       tbl_proyecto_analista pd 
                                  WHERE p.id = pd.id_proyecto
                                  AND pd.id = hc.id_proy_analista) horas_cargadas,
                                (SELECT CASE 
                                  WHEN SUM(pda.horas) > 0 THEN SUM(pda.horas)
                                  ELSE 0
                                  END AS horas_adicionales
                                  FROM tbl_proyecto_divisiones pd,
                                       tbl_proy_div_horas_adic pda
                                  WHERE p.id = pd.id_proyecto
                                  AND pd.id = pda.id_proy_div
                                  AND pda.id_estatus = 1) horas_adicionales,
                                  ((SELECT CASE 
                                  WHEN SUM(pda.horas) > 0 THEN SUM(pda.horas)
                                  ELSE 0
                                  END AS horas_adicionales
                                  FROM tbl_proyecto_divisiones pd,
                                       tbl_proy_div_horas_adic pda
                                  WHERE p.id = pd.id_proyecto
                                  AND pd.id = pda.id_proy_div
                                  AND pda.id_estatus = 1) + (SELECT SUM(pd.horas_contratadas) FROM tbl_proyecto_divisiones pd WHERE p.id = pd.id_proyecto)) AS total_horas,
                                  CONCAT(round((SELECT CASE WHEN SUM(cast(time_to_sec(hc.horas_trabajadas) / (60 * 60) as decimal(10, 1))) > 0 THEN SUM(cast(time_to_sec(hc.horas_trabajadas) / (60 * 60) as decimal(10, 1)))ELSE 0 END AS horas_cargables FROM tbl_horas_cargables hc,  tbl_proyecto_analista pd WHERE p.id = pd.id_proyecto AND pd.id = hc.id_proy_analista) / ((SELECT CASE 
                                  WHEN SUM(pda.horas) > 0 THEN SUM(pda.horas)
                                  ELSE 0
                                  END AS horas_adicionales
                                  FROM tbl_proyecto_divisiones pd,
                                       tbl_proy_div_horas_adic pda
                                  WHERE p.id = pd.id_proyecto
                                  AND pd.id = pda.id_proy_div
                                  AND pda.id_estatus = 1) + (SELECT SUM(pd.horas_contratadas) FROM tbl_proyecto_divisiones pd WHERE p.id = pd.id_proyecto))*100, 2 
                                  ),"%") AS porc_total_horas                                
                         FROM tbl_proyecto p,                         
                              tbl_usuario u,                              
                              tbl_cliente c,
                              tbl_estatus e
                         WHERE p.id_cliente = c.id
                         AND p.id_socio = u.id
                         AND p.id_estatus = e.valor
                         AND e.tabla = "tbl_proyecto"
                         '.$sql_proyecto.'
                         '.$sql_cliente.'
                         '.$sql_empleado.'
                         '.$sql_empleadoC.'
                         '.$sql_estatus.'
                         '.$sql_asignado.'
                         '.$sql_fecha.'
                         GROUP BY p.id,
                                  p.descripcion,
                                  u.nombre_1,
                                  u.nombre_2,
                                  u.apellido_1,
                                  u.apellido_2,
                                  c.id,
                                  c.razon_social
                          ORDER BY p.fecha_contratacion DESC
                         LIMIT '.$desde.', '.$paginar);

      return $sql;

    }

    function pagCantidadTotalHorasProy($id_usuario, $paginar, $fecha_hasta, $fecha_desde = null, $proyecto = null, $empleado = null, $empleadoC = null, $cliente = null, $estatus = null){

      if($id_usuario == 1 || $id_usuario == 140 || $id_usuario == 144 || $id_usuario == 146 || $id_usuario == 154){
        $sql_asignado = "";
      }else{
        $sql_asignado = ' AND (p.id_socio = '.$id_usuario.' OR p.id_socio_calidad = '.$id_usuario.' OR p.id_gerente = '.$id_usuario.' OR p.id = (SELECT a.id_proyecto FROM tbl_proyecto_analista a WHERE p.id = a.id_proyecto AND a.id_analista = '.$id_usuario.') OR p.id = (SELECT pd.id_proyecto FROM tbl_proyecto_divisiones pd WHERE p.id = pd.id_proyecto AND pd.id_gerente = '.$id_usuario.'))';
      }

      if($fecha_desde !== null){
        $sql_fecha = 'AND p.fecha_contratacion BETWEEN "'.$fecha_desde.'" AND "'.$fecha_hasta.'"';
      }else{
        $sql_fecha = "";
      }

      if($proyecto != null && trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
      }

      if($cliente != null && trim($cliente) != ""){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if($empleado !== null){
        $sql_empleado = 'AND u.id = '.$empleado;
      }else{
        $sql_empleado = "";
      }

      if($empleadoC !== null){
        $sql_empleadoC = 'AND p.id_socio_calidad = '.$empleadoC;
      }else{
        $sql_empleadoC = "";
      }

      if($estatus !== null){
        $sql_estatus = 'AND c.id_estatus = '.$estatus;
      }else{
        $sql_estatus = "";
      }

      $sql = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                         FROM(

                           SELECT p.descripcion AS proyecto
                           FROM tbl_proyecto p,                         
                                tbl_usuario u,                              
                                tbl_cliente c,
                                tbl_estatus e
                           WHERE p.id_cliente = c.id
                           AND p.id_socio = u.id
                           AND p.id_estatus = e.valor
                           AND e.tabla = "tbl_proyecto"
                           '.$sql_proyecto.'
                           '.$sql_cliente.'
                           '.$sql_empleado.'
                           '.$sql_empleadoC.'
                           '.$sql_estatus.'
                           '.$sql_asignado.'
                           '.$sql_fecha.'
                           GROUP BY p.id,
                                  p.descripcion,
                                  u.nombre_1,
                                  u.nombre_2,
                                  u.apellido_1,
                                  u.apellido_2,
                                  c.id,
                                  c.razon_social
                           ORDER BY p.fecha_contratacion DESC
                         )t'
                       );

      return $sql[0]->paginas;

    }

}
