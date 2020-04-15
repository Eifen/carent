<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class LoginModel extends Model
{

    function buscarUsuario($codigo){

      $usuario = DB::select('SELECT u.id,
                                    u.clave,
                                    u.avatar,
                                    u.id_estatus,
                                    e.descripcion AS estatus,
                                    cu.correo_principal,
                                    cu.correo_secundario,
                                    cu.telefono_principal,
                                    cu.telefono_secundario,
                                    u.id_division
                             FROM tbl_usuario u,
                                  tbl_estatus e,
                                  tbl_contacto_usuario cu
                             WHERE codigo = "'.$codigo.'"
                             AND e.tabla = "tbl_usuario"
                             AND e.valor = u.id_estatus
                             AND u.id = cu.id_usuario');

      if(count($usuario) > 0){

        return $usuario[0];

      }else{

        return array();

      }

    }// Fin buscarUsuario

    function estatusLoginDenegado($id_estatus){

      $estatus = DB::select('SELECT * FROM tbl_estatus_login_denegado WHERE id_estatus = '.$id_estatus);

      if(count($estatus) > 0){

        return true;

      }else{

        return false;

      }

    }// Fin estatusLoginDenegado

}
