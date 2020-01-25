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
                    "cedula" => $parametros["cedula"]);

      $idUsuario = DB::table('tbl_usuario')->insertGetId($data);

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

    }// Fin crearUsuario

    function buscarUsuarios($opcionBusqueda, $dato){

      switch ((int) $opcionBusqueda) {
        case 1:
          $condicion = "WHERE u.codigo LIKE '%".$dato."%'";
          break;
        case 2:
            $condicion = "WHERE u.cedula LIKE '%".$dato."%'";
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
          $condicion = "WHERE u.codigo LIKE %'".$dato."'%";
          break;
      }

      $usuarios = DB::select('SELECT u.id,
                                     u.codigo,
                                     u.avatar,
                                     u.cedula,
                                     CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                                     e.descripcion AS estatus,
                                     cu.correo_principal
                             FROM tbl_usuario u,
                                  tbl_estatus e,
                                  tbl_contacto_usuario cu
                             '.$condicion.'
                             AND e.tabla = "tbl_usuario"
                             AND e.valor = u.id_estatus
                             AND u.id = cu.id_usuario');

      if(count($usuarios) > 0){

        return $usuarios;

      }else{

        return array();

      }

    }

    function detalleUsuario($id_usuario){

      $info = DB::select('SELECT u.id,
                                 u.codigo,
                                 u.avatar,
                                 u.cedula,
                                 u.nombre_1,
                                 u.nombre_2,
                                 u.apellido_1,
                                 u.apellido_2,
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
                               tbl_contacto_usuario cu
                          WHERE u.id = '.$id_usuario.'
                          AND e.tabla = "tbl_usuario"
                          AND e.valor = u.id_estatus
                          AND u.id = cu.id_usuario');

      if(count($info) > 0){

        return $info[0];

      }else{

        return array();

      }

    }

    function detalleUsuarioModificar($id_usuario){

      $info = DB::select('SELECT u.id,
                                 u.codigo,
                                 u.avatar,
                                 u.cedula,
                                 u.nombre_1,
                                 u.nombre_2,
                                 u.apellido_1,
                                 u.apellido_2,
                                 u.fecha_nacimiento,
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
                               tbl_contacto_usuario cu
                          WHERE u.id = '.$id_usuario.'
                          AND e.tabla = "tbl_usuario"
                          AND e.valor = u.id_estatus
                          AND u.id = cu.id_usuario');

      if(count($info) > 0){

        return $info[0];

      }else{

        return array();

      }

    }

    function estatusUsuario(){

      $estatus = DB::select('SELECT e.valor as id,
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
                      "cedula" => $parametros["cedula"]);

        $update = DB::table('tbl_usuario')->where("id",$parametros["idUsuario"])->update($data);



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

    }// Fin crearUsuario

}
