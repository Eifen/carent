<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Reports\ReportDirective;
use App\Models\ReportsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

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

    /**
     * Metodo que crea el directivo mensual
     * @param Request $dateRequest Recibe la informacion de startDate y endDate respectivamente desde el HTTP
     */
    public function getDirectiveTotal(Request $dateRequest)
    {
        $starDate = $dateRequest->input('startDate');
        $endDate = $dateRequest->input('endDate');
        $reportInstance = new ReportDirective($starDate, $endDate);
        //Intervalo de fechas
        $getInterval = $reportInstance->getTotalDays($starDate, $endDate);
        //Procedemos a crear un primer reporte
        $reportDTO = $reportInstance->directiveMonthReport();
        // $responseDTO = array();
        // //Recorremos el array
        // foreach ($reportDTO as $user) {
        //     foreach ($user as $userRegister) {
        //         array_push($responseDTO, $userRegister);
        //     }
        // }

        return response(array(
            "response" => true,
            "message" => $reportDTO,
            "refHour" => $getInterval
        ), 200);
    }

    /**
     * Metodo que se encarga de devolver un reporte de todas las horas administrativas
     */
    public function getAdminReport(Request $dateInterval)
    {
        $hoursReport = new ReportDirective($dateInterval->input('startDate'), $dateInterval->input('endDate'));
        return response(array(
            "response" => true,
            "message" => $hoursReport->adminHoursReport()
        ), 200);
    }

    /**
     * Metodo que crea el formato para el reporte y lo devuelve al axios
     * @param Request $listRequest recibe desde HTTP la lista a formatear
     */
    public function adminIntervalReport(Request $listRequest)
    {
        $hoursReport = new ReportDirective();
        return response(array(
            "response" => true,
            "message" => $hoursReport->adminHoursFormat($listRequest->input('adminList'))
        ), 200);
    }

    /**
     * Metodo que retorna las horas estimadas en funcion de una fecha con formato YYYY-MM
     */
    public function getHoursEstimatedMonth(Request $estimatedRequest)
    {
        $startDate = $estimatedRequest->input('date') . "-01";
        $endDate = $estimatedRequest->input('date') . "-" . date('t', strtotime($startDate));

        $reportInstance = new ReportDirective();
        return response($reportInstance->getTotalDays($startDate, $endDate));
    }
}
