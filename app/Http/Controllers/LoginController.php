<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    //Inicializar aplicación
    public function index(Request $request){

        return view('index')
        ->with('Data',$request);
    }
}
