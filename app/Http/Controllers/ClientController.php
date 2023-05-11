<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\ConfigModel;
use App\Models\ClientModel;

class ClientController extends Controller
{
    //Propiedades
    protected $modelInstance;
    protected $permitControl;

    /**
     * Metodo inicial de la vista de clientes
     * @param mixed $request recibe la data de session del sistema
     */
    public function index(Request $request)
    {
        //Corroboraros que exista un usuario
        if (Session::has('idUsuario')) $this->permitControl = true;

        return view('index')->with("Session",$this->permitControl);
    }

    /**
     * Metodo que obtiene toda la data de clientes
     * @return Response Devuelve un objeto con la data de clientes
     */
    public function GetAllClients()
    {
        $this->modelInstance = new ConfigModel();
        $allData = $this->modelInstance->GetAll('clients');

        return response($allData,200);
    }

    /**
     * Metodo que hace llamado a todos los datos iniciales del cliente
     * @return Response Retorna un objeto con la data inicial de los clientes y status 200 si está todo correcto
     */
    public function GetInitData()
    {
        $paramsInit = [
            "dataSocio" => ClientModel::GetAllSocios(1),
            "dataSectores" => ClientModel::GetAllSectores(1),
            "dataServicios" => ClientModel::GetAllServicios(1),
            "dataPaises" => ClientModel::GetAllPaises(),
            "dataStatus" => ConfigModel::GetAllStatus('clientes')
        ];

        return response($paramsInit,200);
    }

    /**
     * Metodo que crea o actualiza un cliente
     * @param Request $dataClient Variable en formato request que recibe la data recibida por POST
     * @return Array Retorna un array en formato response
     */
    public function ClientControl(Request $dataClient)
    {
        $paramsEdit = array(); #Inicializacion de array de edición

        //Pasamos la data
        $paramsToControl = array(
            $dataClient->input('client')['IdSocio'],
            $dataClient->input('client')['Rif'],
            $dataClient->input('client')['Nit'] === null ? 0 : $dataClient->input('client')['Nit'],
            mb_strtoupper($dataClient->input('client')['RazonSocial']),
            $dataClient->input('client')['IdPais'],
            $dataClient->input('client')['Direccion'],
            strtolower($dataClient->input('client')['Telefono']),
            strtolower($dataClient->input('client')['PaginaWeb']),
            strtolower($dataClient->input('client')['EmailFiscal']),
            $dataClient->input('client')['IdSector'],
            $dataClient->input('client')['IdServicio'],
            Session::get('idUsuario'),
            ConfigController::GetIpUser()
        );

        $dataClient->input('isEdit')
        ? null
        : $ResponseClient = ClientModel::ControlClients($paramsToControl,'create');

        //Retornamos la data
        return response($ResponseClient,200);
    }
}
