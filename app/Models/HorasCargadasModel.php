<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class HorasCargadasModel extends Model
{

    function detalleProyAnalista($idProyAnalista){

    	$info = DB::select('SELECT (SELECT p.descripcion FROM tbl_proyecto p WHERE a.id_proyecto = 	p.id 					  )descripcion,
    							   (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = (SELECT p.id_cliente FROM tbl_proyecto p WHERE a.id_proyecto = p.id))cliente,
    							   (SELECT CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) FROM tbl_usuario u WHERE a.id_analista = u.id)nombre,
                     a.horas_asignadas
    						FROM tbl_proyecto_analista a
    						WHERE a.id = '.$idProyAnalista.'');
    if(count($info) > 0){
      return $info;
    }else{
      return array();
    }
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

    function permisoCrear($id_usuario, $id_menu,$idProyAnalista){

      $permiso = DB::select('SELECT CASE mu.C
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                    END AS permiso
                             FROM tbl_menu_usuario mu
                             WHERE mu.id_usuario = '.$id_usuario.'
                             AND mu.C = 1
                             AND mu.id_menu = '. $id_menu.'
                             AND (SELECT p.id_estatus FROM tbl_proyecto p WHERE p.id = (SELECT a.id_proyecto FROM tbl_proyecto_analista a WHERE a.id = '.$idProyAnalista.')) = 1');

      if(count($permiso) > 0){

        return $permiso[0]->permiso;

      }else{

        return false;

      }

    }

    function horasCargadas($idProyAnalista){

    	$info = DB::select('SELECT h.id,
    							               h.fecha,
    							               h.descripcion,
    							               h.horas_trabajadas
    						            FROM tbl_horas_cargables h
    						            WHERE h.id_proy_analista = '.$idProyAnalista.'
    						            ORDER BY h.fecha DESC');
      if(count($info) > 0){
        return $info;
      }else{
        return array();
      }
    }


    function cargarHoras($idProyAnalista,$fecha,$descripcion,$horas_trabajadas){

      DB::beginTransaction();

      $data = array("id_proy_analista" => $idProyAnalista,
                    "fecha" => $fecha,
                    "descripcion" => $descripcion,
                    "horas_trabajadas" => $horas_trabajadas);

      $horasCargadas = DB::table('tbl_horas_cargables')->insertGetId($data);

      if($horasCargadas){

        $analista = db::select('SELECT u.codigo FROM tbl_usuario u WHERE u.id = (SELECT a.id_analista  FROM tbl_proyecto_analista a WHERE a.id = '.$idProyAnalista.')');
        $proyecto = db::select('SELECT UPPER(p.descripcion) AS descripcion FROM tbl_proyecto p WHERE p.id = (SELECT a.id_proyecto  FROM tbl_proyecto_analista a WHERE a.id = '.$idProyAnalista.')');

        DB::commit();

        return array(
          "analista" => $analista[0]->codigo,
          "proyecto" => $proyecto[0]->descripcion,
          "response" => true,
          "message" => "Horas Cargadas con éxito."
        );

      }else{

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de cargar horas");

      }

    }

    function detalleModHorasCargadas($idHcargadas){

    	$info = DB::select('SELECT h.id,
    		 					               DATE_ADD(h.fecha, INTERVAL 1 DAY) AS fecha,
    							               h.descripcion,
    							               h.horas_trabajadas
    						FROM tbl_horas_cargables h
    						WHERE h.id = '.$idHcargadas.'');
    if(count($info) > 0){
      return $info[0];
    }else{
      return array();
    }
    }

    function ModificarHorasCargadas($idHoraCargada,$fecha,$descripcion,$horas_trabajadas){

      DB::beginTransaction();

      try{

        $data = array("fecha" => $fecha,
                    "descripcion" => $descripcion,
                    "horas_trabajadas" => $horas_trabajadas);

        $update = DB::table('tbl_horas_cargables')->where("id",$idHoraCargada)->update($data);

        DB::commit();
        $info = db::select('SELECT * FROM tbl_proyecto_analista WHERE id = (SELECT id_proy_analista FROM tbl_horas_cargables WHERE id = '.$idHoraCargada.')');

        $analista = db::select('SELECT u.codigo FROM tbl_usuario u WHERE u.id = (SELECT a.id_analista  FROM tbl_proyecto_analista a WHERE a.id = '.$info[0]->id.')');

        $proyecto = db::select('SELECT UPPER(p.descripcion) AS descripcion FROM tbl_proyecto p WHERE p.id = (SELECT a.id_proyecto  FROM tbl_proyecto_analista a WHERE a.id = '.$info[0]->id.')');

        return array(
          "analista" => $analista[0]->codigo,
          "proyecto" => $proyecto[0]->descripcion,
          "response" => true,
          "message" => "Horas cargadas actualizadas con éxito."
        );

      } catch(\Illuminate\Database\QueryException $ex){

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de actualizar la información del proyecto.");

      }

    }

    function detalleEliHorasCargadas($idHcargadas){

    	$info = DB::select('SELECT h.id,
    		 					   h.fecha,
    							   h.descripcion,
    							   h.horas_trabajadas
    						FROM tbl_horas_cargables h
    						WHERE h.id = '.$idHcargadas.'');
    if(count($info) > 0){
      return $info[0];
    }else{
      return array();
    }
    }

    function EliminarHorasCargadas($idHcargadas){

      DB::beginTransaction();
      $horas = DB::select('SELECT * FROM tbl_horas_cargables WHERE id = '.$idHcargadas.'');
      $info = db::select('SELECT * FROM tbl_proyecto_analista WHERE id = (SELECT id_proy_analista FROM tbl_horas_cargables WHERE id = '.$idHcargadas.')');

      $analista = db::select('SELECT u.codigo FROM tbl_usuario u WHERE u.id = (SELECT a.id_analista  FROM tbl_proyecto_analista a WHERE a.id = '.$info[0]->id.')');

      $proyecto = db::select('SELECT UPPER(p.descripcion) AS descripcion FROM tbl_proyecto p WHERE p.id = (SELECT a.id_proyecto  FROM tbl_proyecto_analista a WHERE a.id = '.$info[0]->id.')');

      $delete = DB::table('tbl_horas_cargables')->where("id",$idHcargadas)->delete();

      if($delete){

        DB::commit();

        return array(
          "analista" => $analista[0]->codigo,
          "horas_trabajadas" => $horas[0]->horas_trabajadas,
          "descripcion" => $horas[0]->descripcion,
          "proyecto" => $proyecto[0]->descripcion,
          "response" => true,
          "message" => "Hora eliminada con exito"
        );

      }else{

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de eliminar horas");

      }

    }

    function dataNotificarHorasCargadas($codigo){

      $data_usuario = DB::select('SELECT CONCAT(
                                           u.nombre_1," ",
                                           u.nombre_2," ",
                                           u.apellido_1," ",
                                           u.apellido_2
                                         ) AS nombre,
                                         u.id_division,
                                         d.descripcion AS division,
                                         u.id_cargo,
                                         cu.correo_principal AS correo
                                  FROM tbl_usuario u,
                                       tbl_division d,
                                       tbl_contacto_usuario cu
                                  WHERE u.id_division = d.id
                                  AND u.id = cu.id_usuario
                                  AND u.codigo = '.$codigo);

      $data_supervisores = DB::select('SELECT * FROM(
                                        SELECT DISTINCT u.id,
                                               CONCAT(
                                                 u.nombre_1," ",
                                                 u.nombre_2," ",
                                                 u.apellido_1," ",
                                                 u.apellido_2
                                               ) AS nombre,
                                               cu.correo_principal AS correo
                                        FROM tbl_usuario u,
                                             tbl_cargo_supervisa cs,
                                             tbl_contacto_usuario cu
                                        WHERE u.id_cargo = cs.id_cargo_supervisor
                                        AND u.id = cu.id_usuario
                                        AND u.id_division = '.$data_usuario[0]->id_division.'
                                        AND cs.id_cargo_supervisor IN(
                                           SELECT cs2.id_cargo_supervisor FROM tbl_cargo_supervisa cs2 WHERE cs2.id_cargo = '.$data_usuario[0]->id_cargo.'
                                        )
                                        AND cs.id_cargo_supervisor NOT IN(16)
                                        GROUP BY u.id,
                                                 u.nombre_1,
                                                 u.nombre_2,
                                                 u.apellido_1,
                                                 u.apellido_2,
                                                 cu.correo_principal
                                        )t
                                    ORDER BY nombre ASC');

        return [
          "empleado" => $data_usuario[0],
          "supervisores" => $data_supervisores
        ];

    }

     function dataNotificarEliHorasCargadas($codigo){

      $data_usuario = DB::select('SELECT CONCAT(
                                           u.nombre_1," ",
                                           u.nombre_2," ",
                                           u.apellido_1," ",
                                           u.apellido_2
                                         ) AS nombre,
                                         u.id_division,
                                         d.descripcion AS division,
                                         u.id_cargo,
                                         cu.correo_principal AS correo
                                  FROM tbl_usuario u,
                                       tbl_division d,
                                       tbl_contacto_usuario cu
                                  WHERE u.id_division = d.id
                                  AND u.id = cu.id_usuario
                                  AND u.codigo = '.$codigo);

        return [
          "empleado" => $data_usuario[0]
        ];

    }

}
