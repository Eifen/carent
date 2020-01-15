<template>
  <nav id="menu-principal" class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand">
      <img src="/images/logo-carent-menu-expandido.png">
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul id="wrapper-menu-items" class="navbar-nav mr-auto" v-html="menus"></ul>
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Mi Cuenta
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="/cambiarClave">Cambiar Contraseña</a>
            <a class="dropdown-item" href="/logout">Salir</a>
          </div>
        </li>
      </ul>
    </div>
  </nav>
</template>

<style>

#menu-principal{
  background-color: white;
  margin-left: -15px;
  margin-right: -15px;
}

#menu-principal .navbar-brand{
  height: 100%;
  position: relative;
}

#menu-principal .navbar-brand img{
  height: 35px;
  width: auto;
}

#menu-principal .nav-link{
  color: #000000 !important;
  transition: all .3s;
}

#menu-principal .nav-link:hover{
  color:#F6A81C !important;
  cursor:pointer;
}

#menu-principal #wrapper-menu-items .dropdown-submenu {
  position: relative;
}

#menu-principal #wrapper-menu-items .dropdown-submenu>.dropdown-menu {
  left: 90%;
  margin-top: 0px;
  margin-left: 0px;
  top: 10;
}

#menu-principal #wrapper-menu-items > .dropdown-submenu > .dropdown-menu{
  left: 7px;
}

</style>

<script>

  window.axios = require('axios');
  var self;

  export default {
      data() {
        return {"menus": null};
      },
      beforeCreate: function(){

        self = this;

        axios.get('/menUsuario')
        .then(function (response) {

          if(response.status === 200 && Object.keys(response.data).length > 0){

            self.menus = self.armarMenu(response.data);

          }else{

            throw "error";

          }

        })
        .catch(error => {

          console.log("ERROR NO MENUS SAPEEE 2");

        });


      },
      mounted: function() {
      },
      updated: function(){

        $("ul.dropdown-menu [data-toggle='dropdown']").on("click", function(event) {

          event.preventDefault();
          event.stopPropagation();

          $(this).siblings().toggleClass("show");

          if (!$(this).next().hasClass('show')) {

            $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
          }

          $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
            $('.dropdown-submenu .show').removeClass("show");
          });

        });

      },
      methods: {
        armarMenu: function(menus){

          var htmlMenu = "";

          Object.keys(menus).forEach(function(indiceObjecto, indice) {

            var menu = menus[indiceObjecto];

            if(Object.keys(menu.submenu).length > 0){

              let submenu = self.armarMenu(menu.submenu);

              htmlMenu += `<li class="nav-item dropdown dropdown-submenu">
                             <a class="nav-link dropdown-toggle" id="navbarDropdown-${indiceObjecto}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                               ${menu.descripcion}
                             </a>
                             <ul class="dropdown-menu" aria-labelledby="navbarDropdown-${indiceObjecto}">
                                ${submenu}
                             </ul>
                           </li>`;


            }else{
              htmlMenu += `<li class="nav-item">
                             <a class="nav-link" id="navbarDropdown-${indiceObjecto}" aria-haspopup="true" href="${menu.url}">
                               ${menu.descripcion}
                             </a>
                           </li>`;
            }

          });

          return htmlMenu;

        }

      }
  }
</script>
