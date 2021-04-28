<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class facturadoCliProyModel extends Model
{

    function estatusProyectos(){

      $estatus = DB::select('SELECT e.valor AS id,
                                    e.descripcion
                             FROM tbl_estatus e
                             WHERE tabla = "tbl_proyecto"
                             ORDER BY e.descripcion ASC');

      return $estatus;

    }// Fin estatusClientes

    function monedas(){

      $sql = DB::select('SELECT m.id,
                                m.moneda AS descripcion
                         FROM tbl_monedas m
                         ORDER BY m.moneda ASC');

      return $sql;

    }// Fin estatusClientes

    function repFacturadoCliProy($paginar, $filtros){

      if($filtros["razon_social"] !== null){
        $sql_razon_social = 'AND c.razon_social LIKE "%'.$filtros["razon_social"].'%"';
      }else{
        $sql_razon_social = '';
      }

      if($filtros["rif"] !== null){
        $sql_rif = 'AND c.rif LIKE "'.$filtros["rif"].'%"';
      }else{
        $sql_rif = '';
      }

      if($filtros["estatus"] !== null){
        $sql_estatus = 'AND p.id_estatus = '.$filtros["estatus"];
      }else{
        $sql_estatus = '';
      }

      if($filtros["proyecto"] !== null){
        $sql_proyecto = 'AND p.descripcion LIKE "'.$filtros["proyecto"].'%"';
      }else{
        $sql_proyecto = '';
      }

      if($filtros["monedas"] !== null){
        $sql_monedas = 'AND p.id_moneda LIKE "'.$filtros["monedas"].'%"';
      }else{
        $sql_monedas = '';
      }

      $sql = DB::select('SELECT *,
                                FORMAT(monto_proyecto,2,"de_DE") AS monto_proyecto_formated,
                                FORMAT(monto_facturado,2,"de_DE") AS monto_facturado_formated,
                                FORMAT(monto_notas_credito,2,"de_DE") AS monto_notas_credito_formated,
                                FORMAT(monto_gasto,2,"de_DE") AS monto_gasto_formated,
                                FORMAT(monto_otros_gastos,2,"de_DE") AS monto_otros_gastos_formated,
                                CONCAT(ROUND(((monto_facturado/(monto_proyecto + monto_notas_credito + monto_gasto + monto_otros_gastos))*100),2)," %") AS porct_facturado
                         FROM(
                           SELECT p.id AS id_proyecto,
                                  p.descripcion AS proyecto,
                                  p.monto AS monto_proyecto,
                                  e.descripcion AS estatus,
                                  p.id_estatus,
                                  c.id AS id_cliente,
                                  c.razon_social AS cliente,
                                  m.simbolo AS moneda,
                                  (
                                   SELECT IF(
                                           SUM(fp.monto_factura) IS NULL,
                                           0,
                                           SUM(fp.monto_factura)
                                          )
                                   FROM tbl_factura_proyecto fp,
                                        tbl_concepto_factura cf
                                   WHERE fp.id_concepto_factura = cf.id
                                   AND fp.id_proyecto = p.id
                                   AND cf.id_tipo_concepto_factura = 1
                                   AND fp.id NOT IN(
                                     SELECT id_factura_anular FROM tbl_factura_proyecto WHERE id_estatus = 1 AND id_factura_anular IS NOT NULL
                                   )
                                   AND fp.id_estatus = 1
                                  ) AS monto_facturado,
                                  (
                                   SELECT IF(
                                            SUM(fp.monto_factura) IS NULL,
                                            0,
                                            SUM(fp.monto_factura)
                                            )
                                   FROM tbl_factura_proyecto fp,
                                        tbl_concepto_factura cf
                                   WHERE fp.id_concepto_factura = cf.id
                                   AND fp.id_proyecto = p.id
                                   AND cf.id_tipo_concepto_factura = 3
                                   AND fp.id_estatus = 1
                                  ) AS monto_notas_credito,
                                  (
                                   SELECT IF(
                                            SUM(fp.monto_factura) IS NULL,
                                            0,
                                            SUM(fp.monto_factura)
                                          )
                                   FROM tbl_factura_proyecto fp,
                                        tbl_concepto_factura cf
                                   WHERE fp.id_concepto_factura = cf.id
                                   AND fp.id_proyecto = p.id
                                   AND cf.id_tipo_concepto_factura = 2
                                   AND cf.id <> 5
                                   AND fp.id_estatus = 1
                                  ) AS monto_gasto,
                                  (
                                   SELECT IF(
                                            SUM(fp.monto_factura) IS NULL,
                                            0,
                                            SUM(fp.monto_factura)
                                          )
                                   FROM tbl_factura_proyecto fp,
                                        tbl_concepto_factura cf
                                   WHERE fp.id_concepto_factura = cf.id
                                   AND fp.id_proyecto = p.id
                                   AND cf.id_tipo_concepto_factura = 2
                                   AND cf.id = 5
                                   AND fp.id_estatus = 1
                                  ) AS monto_otros_gastos
                          FROM tbl_proyecto p,
                               tbl_factura_proyecto fp,
                               tbl_cliente c,
                               tbl_estatus e,
                               tbl_monedas m
                          WHERE p.id = fp.id_proyecto
                          AND p.id_cliente = c.id
                          AND p.id_estatus = e.valor
                          AND e.tabla = "tbl_proyecto"
                          AND p.id_moneda = m.id
                          '.$sql_razon_social.'
                          '.$sql_rif.'
                          '.$sql_estatus.'
                          '.$sql_proyecto.'
                          '.$sql_monedas.'
                          GROUP BY p.id,
                                   p.descripcion,
                                   e.descripcion,
                                   c.id,
                                   c.razon_social,
                                   p.monto
                          ORDER BY razon_social ASC
                      ) t
                      LIMIT '.$paginar["desde"].', '.$paginar["paginar"]);

      return $sql;

    }

    function totalesFacturadoCliProy($filtros){

      if($filtros["razon_social"] !== null){
        $sql_razon_social = 'AND c.razon_social LIKE "%'.$filtros["razon_social"].'%"';
      }else{
        $sql_razon_social = '';
      }

      if($filtros["rif"] !== null){
        $sql_rif = 'AND c.rif LIKE "'.$filtros["rif"].'%"';
      }else{
        $sql_rif = '';
      }

      if($filtros["estatus"] !== null){
        $sql_estatus = 'AND p.id_estatus = '.$filtros["estatus"];
      }else{
        $sql_estatus = '';
      }

      if($filtros["proyecto"] !== null){
        $sql_proyecto = 'AND p.descripcion LIKE "'.$filtros["proyecto"].'%"';
      }else{
        $sql_proyecto = '';
      }

      if($filtros["monedas"] !== null){
        $sql_monedas = 'AND p.id_moneda LIKE "'.$filtros["monedas"].'%"';
      }else{
        $sql_monedas = '';
      }

      $sql = DB::select('SELECT COUNT(1) AS proyectos
                         FROM tbl_proyecto p,
                              tbl_cliente c
                         WHERE p.id IN (
                           SELECT DISTINCT fp.id_proyecto
                           FROM tbl_factura_proyecto fp
                         )
                         AND p.id_cliente = c.id
                         '.$sql_razon_social.'
                         '.$sql_rif.'
                         '.$sql_estatus.'
                         '.$sql_proyecto.'
                         '.$sql_monedas);

      return $sql[0];

    }

    function pagFacturadoCliProy($paginar, $filtros){

      if($filtros["razon_social"] !== null){
        $sql_razon_social = 'AND c.razon_social LIKE "%'.$filtros["razon_social"].'%"';
      }else{
        $sql_razon_social = '';
      }

      if($filtros["rif"] !== null){
        $sql_rif = 'AND c.rif LIKE "'.$filtros["rif"].'%"';
      }else{
        $sql_rif = '';
      }

      if($filtros["estatus"] !== null){
        $sql_estatus = 'AND p.id_estatus = '.$filtros["estatus"];
      }else{
        $sql_estatus = '';
      }

      if($filtros["proyecto"] !== null){
        $sql_proyecto = 'AND p.descripcion LIKE "'.$filtros["proyecto"].'%"';
      }else{
        $sql_proyecto = '';
      }

      if($filtros["monedas"] !== null){
        $sql_monedas = 'AND p.id_moneda LIKE "'.$filtros["monedas"].'%"';
      }else{
        $sql_monedas = '';
      }

      $sql = DB::select('SELECT CEILING( COUNT(1) / '.$paginar["paginar"].') paginas
                         FROM(

                           SELECT p.id AS id_proyecto,
                                  p.descripcion AS proyecto,
                                  FORMAT(p.monto,2,"de_DE") AS monto_proyecto,
                                  e.descripcion AS estatus_proyecto,
                                  c.id AS id_cliente,
                                  c.razon_social AS cliente,
                                  (
                                   SELECT IF(
                                            SUM(fp.monto_factura) IS NULL,
                                            FORMAT(0,2,"de_DE"),
                                            FORMAT(SUM(fp.monto_factura),2,"de_DE")
                                          )
                                   FROM tbl_factura_proyecto fp,
                                        tbl_concepto_factura cf
                                   WHERE fp.id_concepto_factura = cf.id
                                   AND fp.id_proyecto = p.id
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
                                   AND fp.id_proyecto = p.id
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
                                   AND fp.id_proyecto = p.id
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
                                   AND fp.id_proyecto = p.id
                                   AND cf.id_tipo_concepto_factura = 2
                                   AND cf.id = 5
                                   AND fp.id_estatus = 1
                                  ) AS monto_otros_gastos
                          FROM tbl_proyecto p,
                               tbl_factura_proyecto fp,
                               tbl_cliente c,
                               tbl_estatus e
                          WHERE p.id = fp.id_proyecto
                          AND p.id_cliente = c.id
                          AND p.id_estatus = e.valor
                          AND e.tabla = "tbl_proyecto"
                          '.$sql_razon_social.'
                          '.$sql_rif.'
                          '.$sql_estatus.'
                          '.$sql_proyecto.'
                          '.$sql_monedas.'
                          GROUP BY p.id,
                                   p.descripcion,
                                   e.descripcion,
                                   c.id,
                                   c.razon_social

                         )t'
                       );

      return $sql[0]->paginas;

    }

}
