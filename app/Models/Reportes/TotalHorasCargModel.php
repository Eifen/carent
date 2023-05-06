<?php

namespace App\Models\Reportes;

use DateInterval;
use DateTime;
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

    /**
     * Busca el cargo del cliente en funcion de la id que tenga registrada
     * @param Number $Id_cargo Almacena un numero con el cargo del usuario
     */
    static function Cargos($Id_cargo)
    {
        $getCargo = DB::table('tbl_cargo_empleado')
        ->where('id','=',$Id_cargo)
        ->get(['id', 'descripcion','orden']);

        return $getCargo[0];
    } // Fin cargos

    /**
     * Metodo que abstrae la cargabilidad de las divisiones
     */
    static function DivisionCargabilidad($id_cargo,$Id_Tipo_Division)
    {
        return DB::table('tbl_division_cargabilidad')
        ->where([
            ["id_cargo","=",$id_cargo],
            ["id_tipo_division","=",$Id_Tipo_Division]])
        ->first(['porcentaje']); //Obtenemos un unico valor
    }

    /**
     * Metodo que abstrae el grupo de cargos
     * @param int $id_cargo. Id del cargo
     */
    static function GroupCargo($id_cargo)
    {
        return DB::table('tbl_cargo_group_niveles')
        ->where("id_cargo","=",$id_cargo)
        ->first();
    }

    /**
     * Metodo que abstrae las divisiones de la base de datos
     * @param Array $Iddivision captura el id de la division
     * @return Array Retorna un array de objetos. La longitud depende de las divisiones
     */
    static function Divisiones($DivisionArray=null)
    {
        if($DivisionArray !== null)
        {
            return DB::table('tbl_division')->whereIn('id',$DivisionArray)
            ->orderBy('orden_division','asc')
            ->get(['id','descripcion','id_tipo']);
        };

        return DB::table('tbl_division')
        ->orderBy('orden_division','asc')
        ->get(['id', 'descripcion','id_tipo']);
    } // Fin divisiones

    /**
     * Metodo que abstrae los cargos de la base de datos
     * @param Array $CargosArray captura el id de la division, o los id
     * @return Array Retorna un array de objetos. La longitud depende de las divisiones
     */
    static function GetAllCargos($CargosArray=null)
    {
        if($CargosArray !== null)
        {
            return DB::table('tbl_cargo_empleado')->whereIn('id',$CargosArray)->get(['id','descripcion']);
        };

        return DB::table('tbl_cargo_empleado')->get(['id', 'descripcion']);
    } // Fin cargos

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
            $this->getUsersPerDivision[$cursor] = $this->GetUsersByDivisionId($divisiones[$cursor]->id,$empleado);
            //Creamos el array de usuarios dependiendo de la division. Quitamos los array vacios
        }

        //Comparamos las fechas de cada usuario
        for ($cursor2 = 0; $cursor2 < count($this->getUsersPerDivision); $cursor2++) {
            //Agrupamos la fecha de ingreso
            foreach ($this->getUsersPerDivision[$cursor2] as $column => $valor) {
                //Fecha ingreso
                !is_null($valor->fecha_ingreso)
                    ? $this->fecha_ingreso = date('Y-m-d',strtotime($valor->fecha_ingreso))
                    : $this->fecha_ingreso = date('Y-m-d', strtotime($fecha_desde . " -1 day"));

                //Fecha de egreso
                !is_null($valor->fecha_egreso)
                    ? $this->fecha_egreso = date('Y-m-d',strtotime($valor->fecha_egreso))
                    : $this->fecha_egreso = date('Y-m-d', strtotime($fecha_hasta . " +1 day"));

                //Comparamos para calcular las horas totales en función de cada empleado
                switch (true) {
                        //Ingreso despues del intervalo inicial
                    case $fecha_desde < $this->fecha_ingreso:
                        $this->totalHorasEmpleado = $this->GetHorasTotales($this->fecha_ingreso, $fecha_hasta);
                        break;
                        //Egreso antes del intervalo final
                    case $fecha_hasta > $this->fecha_egreso:
                        $this->totalHorasEmpleado = $this->GetHorasTotales($fecha_desde, $this->fecha_egreso);
                        break;
                        //Ingreso despues y egreso antes de los intervalos
                    case $fecha_desde < $this->fecha_ingreso && $fecha_hasta > $this->fecha_egreso:
                        $this->totalHorasEmpleado = $this->GetHorasTotales($this->fecha_ingreso, $this->fecha_egreso);
                        break;
                        //Su ingreso y egreso esta fuera de las intervalos
                    default:
                        $this->totalHorasEmpleado = $this->totalHoras;
                        break;
                }

                $this->rangoFechasUsers[$cursor2][$column] = array(
                    "id" => $valor->id,
                    "fecha_desde" => $this->totalHorasEmpleado["fecha_desde"],
                    "fecha_hasta" => $this->totalHorasEmpleado["fecha_hasta"],
                    "horasRef" => $this->totalHorasEmpleado["horas"]
                );
            }
        }

        return $this->MakeReport($fecha_desde,$fecha_hasta);
    }

    /**
     * Metodo que genera el reporte de horas de cargabilidad
     * @param mixed $fecha_desde Inicio del intervalo del reporte
     * @param mixed $fecha_hasta Fin del intervalo del reporte
     * @return Array retorna un array con el reporte ya generado
     */
    protected  function MakeReport($fecha_desde, $fecha_hasta)
    {
        $reportDTO = []; //Objeto de transferencia de data
        $userDTOArray = $this->getUsersPerDivision; //Objeto de transferencia para users

        for ($cursorUser=0; $cursorUser < count($userDTOArray) ; $cursorUser++) {
            # code...
            foreach ($userDTOArray[$cursorUser] as $division => $usuario) {
                # Nombre
                $NombreCompleto = implode(" ",[
                    $usuario->nombre_1,
                    $usuario->nombre_2,
                    $usuario->apellido_1,
                    $usuario->apellido_2,
                ]);

                # Horas Cargables
                $HorasProy = intval($this->GetHorasProyAdmin(
                    $fecha_desde,
                    $fecha_hasta,
                    $usuario->id,
                    2));

                # Horas No Cargables
                $HorasAdmon = intval($this->GetHorasProyAdmin(
                    $fecha_desde,
                    $fecha_hasta,
                    $usuario->id,
                    1));

                #Hora Total
                $HoraTotal = $HorasAdmon + $HorasProy;

                #Ref Hora Empleado
                $ReferenciaTotal = $this->rangoFechasUsers[$cursorUser][$division]["horasRef"];

                $Cargo = $this->Cargos($usuario->id_cargo);

                #Definimos la cargabilidad

                $Division = $this->Divisiones([$usuario->id_division]);
                $Cargabilidad = $this->DivisionCargabilidad($Cargo->id, $Division[0]->id_tipo);
                $CargabilidadAdmin = 100 - optional($Cargabilidad)->porcentaje;
                $GroupCargo = $this->GroupCargo($Cargo->id);

		        #Porcentaje proyectos
		        $PerProyecto = ($HorasProy * 100) / ($HorasProy != 0 && $ReferenciaTotal != 0 ? $ReferenciaTotal : 1);
                $ExcesoPerProy = ($PerProyecto > optional($Cargabilidad)->porcentaje ? ($PerProyecto - optional($Cargabilidad)->porcentaje) : 0);

                #Porcentaje Administrativo
                $PerAdmon = ($HorasAdmon * 100) / ($HorasAdmon != 0 && $ReferenciaTotal != 0 ? $ReferenciaTotal : 1);
                $ExcesoPerAdmon = ($PerAdmon > $CargabilidadAdmin ? ($PerAdmon - $CargabilidadAdmin) : 0);


                #Porcentaje total
                $PerTotal = ($HoraTotal * 100) / ($HoraTotal != 0 && $ReferenciaTotal != 0 ? $ReferenciaTotal : 1);

                #Array Reporte
                $reportDTO[$cursorUser][$division] = array(
                    "id"=> $usuario->id,
                    "nombre"=> $NombreCompleto,
                    "usuario_cargo" => $Cargo->descripcion,
                    "usuario_division" => $this->Divisiones([$usuario->id_division])[0]->descripcion,
                    'total_horas_cargables' => $HorasProy,
                    'porcen_horas_cargables' => $PerProyecto / 100,
                    'total_horas_no_cargables' => $HorasAdmon,
                    'porcen_horas_no_cargables' => $PerAdmon / 100,
                    'total_horas' => $HoraTotal,
                    'porcen_carga_total' => ($PerTotal > 100 ? 1 : $PerTotal / 100),
                    'total_exceso_proyectos' => $ReferenciaTotal * ($ExcesoPerProy / 100),
                    'exceso_per_proyectos' => $ExcesoPerProy / 100,
                    'total_exceso_administrativo' => $ReferenciaTotal * ($ExcesoPerAdmon / 100),
                    'exceso_per_administrativo' => $ExcesoPerAdmon / 100,
                    'fecha_desde' => $this->rangoFechasUsers[$cursorUser][$division]["fecha_desde"],
                    'fecha_hasta' => $this->rangoFechasUsers[$cursorUser][$division]["fecha_hasta"],
                    'ref_usuario_total' => $ReferenciaTotal,
                    'fecha_ingreso' => ($usuario->fecha_ingreso === null ? $usuario->fecha_ingreso : date('Y-m-d',strtotime($usuario->fecha_ingreso))),
                    'fecha_egreso' => ($usuario->fecha_egreso === null ? $usuario->fecha_egreso : date('Y-m-d',strtotime($usuario->fecha_egreso))),
                    'orden' => $Cargo->orden,
                    'eficiencia' => (optional($Cargabilidad)->porcentaje <= $PerProyecto  ? true : false),
                    'grupo_nivel' => (optional($GroupCargo)->nivel_group)
                );
            }
        }

        return $reportDTO;
    }

    /**
     * Metodo privado que devuelve la lista de usuarios en funcion de la division
     * @param Number $idDivision almacena el Id de la division
     * @param String $empleado Nombre del empleado en caos que queramos filtrar por empleado
     */
    protected  function GetUsersByDivisionId($idDivision,$empleado = null)
    {
        $empleadoDTO = []; //Array de Nombres
        //[0] => Nombre 1, [1] => Nombre 2, [2] => Apellido 1, [3] => Apellido 2
        if($empleado !== null) $empleadoDTO = explode(" ",$empleado);

        $getUsersByDivision = DB::table('tbl_usuario')
        ->where('id_division', '=', $idDivision)
        ->whereRaw('CONCAT(nombre_1,nombre_2,apellido_1,apellido_2) LIKE
                    "%'.(isset($empleadoDTO[0]) ? $empleadoDTO[0] : "").
                    '%'.(isset($empleadoDTO[1]) ? $empleadoDTO[1] : "").
                    '%'.(isset($empleadoDTO[2]) ? $empleadoDTO[2] : "").
                    '%'.(isset($empleadoDTO[3]) ? $empleadoDTO[3] : "").'%"')
        ->get([
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
    public  function GetHorasTotales($fecha_desde, $fecha_hasta)
    {
        $fecha_desdeDTO = new DateTime($fecha_desde);
        $fecha_hastaDTO = new DateTime($fecha_hasta);

        //$Intervalo = $fecha_desdeDTO->diff($fecha_hasta);
        //$TotalDias = $Intervalo->days;
        $ContadorDias = 0;
        $FechaLimite = clone $fecha_desdeDTO; //Clonamos la primera fecha

        while ($FechaLimite <= $fecha_hastaDTO) {
            # Procedemos a determinar los días de la semana y solo tomar lunes a viernes
            $DiaSemana = $FechaLimite->format('N'); //Dia de la semana. Lunes a viernes es de 1 - 5
            //Sumamos
            if($DiaSemana < 6) $ContadorDias++;

            $FechaLimite->add(new DateInterval('P1D')); //Añadimos 1 día
        }
        //Capturamos la respuesta
        $HorasTotales = $ContadorDias * 8; //Determinamos las horas a 8 horas

        //Retornamos el total de horas
        return ["horas" => $HorasTotales, "fecha_desde" => $fecha_desde, "fecha_hasta" => $fecha_hasta];
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
