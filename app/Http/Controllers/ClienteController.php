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
    $servicios = $modelo->servicios();
    $sectores = $modelo->sectores();
    if (!empty($codigoCliente)) {
      $codigo = $codigoCliente->codigo + 1;
      $response = array("response" => true, 
                        "codigo" => $codigo, 
                        "paises" => $paises, 
                        "servicios" => $servicios,
                        "sectores" => $sectores);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
    return $response;
  }

  function buscarUsuariosS(Request $request){

    $modelo = new ClienteModel();
    $nombre = $request->input("nombre");
    $usuarios = $modelo->buscarUsuarios($nombre);
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
        session("usuario_id"),
        $request->input("idUsuario"),
        $codigoCliente,
        $request->input("rif"),
        $request->input("nit"),
        $request->input("razon_social"),
        $request->input("pais"),
        $request->input("direccion"),
        $request->input("telefono_fiscal"),
        $request->input("pagina_web"),
        $request->input("email_fiscal"),
        $request->input("servicios"),
        $request->input("sector"),
        session("usuario_ip")
    );

    $response = $modelo->crearCliente($parametros);

    if($response["response"]){

      $parametros = [
        "accion" => 'Registro del cliente codigo: '.$codigoCliente,
        "direccion_ip" => $request->session()->get('usuario_ip'),
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

    function UpdateClientData(Request $request){

        $model = new ClienteModel();

        $params = array(
            $request->input("idCliente"),
            (int) $request->input("idUsuario"),
            (int) $request->input("codigoCliente"),
            $request->input("rif"),
            (int) $request->input("nit"),
            mb_strtoupper ($request->input("razon_social")),
            $request->input("pais"),
            $request->input("direccion"),
            $request->input("telefono_fiscal"),
            (string) $request->input("pagina_web"),
            strtolower($request->input("email_fiscal")),
            $request->input("estatus")
        );

        $response = $model->UpdateClientData($params);

        if($response["response"]){

            $params = [
                "accion" => 'Modificación del cliente: '. $request->input("codigoCliente"),
                "direccion_ip" => $request->session()->get('usuario_ip'),
                "fecha" => date("Y-m-d H:i:s"),
                "tabla" => 'tbl_cliente',
                "usuario_id" => $request->session()->get('usuario_id')
            ];

            $model = new AuditoriaLogModel();
            $model->logs_auditoria($params);

        }

        return $response;

    }

}
