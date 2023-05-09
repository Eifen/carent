<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClientModel extends Model
{
    /**
     * Metodo que devuelve todos los socios (Usuarios) con solo dos campos: Id, Nombre
     * @param int $Status Define el tipo de estatus que debe tener el usuario para la extracción
     */
    static public function GetAllSocios($Status)
    {
        return DB::table('tbl_usuarios')
        ->whereIn('Id_jerarquia_cargo',[16,17])
        ->where('Id_estatus','=',$Status)
        ->get(['Id','Primer_nombre','Segundo_nombre','Primer_apellido','Segundo_apellido']);
    }
    
    /**
     * Metodo que devuelve todos los sectores activos. Campo Id y Nombre_sector
     * @param int $Status Captura el tipo de estatus
     * @return Array Retorna un array de todos los sectores
     */
    static public function GetAllSectores($Status){
        return DB::table('tbl_clientes_sector')
        ->where('Id_estatus',$Status)
        ->get(['Id','Nombre_sector']);
    }

    /**
     * Metodo que devuelve todos los servicios activos. Campo Id y Nombre_servicio
     * @param int $Status Captura el tipo de estatus
     * @return Array Retorna un array de todos los servicios
     */
    static public function GetAllServicios($Status){
        return DB::table('tbl_clientes_servicios')
        ->where('Id_estatus',$Status)
        ->get(['Id','Nombre_servicio']);
    }

    /**
     * Metodo sin parametros que devuelve todos los paises registrados
     * @return Array Retorna un array de todos los paises
     */
    static public function GetAllPaises()
    {
        return DB::table('tbl_clientes_direccion_pais')->get();
    }
}
