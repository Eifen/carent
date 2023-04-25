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
                    "clave" => DB::raw('AES_ENCRYPT("'.$parametros["clave"].'", "'.$parametros["keysecret"].'")'),
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

    function searchUsers($params){

        switch ((int) $params["searchBy"]) {
            case 0:
                $condicion = "";
                break;
            case 1:
                $condicion = "AND u.codigo LIKE '%".$params["data"]."%'";
                break;
            case 2:
                $condicion = "AND udi.documento LIKE '%".$params["data"]."%'";
                break;
            case 3:
                $condicion = "AND cu.correo_principal LIKE '%".$params["data"]."%'";
                break;
            case 4:
                $condicion = "AND (
                                         u.nombre_1 LIKE '%".$params["data"]."%'
                                      OR u.nombre_2 LIKE '%".$params["data"]."%'
                                      OR u.apellido_1 LIKE '%".$params["data"]."%'
                                      OR u.apellido_2 LIKE '%".$params["data"]."%'
                              )";
                break;
            default:
                $condicion = "";
                break;
        }

        $users = DB::select('SELECT u.id,
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
                             WHERE e.tabla = "tbl_usuario"
                             AND e.valor = u.id_estatus
                             AND u.id = cu.id_usuario
                             AND u.id = udi.id_usuario
                             AND udi.id_tipo_documento_identidad = tdi.id
                             '.$condicion.'
                             LIMIT '.$params["searchFrom"].', '.$params["paginate"]);

        $pages = DB::select('SELECT CEILING( COUNT(1) / '.$params["paginate"].') AS pages
                             FROM tbl_usuario u,
                                  tbl_estatus e,
                                  tbl_contacto_usuario cu,
                                  tbl_usuario_documento_identidad udi,
                                  tbl_tipo_documento_identidad tdi
                             WHERE e.tabla = "tbl_usuario"
                             AND e.valor = u.id_estatus
                             AND u.id = cu.id_usuario
                             AND u.id = udi.id_usuario
                             AND udi.id_tipo_documento_identidad = tdi.id
                             '.$condicion);

        return [
            "pages" => $pages[0]->pages,
            "users" => $users
        ];
        /*if(count($usuarios) > 0) {
            return $usuarios;
        } else {
            return array();
        }*/

    }

    function buscarUsuarios($buscarPor,$dato){
        $condicion = "";
        $retorno = array();

        switch ((int) $buscarPor) {
            case 0:
                $condicion = "";
                break;
            case 1:
                $condicion = "AND u.codigo LIKE '%".$dato."%'";
                break;
            case 2:
                $condicion = "AND udi.documento LIKE '%".$dato."%'";
                break;
            case 3:
                $condicion = "AND cu.correo_principal LIKE '%".$dato."%'";
                break;
            case 4:
                $nombreFraccion = explode(" ",$dato); //[0] = nombre_1 [1] = nombre_ 2 [2] = apellido_1 [3] apellido_2
                $condicion = "AND (CONCAT(u.nombre_1,u.nombre_2,u.apellido_1,u.apellido_2) 
                              LIKE '%".(isset($nombreFraccion[0]) ? $nombreFraccion[0] : "").
                              "%".(isset($nombreFraccion[1]) ? $nombreFraccion[1] : "").
                              "%".(isset($nombreFraccion[2]) ? $nombreFraccion[2] : "").
                              "%".(isset($nombreFraccion[3]) ? $nombreFraccion[3] : "")."%')";
                break;
            default:
                $condicion = "";
                break;
        }

        $usuarios = DB::select('SELECT u.id,
                                    u.codigo as Codigo,
                                    CONCAT(tdi.abreviatura,"-",udi.documento) AS Cedula,
                                    CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS Nombre,
                                    cu.correo_principal AS Correo,
                                    e.descripcion AS Estatus
                                    FROM tbl_usuario u,
                                    tbl_estatus e,
                                    tbl_contacto_usuario cu,
                                    tbl_usuario_documento_identidad udi,
                                    tbl_tipo_documento_identidad tdi
                                    WHERE e.tabla = "tbl_usuario"
                                    AND e.valor = u.id_estatus
                                    AND u.id = cu.id_usuario
                                    AND u.id = udi.id_usuario
                                    AND udi.id_tipo_documento_identidad = tdi.id '.$condicion);
          if(count($usuarios) > 0){
            $retorno = $usuarios;
          }else{
            $retorno = array();
          }

          return $retorno;
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

      $menus = DB::select('SELECT m.id,
                                  m.id_menu_padre,
                                  m.descripcion,
                                  m.url,
                                  m.C,
                                  m.R,
                                  m.U,
                                  m.D,
                                  (SELECT CASE
                                      WHEN mu.id_menu = m.id THEN "true"
                                      ELSE "false"
                                    END AS permiso
                                  FROM tbl_menu_usuario mu
                                  WHERE mu.id_usuario = '.$id_usuario.'
                                  AND mu.id_menu = m.id)permiso
                           FROM tbl_menu m
                           WHERE m.id_estatus = 1
                           ORDER BY m.id_menu_padre ASC');

      if(count($menus) > 0){

        $array_menus = $this->arbolMenu($menus,$id_usuario);

        return $array_menus;

      }else{

        return array();

      }
    }

    private function arbolMenu($menus,$id_usuario){

      $arbolMenu = [];

      foreach($menus as $menu){

        $ramaTmp = $this->ramas($menu,$id_usuario);
        $rama[$ramaTmp->id] = $ramaTmp;
        $arbolMenu = $this->unirRamas($arbolMenu, $rama);

      }// Fin foreach

      return $arbolMenu;

    }

    private function ramas($menu,$id_usuario){

      $sql = DB::select('SELECT m.id,
                                m.id_menu_padre,
                                m.descripcion,
                                m.url,
                                m.C,
                                m.R,
                                m.U,
                                m.D,
                                (SELECT CASE
                                      WHEN mu.id_menu = m.id THEN "true"
                                      ELSE "false"
                                    END AS permiso
                                  FROM tbl_menu_usuario mu
                                  WHERE mu.id_usuario = '.$id_usuario.'
                                  AND mu.id_menu = m.id)permiso
                         FROM tbl_menu m
                         WHERE m.id = "'.$menu->id_menu_padre.'"
                         AND m.id_estatus = 1');

      if(empty($sql)){

        $menu->submenu = [];
        return $menu;

      }else{

        if(!property_exists($menu, "submenu")){
          $menu->submenu = [];
        }

        if($sql[0]->id_menu_padre === 0){

          $sql[0]->submenu[$menu->id] = $menu;
          return $sql[0];

        }else{

          $sql[0]->submenu[$menu->id] = $menu;

          $ramaMenu = $this->ramas($sql[0]);
          return $ramaMenu;

        }

      }

    }

    private function unirRamas($arbolMenu, $rama){

      foreach($rama as $indice => $hoja){

        if(!array_key_exists($indice, $arbolMenu)){

          $arbolMenu[$indice] = $hoja;
          break;

        }else{

          $p = $this->unirRamas($arbolMenu[$indice]->submenu,$hoja->submenu);
          $arbolMenu[$indice]->submenu = $p;

        }

      }

      return $arbolMenu;

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

    function menuUsuario($id_usuario,$id_menu,$C,$R,$U,$D){

      $menu = DB::select('SELECT mu.id_usuario,
                                 mu.id_menu
                             FROM tbl_menu_usuario mu
                             WHERE mu.id_usuario = '.$id_usuario.'
                             AND mu.id_menu = '. $id_menu);

      if(count($menu) > 0){

        DB::beginTransaction();

        $contacto = DB::table('tbl_menu_usuario')->where([['id_usuario', '=', $id_usuario],['id_menu', '=', $id_menu]])->delete();
        if($contacto){
          DB::commit();
          return array("response" => true, "message" => "menu eliminado con exito.");
        }else{
          DB::rollBack();
          return array("response" => false, "message" => "Error al tratar de eliminar el menu.");
        }

      }else{

        DB::beginTransaction();

        $data = array("id_usuario" => $id_usuario,
                      "id_menu" => $id_menu,
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
