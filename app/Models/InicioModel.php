<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class InicioModel extends Model
{

    function menUsuario($id_usuario){

      $menus = DB::select('SELECT m.id,
                                  m.id_menu_padre,
                                  (SELECT m2.descripcion
                                   FROM tbl_menu m2
                                   WHERE m2.id = m.id_menu_padre) desc_menu_padre,
                                  m.descripcion,
                                  m.url
                            FROM tbl_menu m,
                                 tbl_menu_usuario mu,
                                 tbl_usuario u
                            WHERE m.id = mu.id_menu
                            AND u.id = mu.id_usuario
                            AND u.id = '.$id_usuario.'
                            AND m.id_estatus = 1
                            ORDER BY m.id_menu_padre');

      if(count($menus) > 0){

        return $menus;

      }else{

        return array();

      }

    }// Fin menUsuario

}
