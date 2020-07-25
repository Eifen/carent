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

}
