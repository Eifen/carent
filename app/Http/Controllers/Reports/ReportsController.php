<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ReportsController extends Controller
{
    //Propiedades
    protected $modelInstance;
    protected $permitControl;

    /**
     * Metodo inicial de la vista de Repòrtes
     * @param mixed $request recibe la data de session del sistema
     */
    public function index(Request $request)
    {
        //Corroboraros que exista un usuario
        if (Session::has('userId')) $this->permitControl = true;

        return view('index')->with("Session", $this->permitControl);
    }
}
