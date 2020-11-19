<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ClientesProyectoModel extends Model
{

    /*
      Función que valida si supervisa a empleados o no
    */
    function divisiones(){

      $divisiones = DB::select('SELECT d.id,
                                       d.descripcion
                                FROM tbl_division d
                                WHERE d.id_estatus = 1
                                ORDER BY d.descripcion ASC');

      return $divisiones;

    }// Fin divisiones

    function estatusClientes(){

      $estatus = DB::select('SELECT e.valor AS id,
                                    e.descripcion
                             FROM tbl_estatus e
                             WHERE tabla = "tbl_cliente"
                             ORDER BY e.descripcion ASC');

      return $estatus;

    }// Fin estatusProyectos

    function empresaClientes(){

      $empresa = DB::select('SELECT em.id,
                                    em.razon_social
                             FROM tbl_empresa em
                             ORDER BY em.razon_social ASC');

      return $empresa;

    }// Fin empresaClientes

    function cantidadClie(){

      $clienteCant = DB::select('SELECT COUNT(c.id) AS cantidad
                             FROM tbl_cliente c
                             WHERE c.id_estatus = 1');

      return $clienteCant[0];

    }// Fin cantidadClie

    function repoCantidadClientes($id_usuario, $paginar, $divisiones, $desde = 0, $empresa = null, $cliente = null, $estatus = null){

      if($divisiones == null){
        $sql_division = "";
      }else{

        $idsDivision = [];

        if(is_array($divisiones)){

          foreach ($divisiones as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsDivision,$item->id);
          }

        }else{

          array_push($idsDivision,$divisiones);

        }

        $idsDivision = implode(",", $idsDivision);
        $sql_division = "AND pd.id_division IN(".$idsDivision.")";

      }
    
      if($cliente != null && trim($cliente) != ""){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if($empresa !== null){
        $sql_empresa = 'AND p.id_empresa = '.$empresa;
      }else{
        $sql_empresa = '';
      }


      if($estatus !== null){
        $sql_estatus = 'AND c.id_estatus = '.$estatus;
      }else{
        $sql_estatus = '';
      }

      
      $sql = DB::select('SELECT p.id_cliente AS id_cliente,
                                c.razon_social AS cliente,
                                e.descripcion AS estatus,
                                GROUP_CONCAT(DISTINCT em.razon_social SEPARATOR "; ") AS empresa,
                                GROUP_CONCAT(DISTINCT d.descripcion SEPARATOR "; ") AS division
                         FROM tbl_proyecto p,
                              tbl_cliente c,
                              tbl_division d,
                              tbl_empresa em,
                              tbl_proyecto_divisiones pd,
                              tbl_usuario u,
                              tbl_estatus e
                         WHERE p.id_cliente = c.id
                         AND p.id_empresa = em.id
                         AND p.id_estatus = e.valor
                         AND e.tabla = "tbl_proyecto"
                         AND p.id_estatus = 1
                         AND (pd.id_proyecto = p.id AND pd.id_division = d.id)
                         '.$sql_division.'
                         '.$sql_cliente.'
                         '.$sql_empresa.'
                         '.$sql_estatus.'
                         GROUP BY p.id_cliente,
                                  c.razon_social,
                                  e.descripcion 
                         LIMIT '.$desde.', '.$paginar);

      return $sql;

    }

    function pagCantidadClientes($id_usuario, $paginar, $divisiones, $empresa = null, $cliente = null, $estatus = null){

      if($divisiones == null){
        $sql_division = "";
      }else{

        $idsDivision = [];

        if(is_array($divisiones)){

          foreach ($divisiones as $key => $item) {

            if(!isset($item->id)){
              $item = json_decode($item);
            }

            array_push($idsDivision,$item->id);
          }

        }else{

          array_push($idsDivision,$divisiones);

        }

        $idsDivision = implode(",", $idsDivision);
        $sql_division = "AND pd.id_division IN(".$idsDivision.")";

      }

      if($cliente != null && trim($cliente) != ""){
        $sql_cliente = 'AND LOWER(c.razon_social) LIKE "%'.strtolower($cliente).'%"';
      }else{
        $sql_cliente = "";
      }

      if($empresa !== null){
        $sql_empresa = 'AND p.id_empresa = '.$empresa;
      }else{
        $sql_empresa = '';
      }

      if($estatus !== null){
        $sql_estatus = 'AND c.id_estatus = '.$estatus;
      }else{
        $sql_estatus = '';
      }

      $sql = DB::select('SELECT CEILING( COUNT(1) / '.$paginar.') paginas
                         FROM(

                           SELECT p.id_cliente AS id_cliente,
                                c.razon_social AS cliente,
                                e.descripcion AS estatus,
                                GROUP_CONCAT(DISTINCT em.razon_social SEPARATOR "; ") AS empresa,
                                GROUP_CONCAT(DISTINCT d.descripcion SEPARATOR "; ") AS division
                         FROM tbl_proyecto p,
                              tbl_cliente c,
                              tbl_division d,
                              tbl_empresa em,
                              tbl_proyecto_divisiones pd,
                              tbl_usuario u,
                              tbl_estatus e
                         WHERE p.id_cliente = c.id
                         AND  (c.id_usuario_socio = u.id OR c.id_usuario_socio > 5000)
                         AND p.id_empresa = em.id
                         AND p.id_estatus = e.valor
                         AND e.tabla = "tbl_proyecto"
                         AND p.id_estatus = 1
                         AND (pd.id_proyecto = p.id AND pd.id_division = d.id)
                         '.$sql_division.'
                         '.$sql_cliente.'
                         '.$sql_empresa.'
                         '.$sql_estatus.'
                         GROUP BY p.id_cliente,
                                  c.razon_social,
                                  e.descripcion
                         )t'
                       );

      return $sql[0]->paginas;

    }

}
