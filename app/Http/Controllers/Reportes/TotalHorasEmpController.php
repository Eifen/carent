<?php

namespace App\Http\Controllers\Reportes;
use Illuminate\Http\Request;
use App\Models\Reportes\TotalHorasEmpModel;
use Mail;
use App\Http\Controllers\Controller;

class TotalHorasEmpController extends Controller
{

    function dataRepTotalHorasEmp(){

      $modelo = new TotalHorasEmpModel();

      $divisiones = $modelo->divisiones();

      return [
        "divisiones" => $divisiones,
        "response" => true
      ];

    }

    function repTotalHorasEmpEmpleadosDivision(Request $request){

      $modelo = new TotalHorasEmpModel();
      $id_division = $request->input("id_division");

      $empleados = $modelo->empleados($id_division);

      return [
        "empleados" => $empleados
      ];

    }

    function repTotalHorasInfoEmp(Request $request){

      $modelo = new TotalHorasEmpModel();
      $parametros = [
        "fecha_desde" => $request->input("fecha_desde"),
        "fecha_hasta" => $request->input("fecha_hasta"),
        "id_usuario" => $request->input("empleado")
      ];

      $horas_cargables = $modelo->horas_cargables($parametros);
      $horas_no_cargables = $modelo->horas_no_cargables($parametros);
      $totales = $modelo->total_horas($parametros);

      return [
        "horas_cargables" => $horas_cargables,
        "horas_no_cargables" => $horas_no_cargables,
        "response" => true,
        "totales" => $totales
      ];

    }

    function repEmpSinCargarHoras(Request $request){

      $modelo = new TotalHorasEmpModel();

      $horas_cargables = $modelo->sin_cargar_horas_cargables();
      $destinatarios = $modelo->destinatarios_notificaciones_tarea_programada(1);
      $config = $modelo->configuracion_tarea_programada(1);

      Mail::send('reportes.emails.repEmpSinCargarHoras', ["empleados" => $horas_cargables, "dias" => $config->dias], function($message) use ($destinatarios)  {

          $message->from('sistema.carent@crowe.com.ve', 'CARENT')->to($destinatarios)->subject('Reporte Empleados Sin Cargar Horas');

      });

      if(Mail::failures()){

        $response = array("response" => false, "message" => "No se pudo enviar el correo, intente nuevamente.");

      }else{

        $response = array("response" => true, "message" => "Correo enviado.");

      }

      return $response;

    }

}
