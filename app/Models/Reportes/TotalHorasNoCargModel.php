<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class TotalHorasNoCargModel extends Model
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

    function concepto(){

      $concepto = DB::select('SELECT chnc.id,
                                     chnc.descripcion
                                FROM  tbl_concepto_horas_no_cargables chnc');

      return $concepto;

    }// Fin concepto
   
    function horasNoCargables($fecha_desde,$fecha_hasta, $concepto, $divisiones, $cargos, $empleado = null){

      if($concepto == null){
        $sql_concepto = "";
      }else{
        $idsConcepto = [];
        if(is_array($concepto)){
          foreach ($concepto as $key => $item) {
            if(!isset($item->id)){
              $item = json_decode($item);
            }
            array_push($idsConcepto,$item->id);
          }
        }else{
          array_push($idsConcepto,$concepto);
        }
        $idsConcepto = implode(",", $idsConcepto);
        $sql_concepto = "AND chnc.id IN(".$idsConcepto.")";
      }

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
                                  CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                                  u.codigo,
                                  hnc.id_concepto,
                                  chnc.descripcion AS concepto,
                                  DATE_FORMAT(hnc.fecha_desde, "%Y-%m-%d") AS fecha_desde, 
                                  cast(time_to_sec(DATE_FORMAT(hnc.fecha_desde, "%H:%i:%s"))/ (60 * 60) as decimal(10, 1)) AS hora_desde,
                                  DATE_FORMAT(hnc.fecha_hasta, "%Y-%m-%d") AS fecha_hasta, 
                                  cast(time_to_sec(DATE_FORMAT(hnc.fecha_hasta, "%H:%i:%s"))/ (60 * 60) as decimal(10, 1)) AS hora_hasta
                             FROM tbl_horas_no_cargables hnc,
                                  tbl_usuario u,
                                  tbl_division d,
                                  tbl_cargo_empleado ce,
                                  tbl_concepto_horas_no_cargables chnc 
                             WHERE hnc.id_usuario = u.id
                             AND u.id_division = d.id
                             AND u.id_cargo = ce.id
                             AND hnc.id_concepto = chnc.id
                             '.$sql_empleado.'
                             '.$sql_division.'
                             '.$sql_cargos.'
                             '.$sql_concepto.'
                             AND hnc.id_estatus IN(1,2)
                             AND (hnc.fecha_desde BETWEEN "'.$fecha_desde.' 00:00:00" AND "'.$fecha_hasta.' 23:59:00"
                             OR hnc.fecha_hasta BETWEEN "'.$fecha_desde.' 00:00:00" AND "'.$fecha_hasta.' 23:59:00")
                             ORDER BY hnc.id_usuario ASC,
                                      hnc.id_concepto ASC,
                                      hnc.fecha_desde');

      $horas_no_cargables = 0;
      $horas_cargadas = 0;
      $maximo_horas = 0;
      $id_usuario = 0;
      $id_concepto = 0;
      $horas_no = 0;
      $total = 0;
        for ($i=0; $i < count($horas) ; $i++) {         

            while ($fecha_desde > $horas[$i]->fecha_desde) {
              $horas[$i]->fecha_desde = date("Y-m-d", strtotime($horas[$i]->fecha_desde."+ 1 days"));
              $horas[$i]->hora_desde = 8.0;
            }
            while ($fecha_hasta < $horas[$i]->fecha_hasta) {
              $horas[$i]->fecha_hasta = date("Y-m-d", strtotime($horas[$i]->fecha_hasta."- 1 days"));
              $horas[$i]->hora_hasta = 17.0;
            }
            $fecha_especial = date("Y-m-d", strtotime($horas[$i]->fecha_desde."+ 1 days"));
            if ($horas[$i]->hora_hasta < 6.0 && $fecha_especial === $horas[$i]->fecha_hasta) {
              while ($horas[$i]->hora_desde != $horas[$i]->hora_hasta) {
                $horas[$i]->hora_desde = $horas[$i]->hora_desde + 0.5;
                $horas_cargadas = $horas_cargadas + 0.5;
                if ($horas[$i]->hora_desde == 24.0) {
                  $horas[$i]->hora_desde = 0;
                }
              }              
            }elseif ($horas[$i]->fecha_desde === $horas[$i]->fecha_hasta) {
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
          
         $horas_no_cargables = $horas_cargadas;

          if ($id_usuario === 0) {
            $id_usuario = $horas[$i]->id_usuario;
            $nombre = $horas[$i]->nombre; 
            $codigo = $horas[$i]->codigo; 
            $id_concepto = $horas[$i]->id_concepto; 
            $concepto = $horas[$i]->concepto; 
            $horas_no = $horas_no_cargables;
            $total = $horas_no_cargables;
            $resultado[] = array('id' => $horas[$i]->id_usuario, 'nombre' => $horas[$i]->nombre, 'codigo' => $horas[$i]->codigo, 'concepto' => " ", 'horas_no_cargables' => " ");

          }elseif ($id_usuario === $horas[$i]->id_usuario) {

            if ($id_concepto === $horas[$i]->id_concepto) {
              $horas_no = $horas_no +  $horas_no_cargables;
              $total = $total +  $horas_no_cargables;
            }elseif ($id_concepto != $horas[$i]->id_concepto) {

              array_push($resultado, array('id' => $id_usuario, 'nombre' => "", 'codigo' => "", 'concepto' => $concepto, 'horas_no_cargables' => $horas_no));

              $concepto = $horas[$i]->concepto; 
              $id_concepto = $horas[$i]->id_concepto; 
              $horas_no = $horas_no_cargables;
              $total = $total +  $horas_no_cargables;
            }            
          }else{
            array_push($resultado, array('id' => $id_usuario, 'nombre' => "", 'codigo' => "", 'concepto' => $concepto, 'horas_no_cargables' => $horas_no, 'total' => $total));

            array_push($resultado, array('id' => $horas[$i]->id_usuario, 'nombre' => $horas[$i]->nombre, 'codigo' => $horas[$i]->codigo, 'concepto' => " ", 'horas_no_cargables' => " "));
            $id_usuario = $horas[$i]->id_usuario;
            $nombre = $horas[$i]->nombre; 
            $codigo = $horas[$i]->codigo; 
            $id_concepto = $horas[$i]->id_concepto; 
            $concepto = $horas[$i]->concepto; 
            $horas_no = $horas_no_cargables;
            $total = $horas_no_cargables;
          }
         $horas_cargadas = 0;
         $hora_cargada1 = 0;
         $hora_cargada2 = 0;
        }        
        if ($id_usuario != 0) {
          array_push($resultado, array('id' => $id_usuario, 'nombre' => "", 'codigo' => "", 'concepto' => $concepto, 'horas_no_cargables' => $horas_no, $horas_no, 'total' => $total));
          return $resultado;
        }        

      return $horas;

    }// Fin

}
