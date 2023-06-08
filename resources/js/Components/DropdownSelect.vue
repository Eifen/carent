<template>
    <div class="input-info-list">
        <div class="input-info-list-options"
        v-for="(select,cursor) in arrayObjectResult"
        :key="cursor">{{ select.bussiness_name }}</div>
    </div>
</template>
<script>
export default {
    props:{
        stringToSearch: String, //Almacena el parametro a buscar
        columnToSearch: String, //Captura la columna  que queremos buscar
        arrayObjectResult: Object, //Almacena la informacion que se va a mostrar en el dropdown
        noInput: Boolean //Controla la activacion del dropdown
    },
    data(){
        return{
            dtoObjectResult: [] //Objeto de transferencia para definir busqueda en el dropdown
        }
    },
    created(){
        this.dtoObjectResult = this.objectResult.filter()
    },
    mounted(){
        console.log(this.objectResult)
    }
}
</script>