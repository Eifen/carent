<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class InicioModel extends Model
{

  function guardarNuevaClave($parametros){

    $funcionario = DB::select('call sp_usuario_cambia_su_clave(?,?,?,?,@respuesta)',$parametros);
    $respuestaSp = DB::select('SELECT @respuesta AS respuesta_json');
    $respuestaJson = json_decode($respuestaSp[0]->respuesta_json, true);

    return $respuestaJson;

  }

}
