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

    /**
     * Metodo que sincroniza la data inicial
     * @return Response Object con las listas en el formulario
     */
    public function GetInitData()
    {
        $dataInit = [
            "tiposDocumento" => UsersModel::TypeDocument(1),
            "statesUsuario" =>  UsersModel::GetAllState(),
            "divisiones" => UsersModel::GetAllDivision(),
            "cargos" => UsersModel::GetAllCargo(),
            "statusUsuario" => ConfigModel::GetAllStatus('usuarios'),
            "municipalityUsuario" => UsersModel::GetMunicipality(),
            "parishUsuario" => UsersModel::GetParish()
        ];

        return response($dataInit,200);
    }

    //Registro de nuevo Usuarios
    public function UserControl(Request $dataUser)
    {
        $decryptCode = ConfigController::DecryptData($dataUser->input('user')['Code']);
        $paramsEdit = array(); //Array para la función de edit

        //Tercer nivel de validaciones

        //Fecha de ingreso null o vacia
        $fechaIngreso = $dataUser->input('user')['DateIngreso'] != null || $dataUser->input('user')['DateIngreso'] != ''
                        ? date("Y-m-d",strtotime($dataUser->input('user')['DateIngreso']))
                        : null;

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

        //Fecha de egreso null o vacia si se está editando
        if($dataUser->input('isEdit'))
        {
            $fechaEgreso = $dataUser->input('user')['DateEgreso'] != null || $dataUser->input('user')['DateEgreso'] != ''
            ? date("Y-m-d",strtotime($dataUser->input('user')['DateEgreso']))
            : null;

            $paramsEdit = array(
                $dataUser->input('user')['IdUser'],
                $dataUser->input('user')['IdStatus'],
                $fechaEgreso
            );

            //Colocamos el status y la fecha de egreso al final del array
            $paramsUser = array_merge($paramsUser,$paramsEdit);
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
        ? ($ResponseUser = UsersModel::ControlUser($paramsUser,$paramsContact,"update"))
        : $ResponseUser = UsersModel::ControlUser($paramsUser,$paramsContact,"create");

        //Eliminamos el session update en caso de que exista.
        if(Session::has('dataUpdate') && $ResponseUser['response']) Session::forget("dataUpdate");

        return response($ResponseUser,200);
    }

    /**
     * Metodo que borra la session dela data Update
     */
    public function DeleteDataUpdate(){ if(Session::has('dataUpdate')) Session::forget("dataUpdate"); }
}
