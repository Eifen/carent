<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectModel;
use App\Models\ClientModel;
use App\Models\ConfigModel;
use Illuminate\Support\Facades\Session;

class ProjectController extends Controller
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
        if (Session::has('userId')) $this->permitControl = true;

        return view('index')->with("Session", $this->permitControl);
    }

    /**
     * Metodo que se encarga de devolver una vista de los proyectos activos e inactivos
     */
    public function getAllProjects()
    {
        $this->modelInstance = new ConfigModel();
        $allData = $this->modelInstance->GetAll('projects');
        return response($allData, 200);
    }

    /**
     * Metodo que se encarga se llenar las listas del formulario de proyectos
     * @return Response Retorna un formato JSON con informacion de las listas
     */
    public function getInitData()
    {
        $projectParams = [
            "currencies" => ConfigModel::getAllDataStatusControl('control_currencies'),
            "companies" => ConfigModel::getAllDataStatusControl('control_companies'),
            "departments" => ConfigModel::getAllDataStatusControl('users_hierarchy_departments'),
            "clients" => ConfigModel::getAllDataStatusControl('clients'),
            "partners" => ProjectModel::getAllAssociated(1, [16, 17]),
            "managers" => ProjectModel::getAllAssociated(1, [12, 13, 14, 15, 16, 17]),
            "status" => ConfigModel::GetAllStatus()
        ];

        return response($projectParams, 200);
    }
}
