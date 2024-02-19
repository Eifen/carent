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

    /**
     * Metodo que obtiene la informacion de los dias en un intervalo de fechas
     * @param Date $start_date Fecha inicial
     * @param Date $end_date Fecha final
     */
    public static function getTotalDays($start_date, $end_date)
    {
        DB::select('CALL sp_total_days(?,?,@interval)', [$start_date, $end_date]);
        $getInterval = DB::select('SELECT @interval AS refHours');
        return $getInterval[0]->refHours;
    }

    /**
     * Metodo que se encarga de devolver las horas totales que cargo un usuario sea administrativas o proyectos
     * @param Array $paramsHours Captura los parametros del procedimiento almacenado.
     */
    public static function getRegisterHours($paramsHours)
    {
        $getTotal = DB::select('call sp_report_hours(?,?,?,?,?)', $paramsHours);
        return $getTotal;
    }

    /**
     * Metodo que se encarga de agrupar las personas que no han cargado horas en un intervalo
     * @param Array $paramsDate Recibe los valores de fecha inicial y fecha final del intervalo en las posiciones 0 y 1 respectivamente
     */
    public static function noRegisterHoursPersonal($paramsDate)
    {
        $getList = DB::select('call sp_report_no_register(?,?)', $paramsDate);
        return $getList;
    }

    /**
     * Metodo que devuelve las facturas dependiendo de la fecha ingresada
     */
    public static function billingsReport($paramsDate)
    {
        $getBillings = DB::select('SELECT bs.billing_date, bs.billing_number, cs.bussiness_name, bs.billing_value, ccs.currency_symbol, CONCAT(us.first_name," ",us.first_surname) as partner_name  FROM billings bs
        INNER JOIN projects ps ON bs.project_id = ps.project_id
        INNER JOIN clients cs ON ps.client_id = cs.client_id
        INNER JOIN users us ON ps.partner_id = us.user_id
        INNER JOIN control_currencies ccs ON ps.currency_id = ccs.currency_id
        WHERE bs.billing_date BETWEEN ? AND ?', $paramsDate);

        return $getBillings;
    }

    /**
     * Metodo que devuelve la informacion de proyectos y facturas
     */
    public static function billingsProjReport($paramsDate)
    {
        $getBillings = DB::select('SELECT ps.hiring_date, cs.bussiness_name, ps.project_description, ps.project_value, ccs.currency_symbol, SUM(bs.billing_value) as "billings", ps.status_id FROM billings bs
        INNER JOIN projects ps ON bs.project_id = ps.project_id
        INNER JOIN clients cs ON ps.client_id = cs.client_id
        INNER JOIN control_currencies ccs ON ps.currency_id = ccs.currency_id
        WHERE bs.payment_date IS NOT NULL
        AND ps.closure_date BETWEEN ? AND ? OR ps.closure_date IS NULL
        GROUP BY ps.project_id, ps.hiring_date, cs.bussiness_name, ps.project_description, ps.project_value, ccs.currency_symbol, ps.status_id', $paramsDate);
        return $getBillings;
    }
}
