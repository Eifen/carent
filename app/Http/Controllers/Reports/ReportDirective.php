<?php

namespace App\Http\Controllers\Reports;

use App\Models\UsersModel;
use App\Http\Controllers\Controller;
use App\Models\ReportsModel;
use Illuminate\Http\Request;

class ReportDirective extends Controller
{
    private $users; //Array de usuarios
    private $startDate; //Fecha inicial
    private $endDate; //Fecha final
    public function __construct()
    {
        //Cambiamos el formato de la fecha
        setlocale(LC_TIME, "es_ES");
        //Asignamos las propiedades
        $this->users = UsersModel::getInfoUsers();
        $this->startDate = date('2020-03-01');
        $this->endDate = date('Y-m-d');
    }
    /**
     * Metodo principal de la clase que se encarga de estructurar todo el esquema del reporte directivo
     */
    public function directiveMonthReport()
    {
        $responseArray = array();
        foreach ($this->users as $user) {
            $adminHours = $this->adminHours($user->user_id);
            $projectHours = $this->projectHours($user->user_id);
            //Creamos el formato
            array_push($responseArray, $this->mergeHours($adminHours, $projectHours));
        }
        return $responseArray;
    }

    /**
     * Metodo privado que abstrae las horas administrativas cargadas por todos los usuarios
     */
    private function adminHours($userId)
    {
        //Hacemos un recorrido de los usuarios para abstraer las horas de usuarios
        $params = array(
            $this->startDate,
            $this->endDate,
            $userId,
            1
        );
        #Obtenemos las horas registradas, cargando el array
        $getAdminHours = ReportsModel::getRegisterHours($params);
        return $getAdminHours;
    }

    /**
     * Metodo privado que abstrae las horas a proyecto cargadas por todos los usuarios
     */
    private function projectHours($userId)
    {
        //Hacemos un recorrido de los usuarios para abstraer las horas de usuarios
        $params = array(
            $this->startDate,
            $this->endDate,
            $userId,
            2
        );
        #Obtenemos las horas registradas, cargando el array
        $getProjectHours = ReportsModel::getRegisterHours($params);
        return $getProjectHours;
    }

    /**
     * Metodo que se encarga de hacer merge de las horas administrativas y de proyectos de  un usuario
     */
    private function mergeHours($adminHours, $projectHours)
    {
        $responseArray = array();
        //Hacemos un recorrido de las horas administrativas
        foreach ($adminHours as $admin) {
            array_push(
                $responseArray,
                array(
                    "mes" => $admin->month,
                    "proj_hours" => 0,
                    "admin_hours" => $admin->admin_hours
                )
            );
        }
        $countMerge = 0; #Contador que se encarga de definir si el mes se repite
        //Hacemos un recorrido de las horas a proyectos para hacer merge con las administrativas
        foreach ($projectHours as $project) {
            //Verificamos si ya existe el mes
            foreach ($responseArray as $merge) {
                //Condicion de mes

            }
        }

        return $responseArray;
    }
}
