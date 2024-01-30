<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    //Propiedades
    protected $modelInstance;
    protected $permitControl;
    /**
     * Metodo inicial de la vista de Admin
     * @param mixed $request recibe la data de session del sistema
     */
    public function index(Request $request)
    {
        //Corroboraros que exista un usuario
        if (Session::has('userId')) $this->permitControl = true;
        $catchStatusMaintenance = ConfigModel::checkMaintenance();

        return view('index')
            ->with("Session", $this->permitControl)
            ->with('Maintenance', $catchStatusMaintenance);
    }
}
