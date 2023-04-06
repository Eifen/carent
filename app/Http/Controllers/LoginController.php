<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoginModel;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    protected $KeyEncrypt = '0123456789abcdef0123456789abcdef'; //Contexto del Encrypt
    protected $IvEncrypt = 'abcdef9876543210abcdef9876543210'; //Vector de inicialización
    protected $tupleValidacion = false;
    //Inicializar aplicación
    public function index(Request $request){

        $LoginInstance = new LoginModel();
        $encryptData = $LoginInstance->GetEncryptKey();
        //Enviamos la data dependiendo del estado de la sesión
        if($request->session()->has('usuario_id')){ $this->tupleValidacion = true; }

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
            $this->DecryptData($request->input('Codigo')),
            $this->DecryptData($request->input('Clave')),
            $this->GetIpUser()
        ];

        $Login = $LoginInstance->VerificarLogin($ParamsSession);
        return $Login;
    }

    /**
     * Metodo que optiene la IP del usuario que se conecta o está conectado
     */
    protected static function GetIpUser()
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
    protected static function DecryptData($CampoDesencryptar)
    {
        //Empaquetamos el vector y la llave del encrypt a hexadecimal para poder desencriptar la data Entrante
        $Key = pack("H*",Session::get('encrypt-key'));
        $Iv = pack("H*",Session::get('encrypt-iv'));

        //Parametros: Data, Metodo del Encrypt, Key, Opciones de Encrypt, Iv
        $DecryptData = openssl_decrypt($CampoDesencryptar, 'AES-128-CBC', $Key, OPENSSL_ZERO_PADDING, $Iv);
        //Limpiamos el Decrypt
        $DecryptData = trim($DecryptData);

        return $DecryptData;
    }
}
