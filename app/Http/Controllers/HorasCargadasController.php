<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
use App\Models\HorasCargadasModel;
use App\Models\AuditoriaLogModel;
use Illuminate\Http\RedirectResponse;

class HorasCargadasController extends Controller
{

    function datosHorasProyecto(Request $request){
      $modelo = new HorasCargadasModel;
      $idProyAnalista = (int) session("idProyAnalista");
      $permisoActualizar = $modelo->permisoActualizar(session("usuario_id"), 11);
      $permisoCrear = $modelo->permisoCrear(session("usuario_id"), 11,$idProyAnalista);
      $infoProyAnalista = $modelo->detalleProyAnalista($idProyAnalista);
      $infoHorasCargadas = $modelo->horasCargadas($idProyAnalista);
      $hora = $infoProyAnalista[0]->horas_asignadas;
      $horas_asignadas = date("$hora:00");
      $horas = 0;
      $minutos = 0;
      for ($i=0; $i < count($infoHorasCargadas); $i++) {
        $horas += date('H', strtotime($infoHorasCargadas[$i]->horas_trabajadas));
        $minutos += date('i', strtotime($infoHorasCargadas[$i]->horas_trabajadas)) ;
        if ($minutos === 60) {
          $minutos = 0;
          $horas += 1;
        }
      }
      if ($minutos === 0) {
        $horas_cargadas = date("$horas:00");
      }else{
        $horas_cargadas = date("$horas:$minutos");
      }


      if(!empty($infoProyAnalista)){
      	$response = array(
        				"infoProyAnalista" => $infoProyAnalista,
        				"idProyAnalista" => $idProyAnalista,
        				"infoHorasCargadas" => $infoHorasCargadas,
        				"permisoActualizar" => $permisoActualizar,
        				"permisoCrear" => $permisoCrear,
                "horas_asignadas" => $horas_asignadas,
                "horas_cargadas" => $horas_cargadas
      				);
  	  }else{

        $response = array("response" => false, "message" => "No se encontraron resultados");

      }

      return $response;

    }

    function cargarHoras(Request $request){

      $modelo = new HorasCargadasModel();
      $idProyAnalista = (int) session("idProyAnalista");
      $horas_asignadas = $request->input("horas_asignadas");
      $descripcion = mb_strtoupper($request->input("descripcion"));

      $fechaC = $request->input("fecha");
      $fecha = date('Y-m-d', strtotime($fechaC));
      $fechaActual = date("Y-m-d", strtotime("+1 day"));

      $horas_trabajadas = $request->input("horas_trabajadas");
      $horasT = date('H', strtotime($horas_trabajadas)) + (date('i', strtotime($horas_trabajadas))/60);
      $horas_cargadas = $request->input("horas_cargadas");
      if ($horas_cargadas != null) {
        list($horas_c,$minutos_c) = preg_split('/[:| ]/', $horas_cargadas);
        $horas_cargadas = $horas_c + ($minutos_c / 60);
      }else{
        $horas_cargadas = 0;
      }

      if ($horas_asignadas >= $horasT + $horas_cargadas) {
        if ($fechaActual > $fecha) {
          $response = $modelo->cargarHoras($idProyAnalista,$fecha,$descripcion,$horas_trabajadas);

          if($response["response"]){

            $parametros = [
              "accion" => 'Analista codigo: '.$response["analista"].' Cargo: '.$horas_trabajadas.' horas en el proyecto: '.$response["proyecto"],
              "direccion_ip" => $request->session()->get('usuario_ip'),
              "fecha" => date("Y-m-d H:i:s"),
              "tabla" => 'tbl_horas_cargables',
              "usuario_id" => $request->session()->get('usuario_id')
            ];

            $modeloAudit = new AuditoriaLogModel();
            $modeloAudit->logs_auditoria($parametros);
          }

          //$parametros_email = $modelo->dataNotificarHorasCargadas($response["analista"]);
          //$this->notificarHorasCargadas($parametros_email,$descripcion,$horas_trabajadas,$response["proyecto"]);
          return $response;
        }
        $response = array("response" => false, "message" => "A intentado introducir una actividad en una fecha a futuros acción no permitida");
        return $response;
      }
        $response = array("response" => false, "message" => "A sobrepasado el limite de horas asignadas");
        return $response;
    }

    function notificarHorasCargadas($parametros, $descripcion, $horas_trabajadas, $proyecto){

      $datos_correo = [
        "empleado" => $parametros["empleado"]->nombre,
        "division" => $parametros["empleado"]->division,
        "proyecto" => $proyecto,
        "descripcion" => $descripcion,
        "horas_cargadas" => $horas_trabajadas,
        "fecha" => date("d/m/Y H:i A"),
      ];

      $destinatarios = [];

      foreach ($parametros["supervisores"] as $key => $supervisor) {
        array_push($destinatarios,$supervisor->correo);
      }

      $destinatarios = (count($destinatarios) == 0) ?  $parametros["empleado"]->correo : $destinatarios;

      Mail::send('horasCargables.emails.horasCargadas', $datos_correo, function($message) use ($destinatarios)  {

          $message->from('sistema.carent@crowe.com.ve', 'CARENT')->to($destinatarios)->subject('Registro de Hora cargable');

      });

      if(Mail::failures()){

        return [
          "message" => "No se pudo enviar el correo, intente nuevamente.",
          "response" => false
        ];

      }

      return [
        "message" => "Enviamos sus datos a su correo, por favor revise!.",
        "response" => true
      ];

    }

