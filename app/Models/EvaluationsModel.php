<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class EvaluationsModel extends Model
{
    /**
     * Metodo que crea o actualiza una evaluacion
     * @param Array $dataRequest Los parametros a ejecutar en el procedure
     * @param String $typeControl Tipo de operacion = create, o update
     * @return Array Retorna un array response con la estrucutra {response:boolean,message:string|object}
     */
    static public function ControlEvaluations($dataRequest, $typeControl)
    {
        //Separamos el tipo de proceso
        switch ($typeControl) {
            case 'create':
                $response = DB::select('INSERT INTO `evaluations_period`(`evaluation_period`, `evaluation_period_date_from`, `evaluation_period_date_until`, `evaluation_period_description`, `evaluation_period_observation`, `evaluation_type_id`, `evaluation_method_id`, `status_id`) VALUES (?,?,?,?,?,?,?,?)', $dataRequest);
                break;
            case 'update':
                $query = DB::table('evaluations_period')->where('evaluation_period_id', $dataRequest['idperiod'])->update($dataRequest['dats']);
                if ($query > 0) {
                    return true;
                }
                break;
        }

        //Capturamos el JSON
        if (is_array($response)) {
            return array(
                "response" => true,
                "message" => "Periodo creado exitosamente"
            );
        };

        return array(
            "response" => false,
            "message" => "Ha ocurrido un error"
        );
    }
    /**
     * Metodo que devuelve todos los status activos. Campo Id y data_status
     * @param int $Status Captura el tipo de estatus
     * @return Array Retorna un array de todos los sectores
     */
    static public function GetAllDescriptionStatus($Status)
    {
        return DB::table('control_status')
            ->where('status_id', $Status)
            ->get(['data_status', 'status_description']);
    }

    /**
     * Metodo que devuelve todos los periodos activos
     * @param int $status Captura el tipo de periodo
     * @return Array Retorna un array de todos los periodos
     */
    static public function GetAllPeriodos($Status)
    {
        return DB::table('evaluations_period')
            ->get(['evaluation_period_id', 'evaluation_period_description']);
    }


    /**
     * Metodo que retorna la informacion de un usuario evaluado
     * @param Number $user_code almacena el codigo del usuario, el cual es unico
     */
    static public function getInfoEvaluationsReport($user_code)
    {
        return DB::select('call sp_get_evaluations_report(?)', [$user_code]);
        // return DB::select('call sp_get_evaluations_report(?)', [11535]);
    }

    /**
     * Metodo que retorna la informacion de un usuario evaluado
     * @param Number $user_code almacena el codigo del usuario, el cual es unico
     */
    static public function getInfoEvaluationsProject($user_code)
    {
        return DB::select('call sp_get_info_evaluation(?)', [$user_code]);
    }

    // /**
    //  * Metodo que obtiene la data de un usuario evaluado por su codigo
    //  * @param int $codeClient Recibe el codigo para usar como filtro
    //  * @return Array retorna un array asociativo con la información del usuario
    //  */
    // static public function GetUsersPerCode($codeUser)
    // {
    //     return DB::table('users')
    //         ->where('user_code', '=', $codeUser)
    //         ->first();
    // }



    /**
     * Metodo que devuelve todos los metodos de evaluacion (activos o inactivos)
     * @param int $Description Captura el tipo de estatus
     * @return Array Retorna un array de todos los sectores
     */
    static public function GetAllMetodosEvaluacion()
    {
        return DB::table('evaluations_methods')
            ->get(['evaluation_method_id', 'status_description']);
    }

    /**
     * Metodo que devuelve todos los tipos de evaluacion (activos o inactivos)
     * @param int $Description Captura el tipo de estatus
     * @return Array Retorna un array de todos los sectores
     */
    static public function GetAllTiposEvaluacion()
    {
        return DB::table('evaluations_types')
            ->get(['evaluation_type_id', 'evaluation_type_description']);
    }

    /**
     * Metodo que devuelve todos las fechas desde de periodos (activos o inactivos)
     * @param int $Description Captura el tipo de estatus
     * @return Array Retorna un array de todos los sectores
     */
    static public function GetAllPeriodDateFrom()
    {
        return DB::table('evaluations_period')
            ->get(['evaluation_period_id', 'evaluation_period_date_from']);
    }




    //////////////////////////////////////////////////////////////////////

    protected $table = 'evaluations_doc';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'codigo',
        'documento'
    ];

    static public function datos()
    {
        return DB::table('evaluations_doc')->get();
    }
}
