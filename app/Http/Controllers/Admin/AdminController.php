<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

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

    public function refix(Request $refixInfo)
    {
        $registerDate = date("Y-m-d", strtotime($refixInfo->input('endDate')));
        DB::table('control_load_admin_hours')->insert([
            "user_id" => $refixInfo->input('userId'),
            "register_date" => $registerDate,
            "register_hour" => $refixInfo->input('diffHour'),
            "admin_load_observation" => "Carga automatica por el sistema",
            "admin_hours_id" => 26,
            "approved_by" => 1,
            "approved_date" => $registerDate,
            "status_load_id" => 2
        ]);

        return response([
            "response" => true,
            "message" => "Se ha modificado exitosamente las horas del usuario #" . $refixInfo->input('userId')
        ], 200);
    }

    public function refixAll(Request $refixInfo)
    {
        $registerDate = date("Y-m-d", strtotime($refixInfo->input('endDate')));
        $users = $refixInfo->input('users');

        foreach ($users as $user) {
            # Diferencia de horas
            $diffHours = ($user['ref_total'] - $user['total_hours']);
            $userId = $user['código'];
            DB::table('control_load_admin_hours')->insert([
                "user_id" => $userId,
                "register_date" => $registerDate,
                "register_hour" => $diffHours,
                "admin_load_observation" => "Carga automatica por el sistema",
                "admin_hours_id" => 26,
                "approved_by" => 1,
                "approved_date" => $registerDate,
                "status_load_id" => 2
            ]);
        }

        return response([
            "response" => true,
            "message" => "Se ha modificado exitosamente las horas de todos los usuarios"
        ], 200);
    }
}
