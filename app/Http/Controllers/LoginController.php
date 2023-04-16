<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoginModel;
use App\Http\Controllers\ConfigController;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    protected $tupleValidacion = false;
    //Inicializar aplicación
    public function index(Request $request){

        $LoginInstance = new LoginModel();
        $encryptData = $LoginInstance->GetEncryptKey();
        //Enviamos la data dependiendo del estado de la sesión
        if($request->session()->has('idUsuario')){ $this->tupleValidacion = true; }

        //Traemos el KEY y el IV de la base de datos y lo asignamos a una instancia de Session
        $request->session()->put('encrypt-key', $encryptData["key"]);
        $request->session()->put('encrypt-iv', $encryptData["iv"]);

        return view('index')
        ->with('Session',$this->tupleValidacion)
        ->with('DataSession', $request);
    }

    /**
     * Metodo que controla el inicio de sesión de usuario
     */
    public function Login(Request $request){
        $LoginInstance = new LoginModel();

        $ParamsSession =
        [
            ConfigController::DecryptData($request->input('Codigo')),
            ConfigController::DecryptData($request->input('Clave')),
            ConfigController::GetIpUser()
        ];

        $Login = $LoginInstance->VerificarLogin($ParamsSession);

        if($Login['response'])
        {
            Session::put('idUsuario',$Login['idUsuario']);
            Session::put('idCargo',$Login['idCargo']);
            Session::put('idDivision',$Login['idDivision']);
            Session::put('emailUser',$Login['emailUser']);
            Session::put('passwordChange',$Login['passwordChange']);
        }

        //Retornamos la dato con un status de 200
        return response(array("response" => $Login['response'], "message" => $Login['message']),200);
    }

    /**
     * Metodo que cierra la sesión, eliminando la data en el Session
     * @param $SessionLogout Almacena los datos de la sesion en el Request
     * @return void Redirecciona a la pagina principal
     */
    public function Logout(Request $SessionLogout)
    {
        $SessionLogout->session()->flush();
        return redirect('/');
    }
}
