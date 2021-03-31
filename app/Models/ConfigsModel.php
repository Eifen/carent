<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ConfigsModel extends Model
{

    function encryptConfig(){

      $sql = DB::select('SELECT * FROM vw_config_encryption LIMIT 1');
      return ["key" => $sql[0]->key, "iv" => $sql[0]->iv];

    }// Fin encryptConfig

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

}
