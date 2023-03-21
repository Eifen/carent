<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoginModel;

class LoginController extends Controller
{
    protected $KeyEncrypt = '0123456789abcdef0123456789abcdef'; //Contexto del Encrypt
    protected $IvEncrypt = 'abcdef9876543210abcdef9876543210'; //Vector de inicialización
    protected $tupleValidacion = false;
    //Inicializar aplicación
    public function index(Request $request){

        //Enviamos la data dependiendo del estado de la sesión
        if($request->session()->has('usuario_id')){ $this->tupleValidacion = true; }

        //Traemos el KEY y el IV de la base de datos y lo asignamos a una instancia de Session
        $request->session()->put('encrypt-key', $this->KeyEncrypt);
        $request->session()->put('encrypt-iv', $this->IvEncrypt);

        return view('index')
        ->with('Session',$this->tupleValidacion)
        ->with('DataSession', $request);
    }
}
