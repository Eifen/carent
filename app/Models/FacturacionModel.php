<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class FacturacionModel extends Model
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

    function estatusProyectos(){

      $sql = DB::select('SELECT e.valor,
                                e.descripcion
                         FROM tbl_estatus e
                         WHERE e.tabla = "tbl_proyecto"
                         ORDER BY e.descripcion ASC');

      return $sql;

    }// Fin estatusProyectos

    function proyectosFacturacion($paginar, $desde = 0, $cliente = "", $proyecto = "", $estatus = null){

      if(trim($cliente) != ""){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

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

      $sql = DB::select('SELECT p.id,
                                UPPER(p.descripcion) AS proyecto,
                                CONCAT(so.nombre_1," ",so.nombre_2," ",so.apellido_1," ",so.apellido_2) AS socio,
                                CONCAT(ge.nombre_1," ",ge.nombre_2," ",ge.apellido_1," ",ge.apellido_2) AS gerente,
                                DATE_FORMAT(p.fecha_contratacion, "%d/%m/%Y") AS fecha_contratacion,
                                FORMAT(p.monto,2,"de_DE") AS monto_contratado,
                                mo.simbolo AS simbolo_moneda,
                                e.descripcion estatus,
                                p.id_estatus,
                                c.razon_social cliente
                         FROM tbl_proyecto p,
                              tbl_usuario so,
                              tbl_usuario ge,
                              tbl_monedas mo,
                              tbl_estatus e,
                              tbl_cliente c
                         WHERE p.id_socio = so.id
                         AND p.id_gerente = ge.id
                         AND p.id_moneda = mo.id
                         AND p.id_estatus = e.valor
                         AND p.id_cliente = c.id
                         AND e.tabla = "tbl_proyecto"
                         '.$sql_proyecto.'
                         '.$sql_cliente.'
                         '.$sql_estatus.'
                         ORDER BY p.id DESC
                         LIMIT '.$desde.', '.$paginar);

      return $sql;

    }

    function cantidadPaginasProyectoFacturacion($paginar, $cliente = "", $proyecto = "", $estatus = null){

      if(trim($cliente) != ""){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

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

      $numProyectos = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                                  FROM tbl_proyecto p,
                                       tbl_usuario so,
                                       tbl_usuario ge,
                                       tbl_monedas mo,
                                       tbl_estatus e,
                                       tbl_cliente c
                                  WHERE p.id_socio = so.id
                                  AND p.id_gerente = ge.id
                                  AND p.id_moneda = mo.id
                                  AND p.id_estatus = e.valor
                                  AND p.id_cliente = c.id
                                  AND e.tabla = "tbl_proyecto"
                                  '.$sql_proyecto.'
                                  '.$sql_cliente.'
                                  '.$sql_estatus);

      return $numProyectos[0]->paginas;

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

    function iva(){

      $sql = DB::select('SELECT id,
                                descripcion,
                                valor
                         FROM tbl_iva
                         WHERE id_estatus = 1');

      return $sql;

    }

    function porcentaje_retencion_iva(){

      $sql = DB::select('SELECT id,
                                descripcion,
                                valor
                         FROM tbl_porcentaje_retencion_iva
                         WHERE id_estatus = 1');

      return $sql;

    }

    function porcentaje_islr(){

      $sql = DB::select('SELECT id,
                                descripcion,
                                valor
                         FROM tbl_deduccion_islr
                         WHERE id_estatus = 1');

      return $sql;

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
                                fp.concepto,
                                (SELECT UPPER(numero_factura) FROM tbl_factura_proyecto WHERE id = fp.id_factura_anular) numero_factura_anular,
                                (SELECT UPPER(numero_control) FROM tbl_factura_proyecto WHERE id = fp.id_factura_anular) numero_control_anular,
                                (SELECT descripcion FROM tbl_iva i WHERE i.id = fp.id_iva) AS porc_iva,
                                (SELECT descripcion FROM tbl_porcentaje_retencion_iva t WHERE t.id = fp.id_porcentaje_retencion_iva) AS porc_retencion_iva,
                                (SELECT descripcion FROM tbl_deduccion_islr t WHERE t.id = fp.id_deduccion_islr) porc_islr,
                                TRUNCATE((
                                   fp.monto_factura
                                   *
                                   ((SELECT valor FROM tbl_iva i WHERE i.id = fp.id_iva) * (SELECT valor FROM tbl_porcentaje_retencion_iva t WHERE t.id = fp.id_porcentaje_retencion_iva) / 100)
                                   /
                                   100
                                ),2) AS retencion_iva,
                                (
                                   fp.monto_factura
                                   *
                                   (SELECT descripcion FROM tbl_deduccion_islr t WHERE t.id = fp.id_deduccion_islr)
                                    /
                                    100
                                ) AS deduccion_islr,
                                TRUNCATE((
                                  fp.monto_factura
                                  +
                                  (
                                   fp.monto_factura
                                   *
                                   ((SELECT valor FROM tbl_iva i WHERE i.id = fp.id_iva) * (SELECT valor FROM tbl_porcentaje_retencion_iva t WHERE t.id = fp.id_porcentaje_retencion_iva) / 100)
                                   /
                                   100
                                  )
                                ),2) AS subtotal,
                                fp.id_iva,
                                fp.id_deduccion_islr,
                                fp.id_porcentaje_retencion_iva,
                                (SELECT valor FROM tbl_iva i WHERE i.id = fp.id_iva) AS valor_iva
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
