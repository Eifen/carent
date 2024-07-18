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
    public function __construct($startDTO = '2020-03-01', $endDTO = 'Y-m-d')
    {
        //Cambiamos el formato de la fecha
        setlocale(LC_TIME, "es_ES");
        //Asignamos las propiedades
        $this->users = UsersModel::getInfoUsers();
        $this->startDate = date($startDTO);
        $this->endDate = date($endDTO);
    }
    public function adminHoursReport()
    {
        $responseArray = DB::select("SELECT * FROM control_load_admin_hours clah
        INNER JOIN control_concept_admin_hours ccah ON clah.admin_hours_id = ccah.admin_hours_id
        WHERE clah.register_date BETWEEN ? AND ? AND clah.status_load_id = ?", [$this->startDate, $this->endDate, 2]);

        return $responseArray;
    }

    public function proyHoursReport()
    {
        $responseArray = DB::select("call sp_report_proy(?,?)", [$this->startDate, $this->endDate]);
        return $responseArray;
    }

    public function historyHoursReport($userCode)
    {
        $responseArray = DB::select("call sp_admin_proy_log_report(?)", [$userCode]);
        return $responseArray;
    }

    /**
     * Metodo que se encarga de crear el formato del reporte
     * @param Array $listToFormat Recibe la lista desde el controlador de reportes
     */
    public function adminHoursFormat($listToFormat)
    {
        $responseArray = array();
        foreach ($listToFormat as $adminHour) {
            $formatArray = array();
            //Comparamos la id del usuario con la id del listToFormat
            foreach ($this->users as $user) {
                if ($adminHour["user_id"] == $user->user_id) {
                    array_push($responseArray, array(
                        "nombre" => $user->user_name,
                        "código" => $user->user_code,
                        "area" => $user->department_prefix,
                        "concepto" => $adminHour["concept_admin"],
                        "horas" => number_format($adminHour["admin_hours"], 2, ",", ".")
                    ));
                }
            }
            //Cargamos el formato al array resultante
            // array_push($responseArray, $formatArray);
        }
        //Importamos el nuevo array
        return $responseArray;
    }
    /**
     * Metodo principal de la clase que se encarga de estructurar todo el esquema del reporte directivo
     * @param Int $type 1: Directivo total, 0 (default): Directivo mensual
     */
    public function directiveMonthReport($type = 0)
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
                $startDate = $type == 0 ? $hours["mes"] . "-01" : $this->startDate;
                $endDate = $type == 0 ? $hours["mes"] . "-" . date("t", strtotime($startDate)) : $this->endDate; //Obtenemos el ultimo dia del mes
                //Capturamos la referencia de horas
                $refHours = $this->getRefTotal($startDate, $endDate, $user); //Referencia total de horas
                $adminPer = ($hours["admin_hours"] * 100) / ($refHours == 0 ? 1 : $refHours);
                $proyPer = ($hours["proj_hours"] * 100) / ($refHours == 0 ? 1 : $refHours);
                $totalPer = ($totalHours * 100) / ($refHours == 0 ? 1 : $refHours);
                $nivelPer = $user->department_prefix == 'ADMON' || $user->department_prefix == 'COP' ? $refHours : $user->nivel_percen;
                //Hacemos push para el formato
                array_push($formatArray, array(
                    "order_user" => $user->user_id,
                    "nombre" => $user->user_name,
                    "area" => $user->department_prefix,
                    "nivel" => $user->nivel_description,
                    "mes" => $hours["mes"],
                    "percen_carg" => $user->department_prefix == 'ADMON' || $user->department_prefix == 'COP' ? 0 : $user->nivel_percen,
                    "eval" => floatval($user->nivel_percen) >= ($user->department_prefix == 'ADMON' || $user->department_prefix == 'COP' ? $hours["admin_hours"] : $proyPer) ? "DE" : "E",
                    "proy_hours" => number_format($hours["proj_hours"], 2, ",", "."),
                    "percen_proy" => number_format($proyPer, 2, ",", "."),
                    "admin_hours" => number_format($hours["admin_hours"], 2, ",", "."),
                    "percen_admon" => number_format($adminPer, 2, ",", "."),
                    "total_hours" => number_format($totalHours, 2, ",", "."),
                    "percen_total" => number_format($totalPer, 2, ",", "."),
                    "ref_total" => number_format($refHours, 2, ",", "."),
                    "estatus" => $user->status_id,
                    "fecha_egreso" => $user->departure_date == null ? $user->status_prefix : $user->departure_date,
                    "order" => $user->nivel_id,
                    "department_order" => $user->department_order
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
            1,
            2
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
            2,
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
        $DTOEnd = $userInfo->departure_date !== null
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
