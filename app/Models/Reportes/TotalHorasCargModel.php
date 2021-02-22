<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class TotalHorasCargModel extends Model
{

    function cargos(){

      $cargos = DB::select('SELECT ce.id,
                                         ce.descripcion
                                FROM tbl_cargo_empleado ce');

      return $cargos;

    }// Fin cargos

    function divisiones(){

      $divisiones = DB::select('SELECT d.id,
                                      d.descripcion
                                FROM tbl_division d');

      return $divisiones;

    }// Fin divisiones
   
    function horasCargadas($fecha_desde,$fecha_hasta,$divisiones, $cargos, $empleado = null){

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
        $sql_cargos = "AND ce.id IN(".$idsCargo.")";

      }

      if($empleado != null && trim($empleado) != ""){
          $sql_empleado = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($empleado).'%"';
        }else{
          $sql_empleado = "";
        }

      $horas = DB::select('SELECT hnc.id,
                                  hnc.id_usuario,
                                  DATE_FORMAT(hnc.fecha_desde, "%Y-%m-%d") AS fecha_desde, 
                                  cast(time_to_sec(DATE_FORMAT(hnc.fecha_desde, "%H:%i:%s"))/ (60 * 60) as decimal(10, 1)) AS hora_desde,
                                  DATE_FORMAT(hnc.fecha_hasta, "%Y-%m-%d") AS fecha_hasta, 
                                  cast(time_to_sec(DATE_FORMAT(hnc.fecha_hasta, "%H:%i:%s"))/ (60 * 60) as decimal(10, 1)) AS hora_hasta 
                             FROM tbl_horas_no_cargables hnc
                             WHERE hnc.fecha_desde BETWEEN "'.$fecha_desde.' 00:00:00" AND "'.$fecha_hasta.' 23:59:00"
                             OR hnc.fecha_hasta BETWEEN "'.$fecha_desde.' 00:00:00" AND "'.$fecha_hasta.' 23:59:00"');
      $usuarios = DB::select('SELECT u.id,
                                     CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                                     (SELECT CASE 
                                      WHEN SUM(cast(time_to_sec(h.horas_trabajadas) / (60 * 60) as decimal(10, 1))) > 0 THEN SUM(cast(time_to_sec(h.horas_trabajadas) / (60 * 60) as decimal(10, 1)))
                                      ELSE 0
                                      END AS horas_cargables
                                      FROM tbl_proyecto_analista a,
                                           tbl_horas_cargables h
                                      WHERE u.id = a.id_analista
                                      AND h.id_proy_analista = a.id
                                      AND h.fecha BETWEEN "'.$fecha_desde.'" AND "'.$fecha_hasta.'")horas_cargables
                             FROM tbl_usuario u,
                                  tbl_cargo_empleado ce,
                                  tbl_division d                                  
                             WHERE u.id_estatus != 2
                             AND u.id_cargo = ce.id
                             AND u.id_division = d.id
                             '.$sql_cargos.'
                             '.$sql_division.'
                             '.$sql_empleado.'
                             ORDER BY u.nombre_1 ASC,
                                      u.nombre_2 ASC,
                                      u.apellido_1 ASC,
                                      u.apellido_2 ASC
                             ');
      $total_horas_no_cargables = 0;
      $horas_cargadas = 0; 
      for ($u=0; $u < count($usuarios) ; $u++) {
        for ($i=0; $i < count($horas) ; $i++) {
          if ($usuarios[$u]->id === $horas[$i]->id_usuario) {
            while ($fecha_desde > $horas[$i]->fecha_desde) {
              $horas[$i]->fecha_desde = date("Y-m-d", strtotime($horas[$i]->fecha_desde."+ 1 days"));
              $horas[$i]->hora_desde = 8.0;
            }
            while ($fecha_hasta < $horas[$i]->fecha_hasta) {
              $horas[$i]->fecha_hasta = date("Y-m-d", strtotime($horas[$i]->fecha_hasta."- 1 days"));
              $horas[$i]->hora_hasta = 17.0;
            }
            if ($horas[$i]->fecha_desde === $horas[$i]->fecha_hasta) {
              if ($horas[$i]->hora_desde < 12.0 && $horas[$i]->hora_hasta > 13.0) {
                $hora_cargada1 = 12.0 - $horas[$i]->hora_desde;
                $hora_cargada2 = $horas[$i]->hora_hasta - 13.0;
                $horas_cargadas = $hora_cargada1 + $hora_cargada2;
              }else{
                $horas_cargadas = $horas[$i]->hora_hasta - $horas[$i]->hora_desde;
              }
            }else{
              $dia = date('w',strtotime($horas[$i]->fecha_desde));
              if ($dia === "1" || $dia === "2" || $dia === "3" || $dia === "4" || $dia === "5") {        
                if ($horas[$i]->hora_desde < 12.0) {
                  $hora_cargada1 = 12.0 - $horas[$i]->hora_desde;
                  $horas_cargadas = $hora_cargada1 + 4.0;
                  $hora1 = $horas_cargadas;
                }else{
                  $horas_cargadas = 17 - $horas[$i]->hora_desde;
                  $hora2 = $horas_cargadas;
                }
              }
              $nueva_desde = date("Y-m-d", strtotime($horas[$i]->fecha_desde."+ 1 days"));
              $dia = date("w", strtotime($nueva_desde));        
              while ($nueva_desde != $horas[$i]->fecha_hasta) {
                if ($dia === "1" || $dia === "2" || $dia === "3" || $dia === "4" || $dia === "5") {
                  $horas_cargadas = $horas_cargadas + 8.0;
                }
                $nueva_desde = date("Y-m-d",strtotime($nueva_desde."+ 1 days"));
                $dia = date("w", strtotime($nueva_desde));
              }              
              if ($dia === "1" || $dia === "2" || $dia === "3" || $dia === "4" || $dia === "5") {
                if ($horas[$i]->hora_hasta < 13.0) {
                  $horas_cargadas = $horas_cargadas + $horas[$i]->hora_hasta - 8;
                }else{
                  $hora_cargada2 = $horas[$i]->hora_hasta - 13.0;
                  $horas_cargadas = $horas_cargadas + 4.0 + $hora_cargada2;
                }
              }     
            }
          }
         $total_horas_no_cargables = $total_horas_no_cargables + $horas_cargadas;
         $total_horas = $total_horas_no_cargables + $usuarios[$u]->horas_cargables;
         $horas_cargadas = 0;
         $hora_cargada1 = 0;
         $hora_cargada2 = 0;
        }
        $total[$u] = array('id' => $usuarios[$u]->id, 'nombre' => $usuarios[$u]->nombre, 'total_horas_cargables' => $usuarios[$u]->horas_cargables, 'total_horas_no_cargables' => $total_horas_no_cargables, 'total_horas' => $total_horas);
        $total_horas_no_cargables = 0;
      }
      
      return $total;

    }// Fin

}
