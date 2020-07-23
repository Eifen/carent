<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class FacturacionModel extends Model
{

    function divisiones(){

      $sql = DB::select('SELECT d.id,
                                d.descripcion
                         FROM tbl_division d
                         ORDER BY d.descripcion ASC');

      return $sql;

    }// Fin divisiones

    function estatusProyectos(){

      $sql = DB::select('SELECT e.valor,
                                e.descripcion
                         FROM tbl_estatus e
                         WHERE e.tabla = "tbl_proyecto"
                         ORDER BY e.descripcion ASC');

      return $sql;

    }// Fin estatusProyectos

}
