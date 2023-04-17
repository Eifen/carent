<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class TotalHorasCargModel extends Model
{

    static function Cargos(){ return DB::table('tbl_cargo_empleado')->get(['id','descripcion']); }// Fin cargos
    static function Divisiones(){ return DB::table('tbl_division')->get(['id','descripcion']); }// Fin divisiones

    /**
     * Metodo que devuelve el formato de Reporte
     * @param mixed $fecha_desde Fecha inicial del intervalo de horas
     * @param mixed $fecha_hasta Fecha final dle intervalo de horas
     * @param mixed $divisiones Array que contiene todas las divisiones o una sola
     * @param mixed $cargos Array que contiene todos los cargos o uno solo
     * @param mixed $empleado String que contiene el dato del empleado. Data opcional (defecto null)
     * @return Array devuelve el reporte de cargabilidad
     */
    static function ReporteActualCargabilidad($fecha_desde,$fecha_hasta,$divisiones, $cargos, $empleado = null){
        //TODO: Comunicarse con procedure Reporte horas y Dias Totales
    }

}
