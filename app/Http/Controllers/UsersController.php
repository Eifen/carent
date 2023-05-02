<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsersModel;
use App\Models\ConfigModel;
use App\Http\Controllers\ConfigController;
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
        if($request->session()->has('idUsuario')){ $this->permitControl = true; }

        return view('index')->with('Session',$this->permitControl);
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
        return response($allData,200);
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
        Session::put("dataUpdate",$user);

        return response('User loaded',200);
    }

    //Municipios en funcion del Estado
    public function GetMunicipality(Request $getIdState)
    {
        $getMunicipality = UsersModel::GetMunicipalityById($getIdState->input('IdState'));
        //Retornamos la data
        return response($getMunicipality,200);
    }

    //Parroquias en funcion del Municipio
    public function GetParish(Request $getIdMunicipality)
    {
        $getParish = UsersModel::GetParishById($getIdMunicipality->input('IdMunicipio'));
        //retornamos la data
        return response($getParish,200);
    }

    /**
     * Metodo que sincroniza la data inicial
     * @return Response Object con las listas en el formulario
     */
    public function GetInitData()
    {
        $dataInit = [
            "TiposDocumento" => UsersModel::TypeDocument(1),
            "StatesUsuario" =>  UsersModel::GetAllState(),
            "Divisiones" => UsersModel::GetAllDivision(),
            "Cargos" => UsersModel::GetAllCargo(),
            "StatusUsuario" => ConfigModel::GetAllStatus('usuarios')
        ];

        return response($dataInit,200);
    }

    //Registro de nuevo Usuarios
    public function NewUser(Request $dataUser)
    {
        $decryptCode = ConfigController::DecryptData($dataUser->input('user')['Code']);

        //Tercer nivel de validaciones

        //Fecha de ingreso null o vacia
        $fechaIngreso = $dataUser->input('user')['DateIngreso'] != null || $dataUser->input('user')['DateIngreso'] != ''
                        ? date("Y-m-d",strtotime($dataUser->input('user')['DateIngreso']))
                        : date("Y-m-d");

        $paramsUser = array(
            mb_strtoupper($dataUser->input('user')['FirstName']),
            mb_strtoupper($dataUser->input('user')['SecondName']),
            mb_strtoupper($dataUser->input('user')['LastName']),
            mb_strtoupper($dataUser->input('user')['SecondLastName']),
            $decryptCode,
            date("Y-m-d",strtotime($dataUser->input('user')['Birthday'])),
            $fechaIngreso,
            $dataUser->input('user')['Cedula'],
            $dataUser->input('user')['IdParish'],
            $dataUser->input('user')['IdCargo'],
            $dataUser->input('user')['IdDivision'],
            Session::get('idUsuario'),
            ConfigController::GetIpUser()
        );

        $paramsContact = array(
            $decryptCode,
            $dataUser->input('contact')["FirstEmail"],
            $dataUser->input('contact')["SecondEmail"],
            $dataUser->input('contact')["FirstPhone"],
            $dataUser->input('contact')["SecondPhone"],
            $dataUser->input('document')["TipoCedula"],
            $dataUser->input('document')["Cedula"],
        );

        $RegisterUser = UsersModel::CreateUser($paramsUser,$paramsContact);

        return response($RegisterUser,200);
    }

    //Actualización de usuarios
}
