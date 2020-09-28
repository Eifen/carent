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

      $paginar = 10;
      $estatus = $modelo->estatusProyectos();
      $permisos = $modelo->permisosMenu(session("usuario_id"), 16);
      $proyectos = $modelo->proyectosFacturacion($paginar);

      return [
        "estatus" => $estatus,
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

      if((int) $permisos->permiso_crear == 1 && (int) $permisos->permiso_actualizar == 1){

        return view('facturacion/agregarIngresosGastos', ["id_proyecto" => $idProyecto]);

      }else{

        return view('error/permiso');

      }

    }

    function dataInicialAgregarIngresosGastos(Request $request){

      $modelo = new FacturacionModel();

      $id_proyecto = $request["id_proyecto"];

      $conceptosFactura = $modelo->conceptosFactura();
      $facturasCargadas = $modelo->proyectoFacturasCargadas($id_proyecto);
      $permisos = $modelo->permisosMenu($id_proyecto, 16);
      $proyecto = $modelo->proyecto($id_proyecto);
      $facturadoProyecto = $modelo->facturadoProyecto($id_proyecto);

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
          "id_estatus" => 1
        ];

        $conceptosFactura = $modelo->registrarFactura($parametros);

        if($conceptosFactura){

          $facturasCargadas = $modelo->proyectoFacturasCargadas($request["id_proyecto"]);
          $facturadoProyecto = $modelo->facturadoProyecto($request["id_proyecto"]);

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

    function buscarFacturaProyecto(Request $request){

      $modelo = new FacturacionModel();
      $numero_factura = $request["numero_factura"];
      $id_proyecto = $request["id_proyecto"];

      $facturas = $modelo->proyectoFacturasCargadas($id_proyecto, $numero_factura, 5);

      return [
        "response" => true,
        "facturas" => $facturas
      ];

    }

}
