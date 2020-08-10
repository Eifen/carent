<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class AuditoriaLogModel extends Model
{

    function logs_auditoria($parametros){

      $nuevo_log = DB::table('logs_auditoria')->insertGetId($parametros);

      if($nuevo_log){

        return [
          "response" => true
        ];

      }else{

        return [
          "response" => false
        ];

      }

    }// Fin estados

}
