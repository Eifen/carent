<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class totalHorasAsigModel extends Model
{

  function divisiones(){

      $divisiones = DB::select('SELECT d.id,
                                      d.descripcion
                                FROM tbl_division d');

      return $divisiones;

    }// Fin divisiones

   
    function horasAsignadas($paginar, $fecha_desde, $fecha_hasta, $divisiones, $empleado = null, $desde = 0){

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

      if($empleado != null && trim($empleado) != ""){
          $sql_empleado = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($empleado).'%"';
        }else{
          $sql_empleado = "";
        }

      $sql = DB::select('SELECT u.id,
                                CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                                d.descripcion AS division,
                                (SELECT CASE 
                                        WHEN SUM(cast(time_to_sec(hc.horas_trabajadas) / (60 * 60) as decimal(10, 1))) > 0 THEN SUM(cast(time_to_sec(hc.horas_trabajadas) / (60 * 60) as decimal(10, 1)))
                                        ELSE 0
                                        END AS tota_horas_cargadas
                                        FROM tbl_proyecto_analista a,
                                             tbl_horas_cargables hc
                                        WHERE u.id = a.id_analista
                                        AND a.id_estatus = 1
                                        AND hc.id_proy_analista = a.id)tota_horas_cargadas,

                                (SELECT CASE 
                                        WHEN SUM(a.horas_asignadas) > 0 THEN SUM(a.horas_asignadas)
                                        ELSE 0
                                        END AS total_horas_asignadas
                                        FROM tbl_proyecto_analista a
                                        WHERE u.id = a.id_analista
                                        AND a.id_estatus = 1)total_horas_asignadas,

                                (SELECT CASE 
                                        WHEN SUM(cast(time_to_sec(hc.horas_trabajadas) / (60 * 60) as decimal(10, 1))) > 0 THEN SUM(cast(time_to_sec(hc.horas_trabajadas) / (60 * 60) as decimal(10, 1)))
                                        ELSE 0
                                        END AS horas_cargadas_fecha
                                        FROM tbl_proyecto_analista a,
                                             tbl_horas_cargables hc
                                        WHERE u.id = a.id_analista
                                        AND hc.id_proy_analista = a.id
                                        AND hc.fecha BETWEEN "'.$fecha_desde.'" AND "'.$fecha_hasta.'")horas_cargadas_fecha                               
                         FROM tbl_usuario u,                              
                              tbl_division d
                         WHERE u.id_division = d.id
                         AND u.id_estatus = 1
                         '.$sql_division.'
                         '.$sql_empleado.'
                          GROUP BY u.id
                          ORDER BY d.descripcion ASC, 
                                   nombre ASC
                         LIMIT '.$desde.', '.$paginar);
      $horas_asignadas = [];
      for ($i=0; $i < count($sql) ; $i++) {
        $porcentaje = 0; 
        if ($sql[$i]->total_horas_asignadas > 0) {
          $porcentaje = "
          ".round($sql[$i]->tota_horas_cargadas / $sql[$i]->total_horas_asignadas*100, 2)." %"; 

        }
        $horas_asignadas[$i] = array('id' => $sql[$i]->id, 'nombre' => $sql[$i]->nombre, 'division' => $sql[$i]->division, 'tota_horas_cargadas' => $sql[$i]->tota_horas_cargadas, 'total_horas_asignadas' => $sql[$i]->total_horas_asignadas, 'horas_cargadas_fecha' => $sql[$i]->horas_cargadas_fecha,'porcentaje' => $porcentaje);
      }

      return $horas_asignadas;

    }

    function pagCantidadTotalHorasProy($paginar, $proyecto = null, $empleado = null, $empleadoC = null, $cliente = null, $estatus = null){

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

      if($empleado != null && trim($empleado) != ""){
          $sql_empleado = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($empleado).'%"';
        }else{
          $sql_empleado = "";
        }

      $sql = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                         FROM(

                           SELECT u.id,
                                  CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                                  d.descripcion AS division
                           FROM tbl_usuario u,                              
                                tbl_division d
                           WHERE u.id_division = d.id
                           AND u.id_estatus = 1
                           '.$sql_division.'
                           '.$sql_empleado.'
                           GROUP BY u.id
                          ORDER BY d.descripcion ASC, 
                                   nombre ASC
                         )t'
                       );

      return $sql[0]->paginas;

    }

}
