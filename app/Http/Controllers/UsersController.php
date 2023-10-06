<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsersModel;
use App\Models\ConfigModel;
use App\Http\Controllers\ConfigController;
use App\Models\HoursModel;
use App\Models\ReportsModel;
use Illuminate\Support\Facades\Session;

class UsersController extends Controller
{
    protected $modelInstance;
    protected $permitControl = false;

    /**
     * Metodo inicial de carga de pantalla de usuarios
     * @param Request Almacena los datos de la sesion y permisos de acceder a la vista principal
     */
    public function index(Request $request)
    {
        //Enviamos la data dependiendo del estado de la sesión
        if ($request->session()->has('userId')) {
            $this->permitControl = true;
        }

        $catchStatusMaintenance = ConfigModel::checkMaintenance();

        return view('index')
            ->with('Session', $this->permitControl)
            ->with('Maintenance', $catchStatusMaintenance);
    }

    /**
     * Metodo que extrae todos los usuarios
     * @return Response devuelve objeto response con la data resultante
     */
    public function GetAllUser()
    {
        $this->modelInstance = new ConfigModel();
        $allData = $this->modelInstance->GetAll('users');

        //Retornamos toda la data
        return response($allData, 200);
    }

    /**
     * Metodo que busca un usuario por el Code
     * @param Request Obtiene un array de los datos provenientes de la vista a través de un objeto JSON
     * @return Response Retorna los datos del usuario a excepción de la clave
     */
    public function UserPerCode(Request $dataUser)
    {
        //Pasamos el codigo para obtener todos los datos y luego lo almacenamos en un sessión
        $user = UsersModel::PrepareDataUpdate($dataUser->input("codigoSQL"));

        //Convertidos la data a un json
        Session::put("dataUpdate", $user);

        return response('User loaded', 200);
    }

    /**
     * Metodo que sincroniza la data inicial
     * @return Response Object con las listas en el formulario
     */
    public function GetInitData()
    {
        $dataInit = [
            "tiposDocumento" => UsersModel::TypeDocument(),
            "statesUsuario" =>  UsersModel::GetAllState(),
            "divisiones" => ConfigModel::getAllDataStatusControl('users_hierarchy_departments'),
            "cargos" => ConfigModel::getAllDataStatusControl('users_hierarchy_positions'),
            "statusUsuario" => ConfigModel::GetAllStatus('usuarios'),
            "municipalityUsuario" => UsersModel::GetMunicipality(),
            "parishUsuario" => UsersModel::GetParish()
        ];

        return response($dataInit, 200);
    }

    //Registro de nuevo Usuarios
    public function UserControl(Request $dataUser)
    {
        $decryptCode = ConfigController::DecryptData($dataUser->input('user')['Code']);
        $paramsEdit = array(); //Array para la función de edit

        //Tercer nivel de validaciones

        //Fecha de ingreso null o vacia
        $fechaIngreso = $dataUser->input('user')['DateIngreso'] != null || $dataUser->input('user')['DateIngreso'] != ''
            ? date("Y-m-d", strtotime($dataUser->input('user')['DateIngreso']))
            : null;

        $paramsUser = array(
            mb_strtoupper($dataUser->input('user')['FirstName']),
            mb_strtoupper($dataUser->input('user')['SecondName']),
            mb_strtoupper($dataUser->input('user')['LastName']),
            mb_strtoupper($dataUser->input('user')['SecondLastName']),
            $decryptCode,
            date("Y-m-d", strtotime($dataUser->input('user')['Birthday'])),
            $fechaIngreso,
            $dataUser->input('user')['Cedula'],
            $dataUser->input('user')['IdParish'],
            $dataUser->input('user')['IdCargo'],
            $dataUser->input('user')['IdDivision'],
            Session::get('userId'),
            ConfigController::GetIpUser()
        );

        //Fecha de egreso null o vacia si se está editando
        if ($dataUser->input('isEdit')) {
            $fechaEgreso = !empty($dataUser->input('user')['DateEgreso'])
                ? date("Y-m-d", strtotime($dataUser->input('user')['DateEgreso']))
                : null;

            $paramsEdit = array(
                $dataUser->input('user')['IdUser'],
                $dataUser->input('user')['IdStatus'],
                $fechaEgreso
            );

            //Colocamos el status y la fecha de egreso al final del array
            $paramsUser = array_merge($paramsUser, $paramsEdit);

            //Si inactivamos el usuario, tambien debemos quitarle las horas
            if ($dataUser->input('user')['IdStatus'] == 5 || $dataUser->input('user')['IdStatus'] == 2) {
                $prepareRemove = HoursModel::getProjectHours($dataUser->input('user')['IdUser']);
                HoursController::removeHoursInactiveUser($prepareRemove);
            }
        }

        $paramsContact = array(
            $decryptCode,
            strtolower($dataUser->input('contact')["FirstEmail"]),
            strtolower($dataUser->input('contact')["SecondEmail"]),
            strtolower($dataUser->input('contact')["FirstPhone"]),
            strtolower($dataUser->input('contact')["SecondPhone"]),
            strtolower($dataUser->input('document')["TipoCedula"]),
            strtolower($dataUser->input('document')["Cedula"]),
        );

        $dataUser->input('isEdit')
            ? ($ResponseUser = UsersModel::ControlUser($paramsUser, $paramsContact, "update"))
            : $ResponseUser = UsersModel::ControlUser($paramsUser, $paramsContact, "create");

        //Eliminamos el session update en caso de que exista.
        if (Session::has('dataUpdate') && $ResponseUser['response']) Session::forget("dataUpdate");

        return response($ResponseUser, 200);
    }

