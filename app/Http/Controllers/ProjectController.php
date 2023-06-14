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

    /**
     * Metodo que crea o actualiza un cliente
     * @param Request $dataProject Variable en formato request que recibe la data recibida por POST
     * @return Array Retorna un array en formato response
     */
    public function projectControl(Request $dataProject)
    {
        $paramsEdit = array(); #Inicializacion de array de edición

        //Fecha de ingreso null o vacia
        $hiringDate = $dataProject->input('project')['hiringDate'] != null || $dataProject->input('project')['hiringDate'] != ''
                        ? date("Y-m-d",strtotime($dataProject->input('project')['hiringDate']))
                        : null;
        //Pasamos la data
        $paramsToControl = array(
            $dataProject->input('project')['projectDescription'],
            $dataProject->input('project')['clientId'],
            $dataProject->input('project')['statusId'],
            $dataProject->input('project')['managerId'],
            $dataProject->input('project')['partnerId'],
            $dataProject->input('project')['qualityPartnerId'],
            $dataProject->input('project')['currencyId'],
            $dataProject->input('project')['companyId'],
            $hiringDate,
            $dataProject->input('project')['departments'],
            $dataProject->input('project')['projectValue'],
            Session::get('userId'),
            ConfigController::GetIpUser()
        );

        if($dataProject->input('isEdit'))
        {
            $paramsEdit = array(
                $dataProject->input('project')['IdClient'],
                $dataProject->input('project')['IdStatus']
            );

            //Unimos ambos array
            $paramsToControl = array_merge($paramsToControl,$paramsEdit);
        }

        $dataProject->input('isEdit')
        ? $ResponseProject = ProjectModel::controlProjects($paramsToControl,'update')
        : $ResponseProject = ProjectModel::controlProjects($paramsToControl,'create');

        //Retornamos la data
        return response($ResponseProject,200);
    }
}
