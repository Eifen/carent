<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
use App\Models\ClienteModel;
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

  function buscarUsuarios(Request $request){

    $modelo = new ClienteModel();
    $cargo = 16;
    $buscarPor = (int) $request->input("buscarPor");
    $dato = strtolower($request->input("dato"));
    $usuarios = $modelo->buscarUsuarios($buscarPor, $dato, $cargo);     
    if(!empty($usuarios)){
      $response = array("response" => true, "usuarios" => $usuarios);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
    return $response;
  }
  function buscarUsuariosG(Request $request){

    $modelo = new ClienteModel();
    $cargo = 11;
    $buscarPor = (int) $request->input("buscarPor");
    $dato = strtolower($request->input("dato"));
    $usuariosG = $modelo->buscarUsuariosG($buscarPor, $dato, $cargo);     
    if(!empty($usuariosG)){
      $response = array("response" => true, "usuariosG" => $usuariosG);
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
    $codigoCliente = $request->input("codigoCliente");
    $cliente = $modelo->buscarCliente($codigoCliente);
    if(empty($cliente)){
      $email = $modelo->buscarEmail($request->input("email_fiscal"));
      if(!$email["response"]){
        $parametros = array(
          "idUsuario" => (int) $request->input("idUsuario"),
          "idUsuario2" => (int) $request->input("idUsuario2"),
          "codigoCliente" => $request->input("codigoCliente"),
          "rif" => $request->input("rif"),
          "nit" => $request->input("nit"),
          "razon_social" => mb_strtoupper ($request->input("razon_social")),
          "parroquiafi" => $request->input("parroquiafi"),
          "ciudad_fiscal" => mb_strtoupper($request->input("ciudad_fiscal")),
          "avenida_calle_fiscal" => mb_strtoupper($request->input("avenida_calle_fiscal")),
          "edificio_quinta_fiscal" => mb_strtoupper($request->input("edificio_quinta_fiscal")),
          "piso_fiscal" => mb_strtoupper($request->input("piso_fiscal")),
          "numero_fiscal" => $request->input("numero_fiscal"),
          "telefono_fiscal" => $request->input("telefono_fiscal"),
          "fax_fiscal" => $request->input("fax_fiscal"),
          "email_fiscal" => strtolower($request->input("email_fiscal")),
          "descripcion_factura" => mb_strtoupper($request->input("descripcion_factura")),
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
         $response = $modelo->crearCliente($parametros);
      }else{
        $response = array("response" => false, "message" => "El correo ya se encuentra asociado a otro usuario");
      }
    }else{
      $response = array("response" => false, "message" => "Ya existe un cliente con ese código de usuario");
    }
      return $response;
  }

  function buscarClientes(Request $request){

    $modelo = new ClienteModel();
    $buscarPor = (int) $request->input("buscarPor");
    $dato = strtolower($request->input("dato"));
    $clientes = $modelo->buscarClientes($buscarPor, $dato);
    if(!empty($clientes)){
      $response = array("response" => true, "clientes" => $clientes);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultadoos");
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
    $estados_fiscal = $modelo->estados();
    if($infoCliente->id_estado_fiscal !== NULL){
      $municipios_fiscal = $modelo->municipios($infoCliente->id_estado_fiscal);
      $parroquias_fiscal = $modelo->parroquias($infoCliente->id_municipio_fiscal);
    }else{
      $municipios_fiscal = array();
      $parroquias_fiscal = array();
    }
    $estados_factura = $modelo->estados();
    if($infoCliente->id_estado_factura !== NULL){
      $municipios_factura = $modelo->municipios($infoCliente->id_estado_factura);
      $parroquias_factura = $modelo->parroquias($infoCliente->id_municipio_factura);
    }else{
      $municipios_factura = array();
      $parroquias_factura = array();
    }
    if(!empty($infoCliente)){
      $response = array("response" => true,
                        "info" => $infoCliente,
                        "estadosfi" => $estados_fiscal,
                        "municipiosfi" => $municipios_fiscal,
                        "parroquiasfi" => $parroquias_fiscal,
                        "estadosfa" => $estados_factura,
                        "municipiosfa" => $municipios_factura,
                        "parroquiasfa" => $parroquias_factura);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
    return $response;
  }

    function modificarCliente(Request $request){

    $modelo = new ClienteModel();
    $parametros = array(
      "idCliente" => $request->input("idCliente"),
      "idUsuario" => (int) $request->input("idUsuario"),
      "idUsuario2" => (int) $request->input("idUsuario2"),
      "rif" => $request->input("rif"),
      "nit" => $request->input("nit"),
      "razon_social" => mb_strtoupper ($request->input("razon_social")),
      "parroquiafi" => $request->input("parroquiafi"),
      "ciudad_fiscal" => mb_strtoupper($request->input("ciudad_fiscal")),
      "avenida_calle_fiscal" => mb_strtoupper($request->input("avenida_calle_fiscal")),
      "edificio_quinta_fiscal" => mb_strtoupper($request->input("edificio_quinta_fiscal")),
      "piso_fiscal" => mb_strtoupper($request->input("piso_fiscal")),
      "numero_fiscal" => $request->input("numero_fiscal"),
      "telefono_fiscal" => $request->input("telefono_fiscal"),
      "fax_fiscal" => $request->input("fax_fiscal"),
      "email_fiscal" => strtolower($request->input("email_fiscal")),
      "descripcion_factura" => mb_strtoupper($request->input("descripcion_factura")),
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
    $response = $modelo->modificarCliente($parametros);
    return $response;
  }
}