<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ProyectoModel extends Model
{

    function estatusProyectos(){

      $estatus = DB::select('SELECT e.valor AS id,
                                    e.descripcion
                             FROM tbl_estatus e
                             WHERE tabla = "tbl_proyecto"
                             ORDER BY e.descripcion ASC');

      return $estatus;

    }// Fin estatusProyectos

    function divisiones(){

      $divisiones = DB::select('SELECT d.id,
                                       d.descripcion
                                FROM tbl_division d
                                WHERE d.id_estatus = 1
                                ORDER BY d.descripcion ASC');

      return $divisiones;

    }// Fin divisiones

    function clientes(){

      $clientes = DB::select('SELECT c.id,
                                     c.razon_social
                              FROM tbl_cliente c
                              WHERE c.id_estatus = 1
                              ORDER BY c.razon_social ASC');

      return $clientes;

    }// Fin clientes

    function crearProyecto($descripcion,$cliente,$horas,$fechaContratacion,$divisiones,$estatus){

      DB::beginTransaction();

      $data = array("descripcion" => $descripcion,
                    "id_cliente" => $cliente,
                    "horas_contratadas" => $horas,
                    "fecha_contratacion" => $fechaContratacion,
                    "id_estatus" => $estatus);

      $idProyecto = DB::table('tbl_proyecto')->insertGetId($data);

      $divisionCreada = true;

      for($i = 0; $i < count($divisiones); $i++){

        $data = array("id_proyecto" => $idProyecto,
                      "id_division" => $divisiones[$i]);

        if(!DB::table('tbl_proyecto_divisiones')->insert($data)){
          $divisionCreada = false;
          break;
        }

      }

      if($divisionCreada){

        DB::commit();
        return array("response" => true, "message" => "Proyecto creado con éxito.");

      }else{

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de crear el proyecto.");

      }

    }

    function proyectos($id_division, $paginar, $desde = 0, $proyecto = "", $cliente = "", $divisiones = [], $estatus = null){

      if(trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
      }

      if($estatus != null){
        $sql_estatus = 'AND p.id_estatus = '.$estatus;
      }else{
        $sql_estatus = "";
      }

      if($cliente != null){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if(count($divisiones) > 0){

        $idsDivisiones = implode(",", $divisiones);

        $idProyectos = DB::select('SELECT id
                                   FROM tbl_proyecto_divisiones pd
                                   WHERE pd.id_division IN('.$idsDivisiones.')');

        $arrayIds = [];
        foreach($idProyectos as $id){
          $arrayIds[] = $id->id;
        }

        $ids = implode(",", $arrayIds);

        $sql_division = 'AND p.id IN ('.$ids.')';

      }else{
        $sql_division = "";
      }

      $proyectos = DB::select('SELECT p.id,
                                      p.descripcion,
                                      p.horas_contratadas,
                                      p.fecha_contratacion,
                                      e.descripcion AS estatus,
                                      c.razon_social as cliente
                               FROM tbl_proyecto p,
                                    tbl_estatus e,
                                    tbl_cliente c
                               WHERE p.id_estatus = e.valor
                               AND e.tabla = "tbl_proyecto"
                               AND p.id_cliente = c.id
                               '.$sql_proyecto.'
                               '.$sql_estatus.'
                               '.$sql_cliente.'
                               '.$sql_division.'
                               ORDER BY p.id DESC
                               LIMIT '.$desde.', '.$paginar);

      foreach ($proyectos as $key => $value) {
        $proyectos[$key]->divisiones = $this->proyectoDivisiones($proyectos[$key]->id);
      }

      return $proyectos;

    }

    function proyectoDivisiones($id_proyecto){

      $divisiones = DB::select('SELECT d.id,
                                       d.descripcion
                                FROM tbl_division d
                                ORDER BY d.descripcion ASC');

      return $divisiones;

    }

    function permisoActualizar($id_usuario, $id_menu){

      $permiso = DB::select('SELECT CASE mu.U
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                    END AS permiso
                             FROM tbl_menu_usuario mu
                             WHERE mu.id_usuario = '.$id_usuario.'
                             AND mu.U = 1
                             AND mu.id_menu = '. $id_menu);

      if(count($permiso) > 0){

        return $permiso[0]->permiso;

      }else{

        return false;

      }

    }

    function cantidadPaginas($paginar, $proyecto = "", $cliente = "", $divisiones = [], $estatus = null){

      if(trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
      }

      if($estatus != null){
        $sql_estatus = 'AND p.id_estatus = '.$estatus;
      }else{
        $sql_estatus = "";
      }

      if($cliente != null){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if(count($divisiones) > 0){

        $idsDivisiones = implode(",", $divisiones);

        $idProyectos = DB::select('SELECT id
                                   FROM tbl_proyecto_divisiones pd
                                   WHERE pd.id_division IN('.$idsDivisiones.')');

        $arrayIds = [];
        foreach($idProyectos as $id){
          $arrayIds[] = $id->id;
        }

       $ids = implode(",", $arrayIds);

        $sql_division = 'AND p.id IN ('.$ids.')';

      }else{
        $sql_division = "";
      }

      $numConceptos = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                                  FROM tbl_proyecto p,
                                       tbl_cliente c
                                  WHERE p.id_cliente = c.id
                                  '.$sql_proyecto.'
                                  '.$sql_estatus.'
                                  '.$sql_cliente.'
                                  '.$sql_division);

      return $numConceptos[0]->paginas;

    }

    function detalleProyectoModificar($id_proyecto){

      $info = DB::select('SELECT *
                          FROM tbl_proyecto                              
                          WHERE id = '.$id_proyecto.'');

      if(count($info) > 0){

        return $info[0];

      }else{

        return array();

      }

    }

    function detalleDivisionProyecto($id_proyecto){

      $info = DB::select('SELECT id_division
                          FROM tbl_proyecto_divisiones                              
                          WHERE id_proyecto = '.$id_proyecto.'');
      if(count($info) > 0){

        return $info;

      }else{

        return array();
      }
    }

    function modificarProyecto($descripcion,$cliente,$horas,$fechaContratacion,$divisiones,$estatus,$idProyecto,$divisiones_v){

      DB::beginTransaction();

      try{

      $data = array("descripcion" => $descripcion,
                    "id_cliente" => $cliente,
                    "horas_contratadas" => $horas,
                    "fecha_contratacion" => $fechaContratacion,
                    "id_estatus" => $estatus);

      $update = DB::table('tbl_proyecto')->where("id",$idProyecto)->update($data);

      $divisionCreada = true;

      for($i = 0; $i < count($divisiones); $i++){
        $si = 0;
        for($j = 0; $j < count($divisiones_v); $j++){
          if ($divisiones[$i] === $divisiones_v[$j]->id_division) {
            $si=1;
          }
        }
        if ($si === 0) {
          
          $data = array("id_proyecto" => $idProyecto,
                      "id_division" => $divisiones[$i]);
          $div = DB::table('tbl_proyecto_divisiones')->insert($data);
           $divisionCreada = false;
          }
        
      }

      for($i = 0; $i < count($divisiones_v); $i++){
        $no = 0;
        for($j = 0; $j < count($divisiones); $j++){
          if ($divisiones_v[$i]->id_division === $divisiones[$j]) {
            $no=1;
          }
        }
        if ($no === 0) {
          $delete = DB::table('tbl_proyecto_divisiones')->where([['id_proyecto', '=', $idProyecto],['id_division', '=', $divisiones_v[$i]->id_division]])->delete();
          }
        
      }
        DB::commit();
        return array("response" => true, "message" => "Proyecto actualizado con éxito.");

      } catch(\Illuminate\Database\QueryException $ex){

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de actualizar la información del proyecto.");

      }

    }

}