    function detalleModHorasCargadas(Request $request){

      $modelo = new HorasCargadasModel();
      $idHcargadas = $request->input("idHcargadas");
      $infoModHorasCargadas = $modelo->detalleModHorasCargadas($idHcargadas);
      $response = array(
        				"infoModHorasCargadas" => $infoModHorasCargadas,
      				);
      return $response;

    }

    function ModificarHorasCargadas(Request $request){

      $modelo = new HorasCargadasModel();
      $idHoraCargada = $request->input("id");
      $horas_anterior = $request->input("horas_anterior");
      $horas_trabajadas = $request->input("horas_trabajadas");
      $descripcion = mb_strtoupper($request->input("descripcion"));
      $fechaC = $request->input("fecha");
      $fecha = date('Y-m-d', strtotime("$fechaC"));
      $fechaActual = date("Y-m-d", strtotime("+1 day"));
      $horasT = date('H', strtotime($horas_trabajadas)) + (date('i', strtotime($horas_trabajadas))/60);
      $horasA = date('H', strtotime($horas_anterior)) + (date('i', strtotime($horas_anterior))/60);
      list($horas_a,$minutos_a) = preg_split('/[:| ]/', $request->input("horas_asignadas"));
      $horas_asignadas = $horas_a + ($minutos_a / 60);
      list($horas_c,$minutos_c) = preg_split('/[:| ]/', $request->input("horas_cargadas"));
      $horas_cargadas = $horas_c + ($minutos_c / 60);
      if ($horas_asignadas >= $horas_cargadas - $horasA + $horasT) {
        if ($fechaActual > $fecha) {
          $response = $modelo->ModificarHorasCargadas($idHoraCargada,$fecha,$descripcion,$horas_trabajadas);
          if($response["response"]){
            $parametros = [
              "accion" => 'Modificacion de horas del usuario codigo: '.$response["analista"].' en el proyecto: '.$response["proyecto"],
              "direccion_ip" => $request->session()->get('usuario_ip'),
              "fecha" => date("Y-m-d H:i:s"),
              "tabla" => 'tbl_horas_cargables',
              "usuario_id" => $request->session()->get('usuario_id')
            ];

            $modeloAudit = new AuditoriaLogModel();
            $modeloAudit->logs_auditoria($parametros);
          }
          return $response;
        }
        $response = array("response" => false, "message" => "A intentado introducir una actividad en una fecha a futuros acción no permitida");
        return $response;
      }
      $response = array("response" => false, "message" => "A sobrepasado el limite de horas asignadas");
        return $response;
    }

    function detalleHorasEliminar(Request $request){

      $modelo = new HorasCargadasModel();
      $idHcargadas = $request->input("idHcargadas");
      $infoeliHorasCargadas = $modelo->detalleEliHorasCargadas($idHcargadas);
      $response = array(
        				"infoeliHorasCargadas" => $infoeliHorasCargadas,
      				);
      return $response;

    }

    function EliminarHorasCargadas(Request $request){

      $modelo = new HorasCargadasModel();
      $idHoraCargada = $request->input("id");
      $response = $modelo->EliminarHorasCargadas($idHoraCargada);

      if($response["response"]){

        $parametros = [
          "accion" => 'Eliminacion de '.$response["horas_trabajadas"].' horas del usuario codigo: '.$response["analista"].' en el proyecto:'.$response["proyecto"],
          "direccion_ip" => $request->session()->get('usuario_ip'),
          "fecha" => date("Y-m-d H:i:s"),
          "tabla" => 'tbl_horas_cargables',
          "usuario_id" => $request->session()->get('usuario_id')
        ];

        $modeloAudit = new AuditoriaLogModel();
        $modeloAudit->logs_auditoria($parametros);

      }
      $parametros_email = $modelo->dataNotificarEliHorasCargadas($response["analista"]);
      $this->notificarEliHorasCargadas($parametros_email,$response["descripcion"],$response["horas_trabajadas"],$response["proyecto"]);

      return $response;

    }

    function notificarEliHorasCargadas($parametros, $descripcion, $horas_trabajadas, $proyecto){

      $datos_correo = [
        "empleado" => $parametros["empleado"]->nombre,
        "division" => $parametros["empleado"]->division,
        "proyecto" => $proyecto,
        "descripcion" => $descripcion,
        "horas_cargadas" => $horas_trabajadas,
        "fecha" => date("d/m/Y H:i A"),
      ];

      $destinatarios = [];
      $destinatarios = $parametros["empleado"]->correo;

      Mail::send('horasCargables.emails.eliHorasCargadas', $datos_correo, function($message) use ($destinatarios)  {

          $message->from('sistema.carent@crowe.com.ve', 'CARENT')->to($destinatarios)->subject('Registro de eliminacion Hora cargable');

      });

      if(Mail::failures()){

        return [
          "message" => "No se pudo enviar el correo, intente nuevamente.",
          "response" => false
        ];

      }

      return [
        "message" => "Enviamos sus datos a su correo, por favor revise!.",
        "response" => true
      ];

    }


}
