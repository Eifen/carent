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
            "dataSocio" => ClientModel::GetAllSocios(1)
        ];

        return response($paramsInit,200);
    }
}
