<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        if(!DB::table('vw_'.$tableTarget.'_preview')->exists()){
            return array("response"=>false,"message"=>'Error 0012: table target is empty ('.'vw_'.$tableTarget.'_preview'.')');
        }
        //Si la tabla existe procede a traerlo
        $getSQL = DB::table('vw_'.$tableTarget.'_preview')->get();
        return array("response"=>true,"message"=>$getSQL);
    }

    /**
     * Metodo que retorna los status en función al tipo de tabla
     * @param string $tableReference Tabla a la que se hace referencia. Default null
     * @return array Devuelve un array con la información del status
     */
    public static function GetAllStatus($tableReference = null)
    {
        switch ($tableReference) {
            case 'usuarios':
                return DB::table('vw_users_status')->get();
            default:
                return DB::table('vw_control_status_all')->get();
        }
    }

    /**
     * Metodo que retorna un array asociativo de todas las divisiones
     * No recibe parametros
     */
    public static function getAllDepartments(){
        return DB::table('users_hierarchy_departments')
        ->where('status_id','=',1)
        ->get(['department_id','department_name']);
    }

    /**
     * Metodo que retorna un array asociativo de todos los cargos
     * No recibe parametros
     */
    public static function getAllPosition(){
        return DB::table(('users_hierarchy_positions'))
        ->where("status_id","=",1)
        ->get(['position_id','position_name']);
    }
}
