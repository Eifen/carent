<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\HoursModel;
use Illuminate\Support\Facades\DB;

class HoursController extends Controller
{
    protected $projectAssignedId = 0; //Propiedad que almacena el id del proyecto asignado a cargar
    protected $adminAssignedId = 0; //Propiedad que almacena el id del concepto administrativo asignado a cargar
    /**
     * Metodo que se encarga de preparar la informacion para la carga de horas
     * @param Request $registerRequest parametro que captura la informacion enviada por RegisterIndex.js
     * @return Response Retona un objeto en formato response
     */
    public function prepareAddHour(Request $registerRequest)
    {
        //Distribuimos la informacion
        $this->projectAssignedId = $registerRequest->input("assignedId");
        $arrayProjectInfoDTO = $registerRequest->input("multiSelectProjectInfo");
        $arrayListHours = $registerRequest->input("listHour");
        $userId = Session::get('userId');
        $date = date('Y-m-d', strtotime($registerRequest->input("selectInfo")[0]["day"]));
        $hourValue = floatval($registerRequest->input("selectInfo")[0]["value"]);

        //Ubicamos el proyecto en el multiselect
        $positionProjectInfo = array_search($this->projectAssignedId, array_column($arrayProjectInfoDTO, "projectAssignedId"));

        $getLoadInfo = $this->findHour($arrayListHours, array(
            "userId" => $userId,
            "date" => $date,
            "type" => "project"
        ));

        //Verificamos la cantidad de horas
        $totalHours = ($arrayProjectInfoDTO[$positionProjectInfo]["hoursLoad"] - $getLoadInfo[0]) + $hourValue;

        //Si es mayor al total, retorna un error
        if ($totalHours > $arrayProjectInfoDTO[$positionProjectInfo]["hoursAssigned"]) {
            return response(array(
                "response" => false,
                "message" => array(
                    "newList" => DB::select('call sp_get_hours(?,?)', [$userId, 1]),
                    "error" => "No se pueden cargar mas horas a este proyecto"
                )
            ), 200);
        }

        //Paso la validacion, se agrega a la base de datos
        $prepareHourInfo = array(
            "user_id" => $userId,
            "register_date" => $date,
            "register_hour" => $hourValue,
            "project_load_observation" => $registerRequest->input("selectInfo")[1], //La posicion [1] corresponde a la observacion
            "user_assigned_id" => $this->projectAssignedId,
            "status_load_id" => 2 //
        );

        //Calculamos la nueva diferencia
        $totalDiff = $arrayProjectInfoDTO[$positionProjectInfo]["hoursAssigned"] - $totalHours;

        //Enviamos la informacion
        $response = array(
            "response" => true,
            "message" => array(
                "hour_diff" => $totalDiff,
                "hours_response" => HoursModel::addHour($prepareHourInfo, $getLoadInfo[1])
            )
        );

        return response($response, 200);
    }

    /**
     * Metodo que prepara y envia la informacion de carga de horas administrativas
     * @param Request $registerRequest Almacena los parametros enviados desde RegisterIndex.js mediante axios
     * @return Response Retorna un objeto en formato de response
     */
    public function prepareAddAdminHour(Request $registerRequest)
    {
        //Asignamos los valores
        $this->adminAssignedId = $registerRequest->input('assignedId');
        $userId = Session::get('userId');
        $date = date('Y-m-d', strtotime($registerRequest->input("selectInfo")[0]["day"]));
        $hourValue = floatval($registerRequest->input("selectInfo")[0]["value"]);
        $arrayListHours = $registerRequest->input("listHour");

        $getLoadInfo = $this->findHour($arrayListHours, array(
            "userId" => $userId,
            "date" => $date,
            "type" => "admin"
        ));

        //Preparamos la informacion
        $prepareHourInf = array(
            "user_id" => $userId,
            "register_date" => $date,
            "register_hour" => $hourValue,
            "admin_load_observation" => $registerRequest->input("selectInfo")[1], //La posicion [1] corresponde a la observacion
            "admin_hours_id" => $this->adminAssignedId,
            "status_load_id" => 1 //Si hay cambios o se inserta una nueva hora, su estado sera por aprobar
        );

        //Enviamos la informacion
        $response = HoursModel::addAdminHour($prepareHourInf, $getLoadInfo[1]);

        return response($response, 200);
    }

