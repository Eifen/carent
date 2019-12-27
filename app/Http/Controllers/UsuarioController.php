<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\UsuarioModel;

class UsuarioController extends Controller
{

    function encryptConfig(Request $request){

      $modelo = new UsuarioModel();
      $config = $modelo->encryptConfig();

      /*$key = pack("H*", "0123456789abcdef0123456789abcdef");
      $iv =  pack("H*", "abcdef9876543210abcdef9876543210");
      $decrypted = openssl_decrypt($request->input("clave"), 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
      $decrypted = trim($decrypted);*/


      //return $secretKey;
    /*  $clave = "dc171216";
      $clave2 = $request->input("clave");
      $encrypted = Crypt::encryptString($clave);
      $decrypted = Crypt::decryptString($encrypted);*/
    return $config;

    }

    function login(Request $request){

      $codigoUsuario = $request->input("codigoUsuario");

      $modelo = new UsuarioModel();
      $usuario = $modelo->buscarUsuario($codigoUsuario);

      return $usuario;

    }

}
