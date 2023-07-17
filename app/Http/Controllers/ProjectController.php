<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectModel;
use App\Models\ClientModel;
use App\Models\ConfigModel;
use DateInterval;
use DateTime;
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
     * Metodo que devuelve una vista de todos los proyectos asignados por el usuario actual
     */
    public function getAllAssignProject()
    {
        $this->modelInstance = new ConfigModel();
        $allAssignData = $this->modelInstance->GetAll('projects_assign');
        //Filtramos la información dependiendo del id del usuario. Primero convertimos la collection a un array
        $arrayMessageDTO = $allAssignData["message"]->toArray();
        $allAssignData["message"] = array_filter($arrayMessageDTO, function ($assignUser) {
            return $assignUser->gerente_asignado === Session::get('userId');
        });
        //Filtramos nuevamente, quitando la columnna gerente_asignado
        $allAssignData["message"] = array_map(function ($column) {
            //Desactivamos la propiedad gerente_asociado y retornamos
            unset($column->gerente_asignado);
            return $column;
        }, $allAssignData["message"]);

        //Reindexamos y convertimos nuevamente a una collección
        $allAssignData["message"] = collect(array_values($allAssignData["message"]));
        return response($allAssignData, 200);
    }

    /**
     * Metodo que abstrae todas las horas por cargar del usuario actual, además de los conceptos de horas administrativas
     */
    public function prepareRegisterHours()
    {
        return ProjectModel::getRegisterHoursInfo(Session::get('userId'));
    }

    /**
     * Metodo que recibe una solicitud a través de un parametro
     * @return Response retorna un objeto de respuesta donde data corresponde a la información de fechas asociada a horas cargadas
     */
    public function prepareLoadHoursPerId(Request $userInfoDate)
    {
        $getDateInfo = $userInfoDate->input('request');
        $prepareQuery = array(); //Array que se llenara luego de acomodar las fechas

        //Hacemos un ciclo para acomodar la información
        $getInitDate = new DateTime($getDateInfo["initDateRange"]); #Fecha de inicio almacenada
        $getEndDate = new DateTime($getDateInfo["finishDateRange"]); #Fecha de fin almacenada

        $dateCount = $getInitDate; #Variable que se encarga de alternar la fecha entre dos intervalos
        while ($dateCount <= $getEndDate) {
            $numberOfDay = intval(date('N', strtotime($dateCount->format('Y-m-d')))); #?

            //Mientras la fecha inicial no iguale a la final, agregara un nuevo valor al array
            array_push($prepareQuery, array(
                "user_id" => Session::get('userId'),
                "register_date" => $dateCount->format('Y-m-d'),
                "day_of_week" => $numberOfDay - 1
            ));

            $dateCount->add(new DateInterval('P1D'));
        }

        //Retornamos informacion de horas
        return response(ProjectModel::getLoadHoursPerId($prepareQuery), 200);
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
     * Metodo que hace un llamao al control para traer la data de un proyecto por su codigo
     * @param Request $projectUpdate almacena el objeto pasado por parametro POST a la solicitud
     * @return Response Retorna un objeto responde con status 200 si logra crear existosamente la sesión
     */
    public function projectPerCode(Request $projectUpdate)
    {
        //Creamos una instancia de sesión temporal
        Session::put("projectUpdate", ProjectModel::getProjectInfo($projectUpdate->input('codigoSQL')));
        return response("User Loaded", 200);
    }

    /**
     * Metodo que crea o actualiza un Proyecto
     * @param Request $dataProject Variable en formato request que recibe la data recibida por POST
     * @return Array Retorna un array en formato response
     */
    public function projectControl(Request $dataProject)
    {
        $paramsEdit = array(); #Inicializacion de array de edición

        //Fecha de ingreso null o vacia
        $hiringDate = $dataProject->input('project')['hiringDate'] != null || $dataProject->input('project')['hiringDate'] != ''
            ? date("Y-m-d", strtotime($dataProject->input('project')['hiringDate']))
            : null;
        //Pasamos la data
        $paramsToControl = array(
            "departments" => $dataProject->input('project')['departments'],
            "project" =>  array(
                $dataProject->input('project')['projectDescription'],
                $dataProject->input('project')['clientId'],
                $dataProject->input('project')['statusId'],
                $dataProject->input('project')['managerId'],
                $dataProject->input('project')['partnerId'],
                $dataProject->input('project')['qualityPartnerId'],
                $dataProject->input('project')['currencyId'],
                $dataProject->input('project')['companyId'],
                $hiringDate,
                $dataProject->input('project')['projectValue'],
                $dataProject->input('project')['averageRate'],
                Session::get('userId'),
                ConfigController::GetIpUser()
            )
        );

        if ($dataProject->input('isEdit')) {
            $paramsEdit = array(
                "projectId" => $dataProject->input('project')['projectId'],
                "additionalHours" => $dataProject->input('project')['additionalHours'],
                "additionalValues" => $dataProject->input('project')['additionalValues']
            );

            //Unimos ambos array
            $paramsToControl = array_merge($paramsToControl, $paramsEdit);
        }

        $dataProject->input('isEdit')
            ? $ResponseProject = ProjectModel::controlProjects($paramsToControl, 'update')
            : $ResponseProject = ProjectModel::controlProjects($paramsToControl, 'create');

        //Retornamos la data
        return response($ResponseProject, 200);
    }

    /** Metodo que elimina la sesión temporal de clientUpdate */
    public function deleteProjectUpdate()
    {
        if (Session::has('projectUpdate')) Session::forget('projectUpdate');
    }

    /** Metodo que retorna la Session['usersAssign'] al request y luego elimina la session */
    public function reAssignUsers()
    {
        if (Session::has('usersAssign')) {
            $getSession = Session::get('usersAssign');
            //Eliminamos la session
            Session::forget('usersAssign');
            return response($getSession, 200);
        };
    }

    /**
     * Metodo que devuelve a la vista los usuarios por departamento
     * @param Request $assignProject Captura los parametros enviados mediante la solicitud
     * @return Response Retorna la información en formato request proxy
     */
    public function usersPerDepartment(Request $assignProject)
    {
        Session::put('usersAssign', ProjectModel::getUserPerDepartment($assignProject->input('department')));
        return response("Success", 200);
    }

    /**
     * Metodo que actualiza los valores en la asignación de proyectos
     * @param Request $assignUpdate se encarga de leer los parametros enviados por el request
     * @return Response Retorna un mensaje de exito o fallo respectivamente
     */
    public function updateAsign(Request $assignUpdate)
    {
        $getHoursUsers = $assignUpdate->input('infoAsign');
        $getDepartmentAssignId = $assignUpdate->input('departmentAssignedId');

        return response(ProjectModel::updateAsignHours($getHoursUsers, $getDepartmentAssignId), 200);
    }

    /**
     * Metodo que devuelve la información preeliminar del proyecto
     * @param Request $projectRequest Almacena los parametros enviados por axios en ProjectIndex.js
     */
    public function prepareInfoProject(Request $projectRequest)
    {
        $projectId = $projectRequest->input("project_id");
        return response(ProjectModel::getProjectInfo($projectId));
    }

    public function sessionCloseProjects(Request $closeProject)
    {
        //EL primer parametro de Axio es la ruta y la segunda un objeto json, La propiedad del input se almacena en$projectCloseId
        $projectCloseId = $closeProject->input("project_id");
        Session::put(
            'closeProject',
            ProjectModel::getProjectInfo($projectCloseId)
        );
        return response('Load Completed', 200);
    }
}
