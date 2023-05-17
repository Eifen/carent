<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClientModel extends Model
{
    /**
     * Metodo que devuelve todos los socios (Usuarios) con solo dos campos: Id, Nombre
     * @param int $Status Define el tipo de estatus que debe tener el usuario para la extracción
     */
    static public function GetAllSocios($Status)
    {
        return DB::table('users')
        ->whereIn('position_id',[16,17])
        ->where('status_id','=',$Status)
        ->get(['user_id','first_name','second_name','first_surname','second_surname']);
    }

    /**
     * Metodo que devuelve todos los sectores activos. Campo Id y Nombre_sector
     * @param int $Status Captura el tipo de estatus
     * @return Array Retorna un array de todos los sectores
     */
    static public function GetAllSectores($Status){
        return DB::table('clients_sectors')
        ->where('status_id',$Status)
        ->get(['sector_id','sector_name']);
    }

    /**
     * Metodo que devuelve todos los servicios activos. Campo Id y Nombre_servicio
     * @param int $Status Captura el tipo de estatus
     * @return Array Retorna un array de todos los servicios
     */
    static public function GetAllServicios($Status){
        return DB::table('clients_services')
        ->where('status_id',$Status)
        ->get(['service_id','service_name']);
    }

    /**
     * Metodo sin parametros que devuelve todos los paises registrados
     * @return Array Retorna un array de todos los paises
     */
    static public function GetAllPaises()
    {
        return DB::table('clients_countries')->get();
    }

    /**
     * Metodo que obtiene la data de un cliente por su codigo
     * @param int $codeClient Recibe el codigo para usar como filtro
     * @return Array retorna un array asociativo con la información del cliente
     */
    static public function GetClientsPerCode($codeClient){
        return DB::table('clients')
        ->where('client_code','=',$codeClient)
        ->first();
    }

    /**
     * Metodo que crea o actualiza un cliente
     * @param Array $dataRequest Los parametros a ejecutar en el procedure
     * @param String $typeControl Tipo de operacion = create, o update
     * @return Array Retorna un array response con la estrucutra {response:boolean,message:string|object}
     */
    static public function ControlClients($dataRequest, $typeControl)
    {
        //Separamos el tipo de proceso
        switch ($typeControl) {
            case 'create':
                DB::select('call sp_new_clients(?,?,?,?,?,?,?,?,?,?,?,?,?,@response)',$dataRequest);
                break;
            case 'update':
                DB::select('call sp_update_clients(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@response)',$dataRequest);
                break;
        }

        //Capturamos el JSON
        $GetResponse = DB::select('SELECT @response AS JsonResponse');
        $response = json_decode($GetResponse[0]->JsonResponse,true);

        return $response;
    }
}
