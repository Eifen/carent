<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class FacturacionModel extends Model
{

    function permisosMenu($id_usuario, $id_menu){

      $permisos = DB::select('SELECT
                               (SELECT COUNT(1)
                                FROM tbl_menu_usuario
                                WHERE id_usuario = '.$id_usuario.'
                                AND id_menu = '.$id_menu.'
                                AND r = 1
                                LIMIT 1
                               ) AS permiso_ver,
                               (SELECT COUNT(1)
                                FROM tbl_menu_usuario
                                WHERE id_usuario = '.$id_usuario.'
                                AND id_menu = '.$id_menu.'
                                AND c = 1
                                LIMIT 1
                               ) AS permiso_crear,
                               (SELECT COUNT(1)
                                FROM tbl_menu_usuario
                                WHERE id_usuario = '.$id_usuario.'
                                AND id_menu = '.$id_menu.'
                                AND u = 1
                                LIMIT 1
                               ) AS permiso_actualizar');

      return $permisos[0];

    }// Fin permisosMenu

    function divisiones(){

      $sql = DB::select('SELECT d.id,
                                d.descripcion
                         FROM tbl_division d
                         ORDER BY d.descripcion ASC');

      return $sql;

    }// Fin divisiones

    function estatusProyectos(){

      $sql = DB::select('SELECT e.valor,
                                e.descripcion
                         FROM tbl_estatus e
                         WHERE e.tabla = "tbl_proyecto"
                         ORDER BY e.descripcion ASC');

      return $sql;

    }// Fin estatusProyectos

    function proyectosFacturacion(){

      $sql = DB::select('SELECT p.id,
                                p.descripcion AS proyecto,
                                CONCAT(so.nombre_1," ",so.nombre_2," ",so.apellido_1," ",so.apellido_2) AS socio,
                                CONCAT(ge.nombre_1," ",ge.nombre_2," ",ge.apellido_1," ",ge.apellido_2) AS gerente,
                                DATE_FORMAT(p.fecha_contratacion, "%d/%m/%Y") AS fecha_contratacion,
                                FORMAT(p.monto,2,"de_DE") AS monto_contratado,
                                mo.simbolo AS simbolo_moneda,
                                e.descripcion estatus
                         FROM tbl_proyecto p,
                              tbl_usuario so,
                              tbl_usuario ge,
                              tbl_monedas mo,
                              tbl_estatus e
                         WHERE p.id_socio = so.id
                         AND p.id_gerente = ge.id
                         AND p.id_moneda = mo.id
                         AND p.id_estatus = e.valor
                         AND e.tabla = "tbl_proyecto"
                         ORDER BY p.descripcion ASC');

      return $sql;

    }

}
