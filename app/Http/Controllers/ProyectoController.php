<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ConfigsModel;
use App\Models\ProyectoModel;
use App\Models\AuditoriaLogModel;
use Illuminate\Http\RedirectResponse;

class ProyectoController extends Controller
{

    function dataInicialNuevoProyecto(){

      $modelo = new ProyectoModel();
      $divisiones = $modelo->divisiones();
      $estatus = $modelo->estatusProyectos();
      $empresas = $modelo->empresas();
      $monedas = $modelo->monedas(true);

      return [
        "divisiones" => $divisiones,
        "estatus" => $estatus,
        "empresas" => $empresas,
        "monedas" => $monedas
      ];

    }

    function buscarClienteProyecto(Request $request){

      $modelo = new ProyectoModel();
      $dato = $request["nombreCliente"];

      $clientes = $modelo->clientes($dato,5);

      return [
        "response" => true,
        "clientes" => $clientes
      ];

    }

    function buscarSocioProyecto(Request $request){

      $modelo = new ProyectoModel();
      $dato = $request["nombreSocio"];

      $socios = $modelo->socios($dato,5);

      return [
        "response" => true,
        "socios" => $socios
      ];

    }

    function buscarGerenteProyecto(Request $request){

      $modelo = new ProyectoModel();
      $dato = $request["nombreGerente"];

      $gerentes = $modelo->gerentes($dato,5);

      return [
        "response" => true,
        "gerentes" => $gerentes
      ];

    }

    function proyectoGerentesDivision(Request $request){

      $modelo = new ProyectoModel();
      $id_division = $request["id_division"];

      $gerentes = $modelo->proyectoGerentesDivision($id_division);

      return [
        "response" => true,
        "gerentes" => $gerentes
      ];

    }

    function crearProyecto(Request $request){

      $modelo = new ProyectoModel();
      $descripcion = strtoupper($request->input("descripcion"));
      $cliente = $request->input("cliente");
      $socio = $request->input("socio");
      $socioCalidad = $request->input("socioCalidad");
      $gerente = $request->input("gerente");
      $fechaContratacion = $request->input("fechaContratacion");
      $divisiones = $request->input("divisiones");
      $estatus = $request->input("estatus");
      $id_moneda = $request->input("id_moneda");
      $monto = $request->input("monto");
      $empresa = $request->input("empresa");

      $response = $modelo->crearProyecto($descripcion,$cliente,$socio,$socioCalidad,$gerente,$fechaContratacion,$divisiones,$estatus,$id_moneda,$monto,$empresa);

      if($response["response"]){

        $parametros = [
          "accion" => 'Registro del proyecto: '.$descripcion.'. Cliente: '.$response["cliente"],
          "direccion_ip" => $request->session()->get('usuario_ip'),
          "fecha" => date("Y-m-d H:i:s"),
          "tabla" => 'tbl_proyecto',
          "usuario_id" => $request->session()->get('usuario_id')
        ];

        $modeloAudit = new AuditoriaLogModel();
        $modeloAudit->logs_auditoria($parametros);

      }

      return $response;

    }

    function dataInicialListadoProyectos(){

      $modelo = new ProyectoModel();
      $paginar = 50;
      $permisoActualizar = $modelo->permisoActualizar(session("usuario_id"), 10);
      $proyectos = $modelo->proyectos(session("division_id"), $paginar);
      $divisiones = $modelo->divisiones();
      $estatus = $modelo->estatusProyectos();
      $cantidadPaginas = $modelo->cantidadPaginas($paginar);

      return [
        "divisiones" => $divisiones,
        "estatus" => $estatus,
        "numero_paginas" => $cantidadPaginas,
        "paginar" => $paginar,
        "permisoActualizar" => $permisoActualizar,
        "proyectos" => $proyectos
      ];

    }

    function buscarProyectos(Request $request){

      $modelo = new ProyectoModel();
      $paginar = $request->input("paginar");
      $desde = $request->input("desde");
      $cliente = $request->input("cliente");
      $divisiones = ($request->input("divisiones") == null) ? [] : $request->input("divisiones");
      $proyecto = $request->input("proyecto");
      $estatus = $request->input("estatus");
      $proyectos = $modelo->proyectos(session("division_id"), $paginar, $desde, $proyecto, $cliente, $divisiones, $estatus);
      $cantidadPaginas = $modelo->cantidadPaginas($paginar, $proyecto, $cliente, $divisiones, $estatus);

      return array("proyectos" => $proyectos, "paginas" => $cantidadPaginas);

    }

