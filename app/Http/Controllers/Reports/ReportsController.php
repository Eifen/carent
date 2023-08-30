<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Reports\ReportDirective;
use App\Models\ReportsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

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

    /**
     * Metodo que se encarga de devolver la lista de los reportes registrados en el sistema
     */
    public function getListReports()
    {
        $getReports = DB::table('control_reports_type')
            ->where('status_id', '=', 1)
            ->get();

        return response(array(
            "response" => true,
            "message" => $getReports
        ), 200);
    }

    /**
     * Metodo que se encarga de devolver el report de cierre de proyectos
     */
    public function getClosureReport()
    {
        return response(array(
            "response" => true,
            "message" => ReportsModel::getReport('closure_projects')
        ), 200);
    }

    /**
     * Metodo que se encarga de devolver el reporte directivo intermensual
     */
    public function getDirectiveMonthReport()
    {
        $monthReport = new ReportDirective();
        return response(array(
            "response" => true,
            "message" => $monthReport->directiveMonthReport()
        ), 200);
    }
}
