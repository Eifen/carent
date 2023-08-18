<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfigModel;
use App\Models\UsersModel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;

class ConfigController extends Controller
{
    protected $permitControl = false;
    /**
     * Metodo inicial de carga de cambio de contrasena o olvide la clave
     * @param Request Almacena los datos de la sesion y permisos de acceder a la vista principal
     */
    public function index(Request $request)
    {
        //Enviamos la data dependiendo del estado de la sesión
        if ($request->session()->has('userId')) {
            $this->permitControl = true;
        }

        return view('index')->with('Session', $this->permitControl);
    }

    public function LimitPag(Request $dataLimit)
    {
        $configInstance = new ConfigModel();
        $tableTarget = $dataLimit->input('table');
        $lengthPage = $dataLimit->input('lengthPage');
        $getData = $configInstance->CountTable($tableTarget);

        if ($getData['response'] && $getData['data'] > 0 && $getData['data'] !== null) {
            //Numero por página
            $numberForPage = $getData['data'] / $lengthPage;
            return response($numberForPage, 200);
        }

        //En caso de que no cumpla la condición lanza un SQLSTATE
        return response($getData['data'], 500);
    }

    /**
     * Metodo que optiene la IP del usuario que se conecta o está conectado
     */
    public static function GetIpUser()
    {
        switch (true) {
            case (isset($_SERVER['HTTP_CLIENT_IP'])):
                return $_SERVER['HTTP_CLIENT_IP'];

            case (isset($_SERVER['HTTP_X_FORWARDED_FOR'])):
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            default:
                return $_SERVER['REMOTE_ADDR']; //IP REAL DEL USUARIO
        }
    }

    /**
     * Metodo que desencriptar la data que viene desde el Javascript
     * @param $CampoDesencryptar = Data que queramos desencryptar
     */
    public static function DecryptData($CampoDesencryptar)
    {
        //Empaquetamos el vector y la llave del encrypt a hexadecimal para poder desencriptar la data Entrante
        $Key = pack("H*", Session::get('encrypt-key'));
        $Iv = pack("H*", Session::get('encrypt-iv'));

        //Parametros: Data, Metodo del Encrypt, Key, Opciones de Encrypt, Iv
        $DecryptData = openssl_decrypt($CampoDesencryptar, 'AES-128-CBC', $Key, OPENSSL_ZERO_PADDING, $Iv);
        //Limpiamos el Decrypt
        $DecryptData = trim($DecryptData);

        return $DecryptData;
    }

    /**
     * Metodo que envia a la vista la informacion de filtrado en multiselect
     * @param Request $params Almacena la informacion de la tabla que se va a obtener el status
     * @return Response Retorna un objeto JSON con la informacion de cada campo
     */
    public static function getInfoSelect(Request $params)
    {
        $tableTarget = $params->input('table_target') != '' ? $params->input('table_target') : null;
        $arrayInfo = [
            "status" => ConfigModel::GetAllStatus($tableTarget)
        ];

        return response($arrayInfo, 200);
    }

    /**
     * Metodo que se encarga de preparar el update de una password
     * @param Request $changeRequest Se encarga de capturar la solicitud HTTP del servidor
     */
    public function prepareUpdatePassword(Request $changeRequest)
    {
        //Acomodamos los parametros
        $ParamsSession =
            [
                UsersModel::getUserInfo(Session::get('userId'))->user_code,
                ConfigController::DecryptData($changeRequest->input('Codigo')),
                ConfigController::DecryptData($changeRequest->input('Clave')),
                ConfigController::GetIpUser()
            ];

        //Actualizamos el middleware
        $changePassword = ConfigModel::updatePassword($ParamsSession);

        //Cambiamos el estado de la password y enviamos el correo
        if ($changePassword["response"]) {
            Session::put('passwordChange', 0);

            // $emailUser = Session::get('emailUser');
            // Mail::send('emailTemplate.changePassword', [], function ($message) use ($emailUser) {
            //     $message->from('sistema.carent@crowe.com.ve', 'CARENT')
            //         ->to($emailUser)
            //         ->subject('🚨 Se ha actualizado su contraseña en CARENT');
            // });
        }

        return response($changePassword, 200);
    }
}
