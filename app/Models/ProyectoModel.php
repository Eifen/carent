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

    function divisiones(){

      $divisiones = DB::select('SELECT d.id,
                                       d.descripcion
                                FROM tbl_division d
                                WHERE d.id_estatus = 1
                                ORDER BY d.descripcion ASC');

      return $divisiones;

    }// Fin divisiones

    function clientes(){

      $clientes = DB::select('SELECT c.id,
                                     c.razon_social
                              FROM tbl_cliente c
                              WHERE c.id_estatus = 1
                              ORDER BY c.razon_social ASC');

      return $clientes;

    }// Fin clientes

    function crearProyecto($descripcion,$cliente,$horas,$fechaContratacion,$divisiones,$estatus){

      DB::beginTransaction();

      $data = array("descripcion" => $descripcion,
                    "id_cliente" => $cliente,
                    "horas_contratadas" => $horas,
                    "fecha_contratacion" => $fechaContratacion,
                    "id_estatus" => $estatus);

      $idProyecto = DB::table('tbl_proyecto')->insertGetId($data);

      $divisionCreada = true;

      for($i = 0; $i < count($divisiones); $i++){

        $data = array("id_proyecto" => $idProyecto,
                      "id_division" => $divisiones[$i]);

        if(!DB::table('tbl_proyecto_divisiones')->insert($data)){
          $divisionCreada = false;
          break;
        }

      }

      if($divisionCreada){

        DB::commit();
        return array("response" => true, "message" => "Proyecto creado con éxito.");

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

        $idProyectos = DB::select('SELECT id
                                   FROM tbl_proyecto_divisiones pd
                                   WHERE pd.id_division IN('.$idsDivisiones.')');

        $arrayIds = [];
        foreach($idProyectos as $id){
          $arrayIds[] = $id->id;
        }

        $ids = implode(",", $arrayIds);

        $sql_division = 'AND p.id IN ('.$ids.')';

      }else{
        $sql_division = "";
      }

      $proyectos = DB::select('SELECT p.id,
                                      p.descripcion,
                                      p.horas_contratadas,
                                      p.fecha_contratacion,
                                      e.descripcion AS estatus,
                                      c.razon_social as cliente
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

        $idProyectos = DB::select('SELECT id
                                   FROM tbl_proyecto_divisiones pd
                                   WHERE pd.id_division IN('.$idsDivisiones.')');

        $arrayIds = [];
        foreach($idProyectos as $id){
          $arrayIds[] = $id->id;
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

      $info = DB::select('SELECT *
                          FROM tbl_proyecto                              
                          WHERE id = '.$id_proyecto.'');

      if(count($info) > 0){

        return $info[0];

      }else{

        return array();

      }

    }

    function detalleDivisionProyecto($id_proyecto){

      $info = DB::select('SELECT id_division
                          FROM tbl_proyecto_divisiones                              
                          WHERE id_proyecto = '.$id_proyecto.'');
      if(count($info) > 0){

        return $info;

      }else{

        return array();
      }
    }

    function modificarProyecto($descripcion,$cliente,$horas,$fechaContratacion,$divisiones,$estatus,$idProyecto,$divisiones_v){

      DB::beginTransaction();

      try{

      $data = array("descripcion" => $descripcion,
                    "id_cliente" => $cliente,
                    "horas_contratadas" => $horas,
                    "fecha_contratacion" => $fechaContratacion,
                    "id_estatus" => $estatus);

      $update = DB::table('tbl_proyecto')->where("id",$idProyecto)->update($data);

      $divisionCreada = true;

      for($i = 0; $i < count($divisiones); $i++){
        $si = 0;
        for($j = 0; $j < count($divisiones_v); $j++){
          if ($divisiones[$i] === $divisiones_v[$j]->id_division) {
            $si=1;
          }
        }
        if ($si === 0) {
          
          $data = array("id_proyecto" => $idProyecto,
                      "id_division" => $divisiones[$i]);
          $div = DB::table('tbl_proyecto_divisiones')->insert($data);
           $divisionCreada = false;
          }
        
      }

      for($i = 0; $i < count($divisiones_v); $i++){
        $no = 0;
        for($j = 0; $j < count($divisiones); $j++){
          if ($divisiones_v[$i]->id_division === $divisiones[$j]) {
            $no=1;
          }
        }
        if ($no === 0) {
          $delete = DB::table('tbl_proyecto_divisiones')->where([['id_proyecto', '=', $idProyecto],['id_division', '=', $divisiones_v[$i]->id_division]])->delete();
          }
        
      }
        DB::commit();
        return array("response" => true, "message" => "Proyecto actualizado con éxito.");

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

  function permisoVer($id_usuario, $id_menu){

      $permiso = DB::select('SELECT CASE mu.R
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                    END AS permiso
                             FROM tbl_menu_usuario mu
                             WHERE mu.id_usuario = '.$id_usuario.'
                             AND mu.R = 1
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

  function proyectoSDivision($id_usuario, $id_menu){

    $info = DB::select('SELECT p.id AS id_proyecto,
                               p.fecha_contratacion AS fecha,
                               p.descripcion AS proyecto,
                               (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = p.id_cliente) cliente,
                               (SELECT e.descripcion FROM tbl_estatus e WHERE valor = p.id_estatus AND e.tabla = "tbl_proyecto") estatus,
                               (SELECT a.id FROM tbl_proyecto_analista a WHERE a.id_analista = '.$id_usuario.' AND a.id_proyecto = p.id AND a.id_estatus = 1)id_proy_analista,
                               (SELECT CASE mu.C
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                    END AS permiso
                                    FROM tbl_menu_usuario mu
                                    WHERE mu.id_usuario = '.$id_usuario.'
                                    AND mu.C = 1
                                    AND mu.id_menu = '. $id_menu.'
                                    AND '.$id_usuario.' = (SELECT a.id_analista FROM tbl_proyecto_analista a WHERE a.id_analista = '.$id_usuario.' AND a.id_proyecto = p.id AND a.id_estatus = 1))permisoCrear
                        FROM tbl_proyecto p
                        WHERE p.id_estatus = 1
                        ORDER BY fecha ASC');
    if(count($info) > 0){
      return $info;
    }else{
      return array();
    }
  }

  function proyectoDDivision($id_division,$id_usuario, $id_menu){

    $info = DB::select('SELECT p.id,
                               p.id_division,
                               p.id_proyecto,
                               (SELECT d.fecha_contratacion FROM tbl_proyecto d WHERE d.id = p.id_proyecto) fecha,
                               (SELECT d.descripcion FROM tbl_proyecto d WHERE d.id = p.id_proyecto) proyecto,
                               (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = (SELECT id_cliente FROM tbl_proyecto  WHERE p.id_proyecto = id)) cliente,
                               (SELECT e.descripcion FROM tbl_estatus e WHERE valor = (SELECT id_estatus FROM tbl_proyecto  WHERE p.id_proyecto = id) AND e.tabla = "tbl_proyecto") estatus,
                               (SELECT CASE mu.C
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                    END AS permiso
                                    FROM tbl_menu_usuario mu
                                    WHERE mu.id_usuario = '.$id_usuario.'
                                    AND mu.C = 1
                                    AND mu.id_menu = '. $id_menu.'
                                    AND '.$id_usuario.' = (SELECT a.id_analista FROM tbl_proyecto_analista a WHERE p.id = a.id_proyecto_division AND a.id_estatus = 1 AND a.id_analista = '.$id_usuario.'))permisoCrear,
                                    (SELECT a.id FROM tbl_proyecto_analista a WHERE p.id = a.id_proyecto_division AND a.id_analista = '.$id_usuario.')id_proy_analista                                
                        FROM tbl_proyecto_divisiones p
                        WHERE p.id_division = '.$id_division.'
                        AND (SELECT id_estatus FROM tbl_proyecto  WHERE p.id_proyecto = id) = 1
                        ORDER BY fecha ASC');
    if(count($info) > 0){
      return $info;
    }else{
      return array();
    }
  }

  function proyectoUDivision($id_usuario, $id_menu){

    $info = DB::select('SELECT  a.id AS id_proy_analista,
                                (SELECT p.descripcion FROM tbl_proyecto p WHERE p.id = a.id_proyecto)proyecto,
                                (SELECT d.fecha_contratacion FROM tbl_proyecto d WHERE d.id = a.id_proyecto) fecha,
                                (SELECT e.descripcion FROM tbl_estatus e WHERE valor = (SELECT id_estatus FROM tbl_proyecto   WHERE id = a.id_proyecto) AND e.tabla = "tbl_proyecto") estatus,
                                (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = (SELECT p.id_cliente FROM tbl_proyecto p WHERE p.id = a.id_proyecto AND a.id_analista = '.$id_usuario.'))cliente,
                                (SELECT CASE mu.C
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                    END AS permiso
                                    FROM tbl_menu_usuario mu
                                    WHERE mu.id_usuario = '.$id_usuario.'
                                    AND mu.C = 1
                                    AND mu.id_menu = '. $id_menu.')permisoCrear
                        FROM tbl_proyecto_analista a
                        WHERE a.id_analista = '.$id_usuario.'
                        AND (SELECT p.id_estatus FROM tbl_proyecto p WHERE p.id = a.id_proyecto) = 1
                        AND a.id_estatus = 1
                        ORDER BY fecha ASC');
    if(count($info) > 0){
      return $info;
    }else{
      return array();
    }
  }

  function proyectosSdivi($id_usuario,$id_menu,$proyecto = "", $cliente = "", $estatus = null){

      if(trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
      }

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

      $proyectos = DB::select('SELECT p.id AS id_proyecto,
                                      p.fecha_contratacion AS fecha,
                                      p.descripcion AS proyecto,
                                      p.id_estatus,
                                      (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = p.id_cliente) cliente,
                                      (SELECT e.descripcion FROM tbl_estatus e WHERE valor = p.id_estatus AND e.tabla = "tbl_proyecto") estatus,
                                      (SELECT a.id FROM tbl_proyecto_analista a WHERE a.id_analista = '.$id_usuario.' AND a.id_proyecto = p.id AND a.id_estatus = 1)id_proy_analista,
                                     (SELECT CASE mu.C
                                            WHEN 1 THEN "true"
                                            ELSE "false"
                                          END AS permiso
                                          FROM tbl_menu_usuario mu
                                          WHERE mu.id_usuario = '.$id_usuario.'
                                          AND mu.C = 1
                                          AND mu.id_menu = '. $id_menu.'
                                          AND '.$id_usuario.' = (SELECT a.id_analista FROM tbl_proyecto_analista a WHERE a.id_analista = '.$id_usuario.' AND a.id_proyecto = p.id AND a.id_estatus = 1))permisoCrear
                               FROM tbl_proyecto p
                               WHERE p.id_estatus > 0
                               '.$sql_proyecto.'
                               '.$sql_estatus.'
                               '.$sql_cliente.'
                               ORDER BY fecha ASC');

      if(count($proyectos) > 0){
      return $proyectos;
    }else{
      return array();
    }

    }

  function proyectosDdivi($id_division,$id_usuario,$id_menu,$proyecto = "", $cliente = "", $estatus = null){

      if(trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER((SELECT d.descripcion FROM tbl_proyecto d WHERE d.id = p.id_proyecto)) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
      }

      if($estatus != null){
        $sql_estatus = 'AND (SELECT id_estatus FROM tbl_proyecto  WHERE p.id_proyecto = id) = '.$estatus;
      }else{
        $sql_estatus = 'AND (SELECT id_estatus FROM tbl_proyecto  WHERE p.id_proyecto = id) = 1';
      }

      if($cliente != null){
        $sql_cliente = 'AND LOWER( (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = (SELECT id_cliente FROM tbl_proyecto  WHERE p.id_proyecto = id))) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      $proyectos = DB::select('SELECT p.id,
                                      p.id_division,
                                      p.id_proyecto,
                                      (SELECT d.fecha_contratacion FROM tbl_proyecto d WHERE d.id = p.id_proyecto) fecha,
                                      (SELECT d.descripcion FROM tbl_proyecto d WHERE d.id = p.id_proyecto) proyecto,
                                      (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = (SELECT id_cliente FROM tbl_proyecto  WHERE p.id_proyecto = id)) cliente,
                                      (SELECT d.id_estatus FROM tbl_proyecto d WHERE d.id = p.id_proyecto) id_estatus,
                                      (SELECT e.descripcion FROM tbl_estatus e WHERE valor = (SELECT id_estatus FROM tbl_proyecto  WHERE p.id_proyecto = id) AND e.tabla = "tbl_proyecto") estatus,
                                      (SELECT CASE mu.C
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                      END AS permiso
                                      FROM tbl_menu_usuario mu
                                      WHERE mu.id_usuario = '.$id_usuario.'
                                      AND mu.C = 1
                                      AND mu.id_menu = '. $id_menu.'
                                      AND '.$id_usuario.' = (SELECT a.id_analista FROM tbl_proyecto_analista a WHERE p.id = a.id_proyecto_division AND a.id_estatus = 1 AND a.id_analista = '.$id_usuario.' ))permisoCrear,
                                      (SELECT a.id FROM tbl_proyecto_analista a WHERE p.id = a.id_proyecto_division AND a.id_analista = '.$id_usuario.')id_proy_analista
                               FROM tbl_proyecto_divisiones p
                               WHERE p.id_division = '.$id_division.'
                               '.$sql_proyecto.'
                               '.$sql_estatus.'
                               '.$sql_cliente.'
                               ORDER BY fecha ASC');

      if(count($proyectos) > 0){
      return $proyectos;
    }else{
      return array();
    }

    }

    function proyectosUdivi($id_usuario,$id_menu,$proyecto = "", $cliente = "", $estatus = null){

      if(trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER((SELECT p.descripcion FROM tbl_proyecto p WHERE p.id = a.id_proyecto)) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
      }

      if($estatus != null){
        $sql_estatus = 'AND (SELECT p.id_estatus FROM tbl_proyecto p WHERE p.id = a.id_proyecto) = '.$estatus;
      }else{
        $sql_estatus = 'AND (SELECT p.id_estatus FROM tbl_proyecto p WHERE p.id = a.id_proyecto) = 1';
      }

      if($cliente != null){
        $sql_cliente = 'AND LOWER( (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = (SELECT p.id_cliente FROM tbl_proyecto p WHERE p.id = a.id_proyecto AND a.id_analista = '.$id_usuario.'))) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      $proyectos = DB::select('SELECT  a.id AS id_proy_analista,
                                (SELECT p.descripcion FROM tbl_proyecto p WHERE p.id = a.id_proyecto)proyecto,
                                (SELECT d.fecha_contratacion FROM tbl_proyecto d WHERE d.id = a.id_proyecto) fecha,
                                (SELECT e.descripcion FROM tbl_estatus e WHERE valor = (SELECT id_estatus FROM tbl_proyecto   WHERE id = a.id_proyecto) AND e.tabla = "tbl_proyecto") estatus,
                                (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = (SELECT p.id_cliente FROM tbl_proyecto p WHERE p.id = a.id_proyecto AND a.id_analista = '.$id_usuario.'))cliente,
                                (SELECT CASE mu.C
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                    END AS permiso
                                    FROM tbl_menu_usuario mu
                                    WHERE mu.id_usuario = '.$id_usuario.'
                                    AND mu.C = 1
                                    AND mu.id_menu = '. $id_menu.')permisoCrear
                               FROM tbl_proyecto_analista a
                               WHERE a.id_analista = '.$id_usuario.'
                               AND a.id_estatus = 1
                               '.$sql_proyecto.'
                               '.$sql_estatus.'
                               '.$sql_cliente.'
                               ORDER BY fecha ASC');

      if(count($proyectos) > 0){
      return $proyectos;
    }else{
      return array();
    }

    }


    function DetalleDivProyecto($idDproyecto){

    $info = DB::select('SELECT p.id,
                               p.descripcion,
                               (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = p.id_cliente) cliente,
                               p.horas_contratadas,
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
                               (SELECT d.descripcion FROM tbl_division d WHERE d.id = (SELECT u.id_division FROM tbl_usuario u WHERE u.id = a.id_analista)) division,
                               (SELECT sum(h.horas_trabajadas) FROM tbl_horas_cargables h WHERE h.id_proy_analista = a.id) AS suma
                          FROM tbl_proyecto_analista a
                          WHERE a.id_proyecto = '.$idDproyecto.'
                          AND a.id_estatus = 1');
    if(count($info) > 0)
    {
      return $info;
    }else{
      return array();
    }
  }

    function datosProyecto($id_proyecto,$id_division){

      $info = DB::select('SELECT p.id,
                                 p.descripcion AS proyecto,
                                 (SELECT c.razon_social FROM tbl_cliente c WHERE c.id = p.id_cliente) cliente,
                                 (SELECT d.id FROM tbl_proyecto_divisiones d WHERE d.id_proyecto = '.$id_proyecto.' AND d.id_division = '.$id_division.')id_proyecto_division
                          FROM tbl_proyecto p                      
                          WHERE id = '.$id_proyecto.'');

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
                                 (SELECT a.id_estatus FROM tbl_proyecto_analista a WHERE a.id_proyecto = '.$id_proyecto.' AND a.id_analista = u.id) estatus,

                                 (SELECT a.id FROM tbl_proyecto_analista a WHERE a.id_proyecto = '.$id_proyecto.' AND a.id_analista = u.id) idAnaProy,

                                 (SELECT sum(h.horas_trabajadas) FROM tbl_horas_cargables h WHERE h.id_proy_analista = (SELECT a.id FROM tbl_proyecto_analista a WHERE a.id_proyecto = '.$id_proyecto.' AND a.id_analista = u.id))suma,

                                 (SELECT CASE mu.C
                                      WHEN 1 THEN "true"
                                      ELSE "false"
                                      END AS permiso
                                      FROM tbl_menu_usuario mu
                                      WHERE mu.id_usuario = '.$id_usuario.'
                                      AND mu.C = 1
                                      AND mu.id_menu = '. $id_menu.'
                                      AND u.id = (SELECT a.id_analista FROM tbl_proyecto_analista a WHERE a.id_proyecto = '.$id_proyecto.' AND a.id_analista = u.id AND a.id_estatus = 1))permisoCrear
                                 
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

    function agregarAnalistaProy($estado,$idUsuario,$idProyecto,$id_proyecto_division){

      DB::beginTransaction();

      $data = array("id_estatus" => $estado,
                    "id_analista" => $idUsuario,
                    "id_proyecto" => $idProyecto,
                    "id_proyecto_division" => $id_proyecto_division);

      $analistaAgregado = DB::table('tbl_proyecto_analista')->insertGetId($data);

      if($analistaAgregado){

        DB::commit();
        return array("response" => true, "message" => "Analista agregado con éxito.");

      }else{

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de crear el analista.");

      }

    }

    function estatusAnalistaProy($idAnaProy){

      $info = DB::select('SELECT id_estatus                                 
                          FROM tbl_proyecto_analista                         
                          WHERE id = '.$idAnaProy.'');

      if(count($info) > 0){

        return $info[0];

      }else{

        return array();

      }

    }

    function modAnalistaProy($estado,$idAnaProy){

      DB::beginTransaction();

      try{

      $data = array("id_estatus" => $estado);

      $update = DB::table('tbl_proyecto_analista')->where("id",$idAnaProy)->update($data);
        
      
        DB::commit();
        return array("response" => true, "message" => "Analista actualizado con éxito.");

      } catch(\Illuminate\Database\QueryException $ex){

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de actualizar la información del proyecto.");

      }

    }

}
