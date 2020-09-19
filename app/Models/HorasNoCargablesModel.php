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

    function crearConceptoNoCargable($concepto){

      if(DB::table('tbl_concepto_horas_no_cargables')->insert(array("descripcion" => $concepto, "id_estatus" => 1))){

        return array("respuesta" => true, "mensaje" => "Concepto creado con éxito!");

      }else{

        return array("respuesta" => false, "mensaje" => "Error al crear el concepto, intente nuevamente!");

      }

    }// Fin crearConceptoNoCargable

    function modificarConceptoNoCargable($id,$concepto,$id_estatus){

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

    function horasCargadas($paginar, $desde, $id_empleado = null, $id_division = null, $supervisa, $supervisaTodo, $id_concepto = null, $id_estatus = null, $fechaDesde = null, $fechaHasta = null){

      if($supervisaTodo){
        $sql_division = "";
      }else if($id_division == null){
        $sql_division = "";
      }else{

        $idsDiv = [];

        if(is_array($id_division)){

          foreach ($id_division as $key => $item) {
            $item = json_decode($item);
            array_push($idsDiv,$item->id);
          }

        }else{

          array_push($idsDiv,$id_division);

        }

        $idsDiv = implode(",", $idsDiv);
        $sql_division = " AND hnc.id_division IN (".$idsDiv.")";

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

      if($fechaDesde != null && $fechaHasta != null){
        $sql_fecha = " AND fecha_desde BETWEEN '".$fechaDesde."' AND '".$fechaHasta."'";
      }else if($fechaDesde != null && $fechaHasta == null){
          $sql_fecha = " AND fecha_desde = '".$fechaDesde."'";
      }else{
        $sql_fecha = "";
      }

      if($supervisa == "true"){
        $sql_empleado = "";
      }else if($id_empleado == null){
        $sql_empleado = "";
      }else{

        $idsEmp = [];

        if(is_array($id_empleado)){

          foreach ($id_empleado as $key => $item) {
            $item = json_decode($item);
            array_push($idsEmp,$item->id);
          }

        }else{

          array_push($idsEmp,$id_empleado);

        }

        $idsEmp = implode(",", $idsEmp);
        $sql_empleado = " AND u.id IN (".$idsEmp.")";

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
                                hnc.id_usuario,
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
                         ".$sql_fecha."
                         AND hnc.id_estatus NOT IN(4)
                         ORDER BY hnc.fecha_desde DESC
                         LIMIT ".$desde.", ".$paginar);

        return $sql;

    }// Fin horasCargadas

    function cantidadPaginasHorasCargadas($paginar, $id_empleado = null, $id_division = null, $supervisa, $supervisaTodo, $id_concepto = null, $id_estatus = null, $fechaDesde = null, $fechaHasta = null){

      if($supervisaTodo){
        $sql_division = "";
      }else if($id_division == null){
        $sql_division = "";
      }else{

        $idsDiv = [];

        if(is_array($id_division)){

          foreach ($id_division as $key => $item) {
            $item = json_decode($item);
            array_push($idsDiv,$item->id);
          }

        }else{

          array_push($idsDiv,$id_division);

        }

        $idsDiv = implode(",", $idsDiv);
        $sql_division = " AND hnc.id_division IN (".$idsDiv.")";

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

        $idsEmp = [];

        if(is_array($id_empleado)){

          foreach ($id_empleado as $key => $item) {
            $item = json_decode($item);
            array_push($idsEmp,$item->id);
          }

        }else{

          array_push($idsEmp,$id_empleado);

        }

        $idsEmp = implode(",", $idsEmp);
        $sql_empleado = " AND u.id IN (".$idsEmp.")";

      }

      if($fechaDesde != null && $fechaHasta != null){
        $sql_fecha = " AND fecha_desde BETWEEN '".$fechaDesde."' AND '".$fechaHasta."'";
      }else if($fechaDesde != null && $fechaHasta == null){
          $sql_fecha = " AND fecha_desde = '".$fechaDesde."'";
      }else{
        $sql_fecha = "";
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
                                  '.$sql_empleado.'
                                  '.$sql_fecha.'
                                  AND hnc.id_estatus NOT IN(4)');

      return $numConceptos[0]->paginas;

    }

    function registrarHorasNoCargables($parametros){

      $sql_fecha_desde = DB::select('SELECT COUNT(1) existe
                                     FROM tbl_horas_no_cargables
                                     WHERE ("'.$parametros["fecha_desde"].'" + INTERVAL 1 SECOND)
                                       BETWEEN fecha_desde AND fecha_hasta
                                       AND id_estatus = 1
                                       AND id_usuario = '.$parametros["id_usuario"].'
                                     OR fecha_desde
                                       BETWEEN ("'.$parametros["fecha_desde"].'" + INTERVAL 1 SECOND)
                                       AND ("'.$parametros["fecha_hasta"].'" - INTERVAL 1 SECOND)
                                       AND id_usuario = '.$parametros["id_usuario"].'
                                       AND id_estatus = 1');

      if((int) $sql_fecha_desde[0]->existe == 0){

        // Chequeamos que la fecha hasta no este usada
        $sql_fecha_hasta = DB::select('SELECT COUNT(1) existe
                                       FROM tbl_horas_no_cargables
                                       WHERE ("'.$parametros["fecha_hasta"].'" - INTERVAL 1 SECOND)
                                         BETWEEN fecha_desde AND fecha_hasta
                                         AND id_estatus = 1
                                         AND id_usuario = '.$parametros["id_usuario"].'
                                       OR fecha_hasta
                                         BETWEEN ("'.$parametros["fecha_desde"].'" + INTERVAL 1 SECOND)
                                         AND ("'.$parametros["fecha_hasta"].'" - INTERVAL 1 SECOND)
                                         AND id_usuario = '.$parametros["id_usuario"].'
                                         AND id_estatus = 1');

        if((int) $sql_fecha_hasta[0]->existe == 0){

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

        }else{
          return array("response" => false, "message" => "La fecha HASTA ya ha sido cargada en otra Hora No Cargable, por favor filtre por fecha y busque cuales conceptos ya cargados posee dicha fecha!");
        }

      }else{
        return array("response" => false, "message" => "La fecha DESDE ya ha sido cargada en otra Hora No Cargable, por favor filtre por fecha y busque cuales conceptos ya cargados posee dicha fecha!");
      }

    }// Fin registrarHorasNoCargables

    function modificarHorasNoCargables($parametros, $id, $id_usuario){

      // Chequeamos que la fecha desde no este usada
      $sql_fecha_desde = DB::select('SELECT COUNT(1) existe
                                     FROM tbl_horas_no_cargables
                                     WHERE ("'.$parametros["fecha_desde"].'" + INTERVAL 1 SECOND)
                                       BETWEEN fecha_desde AND fecha_hasta
                                       AND id <> '.$id.'
                                       AND id_estatus = 1
                                     OR fecha_desde
                                       BETWEEN ("'.$parametros["fecha_desde"].'" + INTERVAL 1 SECOND)
                                       AND ("'.$parametros["fecha_hasta"].'" - INTERVAL 1 SECOND)
                                       AND id <> '.$id.'
                                       AND id_estatus = 1');

      if((int) $sql_fecha_desde[0]->existe == 0){

        try {

          $update = DB::table('tbl_horas_no_cargables')->where("id",$id)->update($parametros);

          return array("respuesta" => true, "mensaje" => "Horas modificadas con éxito!");

        } catch(\Illuminate\Database\QueryException $ex){

          return array("respuesta" => false, "mensaje" => "Error al modificar las horas, intente nuevamente!");

        }

      }else{
        return array("response" => false, "message" => "La fecha DESDE ya ha sido cargada en otra Hora No Cargable, por favor filtre por fecha y busque cuales conceptos ya cargados posee dicha fecha!");
      }

    }

    function eliminarHorasNoCargables($id){

      if(DB::table('tbl_horas_no_cargables')->where("id",$id)->update(array("id_estatus" => 4))){
        return array("respuesta" => true, "mensaje" => "Horas eliminadas con éxito!");
      }else{
        return array("respuesta" => false, "mensaje" => "Error al eliminar las horas, intente nuevamente!");
      }

    }

}
