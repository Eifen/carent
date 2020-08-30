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
                               ) AS permiso_actualizar');

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

    function proyectosFacturacion(){

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
                         ORDER BY p.descripcion ASC');

      return $sql;

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
                         WHERE p.id = ?
                         AND p.id_socio = so.id
                         AND p.id_gerente = ge.id
                         AND p.id_moneda = mo.id
                         AND p.id_estatus = e.valor
                         AND e.tabla = "tbl_proyecto"', [$id_proyecto]);

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

    function proyectoFacturasCargadas($id_proyecto){

      $sql = DB::select('SELECT fp.id,
                                UPPER(fp.numero_factura) AS numero_factura,
                                FORMAT(fp.monto_factura,2,"de_DE") AS monto_factura,
                                DATE_FORMAT(fp.fecha_factura, "%d/%m/%Y") AS fecha_factura,
                                UPPER(fp.numero_control) AS numero_control,
                                fp.observaciones,
                                cf.descripcion AS concepto,
                                LOWER(CONCAT(fu.nombre_1," ",fu.nombre_2," ",fu.apellido_1," ",fu.apellido_2)) AS facturador,
                                cf.id_tipo_concepto_factura AS tipo_movimiento,
                                (SELECT descripcion FROM tbl_tipo_concepto_factura WHERE id = cf.id_tipo_concepto_factura) AS movimiento
                         FROM tbl_factura_proyecto fp,
                              tbl_concepto_factura cf,
                              tbl_usuario fu
                         WHERE fp.id_concepto_factura = cf.id
                         AND fp.id_facturador = fu.id
                         AND fp.id_proyecto = ?
                         AND fp.id_estatus = 1', [$id_proyecto]);

      return $sql;

    }

    function registrarFactura($parametros){

      if(DB::table('tbl_factura_proyecto')->insert($parametros)){
        return true;
      }else{
        return false;
      }

    }

}
