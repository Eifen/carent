/*
  Se armo sin sintaxsis de bootstrap-vue porque a la fecha no soporta sub-menu,
  hay que esperar a que actualicen
*/

<template>

  <li class="nav-item dropdown dropdown-submenu" v-if="hasSubmenu">
    <a class="nav-link dropdown-toggle" :id="'navbarDropdown-'+id" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      {{ descripcion }}
    </a>
    <ul class="dropdown-menu" :aria-labelledby="'navbarDropdown-'+id">
      <menuItem v-for="(itemSubmenu, index) in submenus"
                :descripcion="itemSubmenu.descripcion"
                :hasSubmenu="(Object.keys(itemSubmenu.submenu).length > 0) ? true : false"
                :key="index"
                :submenus="itemSubmenu.submenu"
                :url="itemSubmenu.url"></menuItem>
    </ul>
  </li>

  <li class="nav-item" v-else>
     <a class="nav-link" id="'navbarDropdown-'+id" aria-haspopup="true" :href="url">
       {{ descripcion }}
     </a>
  </li>

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
    },
    beforeCreate: function(){},
    created: function(){},
    mounted: function() {},
    updated: function(){},
    methods: {}
  }

</script>
