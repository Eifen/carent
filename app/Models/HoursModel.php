<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HoursModel extends Model
{
    /**
     * Metodo que registra una hora a proyectos
     * @param Array $paramsHours Parametros correspondientes a cada columna de la taba control_load_projects_hours
     * @param Number $loadId Captura el id de carga en caso de existir. Si no existe enviara 0
     */
    static function addHour($paramsHours, $loadId)
    {
        //Actualizamos si el loadId es diferente de 0
        if ($loadId != 0) {
            DB::table('control_load_projects_hours')
                ->where('project_hour_id', '=', $loadId)
                ->update($paramsHours);
        }

        //Insertamps un nuevo valor si loadId es igual a 0
        if ($loadId == 0) {
            DB::table('control_load_projects_hours')
                ->insert($paramsHours);
        }

        //Preparamos la nueva informacion
        $getNewList = DB::select('call sp_get_hours(?,?)', [$paramsHours["user_id"], 1]);

        return array(
            "response" => true,
            "message" => $getNewList
        );
    }
}
