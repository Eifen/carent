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
                             WHERE hnc.id_estatus = 2
                             AND (hnc.fecha_desde BETWEEN "'.$fecha_desde.' 00:00:00" AND "'.$fecha_hasta.' 23:59:00"
                             OR hnc.fecha_hasta BETWEEN "'.$fecha_desde.' 00:00:00" AND "'.$fecha_hasta.' 23:59:00")
                             ORDER BY hnc.id_usuario ASC,
                                      hnc.fecha_desde');
      $usuarios = DB::select('SELECT u.id,
                                     CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                                     u.codigo,
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
                             ORDER BY nombre ASC
                             ');
      $horas_cargables = DB::select('SELECT h.id,
                                            a.id_analista,
                                            h.fecha,
                                            (cast(time_to_sec(h.horas_trabajadas) / (60 * 60) as decimal(10, 1))) AS horas_cargadas
                             FROM tbl_horas_cargables h,
                                  tbl_proyecto_analista a                                 
                             WHERE h.fecha BETWEEN "'.$fecha_desde.'" AND "'.$fecha_hasta.'"
                             AND h.id_proy_analista = a.id
                             ORDER BY a.id_analista ASC,
                                      h.fecha ASC,
                                      horas_cargadas ASC
                             ');

      $horas_arregladas = [];
      $dia = 0;
      $nueva_desde = 0;
      $horas_cargadas = 0;
      $hora_cargada1 = 0;
      $hora_cargada2 = 0;
      $inicio = 1;
      $medio = 0;
      $k = 0;
      $l = 0;
      $h = 0;
      $f = 0;
      $g = 0;
      $comienzo = 1;
      $hora_guardada = 0;
      $fecha_guardada = 0;
      $ultimo_valor = 0;
      $v_cargables = 0;
      $v_no_cargables = 0;
      $s = 0;
      $exceso_cargable = 0;
      $exceso_no_cargable = 0;
      $id_usuario_guardado = 0;
      $exceso_cargable_guardado = 0;
      $exceso_no_cargable_guardado = 0;
      $t_horas_cargables = 0;
      $t_exceso_cargables = 0;
      $t_horas_no_cargables = 0;
      $t_exceso_no_cargables = 0;
      $fecha_desde_mod = $fecha_desde;
      $fecha_hasta_mod = $fecha_hasta;
      $maximo_horas = 0;
      $t_horas_cargadas = 0;
      $exceso = 0;
      $porcen_horas_cargables = "0 %";
      $porcen_carga_cliente = "0 %";
      $porcen_horas_no_cargables = "0 %";
      $porcen_carga_total = "0 %";
      $porcen_carga_no_cliente = "0 %";
      $exceso_cargable = 0;
      $cuenta_cargable = 0;
      $cuenta_no_cargable = 0;
      $total_horas_cargadas= 0;
      $copia = 0;
      $n_exceso_cargables = 0;
      $n_exceso_no_cargables = 0;

      while ( $fecha_hasta_mod >= $fecha_desde_mod) {
        $diaM = date('w',strtotime($fecha_desde_mod));
        if ($diaM === "1" || $diaM === "2" || $diaM === "3" || $diaM === "4" || $diaM === "5") {
          $maximo_horas = $maximo_horas + 8.0;
        }
        $fecha_desde_mod = date("Y-m-d", strtotime($fecha_desde_mod."+ 1 days"));
      }

      for ($i=0; $i < count($horas); $i++) { 
        //Arreglo para cuando la fecha desde del filtro es mayor a la conseguida
        while ($fecha_desde > $horas[$i]->fecha_desde) {
          $horas[$i]->fecha_desde = date("Y-m-d", strtotime($horas[$i]->fecha_desde."+ 1 days"));
          $horas[$i]->hora_desde = 8.0;
        }
        //Arreglo para cuando la fecha hasta del filtro es menor a la conseguida
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
          $horas_arregladas[$k] = array('id_usuario' => $horas[$i]->id_usuario, 'fecha' => $horas[$i]->fecha_desde, 'horas_cargadas' => $horas_cargadas);
          $k++; 
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
            $horas_arregladas[$k] = array('id_usuario' => $horas[$i]->id_usuario, 'fecha' => $horas[$i]->fecha_desde, 'horas_cargadas' => $horas_cargadas);
            $k++;
          }
          $nueva_desde = date("Y-m-d", strtotime($horas[$i]->fecha_desde."+ 1 days"));
          $dia = date("w", strtotime($nueva_desde));     
          while ($nueva_desde != $horas[$i]->fecha_hasta) {
            if ($dia === "1" || $dia === "2" || $dia === "3" || $dia === "4" || $dia === "5") {
              $horas_arregladas[$k] = array('id_usuario' => $horas[$i]->id_usuario, 'fecha' => $nueva_desde, 'horas_cargadas' => 8.0);
              $k++;
            }
            $nueva_desde = date("Y-m-d",strtotime($nueva_desde."+ 1 days"));
            $dia = date("w", strtotime($nueva_desde));
          }
          if ($dia === "1" || $dia === "2" || $dia === "3" || $dia === "4" || $dia === "5") {
            if ($horas[$i]->hora_hasta > 13.0) {
              $hora_cargada1 = 4.0;
              $hora_cargada2 = $horas[$i]->hora_hasta - 13.0;
              $horas_cargadas = $hora_cargada1 + $hora_cargada2;
            }else{
              $horas_cargadas = $horas[$i]->hora_hasta - 8.0;
            }
            $horas_arregladas[$k] = array('id_usuario' => $horas[$i]->id_usuario, 'fecha' => $nueva_desde, 'horas_cargadas' => $horas_cargadas);
            $k++; 
          }
        }
        $hora_cargada1 = 0;
        $hora_cargada2 = 0;
        $horas_cargadas = 0;
        $nueva_desde = 0;
        $dia = 0;
      }

      for ($i=0; $i < count($usuarios) ; $i++) { 
        for ($j=0; $j < count($horas_arregladas) ; $j++) { 
          if ($usuarios[$i]->id === $horas_arregladas[$j]["id_usuario"]) {
            if ($comienzo === 1) {
              $hora_guardada = $horas_arregladas[$j]["horas_cargadas"];
              $fecha_guardada = $horas_arregladas[$j]["fecha"];
              $comienzo = 0;
            }elseif ($fecha_guardada === $horas_arregladas[$j]["fecha"]) {
              $hora_guardada = $hora_guardada + $horas_arregladas[$j]["horas_cargadas"];
            }else{
              if ($hora_guardada > 8.0) {
                $exceso_no_cargable = $hora_guardada - 8.0;
                $hora_guardada = 8.0;
              }
              $horas_no_cargables[$l] = array('id_usuario' => $usuarios[$i]->id, 'fecha' => $fecha_guardada, 'horas_cargadas' => $hora_guardada, 'exceso_no_cargable' => $exceso_no_cargable);
              $$exceso_no_cargable = 0;
              $hora_guardada = $horas_arregladas[$j]["horas_cargadas"];
              $fecha_guardada = $horas_arregladas[$j]["fecha"];
              $l++;
            }
          }
        }
        if ($hora_guardada > 8.0) {
          $exceso_no_cargable = $hora_guardada - 8.0;
          $hora_guardada = 8.0;
        }
        $horas_no_cargables[$l] = array('id_usuario' => $usuarios[$i]->id, 'fecha' => $fecha_guardada, 'horas_cargadas' => $hora_guardada, 'exceso_no_cargable' => $exceso_no_cargable);
        $l++;
        $hora_guardada = 0;
        $fecha_guardada = 0;
        $exceso_no_cargable = 0;
        $exceso_cargable = 0;
        $comienzo = 1;

        for ($j=0; $j < count($horas_cargables) ; $j++) { 
          if ($usuarios[$i]->id === $horas_cargables[$j]->id_analista) {
            if ($comienzo === 1) {
              $hora_guardada = $horas_cargables[$j]->horas_cargadas;
              $fecha_guardada = $horas_cargables[$j]->fecha;
              $comienzo = 0;
            }elseif ($fecha_guardada === $horas_cargables[$j]->fecha) {
              $hora_guardada = $hora_guardada + $horas_cargables[$j]->horas_cargadas;
            }else{
              if ($hora_guardada > 8.0) {
                $exceso_cargable = $hora_guardada - 8.0;
                $hora_guardada = 8.0;
              }
              $total_horas_cargables[$h] = array('id_usuario' => $usuarios[$i]->id, 'fecha' => $fecha_guardada, 'horas_cargadas' => $hora_guardada, 'exceso_cargable' => $exceso_cargable);
              $exceso_cargable = 0;
              $hora_guardada = $horas_cargables[$j]->horas_cargadas;
              $fecha_guardada = $horas_cargables[$j]->fecha;
              $h++;
            }
          }
        }
        if ($hora_guardada > 8.0) {
          $exceso_cargable = $hora_guardada - 8.0;
          $hora_guardada = 8.0;
        }
        $total_horas_cargables[$h] = array('id_usuario' => $usuarios[$i]->id, 'fecha' => $fecha_guardada, 'horas_cargadas' => $hora_guardada, 'exceso_cargable' => $exceso_cargable);
        $h++;
        $hora_guardada = 0;
        $fecha_guardada = 0;
        $exceso_cargable = 0;
        $comienzo = 1;
      }

      for ($j=0; $j < count($total_horas_cargables) ; $j++) { 
        if ($comienzo === 1) {
          $hora_guardada = $total_horas_cargables[$j]["horas_cargadas"];
          $id_usuario_guardado = $total_horas_cargables[$j]["id_usuario"];
          $exceso_cargable_guardado = $total_horas_cargables[$j]["exceso_cargable"];
          $comienzo = 0;
        }elseif ($id_usuario_guardado === $total_horas_cargables[$j]["id_usuario"]) {
          $hora_guardada = $hora_guardada + $total_horas_cargables[$j]["horas_cargadas"];
          $exceso_cargable_guardado = $exceso_cargable_guardado + $total_horas_cargables[$j]["exceso_cargable"];
        }else{
          $suma_horas_cargables[$f] = array('id_usuario' => $id_usuario_guardado, 'fecha' => $fecha_guardada, 'horas_cargadas' => $hora_guardada, 'exceso_cargable' => $exceso_cargable_guardado);
          $hora_guardada = $total_horas_cargables[$j]["horas_cargadas"];
          $id_usuario_guardado = $total_horas_cargables[$j]["id_usuario"];
          $exceso_cargable_guardado = $total_horas_cargables[$j]["exceso_cargable"];
          $f++;
        }
      }
      $suma_horas_cargables[$f] = array('id_usuario' => $id_usuario_guardado, 'fecha' => $fecha_guardada, 'horas_cargadas' => $hora_guardada, 'exceso_cargable' => $exceso_cargable_guardado);

      $hora_guardada = 0;
      $comienzo = 1;

      for ($j=0; $j < count($horas_no_cargables) ; $j++) { 
        if ($comienzo === 1) {
          $hora_guardada = $horas_no_cargables[$j]["horas_cargadas"];
          $id_usuario_guardado = $horas_no_cargables[$j]["id_usuario"];
          $exceso_no_cargable_guardado = $horas_no_cargables[$j]["exceso_no_cargable"];
          $comienzo = 0;
        }elseif ($id_usuario_guardado === $horas_no_cargables[$j]["id_usuario"]) {
          $hora_guardada = $hora_guardada + $horas_no_cargables[$j]["horas_cargadas"];
          $exceso_no_cargable_guardado = $exceso_no_cargable_guardado + $horas_no_cargables[$j]["exceso_no_cargable"];
        }else{
          $suma_horas_no_cargables[$g] = array('id_usuario' => $id_usuario_guardado, 'fecha' => $fecha_guardada, 'horas_cargadas' => $hora_guardada, 'exceso_no_cargable' => $exceso_no_cargable_guardado);
          $hora_guardada = $horas_no_cargables[$j]["horas_cargadas"];
          $id_usuario_guardado = $horas_no_cargables[$j]["id_usuario"];
          $exceso_no_cargable_guardado = $horas_no_cargables[$j]["exceso_no_cargable"];
          $g++;
        }
      }
      $suma_horas_no_cargables[$g] = array('id_usuario' => $id_usuario_guardado, 'fecha' => $fecha_guardada, 'horas_cargadas' => $hora_guardada, 'exceso_no_cargable' => $exceso_no_cargable_guardado);


      for ($i=0; $i < count($usuarios) ; $i++) { 
        for ($j=0; $j < count($suma_horas_cargables); $j++) { 
          if ($suma_horas_cargables[$j]['id_usuario'] === $usuarios[$i]->id) {
            $t_horas_cargables = $t_horas_cargables + $suma_horas_cargables[$j]['horas_cargadas'];
            $t_exceso_cargables = $t_exceso_cargables + $suma_horas_cargables[$j]['exceso_cargable'];
          }
        }
        for ($j=0; $j < count($suma_horas_no_cargables); $j++) { 
          if ($suma_horas_no_cargables[$j]['id_usuario'] === $usuarios[$i]->id) {
            $t_horas_no_cargables = $t_horas_no_cargables + $suma_horas_no_cargables[$j]['horas_cargadas'];
            $t_exceso_no_cargables = $t_exceso_no_cargables + $suma_horas_no_cargables[$j]['exceso_no_cargable'];
          }
        }
        
        //$t_horas_cargadas = $t_horas_cargables + $t_horas_no_cargables;
        $cuenta_cargable = $t_horas_cargables + $t_exceso_cargables;
        $cuenta_no_cargable = $t_horas_no_cargables + $t_exceso_no_cargables;
        $copia = $t_horas_cargables;
        if (($cuenta_cargable + $cuenta_no_cargable - $maximo_horas) >= 0) {
          $exceso = $cuenta_cargable + $cuenta_no_cargable - $maximo_horas;
        }else{
          $exceso = 0;
        }
        if ($exceso > 0 && ($cuenta_no_cargable - $exceso) >= 0) {
          $cuenta_no_cargable = $cuenta_no_cargable - $exceso;
          $n_exceso_no_cargables = $exceso;
        }elseif ($exceso > 0 && ($cuenta_no_cargable - $exceso) < 0) {
          $exceso = $exceso - $cuenta_no_cargable;
          $cuenta_no_cargable = 0;
          $cuenta_cargable = $cuenta_cargable - $exceso;
          $n_exceso_cargables = $exceso;
        }elseif ($exceso > 0 && ($cuenta_cargable - $exceso) >= 0) {
          $cuenta_cargable = $cuenta_cargable - $exceso;
          $n_exceso_cargables = $exceso;
        }
        $total_horas_cargadas = $cuenta_cargable + $cuenta_no_cargable + $exceso; 
        $t_horas_cargables = $cuenta_cargable;
        $t_horas_no_cargables = $cuenta_no_cargable;

        $porcen_carga_total = round($total_horas_cargadas/$maximo_horas*100,2);
        $porcen_carga_total = "$porcen_carga_total %"; 
        if ($t_horas_no_cargables > 0) {
          $porcen_horas_no_cargables = round($t_horas_no_cargables/$total_horas_cargadas*100,2);
          $porcen_horas_no_cargables = "$porcen_horas_no_cargables %";
          $porcen_carga_no_cliente = round($t_horas_no_cargables/$maximo_horas*100,2);
          $porcen_carga_no_cliente = "$porcen_carga_no_cliente %";
        }
        if ($t_horas_cargables > 0) {
          $porcen_horas_cargables = round($t_horas_cargables/$total_horas_cargadas*100,2);
          $porcen_horas_cargables = "$porcen_horas_cargables %";
          $porcen_carga_cliente = round($t_horas_cargables/$maximo_horas*100,2);
          $porcen_carga_cliente = "$porcen_carga_cliente %";
        }
        if ($t_horas_no_cargables > 0) {
          $porcen_carga_no_cliente = round($t_horas_no_cargables/$maximo_horas*100,2);
          $porcen_carga_no_cliente = "$porcen_carga_no_cliente %";
        }
        if ($t_horas_cargables > 0) {
          $porcen_carga_cliente = round($t_horas_cargables/$maximo_horas*100,2);
          $porcen_carga_cliente = "$porcen_carga_cliente %";
        }


        $total[$i] = array('id' => $usuarios[$i]->id, 'codigo' => $usuarios[$i]->codigo, 'nombre' => $usuarios[$i]->nombre, 'total_horas_cargables' => $t_horas_cargables, 'total_horas_no_cargables' => $t_horas_no_cargables, 'total_horas' => $total_horas_cargadas, 'porcen_carga_cliente' => $porcen_carga_cliente, 'porcen_carga_no_cliente' => $porcen_carga_no_cliente, 'porcen_horas_cargables' => $porcen_horas_cargables, 'porcen_horas_no_cargables' => $porcen_horas_no_cargables, 'porcen_carga_total' => $porcen_carga_total, 'exceso' => $exceso, 'maximo_horas' => $maximo_horas, 'exceso_cargables' => $n_exceso_cargables, 'exceso_no_cargables' => $n_exceso_no_cargables);
        $t_horas_cargables = 0;
        $t_exceso_cargables = 0;
        $t_horas_no_cargables = 0;
        $t_exceso_no_cargables = 0;
        $t_horas_cargadas = 0;
        $exceso = 0;
        $porcen_horas_cargables = "0 %";
        $porcen_carga_cliente = "0 %";
        $porcen_horas_no_cargables = "0 %";
        $porcen_carga_total = "0 %";
        $porcen_carga_no_cliente = "0 %";
        $exceso_cargable = 0;
        $cuenta_cargable = 0;
        $cuenta_no_cargable = 0;
        $total_horas_cargadas= 0;
        $copia = 0;
        $n_exceso_cargables = 0;
        $n_exceso_no_cargables = 0;
      }
      return $total;      

    }// Fin

}
