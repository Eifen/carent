<template>

  <b-navbar id="nav-menu-principal" toggleable="lg" type="light" variant="light">

    <b-navbar-brand href="/">
      <b-img src="/images/logo-carent-menu-expandido.png" fluid alt="SETA"></b-img>
    </b-navbar-brand>

    <b-icon
      @click="menuShow = !menuShow"
      @mouseleave="menuHamburguer.animation = 'fade'"
      @mouseover="menuHamburguer.animation = ''"
      :animation="menuHamburguer.animation"
      :aria-expanded="(menuShow) ? 'true' : 'false'"
      :class="(menuShow) ? 'hamburguer-menu' : 'hamburguer-menu collapsed'"
      font-scale="1.8"
      icon="list"></b-icon>

    <b-collapse id="menu-principal" is-nav v-model="menuShow">

      <b-navbar-nav id="wrapper-menu-items">
        <menuItem v-for="(item, index) in menu"
                  :descripcion="item.descripcion"
                  :id="index"
                  :hasSubmenu="(Object.keys(item.submenu).length > 0) ? true : false"
                  :key="index"
                  :submenus="item.submenu"
                  :url="item.url"></menuItem>
      </b-navbar-nav>

      <b-navbar-nav class="ml-auto" id="ul-opciones-cuenta">
        <b-nav-item-dropdown text="Mi Cuenta" right>
          <b-dropdown-item href="/cambiarClave">Cambiar Contraseña</b-dropdown-item>
          <b-dropdown-item href="/logout">Salir</b-dropdown-item>
        </b-nav-item-dropdown>
      </b-navbar-nav>

    </b-collapse>

  </b-navbar>
</template>

<style lang="less">

#nav-menu-principal{
  background-color: white;
  margin-left: -15px;
  margin-right: -15px;

  .navbar-brand{
    height: 100%;
    margin-right:1.7rem;
    position: relative;

    img{
      height: 35px;
    }

  }

  .hamburguer-menu{
    color: #091F40;
    display: none;

    @media (max-width: 991px) {
      display: inherit;
    }

    &:hover{
      cursor: pointer;
    }

  }

  .nav-link{
    color: #000000 !important;
    transition: all .3s;

    &:hover{
      color:#F6A81C !important;
      cursor:pointer;
    }

  }

  #wrapper-menu-items{

    .dropdown-submenu {
      position: relative;

      > .dropdown-menu {
        //left: 90%;
        left: 7px;
        margin-top: 0px;
        margin-left: 0px;
        top: 10;

        a{

          @media (max-width: 991px) {
            padding: 0.25rem 1.5rem;
          }

        }

      }

    }

  }// Fin #wrapper-menu-items

  #ul-opciones-cuenta{

    .dropdown-menu{

      .dropdown-item{
        transition: all .3s;

        &:hover{
          background-color:transparent;
          color:#F6A81C !important;
          cursor:pointer;
        }

        &:focus{
          background-color:transparent;
        }

      }

    }// Fin .dropdown-menu

  }// Fin #ul-opciones-cuenta

}

</style>

<script>

    import Vue from 'vue';
    import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
    window.$ = require('jquery');
    import axios from 'axios';
    import menuItem from './itemMenuPrincipal';
    var self;

    Vue.use(BootstrapVue);
    Vue.use(BootstrapVueIcons);

  export default {
      data() {
        return {
          "menu" : [],
          "menuHamburguer" : {
            "animation" : "fade"
          },
          "menuShow" : false
        };
      },
      components: {
          menuItem
      },
      beforeCreate: function(){

        self = this;

        axios.get('/menUsuario')
        .then(function (response) {

          if(response.status === 200 && Object.keys(response.data).length > 0){

            self.menu = response.data;

          }else{

            throw "error";

          }

        })
        .catch(error => {

          console.log("ERROR NO MENUS");

        });


      },
      updated: function(){

        /*$("ul.dropdown-menu [data-toggle='dropdown']").on("click", function(event) {

          event.preventDefault();
          event.stopPropagation();

          $(this).siblings().toggleClass("show");

          if (!$(this).next().hasClass('show')) {

            $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
          }

          $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
            $('.dropdown-submenu .show').removeClass("show");
          });

        });*/

      },
      methods: {}
  }

</script>
