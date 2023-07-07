<template>
    <span class="input-info-loading" v-if="controlList && arrayObjectResult.length == 0">
        <font-awesome string-icon="fa-solid fa-spinner" is-spin></font-awesome>
    </span>
    <div class="input-info-list" v-if="controlList && arrayObjectResult.length != 0 && dtoObjectResult.length != 0">
        <div class="input-info-list-options"
        v-if="dtoObjectResult.length != 0"
        v-for="(select,cursor) in dtoObjectResult"
        :key="cursor"
        @click="autoCompleteInput(select[columnToSearch])">{{ select[columnToSearch] }}</div>
    </div>
</template>
<script>
import FontAwesome from '@/Components/FontAwesome/FontAwesome.vue'
export default {
    props:{
        stringToSearch: String, //Almacena el parametro a buscar
        columnToSearch: String, //Captura la columna  que queremos buscar
        arrayObjectResult: Array, //Almacena la informacion que se va a mostrar en el dropdown
        controlList: Boolean, //Muestra u oculta la informacion de la lista
    },
    data(){
        return{
            postReady: false, //Controla la carga de la lista para evitar intervalos iniciales
            dtoObjectResult: [] //Objeto de transferencia para definir busqueda en el dropdown
        }
    },
    emits: ["complete-input"],
    methods: {
        /**
         * Metodo de autocompletado del input, activara un evento emit en el componente padre
         * @param {string} stringTarget Almacena el valor seleccionado
         */
        autoCompleteInput(stringTarget){
            this.$emit('complete-input',stringTarget)
        }
    },
    watch: {
        /**
         * Watcher que espera la sincronizacion con el parametro de la lista
         * @param {*} actualList Captura la informacion actual del array de objetos
         */
        arrayObjectResult(actualList){
            this.dtoObjectResult = actualList
            this.postReady = true
        },
        /**
         * Watcher que detecta los cambios del input targeteado
         * @param {String} stringSearch 
         */
        stringToSearch(stringSearch){
            this.dtoObjectResult = this.arrayObjectResult.filter(column => {
                return column[this.columnToSearch.toLowerCase()].toString().toLowerCase().includes(stringSearch.toLowerCase())
            })
        }
    },
    components: {FontAwesome}
}
</script>