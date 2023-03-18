<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    //Inicializar aplicación
    public function index(Request $request){
        //Creamos variable que verifica la Session
        $tupleValidacion = false;
        //Hacemos la condición de carga
        if($request->session()->has('usuario_id')){ $tupleValidacion = true; }
        return view('index')
        ->with('Session',$tupleValidacion);
    }
}
