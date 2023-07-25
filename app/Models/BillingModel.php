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
}
