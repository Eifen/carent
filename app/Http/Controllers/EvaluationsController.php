<?php

namespace App\Http\Controllers;

use App\Models\EvaluationsModel;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\ConfigModel;
use App\Models\ProjectModel;
use Hamcrest\Type\IsNumeric;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\isNull;


class EvaluationsController extends Controller
{
    //Propiedades
    protected $modelInstance;
    protected $permitControl;
    protected $nestedjson;

    /**
     * Metodo inicial de la vista de evaluaciones
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
     * Metodo que se encarga de devolver la lista de los reportes registrados en el sistema (solo evaluaciones)
     */
    public function getListReportsEvaluation()
    {
        $getReports = DB::table('control_reports_type')
            ->where('report_id', '=', 7)
            ->get();

        return response(array(
            "response" => true,
            "message" => $getReports
        ), 200);
    }

    /**
     * Metodo que obtiene toda la data de Evaluaciones
     * @return Response Devuelve un objeto con la data de evaluaciones
     */
    public function GetAllEvaluations()
    {
        //        $this->modelInstance = new ConfigModel();
        //        $allData = $this->modelInstance->GetAll('evaluations_users');
        $getReports = DB::table('evaluations')
            ->select(
                'evaluation_id as código',
                'users_hierarchy_departments.department_name',
                'projects.project_description as proyecto',
                DB::raw("CONCAT(users.first_name,' ',users.second_name,' ',
                users.first_surname,' ',users.second_surname) as trabajador"),
                DB::raw("CONCAT(evaluator.first_name,' ',evaluator.second_name,' ',
                evaluator.first_surname,' ',evaluator.second_surname) as evaluador"),
                'evaluation_date',
                'evaluation_status_id as estatuslp'
            )
            ->join(
                'projects_closure_control',
                'evaluations.clousure_control_id',
                '=',
                'projects_closure_control.closure_id'
            )
            ->join(
                'projects',
                'projects_closure_control.project_id',
                '=',
                'projects.project_id'
            )
            ->join(
                'users',
                'evaluations.user_evaluated_id',
                '=',
                'users.user_id'
            )
            ->join(
                'users as evaluator',
                'evaluations.user_evaluator_id',
                '=',
                'evaluator.user_id'
            )
            ->join(
                'evaluations_promotions',
                'evaluations.evaluation_promotion_id',
                '=',
                'evaluations_promotions.evaluations_promotion_id'
            )
            ->join(
                'users_hierarchy_positions as ultiasc',
                'evaluations_promotions.approved_position',
                '=',
                'ultiasc.position_id'
            )
            ->join(
                'users_hierarchy_positions as cargoactual',
                'users.position_id',
                '=',
                'cargoactual.position_id'
            )
            ->join(
                'users_hierarchy_positions as ultipopuesto',
                'evaluations_promotions.position_propouse',
                '=',
                'ultipopuesto.position_id'
            )
            ->join(
                'users_hierarchy_departments',
                'evaluations.type_format',
                '=',
                'users_hierarchy_departments.department_id'
            )
            ->get();

        if ($getReports) {
            $nestedres = ["response" => true, "message" => $getReports];
        } else {
            $nestedres = ["response" => false, "message" => "Error"];
        }

        return response($nestedres, 200);
    }

    /**
     * Metodo que hace un llamao al control para traer la data de un usuario por su codigo
     * @param Request $userUpdate almacena el objeto pasado por parametro POST a la solicitud
     * @return Response Retorna un objeto responde con status 200 si logra crear existosamente la sesión
     */
    public function UserPerCode(Request $userUpdate)
    {
        //Creamos una instancia de sesión temporal
        Session::put("userUpdate", EvaluationsModel::GetUsersPerCode($userUpdate->input('codigoSQL')));
        return response("User Loaded", 200);
    }


    /**
     * Metodo que hace llamado a todos los datos iniciales de las evaluaciones
     * @return Response Retorna un objeto con la data inicial de las evaluaciones y status 200 si está todo correcto
     */
    public function GetInitData()
    {
        $paramsInit = [
            "dataPeriodos" => EvaluationsModel::GetAllPeriodos(1),
            "dataFechaDesde" => EvaluationsModel::GetAllPeriodDateFrom(1),
            "dataTipos" => EvaluationsModel::GetAllTiposEvaluacion(1),
            "dataMetodos" => EvaluationsModel::GetAllMetodosEvaluacion(1),
            "dataStatus" => ConfigModel::GetAllStatus()

        ];

        return response($paramsInit, 200);
    }