    function formModificarProyecto($idProyecto, Request $request){

      $request->session()->put('id_proyecto_mod', $idProyecto);
      return view('proyecto/modificarProyecto');

    }

    function detalleProyectoModificar(Request $request){

      $modelo = new ProyectoModel();
      $id_proyecto = (int) session("id_proyecto_mod");

      $infoProyecto = $modelo->detalleProyectoModificar($id_proyecto);
      $infoDivProyecto = $modelo->detalleDivisionProyecto($id_proyecto);
      $clientes = $modelo->clientes();
      $divisiones = $modelo->divisiones();
      $estatus = $modelo->estatusProyectos();
      $empresas = $modelo->empresas();
      $monedas = $modelo->monedas(false);

      if(!empty($infoProyecto)){

        $response = array("response" => true,
                          "info" => $infoProyecto,
                          "infodivi" => $infoDivProyecto,
                          'clientes' => $clientes,
                          'divisiones' => $divisiones,
                          "estatus" => $estatus,
                          "empresas" => $empresas,
                          "monedas" => $monedas);
      }else{

        $response = array("response" => false, "message" => "No se encontraron resultados");

      }

      return $response;

    }

    function modificarProyecto(Request $request){

      $modelo = new ProyectoModel();

      $idProyecto = $request->input("id_proyecto");
      $descripcion = strtoupper($request->input("descripcion"));
      $cliente = $request->input("cliente");
      $socio = $request->input("socio");
      $socioCalidad = $request->input("socioCalidad");
      $gerente = $request->input("gerente");
      $fechaContratacion = $request->input("fechaContratacion");
      $divisiones = $request->input("divisiones");
      $estatus = $request->input("estatus");
      $id_moneda = $request->input("id_moneda");
      $monto = $request->input("monto");
      $divisiones_v =  $modelo->detalleDivisionProyecto($idProyecto);
      $empresa = $request->input("empresa");

      $parametros_proyecto = array(
        "descripcion" => $descripcion,
        "id_cliente" => $cliente,
        "fecha_contratacion" => $fechaContratacion,
        "id_estatus" => $estatus,
        "id_socio" => $socio,
        "id_socio_calidad" => $socioCalidad,
        "id_gerente" => $gerente,
        "id_moneda" => $id_moneda,
        "monto" => $monto,
        "id_empresa" => $empresa
      );

      $response = $modelo->modificarProyecto($idProyecto, $parametros_proyecto, $divisiones, $divisiones_v);

      if($response["response"]){

        $parametros = [
          "accion" => 'Modificacion del proyecto: '.$descripcion.'. Cliente: '.$response["cliente"],
          "direccion_ip" => $request->session()->get('usuario_ip'),
          "fecha" => date("Y-m-d H:i:s"),
          "tabla" => 'tbl_proyecto',
          "usuario_id" => $request->session()->get('usuario_id')
        ];

        $modeloAudit = new AuditoriaLogModel();
        $modeloAudit->logs_auditoria($parametros);

      }

      return $response;

    }

    function asignarProyectos(Request $request){

      $modelo = new ProyectoModel();

      $id_usuario = $request->session()->get('usuario_id');
      $infoUsuario = $modelo->detalleInicioUsuario($id_usuario);
      $proyectos = $modelo->proyectosDivision($id_usuario, $infoUsuario->id_division);
      $estatus = $modelo->estatusProyectos();

      return array("proyectos" => $proyectos, "estatus" => $estatus);

    }

    function buscardiviProyectos(Request $request){

      $modelo = new ProyectoModel();
      $id_usuario = $request->session()->get('usuario_id');
      $infoUsuario = $modelo->detalleInicioUsuario($id_usuario);
      $permisoActualizar = $modelo->permisoActualizar(session("usuario_id"), 11);
      $cliente = $request->input("cliente");
      $proyecto = $request->input("proyecto");
      $estatus = $request->input("estatus");
      $proyectoBusqueda = $modelo->proyectoBusqueda($proyecto, $cliente, $estatus);
      $proyectos = $modelo->proyectosDivision($id_usuario, $infoUsuario->id_division);

      return array("proyectoBusqueda" => $proyectoBusqueda, "proyectos" => $proyectos);
    }

    function DetalleDivProyecto(Request $request){

    $modelo = new ProyectoModel();
    $idDproyecto = (int) $request->input("idDproyecto");
    $infoDproyecto = $modelo->DetalleDivProyecto($idDproyecto);
    $infoAproyecto = $modelo->DetalleAnaProyecto($idDproyecto);
    if(!empty($infoDproyecto)){
      $response = array("response" => true, "infoDproyecto" => $infoDproyecto, "infoAproyecto" => $infoAproyecto);
    }else{
      $response = array("response" => false, "message" => "No se encontraron resultados");
    }
    return $response;
  }

