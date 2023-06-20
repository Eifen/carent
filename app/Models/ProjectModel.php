<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProjectModel extends Model
{
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
            "additionalValue" => DB::table('projects_additional_value')
                ->where([
                    ['project_id', '=', $projectId],
                    ['status_id', '=', 1]
                ])->get(),
            "additionalHours" => DB::table('projects_additional_hours')
                ->join('projects_departments_assigned', 'projects_additional_hours.department_assigned_id', '=', 'projects_departments_assigned.department_assigned_id')
                ->where([
                    ['projects_departments_assigned.project_id', '=', $projectId],
                    ['projects_additional_hours.status_id', '=', 1]
                ])
                ->get()
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
        $managersArray = $dataRequest["departments"]; //Corresponde a los departamentos

        //Separamos el tipo de proceso
        switch ($typeControl) {
            case 'create':
                DB::select('call sp_new_projects(?,?,?,?,?,?,?,?,?,?,?,?,@response)', $paramsToProcedure);
                break;
            case 'update':
                DB::select('call sp_update_projects(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@response)', $paramsToProcedure);
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

        return $response;
    }
}
