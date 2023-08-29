<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReportsModel extends Model
{
    /**
     * Metodo que se encarga de devolver un reporte
     * @param String $reportTarget Tipo de reporte a seleccionar, debe estar asociado a una vista
     */
    public static function getReport($reportTarget)
    {
        return DB::table('vw_reports_' . $reportTarget)
            ->get();
    }
}