    /**
     * Metodo que controla la aprobacion y el rechazo de horas administrativas
     * @param Request $hourRequest Captura la solicitud del ProjectValidateIndex.js y controla sus parametros
     */
    public function prepareControlHour(Request $hourRequest)
    {
        $prepareUpdate = array(
            Session::get('userId'),
            $hourRequest->input('operation'), //1 es aprobar, 2 rechazar, 3 cancelar
            date("Y-m-d"),
            $hourRequest->input('loadId'),
            $hourRequest->input('loadStatus'),
            ConfigController::GetIpUser()
        );
        //Una vez preparada la informacion se devuelve una respuesta
        return response(HoursModel::controlHours($prepareUpdate), 200);
    }
    //Espacio para borrar una hora sea proyecto o administrativa
    /**
     * Elimina una hora sea administrativa  o de proyectos
     * @param Request $hourRequest Captura el HTTP request proveniente del axios
     * @param Response devuelve un array con la nueva informacion de las horas
     */
    public function prepareDeleteHour(Request $hourRequest)
    {
        //Acomodamos el request
        $arrayListHours = $hourRequest->input("listHour");
        $date = date('Y-m-d', strtotime($hourRequest->input("date")));

        //Capturamos el ID
        switch ($hourRequest->input('type')) {
            case 2:
                $this->projectAssignedId = $hourRequest->input("assignedId");
                $getLoadInfo = $this->findHour($arrayListHours, array(
                    "userId" => Session::get('userId'),
                    "date" => $date,
                    "type" => "project"
                ));
                break;

            case 1:
                $this->adminAssignedId = $hourRequest->input("assignedId");
                $getLoadInfo = $this->findHour($arrayListHours, array(
                    "userId" => Session::get('userId'),
                    "date" => $date,
                    "type" => "admin"
                ));
                break;
        }

        $prepareParams = array(
            Session::get('userId'),
            $hourRequest->input('type'),
            $getLoadInfo[1],
            ConfigController::GetIpUser()
        );

        return response(HoursModel::deleteHour($prepareParams), 200);
    }

    /**
     * Metodo privado que encuentra el ID de referencia de la hora cargada
     * @param mixed $arrayListHours array que captura la informacion de la lista de horas cargadas a proyectos
     * @param mixed $params array asociativo que almacena ["userId"] = El id del usuario, ["date"] = Fecha registrada, ["type"] = tipo de operacion
     * @return tuple Retorna una tupla donde [0] = Si hay una hora cargada en el sistema, sera diferente de 0, [1] = Id hora Cargada
     */
    private function findHour($arrayListHours, $params)
    {
        //Revisamos si el dia esta cargado
        $getHourCharged = 0; #Variable que cambia su valor si hay una hora cargada en el sistema por el usuario en el dia seleccionado
        $getLoadId = 0; #Variable que cambia su valor si hay una hora cargada en el sistema, almacenando su id de carga

        //Proyectos
        if ($params["type"] == "project") {
            foreach ($arrayListHours as $cursor => $hourCharged) {
                if ($hourCharged["user_id"] == $params["userId"] && $hourCharged["register_date"] == $params["date"] && $hourCharged["user_assigned_id"] == $this->projectAssignedId) {
                    //Tiene hora cargada en el dia
                    $getHourCharged = $hourCharged["register_hour"];
                    $getLoadId = $hourCharged["project_hour_id"];
                }
            }
        }

        //Admin
        if ($params["type"] == "admin") {
            foreach ($arrayListHours as $cursor => $hourCharged) {
                if ($hourCharged["user_id"] == $params["userId"] && $hourCharged["register_date"] == $params["date"] && $hourCharged["admin_hours_id"] == $this->adminAssignedId) {
                    //Tiene hora cargada en el dia
                    $getHourCharged = $hourCharged["register_hour"];
                    $getLoadId = $hourCharged["admin_hour_id"];
                }
            }
        }


        return [$getHourCharged, $getLoadId];
    }
}
