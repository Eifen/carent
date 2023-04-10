<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsersModel;
use App\Models\ConfigModel;

class UsersController extends Controller
{
    protected $modelInstance;

    public function index(){
        $this->modelInstance = new ConfigModel();
        $allData = $this->modelInstance->GetAll('users');

        //Retornamos toda la data
        return response($allData,200);
    }
}
