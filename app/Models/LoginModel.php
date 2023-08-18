<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoginModel extends Model
{
    /** Método que se encarga de verificar el inicio de sesión
     * @param $DataLogin = Es un array dimensional que contiene la información del inicio de sesión
     */
    public static function VerificarLogin($DataLogin)
    {
        DB::select('call sp_login(?,?,?,@response)', $DataLogin);
        $GetResponse = DB::select('SELECT @response as JsonLoginData');
        $ResponseJson = json_decode($GetResponse[0]->JsonLoginData, true);

        return $ResponseJson;
    }
    /**
     * Método que extrae una Key que tenga estatus activo
     * @return = Retorna un array con la informacion array[key] y array[iv]
     */
    public static function GetEncryptKey()
    {
        $initEncrypt = DB::table('control_encrypts')->where('status_id', '=', 1)->get(['encrypt_key', 'encrypt_iv']);
        $getKey = $initEncrypt[0]->encrypt_key;
        $getIv = $initEncrypt[0]->encrypt_iv;

        return array("key" => $getKey, "iv" => $getIv);
    }

    /**
     * Metodo que se encarga de acomodar un array de acceso dependiendo del usuario
     * @param int $userId Id del usuario
     */
    public static function getArrayAccess($userId)
    {
        $userAccess = DB::table('users_permissions')->where('user_id', '=', $userId)->get();
        //Hacemos un foreach para crear un nuevo array asociativo con los permisos
        $arrayAccess = array(
            "userP" => 0,
            "clientP" => 0,
            "projectP" => 0,
            "assignP" => 0,
            "adminP" => 0,
            "closeP" => 0,
            "billingP" => 0,
        );
        if (!$userAccess->isEmpty()) {
            foreach ($userAccess as $permission) {
                switch ($permission->access_id) {
                        //usuarios
                    case 2:
                        $arrayAccess['userP'] = 1;
                        break;
                        //clientes
                    case 4:
                        $arrayAccess['clientP'] = 1;
                        break;
                        //control de proyects
                    case 6:
                        $arrayAccess['projectP'] = 1;
                        break;
                        //asign projects
                    case 7:
                        $arrayAccess['assignP'] = 1;
                        break;
                        //control admin hours
                    case 8:
                        $arrayAccess['adminP'] = 1;
                        break;
                        //Cierre de proyectos
                    case 13:
                        $arrayAccess['closeP'] = 1;
                        break;
                        //facturacion
                    case 10:
                        $arrayAccess['billingP'] = 1;
                        break;
                }
            }
            return $arrayAccess;
        }

        //Retornamos un valor vacio en caso de que no encuentre nada en la tabla
        return 0;
    }
}
