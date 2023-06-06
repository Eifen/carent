/*
  Se armo sin sintaxsis de bootstrap-vue porque a la fecha no soporta sub-menu,
  hay que esperar a que actualicen
*/

<template>

  <b-nav-item-dropdown class="dropdown-submenu" :text="descripcion" right v-if="hasSubmenu">
    <menuItem v-for="(itemSubmenu, index) in submenus"
              :descripcion="itemSubmenu.descripcion"
              :hasSubmenu="(Object.keys(itemSubmenu.submenu).length > 0) ? true : false"
              :key="index"
              :submenus="itemSubmenu.submenu"
              :url="itemSubmenu.url"></menuItem>
  </b-nav-item-dropdown>

  <b-nav-item :href="url" id="'navbarDropdown-'+id" v-else> {{ descripcion }} </b-nav-item>

</template>

<script>

  export default {
    name: "menuItem",
    props: {
      descripcion: String,
      id:  String,
      hasSubmenu: Boolean,
      submenus: [Object,Array],
      url: String
    }
  }

</script>
