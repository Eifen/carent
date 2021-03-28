<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class LoginModel extends Model
{

    function login($parametros){

      $funcionario = DB::select('call sp_login(?,?,?,@respuesta)',$parametros);
      $respuestaSp = DB::select('SELECT @respuesta AS respuesta_json');
      $respuestaJson = json_decode($respuestaSp[0]->respuesta_json, true);

      return $respuestaJson;

    }

    function recoverylogin($parametros){

      $funcionario = DB::select('call sp_recuperar_login(?,?,@respuesta)',$parametros);
      $respuestaSp = DB::select('SELECT @respuesta AS respuesta_json');
      $respuestaJson = json_decode($respuestaSp[0]->respuesta_json, true);

      return $respuestaJson;

    }

}
