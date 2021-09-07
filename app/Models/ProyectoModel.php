<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ProyectoModel extends Model
{

    function estatusProyectos(){

      $estatus = DB::select('SELECT e.valor AS id,
                                    e.descripcion
                             FROM tbl_estatus e
                             WHERE tabla = "tbl_proyecto"
                             ORDER BY e.descripcion ASC');

      return $estatus;

    }// Fin estatusProyectos

    function empresas(){

      $sql = DB::select('SELECT e.id,
                                e.razon_social
                         FROM tbl_empresa e
                         ORDER BY e.razon_social ASC');

      return $sql;

    }// Fin empresas

    function monedas($activas){

      if($activas){
        $condicion = " WHERE m.id_estatus = 1";
      }else{
        $condicion = "";
      }

      $sql = DB::select('SELECT m.id,
                                m.moneda,
                                m.simbolo
                         FROM tbl_monedas m
                         '.$condicion.'
                         ORDER BY m.orden ASC');

      return $sql;

    }

    function divisiones(){

      $divisiones = DB::select('SELECT d.id,
                                       UPPER(d.descripcion) AS descripcion
                                FROM tbl_division d
                                WHERE d.id_estatus = 1
                                ORDER BY d.id ASC');

      return $divisiones;

    }// Fin divisiones

    function clientes($nombre_cliente = null, $limite = null){

      $sql_condicion = ($nombre_cliente != null) ? " AND UPPER(c.razon_social) LIKE UPPER('".$nombre_cliente."%')" : "";
      $sql_limit = ($limite != null) ? " LIMIT ".$limite : "";

      $clientes = DB::select('SELECT c.id,
                                     UPPER(c.razon_social) as razon_social
                              FROM tbl_cliente c
                              WHERE c.id_estatus = 1
                              '.$sql_condicion.'
                              ORDER BY c.razon_social ASC'.
                              $sql_limit);

      return $clientes;

    }// Fin clientes

    function socios($nombre_socio = null, $limite = null){

      $sql_condicion = ($nombre_socio != null) ? " WHERE UPPER(nombre) LIKE UPPER('".$nombre_socio."%')" : "";
      $sql_limit = ($limite != null) ? " LIMIT ".$limite : "";

      $socios = DB::select("SELECT *
                            FROM(

                                SELECT u.id,
                                       CONCAT(u.nombre_1,' ',u.nombre_2,' ',u.apellido_1,' ',u.apellido_2) AS nombre
                                FROM tbl_usuario u
                                WHERE u.id_estatus = 1
                                AND u.id_cargo IN(16,17)

                            )t
                            ".$sql_condicion."
                            ORDER BY nombre ASC".
                            $sql_limit);

      return $socios;

    }// Fin socios

    function gerentes($nombre_gerente = null, $limite = null){

      $sql_condicion = ($nombre_gerente != null) ? " WHERE UPPER(nombre) LIKE UPPER('".$nombre_gerente."%')" : "";
      $sql_limit = ($limite != null) ? " LIMIT ".$limite : "";

      $gerentes = DB::select("SELECT *
                              FROM(

                                SELECT u.id,
                                       CONCAT(u.nombre_1,' ',u.nombre_2,' ',u.apellido_1,' ',u.apellido_2) AS nombre
                                FROM tbl_usuario u
                                WHERE u.id_estatus = 1
                                AND u.id_cargo IN(12,13,14,15,16,17)

                            )t
                            ".$sql_condicion."
                            ORDER BY nombre ASC".
                            $sql_limit);

      return $gerentes;

    }// Fin gerentes

    function proyectoGerentesDivision($id_division){

      $gerentes = DB::select("SELECT *
                              FROM(

                                SELECT u.id,
                                       CONCAT(u.nombre_1,' ',u.nombre_2,' ',u.apellido_1,' ',u.apellido_2) AS nombre
                                FROM tbl_usuario u
                                WHERE u.id_estatus = 1
                                AND u.id_division = ".$id_division."
                                AND u.id_cargo IN(12,13,14,15,16,17)

                              )t
                              ORDER BY nombre ASC");

      return $gerentes;

    }

    function crearProyecto($descripcion,$cliente,$socio,$socioCalidad,$gerente,$fechaContratacion,$divisiones,$estatus,$id_moneda,$monto,$empresa){

      DB::beginTransaction();

      $data = array("descripcion" => $descripcion,
                    "id_cliente" => $cliente,
                    "fecha_contratacion" => $fechaContratacion,
                    "id_estatus" => $estatus,
                    "id_moneda" => $id_moneda,
                    "monto" => $monto,
                    "id_socio" => $socio,
                    "id_socio_calidad" => $socioCalidad,
                    "id_gerente" => $gerente,
                    "id_empresa" => $empresa);

      $idProyecto = DB::table('tbl_proyecto')->insertGetId($data);

      $divisionCreada = true;

      for($i = 0; $i < count($divisiones); $i++){

        $data = array(
                      "id_proyecto" => $idProyecto,
                      "id_division" => $divisiones[$i]["id"],
                      "id_gerente" => $divisiones[$i]["id_gerente"],
                      "horas_contratadas" => $divisiones[$i]["horas"]
                     );

        if(!DB::table('tbl_proyecto_divisiones')->insert($data)){
          $divisionCreada = false;
          break;
        }

      }

      if($divisionCreada){

        $client = DB::select('SELECT c.razon_social FROM tbl_cliente c WHERE c.id = '.$cliente.'');

        DB::commit();
        return array(
          "cliente" => $client[0]->razon_social,
          "response" => true,
          "message" => "Proyecto creado con éxito."
        );

      }else{

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de crear el proyecto.");

      }

    }

    function proyectos($id_division, $paginar, $desde = 0, $proyecto = "", $cliente = "", $divisiones = [], $estatus = null){

      if(trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
      }

      if($estatus != null){
        $sql_estatus = 'AND p.id_estatus = '.$estatus;
      }else{
        $sql_estatus = "";
      }

      if($cliente != null){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if(count($divisiones) > 0){

        $idsDivisiones = implode(",", $divisiones);

        $idProyectos = DB::select('SELECT id_proyecto
                                   FROM tbl_proyecto_divisiones pd
                                   WHERE pd.id_division IN('.$idsDivisiones.')');

        if (!empty($idProyectos)) {
          $arrayIds = [];
          foreach($idProyectos as $id){
            $arrayIds[] = $id->id_proyecto;
          }
        }else{
          $arrayIds = [0];
        }

        $ids = implode(",", $arrayIds);

        $sql_division = 'AND p.id IN ('.$ids.')';

      }else{
        $sql_division = "";
      }

      $proyectos = DB::select('SELECT p.id,
                                      UPPER(p.descripcion) AS descripcion,
                                      (SELECT CASE
                                       WHEN ((SELECT SUM(ph.horas) FROM tbl_proyecto_divisiones d, tbl_proy_div_horas_adic ph WHERE d.id_proyecto = p.id AND d.id  = ph.id_proy_div AND ph.id_estatus = 1) + (SELECT SUM(horas_contratadas) FROM tbl_proyecto_divisiones WHERE id_proyecto = p.id)) > 0 THEN ((SELECT SUM(ph.horas) FROM tbl_proyecto_divisiones d, tbl_proy_div_horas_adic ph WHERE d.id_proyecto = p.id AND d.id  = ph.id_proy_div AND ph.id_estatus = 1) + (SELECT SUM(horas_contratadas) FROM tbl_proyecto_divisiones WHERE id_proyecto = p.id))
                                       ELSE (SELECT SUM(horas_contratadas) FROM tbl_proyecto_divisiones WHERE id_proyecto = p.id)
                                       END AS horas_contratadas) AS horas_contratadas,
                                      DATE_FORMAT(p.fecha_contratacion, "%d/%m/%Y") AS fecha_contratacion,
                                      e.descripcion AS estatus,
                                      c.razon_social as cliente,
                                      (
                                        SELECT CONCAT(u2.nombre_1," ",u2.nombre_2," ",u2.apellido_1," ",u2.apellido_2)
                                        FROM tbl_usuario u2
                                        WHERE u2.id = p.id_socio
                                      ) AS socio,
                                      (
                                        SELECT CONCAT(u3.nombre_1," ",u3.nombre_2," ",u3.apellido_1," ",u3.apellido_2)
                                        FROM tbl_usuario u3
                                        WHERE u3.id = p.id_gerente
                                      ) AS gerente
                               FROM tbl_proyecto p,
                                    tbl_estatus e,
                                    tbl_cliente c
                               WHERE p.id_estatus = e.valor
                               AND e.tabla = "tbl_proyecto"
                               AND p.id_cliente = c.id
                               '.$sql_proyecto.'
                               '.$sql_estatus.'
                               '.$sql_cliente.'
                               '.$sql_division.'
                               ORDER BY p.id DESC
                               LIMIT '.$desde.', '.$paginar);

      foreach ($proyectos as $key => $value) {
        $proyectos[$key]->divisiones = $this->proyectoDivisiones($proyectos[$key]->id);
      }

      return $proyectos;

    }

    function proyectoDivisiones($id_proyecto){

      $divisiones = DB::select('SELECT d.id,
                                       d.descripcion
                                FROM tbl_division d
                                ORDER BY d.descripcion ASC');

      return $divisiones;

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

    function permisoCrear($id_usuario, $id_menu){

      $permiso = DB::select('SELECT CASE mu.C
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                    END AS permiso
                             FROM tbl_menu_usuario mu
                             WHERE mu.id_usuario = '.$id_usuario.'
                             AND mu.C = 1
                             AND mu.id_menu = '. $id_menu.'');

      if(count($permiso) > 0){

        return $permiso[0]->permiso;

      }else{

        return false;

      }

    }

    function permisoVer($id_usuario, $id_menu){

      $permiso = DB::select('SELECT CASE mu.R
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                    END AS permiso
                             FROM tbl_menu_usuario mu
                             WHERE mu.id_usuario = '.$id_usuario.'
                             AND mu.R = 1
                             AND mu.id_menu = '. $id_menu.'');

      if(count($permiso) > 0){

        return $permiso[0]->permiso;

      }else{

        return false;

      }

    }

    function cantidadPaginas($paginar, $proyecto = "", $cliente = "", $divisiones = [], $estatus = null){

      if(trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
      }

      if($estatus != null){
        $sql_estatus = 'AND p.id_estatus = '.$estatus;
      }else{
        $sql_estatus = "";
      }

      if($cliente != null){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if(count($divisiones) > 0){

        $idsDivisiones = implode(",", $divisiones);

        $idProyectos = DB::select('SELECT id_proyecto
                                   FROM tbl_proyecto_divisiones pd
                                   WHERE pd.id_division IN('.$idsDivisiones.')');

        if (!empty($idProyectos)) {
          $arrayIds = [];
          foreach($idProyectos as $id){
            $arrayIds[] = $id->id_proyecto;
          }
        }else{
          $arrayIds = [0];
        }

       $ids = implode(",", $arrayIds);

        $sql_division = 'AND p.id IN ('.$ids.')';

      }else{
        $sql_division = "";
      }

      $numConceptos = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                                  FROM tbl_proyecto p,
                                       tbl_cliente c
                                  WHERE p.id_cliente = c.id
                                  '.$sql_proyecto.'
                                  '.$sql_estatus.'
                                  '.$sql_cliente.'
                                  '.$sql_division);

      return $numConceptos[0]->paginas;

    }

    function detalleProyectoModificar($id_proyecto){

      $info = DB::select('SELECT p.*,
                                 (SELECT SUM(horas_contratadas) FROM tbl_proyecto_divisiones WHERE id_proyecto = p.id) AS horas_contratadas,
                                 (SELECT CASE
                                       WHEN SUM(ph.horas) > 0 THEN SUM(ph.horas)
                                       ELSE 0
                                       END AS horas_adicionales
                                       FROM tbl_proyecto_divisiones d, 
                                            tbl_proy_div_horas_adic ph
                                       WHERE d.id_proyecto = p.id 
                                       AND d.id  = ph.id_proy_div 
                                       AND ph.id_estatus = 1)horas_adicionales,
                                 m.simbolo,
                                 c.razon_social,
                                 (
                                   SELECT CONCAT(u1.nombre_1," ",u1.nombre_2," ",u1.apellido_1," ",u1.apellido_2)
                                   FROM tbl_usuario u1
                                   WHERE u1.id = p.id_socio
                                 ) nombre_socio,
                                 (
                                   SELECT CONCAT(u3.nombre_1," ",u3.nombre_2," ",u3.apellido_1," ",u3.apellido_2)
                                   FROM tbl_usuario u3
                                   WHERE u3.id = p.id_socio_calidad
                                 ) nombre_socio_calidad,
                                 (
                                   SELECT CONCAT(u2.nombre_1," ",u2.nombre_2," ",u2.apellido_1," ",u2.apellido_2)
                                   FROM tbl_usuario u2
                                   WHERE u2.id = p.id_gerente
                                 ) nombre_gerente,
                                 p.id_empresa
                          FROM tbl_proyecto p,
                               tbl_cliente c,
                               tbl_monedas m
                          WHERE p.id = '.$id_proyecto.'
                          AND p.id_cliente = c.id
                          AND p.id_moneda = m.id');

      if(count($info) > 0){

        return $info[0];

      }else{

        return array();

      }

    }

    function detalleDivisionProyecto($id_proyecto){

      $info = DB::select('SELECT d.id,
                                 pd.horas_contratadas,
                                 d.descripcion,
                                 pd.id_gerente,
                                 (
                                   SELECT CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)
                                   FROM tbl_usuario u
                                   WHERE u.id = pd.id_gerente
                                 ) nombre_gerente,
                                 pd.horas_contratadas,
                                 pd.id AS id_proy_div
                          FROM tbl_proyecto_divisiones pd,
                               tbl_division d
                          WHERE pd.id_proyecto = '.$id_proyecto.'
                          AND pd.id_division = d.id');
      if(count($info) > 0){

        return $info;

      }else{

        return array();
      }
    }

    function modificarProyecto($idProyecto, $parametros_proyecto, $divisiones, $divisiones_v){

      DB::beginTransaction();

      try{

        $sql_factura = DB::select('SELECT *
                                   FROM tbl_factura_proyecto fp
                                   WHERE fp.id_proyecto = '.$idProyecto.'
                                   AND fp.id_estatus = 1');

        if(count($sql_factura) == 0){

          $update = DB::table('tbl_proyecto')->where("id", $idProyecto)->update($parametros_proyecto);

          $divisionCreada = true;

          for($i = 0; $i < count($divisiones); $i++){

            $actualizar_division = false;

            for($j = 0; $j < count($divisiones_v); $j++){

              if ($divisiones[$i]["id"] === $divisiones_v[$j]->id) {
                $actualizar_division = true;
              }

            }

            if($actualizar_division){

              $data = array(
                            "horas_contratadas" => $divisiones[$i]["horas"],
                            "id_gerente" => $divisiones[$i]["id_gerente"]
                           );
              $div = DB::table('tbl_proyecto_divisiones')->where([['id_proyecto', '=', $idProyecto],['id_division', '=', $divisiones_v[$i]->id]])->update($data);

            }else{

              $data = array(
                            "id_proyecto" => $idProyecto,
                            "id_division" => $divisiones[$i]["id"],
                            "id_gerente" => $divisiones[$i]["id_gerente"],
                            "horas_contratadas" => $divisiones[$i]["horas"]
                           );

              if(!DB::table('tbl_proyecto_divisiones')->insert($data)){
                throw new Exception('No se pudo asociar la nueva división.');
                break;
              }

            }

          }

          for($i = 0; $i < count($divisiones_v); $i++){

            $eliminar_division = true;

            for($j = 0; $j < count($divisiones); $j++){

              if ($divisiones_v[$i]->id === $divisiones[$j]["id"]) {
                $eliminar_division = false;
              }

            }

            if($eliminar_division) {

              $delete = DB::table('tbl_proyecto_divisiones')->where([['id_proyecto', '=', $idProyecto],['id_division', '=', $divisiones_v[$i]->id]])->delete();

              if(!$delete){
                throw new Exception('No se pudo eliminar la división.');
                break;
              }

            }

          }

          DB::commit();
          $client = DB::select('SELECT c.razon_social FROM tbl_cliente c WHERE c.id = '.$parametros_proyecto["id_cliente"].'');

          return array(
            "cliente" => $client[0]->razon_social,
            "response" => true,
            "message" => "Proyecto actualizado con éxito."
          );

        }else{

          return array(
            "response" => false,
            "message" => "El proyecto ya posee un proceso de facturación activo, es decir tiene al menos una factura en estatus activa!."
          );

        }

      } catch(\Illuminate\Database\QueryException $ex){

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de actualizar la información del proyecto.");

      }

    }

  function detalleInicioUsuario($id_usuario){

    $info = DB::select('SELECT u.id,
                               u.id_division,
                               u.id_cargo
                        FROM tbl_usuario u
                        WHERE u.id = '.$id_usuario.'');

    if(count($info) > 0){
      return $info[0];
    }else{
      return array();
    }
  }

  function proyectosDivision($id_usuario, $division){

    $info = DB::select('SELECT p.id AS id_proyecto,
                               p.id_estatus,
                               (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = p.id_cliente)cliente,
                               UPPER(p.descripcion) AS proyecto,
                               (SELECT
                               CASE WHEN p.id_socio = '.$id_usuario.' THEN 1
                                    WHEN p.id_gerente = '.$id_usuario.' THEN 2
                                    WHEN d.id_gerente = '.$id_usuario.' THEN 3
                                    WHEN a.id_analista = '.$id_usuario.' THEN 4
                                    ELSE 0
                               END AS permiso)permiso,
                               (SELECT CASE
                                       WHEN a.horas_asignadas > 0 THEN a.horas_asignadas
                                       END AS horas_asignadas
                                       FROM tbl_proyecto_analista a
                                       WHERE a.id_proyecto = p.id
                                       AND a.id_analista = '.$id_usuario.')horas_asignadas,
                                (SELECT CASE
                                       WHEN a.id_estatus > 0 THEN true
                                       ELSE false
                                       END AS permisoCrear
                                       FROM tbl_proyecto_analista a
                                       WHERE a.id_proyecto = p.id
                                       AND a.id_analista = '.$id_usuario.'
                                       AND id_estatus = 1)permisoCrear,
                                (SELECT CASE
                                      WHEN p.id_socio = '.$id_usuario.' THEN true
                                      WHEN p.id_gerente = '.$id_usuario.' THEN true
                                      WHEN d.id_gerente = '.$id_usuario.' THEN true
                                      WHEN a.id_analista = '.$id_usuario.' THEN false
                                      ELSE false
                                      END AS permisoActualizar)permisoActualizar,
                                (SELECT CASE
                                      WHEN p.id_socio = '.$id_usuario.' THEN true
                                      WHEN p.id_gerente = '.$id_usuario.' THEN true
                                      WHEN d.id_gerente = '.$id_usuario.' THEN false
                                      WHEN a.id_analista = '.$id_usuario.' THEN false
                                      ELSE false
                                      END AS permisoVer)permisoVer,
                                a.id AS id_proy_analista
                        FROM tbl_proyecto p
                        LEFT JOIN tbl_proyecto_divisiones d ON d.id_gerente = '.$id_usuario.' AND p.id = d.id_proyecto AND p.id_estatus = 1
                        LEFT JOIN tbl_proyecto_analista a ON a.id_analista = '.$id_usuario.' AND p.id = a.id_proyecto
                        ORDER BY cliente ASC');

    if(count($info) > 0){
      return $info;
    }else{
      return array();
    }
  }

  function proyectoBusqueda($proyecto = "", $cliente = "", $estatus = null){

      if(trim($proyecto) != ""){
        $sql_proyecto = 'WHERE LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
        if($estatus != null){
          $sql_estatus = 'AND p.id_estatus = '.$estatus;
        }else{
          $sql_estatus = 'AND p.id_estatus = 1';
        }
        if($cliente != null){
          $sql_cliente = 'AND LOWER( (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = p.id_cliente )) LIKE "%'.strtolower($cliente).'%"';
        }else{
          $sql_cliente = "";
        }
      }else{
        $sql_proyecto = "";
        if($estatus != null){
          $sql_estatus = 'WHERE p.id_estatus = '.$estatus;
        }else{
          $sql_estatus = 'WHERE p.id_estatus = 1';
        }
        if($cliente != null){
          $sql_cliente = 'AND LOWER( (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = p.id_cliente )) LIKE "%'.strtolower($cliente).'%"';
        }else{
          $sql_cliente = "";
        }
      }
      $proyectos = DB::select('SELECT p.id AS id_proyecto,
                                      p.id_estatus
                               FROM tbl_proyecto p
                               '.$sql_proyecto.'
                               '.$sql_estatus.'
                               '.$sql_cliente.'');

    if(count($proyectos) > 0){
      return $proyectos;
    }else{
      return array();
    }
  }

  function DetalleDivProyecto($idDproyecto){

    $info = DB::select('SELECT p.id,
                               UPPER(p.descripcion) descripcion,
                               (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = p.id_cliente) cliente,
                               (SELECT SUM(d.horas_contratadas) FROM tbl_proyecto_divisiones d WHERE d.id_proyecto = '.$idDproyecto.') horas_contratadas,
                               (SELECT SUM(ph.horas) FROM tbl_proyecto_divisiones d, tbl_proy_div_horas_adic ph WHERE d.id_proyecto = '.$idDproyecto.' AND d.id  = ph.id_proy_div AND ph.id_estatus = 1) horas_adicional,
                               p.fecha_contratacion
                          FROM tbl_proyecto p
                          WHERE p.id = '.$idDproyecto.'
                        ');
    if(count($info) > 0)
    {
      return $info;
    }else{
      return array();
    }
  }

  function DetalleAnaProyecto($idDproyecto){

    $info = DB::select('SELECT a.id,
                               (SELECT CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) FROM tbl_usuario u WHERE u.id = a.id_analista) nombre,
                               (SELECT c.descripcion FROM tbl_cargo_empleado c WHERE c.id = (SELECT u.id_cargo FROM tbl_usuario u WHERE u.id = a.id_analista)) cargo,
                               (SELECT u.id_cargo FROM tbl_usuario u WHERE u.id = a.id_analista) id_cargo,
                               (SELECT d.descripcion FROM tbl_division d WHERE d.id = (SELECT u.id_division FROM tbl_usuario u WHERE u.id = a.id_analista)) division,
                               (SELECT SUM(cast(time_to_sec(h.horas_trabajadas) / (60 * 60) as decimal(10, 1))) FROM tbl_horas_cargables h WHERE h.id_proy_analista = a.id AND a.id_analista = (SELECT u.id FROM tbl_usuario u WHERE u.id = a.id_analista))horas_cargadas
                          FROM tbl_proyecto_analista a
                          WHERE a.id_proyecto = '.$idDproyecto.'
                          AND a.id_estatus = 1
                          ORDER BY id_cargo DESC');
    if(count($info) > 0)
    {
      return $info;
    }else{
      return array();
    }
  }

    function datosProyecto($id_proyecto,$id_division){

      $info = DB::select('SELECT p.id,
                                 UPPER(p.descripcion) AS proyecto,
                                 (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = p.id_cliente) cliente,
                                 (SELECT d.id FROM tbl_proyecto_divisiones d WHERE d.id_proyecto = '.$id_proyecto.' AND d.id_division = '.$id_division.' )id_proyecto_division,
                                 (SELECT SUM(d.horas_contratadas) FROM tbl_proyecto_divisiones d WHERE d.id_proyecto = '.$id_proyecto.' AND d.id_division = '.$id_division.')horas_contratadas,
                                 (SELECT SUM(ph.horas) FROM tbl_proyecto_divisiones d, tbl_proy_div_horas_adic ph WHERE d.id_proyecto = '.$id_proyecto.' AND d.id_division = '.$id_division.' AND d.id  = ph.id_proy_div AND ph.id_estatus = 1)horas_adicionales
                          FROM tbl_proyecto p
                          WHERE p.id = '.$id_proyecto.'');

      if(count($info) > 0){

        return $info;

      }else{

        return array();

      }

    }

    function analistasProyecto($id_usuario,$id_menu,$id_proyecto,$id_division){

      $info = DB::select('SELECT u.id,
                                 CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)AS nombre,
                                 (SELECT c.descripcion FROM tbl_cargo_empleado c WHERE c.id = u.id_cargo) cargo,
                                 (SELECT SUM(cast(time_to_sec(h.horas_trabajadas) / (60 * 60) as decimal(10, 1))) FROM tbl_horas_cargables h WHERE id_proy_analista = (SELECT a.id FROM tbl_proyecto_analista a WHERE a.id_proyecto = '.$id_proyecto.' AND a.id_analista = u.id))suma,

                                 (SELECT a.id_estatus FROM tbl_proyecto_analista a WHERE a.id_proyecto = '.$id_proyecto.' AND a.id_analista = u.id) estatus,
                                 (SELECT a.horas_asignadas FROM tbl_proyecto_analista a WHERE a.id_proyecto = '.$id_proyecto.' AND a.id_analista = u.id) horas_asignadas,
                                 (SELECT a.id FROM tbl_proyecto_analista a WHERE a.id_proyecto = '.$id_proyecto.' AND a.id_analista = u.id) idAnaProy,
                                 (SELECT CASE mu.C
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                      END AS permiso
                                      FROM tbl_menu_usuario mu
                                      WHERE mu.id_usuario = '.$id_usuario.'
                                      AND mu.C = 1
                                      AND mu.id_menu = '. $id_menu.'
                                      AND (SELECT a.id_estatus FROM tbl_proyecto_analista a WHERE a.id_proyecto = '.$id_proyecto.' AND a.id_analista = u.id) = 1)permisoCrear

                          FROM tbl_usuario u
                          WHERE u.id_division = '.$id_division.'
                          AND u.id_estatus = 1
                          ORDER BY u.id_cargo DESC');

      if(count($info) > 0){

        return $info;

      }else{

        return array();

      }

    }

    function empleadosProyecto($id_proyecto,$id_division){

      $info = DB::select('SELECT u.id,
                                 u.id_estatus,
                                 CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)AS nombre,
                                 c.descripcion AS cargo,
                                 (SELECT CASE 
                                  WHEN SUM(cast(time_to_sec(hc.horas_trabajadas) / (60 * 60) as decimal(10, 1))) > 0 THEN SUM(cast(time_to_sec(hc.horas_trabajadas) / (60 * 60) as decimal(10, 1)))
                                  ELSE 0
                                  END AS horas_cargadas
                                  FROM tbl_horas_cargables hc,
                                       tbl_proyecto_analista pa 
                                  WHERE hc.id_proy_analista = pa.id
                                  AND pa.id_proyecto = '.$id_proyecto.'
                                  AND pa.id_analista = u.id) horas_cargadas,
                                  (SELECT CASE 
                                  WHEN pa.id  > 0 THEN "true"
                                  ELSE "false"
                                  END AS permisoCarga
                                  FROM tbl_proyecto_analista pa 
                                  WHERE pa.id_proyecto = '.$id_proyecto.'
                                  AND pa.id_analista = u.id) permisoCarga,

                                  (SELECT CASE 
                                  WHEN pa.id  > 0 THEN pa.id
                                  ELSE 0
                                  END AS idAnaProy
                                  FROM tbl_proyecto_analista pa 
                                  WHERE pa.id_proyecto = '.$id_proyecto.'
                                  AND pa.id_analista = u.id) idAnaProy,

                                  (SELECT a.horas_asignadas FROM tbl_proyecto_analista a WHERE a.id_proyecto = '.$id_proyecto.' AND a.id_analista = u.id) horas_asignadas,
                                  (SELECT a.id_estatus FROM tbl_proyecto_analista a WHERE a.id_proyecto = '.$id_proyecto.' AND a.id_analista = u.id) id_estatus
                          FROM tbl_usuario u,
                               tbl_cargo_empleado c
                          WHERE u.id_division = '.$id_division.'
                          AND c.id = u.id_cargo
                          AND (u.id_estatus = 1 OR (SELECT SUM(a.horas_asignadas) FROM tbl_proyecto_analista a WHERE a.id_proyecto = '.$id_proyecto.' AND a.id_analista = u.id) > 0)
                          ORDER BY u.id_cargo DESC, nombre DESC');

      if(count($info) > 0){
        return $info;
      }else{
        return array();
      }
    }

    function asigHorasAnalistaProy($id_proyecto_division, $id_proyecto, $empleados){

      DB::beginTransaction();

      $horasAnalista = DB::select('SELECT pa.id,
                                          pa.id_proyecto,
                                          pa.id_proyecto_division,
                                          pa.id_analista,
                                          pa.horas_asignadas,
                                          pa.id_estatus,
                                          (SELECT SUM(cast(time_to_sec(hc.horas_trabajadas) / (60 * 60) as decimal(10, 1))) AS horas_cargadas
                                            FROM tbl_horas_cargables hc 
                                            WHERE hc.id_proy_analista = pa.id) horas_cargadas
                                   FROM tbl_proyecto_analista pa
                                   WHERE pa.id_proyecto_division = '.$id_proyecto_division.'
                                   AND pa.id_proyecto = '.$id_proyecto.'');
      for ($i=0; $i < count($horasAnalista); $i++) { 
        $analista = 0;
        foreach ($empleados as $key => $item) {
          if(!isset($item->id)){
            $item = json_decode($item);
          }
          $id_usuario = $item->id;
          $horas_cargadas = $item->horas_cargadas;
          $horas = $item->horas;    
          if ($horasAnalista[$i]->id_analista === $id_usuario) {
            $analista = 1;
            if ($horas_cargadas > $horas) {
              $response = array(
               "response" => false,
               "message" => "Las horas asignadas no pueden ser inferior a las horas cargadas por los empleados."
              );
              return $response;
            }
          }
        }
        if ($analista == 0) {
          if ($horasAnalista[$i]->horas_cargadas != null) {
            $response = array(
             "response" => false,
             "message" => "Un analista ya posee horas cargadas, no puede ser eliminado."
            );
            return $response;
          }            
        }
      }



      foreach ($empleados as $key => $item) {
        if(!isset($item->id)){
          $item = json_decode($item);
        }
        $id_usuario = $item->id;
        $horas = $item->horas;
        $idAnaProy = $item->idAnaProy;
        $agregar = 1;
        for ($j=0; $j < count($horasAnalista); $j++) { 
          if ($horasAnalista[$j]->id_analista === $id_usuario) {
            $agregar = 0;
            if ($horasAnalista[$j]->horas_asignadas != $horas) {
              $data = array("horas_asignadas" => $horas,
                            "id_estatus" => 1);
              $update = DB::table('tbl_proyecto_analista')->where("id",$horasAnalista[$j]->id)->update($data);
            }else{
              $data = array("id_estatus" => 1);
              $update = DB::table('tbl_proyecto_analista')->where("id",$horasAnalista[$j]->id)->update($data);
            }
          }
        }
        if ($agregar == 1) {
          $data = array(
                      "id_proyecto" => $id_proyecto,
                      "id_proyecto_division" => $id_proyecto_division,
                      "id_analista" => $id_usuario,
                      "horas_asignadas" => $horas,
                      "id_estatus" => 1
                     );
          $analistaAgregado = DB::table('tbl_proyecto_analista')->insertGetId($data);
        }
      }  

      for ($i=0; $i < count($horasAnalista); $i++) { 
        $analista = 0;
        foreach ($empleados as $key => $item) {
          if(!isset($item->id)){
            $item = json_decode($item);
          }
          $id_usuario = $item->id;    
          if ($horasAnalista[$i]->id_analista === $id_usuario) {
            $analista = 1;
          }
        }
        if ($analista == 0) {
          $data = array("id_estatus" => 0);
          $update = DB::table('tbl_proyecto_analista')->where("id",$horasAnalista[$i]->id)->update($data);
        }
      }

      try{

        DB::commit();
        return array(
          "response" => true,
          "message" => "Analista actualizado con éxito."
        );

      } catch(\Illuminate\Database\QueryException $ex){

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de actualizar la información del proyecto.");

      }

    }

    function agregarMontoAdicionalProy($parametros){

      if(DB::table('tbl_proy_monto_adicional')->insert($parametros)){
        return true;
      }else{
        return false;
      }

    }

    function montosAdicionesProy($id_proyecto){

      $montos = DB::select('SELECT m.id,
                                   m.monto,
                                   FORMAT(m.monto,2,"de_DE") AS monto_formatted,
                                   DATE_FORMAT(m.fecha, "%d/%m/%Y") AS fecha
                            FROM tbl_proy_monto_adicional m
                            WHERE m.id_proyecto = ?
                            AND m.id_estatus = 1
                            ORDER BY m.id DESC', [$id_proyecto]);

      return $montos;

    }

    function eliminarMontosAdicionesProy($id_monto){

      if(DB::table('tbl_proy_monto_adicional')->where("id", $id_monto)->update(["id_estatus" => 2])){
        return true;
      }else{
        return false;
      }

    }

    function horasAdicionesProyDiv($id_proy_div){

      $horas = DB::select('SELECT h.id,
                                  h.horas,
                                  DATE_FORMAT(h.fecha, "%d/%m/%Y") AS fecha
                            FROM tbl_proy_div_horas_adic h
                            WHERE h.id_proy_div = ?
                            AND h.id_estatus = 1
                            ORDER BY h.id DESC', [$id_proy_div]);

      return $horas;

    }

    function agregarHoraAdicionalProyDiv($parametros){

      if(DB::table('tbl_proy_div_horas_adic')->insert($parametros)){
        return true;
      }else{
        return false;
      }

    }

    function eliminarHoraAdicionalProyDiv($id){

      if(DB::table('tbl_proy_div_horas_adic')->where("id", $id)->update(["id_estatus" => 2])){
        return true;
      }else{
        return false;
      }

    }

}
