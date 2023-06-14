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
            ->select(DB::raw('CONCAT(first_name," ",second_name," ",first_surname," ",second_surname) AS user_name'),'user_id','position_id','department_id')
            ->get();
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
        $paramsToProcedure = $dataRequest;
        $managersArray = $dataRequest[10]; //Corresponde a los departamentos

        unset($paramsToProcedure[10]);
        //Separamos el tipo de proceso
        switch ($typeControl) {
            case 'create':
                DB::select('call sp_new_projects(?,?,?,?,?,?,?,?,?,?,?,?,@response)',$paramsToProcedure);
                break;
            case 'update':
                DB::select('call sp_update_projects(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@response)',$paramsToProcedure);
                break;
        }
        //Capturamos el JSON
        $GetResponse = DB::select('SELECT @response AS JsonResponse');
        $response = json_decode($GetResponse[0]->JsonResponse,true);

        //Revisamos si efectuo correctamente la consulta y guardamos el manager dependiendo del estado
        if($response['response'] && $typeControl == 'create'){
            //Almacenamos el ultimo Id
            $queryLastId = DB::select('SELECT COUNT(LAST_INSERT_ID()) AS maxID FROM projects');
            $getLastProjectId = $queryLastId[0]->maxID;
            //Cargamos en proyecto divisiones
            foreach ($managersArray as $posicion => $department) {
                DB::table('projects_departments_assigned')
                ->insert([
                    "department_id" => $department['departmentId'],
                    "project_id" => $getLastProjectId,
                    "manager_id" => $department['manager_id'],
                    "hours_assigned" => $managersArray['hoursAssigned']
                ]);
            }
        }

        return $response;
    }
}