    /**
     * Metodo que hace llamado a todos los datos iniciales de las evaluaciones
     * @return Response Retorna un objeto con la data inicial de las evaluaciones y status 200 si está todo correcto
     */
    public function GetInitMemo()
    {
        $datos = [
            "datos" => EvaluationsModel::datos(1),
        ];

        return response($datos, 200);
    }
    /**
     * Metodo que hace llamado a todos los datos iniciales de las evaluaciones
     * @return \Symfony\Component\HttpFoundation\StreamedResponse Retorna un objeto con la data inicial de las evaluaciones y status 200 si está todo correcto
     */
    public function DownInitMemo(request $request)
    {
        return Storage::download($request->data);
    }

    /**
     * Metodo que hace llamado a todos los datos iniciales de las evaluaciones
     * @return Response Retorna un objeto con la data inicial de las evaluaciones y status 200 si está todo correcto
     */
    public function GetModalData()
    {
        $modalsInit = [
            "cargos" => ConfigModel::getAllDataStatusControl('users_hierarchy_positions')

        ];

        return response($modalsInit, 200);
    }

    /**
     * Metodo que crea o actualiza una Evaluacion
     * @param Request $dataClient Variable en formato request que recibe la data recibida por POST
     * @return Array Retorna un array en formato response
     */
    public function EvaluationControl(Request $dataEvaluation)
    {
        // $paramsEdit = array(); #Inicializacion de array de edición

        //Pasamos la data
        $paramsToControl = array(
            $dataEvaluation->input('evaluations_period')['Periodo'],
            $dataEvaluation->input('evaluations_period')['FechaDesde'],
            $dataEvaluation->input('evaluations_period')['FechaHasta'],
            $dataEvaluation->input('evaluations_period')['PeriodoDescripcion'],
            $dataEvaluation->input('evaluations_period')['PeriodoObservacion'],
            $dataEvaluation->input('evaluations_period')['IdTipo'],
            $dataEvaluation->input('evaluations_period')['IdMetodo'],
            $dataEvaluation->input('evaluations_period')['IdStatus'],
        );

        $ResponseEvaluation = EvaluationsModel::ControlEvaluations($paramsToControl, 'create');

        //Retornamos la data
        return response($ResponseEvaluation, 200);
    }

    /**
     * Metodo que extrae todos los usuarios
     * @return Response devuelve objeto response con la data resultante
     */
    public function GetAllPeriods()
    {
        $this->modelInstance = new ConfigModel();
        $allData = $this->modelInstance->GetAll('evaluations_users');

        //Retornamos toda la data
        return response($allData, 200);
    }

    /**
     * Metodo que obtiene toda la data de projectos para evaluaciones
     * @return Response Devuelve un objeto con la data de evaluaciones
     */
    public function GetAllEvaluationsProject()
    {
        //Corroboraros que exista un usuario
        //        if (Session::has('userId')) $this->permitControl = true;
        $idcurrentuser = Session::get('userId');
        $du = DB::table('vw_evaluations_projects_preview')
            ->where('user_id', $idcurrentuser)
            ->get();
        foreach ($du as $row => $value) {
            $datefrom = Carbon::parse($value->evaluation_period_date_from);
            $dateuntil = Carbon::parse($value->evaluation_period_date_until);
            if (Carbon::now()->between($datefrom, $dateuntil->addDays(7))) {
                $this->nestedjson[$row] = [
                    "código" => $value->project_id,
                    'coduser' => $value->user_id,
                    'proyecto' => $value->project_description,
                ];
            }
        }
        if ($this->nestedjson) {
            $nestedres = ["response" => true, "message" => $this->nestedjson];
        } else {
            $nestedres = ["response" => false, "message" => $du];
        }
        //        dd('Project', $du, $this->nestedjson[0], $nestedres, $allData);

        return response($nestedres, 200);
    }

    /**
     * Metodo que obtiene toda la data de Informacion de evaluacion para su reporte
     * @return Response Devuelve un objeto con la data de evaluaciones
     */
    public function GetAllEvaluationsReport()
    {
        $this->modelInstance = new ConfigModel();
        $allData = $this->modelInstance->GetAll('evaluations_info');

        return response($allData, 200);
    }


