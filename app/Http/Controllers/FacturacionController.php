<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\FacturacionModel;

class FacturacionController extends Controller
{

    function formIngresosGastos(){

      return view('facturacion/ingresosGastos');

    }

    function dataInicialIngresosGastos(){

      $modelo = new FacturacionModel();

      $permisos = $modelo->permisosMenu(session("usuario_id"), 16);
      $estatus = $modelo->estatusProyectos();
      $proyectos = $modelo->proyectosFacturacion();

      return [
        "estatus" => $estatus,
        "permisos" => $permisos,
        "proyectos" => $proyectos,
        "response" => true
      ];

    }

    function formAgregarIngresosGastos($idProyecto, Request $request){

      $modelo = new FacturacionModel();

      $permisos = $modelo->permisosMenu(session("usuario_id"), 16);

      $request->session()->put('proyecto_id', $idProyecto);

      if((int) $permisos->permiso_crear == 1 && (int) $permisos->permiso_actualizar == 1){

        return view('facturacion/agregarIngresosGastos');

      }else{

        return view('error/permiso');

      }

    }

    function dataInicialAgregarIngresosGastos(){

      $modelo = new FacturacionModel();

      $conceptosFactura = $modelo->conceptosFactura();
      $facturasCargadas = $modelo->proyectoFacturasCargadas(session("proyecto_id"));
      $permisos = $modelo->permisosMenu(session("usuario_id"), 16);
      $proyecto = $modelo->proyecto(session("proyecto_id"));
      $facturadoProyecto = $modelo->facturadoProyecto(session("proyecto_id"));

      return [
        "conceptos_factura" => $conceptosFactura,
        "facturas_cargadas" => $facturasCargadas,
        "facturado_proyecto" => $facturadoProyecto,
        "permisos" => $permisos,
        "proyecto" => $proyecto,
        "response" => true
      ];

    }

    function registrarFactura(Request $request){

      $modelo = new FacturacionModel();

      $permisos = $modelo->permisosMenu(session("usuario_id"), 16);

      if($permisos->permiso_crear){

        $parametros = [
          "id_proyecto" => session("proyecto_id"),
          "concepto" => $request["concepto"],
          "id_concepto_factura" => $request["tipo_concepto"],
          "numero_factura" => strtoupper($request["numero_factura"]),
          "monto_factura" => $request["monto_factura"],
          "numero_control" => strtoupper($request["numero_control"]),
          "observaciones" => $request["observaciones"],
          "fecha_factura" => $request["fecha_factura"],
          "fecha_cobro_factura" => $request["fecha_cobro_factura"],
          "fecha_registro" => date("Y-m-d"),
          "id_facturador" => session("usuario_id"),
          "id_estatus" => 1
        ];

        $conceptosFactura = $modelo->registrarFactura($parametros);

        if($conceptosFactura){

          $facturasCargadas = $modelo->proyectoFacturasCargadas(session("proyecto_id"));
          $facturadoProyecto = $modelo->facturadoProyecto(session("proyecto_id"));

          return [
            "facturas_cargadas" => $facturasCargadas,
            "facturado_proyecto" => $facturadoProyecto,
            "message" => "Factura registrada con éxito",
            "response" => true
          ];

        }else{

          return [
            "message" => "Error al tratar de registrar la factura!",
            "response" => false
          ];

        }

      }else{

        return [
          "message" => "No posee permisos para esta acción!",
          "response" => false
        ];

      }

    }

}
