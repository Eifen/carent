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

      $paginar = 50;
      $estatus = $modelo->estatusProyectos();
      $permisos = $modelo->permisosMenu(session("usuario_id"), 16);
      $proyectos = $modelo->proyectosFacturacion($paginar);
      $cantidadPaginas = $modelo->cantidadPaginasProyectoFacturacion($paginar);

      return [
        "estatus" => $estatus,
        "numero_paginas" => $cantidadPaginas,
        "paginar" => $paginar,
        "permisos" => $permisos,
        "proyectos" => $proyectos,
        "response" => true
      ];

    }

    function buscarProyectoFacturacion(Request $request){

      $modelo = new FacturacionModel();

      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $cliente = $request->input("cliente");
      $proyecto = $request->input("proyecto");
      $estatus = $request->input("estatus");
      $proyectos = $modelo->proyectosFacturacion($paginar, $desde, $cliente, $proyecto, $estatus);
      $cantidadPaginas = $modelo->cantidadPaginasProyectoFacturacion($paginar, $cliente, $proyecto, $estatus);

      return array("proyectos" => $proyectos, "paginas" => $cantidadPaginas);

    }

    function formAgregarIngresosGastos($idProyecto, Request $request){

      $modelo = new FacturacionModel();

      $permisos = $modelo->permisosMenu(session("usuario_id"), 16);

      if((int) $permisos->permiso_ver == 1){

        return view('facturacion/agregarIngresosGastos', ["id_proyecto" => $idProyecto]);

      }else{

        return view('error/permiso');

      }

    }

    function dataInicialAgregarIngresosGastos(Request $request){

      $modelo = new FacturacionModel();

      $id_proyecto = $request["id_proyecto"];

      $paginar = 20;
      $conceptosFactura = $modelo->conceptosFactura();
      $facturasCargadas = $modelo->proyectoFacturasCargadas($id_proyecto, null, 0, $paginar);
      $permisos = $modelo->permisosMenu(session("usuario_id"), 16);
      $proyecto = $modelo->proyecto($id_proyecto);
      $facturadoProyecto = $modelo->facturadoProyecto($id_proyecto);
      $cantidadPaginas = $modelo->cantidadPaginasFacturasCargadas($paginar, $id_proyecto);
      $retencionIva = $modelo->porcentaje_retencion_iva();
      $islr = $modelo->porcentaje_islr();
      $iva = $modelo->iva();

      return [
        "numero_paginas" => $cantidadPaginas,
        "conceptos_factura" => $conceptosFactura,
        "facturas_cargadas" => $facturasCargadas,
        "facturado_proyecto" => $facturadoProyecto,
        "islr" => $islr,
        "iva" => $iva,
        "paginar" => $paginar,
        "permisos" => $permisos,
        "proyecto" => $proyecto,
        "response" => true,
        "retencionIva" => $retencionIva
      ];

    }

    function buscarFacturasCargadas(Request $request){

      $modelo = new FacturacionModel();

      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $id_proyecto = $request->input("id_proyecto");
      $facturasCargadas = $modelo->proyectoFacturasCargadas($id_proyecto, null, $desde, $paginar);
      $cantidadPaginas = $modelo->cantidadPaginasFacturasCargadas($paginar, $id_proyecto);

      return[
        "facturas_cargadas" => $facturasCargadas
      ];

    }

    function registrarFactura(Request $request){

      $modelo = new FacturacionModel();

      $permisos = $modelo->permisosMenu(session("usuario_id"), 16);

      if($permisos->permiso_crear){

        $parametros = [
          "id_proyecto" => $request["id_proyecto"],
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
          "id_estatus" => 1,
          "id_factura_anular" => $request["id_factura_anular"],
          "id_factura_anular" => $request["id_factura_anular"],
          "id_iva" => $request["id_iva"],
          "id_porcentaje_retencion_iva" => $request["retencion_iva"],
          "id_deduccion_islr" => $request["islr"]
        ];

        $paginar = $request["paginar"];

        $conceptosFactura = $modelo->registrarFactura($parametros);

        if($conceptosFactura){

          $facturasCargadas = $modelo->proyectoFacturasCargadas($request["id_proyecto"], null, 0, $paginar);
          $facturadoProyecto = $modelo->facturadoProyecto($request["id_proyecto"]);

          return [
            "facturas_cargadas" => $facturasCargadas,
            "facturado_proyecto" => $facturadoProyecto,
            "message" => "Factura/Gasto registrado con éxito",
            "response" => true
          ];

        }else{

          return [
            "message" => "Error al tratar de registrar esta Factura/Gasto!",
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

    function buscarFacturaProyectoNotaCredito(Request $request){

      $modelo = new FacturacionModel();
      $numero_factura = $request["numero_factura"];
      $id_proyecto = $request["id_proyecto"];

      $facturas = $modelo->proyectoFacturasCargadas($id_proyecto, $numero_factura, 0, 5, 1);

      return [
        "response" => true,
        "facturas" => $facturas
      ];

    }

    function eliminarFactura(Request $request){

      $modelo = new FacturacionModel();

      $permisos = $modelo->permisosMenu(session("usuario_id"), 16);

      if($permisos->permiso_eliminar){

        $parametros = [
          "id_estatus" => 2
        ];

        $eliminarFactura = $modelo->eliminarFactura($request["id_factura"], $parametros);

        if($eliminarFactura){

          $paginar = $request["paginar"];

          $facturasCargadas = $modelo->proyectoFacturasCargadas($request["id_proyecto"], null, 0, $paginar);
          $facturadoProyecto = $modelo->facturadoProyecto($request["id_proyecto"]);

          return [
            "facturas_cargadas" => $facturasCargadas,
            "facturado_proyecto" => $facturadoProyecto,
            "message" => "Factura/Gasto eliminado con éxito",
            "response" => true
          ];

        }else{

          return [
            "message" => "Error al tratar de registrar esta Factura/Gasto!",
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

    function modificarFactura(Request $request){

      $modelo = new FacturacionModel();

      $permisos = $modelo->permisosMenu(session("usuario_id"), 16);

      if($permisos->permiso_actualizar){

        $parametros = [
          "concepto" => $request["concepto"],
          "monto_factura" => $request["monto_factura"],
          "numero_control" => strtoupper($request["numero_control"]),
          "observaciones" => $request["observaciones"],
          "fecha_factura" => $request["fecha_factura"],
          "fecha_cobro_factura" => $request["fecha_cobro_factura"]
        ];

        $actualizarFactura = $modelo->actualizarFactura($request["id_factura"], $parametros);

        if($actualizarFactura){

          $paginar = $request["paginar"];

          $facturasCargadas = $modelo->proyectoFacturasCargadas($request["id_proyecto"], null, 0, $paginar);
          $facturadoProyecto = $modelo->facturadoProyecto($request["id_proyecto"]);

          return [
            "facturas_cargadas" => $facturasCargadas,
            "facturado_proyecto" => $facturadoProyecto,
            "message" => "Factura/Gasto actualizado con éxito",
            "response" => true
          ];

        }else{

          return [
            "message" => "Error al tratar de actualizar esta Factura/Gasto!",
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
