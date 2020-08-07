<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class UsuarioModel extends Model
{

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

    function divisiones(){

      $divisiones = DB::select('SELECT d.id,
                                       d.descripcion
                                FROM tbl_division d
                                WHERE d.id_estatus = 1
                                ORDER BY d.descripcion ASC');

      return $divisiones;

    }// Fin divisiones

    function cargos(){

      $cargos = DB::select('SELECT c.id,
                                   c.descripcion
                             FROM tbl_cargo_empleado c
                             WHERE c.id_estatus = 1
                             ORDER BY c.descripcion ASC');

      return $cargos;

    }// Fin cargos

    function tipoDocumentos(){

      $sql = DB::select('SELECT tdi.id,
                                tdi.descripcion
                         FROM tbl_tipo_documento_identidad tdi
                         WHERE tdi.id_estatus = 1
                         ORDER BY tdi.id ASC');

      return $sql;

    }

    function buscarUsuario($codigo){

      $usuario = DB::select('SELECT u.id,
                                    u.clave,
                                    u.avatar,
                                    u.id_estatus,
                                    e.descripcion AS estatus,
                                    cu.correo_principal,
                                    cu.correo_secundario,
                                    cu.telefono_principal,
                                    cu.telefono_secundario
                             FROM tbl_usuario u,
                                  tbl_estatus e,
                                  tbl_contacto_usuario cu
                             WHERE codigo = "'.$codigo.'"
                             AND e.tabla = "tbl_usuario"
                             AND e.valor = u.id_estatus
                             AND u.id = cu.id_usuario');

      if(count($usuario) > 0){

        return $usuario[0];

      }else{

        return array();

      }

    }// Fin buscarUsuario

    function buscarCorreos($correoPrincipal, $correoSecundario){

      if($correoSecundario === "" or $correoSecundario === NULL){
        $parametros = '"'.$correoPrincipal.'"';
      }else{
        $parametros = '"'.$correoPrincipal.'","'.$correoSecundario.'"';
      }

      $correos = DB::select('SELECT
                              (SELECT COUNT(*)
                               FROM tbl_contacto_usuario cu
                               WHERE cu.correo_principal IN('.$parametros.')) correo_principal,
                              (SELECT COUNT(*)
                               FROM tbl_contacto_usuario cu
                               WHERE cu.correo_secundario IN('.$parametros.')) correo_secundario');

      if((int) $correos[0]->correo_principal === 0 && (int) $correos[0]->correo_secundario === 0){

        return array("response" => false, "message" => "No se encontraron coincidencias");

      }else{

        return array("response" => true, "message" => "Ya se encuentran registrados los correos");

      }

    }// Fin buscarCorreos

    function crearUsuario($parametros){

      DB::beginTransaction();

      $data = array("codigo" => $parametros["codigoUsuario"],
                    "nombre_1" => $parametros["nombre1"],
                    "nombre_2" => $parametros["nombre2"],
                    "apellido_1" => $parametros["apellido1"],
                    "apellido_2" => $parametros["apellido2"],
                    "fecha_nacimiento" => $parametros["fechaNacimiento"],
                    "id_cargo" => $parametros["cargo"],
                    "id_division" => $parametros["division"],
                    "id_parroquia" => $parametros["parroquia"],
                    "id_estatus" => 1,
                    "clave" => $parametros["clave"],
                    "fecha_ingreso" => $parametros["fechaIngreso"]);

      $idUsuario = DB::table('tbl_usuario')->insertGetId($data);

      $data = array("id_usuario" => $idUsuario,
                    "id_tipo_documento_identidad" => $parametros["tipoDocumento"],
                    "documento" => $parametros["cedula"]);

      if(DB::table('tbl_usuario_documento_identidad')->insert($data)){

        $data = array("id_usuario" => $idUsuario,
                      "correo_principal" => $parametros["correoPrincipal"],
                      "correo_secundario" => $parametros["correoSecundario"],
                      "telefono_principal" => $parametros["telefono1"],
                      "telefono_secundario" => $parametros["telefono2"]);

        $contacto = DB::table('tbl_contacto_usuario')->insert($data);

        if($contacto){

          DB::commit();
          return array("response" => true, "message" => "Usuario Creado con Éxito; la contraseña es la misma cédula, se recomienda cambiarla al inicio de sesión.");

        }else{

          DB::rollBack();
          return array("response" => false, "message" => "Error al tratar de crear el usuario.");

        }

      }else{

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de crear el usuario.");

      }

    }// Fin crearUsuario

    function buscarUsuarios($opcionBusqueda, $dato){

      switch ((int) $opcionBusqueda) {
        case 1:
          $condicion = "WHERE u.codigo LIKE '%".$dato."%'";
          break;
        case 2:
            $condicion = "WHERE udi.documento LIKE '%".$dato."%'";
            break;
        case 3:
            $condicion = "WHERE cu.correo_principal LIKE '%".$dato."%'";
            break;
        case 4:
            $condicion = "WHERE (u.nombre_1 LIKE '%".$dato."%' OR u.nombre_2 LIKE '%".$dato."%')";
            break;
        case 5:
            $condicion = "WHERE (u.apellido_1 LIKE '%".$dato."%' OR u.apellido_2 LIKE '%".$dato."%')";
            break;
        default:
          $condicion = "WHERE u.codigo LIKE '%".$dato."%'";
          break;
      }

      $usuarios = DB::select('SELECT u.id,
                                     u.codigo,
                                     u.avatar,
                                     CONCAT(tdi.abreviatura,"-",udi.documento) cedula,
                                     CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                                     e.descripcion AS estatus,
                                     cu.correo_principal
                             FROM tbl_usuario u,
                                  tbl_estatus e,
                                  tbl_contacto_usuario cu,
                                  tbl_usuario_documento_identidad udi,
                                  tbl_tipo_documento_identidad tdi
                             '.$condicion.'
                             AND e.tabla = "tbl_usuario"
                             AND e.valor = u.id_estatus
                             AND u.id = cu.id_usuario
                             AND u.id = udi.id_usuario
                             AND udi.id_tipo_documento_identidad = tdi.id');

      if(count($usuarios) > 0){

        return $usuarios;

      }else{

        return array();

      }

    }

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

    function detalleUsuario($id_usuario){

      $info = DB::select('SELECT u.id,
                                 u.codigo,
                                 u.avatar,
                                 udi.id AS id_tipo_documento,
                                 udi.documento AS cedula,
                                 u.nombre_1,
                                 u.nombre_2,
                                 u.apellido_1,
                                 u.apellido_2,
                                 DATE_FORMAT(u.fecha_nacimiento,"%d/%m/%Y") fecha_nacimiento,
                                 DATE_FORMAT(u.fecha_ingreso,"%d/%m/%Y") fecha_ingreso,
                                 DATE_FORMAT(u.fecha_egreso,"%d/%m/%Y") fecha_egreso,
                                 e.descripcion AS estatus,
                                 cu.correo_principal,
                                 cu.correo_secundario,
                                 cu.telefono_principal,
                                 cu.telefono_secundario,
                                 tdi.descripcion tipo_documento_identidad,
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
                               tbl_usuario_documento_identidad udi,
                               tbl_tipo_documento_identidad tdi
                          WHERE u.id = '.$id_usuario.'
                          AND e.tabla = "tbl_usuario"
                          AND e.valor = u.id_estatus
                          AND u.id = cu.id_usuario
                          AND u.id = udi.id_usuario
                          AND udi.id_tipo_documento_identidad = tdi.id');

      if(count($info) > 0){

        return $info[0];

      }else{

        return array();

      }

    }

    function detalleUsuarioModificar($id_usuario){

      $info = DB::select("SELECT u.id,
                                 u.codigo,
                                 u.avatar,
                                 udi.id AS id_usuario_documento_identidad,
                                 udi.id_tipo_documento_identidad AS id_tipo_documento,
                                 udi.documento AS cedula,
                                 u.nombre_1,
                                 u.nombre_2,
                                 u.apellido_1,
                                 u.apellido_2,
                                 DATE_FORMAT(u.fecha_nacimiento,'%Y-%m-%d') fecha_nacimiento_utc,
                                 DATE_FORMAT(u.fecha_nacimiento,'%d/%m/%Y') fecha_nacimiento,
                                 DATE_FORMAT(u.fecha_ingreso,'%Y-%m-%d') fecha_ingreso_utc,
                                 DATE_FORMAT(u.fecha_ingreso,'%d/%m/%Y') fecha_ingreso,
                                 DATE_FORMAT(u.fecha_egreso,'%Y-%m-%d') fecha_egreso_utc,
                                 DATE_FORMAT(u.fecha_egreso,'%d/%m/%Y') fecha_egreso,
                                 u.id_estatus,
                                 cu.correo_principal,
                                 cu.correo_secundario,
                                 cu.telefono_principal,
                                 cu.telefono_secundario,
                                 (SELECT d.id FROM tbl_division d WHERE d.id = u.id_division) id_division,
                                 (SELECT ce.id FROM tbl_cargo_empleado ce WHERE ce.id = u.id_cargo) id_cargo,
                                 u.id_parroquia,
                                 (SELECT m.id
                                  FROM tbl_municipios m
                                  WHERE m.id = (SELECT p.id_municipio FROM tbl_parroquias p WHERE p.id = u.id_parroquia)) id_municipio,
                                 (SELECT e.id
                                  FROM tbl_estados e
                                  WHERE e.id = (SELECT m.id_estado
                                                FROM tbl_municipios m
                                                WHERE m.id = (SELECT p.id_municipio FROM tbl_parroquias p WHERE p.id = u.id_parroquia))) id_estado
                          FROM tbl_usuario u,
                               tbl_estatus e,
                               tbl_contacto_usuario cu,
                               tbl_usuario_documento_identidad udi
                          WHERE u.id = ".$id_usuario."
                          AND e.tabla = 'tbl_usuario'
                          AND e.valor = u.id_estatus
                          AND u.id = cu.id_usuario
                          AND u.id = udi.id_usuario");

      if(count($info) > 0){

        return $info[0];

      }else{

        return array();

      }

    }

    function estatusUsuario(){

      $estatus = DB::select('SELECT e.valor AS id,
                                    e.descripcion
                             FROM tbl_estatus e
                             WHERE e.tabla = "tbl_usuario"
                             ORDER BY descripcion ASC');

      return $estatus;

    }

    function modificarUsuario($parametros){

      DB::beginTransaction();

      try {

        $data = array("nombre_1" => $parametros["nombre1"],
                      "nombre_2" => $parametros["nombre2"],
                      "apellido_1" => $parametros["apellido1"],
                      "apellido_2" => $parametros["apellido2"],
                      "fecha_nacimiento" => $parametros["fechaNacimiento"],
                      "id_cargo" => $parametros["cargo"],
                      "id_division" => $parametros["division"],
                      "id_parroquia" => $parametros["parroquia"],
                      "id_estatus" => $parametros["estatus"],
                      "fecha_ingreso" => $parametros["fechaIngreso"],
                      "fecha_egreso" => $parametros["fechaEgreso"]);

        $update = DB::table('tbl_usuario')->where("id",$parametros["idUsuario"])->update($data);

        $data = array("id_tipo_documento_identidad" => $parametros["tipoDocumento"],
                      "documento" => $parametros["cedula"]);

        DB::table('tbl_usuario_documento_identidad')->where("id",$parametros["idUsuarioDocumentoIdentidad"])->update($data);

        $data = array("correo_principal" => $parametros["correoPrincipal"],
                      "correo_secundario" => $parametros["correoSecundario"],
                      "telefono_principal" => $parametros["telefono1"],
                      "telefono_secundario" => $parametros["telefono2"]);

        $contacto = DB::table('tbl_contacto_usuario')->where("id_usuario",$parametros["idUsuario"])->update($data);

        DB::commit();
        
        return array("response" => true, "message" => "Usuario actualizado con Éxito!.");

      } catch(\Illuminate\Database\QueryException $ex){

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de actualizar la información del usuario.");

      }

    }// Fin modificarUsuario

    function detalleMenu($id_usuario){

      $info = DB::select('SELECT *
                          FROM tbl_menu_usuario m
                          WHERE m.id_usuario = '.$id_usuario.'
                        ');

      if(count($info) > 0){

        return $info;

      }else{

        return array();

      }

    }

    function divisionUsu($id_usuario){

      $info = DB::select('SELECT u.id_division,
                                 u.id_cargo,
                                 u.codigo,
                                 CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)nombre,
                                 (SELECT d.descripcion FROM tbl_division d WHERE d.id = u.id_division)Ddivision,
                                 (SELECT ce.descripcion FROM tbl_cargo_empleado ce WHERE ce.id = u.id_cargo)Dcargo
                          FROM tbl_usuario u
                          WHERE u.id = '.$id_usuario.'
                        ');

      if(count($info) > 0){

        return $info[0];

      }else{

        return array();

      }

    }

    function agregarMenUsu($menuCr,$C,$R,$U,$D, $id_usuario){

    DB::beginTransaction();

     $data = array("id_usuario" => $id_usuario,
                   "id_menu" => $menuCr,
                   "C" => $C,
                   "R" => $R,
                   "U" => $U,
                   "D" => $D);
      $contacto = DB::table('tbl_menu_usuario')->insert($data);
      if($contacto){
        DB::commit();
        return array("response" => true, "message" => "Menu Creada con Éxito.");
      }else{
       DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de crear el menu.");
      }
    }

    function quitarMenUsu($menuCr,$idUsuario){

    DB::beginTransaction();

      $contacto = DB::table('tbl_menu_usuario')->where([['id_usuario', '=', $idUsuario],['id_menu', '=', $menuCr]])->delete();
      if($contacto){
        DB::commit();
        return array("response" => true, "message" => "menu eliminado con exito.");
      }else{
       DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de eliminar el menu.");
      }
  }

  function modificarMenUsu($menuCr,$C,$R,$U,$D,$idUsuario){

    DB::beginTransaction();

    try {

        $data = array("id_usuario" => $idUsuario,
                      "id_menu" => $menuCr,
                      "C" => $C,
                      "R" => $R,
                      "U" => $U,
                      "D" => $D);

        $update = DB::table('tbl_menu_usuario')->where([['id_usuario', '=', $idUsuario],['id_menu', '=', $menuCr]])->update($data);

        DB::commit();
        return array("response" => true, "message" => "Menu actualizado con Éxito!.");

      } catch(\Illuminate\Database\QueryException $ex){

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de actualizar la información del menu.");

      }

  }
}
