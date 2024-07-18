<?php

namespace App\Models;

use App\Http\Controllers\ConfigController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use function PHPUnit\Framework\isEmpty;

class UsersModel extends Model
{
    /**
     * Metodo que devuelve un array con los tipos de documento
     * @return Array info tipo de documento de identidad
     */
    public static function TypeDocument()
    {
        $getType = DB::table('users_identity_type')
            ->get(['identity_abbreviation', 'identity_description']);
        return $getType;
        //[0] = Venezolana, [1] = Extranjera
    }

    /**
     * Metodo que devuelve los permisos que tiene el usuario
     * @param int $userCode Codigo del usuario
     */
    public static function getAccessInfo($userCode)
    {
        $userId = DB::table('users')
            ->where('user_code', '=', $userCode)
            ->value('user_id');

        $arrayAccess = LoginModel::getArrayAccess($userId);

        return $arrayAccess;
    }

    /**
     * Metodo que devuelve un array con todos los estados del pais registrados
     * @return Array info del estado
     */
    public static function GetAllState()
    {
        $getState = DB::table('users_address_states')->get();
        return $getState;
        //state_id, state_name, Iso3166-2
    }

    /**
     * Metodo que devuelve los municipios
     * @return Array info de los municipios disponibles
     */
    public static function GetMunicipality()
    {
        $getMunicipality = DB::table('users_address_municipalities')
            ->get(['municipality_id', 'municipality_name', 'state_id']);
        return $getMunicipality;
    }

    /**
     * Metodo que devuelve todas las parroquias
     * @return Array info de todas las parroquias disponibles
     */
    public static function GetParish()
    {
        $getParish = DB::table('users_address_parishes')
            ->get(['parish_id', 'parish_name', 'municipality_id']);
        return $getParish;
    }

    public static function getUserInfo($userId)
    {
        return DB::table('users')->where('user_id', '=', $userId)
            ->first();
    }

    /**
     * Metodo que abstrae toda la informacion de los usuarios de la base de datos
     */
    public static function getInfoUsers()
    {
        return DB::select('SELECT us.user_id, us.user_code, CONCAT(us.first_surname," ",us.second_surname," ",us.first_name," ",us.second_name) as "user_name", us.department_id, IF(uhd.department_prefix LIKE "%ADM%","ADMON",uhd.department_prefix) as "department_prefix", uhd.department_order, us.status_id, cs.status_prefix, us.admission_date, us.departure_date, uhp.nivel_id, cnp.nivel_description, cnp.nivel_percen, uc.primary_email
        FROM users us
        INNER JOIN users_hierarchy_positions uhp ON us.position_id = uhp.position_id
        INNER JOIN users_hierarchy_departments uhd ON us.department_id = uhd.department_id
        INNER JOIN control_nivels_position cnp ON uhp.nivel_id = cnp.nivel_id
        INNER JOIN users_contact uc ON us.user_id = uc.user_id
        INNER JOIN control_status cs ON us.status_id = cs.status_id
        WHERE us.user_id NOT IN (?,?)
        ORDER BY uhd.department_order, uhp.nivel_id, us.first_surname, us.second_surname, us.first_name, us.second_name ASC;', [1, 267]);
    }

    /**
     * Metodo que devuelve los datos del usuario
     * @param Int $codigo: Almacena el codigo del usuario
     * @return Array devuelve un array de referencias data["info1"] con la información del usuario
     */
    public static function PrepareDataUpdate($codigo)
    {
        $getUser = DB::table('vw_users_update')
            ->where("user_code", "=", $codigo)
            ->first();

        return $getUser;
    }

    /**
     * Metodo que actualiza los permisos del usuario
     * @param int $userCode: Almacena el codigo del usuario
     * @return Array devuelve un array response, message
     */
    public static function updateAccess($userCode, $userAccess)
    {
        //Id del usuario
        $userId = DB::table('users')
            ->where('user_code', '=', $userCode)
            ->value('user_id');
        //Eliminamos todos los permisos que tenga ese usuario
        DB::table('users_permissions')
            ->where('user_id', '=', $userId)
            ->delete();
        //Recorremos el array para actualizar la informacion
        foreach ($userAccess as $value) {
            if ($value[1]) {
                //Insertamos si el valor esta en true
                DB::table('users_permissions')
                    ->insert([
                        "user_id" => $userId,
                        "access_id" => $value[0]
                    ]);
            }
        }
        //Registramos la bitacora
        DB::select('call sp_insert_logs(?,?,?,?,?,?,?,?,?,@response)', [2, 'access', ConfigController::GetIpUser(), Session::has('userId'), "users_permissions", "NA", "NA", "NA", date('Y-m-d H:i:s')]);
        $GetResponse = DB::select('SELECT @response AS JsonResponse');
        $arrayResponse = json_decode($GetResponse[0]->JsonResponse, true);

        //Enviamos un mensaje distinto
        if (!$arrayResponse["response"]) {
            return array(
                "response" => false,
                "message" => "Error 0011: Insert log has failed (" . $arrayResponse['message'] . ")"
            );
        }

        return array(
            "response" => true,
            "message" => "Los permisos del usuario han sido actualizados. En breves momentos será redireccionado..."
        );
    }

    /**
     * Método que registra al usuario en función a los parametros proporcionados
     * @param mixed $paramsUsers Almacena los datos para la creación de usuario y registrar el movimiento
     * @param mixed $paramsContact Almacena los datos para los documentos del usuario recién creado
     * @param mixed $typeControl Define si hará un create o un update
     */
    public static function ControlUser($paramsUsers, $paramsContact, $typeControl)
    {
        switch ($typeControl) {
            case 'create':
                DB::select('call sp_new_users(?,?,?,?,?,?,?,?,?,?,?,?,?,@response)', $paramsUsers);
                break;

            case 'update':
                DB::select('call sp_update_users(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@response)', $paramsUsers);
                break;
        }

        $getResponse = DB::select('SELECT @response as JsonResponse');
        $response1 = json_decode($getResponse[0]->JsonResponse, true);

        //Si creo o actualizó el usuario exitosamente, procedemos a registrar su contacto y documento o actualizarlo
        if ($response1["response"]) {
            switch ($typeControl) {
                case 'create':
                    DB::select('call sp_new_contact_users(?,?,?,?,?,?,?,@response2)', $paramsContact);
                    break;

                case 'update':
                    DB::select('call sp_update_contact_users(?,?,?,?,?,?,?,@response2)', $paramsContact);
                    break;
            }

            $getResponse2 = DB::select('SELECT @response2 as JsonResponse2');
            $response2 = json_decode($getResponse2[0]->JsonResponse2, true);

            //Si registro el contacto, devuelve la primera respuesta
            if ($response2["response"]) {
                return $response1;
            }

            return $response2;
        }

        return $response1;
    }
}
