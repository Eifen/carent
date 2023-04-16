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

    public function index(){
        $this->modelInstance = new ConfigModel();
        $allData = $this->modelInstance->GetAll('users');

        //Retornamos toda la data
        return response($allData,200);
    }

    //Documento de identidad
    public function GetTypeDocument()
    {
        $getType = UsersModel::TypeDocument(1);
        //Retornamos la data
        return response($getType,200);
    }

    //Estados del Pais
    public function GetEstado()
    {
        $getState = UsersModel::GetAllState();
        //Retornamos la data
        return response($getState,200);
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

    //Todas las divisiones
    public function GetDivision()
    {
        $getDivision = UsersModel::GetAllDivision();
        //retornamos la data
        return response($getDivision,200);
    }

    //Todos los cargos
    public function GetCargo()
    {
        $getCargo = UsersModel::GetAllCargo();
        //retornamos la data
        return response($getCargo,200);
    }

    //Registro de nuevo Usuarios
    public function NewUser(Request $dataUser)
    {
        $decryptCode = ConfigController::DecryptData($dataUser->input('user')['Code']);
        $fechaIngreso = $dataUser->input('user')['DateIngreso'] != null || $dataUser->input('user')['DateIngreso'] != ''
                        ? date("Y-m-d",strtotime($dataUser->input('user')['DateIngreso']))
                        : $dataUser->input('user')['DateIngreso'];

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
}
