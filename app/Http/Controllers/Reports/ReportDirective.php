<?php

namespace App\Http\Controllers\Reports;

use App\Models\UsersModel;
use App\Http\Controllers\Controller;
use App\Models\ReportsModel;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use NumberFormatter;

use function PHPUnit\Framework\isEmpty;

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
            //Abstraemos el array de horas
            $hoursArray = $this->mergeHours($adminHours, $projectHours);
            //Generamos el formato
            $formatArray = array();
            foreach ($hoursArray as $hours) {
                $totalHours = $hours["admin_hours"] + $hours["proj_hours"];
                $startDate = $hours["mes"] . "-01";
                $endDate = $hours["mes"] . "-" . date("t", strtotime($startDate)); //Obtenemos el ultimo dia del mes
                //Capturamos la referencia de horas
                $refHours = $this->getRefTotal($startDate, $endDate, $user); //Referencia total de horas
                $adminPer = ($hours["admin_hours"] * 100) / ($refHours == 0 ? 1 : $refHours);
                $proyPer = ($hours["proj_hours"] * 100) / ($refHours == 0 ? 1 : $refHours);
                $totalPer = ($totalHours * 100) / ($refHours == 0 ? 1 : $refHours);
                //Hacemos push para el formato
                array_push($formatArray, array(
                    "nombre" => $user->user_name,
                    "cargo" => $user->position_name,
                    "area" => $user->department_name,
                    "mes" => $hours["mes"],
                    "proy_hours" => number_format($hours["proj_hours"], 2, ",", "."),
                    "percen_proy" => number_format($proyPer, 2, ",", "."),
                    "admin_hours" => number_format($hours["admin_hours"], 2, ",", "."),
                    "percen_admon" => number_format($adminPer, 2, ",", "."),
                    "total_hours" => number_format($totalHours, 2, ",", "."),
                    "percen_total" => number_format($totalPer, 2, ",", "."),
                    "ref_total" => number_format($refHours, 2, ",", "."),
                    "estatus" => $user->status_id,
                    "order" => $user->order,
                ));
            }
            //Cargamos el formato al array resultante
            array_push($responseArray, $formatArray);
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
                    "admin_hours" => floatval($admin->admin_hours)
                )
            );
        }
        //Hacemos un recorrido de las horas a proyectos para hacer merge con las administrativas
        foreach ($projectHours as $project) {
            //Verificamos si ya existe el mes
            $keySearch = array_search($project->month, array_column($responseArray, "mes"));
            if ($keySearch === false) {
                array_push($responseArray, array(
                    "mes" => $project->month,
                    "proj_hours" => floatval($project->project_hours),
                    "admin_hours" => 0
                ));
            } else {
                $responseArray[$keySearch]["proj_hours"] = floatval($project->project_hours);
            }
        }

        #Una vez terminado el merge, acomodamos el array
        usort($responseArray, function ($a, $b) {
            return strtotime($a["mes"]) - strtotime($b["mes"]);
        });
        return $responseArray;
    }

    /**
     * Metodo que obtiene las horas totales que debe cargar un usuario dependiendo de su mes
     * @param String $startDate Fecha inicial
     * @param String $endDate Fecha Final
     * @param Object $userInfo Informacion del usuario
     */
    private function getRefTotal($startDate, $endDate, $userInfo)
    {
        //Relacion con fecha de ingreso
        $DTOStart = (strtotime($startDate) < strtotime($userInfo->admission_date))
            ? $userInfo->admission_date
            : $startDate;

        //Relacion con fecha de egreso
        $DTOEnd = !isEmpty($userInfo->departure_date)
            ? ((strtotime($endDate) > strtotime($userInfo->departure_date)) ? $userInfo->departure_date : $endDate)
            : $endDate;

        #Generamos la consulta
        $getInterval = $this->getTotalDays($DTOStart, $DTOEnd);

        return $getInterval;
    }

    /**
     * Metodo que obtiene el intervalo de dias en formato de 8 horas
     * @param String $startDate Fecha inicial
     * @param String $endDate Fecha final
     */
    public function getTotalDays($startDate, $endDate)
    {
        $countDays = 0; //Contador de dias
        $DTODate = new DateTime($startDate);
        $DTOEndDate = new DateTime($endDate);
        while ($DTODate <= $DTOEndDate) {
            //Si la fecha es domingo o sabado, no la contamos
            if ($DTODate->format("N") != 7 && $DTODate->format("N") != 6) {
                $countDays = $countDays + 1;
            }

            #Sumamos la fecha
            $DTODate->modify("+1 day");
        }

        #Multiplicamos por 8
        $totalDays = ($countDays * 8);

        return $totalDays;
    }
}
