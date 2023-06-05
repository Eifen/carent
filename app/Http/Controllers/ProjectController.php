<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectModel;
use App\Models\ConfigModel;
use Illuminate\Support\Facades\Session;

class ProjectController extends Controller
{
    //Propiedades
    protected $modelInstance;
    protected $permitControl;

    /**
     * Metodo inicial de la vista de clientes
     * @param mixed $request recibe la data de session del sistema
     */
    public function index(Request $request)
    {
        //Corroboraros que exista un usuario
        if (Session::has('userId')) $this->permitControl = true;

        return view('index')->with("Session",$this->permitControl);
    }
    
    /**
     * Metodo que se encarga de devolver una vista de los proyectos activos e inactivos
     */
    public function getAllProjects()
    {
        $this->modelInstance = new ConfigModel();
        $allData = $this->modelInstance->GetAll('projects');
        return response($allData,200);
    }
}
