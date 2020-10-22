<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ReportesModel extends Model
{

    function permisosMenu($id_usuario, $id_menu){

      $permisos = DB::select('SELECT
                               (SELECT COUNT(1)
                                FROM tbl_menu_usuario
                                WHERE id_usuario = '.$id_usuario.'
                                AND id_menu = '.$id_menu.'
                                AND r = 1
                                LIMIT 1
                               ) AS permiso_ver,
                               (SELECT COUNT(1)
                                FROM tbl_menu_usuario
                                WHERE id_usuario = '.$id_usuario.'
                                AND id_menu = '.$id_menu.'
                                AND c = 1
                                LIMIT 1
                               ) AS permiso_crear,
                               (SELECT COUNT(1)
                                FROM tbl_menu_usuario
                                WHERE id_usuario = '.$id_usuario.'
                                AND id_menu = '.$id_menu.'
                                AND u = 1
                                LIMIT 1
                              ) AS permiso_actualizar,
                              (SELECT COUNT(1)
                               FROM tbl_menu_usuario
                               WHERE id_usuario = '.$id_usuario.'
                               AND id_menu = '.$id_menu.'
                               AND d = 1
                               LIMIT 1
                             ) AS permiso_eliminar');

      return $permisos[0];

    }// Fin permisosMenu

    function reportesAsociados($id_usuario){

      $sql = DB::select('SELECT m.id,
                                m.id_menu_padre,
                                m.descripcion,
                                m.url,
                                m.visible
                          FROM tbl_menu m,
                               tbl_menu_usuario mu
                          WHERE m.id = mu.id_menu
                          AND mu.id_usuario = "'.$id_usuario.'"
                          AND m.id_estatus = 1
                          AND m.id IN(18)
                          ORDER BY m.id_menu_padre ASC');

      return $sql;

    }

    function cargosEmpleado(){

      $sql = DB::select('SELECT c.id,
                                c.descripcion
                         FROM tbl_cargo_empleado c
                         WHERE c.id_estatus = 1
                         ORDER BY c.descripcion ASC');

      return $sql;

    }// Fin cargosEmpleado

    function divisiones(){

      $sql = DB::select('SELECT d.id,
                                d.descripcion
                         FROM tbl_division d
                         WHERE d.id_estatus = 1
                         ORDER BY d.descripcion ASC');

      return $sql;

    }// Fin divisiones

    function repoHorasCargables($paginar, $desde = 0, $cargos = null, $cliente = null, $divisiones = null, $proyecto = null, $empleado = null){

      if($cargos !== null){

        $idsCargo = [];
        foreach ($cargos as $key => $item) {
          $item = json_decode($item);
          array_push($idsCargo,$item->id);
        }

        $idsCargo = implode(",", $idsCargo);
        $sql_cargos = "AND ce.id IN(".$idsCargo.")";

      }else{
        $sql_cargos = "";
      }

      if($cliente != null && trim($cliente) != ""){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if($divisiones !== null){

        $idsDivision = [];
        foreach ($divisiones as $key => $item) {
          $item = json_decode($item);
          array_push($idsDivision,$item->id);
        }

        $idsDivision = implode(",", $idsDivision);
        $sql_division = "AND u.id_division IN(".$idsDivision.")";

      }else{
        $sql_division = "";
      }

      if($proyecto != null && trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
      }

      $sql = DB::select('SELECT p.id AS id_proyecto,
                                p.descripcion AS proyecto,
                                u.id_division,
                                d.descripcion AS division,
                                u.id AS id_usuario,
                                u.id_cargo,
                                ce.descripcion AS cargo,
                                CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS empleado,
                                c.id AS id_cliente,
                                c.razon_social AS cliente,
                                TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(hc.horas_trabajadas))),"%H:%i") horas_trabajadas
                         FROM tbl_horas_cargables hc,
                              tbl_proyecto_analista pa,
                              tbl_usuario u,
                              tbl_proyecto p,
                              tbl_cliente c,
                              tbl_division d,
                              tbl_cargo_empleado ce
                         WHERE hc.id_proy_analista = pa.id
                         AND pa.id_analista = u.id
                         AND pa.id_proyecto = p.id
                         AND p.id_cliente = c.id
                         AND u.id_division = d.id
                         AND u.id_cargo = ce.id
                         '.$sql_cargos.'
                         '.$sql_cliente.'
                         '.$sql_division.'
                         '.$sql_proyecto.'
                         GROUP BY p.id,
                                  p.descripcion,
                                  u.nombre_1,
                                  u.nombre_2,
                                  u.apellido_1,
                                  u.apellido_2,
                                  c.razon_social,
                                  c.id,
                                  u.id,
                                  u.id_division,
                                  u.id_cargo
                         LIMIT '.$desde.', '.$paginar);

      return $sql;

    }

    function pagHorasCargables($paginar, $cargos = null, $cliente = null, $divisiones = null, $proyecto = null, $empleado = null){

      if($cargos !== null){

        $idsCargo = [];
        foreach ($cargos as $key => $item) {
          $item = json_decode($item);
          array_push($idsCargo,$item->id);
        }

        $idsCargo = implode(",", $idsCargo);
        $sql_cargos = "AND ce.id IN(".$idsCargo.")";

      }else{
        $sql_cargos = "";
      }

      if($cliente != null && trim($cliente) != ""){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if($divisiones !== null){

        $idsDivision = [];
        foreach ($divisiones as $key => $item) {
          $item = json_decode($item);
          array_push($idsDivision,$item->id);
        }

        $idsDivision = implode(",", $idsDivision);
        $sql_division = "AND u.id_division IN(".$idsDivision.")";

      }else{
        $sql_division = "";
      }

      if($proyecto != null && trim($proyecto) != ""){
        $sql_proyecto = 'AND LOWER(p.descripcion) LIKE "%'.strtolower($proyecto).'%"';
      }else{
        $sql_proyecto = "";
      }

      $sql = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                         FROM(

                           SELECT p.descripcion AS proyecto,
                                  u.id_division,
                                  u.id_cargo,
                                  ce.descripcion AS cargo,
                                  c.razon_social
                           FROM tbl_horas_cargables hc,
                                tbl_proyecto_analista pa,
                                tbl_usuario u,
                                tbl_proyecto p,
                                tbl_cliente c,
                                tbl_division d,
                                tbl_cargo_empleado ce
                           WHERE hc.id_proy_analista = pa.id
                           AND pa.id_analista = u.id
                           AND pa.id_proyecto = p.id
                           AND p.id_cliente = c.id
                           AND u.id_division = d.id
                           AND u.id_cargo = ce.id
                           '.$sql_cargos.'
                           '.$sql_cliente.'
                           '.$sql_division.'
                           '.$sql_proyecto.'
                           GROUP BY p.descripcion,
                                    u.id_division,
                                    u.id_cargo,
                                    ce.descripcion,
                                    c.razon_social
                         )t'
                       );

      return $sql[0]->paginas;

    }

    function proyecto($id_proyecto){

      $sql = DB::select('SELECT p.id,
                                UPPER(p.descripcion) AS proyecto,
                                LOWER(CONCAT(so.nombre_1," ",so.nombre_2," ",so.apellido_1," ",so.apellido_2)) AS socio,
                                LOWER(CONCAT(ge.nombre_1," ",ge.nombre_2," ",ge.apellido_1," ",ge.apellido_2)) AS gerente,
                                DATE_FORMAT(p.fecha_contratacion, "%d/%m/%Y") AS fecha_contratacion,
                                FORMAT(p.monto,2,"de_DE") AS monto_contratado,
                                mo.simbolo AS simbolo_moneda,
                                e.descripcion estatus,
                                p.id_estatus
                         FROM tbl_proyecto p,
                              tbl_usuario so,
                              tbl_usuario ge,
                              tbl_monedas mo,
                              tbl_estatus e
                         WHERE p.id = '.$id_proyecto.'
                         AND p.id_socio = so.id
                         AND p.id_gerente = ge.id
                         AND p.id_moneda = mo.id
                         AND p.id_estatus = e.valor
                         AND e.tabla = "tbl_proyecto"');

      return $sql[0];

    }

    function facturadoProyecto($id_proyecto){

      $sql = DB::select('SELECT (
                                  SELECT IF(
                                             SUM(fp.monto_factura) IS NULL,
                                             FORMAT(0,2,"de_DE"),
                                             FORMAT(SUM(fp.monto_factura),2,"de_DE")
                                           )
                                  FROM tbl_factura_proyecto fp,
                                       tbl_concepto_factura cf
                                  WHERE fp.id_concepto_factura = cf.id
                                  AND fp.id_proyecto = '.$id_proyecto.'
                                  AND cf.id_tipo_concepto_factura = 1
                                  AND fp.id NOT IN(
                                    SELECT id_factura_anular FROM tbl_factura_proyecto WHERE id_estatus = 1 AND id_factura_anular IS NOT NULL
                                  )
                                  AND fp.id_estatus = 1
                               ) AS monto_facturado,
                               (
                                  SELECT IF(
                                            SUM(fp.monto_factura) IS NULL,
                                            FORMAT(0,2,"de_DE"),
                                            FORMAT(SUM(fp.monto_factura),2,"de_DE")
                                          )
                                  FROM tbl_factura_proyecto fp,
                                       tbl_concepto_factura cf
                                  WHERE fp.id_concepto_factura = cf.id
                                  AND fp.id_proyecto = '.$id_proyecto.'
                                  AND cf.id_tipo_concepto_factura = 3
                                  AND fp.id_estatus = 1
                               ) AS monto_notas_credito,
                               (
                                 SELECT IF(
                                            SUM(fp.monto_factura) IS NULL,
                                            FORMAT(0,2,"de_DE"),
                                            FORMAT(SUM(fp.monto_factura),2,"de_DE")
                                          )
                                 FROM tbl_factura_proyecto fp,
                                      tbl_concepto_factura cf
                                 WHERE fp.id_concepto_factura = cf.id
                                 AND fp.id_proyecto = '.$id_proyecto.'
                                 AND cf.id_tipo_concepto_factura = 2
                                 AND cf.id <> 5
                                 AND fp.id_estatus = 1
                              ) AS monto_gasto,
                              (
                                SELECT IF(
                                           SUM(fp.monto_factura) IS NULL,
                                           FORMAT(0,2,"de_DE"),
                                           FORMAT(SUM(fp.monto_factura),2,"de_DE")
                                         )
                                FROM tbl_factura_proyecto fp,
                                     tbl_concepto_factura cf
                                WHERE fp.id_concepto_factura = cf.id
                                AND fp.id_proyecto = '.$id_proyecto.'
                                AND cf.id_tipo_concepto_factura = 2
                                AND cf.id = 5
                                AND fp.id_estatus = 1
                             ) AS monto_otros_gastos');

      return $sql[0];

    }

    function conceptosFactura(){

      $sql = DB::select('SELECT cf.id,
                                cf.descripcion,
                                cf.id_tipo_concepto_factura
                         FROM tbl_concepto_factura cf
                         WHERE cf.id_estatus = 1
                         ORDER BY descripcion ASC');

      return $sql;

    }

    function proyectoFacturasCargadas($id_proyecto, $numero_factura, $desde, $hasta, $tipo_factura = 0){

      if($numero_factura != null){

        $condicion = " AND UPPER(fp.numero_factura) LIKE UPPER('".$numero_factura."%')
                      AND fp.id_concepto_factura = ".$tipo_factura."
                      AND fp.id NOT IN( SELECT id_factura_anular FROM tbl_factura_proyecto WHERE id_estatus = 1 AND id_factura_anular IS NOT NULL )";

        $limit = " LIMIT ".$hasta;

      }else{

        $condicion = "";
        $limit = " LIMIT ".$desde.",".$hasta;

      }

      $sql = DB::select('SELECT fp.id,
                                UPPER(fp.numero_factura) AS numero_factura,
                                FORMAT(fp.monto_factura,2,"de_DE") AS monto_factura_formatted,
                                fp.monto_factura AS monto_factura,
                                DATE_FORMAT(fp.fecha_factura, "%d/%m/%Y") AS fecha_factura_formatted,
                                fp.fecha_factura,
                                DATE_FORMAT(fp.fecha_cobro_factura, "%d/%m/%Y") AS fecha_cobro_factura_formatted,
                                fp.fecha_cobro_factura,
                                UPPER(fp.numero_control) AS numero_control,
                                fp.observaciones,
                                cf.id as id_concepto_factura,
                                cf.descripcion AS tipo_concepto,
                                LOWER(CONCAT(fu.nombre_1," ",fu.nombre_2," ",fu.apellido_1," ",fu.apellido_2)) AS facturador,
                                cf.id_tipo_concepto_factura AS tipo_movimiento,
                                (SELECT descripcion FROM tbl_tipo_concepto_factura WHERE id = cf.id_tipo_concepto_factura) AS movimiento,
                                fp.concepto
                         FROM tbl_factura_proyecto fp,
                              tbl_concepto_factura cf,
                              tbl_usuario fu
                         WHERE fp.id_concepto_factura = cf.id
                         AND fp.id_facturador = fu.id
                         AND fp.id_proyecto = ?
                         AND fp.id_estatus = 1
                         '.$condicion.'
                         ORDER BY fp.id DESC
                         '.$limit, [$id_proyecto]);

      return $sql;

    }

    function cantidadPaginasFacturasCargadas($paginar, $id_proyecto){

      $numFacturas = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                                  FROM tbl_factura_proyecto fp,
                                       tbl_concepto_factura cf,
                                       tbl_usuario fu
                                  WHERE fp.id_concepto_factura = cf.id
                                  AND fp.id_facturador = fu.id
                                  AND fp.id_proyecto = ?
                                  AND fp.id_estatus = 1
                                  ORDER BY fp.id DESC', [$id_proyecto]);

      return $numFacturas[0]->paginas;

    }

    function registrarFactura($parametros){

      if(DB::table('tbl_factura_proyecto')->insert($parametros)){
        return true;
      }else{
        return false;
      }

    }

    function eliminarFactura($id_factura, $parametros){

      if(DB::table('tbl_factura_proyecto')->where("id", $id_factura)->update($parametros)){
        return true;
      }else{
        return false;
      }

    }

    function actualizarFactura($id_factura, $parametros){

      if(DB::table('tbl_factura_proyecto')->where("id", $id_factura)->update($parametros)){
        return true;
      }else{
        return false;
      }

    }

}