    /**
     * Metodo que obtiene toda la data de listado de evaluaciones
     * @return Response Devuelve un objeto con la data de evaluaciones
     */
    public function GetAllEvaluationsList()
    {
        //        $this->modelInstance = new ConfigModel();
        //        $allData = $this->modelInstance->GetAll('evaluations_list');
        $idcurrentuser = Session::get('userId');

        $allData = DB::table('evaluations')
            ->select(
                'evaluation_id as código',
                'projects.project_description as proyecto',
                DB::raw("CONCAT(users.first_name,' ',users.second_name,' ',
                users.first_surname,' ',users.second_surname) as trabajador"),
                'evaluations.evaluation_status_id as estatuslp'
            )
            ->join(
                'projects_closure_control',
                'evaluations.clousure_control_id',
                '=',
                'projects_closure_control.closure_id'
            )
            ->join(
                'projects',
                'projects_closure_control.project_id',
                '=',
                'projects.project_id'
            )
            ->join(
                'users',
                'evaluations.user_evaluated_id',
                '=',
                'users.user_id'
            )
            ->where('user_evaluator_id', $idcurrentuser)
            ->get();

        if ($allData) {
            $nestedres = ["response" => true, "message" => $allData];
        } else {
            $nestedres = ["response" => false, "message" => "Error"];
        }

        return response($nestedres, 200);
    }

    /**
     * Metodo que obtiene toda la data de promociones y ascensos
     * @return Response Devuelve un objeto con la data de evaluaciones
     */
    public function GetAllEvaluationsPromotion()
    {
        //        $this->modelInstance = new ConfigModel();
        //        $allData = $this->modelInstance->GetAll('evaluations_promotion');
        $getReports = DB::table('evaluations')
            ->select(
                'evaluation_id as código',
                'users_hierarchy_departments.department_name',
                'projects.project_description as proyecto',
                DB::raw("CONCAT(users.first_name,' ',users.second_name,' ',
                users.first_surname,' ',users.second_surname) as trabajador"),
                'ultiasc.position_name as ultimoasc',
                'date_promotion as fechaultimoasc',
                'cargoactual.position_name as cargoactual',
                'ultipopuesto.position_name',
                DB::raw("CONCAT(evaluator.first_name,' ',evaluator.second_name,' ',
                evaluator.first_surname,' ',evaluator.second_surname) as evaluador")
            )
            ->join(
                'projects_closure_control',
                'evaluations.clousure_control_id',
                '=',
                'projects_closure_control.closure_id'
            )
            ->join(
                'projects',
                'projects_closure_control.project_id',
                '=',
                'projects.project_id'
            )
            ->join(
                'users',
                'evaluations.user_evaluated_id',
                '=',
                'users.user_id'
            )
            ->join(
                'users as evaluator',
                'evaluations.user_evaluator_id',
                '=',
                'evaluator.user_id'
            )
            ->join(
                'evaluations_promotions',
                'evaluations.evaluation_promotion_id',
                '=',
                'evaluations_promotions.evaluations_promotion_id'
            )
            ->join(
                'users_hierarchy_positions as ultiasc',
                'evaluations_promotions.approved_position',
                '=',
                'ultiasc.position_id'
            )
            ->join(
                'users_hierarchy_positions as cargoactual',
                'users.position_id',
                '=',
                'cargoactual.position_id'
            )
            ->join(
                'users_hierarchy_positions as ultipopuesto',
                'evaluations_promotions.position_propouse',
                '=',
                'ultipopuesto.position_id'
            )
            ->join(
                'users_hierarchy_departments',
                'evaluations.type_format',
                '=',
                'users_hierarchy_departments.department_id'
            )
            ->get();

        if ($getReports) {
            $nestedres = ["response" => true, "message" => $getReports];
        } else {
            $nestedres = ["response" => false, "message" => "Error"];
        }

        return response($nestedres, 200);
    }

    /**
     * Metodo que obtiene toda la data de promociones y ascensos
     * @return Response Devuelve un objeto con la data de evaluaciones
     */
    public function GetAllPeriodDateFrom()
    {
        $this->modelInstance = new ConfigModel();
        $allData = $this->modelInstance->GetAll('evaluations_period');

        return response($allData, 200);
    }


    /**
     * Metodo que obtiene la información preeliminar de un usuario evaluado
     * @param Request $userRequest recibe los parametros enviados desde el EvaluationsReportIndex.js
     * @return Response Retorna la información del usuario en formato response
     */
    public function prepareInfoUser(Request $userRequest)
    {
        $userCode = $userRequest->input('user_code');
        return response(EvaluationsModel::getInfoEvaluationsReport($userCode), 200);
    }

