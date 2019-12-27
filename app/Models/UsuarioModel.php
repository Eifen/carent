<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class UsuarioModel extends Model
{

    function encryptConfig(){

      $key = DB::select('SELECT valor FROM tbl_configuracion WHERE nombre = "encrypt-key"');
      $key = $key[0]->valor;

      $iv = DB::select('SELECT valor FROM tbl_configuracion WHERE nombre = "encrypt-iv"');
      $iv = $iv[0]->valor;

      return array("key" => $key, "iv" => $iv);

    }

    function buscarUsuario($codigo){

      $usuario = DB::select('SELECT id, clave, avatar, id_estatus FROM tbl_usuario WHERE codigo = "'.$codigo.'"');

      return $usuario;

    }

}
