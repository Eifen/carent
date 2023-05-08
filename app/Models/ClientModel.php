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
}
