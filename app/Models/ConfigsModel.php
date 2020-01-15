<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ConfigsModel extends Model
{

    function encryptConfig(){

      $key = DB::select('SELECT valor FROM tbl_configuracion WHERE nombre = "encrypt-key"');
      $key = $key[0]->valor;

      $iv = DB::select('SELECT valor FROM tbl_configuracion WHERE nombre = "encrypt-iv"');
      $iv = $iv[0]->valor;

      return array("key" => $key, "iv" => $iv);

    }// Fin encryptConfig  

}
