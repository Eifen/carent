<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class facturadoCliProyModel extends Model
{

    function estatusClientes(){

      $estatus = DB::select('SELECT e.valor AS id,
                                    e.descripcion
                             FROM tbl_estatus e
                             WHERE tabla = "tbl_cliente"
                             ORDER BY e.descripcion ASC');

      return $estatus;

    }// Fin estatusClientes

    function repoClientes($paginar, $filtros){

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

      if($filtros["socio"] !== null){
        $sql_socio = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($filtros["socio"]).'%"';
      }else{
        $sql_socio = '';
      }

      if($filtros["estatus"] !== null){
        $sql_estatus = 'AND c.id_estatus = '.$filtros["estatus"];
      }else{
        $sql_estatus = '';
      }

      $sql = DB::select('SELECT c.rif,
                                c.razon_social,
                                CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS socio,
                                e.descripcion AS estatus,
                                e.valor AS id_estatus
                          FROM tbl_cliente c,
                               tbl_usuario u,
                               tbl_estatus e
                          WHERE c.id_usuario_socio = u.id
                          AND c.id_estatus = e.valor
                          AND e.tabla = "tbl_cliente"
                          '.$sql_razon_social.'
                          '.$sql_rif.'
                          '.$sql_socio.'
                          '.$sql_estatus.'
                          ORDER BY razon_social ASC
                          LIMIT '.$paginar["desde"].', '.$paginar["paginar"]);

      return $sql;

    }

    function totalesClientes($filtros){

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

      if($filtros["socio"] !== null){
        $sql_socio = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($filtros["socio"]).'%"';
      }else{
        $sql_socio = '';
      }

      if($filtros["estatus"] !== null){
        $sql_estatus = 'AND c.id_estatus = '.$filtros["estatus"];
      }else{
        $sql_estatus = '';
      }

      $sql = DB::select('SELECT COUNT(1) AS clientes
                         FROM tbl_cliente c,
                              tbl_usuario u,
                              tbl_estatus e
                         WHERE c.id_usuario_socio = u.id
                         AND c.id_estatus = e.valor
                         AND e.tabla = "tbl_cliente"
                         '.$sql_razon_social.'
                         '.$sql_rif.'
                         '.$sql_socio.'
                         '.$sql_estatus);

      return $sql[0];

    }

    function pagClientes($paginar, $filtros){

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

      if($filtros["socio"] !== null){
        $sql_socio = 'AND LOWER(CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2)) LIKE "%'.strtolower($filtros["socio"]).'%"';
      }else{
        $sql_socio = '';
      }

      if($filtros["estatus"] !== null){
        $sql_estatus = 'AND c.id_estatus = '.$filtros["estatus"];
      }else{
        $sql_estatus = '';
      }

      $sql = DB::select('SELECT CEILING( COUNT(1) / '.$paginar["paginar"].') paginas
                         FROM(

                           SELECT c.rif,
                                  c.razon_social,
                                  CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS socio,
                                  e.descripcion AS estatus
                           FROM tbl_cliente c,
                                tbl_usuario u,
                                tbl_estatus e
                           WHERE c.id_usuario_socio = u.id
                           AND c.id_estatus = e.valor
                           AND e.tabla = "tbl_cliente"
                           '.$sql_razon_social.'
                           '.$sql_rif.'
                           '.$sql_socio.'
                           '.$sql_estatus.'

                         )t'
                       );

      return $sql[0]->paginas;

    }

}
