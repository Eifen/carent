<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class UltimaCargaHorasNoCargablesModel extends Model
{

  function divisiones(){

      $divisiones = DB::select('SELECT d.id,
                                      d.descripcion
                                FROM tbl_division d');

      return $divisiones;

    }// Fin divisiones

  function cargos(){

      $cargos = DB::select('SELECT ce.id,
                                   ce.descripcion
                                FROM tbl_cargo_empleado ce');

      return $cargos;

    }// Fin cargos

   
    function ultimaCargaHorasNo($paginar, $divisiones, $cargos, $empleado = null, $desde = 0){

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

      $sql = DB::select('SELECT u.id,
                                CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                                u.codigo,
                                d.descripcion AS division,
                                ce.descripcion AS cargo,
                                (SELECT DATE_FORMAT(hnc.fecha_hasta, "%d-%m-%Y") 
                                  FROM tbl_horas_no_cargables hnc 
                                  WHERE hnc.id_usuario = u.id 
                                  AND hnc.id_estatus IN(1,2) 
                                  ORDER BY hnc.fecha_hasta DESC 
                                  LIMIT 1)fecha_no_cargable
                         FROM tbl_usuario u,                              
                              tbl_division d,
                              tbl_cargo_empleado ce
                         WHERE u.id_division = d.id
                         AND u.id_cargo = ce.id
                         AND u.id_estatus = 1
                         '.$sql_division.'
                         '.$sql_cargos.'
                         '.$sql_empleado.'
                          GROUP BY u.id
                          ORDER BY d.id ASC, 
                                   ce.id ASC, 
                                   nombre ASC
                         LIMIT '.$desde.', '.$paginar);

      $ultimaCarga = [];
      for ($i=0; $i < count($sql) ; $i++) {
        $fecha = ""; 
        if ($sql[$i]->fecha_no_cargable === null) {
          $fecha = "No ha cargado nada hasta la fecha"; 
        }else{
          $fecha = $sql[$i]->fecha_no_cargable; 
        }
        $ultimaCarga[$i] = array('id' => $sql[$i]->id, 'codigo' => $sql[$i]->codigo, 'nombre' => $sql[$i]->nombre, 'division' => $sql[$i]->division, 'cargo' => $sql[$i]->cargo, 'fecha' => $fecha);
      }

      return $ultimaCarga;

    }

    function pagCantidadUltimaCargaHorasNo($paginar, $divisiones, $cargos, $empleado = null){

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

      $sql = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                         FROM(

                           SELECT u.id,
                                  CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                                  u.codigo,
                                  d.descripcion AS division,
                                  ce.descripcion AS cargo
                           FROM tbl_usuario u,                              
                                tbl_division d,
                                tbl_cargo_empleado ce
                           WHERE u.id_division = d.id
                           AND u.id_cargo = ce.id
                           AND u.id_estatus = 1
                           '.$sql_division.'
                           '.$sql_cargos.'
                           '.$sql_empleado.'
                           GROUP BY u.id
                           ORDER BY d.id ASC, 
                                     ce.id ASC, 
                                     nombre ASC
                         )t'
                       );

      return $sql[0]->paginas;

    }

}
