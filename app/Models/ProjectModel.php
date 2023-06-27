<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProjectModel extends Model
{
    private static $lastAssignedId = []; #Captura el ultimo ID de los departamentos asignados
    private static $lastInsertId = 0; #Se asigna al momento de sacar el last_insert del $lastAssignedId[]
    /**
     * Metodo que se encarga de abstraer de la base de dato todos los managers, o socios activos
     * @param int $status Que estado debe tener el usuario para el retorno
     * @param array $positionsArray Array numerico que indica que numero de cargos se debe mostrar de usuarios
     */
    public static function getAllAssociated($status, $positionsArray)
    {
        return DB::table('users')
            ->whereIn('position_id', $positionsArray)
            ->where([['status_id', '=', $status]])
            ->select(DB::raw('CONCAT(first_name," ",second_name," ",first_surname," ",second_surname) AS user_name'), 'user_id', 'position_id', 'department_id')
            ->get();
    }

    /**
     * Metodo que prepara la información para la grilla de control de horas
     * @param Int $userId EL id del usaurio
     */
    public static function getRegisterHoursInfo($userId)
    {
        //Horas asignadas al usuario actual (El que inicio sesion)
        $getUsersAsign = DB::table('projects_users_assigned')
            ->join("projects_departments_assigned", "projects_departments_assigned.department_assigned_id", "=", "projects_users_assigned.department_assigned_id")
            ->join("projects", "projects.project_id", "=", "projects_users_assigned.project_id")
            ->join("clients", "clients.client_id", "=", "projects.client_id")
            ->where([
                ["projects_users_assigned.user_id", "=", $userId],
                ["projects.status_id", "=", 1]
            ])
            ->get();

        //Conceptos de horas administrativas con status 1
        $getConceptHour = DB::table('control_concept_admin_hours')
            ->where("status_id", "=", 1)
            ->get();

        return array("hoursPerCharge" => $getUsersAsign, "conceptHour" => $getConceptHour);
    }

    /**
     * Metodo que devuelve una lista de todos los usuarios por departamento de acuerdo al id del departamento asignado
     * @param Int $departmentAssignedId Captura el id del departamento asignado en la tabla projects_departments_assigned
     */
    public static function getUserPerDepartment($departmentAssignedId)
    {
        $getDepartmentId = DB::table('projects_departments_assigned')
            ->where('department_assigned_id', '=', $departmentAssignedId)
            ->first();

        //Devolvemos los usuarios asignados al departamento. Con cargo menor a supervisor y el gerente asignado a esa division
        #Usuarios
        $getUsers = DB::table('users')
            ->where('department_id', '=', $getDepartmentId->department_id)
            ->select(DB::raw('CONCAT(first_name," ",second_name," ",first_surname," ",second_surname) AS user_name'), 'user_id', 'position_id', 'department_id')
            ->get();

        #Proyecto
        $getProject = DB::table("projects")
            ->join('projects_departments_assigned', 'projects.project_id', '=', 'projects_departments_assigned.project_id')
            ->join('clients', 'projects.client_id', '=', 'clients.client_id')
            ->where('projects_departments_assigned.department_assigned_id', '=', $departmentAssignedId)
            ->first();

        #Proyecto Analista
        $getAnalyst = DB::table("projects_users_assigned")
            ->join('users', 'users.user_id', '=', 'projects_users_assigned.user_id')
            ->where([
                ["department_assigned_id", "=", $departmentAssignedId],
                ["users.department_id", "=", $getDepartmentId->department_id]
            ])
            ->select(
                DB::raw('CONCAT(users.first_name," ",users.second_name," ",users.first_surname," ",users.second_surname) AS user_name'),
                'projects_users_assigned.user_assigned_id',
                'projects_users_assigned.project_id',
                'projects_users_assigned.department_assigned_id',
                'projects_users_assigned.user_id',
                'projects_users_assigned.assigned_hours',
                'projects_users_assigned.status_id'
            )
            ->get();

        #Acoplamos la informacion en un array asociativo
        $prepareInfo = array(
            "users" => $getUsers,
            "project" => $getProject,
            "analyst" => $getAnalyst
        );
        return $prepareInfo;
    }

    /**
     * Metodo que se encarga de abstraer de la base de datos toda la informacion del proyecto, gerentes asignados, horas adicionales y montos adicionales
     * @param int $projectId El ID del proyecto, en la vista seria el codigo
     * @return array Retorna un array asociativo donde cada fila es respectivamente: info del proyecto, departaments, horas adicionales y montons adicionales
     */
    public static function getProjectInfo($projectId)
    {
        //Estructuramos el array para el response con la informacion del proyecto
        return array(
            "project" => DB::table('projects')->where('project_id', '=', $projectId)->first(),
            "departments" => DB::table('projects_departments_assigned')->where('project_id', "=", $projectId)->get(),
            //Montos adicionales
            "additionalValue" => DB::table('projects_additional_value')
                ->where([
                    ['project_id', '=', $projectId],
                    ['status_id', '=', 1]
                ])->get(),
            "lastValue" => DB::table('projects_additional_value')->orderBy('value_id', 'desc')->value('value_id'),
            //Horas adicionales
            "additionalHours" => DB::table('projects_additional_hours')
                ->join('projects_departments_assigned', 'projects_additional_hours.department_assigned_id', '=', 'projects_departments_assigned.department_assigned_id')
                ->where([
                    ['projects_departments_assigned.project_id', '=', $projectId],
                    ['projects_additional_hours.status_id', '=', 1]
                ])
                ->get(),
            "lastHour" => DB::table('projects_additional_hours')->orderBy('hours_id', 'desc')->value('hours_id')
        );
    }

    /**
     * Metodo que crea o actualiza un cliente
     * @param Array $dataRequest Los parametros a ejecutar en el procedure
     * @param String $typeControl Tipo de operacion = create, o update
     * @return Array Retorna un array response con la estrucutra {response:boolean,message:string|object}
     */
    static public function controlProjects($dataRequest, $typeControl)
    {
        //Separamos el array
        $paramsToProcedure = $dataRequest["project"];
        //Añadimos el projectId si estamos actualizando
        if ($typeControl == 'update') {
            array_push($paramsToProcedure, $dataRequest["projectId"]);
            $valuesArray = $dataRequest["additionalValues"]; //Corresponse a los montos adicionales
            $hoursArray = $dataRequest["additionalHours"]; //Corresponse a las horas adicionales
        }

        $managersArray = $dataRequest["departments"]; //Corresponde a los departamentos

        //Separamos el tipo de proceso
        switch ($typeControl) {
            case 'create':
                DB::select('call sp_new_projects(?,?,?,?,?,?,?,?,?,?,?,?,@response)', $paramsToProcedure);
                break;
            case 'update':
                DB::select('call sp_update_projects(?,?,?,?,?,?,?,?,?,?,?,?,?,@response)', $paramsToProcedure);
                break;
        }
        //Capturamos el JSON
        $GetResponse = DB::select('SELECT @response AS JsonResponse');
        $response = json_decode($GetResponse[0]->JsonResponse, true);

        //Revisamos si efectuo correctamente la consulta y guardamos el manager dependiendo del estado
        if ($response['response'] && $typeControl == 'create') {
            //Almacenamos el ultimo Id
            $queryLastId = DB::select('SELECT project_id as maxID FROM projects ORDER BY project_id DESC LIMIT 1');
            $getLastProjectId = $queryLastId[0]->maxID;
            //Cargamos en proyecto divisiones
            foreach ($managersArray as $posicion => $department) {
                DB::table('projects_departments_assigned')
                    ->insert([
                        "department_id" => $department['departmentId'],
                        "project_id" => $getLastProjectId,
                        "manager_id" => $department['selectManager'],
                        "hours_assigned" => $department['hoursAssigned']
                    ]);
            }
        }

        //Update
        if ($response['response'] && $typeControl == 'update') {
            # Departamentos
            self::prepareDepartmentUpdate($managersArray, $dataRequest["projectId"]);

            #Montos adicionales
            self::prepareValuesUpdate($valuesArray, $dataRequest["projectId"]);

            #Horas adicionales
            self::prepareHoursUpdate($hoursArray, $dataRequest["projectId"]);
        }

        return $response;
    }

    /**
     * Metodo que configura la fecha en formato YYYY-mm-dd
     * @param String $dateToConver Captura la fecha en formato String
     * @return Date Retorna la fecha en formato YYYY-mm-dd o null en caso de string vacio o null
     */
    private static function convertDate($dateToConver)
    {
        return $dateToConver != null || $dateToConver != ''
            ? date("Y-m-d", strtotime($dateToConver))
            : null;
    }

    /**
     * Metodo que se encarga de actualizar la información de los departamentos
     * @param array $managersArray captura la información sobre las divisiones sumnistradas por el frontEnd
     * @param int $projectId captura el id del proyecto a modificar
     */
    private  static function prepareDepartmentUpdate($managersArray, $projectId)
    {
        //Abstraemos de la base de datos los departamentos asignados
        $departmentsAssigned = DB::table('projects_departments_assigned')
            ->where('project_id', '=', $projectId)
            ->get();
        foreach ($managersArray as $posicion => $department) {
            //recorremos el array de la base de datos
            if (isset($departmentsAssigned[$posicion])) {
                $idDepartmentAssigned = $departmentsAssigned[$posicion]->department_assigned_id; #Capturamos el id de esa fila
                #Si existe la fila, actualizamos en la base de datos
                DB::table('projects_departments_assigned')
                    ->where('department_assigned_id', '=', $idDepartmentAssigned)
                    ->update([
                        "department_id" => $department['departmentId'],
                        "project_id" => $projectId,
                        "manager_id" => $department['selectManager'],
                        "hours_assigned" => $department['hoursAssigned']
                    ]);
            } else {
                #Si no existe, hacemos un insert en la base de datos
                array_push(self::$lastAssignedId, array(
                    "department_id" => $department['departmentId'],
                    "last_insert" => DB::table('projects_departments_assigned')
                        ->insertGetId([
                            "department_id" => $department['departmentId'],
                            "project_id" => $projectId,
                            "manager_id" => $department['selectManager'],
                            "hours_assigned" => $department['hoursAssigned']
                        ])
                ));
            }
        }

        //Elimina los campos mayores al tamaño del array de la solictud es decir $managersArray.length < BDDArray.length
        if (count($managersArray) < count($departmentsAssigned)) {
            foreach ($departmentsAssigned as $cursor => $department) {
                #Si el cursor supera al tamaño del array del request, borra el registro de la BDD
                if (($cursor + 1) > count($managersArray)) {
                    DB::table('projects_departments_assigned')
                        ->where('department_assigned_id', '=', $department->department_assigned_id)
                        ->delete();
                }
            }
        }
    }

    /**
     * Metodo que controla la actualizacion de montos adicionales
     * @param array $valuesArray Captura un array de los cambios sobre los montos adicionales
     * @param int $projectId Id del proyecto al cual hace referencia
     */
    private static function prepareValuesUpdate($valuesArray, $projectId)
    {
        $valuesAdditional = DB::table('projects_additional_value')
            ->where('project_id', '=', $projectId)
            ->get();
        //Procedemos a recorrer la informacion
        foreach ($valuesArray as $posicion2 => $value) {
            # Verificamos que exista la fila
            if (isset($valuesAdditional[$posicion2])) {
                $idValueAssigned = $valuesAdditional[$posicion2]->value_id; #Capturamos la fila
                #Si el status es inactivo, lo elimina de la tabla. Si es activo, no actualiza la tabla
                if ($value["status_id"] === 2) {
                    DB::table('projects_additional_value')
                        ->where('value_id', '=', $idValueAssigned)
                        ->delete();
                }
            } else {
                #Si no existe, la insertamos, unicamente los valores activos
                if ($value["status_id"] === 1) {
                    DB::table('projects_additional_value')
                        ->insert([
                            "project_id" => $projectId,
                            "aditional_project_value" => $value["aditional_project_value"],
                            "register_date" => self::convertDate($value["register_date"]),
                            "status_id" => 1
                        ]);
                }
            }
        }
    }
    /**
     * Metodo que controla el proceso de actualizacion de horas adicionales
     * @param $hoursArray Captura el array de los cambios realizados para horas adicionales
     * @param $projectId Id del proyecto al que se hace referencia
     */
    private static function prepareHoursUpdate($hoursArray, $projectId)
    {
        $hoursAdditional = DB::table('projects_additional_hours')
            ->join('projects_departments_assigned', 'projects_additional_hours.department_assigned_id', '=', 'projects_departments_assigned.department_assigned_id')
            ->where('projects_departments_assigned.project_id', '=', $projectId)
            ->get();
        //Procedemos a recorrer la informacion
        foreach ($hoursArray as $posicion3 => $hour) {
            // Buscamos el último departamento asignado que coincida con el id del departamento de la hora actual
            $lastDepartmentAssigned = array_filter(self::$lastAssignedId, function ($departmentAssigned) use ($hour) {
                return $departmentAssigned["department_id"] === $hour["department_id"];
            });
            // Si lo encontramos, asignamos el valor de last_insert a self::$lastInsertId
            if (!empty($lastDepartmentAssigned)) {
                self::$lastInsertId = reset($lastDepartmentAssigned)["last_insert"];
            }

            # Verificamos que exista la fila
            if (isset($hoursAdditional[$posicion3])) {
                $idHourAssigned = $hoursAdditional[$posicion3]->hours_id; #Capturamos la fila
                #Si el status es inactivo, lo elimina de la tabla. Si es activo, no actualiza la tabla
                if ($hour["status_id"] === 2) {
                    DB::table('projects_additional_hours')
                        ->where('hours_id', '=', $idHourAssigned)
                        ->delete();
                }
            } else {
                #Si no existe, la insertamos, unicamente los valores activos
                if ($hour["status_id"] === 1) {
                    DB::table('projects_additional_hours')
                        ->insert([
                            "department_assigned_id" => self::$lastInsertId,
                            "additional_hour" => $hour["additional_hour"],
                            "register_date" => self::convertDate($hour["register_date"]),
                            "status_id" => 1
                        ]);
                }
            }
        }
    }

    /**
     * Metodo que se encarga de actualizar los valores en la tabla projects_users_assigned
     * @param $asignArray almacena el array configurado en el frontEnd
     * @param $departmentAssignedId Captura el id del departamento asignado (department_assigned_id)
     */
    public static function updateAsignHours($asignArray, $departmentAssignedId)
    {
        $assignHoursUsers = DB::table('projects_users_assigned')
            ->where('department_assigned_id', '=', $departmentAssignedId)
            ->get();
        //Obtenemos el codigo del proyecto
        $getProjectId = $assignHoursUsers[0]->project_id;
        //Procedemos a recorrer la informacion
        foreach ($asignArray as $posicion2 => $value) {
            # Verificamos que exista la fila
            if (isset($assignHoursUsers[$posicion2])) {
                $idHoursAssigned = $assignHoursUsers[$posicion2]->user_assigned_id; #Capturamos la fila
                #Solo actualiza si hoursAssigned es distinto de 0
                if ($value["hoursAssigned"] !== 0) {
                    DB::table('projects_users_assigned')
                        ->where('user_assigned_id', '=', $idHoursAssigned)
                        ->update([
                            "user_id" => $value["idUser"],
                            "assigned_hours" => $value["hoursAssigned"]
                        ]);
                }
            } else {
                #Si no existe, la insertamos, unicamente las horas mayores a 0
                if ($value["hoursAssigned"] !== 0) {
                    DB::table('projects_users_assigned')
                        ->insert([
                            "project_id" => $getProjectId,
                            "department_assigned_id" => $departmentAssignedId,
                            "user_id" => $value["idUser"],
                            "assigned_hours" => $value["hoursAssigned"],
                            "status_id" => 1
                        ]);
                }
            }
        }

        return array("response" => true, "message" => "Horas asignadas existosamente");
    }

    /**
     * Metodo que se encarga de devolver la información de las horas cargadas en el sistema, en funcion de un intervalo de fechas
     * @param Array $dateInfo Array asociativo donde la primera posicion almacena la fecha inicial y la ultima la final
     * El nombre de las llaves es respectivamente user_id, register_date
     */
    public static function getAllLoadHours($dateInfo)
    {
        $getLastIndex = count($dateInfo) - 1;
        //Acomodamos las fechas antes de enviar al procedimiento almacenado
        $startDay = $dateInfo[0]["register_date"];
        $endDay = $dateInfo[$getLastIndex]["register_date"];
        $userId = $dateInfo[0]["user_id"];

        //Hora a proyectos
        $getProjectsHours = DB::select('call sp_get_hours(?,?,?,?)', [$userId, $startDay, $endDay, 1]);

        //Horas administrativas
        $getAdminHours = DB::select('call sp_get_hours(?,?,?,?)', [$userId, $startDay, $endDay, 2]);

        return array(
            "date_interval" => $dateInfo,
            "projects_hours" => $getProjectsHours,
            "admin_hours" => $getAdminHours
        );
    }
}
