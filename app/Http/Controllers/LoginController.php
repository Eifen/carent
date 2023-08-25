<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoginModel;
use App\Http\Controllers\ConfigController;
use App\Models\ConfigModel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    protected $validacion = false;
    //Inicializar aplicación
    public function index(Request $request)
    {

        $LoginInstance = new LoginModel();
        $encryptData = $LoginInstance->GetEncryptKey();
        //Enviamos la data dependiendo del estado de la sesión
        if ($request->session()->has('userId')) {
            $this->validacion = true;
        }

        //Traemos el KEY y el IV de la base de datos y lo asignamos a una instancia de Session
        $request->session()->put('encrypt-key', $encryptData["key"]);
        $request->session()->put('encrypt-iv', $encryptData["iv"]);

        return view('index')->with('Session', $this->validacion);
    }

    /**
     * Metodo que controla el inicio de sesión de usuario
     */
    public function Login(Request $request)
    {
        $LoginInstance = new LoginModel();

        $ParamsSession =
            [
                ConfigController::DecryptData($request->input('Codigo')),
                ConfigController::DecryptData($request->input('Clave')),
                ConfigController::GetIpUser()
            ];

        $Login = $LoginInstance->VerificarLogin($ParamsSession);

        if ($Login['response']) {
            Session::put('userId', $Login['userId']);
            Session::put('userName', strtolower($Login['name']));
            Session::put('positionId', $Login['positionId']);
            Session::put('departmentId', $Login['departmentId']);
            Session::put('emailUser', $Login['emailUser']);
            Session::put('passwordChange', $Login['passwordChange']);
            #Llenamos el array de acceso
            $prepareAccess = LoginModel::getArrayAccess($Login['userId']);
            if ($prepareAccess != 0) {
                Session::put('userPermissions', $prepareAccess);
            }
        }

        //Retornamos la dato con un status de 200
        return response(array("response" => $Login['response'], "message" => $Login['message']), 200);
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

    /**
     * Metodo que se encarga de recuperar una contra
     * @param Request $recoveryRequest almacena la informacion de la peticion HTTP axios de login.js
     */
    public function recovery(Request $recoveryRequest)
    {
        $prepareParams = array(
            $recoveryRequest->input('userCode'),
            ConfigController::GetIpUser()
        );

        //Llamamos a la funcion de recuperar password
        $recoveryPassword = ConfigModel::recoveryPassword($prepareParams);

        if ($recoveryPassword["response"]) {
            $emailUser = $recoveryPassword["correo"];
            Mail::send('emailTemplate.recoveryPassword', ["clave" => $recoveryPassword["clave"]], function ($message) use ($emailUser) {
                $message->from('sistema.carent@crowe.com.ve', 'CARENT')->to($emailUser)->subject('Recuperación de Contraseña');
            });
        }

        return response($recoveryPassword, 200);
    }
}
