<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConfigModel;
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

    /**
     * Metodo que devuelve una estructura de informacion de las horas cargadas por un usuario y sus proyectos asignados
     * @param int $userId Id del usuario a consultar
     * @return array Retorna un array asociativo con los siguientes campos: "projectsAssigned" = projectos asignados,
     * "registerHours" = horas registradas a proyectos
     */
    static function getProjectHours($userId)
    {
        $assignedHours = DB::table('projects_users_assigned')
            ->where('user_id', '=', $userId)
            ->get();

        $registerHours = DB::table('control_load_projects_hours')
            ->where('user_id', '=', $userId)
            ->get();

        return array(
            "projectsAssigned" => $assignedHours,
            "registerHours" => $registerHours
        );
    }

    /**
     * Metodo que registra una hora a proyectos
     * @param Array $paramsHours Parametros correspondientes a cada columna de la taba control_load_admin_hours
     * @param Number $loadId Captura el id de carga en caso de existir. Si no existe enviara 0
     */
    static function addAdminHour($paramsHours, $loadId)
    {
        //Actualizamos si el loadId es diferente de 0
        if ($loadId != 0) {
            DB::table('control_load_admin_hours')
                ->where('admin_hour_id', '=', $loadId)
                ->update($paramsHours);
        }

        //Insertamps un nuevo valor si loadId es igual a 0
        if ($loadId == 0) {
            DB::table('control_load_admin_hours')
                ->insert($paramsHours);
        }

        //Preparamos la nueva informacion
        $getNewList = DB::select('call sp_get_hours(?,?)', [$paramsHours["user_id"], 2]);

        return array(
            "response" => true,
            "message" => $getNewList,
            "maintenance" => ConfigModel::checkMaintenance()
        );
    }

    /**
     * Metodo que aprueba o rechazar una hoja. Retorna la nueva lista de horas en el message
     * @param $params Almacena los parametros a pasar al procedimiento almacenado sp_update_load_hours
     * @return array retorna un array asociativo con response y message donde el primero define si fue valido o incorrecto el proceso (true,false)
     */
    static function controlHours($params)
    {
        //Ejecutamos el procedimiento almacenado
        DB::select('call sp_update_load_hours(?,?,?,?,?,?,@response)', $params);
        //Convertimos a un array asociativo
        $convert = DB::select('SELECT @response AS "jsonResponse"');
        $decodeJson = json_decode($convert[0]->jsonResponse, true);

        return $decodeJson;
    }

    //Elimina una hora dependiendo del tipo
    static function deleteHour($params)
    {
        //Ejecutamos el procedimiento almacenado
        DB::select('call sp_delete_load_hours(?,?,?,?,@response)', $params);
        $convert = DB::select('SELECT @response AS JsonResponse');
        $decodeJson = json_decode($convert[0]->JsonResponse, true);

        //$params[1] = Type
        if ($decodeJson["response"] && $params[1] == 1) {
            //Obtenemos la nueva lista. $params[0] = userId
            $getNewList = DB::select('call sp_get_hours(?,?)', [$params[0], 2]);

            return array(
                "response" => true,
                "message" => $getNewList,
                "maintenance" => ConfigModel::checkMaintenance()
            );
        }

        //$params[2] = Type
        if ($decodeJson["response"] && $params[1] == 2) {
            //OBtenemos la nueva lista. $params[0] = userId
            $getNewList = DB::select('call sp_get_hours(?,?)', [$params[0], 1]);

            return array(
                "response" => true,
                "message" => $getNewList
            );
        }
    }

    /**
     * Metodo que actualiza la informacion de las horas asignadas de un usuario
     */
    public static function updateAssignedHour($userAssigned, $assignedHours)
    {
        DB::table('projects_users_assigned')
            ->where('user_assigned_id', '=', $userAssigned)
            ->update([
                "assigned_hours" => $assignedHours
            ]);
    }
}
