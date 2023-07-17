<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfigModel;
use Illuminate\Support\Facades\Session;

class ConfigController extends Controller
{
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
}
