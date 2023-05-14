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

    /**
     * Metodo que retorna las columnas necesarias de toda una tabla
     * @param mixed $tableTarget Almacena la tabla objetivo
     * @return array Devuelve un array en formato JSON
     */
    public function GetAll($tableTarget)
    {
        DB::select('CALL sp_QueryPagination(?,@jsonResponse)', [$tableTarget]);
        $GetReponse = DB::select('SELECT @jsonResponse as JsonDataTable');
        $Response = json_decode($GetReponse[0]-> JsonDataTable,true);
        return $Response;
    }

    /**
     * Metodo que retorna los status en función al tipo de tabla
     * @param string $tableReference Tabla a la que se hace referencia
     * @return array Devuelve un array con la información del status
     */
    public static function GetAllStatus($tableReference)
    {
        switch ($tableReference) {
            case 'usuarios':
                return DB::table('tbl_usuarios_status')->get();
            case 'clientes':
                return DB::table('tbl_clientes_status')->get();
        }
    }
}
