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
                $totalHours = $hours->admin_hours + $hours->project_hours;
                $adminPer = ($hours->admin_hours * 100) / ($totalHours == 0 ? 1 : $totalHours);
                $proyPer = ($hours->project_hours * 100) / ($totalHours == 0 ? 1 : $totalHours);
                $startDate = $hours->month . "-01";
                $endDate = $hours->month . "-" . date("t", strtotime($startDate)); //Obtenemos el ultimo dia del mes
                //Capturamos la referencia de horas
                $refHours = $this->getRefTotal($startDate, $endDate, $user); //Referencia total de horas
                $totalPer = ($totalHours * 100) / ($refHours == 0 ? 1 : $refHours);
                //Hacemos push para el formato
                array_push($formatArray, array(
                    "nombre" => $user->user_name,
                    "cargo" => $user->position_name,
                    "area" => $user->department_name,
                    "mes" => $hours->month,
                    "proy_hours" => $hours->project_hours,
                    "percen_proy" => number_format($proyPer, 2, ",", "."),
                    "admin_hours" => $hours->admin_hours,
                    "percen_admon" => number_format($adminPer, 2, ",", "."),
                    "total_hours" => $totalHours,
                    "percen_total" => number_format($totalPer, 2, ",", "."),
                    "ref_total" => $refHours,
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
        $adminDTO = $adminHours;
        $projectDTO = $projectHours;
        $DTOMerge = array_replace_recursive($adminDTO, $projectDTO);
        //Recorremos el array resultante
        foreach ($DTOMerge as $key => $value) {
            if (!isset($value->admin_hours)) {
                // Si no existe la propiedad admin_hours, crearla y asignarle 0
                $DTOMerge[$key]->admin_hours = 0;
            }
            if (!isset($value->project_hours)) {
                // Si no existe la propiedad project_hours, crearla y asignarle 0
                $DTOMerge[$key]->project_hours = 0;
            }
        }
        #Una vez terminado el merge, acomodamos el array
        usort($DTOMerge, function ($a, $b) {
            return strtotime($a->month) - strtotime($b->month);
        });
        return $DTOMerge;
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
