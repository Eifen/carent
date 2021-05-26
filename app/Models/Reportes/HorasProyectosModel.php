<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class HorasProyectosModel extends Model
{


    function proyectos(){

      $proyectos = DB::select('SELECT p.id AS id_proyecto,
                                      p.descripcion
                                FROM tbl_proyecto p');

      return $proyectos;

    }// Fin proyectos

    function cargos(){

      $cargos = DB::select('SELECT ce.id,
                                         ce.descripcion
                                FROM tbl_cargo_empleado ce');

      return $cargos;

    }// Fin cargos

    function divisionUsuario($usuario_id){

      $divisionUsuario = DB::select('SELECT u.id_division
                                     FROM tbl_usuario u
                                     WHERE u.id = '.$usuario_id.'');

      return $divisionUsuario[0]->id_division;

    }// Fin divisionUsuario

    function divisiones($usuario_div, $usuario_id){

      if($usuario_id == 1 || $usuario_id == 140 || $usuario_id == 144 || $usuario_id == 146 || $usuario_id == 154){
        $sql_division = "";
      }else{
        $sql_division = 'AND d.id = '.$usuario_div.'';
      }

      $divisiones = DB::select('SELECT d.id,
                                       d.descripcion
                                FROM tbl_division d
                                WHERE d.id_estatus = 1
                                '.$sql_division.'');

      return $divisiones;

    }// Fin divisiones
   
    function repoCantidadHorasProy($id_usuario, $paginar, $divisiones, $cargos, $desde = 0, $proyecto = null, $empleado = null, $cliente = null){

      if($id_usuario == 1 || $id_usuario == 140 || $id_usuario == 144 || $id_usuario == 146 || $id_usuario == 154){
        $sql_asignado = "";
      }else{
        $sql_asignado = 'AND (p.id_socio = '.$id_usuario.' OR p.id_socio_calidad = '.$id_usuario.' OR p.id_gerente = '.$id_usuario.' OR pa.id_analista = '.$id_usuario.' OR p.id = (SELECT pd.id_proyecto FROM tbl_proyecto_divisiones pd WHERE p.id = pd.id_proyecto AND pd.id_gerente = '.$id_usuario.'))';
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

      if($empleado != null && trim($empleado) != ""){
          $sql_empleado = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($empleado).'%"';
        }else{
          $sql_empleado = "";
        }

      $sql = DB::select('SELECT p.id AS id_proyecto,
                                p.descripcion AS proyecto,
                                (SELECT CONCAT(m.simbolo," ", p.monto + SUM(pm.monto)) FROM tbl_proy_monto_adicional pm WHERE p.id = pm.id_proyecto) montoA,
                                CONCAT(m.simbolo," ", p.monto) AS monto ,
                                u.id_division,
                                d.descripcion AS division,
                                u.id_cargo,
                                ce.descripcion AS cargo,
                                CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS empleado,
                                u.codigo,
                                c.razon_social AS cliente,
                                TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(hc.horas_trabajadas))),"%H") horas_trabajadas,
                                (SELECT SUM(pd.horas_contratadas) FROM tbl_proyecto_divisiones pd WHERE pa.id_proyecto = pd.id_proyecto) horas_contratadas,
                                (SELECT SUM(ph.horas) FROM tbl_proyecto_divisiones pd, tbl_proy_div_horas_adic ph WHERE pd.id_proyecto = p.id AND pd.id  = ph.id_proy_div) horas_adicional
                         FROM tbl_horas_cargables hc,
                              tbl_proyecto_analista pa,
                              tbl_usuario u,
                              tbl_proyecto p,
                              tbl_cliente c,
                              tbl_division d,
                              tbl_cargo_empleado ce,
                              tbl_monedas m
                         WHERE hc.id_proy_analista = pa.id
                         AND pa.id_analista = u.id
                         AND pa.id_proyecto = p.id
                         AND p.id_cliente = c.id
                         AND p.id_moneda = m.id
                         AND u.id_division = d.id
                         AND u.id_cargo = ce.id
                         '.$sql_asignado.'
                         '.$sql_cargos.'
                         '.$sql_division.'
                         '.$sql_proyecto.'
                         '.$sql_cliente.'
                         '.$sql_empleado.'
                         GROUP BY p.id,
                                  p.descripcion,
                                  u.nombre_1,
                                  u.nombre_2,
                                  u.apellido_1,
                                  u.apellido_2,
                                  u.codigo,
                                  c.id,
                                  c.razon_social, 
                                  u.id_division,
                                  u.id_cargo,
                                  m.id
                          ORDER BY p.id ASC, u.id_cargo DESC
                         LIMIT '.$desde.', '.$paginar);

      return $sql;

    }

    function pagCantidadHorasProy($id_usuario, $paginar, $divisiones, $cargos, $proyecto = null, $empleado = null, $cliente = null){

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

      if($empleado != null && trim($empleado) != ""){
          $sql_empleado = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($empleado).'%"';
        }else{
          $sql_empleado = "";
        }


      $sql = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                         FROM(

                           SELECT p.descripcion AS proyecto,
                                  u.id_division,
                                  u.id_cargo,
                                  ce.descripcion AS cargo
                           FROM tbl_horas_cargables hc,
                                tbl_proyecto_analista pa,
                                tbl_usuario u,
                                tbl_proyecto p,
                                tbl_cliente c,
                                tbl_division d,
                                tbl_cargo_empleado ce,
                                tbl_monedas m
                           WHERE hc.id_proy_analista = pa.id
                           AND pa.id_analista = u.id
                           AND pa.id_proyecto = p.id
                           AND p.id_cliente = c.id
                           AND u.id_division = d.id
                           AND u.id_cargo = ce.id
                           AND p.id_moneda = m.id
                           '.$sql_cargos.'
                           '.$sql_division.'
                           '.$sql_proyecto.'
                           '.$sql_cliente.'
                           '.$sql_empleado.'
                           GROUP BY p.descripcion,
                                    u.id_division,
                                    u.id_cargo,
                                    c.id,
                                    ce.descripcion,
                                    m.id
                         )t'
                       );

      return $sql[0]->paginas;

    }

}
