<style scoped lang="less">

.alert{
  margin-bottom: 0px;
  width: 100%;

  .progress{
    margin-top: 10px;
  }

}

</style>

<template>

  <b-alert
    @dismissed="ocultarCountDown=0"
    @dismiss-count-down="countDownChanged"
    :dismissible="iconoCerrar"
    :show="ocultarCountDown"
    :variant="variante"
    class="text-center"
    fade
    v-if="contador">
      <div v-html="mensaje"></div>
      <b-progress
        :max="ocultarSeg"
        :value="ocultarCountDown"
        :variant="variante"
        height="4px"></b-progress>
  </b-alert>

  <b-alert
    :dismissible="iconoCerrar"
    :show="mostrar"
    :variant="variante"
    class="text-center"
    fade
    v-else>
    <div v-html="mensaje"></div>
  </b-alert>

</template>

<script>

  export default {
      props:{
        contador: Boolean,
        iconoCerrar: Boolean,
        mensaje: String,
        mostrar: Boolean,
        ocultarSeg: Number,
        variante: String
      },
      data: function() {
        return {
          ocultarCountDown: 0
        }
      },
      watch: {
      	ocultarSeg: function(nuevoValor, viejoValor) {
          this.ocultarCountDown = nuevoValor;
        }
      },
      beforeCreate: function(){},
      mounted: function(){},
      beforeUpdate:function(){},
      updated: function(){},
      methods: {

        countDownChanged: function(ocultarCountDown) {
          this.ocultarCountDown = ocultarCountDown;
        }

      }
  }

</script>
