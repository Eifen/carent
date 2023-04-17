<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class TotalHorasCargModel extends Model
{
    //Propiedades
    protected $getUsersPerDivision = []; //Almacena todos los usuarios por divisiones en array distintos
    protected $fecha_ingreso = ""; //Almacena la fecha ingreso del usuario seleccionado en formato date('Y-m-d')
    protected $fecha_egreso = ""; //Almacena la fecha egreso del usuario seleccionado en formato date('Y-m-d')
    protected $totalHorasEmpleado = 0; //Number que almacena las horas totales entre dos fechas para un empleado
    protected $totalHoras = 0; //Number que almacena las horas totales entre dos fechas
    protected $rangoFechasUsers = []; /*
    Array que almacena todas las horas totales en funcion al empleado. Formato:
    [['id' => idUser, 'horasTotales' => totalHorasEmpleado],[...]]*/

    static function Cargos()
    {
        return DB::table('tbl_cargo_empleado')->get(['id', 'descripcion']);
    } // Fin cargos
    static function Divisiones()
    {
        return DB::table('tbl_division')->get(['id', 'descripcion']);
    } // Fin divisiones

    /**
     * Metodo que devuelve el formato de Reporte
     * @param mixed $fecha_desde Fecha inicial del intervalo de horas
     * @param mixed $fecha_hasta Fecha final dle intervalo de horas
     * @param mixed $divisiones Array que contiene todas las divisiones o una sola
     * @param mixed $cargos Array que contiene todos los cargos o uno solo
     * @param mixed $empleado String que contiene el dato del empleado. Data opcional (defecto null)
     * @return Array devuelve el reporte de cargabilidad
     */
    function ReporteActualCargabilidad($fecha_desde, $fecha_hasta, $divisiones, $empleado = null)
    {
        //TODO: Comunicarse con procedure Reporte horas y Dias Totales
        $this->totalHoras = $this->GetHorasTotales($fecha_desde, $fecha_hasta);

        for ($cursor = 0; $cursor < count($divisiones); $cursor++) {
            # LLamamos al método estatico
            $this->getUsersPerDivision[$cursor] = $this->GetUsersByDivisionId($divisiones[$cursor]->id);
            //Creamos el array de usuarios dependiendo de la division
        }

        //Comparamos las fechas de cada usuario
        $contadorFechas = 0;
        for ($cursor2 = 0; $cursor2 < count($this->getUsersPerDivision); $cursor2++) {
            //Agrupamos la fecha de ingreso
            foreach ($this->getUsersPerDivision[$cursor2] as $column => $valor) {
                //Fecha ingreso
                !is_null($valor->fecha_ingreso)
                    ? $this->fecha_ingreso = date($valor->fecha_ingreso)
                    : $this->fecha_ingreso = date('Y-m-d', PHP_INT_MIN);

                //Fecha de egreso
                !is_null($valor->fecha_egreso)
                    ? $this->fecha_egreso = date($valor->fecha_egreso)
                    : $this->fecha_egreso = date('Y-m-d', PHP_INT_MAX);

                $this->rangoFechasUsers[$cursor2] = [$valor->fecha_ingreso,$valor->fecha_egreso];

                //Comparamos para calcular las horas totales en función de cada empleado
                // switch (true) {
                //         //Ingreso despues del intervalo inicial
                //     case $fecha_desde < $this->fecha_ingreso:
                //         $this->totalHorasEmpleado = $this->GetHorasTotales($this->fecha_ingreso, $fecha_hasta);
                //         break;
                //         //Egreso antes del intervalo final
                //     case $fecha_hasta > $this->fecha_egreso:
                //         $this->totalHorasEmpleado = $this->GetHorasTotales($fecha_desde, $this->fecha_egreso);
                //         break;
                //         //Ingreso despues y egreso antes de los intervalos
                //     case $fecha_desde < $this->fecha_ingreso && $fecha_hasta > $this->fecha_egreso:
                //         $this->totalHorasEmpleado = $this->GetHorasTotales($this->fecha_ingreso, $this->fecha_egreso);
                //         break;
                //         //Su ingreso y egreso esta fuera de las intervalos
                //     default:
                //         $this->totalHorasEmpleado = $this->totalHoras;
                //         break;
                // }
            }
        }

        return $this->rangoFechasUsers;
    }

    /**
     * Metodo que genera el reporte de horas de cargabilidad
     * @param mixed $fecha_desde Inicio del intervalo del reporte
     * @param mixed $fecha_hasta Fin del intervalo del reporte
     * @param mixed $empleado String que contiene el nombre de un empleado. Por defecto es null
     * @return Array retorna un array con el reporte ya generado
     */
    protected  function MakeReport($fecha_desde, $fecha_hasta, $empleado  = null)
    {
        $reportDTO = []; //Objeto de transferencia de data
        $userDTOArray = $this->getUsersPerDivision; //Objeto de transferencia para users

        if ($empleado != null) {
            $separarArray = explode(" ", $empleado); //[0] = nombre_1,[1] = nombre_2,[2] = apellido_1, [3] = apellido_2

            //Creamos un nuevo array depende de la longitud del array en el explode y del nombre introducido
            switch (count($separarArray)) {
                case 1:
                    $userDTOArray = array_filter($this->getUsersPerDivision, function ($row) use ($separarArray) {
                        return strpos($row['nombre_1'], $separarArray[0]) !== false;
                    });
                    break;
                case 2:
                    $userDTOArray = array_filter($this->getUsersPerDivision, function ($row) use ($separarArray) {
                        return strpos($row['nombre_1'], $separarArray[0]) !== false ||
                            strpos($row['nombre_2'], $separarArray[1]) !== false ||
                            (strpos($row['nombre_1'], $separarArray[0]) !== false &&
                                strpos($row['nombre_2'], $separarArray[1]) !== false);
                    });
                    break;
                case 3:
                    $userDTOArray = array_filter($this->getUsersPerDivision, function ($row) use ($separarArray) {
                        return strpos($row['nombre_1'], $separarArray[0]) !== false ||
                            strpos($row['nombre_2'], $separarArray[1]) !== false ||
                            strpos($row['apellido_1'], $separarArray[2]) !== false ||
                            (strpos($row['nombre_1'], $separarArray[0]) !== false &&
                                strpos($row['nombre_2'], $separarArray[1]) !== false &&
                                strpos($row['apellido_1'], $separarArray[2]) !== false);
                    });
                    break;

                default:
                    $userDTOArray = array_filter($this->getUsersPerDivision, function ($row) use ($separarArray) {
                        return strpos($row['nombre_1'], $separarArray[0]) !== false ||
                            strpos($row['nombre_2'], $separarArray[1]) !== false ||
                            strpos($row['apellido_1'], $separarArray[2]) !== false ||
                            strpos($row['apellido_2'], $separarArray[3]) !== false ||
                            (strpos($row['nombre_1'], $separarArray[0]) !== false &&
                                strpos($row['nombre_2'], $separarArray[1]) !== false &&
                                strpos($row['apellido_1'], $separarArray[2]) !== false &&
                                strpos($row['apellido_2'], $separarArray[3]) !== false);
                    });
                    break;
            }
        }

        #Seccionamos el código dependiendo si existe empleado o no
        for ($cursor = 0; $cursor < count($userDTOArray); $cursor++) {
            #Nombre
            $NombreCompleto = implode(" ", [
                $userDTOArray[$cursor]['nombre_1'],
                $userDTOArray[$cursor]['nombre_2'],
                $userDTOArray[$cursor]['Apellido_1'],
                $userDTOArray[$cursor]['Apellido_2'],
            ]);
            #Horas Cargables
            $HorasProy = intval(self::GetHorasProyAdmin(
                $fecha_desde,
                $fecha_hasta,
                $userDTOArray[$cursor]['id'],
                2
            ), 10);

            #Horas No Cargables
            $HorasAdmon = intval(self::GetHorasProyAdmin(
                $fecha_desde,
                $fecha_hasta,
                $userDTOArray[$cursor]['id'],
                1
            ), 10);

            #Horas totales
            $HoraTotal = $HorasAdmon + $HorasProy;

            #Referencia Horas
            $ReferenciaTotal = $this->rangoFechasUsers[$cursor]['horasTotales'];

            #Array Reporte
            $reportDTO[$cursor] = array(
                'id' => $userDTOArray[$cursor]['id'],
                'nombre' => $NombreCompleto,
                'total_horas_cargables' => $HorasProy,
                'porcen_horas_cargables' => ($HorasProy * 100) / $ReferenciaTotal,
                'total_horas_no_cargables' => $HorasAdmon,
                'porcen_horas_no_cargables' => ($HorasAdmon * 100) / $ReferenciaTotal,
                'total_horas' => $HoraTotal,
                'porcen_carga_total' => ($HoraTotal * 100) / $ReferenciaTotal,
                'ref_usuario_total' => $ReferenciaTotal,
                'fecha_ingreso' => $userDTOArray[$cursor]['fecha_ingreso'],
                'fecha_egreso' => $userDTOArray[$cursor]['fecha_egreso']
            );
        }

        return $reportDTO;
    }

    /**
     * Metodo privado que devuelve la lista de usuarios en funcion de la division
     * @param mixed $idDivision almacena el Id de la division
     */
    protected  function GetUsersByDivisionId($idDivision)
    {
        $getUsersByDivision = DB::table('tbl_usuario')->where('id_division', '=', $idDivision)->get([
            'id',
            'codigo',
            'nombre_1',
            'nombre_2',
            'apellido_1',
            'apellido_2',
            'id_cargo',
            'id_division',
            'fecha_ingreso',
            'fecha_egreso'
        ]);
        //Crea un array con los mismos Key que las columnas registradas en la base de datos
        return $getUsersByDivision;
    }

    /**
     * Metodo que calcula el intervalo de horas en función a 8 horas
     * @param mixed $fecha_desde Inicio del intervalo
     * @param mixed $fecha_hasta Fin del intervalo
     */
    protected  function GetHorasTotales($fecha_desde, $fecha_hasta)
    {
        DB::select('call sp_diasTotales(?,?,@horasTotales)', [$fecha_desde, $fecha_hasta]);
        //Capturamos la respuesta
        $getHoras = DB::select('SELECT @horasTotales as HorasTotales');
        $HorasTotales = intval($getHoras[0]->HorasTotales, 10);

        //Retornamos el total de horas
        return ($HorasTotales * 8);
    }

    /**
     * Metodo que abstrae de la base de datos las horas trabajadas, sea administativas o cargables del usuario
     * @param mixed $fecha_desde Fecha inicial
     * @param mixed $fecha_hasta Fecha Final
     * @param mixed $id_usuario Id del usuario
     * @param mixed $tipo_hora 1= Horas no Cargables, 2= Horas Cargables
     * @return Number retorna el numero total de horas
     */
    protected  function GetHorasProyAdmin($fecha_desde, $fecha_hasta, $id_usuario, $tipo_hora)
    {
        DB::select('CALL sp_reporteHoras(?,?,?,?,@horasResponse)', [$fecha_desde, $fecha_hasta, $id_usuario, $tipo_hora]);
        $getHorasDTO = DB::select('SELECT @horasResponse AS HorasTrabajadas');
        $HorasTrabajadas = intval($getHorasDTO[0]->HorasTrabajadas, 10);

        //Enviamos las horas cargables o no cargables
        return $HorasTrabajadas;
    }
}