    /**
     * Metodo que borra la session dela data Update
     */
    public function DeleteDataUpdate()
    {
        if (Session::has('dataUpdate')) {
            $getSession = json_encode(Session::get('dataUpdate'));
            //Borramos la sesion
            Session::forget("dataUpdate");
            //retornamos
            return response($getSession, 200);
        } else {
            return response(0, 200);
        }
    }

    /**
     * Metodo que devuelve los permisos que posee el usuario
     */
    public function previewAccessUser(Request $userPreview)
    {
        $userCode = $userPreview->input('user_code');
        return response(UsersModel::getAccessInfo($userCode), 200);
    }

    /**
     * Metodo que actualiza la informacion de acceso del usuario
     */
    public function updateAccessUser(Request $userAccess)
    {
        $responseAccess = UsersModel::updateAccess($userAccess->input('user_code'), $userAccess->input('user_access'));
        return response($responseAccess, 200);
    }

    /**
     * Metodo que devuelve la informacion de carga para al usuario conectado en el mes actual.
     */
    public function getLogUser()
    {
        $dateStart = date("Y-m-01");
        $dateEnd = date("Y-m-t");
        $intervalDays = intval(ReportsModel::getTotalDays($dateStart, $dateEnd)) * 8;
        $getAreaType = 0; #Si es 0 indica que el departamento de la persona es de administracion, 1 es de auditoria
        $getPositionNivel = 0; #Si es 0 indica que al cargo actual no se le aplica la cargabilidad

        //Revisamos el tipo de area
        if (Session::get('departmentId') <= 7 && Session::get('departmentId') > 0 || Session::get('departmentId') == 17) {
            $getAreaType = 1;
            $allInfo = UsersModel::getInfoUsers();
            foreach ($allInfo as $userInfo) {
                # Si coinciden el usuario, almacena su nivel de carga
                if ($userInfo->user_id == Session::get('userId')) {
                    $getPositionNivel = intval($userInfo->nivel_percen);
                }
            }
        }
        $params = array(
            $dateStart,
            $dateEnd,
            Session::get("userId")
        );

        //Configuramos las horas administrativas
        $paramsAdmin = $params;
        array_push($paramsAdmin, 1);

        //Configuramos las horas a proyectos
        $paramsProj = $params;
        array_push($paramsProj, 2);

        $getAdminHours = ReportsModel::getRegisterHours($paramsAdmin);
        $getProyHours = ReportsModel::getRegisterHours($paramsProj);


        $responseArray = array(
            "month" => date("Y-m"),
            "estimated_hour" => $intervalDays,
            "estimated_proy" => $getAreaType == 0 ? 0 : (($intervalDays * $getPositionNivel) / 100),
            "estimated_admon" => $getAreaType == 0 ? $intervalDays : (($intervalDays * (100 - $getPositionNivel)) / 100),
            "real_proy" => $getProyHours,
            "real_admon" => $getAdminHours
        );
        return response(array(
            "response" => true,
            "message" => $responseArray
        ), 200);
    }
}
