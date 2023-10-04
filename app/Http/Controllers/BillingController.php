<?php

namespace App\Http\Controllers;

use App\Models\BillingModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\ConfigModel;
use App\Models\ProjectModel;
use Hamcrest\Type\IsNumeric;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

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
        $catchStatusMaintenance = ConfigModel::checkMaintenance();

        return view('index')
            ->with("Session", $this->permitControl)
            ->with('Maintenance', $catchStatusMaintenance);
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

    /**
     * Metodo que prepara la informacion para actualizar o crear una nueva factura en la base de datos
     * @param Request $submitRequest captura los parametros de la solicitud HTTP
     */
    public function prepareSubmit(Request $submitRequest)
    {
        //Fecha de ingreso null o vacia
        $dateBilling = $submitRequest->input('dateBilling') != null || $submitRequest->input('dateBilling') != ''
            ? date("Y-m-d", strtotime($submitRequest->input('dateBilling')))
            : null;
        $paymentBilling = $submitRequest->input('datePayment') != null || $submitRequest->input('datePayment') != ''
            ? date("Y-m-d", strtotime($submitRequest->input('datePayment')))
            : null;

        //Pasamos la data
        $paramsToControl = array(
            Session::get('userId'),
            ConfigController::GetIpUser(),
            $submitRequest->input('projectId'),
            $submitRequest->input('concept'),
            $submitRequest->input('numberBilling'),
            $dateBilling,
            $submitRequest->input('valueBilling'),
            $submitRequest->input('iva'),
            $submitRequest->input('retIva'),
            $submitRequest->input('islr'),
            $submitRequest->input('description'),
            $submitRequest->input('numberControl'),
            $paymentBilling,
            $submitRequest->input('observation'),
            is_numeric($submitRequest->input('nullId')) && $submitRequest->input('nullId') != 0  ? $submitRequest->input('nullId') : null,
        );

        if ($submitRequest->input('edit')) {
            array_push($paramsToControl, $submitRequest->input('billingId'));
        }

        $submitRequest->input('edit')
            ? $ResponseBilling = BillingModel::controlBilling($paramsToControl, 'update')
            : $ResponseBilling = BillingModel::controlBilling($paramsToControl, 'create');

        return response($ResponseBilling, 200);
    }

    /**
     * Metodo que devuelve la lista de facturas luego de ser creada un o actualizar
     */
    public function refreshBilling(Request $refreshRequest)
    {
        return response(ProjectModel::getProjectInfo($refreshRequest->input('project_id')), 200);
    }

    /**
     * Metodo que elimina la factura
     */
    public function deleteBilling(Request $deleteRequest)
    {
        DB::table('billings')
            ->where("billing_id", "=", $deleteRequest->input("billingId"))
            ->delete();
        return response(array(
            "response" => true,
            "message" => "Factura eliminada exitosamente"
        ));
    }
}
