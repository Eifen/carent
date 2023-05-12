<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ClienteModel extends Model
{

  function buscarUsuarios($nombre = null){

    if($nombre != null && trim($nombre) != ""){
      $sql_nombre = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($nombre).'%"';
    }else{
      $sql_nombre = "";
    }

    $usuario = DB::select('SELECT u.id,
                                   u.codigo,
                                   u.avatar,
                                   udi.id_tipo_documento_identidad AS id_tipo_documento,
                                   udi.documento AS cedula,
                                   u.id_cargo,
                                   CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                                   e.descripcion AS estatus,
                                   cu.correo_principal
                            FROM tbl_usuario u,
                                 tbl_estatus e,
                                 tbl_contacto_usuario cu,
                                 tbl_usuario_documento_identidad udi
                            WHERE u.id_cargo IN(16,17)
                            AND e.tabla = "tbl_usuario"
                            AND e.valor = u.id_estatus
                            AND u.id = cu.id_usuario
                            AND u.id = udi.id_usuario
                            '.$sql_nombre.'
                            ORDER BY nombre ASC');
    if(count($usuario) > 0){
      return $usuario;
    }
      return array();
  }

  function buscarUsuariosG($opcionBusqueda, $dato, $cargo){

    switch ((int) $opcionBusqueda) {
      case 1:
        $condicion = "WHERE u.codigo LIKE '%".$dato."%'";
      break;
      case 2:
        $condicion = "WHERE udi.documento LIKE '%".$dato."%'";
      break;
      case 3:
        $condicion = "WHERE (u.nombre_1 LIKE '%".$dato."%' OR u.nombre_2 LIKE '%".$dato."%')";
      break;
      case 4:
        $condicion = "WHERE (u.apellido_1 LIKE '%".$dato."%' OR u.apellido_2 LIKE '%".$dato."%')";
      break;
      default:
        $condicion = "WHERE u.codigo LIKE '%".$dato."%'";
      break;
    }

    $usuariosG = DB::select('SELECT u.id,
                                   u.codigo,
                                   u.avatar,
                                   udi.id_tipo_documento_identidad AS id_tipo_documento,
                                   udi.documento AS cedula,
                                   u.id_cargo,
                                   (SELECT ce.descripcion FROM tbl_cargo_empleado ce WHERE ce.id = u.id_cargo) cargo,
                                   CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                                   e.descripcion AS estatus,
                                   cu.correo_principal
                            FROM tbl_usuario u,
                                 tbl_estatus e,
                                 tbl_contacto_usuario cu,
                                 tbl_usuario_documento_identidad udi
                            '.$condicion.'
                            AND u.id_cargo > 12 AND u.id_cargo < 16
                            AND e.tabla = "tbl_usuario"
                            AND e.valor = u.id_estatus
                            AND u.id = cu.id_usuario
                            AND u.id = udi.id_usuario');

    if(count($usuariosG) > 0){
      return $usuariosG;
    }else{
      return array();
    }
  }

  function detalleUsuario($id_usuario){

    $info = DB::select('SELECT u.id,
                               u.codigo,
                               u.avatar,
                               udi.id_tipo_documento_identidad AS id_tipo_documento,
                               udi.documento AS cedula,
                               CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                               u.fecha_nacimiento,
                               e.descripcion AS estatus,
                               cu.correo_principal,
                               cu.correo_secundario,
                               cu.telefono_principal,
                               cu.telefono_secundario,
                               (SELECT d.descripcion FROM tbl_division d WHERE d.id = u.id_division) division,
                               (SELECT ce.descripcion FROM tbl_cargo_empleado ce WHERE ce.id = u.id_cargo) cargo,
                               (SELECT p.parroquia FROM tbl_parroquias p WHERE p.id = u.id_parroquia) parroquia,
                               (SELECT m.municipio
                                 FROM tbl_municipios m
                                  WHERE m.id = (SELECT p.id_municipio FROM tbl_parroquias p WHERE p.id = u.id_parroquia)) municipio,
                                (SELECT e.estado
                                  FROM tbl_estados e
                                   WHERE e.id = (SELECT m.id_estado
                                                FROM tbl_municipios m
                                                WHERE m.id = (SELECT p.id_municipio FROM tbl_parroquias p WHERE p.id = u.id_parroquia))) estado
                        FROM tbl_usuario u,
                             tbl_estatus e,
                             tbl_contacto_usuario cu,
                             tbl_usuario_documento_identidad udi
                        WHERE u.id = '.$id_usuario.'
                        AND e.tabla = "tbl_usuario"
                        AND e.valor = u.id_estatus
                        AND u.id = cu.id_usuario
                        AND u.id = udi.id_usuario');

    if(count($info) > 0){
      return $info[0];
    }else{
      return array();
    }
  }

  function estados(){

    $estados = DB::select('SELECT e.id,
                                  e.estado
                           FROM tbl_estados e
                           ORDER BY e.estado ASC');
      return $estados;
  }// Fin estados

  function municipios($id_estado){

    $municipios = DB::select('SELECT m.id,
                                     m.municipio
                           FROM tbl_municipios m
                           WHERE m.id_estado = '.$id_estado.'
                           ORDER BY m.municipio ASC');
    return $municipios;
  }// Fin municipios

  function parroquias($id_municipio){

     $parroquias = DB::select('SELECT p.id,
                                     p.parroquia
                              FROM tbl_parroquias p
                              WHERE p.id_municipio = '.$id_municipio.'
                              ORDER BY p.parroquia ASC');
    return $parroquias;
  }// Fin parroquias

  function codigoCliente(){

    $cliente = DB::select('SELECT
                                codigo
                           FROM tbl_cliente
                           ORDER BY codigo DESC');
    if(count($cliente) > 0){
      return $cliente[0];
    }else{
      return array();
    }

  }

  function paises(){

    $paises = DB::select('SELECT pa.id,
                                  pa.nombre,
                                  pa.codigo_telf
                           FROM tbl_paises pa');

      return $paises;
  }

  function servicios(){
    $paises = DB::select('SELECT ser.ServicioId, ser.NombreServicio FROM tbl_servicio ser WHERE ser.EstadoServicio = 1');
    return $paises;
  }

  function sectores(){
    $sector = DB::select('SELECT sec.SectorId, sec.SectorNombre FROM tbl_sector sec WHERE sec.SectorEstado = 1');
    return $sector;
  }

  function buscarCliente($codigo){

    $cliente = DB::select('SELECT id,
                                  codigo,
                                  email_fiscal
                           FROM tbl_cliente
                           WHERE codigo = "'.$codigo.'"');
    if(count($cliente) > 0){
      return $cliente[0];
    }else{
      return array();
    }
  }// Fin buscarCliente

  function buscarEmail($email_fiscal){

    if($email_fiscal === "" ){
      $parametros = '"'.$email_fiscal.'"';
    }else{
      $parametros = '"'.$email_fiscal.'"';
    }
    $email = DB::select('SELECT
                            (SELECT COUNT(*)
                             FROM tbl_cliente cu
                             WHERE cu.email_fiscal IN('.$parametros.')) email_fiscal');
    if((int) $email[0]->email_fiscal === 0){
      return array("response" => false, "message" => "No se encontraron coincidencias");
    }else{
      return array("response" => true, "message" => "Ya se encuentran registrados los correos");
    }
  }// Fin buscarCorreos

  function crearCliente($parametros){

    $funcionario = DB::select('call sp_create_client(?,?,?,?,?,?,?,?,?,?,?,?,?,?,@respuesta)',$parametros);
    $respuestaSp = DB::select('SELECT @respuesta AS respuesta_json');
    $respuestaJson = json_decode($respuestaSp[0]->respuesta_json, true);

    return $respuestaJson;

  }// Fin crearCliente

  function permisoActualizar($id_usuario, $id_menu){

      $permiso = DB::select('SELECT CASE mu.U
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                    END AS permiso
                             FROM tbl_menu_usuario mu
                             WHERE mu.id_usuario = '.$id_usuario.'
                             AND mu.U = 1
                             AND mu.id_menu = '. $id_menu);

      if(count($permiso) > 0){

        return $permiso[0]->permiso;

      }else{

        return false;

      }

    }

    function permisoCrear($id_usuario, $id_menu){

      $permiso = DB::select('SELECT CASE mu.C
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                    END AS permiso
                             FROM tbl_menu_usuario mu
                             WHERE mu.id_usuario = '.$id_usuario.'
                             AND mu.C = 1
                             AND mu.id_menu = '. $id_menu);

      if(count($permiso) > 0){

        return $permiso[0]->permiso;

      }else{

        return false;

      }

    }

  function buscarClientes($buscarPor, $dato){

    switch ((int) $buscarPor) {
      case 1:
          $condicion = "WHERE c.codigo LIKE '%".$dato."%'";
      break;
      case 2:
        $condicion = "WHERE c.razon_social LIKE '%".$dato."%'";
        break;
      case 3:
        $condicion = "WHERE c.rif LIKE '%".$dato."%'";
      break;
      default:
        $condicion = "WHERE c.codigo LIKE %'".$dato."'%";
      break;
    }
    $clientes = DB::select('SELECT c.id,
                                   c.codigo,
                                   UPPER(c.razon_social) AS razon_social,
                                   LOWER(c.email_fiscal) AS email_fiscal,
                                   UPPER(c.rif) AS rif
                            FROM tbl_cliente c
                            '.$condicion.'
                         ');
    if(count($clientes) > 0){
      return $clientes;
    }else{
      return array();
    }
  }

  function detalleCliente($id_cliente){

    $info = DB::select('SELECT id,
                                id_usuario_socio,
                                (SELECT codigo FROM tbl_usuario WHERE id_usuario_socio = id) codigoU,
                                (SELECT CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) FROM tbl_usuario u WHERE id_usuario_socio = id) nombre,
                                codigo,
                                rif,
                                razon_social,
                                direccion,
                                telefono_fiscal,
                                pagina_web,
                                email_fiscal,
                                (SELECT pa.nombre FROM tbl_paises pa WHERE pa.id = id_pais) pais
                          FROM tbl_cliente
                          WHERE id = '.$id_cliente.'
                        ');
    if(count($info) > 0)
    {
      return $info[0];
    }else{
      return array();
    }
  }

  function detalleFactCliente($id_proyecto, $id_cliente){

    $info = DB::select('SELECT  id,
                                ciudad_factura,
                                avenida_calle_factura,
                                edificio_quinta_factura,
                                piso_factura,
                                numero_factura,
                                telefono_factura,
                                fax_factura,
                                email_factura,
                                id_parroquia_factura,
                                 (SELECT id
                                  FROM tbl_municipios
                                  WHERE id = (SELECT id_municipio FROM tbl_parroquias WHERE id =id_parroquia_factura)) id_municipio_factura,
                                 (SELECT id
                                  FROM tbl_estados
                                  WHERE id = (SELECT id_estado
                                                FROM tbl_municipios
                                                WHERE id = (SELECT id_municipio FROM tbl_parroquias WHERE id = id_parroquia_factura))) id_estado_factura
                          FROM tbl_cliente_facturacion
                          WHERE id_cliente = '.$id_cliente.'
                          AND id_proyecto = '.$id_proyecto.'');

    if(count($info) > 0){
      return $info[0];
    }else{
      return array();
    }
  }

  function estatusCliente(){

      $estatus = DB::select('SELECT e.valor AS id,
                                    e.descripcion
                             FROM tbl_estatus e
                             WHERE e.tabla = "tbl_cliente"
                             ORDER BY descripcion ASC');

      return $estatus;

    }

  function detalleClienteModificar($id_cliente){

    $info = DB::select('SELECT id,
                                 id_usuario_socio,
                                 (SELECT codigo FROM tbl_usuario WHERE id_usuario_socio = id) codigoU,
                                (SELECT CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) FROM tbl_usuario u WHERE id_usuario_socio = id) nombre,
                                 codigo,
                                 rif,
                                 nit,
                                 razon_social,
                                 direccion,
                                 telefono_fiscal,
                                 pagina_web,
                                 email_fiscal,
                                 id_estatus,
                                 SectorAsociado,
                                 ServicioAsociado,
                                 id_pais,
                                 (SELECT pa.nombre FROM tbl_paises pa WHERE pa.id = id_pais) pais,
                                 (SELECT sec.SectorNombre FROM tbl_sector sec WHERE sec.SectorId = SectorAsociado) sector,
                                 (SELECT ser.NombreServicio FROM tbl_servicio ser WHERE ser.ServicioId = ServicioAsociado) servicio
                          FROM tbl_cliente
                          WHERE id = '.$id_cliente.'');

    if(count($info) > 0){
      return $info[0];
    }else{
      return array();
    }
  }

    function UpdateClientData($params) {

        $sp = DB::select('CALL sp_update_client(?,?,?,?,?,?,?,?,?,?,?,?,@response)',$params);
        $responseSp = DB::select('SELECT @response AS objectJson');
        $responseSp = json_decode($responseSp[0]->objectJson, true);

        return $responseSp;

    }

}