    function detalleAnalistaProyecto(Request $request){

      $modelo = new ProyectoModel();
      $id_proyecto = (int) $request->input("idDproyecto");
      $id_proyecto_division = (int) $request->input("id_proyecto_division");
      $id_usuario = $request->session()->get('usuario_id');
      $infoUsuario = $modelo->detalleInicioUsuario($id_usuario);
      $datosProyecto = $modelo->datosProyecto($id_proyecto,$infoUsuario->id_division);
      //$analistas = $modelo->analistasProyecto($id_usuario,11,$id_proyecto,$infoUsuario->id_division);
      $empleados = $modelo->empleadosProyecto($id_proyecto,$infoUsuario->id_division, $id_proyecto_division);

      if(!empty($datosProyecto)){
      return array("response" => true, "proyecto" => $datosProyecto, "empleados" => $empleados);
      }else{
        $response = array("response" => false, "message" => "No se encontraron resultados");
      }
      return $response;
    }

    function agregarAnalistaProy(Request $request){

      $modelo = new ProyectoModel();
      $estado = $request->input("estado");
      $idUsuario = $request->input("idUsuario");
      $idProyecto = $request->input("idDproyecto");
      $id_proyecto_division = $request->input("id_proyecto_division");
      $usuario_id = $request->session()->get('usuario_id');
      $fecha = date("Y-m-d H:i:s");
      $direccion_ip = $request->session()->get('usuario_ip');
      $analis = $modelo->agregarAnalistaProy($estado,$idUsuario,$idProyecto,$id_proyecto_division);

      if($analis["response"]){

        $parametros = [
          "accion" => 'Asignacion del analista codigo: '.$analis["analista"].'. Al proyecto: '.$analis["proyecto"],
          "direccion_ip" => $request->session()->get('usuario_ip'),
          "fecha" => date("Y-m-d H:i:s"),
          "tabla" => 'tbl_proyecto_analista',
          "usuario_id" => $request->session()->get('usuario_id')
        ];

        $modeloAudit = new AuditoriaLogModel();
        $modeloAudit->logs_auditoria($parametros);

      }

      $id_usuario = $request->session()->get('usuario_id');
      $infoUsuario = $modelo->detalleInicioUsuario($id_usuario);
      $datosProyecto = $modelo->datosProyecto($idProyecto,$infoUsuario->id_division);
      $analistas = $modelo->analistasProyecto($id_usuario,11,$idProyecto,$infoUsuario->id_division);

      $response = array("response" => true, "analis" => $analis,"analistas" => $analistas, "proyecto" => $datosProyecto);

      return $response;

    }

    function modAnalistaProy(Request $request){

      $modelo = new ProyectoModel();
      $idAnaProy = $request->input("idAnaProy");
      $estatus = $modelo->estatusAnalistaProy($idAnaProy);
      $idProyecto = $request->input("idDproyecto");
      if ($estatus->id_estatus === 1) {
        $estado = 0;
      }
      if($estatus->id_estatus === 0) {
        $estado = 1;
      }

      $analis = $modelo->modAnalistaProy($estado,$idAnaProy,$idProyecto);

      if($analis["response"]){

        $parametros = [
          "accion" => $analis["accion"],
          "direccion_ip" => $request->session()->get('usuario_ip'),
          "fecha" => date("Y-m-d H:i:s"),
          "tabla" => 'tbl_proyecto_analista',
          "usuario_id" => $request->session()->get('usuario_id')
        ];

        $modeloAudit = new AuditoriaLogModel();
        $modeloAudit->logs_auditoria($parametros);

      }

      $id_usuario = $request->session()->get('usuario_id');
      $infoUsuario = $modelo->detalleInicioUsuario($id_usuario);
      $datosProyecto = $modelo->datosProyecto($idProyecto,$infoUsuario->id_division);
      $analistas = $modelo->analistasProyecto($id_usuario,11,$idProyecto,$infoUsuario->id_division);
      $response = array("response" => true, "analis" => $analis,"analistas" => $analistas, "proyecto" => $datosProyecto);
      return $response;

    }

