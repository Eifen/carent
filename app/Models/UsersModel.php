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
        ->get(['AbreviaturaTipo','DescripcionTipo']);
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
     * Metodo que devuelve los municipios
     * @return Array info de los municipios disponibles
     */
    public static function GetMunicipality()
    {
        $getMunicipality = DB::table('tbl_usuarios_direccion_municipio')
        ->get(['Id','NombreMunicipio','Id_direccion_estado']);
        return $getMunicipality;
        //[0] = Id, [1] = NombreMunicipio
    }

    /**
     * Metodo que devuelve todas las parroquias
     * @return Array info de todas las parroquias disponibles
     */
    public static function GetParish()
    {
        $getParish = DB::table('tbl_usuarios_direccion_parroquia')
        ->get(['Id','NombreParroquia','Id_direccion_municipio']);
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
        ->get(['Id','NombreDivision']);
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
        ->get(['Id','NombreCargo']);
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
        $getUser = DB::table('tbl_usuarios')
        //Cargo y Division
        ->join("tbl_usuarios_jerarquia_cargo","tbl_usuarios.Id_jerarquia_cargo","=","tbl_usuarios_jerarquia_cargo.Id")
        ->join("tbl_usuarios_jerarquia_division","tbl_usuarios.Id_jerarquia_division","=","tbl_usuarios_jerarquia_division.Id")
        //Documento de identidad
        ->join("tbl_usuarios_documentoidentidad", "tbl_usuarios.Id", "=", "tbl_usuarios_documentoidentidad.Id_usuario")
        ->join("tbl_usuarios_documentoidentidad_tipo", "tbl_usuarios_documentoidentidad.Id_tipo_documento", "=", "tbl_usuarios_documentoidentidad_tipo.Id")
        //Correo y Numero
        ->join("tbl_usuarios_contacto", "tbl_usuarios.Id", "=", "tbl_usuarios_contacto.Id_usuario")
        //Dirección
        ->join("tbl_usuarios_direccion_parroquia","tbl_usuarios.Id_direccion_parroquia","=","tbl_usuarios_direccion_parroquia.Id")
        //Municipio
        ->join("tbl_usuarios_direccion_municipio","tbl_usuarios_direccion_parroquia.Id_direccion_municipio","=","tbl_usuarios_direccion_municipio.Id")
        //Municipio
        ->join("tbl_usuarios_direccion_estado","tbl_usuarios_direccion_municipio.Id_direccion_estado","=","tbl_usuarios_direccion_estado.Id")
        //Hacemos el select
        ->select("tbl_usuarios.Id","tbl_usuarios.Id_estatus as StatusId","tbl_usuarios.Codigo","tbl_usuarios.Primer_nombre","tbl_usuarios.Segundo_nombre","tbl_usuarios.Primer_Apellido","tbl_usuarios.Segundo_apellido","tbl_usuarios.Fecha_nacimiento","tbl_usuarios.Fecha_ingreso","tbl_usuarios.Fecha_egreso","tbl_usuarios_documentoidentidad_tipo.AbreviaturaTipo","tbl_usuarios_documentoidentidad.Descripcion as Cedula","tbl_usuarios_contacto.Correo_principal", "tbl_usuarios_contacto.Correo_secundario","tbl_usuarios_contacto.Telefono_principal","tbl_usuarios_contacto.Telefono_secundario","tbl_usuarios_direccion_parroquia.Id as ParroquiaId","tbl_usuarios_direccion_municipio.Id as MunicipioId","tbl_usuarios_direccion_estado.Id as EstadoId","tbl_usuarios_jerarquia_cargo.Id as CargoId","tbl_usuarios_jerarquia_division.Id as DivisionId")
        ->where("tbl_usuarios.Codigo","=",$codigo)
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
                DB::select('call sp_NewUser(?,?,?,?,?,?,?,?,?,?,?,?,?,@response)',$paramsUsers);
                break;

            case 'update':
                DB::select('call sp_UpdateUser(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@response)',$paramsUsers);
                break;
        }

        $getResponse = DB::select('SELECT @response as JsonResponse');
        $response1 = json_decode($getResponse[0]->JsonResponse,true);

        //Si creo o actualizó el usuario exitosamente, procedemos a registrar su contacto y documento o actualizarlo
        if($response1["response"])
        {
            switch ($typeControl) {
                case 'create':
                    DB::select('call sp_NewContactUser(?,?,?,?,?,?,?,@response2)',$paramsContact);
                    break;

                case 'update':
                    DB::select('call sp_UpdateContactUser(?,?,?,?,?,?,?,@response2)',$paramsContact);
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
