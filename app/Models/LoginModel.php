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
        $initEncrypt = DB::table('control_encrypts')->where('status_id','=',1)->get(['encrypt_key','encrypt_iv']);
        $getKey = $initEncrypt[0]->encrypt_key;
        $getIv = $initEncrypt[0]->encrypt_iv;

        return array("key" => $getKey, "iv" => $getIv);
    }
}
