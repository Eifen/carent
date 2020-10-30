<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class InicioModel extends Model
{

    function menUsuario($id_usuario){

      $menus = DB::select('SELECT m.id,
                                  m.id_menu_padre,
                                  m.descripcion,
                                  m.url,
                                  m.visible
                           FROM tbl_menu m,
                                tbl_menu_usuario mu
                           WHERE m.id = mu.id_menu
                           AND mu.id_usuario = "'.$id_usuario.'"
                           AND m.id_estatus = 1
                           ORDER BY m.id_menu_padre ASC');

      if(count($menus) > 0){

        $array_menus = $this->arbolMenu($menus);

        return $array_menus;

      }else{

        return array();

      }

    }// Fin menUsuario

    private function arbolMenu($menus){

      $arbolMenu = [];

      foreach($menus as $menu){

        $ramaTmp = $this->ramas($menu);
        $rama[$ramaTmp->id] = $ramaTmp;
        $arbolMenu = $this->unirRamas($arbolMenu, $rama);

      }// Fin foreach

      return $arbolMenu;

    }

    private function ramas($menu){

      $sql = DB::select('SELECT m.id,
                                m.id_menu_padre,
                                m.descripcion,
                                m.url,
                                m.visible
                         FROM tbl_menu m
                         WHERE m.id = "'.$menu->id_menu_padre.'"
                         AND m.id_estatus = 1');

      if(empty($sql)){

        $menu->submenu = [];
        return $menu;

      }else{

        if(!property_exists($menu, "submenu")){
          $menu->submenu = [];
        }

        if($sql[0]->id_menu_padre === 0){

          if($menu->visible === 1){
            $sql[0]->submenu[$menu->id] = $menu;
          }else{
            $sql[0]->submenu = [];
          }
          
          return $sql[0];

        }else{

          $sql[0]->submenu[$menu->id] = $menu;

          $ramaMenu = $this->ramas($sql[0]);
          return $ramaMenu;

        }

      }

    }

    private function unirRamas($arbolMenu, $rama){

      foreach($rama as $indice => $hoja){

        if(!array_key_exists($indice, $arbolMenu)){

          $arbolMenu[$indice] = $hoja;
          break;

        }else{

          $p = $this->unirRamas($arbolMenu[$indice]->submenu,$hoja->submenu);
          $arbolMenu[$indice]->submenu = $p;

        }

      }

      return $arbolMenu;

    }

    function contraseñaActualUsuario($id_usuario){

      $clave = DB::select('SELECT u.clave
                           FROM tbl_usuario u
                           WHERE u.id = '.$id_usuario);

      if(count($clave) > 0){

        return $clave[0];

      }else{

        return array();

      }

    }

    function actualizarContraseña($id_usuario, $nuevaClave){

      $nuevaClave = DB::table('tbl_usuario')
                    ->where('id', $id_usuario)
                    ->update(['clave' => $nuevaClave]);

      if($nuevaClave){

        return array("response" => true, "message" => "Contraseña actualizada con éxito!");

      }else{

        return array("response" => false, "message" => "No se pudo actualizar la Contraseña, por favor intente nuevamente!");

      }

    }

}
