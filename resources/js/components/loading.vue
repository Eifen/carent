<style scoped lang="less">

/* Aniamción preloader */
@keyframes loading {
  0% {opacity: 1;}
  25% {opacity: 0;}
  50% {opacity: 1;}
  75% {opacity: 0;}
  100% {opacity: 1;}
}

#wrapper-loading{
  background-color: #E9ECEF;
  height: 100vh;
  overflow-x: hidden;
  position:absolute;
  width: 100%;
  z-index: 2;

  .wrapper-images{

    img{
      height: 70px;
      width: auto;

      &.efecto{
        animation: loading 3s infinite;
        left: 0;
        margin-left: auto;
        margin-right: auto;
        position: absolute;
        right: 0;
      }

    }// Fin img

  }// .wrapper-images

}// #wrapper-loading

</style>

<template>
  <b-row align-h="center" align-v="center" id="wrapper-loading" v-if="loading">
    <b-col cols="12" class="text-center wrapper-images">
      <img src="/images/loading-1.png" class="efecto">
      <img src="/images/loading-2.png">
    </b-col>
  </b-row>
</template>

<script>

  export default {
      props:{
        loading: Boolean
      },
      beforeCreate: function(){},
      mounted: function(){},
      updated: function(){},
      methods: {}
  }

</script>
