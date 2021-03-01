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
                             WHERE hnc.id_estatus != 2
                             AND (hnc.fecha_desde BETWEEN "'.$fecha_desde.' 00:00:00" AND "'.$fecha_hasta.' 23:59:00"
                             OR hnc.fecha_hasta BETWEEN "'.$fecha_desde.' 00:00:00" AND "'.$fecha_hasta.' 23:59:00")
                             ORDER BY hnc.id_usuario ASC,
                                      hnc.fecha_desde');
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
      $horas_cargables = DB::select('SELECT h.id,
                                            a.id_analista,
                                            h.fecha,
                                            (cast(time_to_sec(h.horas_trabajadas) / (60 * 60) as decimal(10, 1))) AS horas_cargadas
                             FROM tbl_horas_cargables h,
                                  tbl_proyecto_analista a                                 
                             WHERE h.fecha BETWEEN "'.$fecha_desde.'" AND "'.$fecha_hasta.'"
                             AND h.id_proy_analista = a.id
                             
                             ');
      $total_horas_no_cargables = 0;
      $horas_cargadas = 0;
      $total_horas = 0; 
      $horas_cargadas_anterior = 0;
      $fecha_desde_anterior = null;
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
              }elseif ($horas[$i]->hora_desde == 12 && $horas[$i]->hora_hasta > 14) {
                $horas_cargadas = $horas[$i]->hora_hasta - $horas[$i]->hora_desde - 1.0;
              }elseif ($horas[$i]->hora_desde == 12.5 && $horas[$i]->hora_hasta > 14){
                $horas_cargadas = $horas[$i]->hora_hasta - $horas[$i]->hora_desde - 0.5;
              }else{
                $horas_cargadas = $horas[$i]->hora_hasta - $horas[$i]->hora_desde;
              }
              for ($c=0; $c < count($horas_cargables) ; $c++) { 
                if ($horas_cargables[$c]->id_analista === $horas[$i]->id_usuario) {
                  if ($fecha_desde_anterior != $horas_cargables[$c]->fecha) {
                    $horas_cargadas_anterior = 0;
                  }
                  if ($horas_cargables[$c]->fecha === $horas[$i]->fecha_desde && $horas_cargadas_anterior === 0) {
                    if ($horas_cargadas + $horas_cargables[$c]->horas_cargadas >= 8.0) {
                      $horas_cargadas = 0;
                      while (8.0 > $horas_cargables[$c]->horas_cargadas) {
                        $horas_cargadas = $horas_cargadas + 0.5;
                        $horas_cargables[$c]->horas_cargadas = $horas_cargables[$c]->horas_cargadas + 0.5;
                      }
                      $horas_cargadas_anterior = $horas_cargadas + $horas_cargables[$c]->horas_cargadas;
                    }else{
                      $horas_cargadas2 = $horas_cargadas;
                      $horas_cargadas_anterior = $horas_cargadas + $horas_cargables[$c]->horas_cargadas;
                    }
                  }elseif ($horas_cargables[$c]->fecha === $fecha_desde_anterior && $horas_cargadas_anterior > 0) {
                    if ($horas_cargadas_anterior < 8) {
                      $limite = $horas_cargadas2 + $horas_cargadas; 
                      if ($limite >= $horas_cargadas_anterior) {
                        $horas_cargadas = 0;
                        while ($limite > $horas_cargadas_anterior) {
                          $horas_cargadas = $horas_cargadas + 0.5;
                          $horas_cargadas_anterior = $horas_cargadas_anterior + 0.5;
                        }
                      }else{
                          $horas_cargadas = $limite - $horas_cargadas_anterior;
                      }                      
                    }else{
                      $horas_cargadas = 0;
                    }
                  }
                  $fecha_desde_anterior = $horas[$i]->fecha_desde;
                }
              }
            }else{
              $dia = date('w',strtotime($horas[$i]->fecha_desde));
              if ($dia === "1" || $dia === "2" || $dia === "3" || $dia === "4" || $dia === "5") {        
                if ($horas[$i]->hora_desde < 12.0) {
                  $hora_cargada1 = 12.0 - $horas[$i]->hora_desde;
                  $horas_cargadas = $hora_cargada1 + 4.0;
                }elseif ($horas[$i]->hora_desde == 12) {
                  $horas_cargadas = 17 - $horas[$i]->hora_desde - 1.0;
                }elseif ($horas[$i]->hora_desde == 12.5) {
                  $horas_cargadas = 17 - $horas[$i]->hora_desde - 0.5;
                }else{
                  $horas_cargadas = 17 - $horas[$i]->hora_desde;
                }
                for ($c=0; $c < count($horas_cargables) ; $c++) { 
                  if ($horas_cargables[$c]->id_analista === $horas[$i]->id_usuario) {
                    if ($horas_cargables[$c]->fecha === $horas[$i]->fecha_desde) {
                      if ($horas_cargadas + $horas_cargables[$c]->horas_cargadas >= 8.0) {
                        $horas_cargadas = 0;
                        while (8.0 > $horas_cargables[$c]->horas_cargadas) {
                          $horas_cargadas = $horas_cargadas + 0.5;
                          $horas_cargables[$c]->horas_cargadas = $horas_cargables[$c]->horas_cargadas + 0.5;
                        }
                      }
                    }
                  }
                }
              }
              $nueva_desde = date("Y-m-d", strtotime($horas[$i]->fecha_desde."+ 1 days"));
              $dia = date("w", strtotime($nueva_desde));     
              while ($nueva_desde != $horas[$i]->fecha_hasta) {
                if ($dia === "1" || $dia === "2" || $dia === "3" || $dia === "4" || $dia === "5") {
                  $horas_cargadas = $horas_cargadas + 8.0;
                }
                for ($c=0; $c < count($horas_cargables) ; $c++) { 
                  if ($horas_cargables[$c]->id_analista === $horas[$i]->id_usuario) {
                    if ($horas_cargables[$c]->fecha === $nueva_desde) { 
                      $horas_cargadas = $horas_cargadas - 8.0;
                      while (8.0 > $horas_cargables[$c]->horas_cargadas) {
                        $horas_cargadas = $horas_cargadas + 0.5;
                        $horas_cargables[$c]->horas_cargadas = $horas_cargables[$c]->horas_cargadas + 0.5;
                      }                      
                    }
                  }
                }
                $nueva_desde = date("Y-m-d",strtotime($nueva_desde."+ 1 days"));
                $dia = date("w", strtotime($nueva_desde));
              }
              if ($dia === "1" || $dia === "2" || $dia === "3" || $dia === "4" || $dia === "5") {
                if ($horas[$i]->hora_hasta < 13.0) {
                  $horas_cargadas = $horas_cargadas + $horas[$i]->hora_hasta - 8;
                  $acumulado = 0;
                  for ($c=0; $c < count($horas_cargables) ; $c++) { 
                    if ($horas_cargables[$c]->id_analista === $horas[$i]->id_usuario) {
                      if ($horas_cargables[$c]->fecha === $nueva_desde) {
                        if ($horas[$i]->hora_hasta - 8 + $horas_cargables[$c]->horas_cargadas >= 8) {
                          $horas_cargadas = $horas_cargadas - $horas[$i]->hora_hasta + 8;
                          $horas_carga = $horas_cargables[$c]->horas_cargadas;
                          while (8.0 > $horas_cargables[$c]->horas_cargadas) {
                            $horas_cargadas = $horas_cargadas + 0.5;
                            $acumulado = $acumulado + 0.5;
                            $horas_cargables[$c]->horas_cargadas = $horas_cargables[$c]->horas_cargadas + 0.5;
                          } 
                          while ($acumulado + $horas_carga > 8.0) {
                             $horas_cargadas = $horas_cargadas - 0.5;
                             $horas_carga = $horas_carga - 0.5;
                           } 
                        }                         
                      }                   
                    }
                  }
                }else{
                  $hora_cargada2 = $horas[$i]->hora_hasta - 13.0;
                  $horas_cargadas = $horas_cargadas + 4.0 + $hora_cargada2;
                  for ($c=0; $c < count($horas_cargables) ; $c++) { 
                    if ($horas_cargables[$c]->id_analista === $horas[$i]->id_usuario) {
                      if ($horas_cargables[$c]->fecha === $nueva_desde) {
                        if ($horas[$i]->hora_hasta - 13.0 + 4.0 + $horas_cargables[$c]->horas_cargadas >= 8) {
                          $horas_cargadas = $horas_cargadas - 4.0 - $hora_cargada2;
                          $horas_carga = $horas_cargables[$c]->horas_cargadas;
                          $acumulado = 0;
                          while (8.0 > $horas_cargables[$c]->horas_cargadas) {
                            $horas_cargadas = $horas_cargadas + 0.5;
                            $acumulado = $acumulado + 0.5;
                            $horas_cargables[$c]->horas_cargadas = $horas_cargables[$c]->horas_cargadas + 0.5;
                          } 
                          while ($acumulado + $horas_carga > 8.0) {
                             $horas_cargadas = $horas_cargadas - 0.5;
                             $horas_carga = $horas_carga - 0.5;
                           } 
                        }                         
                      }                   
                    }
                  }
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
        $total_horas = 0;
      }
      
      return $total;

    }// Fin

}
