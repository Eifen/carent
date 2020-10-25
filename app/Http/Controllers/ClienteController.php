<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ClienteModel;
use App\Models\AuditoriaLogModel;
use Illuminate\Http\RedirectResponse;

class ClienteController extends Controller
{
  function estados(){

    $modelo = new ClienteModel();
    $estados = $modelo->estados();
    return $estados;
  }

  function municipios(Request $request){

    $id_estado = $request->input("id_estado");
    $modelo = new ClienteModel();
    $municipios = $modelo->municipios($id_estado);
    return $municipios;
  }

  function parroquias(Request $request){

    $id_municipio = $request->input("id_municipio");
    $modelo = new ClienteModel();
    $parroquias = $modelo->parroquias($id_municipio);
    return $parroquias;
  }

  function dataInicialCliente(Request $request){

    $modelo = new ClienteModel();
    $codigoCliente = $modelo->codigoCliente();
    $paises = $modelo->paises();
    if (!empty($codigoCliente)) {
      $codigo = $codigoCliente->codigo + 1;
      $response = array("response" => true, "codigo" => $codigo, "paises" => $paises);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
    return $response;
  }

  function buscarUsuariosS(Request $request){

    $modelo = new ClienteModel();
    $buscarPor = (int) $request->input("buscarPor");
    $dato = strtolower($request->input("dato"));
    $usuarios = $modelo->buscarUsuarios($buscarPor, $dato);
    if(!empty($usuarios)){
      $response = array("response" => true, "usuarios" => $usuarios);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
    return $response;
  }

  function buscarCliente(Request $request){

    $modelo = new ClienteModel();
    $buscarPor = (int) $request->input("buscarPor");
    $dato = strtolower($request->input("dato"));
    $clientes = $modelo->buscarClientes($buscarPor, $dato);
    if(!empty($clientes)){
      $response = array("response" => true, "clientes" => $clientes);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
    return $response;
  }

  function buscarClieProyec(Request $request){

    $modelo = new ClienteModel();
    $idCliente = (int) $request->input("idCliente");
    $permisoCrear = $modelo->permisoCrear(session("usuario_id"), 7);
    $clienteProy = $modelo->buscarClieProyec($idCliente);
    if(!empty($clienteProy)){
      $response = array("response" => true, "clienteProy" => $clienteProy, "permisoCrear" => $permisoCrear);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
    return $response;
  }

  function detalleUsuario(Request $request){

    $modelo = new ClienteModel();
    $id_usuario = (int) $request->input("idUsuario");
    $infoUsuario = $modelo->detalleUsuario($id_usuario);
    if(!empty($infoUsuario)){
      $response = array("response" => true, "info" => $infoUsuario);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
      return $response;
    }

  function crearCliente(Request $request){

    $modelo = new ClienteModel();
    $codigoCliente = (int) $request->input("codigoCliente");
    $pais = $request->input("pais");

    $parametros = array(
        "idUsuario" => (int) $request->input("idUsuario"),
        "codigoCliente" => $codigoCliente,
        "rif" => $request->input("rif"),
        "nit" => $request->input("nit"),
        "razon_social" => mb_strtoupper ($request->input("razon_social")),
        "id_pais" => $request->input("pais"),
        "direccion" => $request->input("direccion"),
        "telefono_fiscal" => $request->input("telefono_fiscal"),
        "pagina_web" => $request->input("pagina_web"),
        "email_fiscal" => strtolower($request->input("email_fiscal"))
    );

    $response = $modelo->crearCliente($parametros);

    if($response["response"]){

      $parametros = [
        "accion" => 'Registro del cliente codigo: '.$codigoCliente,
        "direccion_ip" => $request->session()->get('direccion_ip'),
        "fecha" => date("Y-m-d H:i:s"),
        "tabla" => 'tbl_cliente',
        "usuario_id" => $request->session()->get('usuario_id')
      ];

      $modeloAudit = new AuditoriaLogModel();
      $modeloAudit->logs_auditoria($parametros);

    }

    return $response;

  }

  function buscarClientes(Request $request){

    $modelo = new ClienteModel();
    $buscarPor = (int) $request->input("buscarPor");
    $dato = strtolower($request->input("dato"));
    $permisoActualizar = $modelo->permisoActualizar(session("usuario_id"), 6);
    $clientes = $modelo->buscarClientes($buscarPor, $dato);
    if(!empty($clientes)){
      $response = array("response" => true, "clientes" => $clientes, "permisoActualizar" => $permisoActualizar);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
    return $response;
  }

  function detalleCliente(Request $request){

    $modelo = new ClienteModel();
    $id_cliente = (int) $request->input("idCliente");
    $infoCliente = $modelo->detalleCliente($id_cliente);
    if(!empty($infoCliente)){
      $response = array("response" => true, "info" => $infoCliente);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
    return $response;
  }

  function detalleClienteProy(Request $request){

    $modelo = new ClienteModel();
    $permisoActualizar = $modelo->permisoActualizar(session("usuario_id"), 7);
    $permisoCrear = $modelo->permisoCrear(session("usuario_id"), 7);
    $idclienteProy = (int) $request->input("idclienteProy");
    $infoClienteProy = $modelo->detalleClienteProy($idclienteProy);
    $infoFactCliente = $modelo->detalleFactCliente($infoClienteProy->id, $infoClienteProy->id_cliente);
    $estados_factura = $modelo->estados();
    if (!empty($infoFactCliente)) {
      if($infoFactCliente->id_estado_factura !== NULL){
        $municipios_factura = $modelo->municipios($infoFactCliente->id_estado_factura);
        $parroquias_factura = $modelo->parroquias($infoFactCliente->id_municipio_factura);
      }
    }else{
      $municipios_factura = array();
      $parroquias_factura = array();
    }

    if(!empty($infoClienteProy)){
      $response = array("response" => true,
                        "infoproy" => $infoClienteProy,
                        "infoFactCliente" => $infoFactCliente,
                        "estadosfa" => $estados_factura,
                        "municipiosfa" => $municipios_factura,
                        "parroquiasfa" => $parroquias_factura,
                        "permisoActualizar" => $permisoActualizar,
                        "permisoCrear" => $permisoCrear);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
    return $response;
  }

  function formModificarCliente($idCliente, Request $request){

    $request->session()->put('id_cliente_mod', $idCliente);
    return view('cliente/modificarCliente');
  }

  function detalleClienteModificar(Request $request){

    $modelo = new ClienteModel();
    $id_cliente = (int) session("id_cliente_mod");
    $infoCliente = $modelo->detalleClienteModificar($id_cliente);
    $estatus = $modelo->estatusCliente();
    $paises = $modelo->paises();

    if(!empty($infoCliente)){
      $response = array("response" => true,
                        "info" => $infoCliente,
                        "paises" => $paises,
                        "estatus" => $estatus);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
    return $response;
  }

  function crearFactCliente(Request $request){

    $modelo = new ClienteModel();

    $parametros = array(
      "id_cliente" => (int) $request->input("id_cliente"),
      "id_proyecto" => (int) $request->input("id_proyecto"),
      "parroquiafa" => $request->input("parroquiafa"),
      "ciudad_factura" => mb_strtoupper($request->input("ciudad_factura")),
      "avenida_calle_factura" => mb_strtoupper($request->input("avenida_calle_factura")),
      "edificio_quinta_factura" => mb_strtoupper($request->input("edificio_quinta_factura")),
      "piso_factura" => mb_strtoupper($request->input("piso_factura")),
      "numero_factura" => $request->input("numero_factura"),
      "telefono_factura" => $request->input("telefono_factura"),
      "fax_factura" => $request->input("fax_factura"),
      "correo_factura" => strtolower($request->input("correo_factura"))
    );

    $response = $modelo->CrearFactCliente($parametros);

    if($response["response"]){

      $parametros = [
        "accion" => 'Registro del detalle de facturacion del cliente: '. $response["razon_social"] .'. proyecto: '.$response["proyecto"],
        "direccion_ip" => $request->session()->get('direccion_ip'),
        "fecha" => date("Y-m-d H:i:s"),
        "tabla" => 'tbl_cliente_facturacion',
        "usuario_id" => $request->session()->get('usuario_id')
      ];

      $modeloAudit = new AuditoriaLogModel();
      $modeloAudit->logs_auditoria($parametros);

    }

    return $response;

  }

  function actualizarFactCliente(Request $request){

    $modelo = new ClienteModel();
      $parametros = array(
        "id_cliente" => (int) $request->input("id_cliente"),
        "id_proyecto" => (int) $request->input("id_proyecto"),
        "id_fact_cliente" => (int) $request->input("id_fact_cliente"),
        "parroquiafa" => $request->input("parroquiafa"),
        "ciudad_factura" => mb_strtoupper($request->input("ciudad_factura")),
        "avenida_calle_factura" => mb_strtoupper($request->input("avenida_calle_factura")),
        "edificio_quinta_factura" => mb_strtoupper($request->input("edificio_quinta_factura")),
        "piso_factura" => mb_strtoupper($request->input("piso_factura")),
        "numero_factura" => $request->input("numero_factura"),
        "telefono_factura" => $request->input("telefono_factura"),
        "fax_factura" => $request->input("fax_factura"),
        "correo_factura" => strtolower($request->input("correo_factura"))
      );
      $response = $modelo->actualizarFactCliente($parametros);

      if($response["response"]){

        $parametros = [
          "accion" => 'Modificacion del detalle de facturacion del cliente: '. $response["razon_social"] .'. proyecto: '.$response["proyecto"],
          "direccion_ip" => $request->session()->get('direccion_ip'),
          "fecha" => date("Y-m-d H:i:s"),
          "tabla" => 'tbl_cliente_facturacion',
          "usuario_id" => $request->session()->get('usuario_id')
        ];

        $modeloAudit = new AuditoriaLogModel();
        $modeloAudit->logs_auditoria($parametros);

      }

      return $response;
  }

    function modificarCliente(Request $request){

    $modelo = new ClienteModel();

    $parametros = array(
      "idCliente" => $request->input("idCliente"),
      "idUsuario" => (int) $request->input("idUsuario"),
      "codigoCliente" => (int) $request->input("codigoCliente"),
      "rif" => $request->input("rif"),
      "nit" => $request->input("nit"),
      "razon_social" => mb_strtoupper ($request->input("razon_social")),
      "id_pais" => $request->input("pais"),
      "direccion" => $request->input("direccion"),
      "telefono_fiscal" => $request->input("telefono_fiscal"),
      "pagina_web" => $request->input("pagina_web"),
      "email_fiscal" => strtolower($request->input("email_fiscal")),
      "estatus" => $request->input("estatus"),
      "usuario_id" => $request->session()->get('usuario_id'),
      "fecha" => date("Y-m-d H:i:s"),
      "direccion_ip" => $request->session()->get('direccion'),
    );

    $response = $modelo->modificarCliente($parametros);

    if($response["response"]){

      $parametros = [
        "accion" => 'Modificación del cliente: '. $request->input("codigoCliente"),
        "direccion_ip" => $request->session()->get('direccion_ip'),
        "fecha" => date("Y-m-d H:i:s"),
        "tabla" => 'tbl_cliente',
        "usuario_id" => $request->session()->get('usuario_id')
      ];

      $modeloAudit = new AuditoriaLogModel();
      $modeloAudit->logs_auditoria($parametros);

    }

    return $response;

  }
}
