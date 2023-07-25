<?php

namespace App\Http\Controllers;

use App\Models\BillingModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\ConfigModel;
use App\Models\ProjectModel;

class BillingController extends Controller
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

        return view('index')->with("Session", $this->permitControl);
    }

    /**
     * Metodo que se encarga de devolver una vista de los proyectos activos e inactivos para a vista de billings
     */
    public function getProjectBillings()
    {
        $this->modelInstance = new ConfigModel();
        $allData = $this->modelInstance->GetAll('billings');
        return response($allData, 200);
    }

    /**
     * Metodo que hace un llamao al control para traer la data de un proyecto por su codigo
     * @param Request $billingRequest almacena el objeto pasado por parametro POST a la solicitud
     * @return Response Retorna un objeto responde con status 200 si logra crear existosamente la sesión
     */
    public function billingPerProject(Request $billingRequest)
    {
        //Creamos una instancia de sesión temporal
        Session::put("billingProject", ProjectModel::getProjectInfo($billingRequest->input('codigoSQL')));
        return response("Project Loaded", 200);
    }

    /** Metodo que elimina la sesión temporal*/
    public function deleteBillingInfo()
    {
        if (Session::has('billingProject')) {
            $getBillingInfo = json_encode(Session::get('billingProject'));
            //Eliminamos la sesion
            Session::forget('billingProject');
            return response($getBillingInfo, 200);
        }
    }

    /**
     * Metodo que captura los parametros para el formulario de crear/actualizar factura
     */
    public function prepareParams()
    {
        return response(BillingModel::getBillingParams(), 200);
    }
}
