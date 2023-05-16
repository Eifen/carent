<template>
  <div :class="scope.tableClass.search">
    <div class="input-group mb-3"
        v-for="(columnName,cursor) in columnsSearch"
        :key="cursor">
        <span class="input-group-text" id="basic-addon1">{{ columnName }} </span>
        <!-- Representa el nombre de cada campo a buscar-->
        <input type="text"
        class="form-control"
        :aria-label="columnName"
        aria-describedby="basic-addon1"
        :placeholder="'Ingrese ' + columnName"
        @input="emitInputSearch($event,columnName)"/>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    scope: Object, //Hereda la data del padre
    columnsSearch: Object, //Hereda la propiedad selectSearch del padre
  },
  data(){
    return {
        fieldsInput: {} //Objeto encargado de distribuir el valor de cada input creado dinamicamente
    }
  },
  methods:{
    /**
     * Metodo que envia la informacion de los input al padre para filtrar la Data
     * @param {Event} inputEvent InputEvent Object para almacenar el valor del input
     * @param {String} columnName captura la columna en donde se esta ingresando la data
     */
    emitInputSearch(inputEvent,columnName)
    {
        this.fieldsInput[columnName.toLowerCase()] = inputEvent.target.value.toLowerCase() //Creamos una propiedad en funcion del campo afectado
        this.$emit('search-data',this.fieldsInput)
    },
  }
};
</script>

<style>
</style>