    /**
     * Metodo que obtiene la información preeliminar de un usuario evaluado
     * @param Request $userRequest recibe los parametros enviados desde el EvaluationsReportIndex.js
     * @return Response Retorna la información del usuario en formato response
     */
    public function prepareInfoUserProject(Request $userRequest)
    {
        $userInfo = array(
            $userRequest->input('user_code')['coduser'],
            $userRequest->input('user_code')['código']
        );

        return response(DB::select('call sp_get_info_evaluation(?,?)', $userInfo), 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Metodo que obtiene la información preeliminar de un usuario evaluado
     * @param Request $userRequest recibe los parametros enviados desde el EvaluationsReportIndex.js
     * @return \Illuminate\Http\RedirectResponse Retorna la información del usuario en formato response
     */
    public function Insertar(Request $request)
    {
        $path = $request->file('file')->store('uploads');
        $idep = DB::table('evaluations')
            ->select('evaluation_promotion_id')
            ->where('evaluation_id', $request->codigo)
            ->first();
        $dt = DB::table('evaluations_promotions')
            ->where('evaluations_promotion_id', $idep->evaluation_promotion_id)
            ->update(['memorandum' => $path]);

        if ($dt > 0) {
            $response = "Actualizado";
        } else {
            $response = "Error";
        }

        Session::flash('nenomessage', $response);

        return redirect()->route('evaluationsList');
    }

    /**
     * Metodo que
     * @param Request
     * @return Response
     */
    public function periodPerCode(Request $request)
    {
        $getPeriod = DB::table('evaluations_period')
            ->where("evaluation_period_id", "=", $request->codigoSQL)
            ->first();
        //Convertidos la data a un json
        Session::put("periodUpdate", $getPeriod);

        return response('Period loaded', 200);
    }

    /** Metodo que elimina la sesión temporal de clientUpdate */
    public function DeletePeriodUpdate()
    {
        if (Session::has('periodUpdate')) {
            $getPeriodInfo = json_encode(Session::get('periodUpdate'));
            Session::forget('periodUpdate');
            return response($getPeriodInfo, 200);
        } else {
            response(0, 200);
        }
    }

    /**
     * Metodo que crea o actualiza un periodo
     * @param Request $dataClient Variable en formato request que recibe la data recibida por POST
     * @return Array Retorna un array en formato response
     */
    public function periodControl(Request $request)
    {
        $dt = [
            'idperiod' => $request->period['IdPeriod'], 'dats' => [
                'evaluation_period' => $request->period['Periodo'],
                'evaluation_period_date_from' => $request->period['FechaDesde'],
                'evaluation_period_date_until' => $request->period['FechaHasta'],
                'evaluation_period_description' => $request->period['PeriodoDescripcion'],
                'evaluation_period_observation' => $request->period['PeriodoObservacion'],
                'evaluation_type_id' => $request->period['IdTipo'],
                'evaluation_method_id' => $request->period['IdMetodo'],
                'status_id' => $request->period['IdStatus'],
            ]
        ];
        $ResponseEvaluation = EvaluationsModel::ControlEvaluations($dt, 'update');
        if ($ResponseEvaluation) {
            $response = ["response" => $ResponseEvaluation, "message" => "Actualizado"];
        } else {
            $response = ["response" => false, "message" => "Error"];
        }

        return response($response, 200);
    }

    /**
     * Metodo que
     * @param Request
     * @return Response
     */
    public function AuEvaPerCode(Request $request)
    {
        //Convertidos la data a un json
        Session::put("IdUserAuEva", $request->input('codigoSQL'));
        return response('AuEva loaded', 200);
    }

    /** Metodo que elimina la sesión temporal de evaluationsCreateAutoevaluation */
    public function DeleteAuEvaUpdate()
    {
        if (Session::has('IdUserAuEva')) {
            $getIdUser = Session::get('IdUserAuEva');


            $getReports = DB::table('evaluations')
                ->join(
                    'projects_closure_control',
                    'evaluations.clousure_control_id',
                    '=',
                    'projects_closure_control.closure_id'
                )
                ->join(
                    'projects',
                    'projects_closure_control.project_id',
                    '=',
                    'projects.project_id'
                )
                ->where('user_evaluated_id', $getIdUser['coduser'])
                ->where('projects_closure_control.project_id',  $getIdUser['código'])
                ->first();

            if ($getReports) {
                $this->nestedjson = [
                    'dateevaperiod' => [
                        'datefrom' => 0,
                        'dateuntil' => 0,
                        'id' => 0
                    ],
                    'evaluado' => 0,
                    'evaluador' => 0,
                    'department' => 999999999,
                    'evatype' => 0,
                    'dtactividades' => 0,
                    'dtactividadesr' => 0,
                    'potitionp' => 0,
                    'potitiondataimp' => 0,
                    'clousure_control' => 0,
                ];
                Session::forget('IdUserAuEva');
                return response($this->nestedjson, 200);
            } else {
                $du = DB::table('projects_users_assigned')
                    ->select(
                        'evaluation_period_date_from',
                        'evaluation_period_date_until',
                        'projects_users_assigned.user_id',
                        'projects_users_assigned.project_id',
                        'project_description',
                        'assigned_hours',
                        'projects_departments_assigned.department_id',
                        'projects_departments_assigned.manager_id',
                        'projects_users_assigned.user_assigned_id',
                        'evaluations_period.evaluation_period_id',
                        'projects_closure_control.closure_id'
                    )
                    ->join('projects', 'projects_users_assigned.project_id', '=', 'projects.project_id')
                    ->join(
                        'projects_closure_control',
                        'projects_users_assigned.project_id',
                        '=',
                        'projects_closure_control.project_id'
                    )
                    ->join(
                        'evaluations_period',
                        'projects_closure_control.evaluation_period_id',
                        '=',
                        'evaluations_period.evaluation_period_id'
                    )
                    ->join(
                        'projects_departments_assigned',
                        'projects_users_assigned.department_assigned_id',
                        '=',
                        'projects_departments_assigned.department_assigned_id'
                    )
                    ->where('projects_users_assigned.user_id', $getIdUser['coduser'])
                    ->where('projects.status_id', 2)
                    ->where('projects.project_id', $getIdUser['código'])
                    ->get();
                $evaluado = DB::table('users')
                    ->select(
                        'user_id',
                        'first_name',
                        'first_surname',
                        'admission_date',
                        'users_hierarchy_positions.position_name'
                    )
                    ->join(
                        'users_hierarchy_positions',
                        'users.position_id',
                        '=',
                        'users_hierarchy_positions.position_id'
                    )
                    ->where('user_id', $du[0]->user_id)->get();
                $evaluador = DB::table('users')
                    ->select(
                        'user_id',
                        'first_name',
                        'first_surname',
                        'admission_date',
                        'users_hierarchy_positions.position_name'
                    )
                    ->join(
                        'users_hierarchy_positions',
                        'users.position_id',
                        '=',
                        'users_hierarchy_positions.position_id'
                    )
                    ->where('user_id', $du[0]->manager_id)->get();
                $dtactivi = DB::table('control_load_projects_hours')
                    ->select('project_hour_id', 'register_hour', 'project_load_observation')
                    ->where('user_id', $du[0]->user_id)
                    ->where('user_assigned_id', $du[0]->user_assigned_id)->get();
                $dtactivir = DB::table('control_load_admin_hours')
                    ->select('admin_hour_id', 'register_hour', 'admin_load_observation')
                    ->where('user_id', $du[0]->user_id)
                    ->whereIn('admin_hours_id', [17, 16])->get();
                $potition = DB::table('evaluations_promotions')
                    ->select(
                        'date_promotion',
                        'evaluations_promotion_id',
                        'position_propouse',
                        'users_hierarchy_positions.position_name'
                    )
                    ->join(
                        'users_hierarchy_positions',
                        'evaluations_promotions.position_propouse',
                        '=',
                        'users_hierarchy_positions.position_id'
                    )
                    ->where('status', 1)
                    ->where('promotion_user_id', $du[0]->user_id)->latest("evaluations_promotion_id")->first();
                $potitiondataimp = DB::table('users_hierarchy_positions')
                    ->select('position_id', 'position_name')->get();
                if ($potition === null) {
                    $potition = ['position_id' => '', 'position_name' => ''];
                }

                $datefrom = Carbon::parse($du[0]->evaluation_period_date_from)->format('Y/m/d');
                $dateuntil = Carbon::parse($du[0]->evaluation_period_date_until)->addDays(7)->format('Y/m/d');

                $this->nestedjson = [
                    'dateevaperiod' => [
                        'datefrom' => $datefrom,
                        'dateuntil' => $dateuntil,
                        'id' => $du[0]->evaluation_period_id
                    ],
                    'evaluado' => $evaluado[0],
                    'evaluador' => $evaluador[0],
                    'department' => $du[0]->department_id,
                    'evatype' => 0,
                    'dtactividades' => $dtactivi,
                    'dtactividadesr' => $dtactivir,
                    'potitionp' => $potition,
                    'potitiondataimp' => $potitiondataimp,
                    'clousure_control' => $du[0]->closure_id,
                ];
                Session::forget('IdUserAuEva');
                return response($this->nestedjson, 200);
            }
        } else {
            response(0, 200);
        }
    }
    /**
     * Metodo que
     * @param Request
     * @return Response
     */
    public function EvaPerCode(Request $request)
    {
        //Convertidos la data a un json
        Session::put("IdUserEva", $request->input('codigoSql'));
        return response('Eva loaded', 200);
    }
    /** Metodo que elimina la sesión temporal de evaluationsCreateAutoevaluation */
    public function DeleteEvaInfo()
    {
        if (Session::has('IdUserEva')) {
            $data = Session::get('IdUserEva');

            $du = DB::select('SELECT pccs.closure_id, eps.evaluation_period_date_from, eps.evaluation_period_date_until, eps.evaluation_period_id
            FROM projects_closure_control pccs
            INNER JOIN evaluations_period eps ON pccs.evaluation_period_id = eps.evaluation_period_id
            WHERE pccs.project_id = ?', [$data['código']]);

            $datefrom = Carbon::parse($du[0]->evaluation_period_date_from)->format('Y/m/d');
            $dateuntil = Carbon::parse($du[0]->evaluation_period_date_until)->addDays(7)->format('Y/m/d');
            $dt = DB::table('evaluations')
                ->select(
                    'user_evaluated_id',
                    'user_evaluator_id',
                    'evaluation_detail_user_id',
                    'type_format',
                    'evaluation_au_date',
                    'evaluation_promotion_id'
                )
                ->where('clousure_control_id', $du[0]->closure_id)
                ->where('user_evaluated_id', $data['coduser'])
                ->get();
            $evaluado = DB::table('users')
                ->select(
                    'user_id',
                    'first_name',
                    'first_surname',
                    'admission_date',
                    'users_hierarchy_positions.position_name'
                )
                ->join(
                    'users_hierarchy_positions',
                    'users.position_id',
                    '=',
                    'users_hierarchy_positions.position_id'
                )
                ->where('user_id', $dt[0]->user_evaluated_id)->get();
            $evaluador = DB::table('users')
                ->select(
                    'user_id',
                    'first_name',
                    'first_surname',
                    'admission_date',
                    'users_hierarchy_positions.position_name'
                )
                ->join(
                    'users_hierarchy_positions',
                    'users.position_id',
                    '=',
                    'users_hierarchy_positions.position_id'
                )
                ->where('user_id',  $dt[0]->user_evaluator_id)->get();
            $dtt = DB::table('evaluations_detail_user')
                ->where('evaluation_detail_user_id', $dt[0]->evaluation_detail_user_id)
                ->get();
            $potition = DB::table('evaluations_promotions')
                ->select(
                    'date_promotion',
                    'evaluations_promotion_id',
                    'position_propouse',
                    'users_hierarchy_positions.position_name'
                )
                ->join(
                    'users_hierarchy_positions',
                    'evaluations_promotions.position_propouse',
                    '=',
                    'users_hierarchy_positions.position_id'
                )
                ->where('status', 1)
                ->where('promotion_user_id', $dt[0]->user_evaluated_id)->latest("evaluations_promotion_id")->first();
            $potitiondataimp = DB::table('users_hierarchy_positions')
                ->select('position_id', 'position_name')->get();
            if ($potition === null) {
                $potition = ['position_id' => '', 'position_name' => ''];
            }
            $potitionprev = DB::table('evaluations_promotions')
                ->select('position_propouse')
                ->where('status', 0)
                ->where('promotion_user_id', $dt[0]->user_evaluated_id)->latest("evaluations_promotion_id")->first();
            if ($potitionprev === null) {
                $potitionprev = 0;
            }

            $this->nestedjson = [
                'dateevaperiod' => [
                    'datefrom' => $datefrom,
                    'dateuntil' => $dateuntil,
                    'id' => $du[0]->evaluation_period_id
                ],
                'evaluado' => $evaluado[0],
                'evaluador' => $evaluador[0],
                'department' => $dt[0]->type_format,
                'evaluation_au_date' => $dt[0]->evaluation_au_date,
                'fulldata' => $dtt[0],
                'evatype' => 0,
                'potitionp' => $potition,
                'potitiondataimp' => $potitiondataimp,
                'potitionprev' => $potitionprev,
            ];

            Session::forget('IdUserAuEva');
            return response($this->nestedjson, 200);
        } else {
            response(0, 200);
        }
    }

    /** Metodo que elimina la sesión temporal de clientUpdate */
    public function controlData(Request $request)
    {
        $datenow = Carbon::now()->format('Y/m/d');

        if ($request->evaluator === false) {
            $nestedjsonevad = [
                'dt_section1' => json_encode($request->data['tbdetails']['section1']),
                'dt_section1_total' => json_encode($request->data['tbdetails']['section1_totals']),
                'dt_section2' => json_encode($request->data['tbdetails']['section2']),
                'dt_section2_total' => json_encode($request->data['tbdetails']['section2_totals']),
                'dt_section3' => json_encode($request->data['tbdetails']['section3']),
                'evaluator_comments' => $request->data['tbdetails']['evaluator_comments'],
            ];
            $detaileva = DB::table('evaluations_detail_user')
                ->insertGetId($nestedjsonevad);
            $nestedjsoneva = [
                'evaluation_detail_user_id' => $detaileva,
                'evaluation_au_date' => $datenow,
                'evaluation_status_id' => 0,
                'clousure_control_id' => $request->data['clousure_control'],
                'user_evaluated_id' => json_encode($request->data['evaluated']['user_id']),
                'user_evaluator_id' => json_encode($request->data['evaluator']['user_id']),
                'type_format' => $request->data['type_format'],
            ];
            DB::table('evaluations')
                ->insertGetId($nestedjsoneva);
            $response = ["response" => true, "message" => "Evaluacion exitosa"];
        } elseif ($request->evaluator === true) {
            $nestedjsonevad = [
                'dt_section1' => json_encode($request->data['tbdetails']['section1']),
                'dt_section1_total' => json_encode($request->data['tbdetails']['section1_totals']),
                'dt_section2' => json_encode($request->data['tbdetails']['section2']),
                'dt_section2_total' => json_encode($request->data['tbdetails']['section2_totals']),
                'dt_section3' => json_encode($request->data['tbdetails']['section3']),
                'evaluator_comments' => $request->data['tbdetails']['evaluator_comments'],
            ];
            DB::table('evaluations_detail_user')
                ->where('evaluation_detail_user_id', $request->updateids['edu'])
                ->update($nestedjsonevad);
            if ($request->data['promotion_p'] !== 0) {
                $nestedjsonpromo = [
                    'evaluations_id' => $request->updateids['e'],
                    'date_promotion' => $datenow,
                    'position_propouse' => $request->data['promotion_p'],
                    'promotion_user_id' => $request->data['evaluated']['user_id'],
                    'status' => 0,
                ];
                if ($request->updateids['ep'] !== null) {
                    DB::table('evaluations_promotions')
                        ->where('evaluations_promotion_id', $request->updateids['ep'])
                        ->update($nestedjsonpromo);
                    $nestedjsoneva = [
                        'evaluation_promotion_id' => $request->updateids['ep'],
                        'evaluation_date' => $datenow,
                        'evaluation_status_id' => 1,
                    ];
                } else {
                    $promo = DB::table('evaluations_promotions')
                        ->insertGetId($nestedjsonpromo);
                    $nestedjsoneva = [
                        'evaluation_promotion_id' => $promo,
                        'evaluation_date' => $datenow,
                        'evaluation_status_id' => 1,
                    ];
                }
                DB::table('evaluations')
                    ->where('evaluation_id', $request->updateids['e'])
                    ->update($nestedjsoneva);
                $response = ["response" => true, "message" => "Evaluacion exitosa"];
            } else {
                $nestedjsoneva = [
                    'evaluation_date' => $datenow,
                    'evaluation_status_id' => 1,
                ];
                DB::table('evaluations')
                    ->where('evaluation_id', $request->updateids['e'])
                    ->update($nestedjsoneva);
                $response = ["response" => true, "message" => "Actualizado"];
            }
        }

        //        if ($eva) {
        //            $response = ["response" => true, "message" => "Actualizado"];
        //        } else {
        //            $response = ["response" => false, "message" => "Error"];
        //        }

        return response($response, 200);
    }
    /**
     * Metodo que
     * @param Request
     * @return Response
     */
    public function EvaluatorPerCode(Request $request)
    {
        //Convertidos la data a un json
        Session::put("idEvaluator", $request->codigoSQL);
        return response('Evaluator loaded', 200);
    }

    /** Metodo que elimina la sesión temporal de evaluationsCreateAutoevaluation */
    public function DeleteEvaluatorUpdate()
    {
        if (Session::has('idEvaluator')) {
            $getIdeva = Session::get('idEvaluator');

            $dt = DB::table('evaluations')
                ->select(
                    'user_evaluated_id',
                    'user_evaluator_id',
                    'evaluation_detail_user_id',
                    'user_evaluated_id',
                    'evaluations_period.evaluation_period_date_from',
                    'type_format',
                    'evaluations_period.evaluation_period_date_until',
                    'evaluations_period.evaluation_period_id',
                    'evaluation_au_date',
                    'evaluation_date',
                    'evaluations.evaluation_id'
                )
                ->join(
                    'projects_closure_control',
                    'evaluations.clousure_control_id',
                    '=',
                    'projects_closure_control.closure_id'
                )
                ->join(
                    'evaluations_period',
                    'projects_closure_control.evaluation_period_id',
                    '=',
                    'evaluations_period.evaluation_period_id'
                )
                ->where('evaluation_id', $getIdeva)
                ->get();
            $datefrom = Carbon::parse($dt[0]->evaluation_period_date_from)->format('Y/m/d');
            $dateuntil = Carbon::parse($dt[0]->evaluation_period_date_until)->addDays(7)->format('Y/m/d');
            $evaluado = DB::table('users')
                ->select(
                    'user_id',
                    'first_name',
                    'first_surname',
                    'admission_date',
                    'users_hierarchy_positions.position_name'
                )
                ->join(
                    'users_hierarchy_positions',
                    'users.position_id',
                    '=',
                    'users_hierarchy_positions.position_id'
                )
                ->where('user_id', $dt[0]->user_evaluated_id)->get();
            $evaluador = DB::table('users')
                ->select(
                    'user_id',
                    'first_name',
                    'first_surname',
                    'admission_date',
                    'users_hierarchy_positions.position_name'
                )
                ->join(
                    'users_hierarchy_positions',
                    'users.position_id',
                    '=',
                    'users_hierarchy_positions.position_id'
                )
                ->where('user_id',  $dt[0]->user_evaluator_id)->get();
            $dtt = DB::table('evaluations_detail_user')
                ->where('evaluation_detail_user_id', $dt[0]->evaluation_detail_user_id)
                ->get();
            $potition = DB::table('evaluations_promotions')
                ->select(
                    'date_promotion',
                    'evaluations_promotion_id',
                    'position_propouse',
                    'users_hierarchy_positions.position_name'
                )
                ->join(
                    'users_hierarchy_positions',
                    'evaluations_promotions.position_propouse',
                    '=',
                    'users_hierarchy_positions.position_id'
                )
                ->where('status', 1)
                ->where('promotion_user_id', $dt[0]->user_evaluated_id)->latest("evaluations_promotion_id")->first();
            $potitionprev = DB::table('evaluations_promotions')
                ->select('position_propouse')
                ->where('status', 0)
                ->where('promotion_user_id', $dt[0]->user_evaluated_id)->latest("evaluations_promotion_id")->first();
            if ($potitionprev === null) {
                $potitionprev = 0;
            }
            $potitiondataimp = DB::table('users_hierarchy_positions')
                ->select('position_id', 'position_name')->get();
            if ($potition === null) {
                $potition = ['position_id' => '', 'position_name' => '', 'evaluations_promotion_id' => null];
            }

            $this->nestedjson = [
                'dateevaperiod' => [
                    'datefrom' => $datefrom,
                    'dateuntil' => $dateuntil,
                    'id' => $dt[0]->evaluation_period_id
                ],
                'evaluado' => $evaluado[0],
                'evaluador' => $evaluador[0],
                'department' => $dt[0]->type_format,
                'evaluation_au_date' => $dt[0]->evaluation_au_date,
                'evaluation_date' => $dt[0]->evaluation_date,
                'fulldata' => $dtt[0],
                'evatype' => 1,
                'potitionp' => $potition,
                'potitionprev' => $potitionprev,
                'potitiondataimp' => $potitiondataimp,
                'edu' => $dtt[0]->evaluation_detail_user_id,
                'e' => $dt[0]->evaluation_id,
                'ep' => $potition['evaluations_promotion_id'],
            ];


            Session::forget('idEvaluator');
            return response($this->nestedjson, 200);
        } else {
            response(0, 200);
        }
    }
    /**
     * Metodo que
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function controlPotitionapro(Request $request)
    {
        $nestedjsoneva = [
            'observations' => $request->data['potitionobser'],
            'approved_position' => $request->data['potitionapro'],
        ];
        $eva = DB::table('evaluations_promotions')
            ->where('evaluations_promotion_id', $request->data['potitionoid'])
            ->update($nestedjsoneva);

        if ($eva >= 0) {
            $response = ["response" => true, "message" => "Actualizado"];
        } else {
            $response = ["response" => false, "message" => "Error"];
        }

        return response($response, 200);
    }
}
