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

        if (is_array($getLimit)) {
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
        if (!DB::table('vw_' . $tableTarget . '_preview')->exists()) {
            return array("response" => false, "message" => 'Error 0012: table target is empty (' . 'vw_' . $tableTarget . '_preview' . ')');
        }
        //Si la tabla existe procede a traerlo
        $getSQL = DB::table('vw_' . $tableTarget . '_preview')->get();
        return array("response" => true, "message" => $getSQL);
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
            case 'adminHours':
                return DB::table('vw_load_admin_status')->get();
                //Si el valor de entrada es '' el sistema colocara null y se ira directamente al default
            default:
                return DB::table('vw_control_status_all')->get();
        }
    }

    /**
     * Metodo que abstrae y retorna un array asociativo de la tabla seleccionada.
     * Debe existir una columna de control de estados (status_id) en la tabla seleccionada
     * @param String $tableReference: Captura la tabla seleccionada
     */
    public static function getAllDataStatusControl($tableReference)
    {
        return DB::table($tableReference)
            ->where("status_id", '=', 1)
            ->get();
    }

    /**
     * Metodo que se encarga de cambiar la contrasena
     * @param $params Recibe los parametros a pasar al procedure
     */
    public static function updatePassword($params)
    {
        DB::select('call sp_change_password(?,?,?,?,@response)', $params);
        $GetResponse = DB::select('SELECT @response as JsonChangeData');
        $ResponseJson = json_decode($GetResponse[0]->JsonChangeData, true);

        return $ResponseJson;
    }

    public static function recoveryPassword($params)
    {
        DB::select('call sp_recovery_password(?,?,@response)', $params);
        $GetResponse = DB::select('SELECT @response as JsonChangeData');
        $ResponseJson = json_decode($GetResponse[0]->JsonChangeData, true);

        return $ResponseJson;
    }

    public static function checkMaintenance()
    {
        $getStatus = DB::table('control_page')
            ->where("page_id", "=", 1)
            ->first();

        return $getStatus->status_id;
    }

    public static function checkDeadline()
    {
        $getDeadline = DB::table('control_page')
            ->where("page_id", "=", 2)
            ->first();

        return $getDeadline->page_value;
    }

    public static function checkDeadMonth()
    {
        $getDeadMonth = DB::table('control_page')
            ->where("page_id", "=", 3)
            ->first();

        return $getDeadMonth->page_value;
    }
}
