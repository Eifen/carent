<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsersModel extends Model
{
    /**
     * Metodo que devuelve un array con los tipos de documento
     * @return Array info tipo de documento de identidad
     */
    public static function TypeDocument()
    {
        $getType = DB::table('users_identity_type')
        ->get(['identity_abbreviation','identity_description']);
        return $getType;
        //[0] = Venezolana, [1] = Extranjera
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
        ->get(['municipality_id','municipality_name','state_id']);
        return $getMunicipality;
    }

    /**
     * Metodo que devuelve todas las parroquias
     * @return Array info de todas las parroquias disponibles
     */
    public static function GetParish()
    {
        $getParish = DB::table('users_address_parishes')
        ->get(['parish_id','parish_name','municipality_id']);
        return $getParish;
    }

    /**
     * Metodo que devuelve todas las divisiones
     * @return Array info de las divisiones
     */
    public static function GetAllDivision()
    {
        $getDivision = DB::table(('users_hierarchy_departments'))
        ->where("status_id","=",1)
        ->get(['department_id','department_name']);
        return $getDivision;
    }

    /**
     * Metodo que devuelve todas los cargos
     * @return Array info de los cargos
     */
    public static function GetAllCargo()
    {
        $getCargo = DB::table(('users_hierarchy_positions'))
        ->where("status_id","=",1)
        ->get(['position_id','position_name']);
        return $getCargo;
        //[0] = Id, [1] = Descripcion
    }

    /**
     * Metodo que devuelve los datos del usuario
     * @param Int $codigo: Almacena el codigo del usuario
     * @return Array devuelve un array de referencias data["info1"] con la información del usuario
     */
    public static function PrepareDataUpdate($codigo)
    {
        $getUser = DB::table('vw_users_update')
        ->where("user_code","=",$codigo)
        ->first();

        return $getUser;
    }

    /**
     * Método que registra al usuario en función a los parametros proporcionados
     * @param mixed $paramsUsers Almacena los datos para la creación de usuario y registrar el movimiento
     * @param mixed $paramsContact Almacena los datos para los documentos del usuario recién creado
     * @param mixed $typeControl Define si hará un create o un update
     */
    public static function ControlUser($paramsUsers,$paramsContact,$typeControl)
    {
        switch ($typeControl) {
            case 'create':
                DB::select('call sp_new_users(?,?,?,?,?,?,?,?,?,?,?,?,?,@response)',$paramsUsers);
                break;

            case 'update':
                DB::select('call sp_update_users(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@response)',$paramsUsers);
                break;
        }

        $getResponse = DB::select('SELECT @response as JsonResponse');
        $response1 = json_decode($getResponse[0]->JsonResponse,true);

        //Si creo o actualizó el usuario exitosamente, procedemos a registrar su contacto y documento o actualizarlo
        if($response1["response"])
        {
            switch ($typeControl) {
                case 'create':
                    DB::select('call sp_new_contact_users(?,?,?,?,?,?,?,@response2)',$paramsContact);
                    break;

                case 'update':
                    DB::select('call sp_update_contact_users(?,?,?,?,?,?,?,@response2)',$paramsContact);
                    break;
            }

            $getResponse2 = DB::select('SELECT @response2 as JsonResponse2');
            $response2 = json_decode($getResponse2[0]->JsonResponse2,true);

            //Si registro el contacto, devuelve la primera respuesta
            if($response2["response"])
            {
                return $response1;
            }

            return $response2;
        }

        return $response1;
    }
}
