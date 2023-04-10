<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ConfigModel extends Model
{
    /**
     * Metodo que abstrae la cantidad máxima de registros de una tabla
     * @param mixed $tableToGet Indica que tabla se va a contar los registros
     * @return array con dos valores. Response (true or false) y data (con el valor o el error)
     */
    public function CountTable($tableToGet)
    {
        //Capturamos la cantidad de registros actuales
        $getLimit = DB::select("SELECT COUNT(LAST_INSERT_ID()) AS maxID FROM $tableToGet");

        if(is_array($getLimit))
        {
            return array(
                "response" => true,
                "data" => $getLimit[0]->maxID
            );
        }

        return array(
            "response" => false,
            "data" => 'No se ha podido capturar el contador'
        );
    }
}
