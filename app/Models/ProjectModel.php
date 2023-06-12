<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProjectModel extends Model
{
    /**
     * Metodo que se encarga de abstraer de la base de dato todos los managers, o socios activos
     * @param int $status Que estado debe tener el usuario para el retorno
     * @param array $positionsArray Array numerico que indica que numero de cargos se debe mostrar de usuarios
     */
    public static function getAllAssociated($status, $positionsArray)
    {
        return DB::table('users')
            ->whereIn('position_id', $positionsArray)
            ->where([['status_id', '=', $status]])
            ->select(DB::raw('CONCAT(first_name," ",second_name," ",first_surname," ",second_surname) AS user_name'))
            ->get(['user_id', 'position_id', 'department_id']);
    }
}
