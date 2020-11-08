<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ReportesModel extends Model
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
                              ) AS permiso_actualizar,
                              (SELECT COUNT(1)
                               FROM tbl_menu_usuario
                               WHERE id_usuario = '.$id_usuario.'
                               AND id_menu = '.$id_menu.'
                               AND d = 1
                               LIMIT 1
                             ) AS permiso_eliminar');

      return $permisos[0];

    }// Fin permisosMenu

    function reportesAsociados($id_usuario){

      $sql = DB::select('SELECT m.id,
                                m.id_menu_padre,
                                m.descripcion,
                                m.url,
                                m.visible
                          FROM tbl_menu m,
                               tbl_menu_usuario mu
                          WHERE m.id = mu.id_menu
                          AND mu.id_usuario = "'.$id_usuario.'"
                          AND m.id_estatus = 1
                          AND m.id_menu_padre = 17
                          ORDER BY m.id_menu_padre ASC');

      return $sql;

    }

}
