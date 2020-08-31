<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class HorasNoCargablesModel extends Model
{

    function conceptosHorasNoCargables($paginar, $desde = 0, $concepto = "", $estatus = null){

      if(trim($concepto) != ""){
        $sql_concepto = 'AND LOWER(c.descripcion) LIKE "%'.strtolower($concepto).'%"';
      }else{
        $sql_concepto = "";
      }

      if($estatus != null){
        $sql_estatus = 'AND c.id_estatus = '.$estatus;
      }else{
        $sql_estatus = "";
      }

      $conceptos = DB::select('SELECT c.id,
                                      c.descripcion,
                                      e.descripcion AS estatus,
                                      c.id_estatus
                               FROM tbl_concepto_horas_no_cargables c,
                                    tbl_estatus e
                               WHERE c.id_estatus = e.valor
                               AND tabla = "tbl_concepto_horas_no_cargables"
                               '.$sql_concepto.'
                               '.$sql_estatus.'
                               ORDER BY c.descripcion ASC
                               LIMIT '.$desde.', '.$paginar);

      return $conceptos;

    }// Fin conceptosHorasNoCargables

    function estatusHorasNoCargables(){

      $estatus = DB::select('SELECT e.valor AS id,
                                    e.descripcion
                             FROM tbl_estatus e
                             WHERE tabla = "tbl_concepto_horas_no_cargables"
                             ORDER BY e.descripcion ASC');

      return $estatus;

    }// Fin estatusHorasNoCargables

    function cantidadPaginasConceptosHorasNoCargables($paginar, $concepto = "", $estatus = null){

      if(trim($concepto) != ""){
        $sql_concepto = 'AND LOWER(c.descripcion) LIKE "%'.strtolower($concepto).'%"';
      }else{
        $sql_concepto = "";
      }

      if($estatus != null){
        $sql_estatus = 'AND c.id_estatus = '.$estatus;
      }else{
        $sql_estatus = "";
      }

      $numConceptos = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                                  FROM tbl_concepto_horas_no_cargables c
                                  WHERE c.id IS NOT NULL
                                  '.$sql_concepto.'
                                  '.$sql_estatus);

      return $numConceptos[0]->paginas;

    }

    function crearConceptoNoCargable($concepto,$usuario_id,$fecha,$direccion_ip){

      if(DB::table('tbl_concepto_horas_no_cargables')->insert(array("descripcion" => $concepto, "id_estatus" => 1))){

        return array("respuesta" => true, "mensaje" => "Concepto creado con éxito!");

      }else{

        return array("respuesta" => false, "mensaje" => "Error al crear el concepto, intente nuevamente!");

      }

    }// Fin crearConceptoNoCargable

    function modificarConceptoNoCargable($id,$concepto,$id_estatus,$usuario_id,$fecha,$direccion_ip){

      if(DB::table('tbl_concepto_horas_no_cargables')->where("id",$id)->update(array("descripcion" => $concepto, "id_estatus" => $id_estatus))){
        return array("respuesta" => true, "mensaje" => "Concepto modificado con éxito!");
      }else{
        return array("respuesta" => false, "mensaje" => "Error al modificar el concepto, intente nuevamente!");
      }

    }// Fin modificarConceptoNoCargable

    /*
      Función que valida si supervisa a empleados o no
    */
    function supervisaA($id_cargo, $id_division, $id_usuario){

      $sql = DB::select('SELECT * FROM(
                            (
                             SELECT COUNT(DISTINCT cs.id_cargo_supervisor) num_cargo_sup
                             FROM tbl_cargo_supervisa cs
                             WHERE cs.id_cargo_supervisor <> '.$id_cargo.'
                            ) t1,
                            (
                             SELECT COUNT(1) supervisa
                             FROM tbl_cargo_supervisa cs
                             WHERE cs.id_cargo_supervisor = '.$id_cargo.'
                             AND cs.id_cargo IN(
                             	SELECT DISTINCT cs.id_cargo_supervisor
                                FROM tbl_cargo_supervisa cs
                                WHERE cs.id_cargo_supervisor <> '.$id_cargo.'
                             )) t2
                        )');

      if($sql[0]->num_cargo_sup === $sql[0]->supervisa){ // Supervisa a todos

        $condicion_divisiones = "";
        $condicion_empleados = "";
        $supervisor = true;
        $supervisaTodo = true;

      }else if($sql[0]->num_cargo_sup > $sql[0]->supervisa && $sql[0]->supervisa > 0){ //Directores, Gerentes, etc

        $condicion_divisiones = " WHERE d.id = ".$id_division;
        $condicion_empleados = " WHERE u.id_division = ".$id_division;
        $supervisor = true;
        $supervisaTodo = false;

      }else{

        $condicion_divisiones = " WHERE d.id = ".$id_division;
        $condicion_empleados = " WHERE u.id = ".$id_usuario;
        $supervisor = false;
        $supervisaTodo = false;

      }

      $divisiones = DB::select('SELECT d.id,
                                       d.descripcion
                                FROM tbl_division d
                                '.$condicion_divisiones.'
                                ORDER BY d.descripcion ASC');

      $conceptos = DB::select('SELECT c.id,
                                      c.descripcion
                               FROM tbl_concepto_horas_no_cargables c
                               ORDER BY c.descripcion ASC');

      $empleados = DB::select('SELECT * FROM (
                                 SELECT u.id,
                                        CONCAT(u.nombre_1," ",u.apellido_1) nombre,
                                        u.codigo
                                 FROM tbl_usuario u
                                 '.$condicion_empleados.'
                               ) t
                               ORDER BY nombre ASC');

      $estatus = DB::select('SELECT e.valor id,
                                    e.descripcion
                             FROM tbl_estatus e
                             WHERE e.tabla = "tbl_horas_no_cargables"
                             ORDER BY e.valor ASC');

      return [
        "conceptos" => $conceptos,
        "divisiones" => $divisiones,
        "empleados" => $empleados,
        "estatus" => $estatus,
        "supervisa" => $supervisor,
        "supervisaTodo" => $supervisaTodo
      ];

    }// Fin supervisaA

    function horasCargadas($paginar, $desde, $id_empleado = null, $id_division = null, $supervisa, $supervisaTodo, $id_concepto = null, $id_estatus = null){

      if($supervisaTodo){
        $sql_division = "";
      }else if($id_division == null){
        $sql_division = "";
      }else{
        $sql_division = " AND hnc.id_division = ".$id_division;
      }

      if($id_concepto == null){
        $sql_concepto = "";
      }else{
        $sql_concepto = " AND c.id = ".$id_concepto;
      }

      if($id_estatus == null){
        $sql_estatus = "";
      }else{
        $sql_estatus = " AND hnc.id_estatus = ".$id_estatus;
      }

      if($supervisa == "true"){
        $sql_empleado = "";
      }else if($id_empleado == null){
        $sql_empleado = "";
      }else{
        $sql_empleado = " AND u.id = ".$id_empleado;
      }

      $sql = DB::select("SELECT hnc.id,
                                CONCAT(u.nombre_1,' ',u.apellido_1) nombre,
                                u.codigo,
                                c.id id_concepto,
                                c.descripcion concepto,
                                e.descripcion estatus,
                                d.descripcion division,
                                DATE_FORMAT(hnc.fecha_desde,'%Y-%m-%dT%H:%i:%s.000-04:00') fecha_desde_utc,
                                DATE_FORMAT(hnc.fecha_desde, '%d/%m/%Y %l:%i %p') fecha_desde,
                                DATE_FORMAT(hnc.fecha_hasta,'%Y-%m-%dT%H:%i:%s.000-04:00') fecha_hasta_utc,
                                DATE_FORMAT(hnc.fecha_hasta, '%d/%m/%Y %l:%i %p') fecha_hasta,
                                DATE_FORMAT(hnc.fecha_aprobacion, '%d/%m/%Y %l:%i %p') fecha_aprobacion,
                                hnc.observacion,
                                hnc.id_estatus,
                                IF(u.id = ".session('usuario_id').", true, false) autor,
                                IF(hnc.id_estatus = 1, true, false) editar,
                                DATE_FORMAT(hnc.fecha_aprobacion, '%d/%m/%Y %l:%i %p') fecha_aprobacion,
                                (SELECT CONCAT(u2.nombre_1,' ',u2.apellido_1) FROM tbl_usuario u2 WHERE u2.id = hnc.aprobado_por) aprobado_por
                         FROM tbl_horas_no_cargables hnc,
                              tbl_usuario u,
                              tbl_concepto_horas_no_cargables c,
                              tbl_estatus e,
                              tbl_division d
                         WHERE hnc.id_usuario = u.id
                         AND hnc.id_concepto = c.id
                         AND e.tabla = 'tbl_horas_no_cargables'
                         AND hnc.id_estatus = e.valor
                         AND hnc.id_division = d.id
                         ".$sql_division."
                         ".$sql_concepto."
                         ".$sql_estatus."
                         ".$sql_empleado."
                         ORDER BY hnc.fecha_desde DESC
                         LIMIT ".$desde.", ".$paginar);

        return $sql;

    }// Fin horasCargadas

    function cantidadPaginasHorasCargadas($paginar, $id_empleado = null, $id_division = null, $supervisa, $supervisaTodo, $id_concepto = null, $id_estatus = null){

      if($supervisaTodo){
        $sql_division = "";
      }else if($id_division == null){
        $sql_division = "";
      }else{
        $sql_division = " AND hnc.id_division = ".$id_division;
      }

      if($id_concepto == null){
        $sql_concepto = "";
      }else{
        $sql_concepto = " AND c.id = ".$id_concepto;
      }

      if($id_estatus == null){
        $sql_estatus = "";
      }else{
        $sql_estatus = " AND hnc.id_estatus = ".$id_estatus;
      }

      if($supervisa == "true"){
        $sql_empleado = "";
      }else if($id_empleado == null){
        $sql_empleado = "";
      }else{
        $sql_empleado = " AND u.id = ".$id_empleado;
      }

      $numConceptos = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                                  FROM tbl_horas_no_cargables hnc,
                                       tbl_usuario u,
                                       tbl_concepto_horas_no_cargables c,
                                       tbl_estatus e,
                                       tbl_division d
                                  WHERE hnc.id_usuario = u.id
                                  AND hnc.id_concepto = c.id
                                  AND e.tabla = "tbl_horas_no_cargables"
                                  AND hnc.id_estatus = e.valor
                                  AND hnc.id_division = d.id
                                  '.$sql_division.'
                                  '.$sql_concepto.'
                                  '.$sql_estatus.'
                                  '.$sql_empleado);

      return $numConceptos[0]->paginas;

    }

    function registrarHorasNoCargables($parametros){

      if(DB::table('tbl_horas_no_cargables')->insert($parametros)){

        $analista = db::select('SELECT u.codigo FROM tbl_usuario u WHERE u.id ='.$parametros["id_usuario"].' ');

        return array(
          "analista" => $analista[0]->codigo,
          "response" => true,
          "message" => "Horas cargadas con éxito!"
        );

      }else{
        return array("response" => false, "message" => "Error al tratar de cargar las horas, intente nuevamente!");
      }

    }// Fin registrarHorasNoCargables

    function modificarHorasNoCargables($parametros, $id){

      if(DB::table('tbl_horas_no_cargables')->where("id",$id)->update($parametros)){
        return array("respuesta" => true, "mensaje" => "Horas modificadas con éxito!");
      }else{
        return array("respuesta" => false, "mensaje" => "Error al modificar las horas, intente nuevamente!");
      }

    }

}