    function asigHorasAnalistaProy(Request $request){

      $modelo = new ProyectoModel();

      $id_proyecto_division = $request->input("id_proyecto_division");
      $id_proyecto = $request->input("id_proyecto");
      $empleadosA = $request->input("empleados");
      //$usuario_id = $request->session()->get('usuario_id');
      $response = $modelo->asigHorasAnalistaProy($id_proyecto_division, $id_proyecto, $empleadosA);

      $id_usuario = $request->session()->get('usuario_id');
      $infoUsuario = $modelo->detalleInicioUsuario($id_usuario);
      $empleados = $modelo->empleadosProyecto($id_proyecto,$infoUsuario->id_division,$id_proyecto_division);

      //if($analis["response"]){

        //$modeloAudit = new AuditoriaLogModel();

          //$parametros = [
            //"accion" => $analis["horas"],
            //"direccion_ip" => $request->session()->get('direccion_ip'),
            //"fecha" => date("Y-m-d H:i:s"),
            //"tabla" => 'tbl_proyecto_analista',
            //"usuario_id" => $request->session()->get('usuario_id')
          //];

          //$modeloAudit->logs_auditoria($parametros);

      //}

      $response = array("response" => $response, "empleados" => $empleados);
      return $response;

    }

    function formCargarHoras($idProyAnalista, Request $request){

      $request->session()->put('idProyAnalista', $idProyAnalista);
      return view('horasCargables/cargarHoras');

    }

    function agregarMontoAdicionalProy(Request $request){

      $modelo = new ProyectoModel();

      $parametros = array(
        "monto" => $request->input("monto"),
        "id_proyecto" => $request->input("id_proyecto"),
        "fecha" => date("Y-m-d"),
        "id_estatus" => 1
      );

      $agregar_monto = $modelo->agregarMontoAdicionalProy($parametros);
      $montos = $modelo->montosAdicionesProy($request->input("id_proyecto"));

      if($agregar_monto){

        return [
          "message" => "Monto agregado con éxito!",
          "montos" => $montos,
          "response" => true
        ];

      }else{

        return [
          "message" => "Ocurrió un error al tratar de agregar el monto!",
          "response" => false
        ];

      }

    }

    function montosAdicionesProy(Request $request){

      $modelo = new ProyectoModel();

      $id_proyecto = $request->input("id_proyecto");

      $montos = $modelo->montosAdicionesProy($id_proyecto);

      return [
        "montos" => $montos
      ];

    }

    function eliminarMontosAdicionesProy(Request $request){

      $modelo = new ProyectoModel();

      $id_proyecto = $request->input("id_proyecto");
      $id_monto = $request->input("id_monto");

      if($modelo->eliminarMontosAdicionesProy($id_monto)){

        $montos = $modelo->montosAdicionesProy($id_proyecto);

        return [
          "message" => "Monto eliminado con éxito!",
          "montos" => $montos,
          "response" => true
        ];

      }else{

        return [
          "message" => "Error al momento de eliminar el monto.",
          "response" => false
        ];

      }

    }

    function horasAdicionesProyDiv(Request $request){

      $modelo = new ProyectoModel();

      $id_proy_div = $request->input("id_proy_div");

      $horas = $modelo->horasAdicionesProyDiv($id_proy_div);

      return [
        "horas" => $horas
      ];

    }

    function agregarHoraAdicionalProyDiv(Request $request){

      $modelo = new ProyectoModel();

      $parametros = array(
        "horas" => $request->input("horas"),
        "id_proy_div" => $request->input("id_proy_div"),
        "fecha" => date("Y-m-d"),
        "id_estatus" => 1
      );

      $agregar_horas = $modelo->agregarHoraAdicionalProyDiv($parametros);
      $horas = $modelo->horasAdicionesProyDiv($request->input("id_proy_div"));
      $id_proyecto = (int) session("id_proyecto_mod");
      $infoProyecto = $modelo->detalleProyectoModificar($id_proyecto);

      if($agregar_horas){

        return [
          "info" => $infoProyecto,
          "message" => "Horas agregadas con éxito!",
          "horas" => $horas,
          "response" => true
        ];

      }else{

        return [
          "message" => "Ocurrió un error al tratar de agregar las horas!",
          "response" => false
        ];

      }

    }

    function eliminarHoraAdicionalProyDiv(Request $request){

      $modelo = new ProyectoModel();

      $id = $request->input("id");

      if($modelo->eliminarHoraAdicionalProyDiv($id)){

        $horas = $modelo->horasAdicionesProyDiv($request->input("id_proy_div"));
        $id_proyecto = (int) session("id_proyecto_mod");
        $infoProyecto = $modelo->detalleProyectoModificar($id_proyecto);
        return [
          "message" => "horas eliminadas con éxito!",
          "info" => $infoProyecto,
          "horas" => $horas,
          "response" => true
        ];

      }else{

        return [
          "message" => "Error al momento de eliminar las horas.",
          "response" => false
        ];

      }

    }

}
