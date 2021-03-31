<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class InicioModel extends Model
{

    function contraseñaActualUsuario($id_usuario){

      $clave = DB::select('SELECT u.clave
                           FROM tbl_usuario u
                           WHERE u.id = '.$id_usuario);

      if(count($clave) > 0){

        return $clave[0];

      }else{

        return array();

      }

    }

    function actualizarContraseña($id_usuario, $nuevaClave){

      $nuevaClave = DB::table('tbl_usuario')
                    ->where('id', $id_usuario)
                    ->update(['clave' => $nuevaClave]);

      if($nuevaClave){

        return array("response" => true, "message" => "Contraseña actualizada con éxito!");

      }else{

        return array("response" => false, "message" => "No se pudo actualizar la Contraseña, por favor intente nuevamente!");

      }

    }

}
