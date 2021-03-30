<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ConfigsModel extends Model
{

    function encryptConfig(){

      $sql = DB::select('SELECT * FROM vw_config_encryption LIMIT 1');
      return ["key" => $sql[0]->key, "iv" => $sql[0]->iv];

    }// Fin encryptConfig

}
