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
    public static function TypeDocument($estatus)
    {
        $getType = DB::table('tbl_usuarios_documentoidentidad_tipo')
        ->where('Id_estatus',$estatus)
        ->get(['Abreviatura','Descripcion']);
        return $getType;
        //[0] = Venezolana, [1] = Extranjera
    }

    /**
     * Metodo que devuelve un array con todos los estados del pais registrados
     * @return Array info del estado
     */
    public static function GetAllState()
    {
        $getState = DB::table('tbl_usuarios_direccion_estado')->get();
        return $getState;
        //[0]= Id, [1]=NombreEstado, [2]=Iso3166-2
    }

    /**
     * Metodo que devuelve los municipios asociados a un estado
     * @return Array info de los municipios disponibles
     */
    public static function GetMunicipalityById($Id)
    {
        $getMunicipality = DB::table('tbl_usuarios_direccion_municipio')
        ->where('Id_direccion_estado',$Id)
        ->get(['Id','NombreMunicipio']);
        return $getMunicipality;
        //[0] = Id, [1] = NombreMunicipio
    }

    /**
     * Metodo que devuelve todas las parroquias asociadas al municipio
     * @return Array info de todas las parroquias disponibles
     */
    public static function GetParishById($Id)
    {
        $getParish = DB::table('tbl_usuarios_direccion_parroquia')
        ->where('Id_direccion_municipio',$Id)
        ->get(['Id','NombreParroquia']);
        return $getParish;
        //[0] = Id, [1] = NombreParroquia
    }

    /**
     * Metodo que devuelve todas las divisiones
     * @return Array info de las divisiones
     */
    public static function GetAllDivision()
    {
        $getDivision = DB::table(('tbl_usuarios_jerarquia_division'))
        ->get(['Id','Descripcion']);
        return $getDivision;
        //[0] = Id, [1] = Descripcion
    }

    /**
     * Metodo que devuelve todas los cargos
     * @return Array info de los cargos
     */
    public static function GetAllCargo()
    {
        $getCargo = DB::table(('tbl_usuarios_jerarquia_cargo'))
        ->get(['Id','Descripcion']);
        return $getCargo;
        //[0] = Id, [1] = Descripcion
    }

    /**
     * Método que registra al usuario en función a los parametros proporcionados
     * @param mixed $ParamsCreate Almacena los datos para la creación de usuario y registrar el movimiento
     * @param mixed $ParamsContact Almacena los datos para los documentos del usuario recién creado
     */
    public static function CreateUser($ParamsCreate,$ParamsContact)
    {
        DB::select('call sp_NewUser(?,?,?,?,?,?,?,?,?,?,?,?,?,@response)',$ParamsCreate);
        $GetResponse = DB::select('SELECT @response as JsonResponse');
        $Response1 = json_decode($GetResponse[0]->JsonResponse,true);

        //Si creo el usuario exitosamente, procedemos a registrar su contacto y documento
        if($Response1["response"])
        {
            DB::select('call sp_NewContactUser(?,?,?,?,?,?,?,@response2)',$ParamsContact);
            $GetResponse2 = DB::select('SELECT @response2 as JsonResponse2');
            $Response2 = json_decode($GetResponse2[0]->JsonResponse2,true);

            //Si registro el contacto, devuelve la primera respuesta
            if($Response2["response"])
            {
                return $Response1;
            }

            return $Response2;
        }

        return $Response1;
    }
}
