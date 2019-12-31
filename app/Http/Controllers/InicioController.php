<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
//use App\Models\LoginModel;

class InicioController extends Controller
{

    function inicio(Request $request){

      $data = array();
      return view('inicio', $data);

    }

}
