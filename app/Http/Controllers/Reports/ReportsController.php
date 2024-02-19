<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Reports\ReportDirective;
use App\Models\ConfigModel;
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
        $catchStatusMaintenance = ConfigModel::checkMaintenance();

        return view('index')
            ->with("Session", $this->permitControl)
            ->with('Maintenance', $catchStatusMaintenance);
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
        $getClosureReport = ReportsModel::getReport('closure_projects');
        $closureDTO = $getClosureReport->toArray();
        $getClosureReport = array_filter($closureDTO, function ($infoClosure) {
            //Condiciones de visualizacion
            switch (Session::get('isAdmin')) {
                case 1: //Administradores
                    return 1;
                default:
                    $isPartner = $infoClosure->partner_id === Session::get('userId');
                    $isManager = $infoClosure->manager_id === Session::get('userId');
                    return $isManager ? $isManager : ($isPartner ? $isPartner : $isManager);
            }
        });

        //Quitamos las propiedades innecesarias
        $getClosureReport = array_map(function ($infoClosure) {
            unset($infoClosure->partner_id);
            unset($infoClosure->manager_id);
            return $infoClosure;
        }, $getClosureReport);

        return response(array(
            "response" => true,
            "message" => collect(array_values($getClosureReport))
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
        $starDate = date('Y-m-d', strtotime($dateRequest->input('startDate')));
        $endDate = date('Y-m-d', strtotime($dateRequest->input('endDate')));
        $reportInstance = new ReportDirective($starDate, $endDate);
        //Intervalo de fechas
        $getInterval = $reportInstance->getTotalDays($starDate, $endDate);
        //Procedemos a crear un primer reporte
        $reportDTO = $reportInstance->directiveMonthReport(1);
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
     * Metodo que se encarga de devolver un reporte de todas las horas administrativas
     */
    public function getProyReport(Request $dateInterval)
    {
        $hoursReport = new ReportDirective($dateInterval->input('startDate'), $dateInterval->input('endDate'));
        return response(array(
            "response" => true,
            "message" => $hoursReport->proyHoursReport()
        ), 200);
    }

    /**
     * Metodo que se encarga de devolver un reporte historico del usuario seleccionado
     */
    public function getHistoryHoursReport(Request $userInfo)
    {
        $hoursReport = new ReportDirective();
        return response(array(
            "response" => true,
            "message" => $hoursReport->historyHoursReport($userInfo->input('userCode'))
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

    /**
     * Metodo que retorna el registro de carga de todos los proyectos
     */
    public function getLogProject()
    {
        $getProjectLog = ReportsModel::getReport('project_log');
        $closureDTO = $getProjectLog->toArray();
        $getProjectLog = array_filter($closureDTO, function ($infoLog) {
            //Condiciones de visualizacion
            switch (Session::get('isAdmin')) {
                case 1: //Administradores
                    return 1;
                default:
                    $isPartner = $infoLog->partner_id === Session::get('userId');
                    $isManager = $infoLog->manager_id === Session::get('userId');
                    return $isManager ? $isManager : ($isPartner ? $isPartner : $isManager);
            }
        });

        //Quitamos las propiedades innecesarias
        $getProjectLog = array_map(function ($infoLog) {
            unset($infoLog->partner_id);
            unset($infoLog->manager_id);
            return $infoLog;
        }, $getProjectLog);

        return response(array(
            "response" => true,
            "message" => collect(array_values($getProjectLog))
        ), 200);
    }

    /**
     * Metodo que retorna el registro de todos los usuarios
     */
    public function getUsersReport()
    {
        return response(array(
            "response" => true,
            "message" => ReportsModel::getReport('users_log')
        ), 200);
    }

    public function getNoRegisterReport(Request $requestDate)
    {

        $prepareDate = array(
            date("Y-m-d", strtotime($requestDate->input("start"))),
            date("Y-m-d", strtotime($requestDate->input("end"))),
        );

        return response(array(
            "response" => true,
            "message" => ReportsModel::noRegisterHoursPersonal($prepareDate)
        ), 200);
    }

    public function getQuotasReport()
    {
        return response(array(
            "response" => true,
            "message" => ReportsModel::getReport('billing_quotas')
        ), 200);
    }

    public function getClientsReport()
    {
        return response(array(
            "response" => true,
            "message" => ReportsModel::getReport('clients_log')
        ), 200);
    }

    public function getBillingsReport(Request $requestDate)
    {
        $paramsDate = array(
            $requestDate->input("start"),
            $requestDate->input("end")
        );

        return response(array(
            "response" => true,
            "message" => ReportsModel::billingsReport($paramsDate)
        ), 200);
    }

    public function getBillingsProjReport(Request $requestDate)
    {
        $paramsDate = array(
            $requestDate->input("end"),
            $requestDate->input("start"),
        );

        return response(array(
            "response" => true,
            "message" => ReportsModel::billingsProjReport($paramsDate)
        ), 200);
    }
}
