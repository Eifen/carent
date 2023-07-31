<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BillingModel extends Model
{
    /**
     * Metodo que retorna un array asociativo con la informacion de los conceptos de factura, los porcentajes de iva, retencion, e ISLR con status 1
     */
    static function getBillingParams()
    {
        return array(
            "conceptType" => DB::table('billings_concepts')->where('status_id', '=', 1)->get(),
            "ivaInfo" => DB::table('billings_iva')->where('status_id', '=', 1)->get(),
            "retIvaInfo" => DB::table('billings_retention_iva')->where('status_id', '=', 1)->get(),
            "islrInfo" => DB::table('billings_deduction_islr')->where('status_id', '=', 1)->get(),
        );
    }

    /**
     * Metodo que actualiza o crea una fila en la tabla billings a traves de procedure
     * @param Array $params corresponde al array de parametros a pasarle al procedure
     * @param String $type corresponde al string del tipo de operacion, si update o create
     */
    static function controlBilling($params, $type)
    {
        switch ($type) {
            case 'update':
                DB::select('call sp_update_billing(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@response)', $params);
                break;

            case 'create':
                DB::select('call sp_create_billing(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@response)', $params);
                break;
        }

        //Convertimos el valor
        $getResponse = DB::select('SELECT @response AS JsonResponse');
        return json_decode($getResponse[0]->JsonResponse, true);
    }
}
