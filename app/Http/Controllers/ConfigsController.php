<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
use Illuminate\Http\RedirectResponse;

class ConfigsController extends Controller
{

    function encryptConfig(Request $request){

      $modelo = new ConfigsModel();
      $config = $modelo->encryptConfig();

      return $config;

    }

}
