<template>
    <div :class="scope.tableClass.search">
        <div class="input-group mb-3" v-for="(columnName, cursor) in columnsSearch" :key="cursor">
            <span class="input-group-text" id="basic-addon1">{{ columnName }} </span>
            <!-- Representa el nombre de cada campo a buscar-->
            <input v-if="columnName != 'Estatus'" type="text" class="form-control" :aria-label="columnName"
                aria-describedby="basic-addon1" :placeholder="'Ingrese ' + columnName"
                @input="emitInputSearch($event, columnName)" />
            <!-- Campos multiples -->
            <!-- Status -->
            <Multiselect v-if="columnName == 'Estatus'" v-model="multiSelectStatus" :options="multiSelectList.status"
                placeholder="Seleccione el status" mode="single" class="form-control"
                @input="emitSelectSearch($event, columnName)"></Multiselect>
        </div>
    </div>
</template>

<script>
//Importar multiselect
import Multiselect from '@vueform/multiselect'

export default {
    props: {
        scope: Object, //Hereda la data del padre
        columnsSearch: Object, //Hereda la propiedad selectSearch del padre
        catchStatusTable: String, //Captura la propiedad statusTable del padre listingCrud
    },
    data() {
        return {
            fieldsInput: {}, //Objeto encargado de distribuir el valor de cada input creado dinamicamente
            multiSelectStatus: null, //Captura los campos seleccionados del multiselect de estatus
            multiSelectList: {
                "status": []
            }, //Almacena la informacion de los campos con multiselect
            dtoSelectStatus: [] //Objeto de transferencia que almacena la estructura de la tabla status
        }
    },
    created() {
        axios.post('/get-info-select', { 'table_target': this.catchStatusTable })
            .then(request => {
                //Asignamos la data de transferencia
                this.dtoSelectStatus = request.data.status
                //Status multiselect
                for (const key in this.dtoSelectStatus) {
                    this.multiSelectList.status.push(this.dtoSelectStatus[key].status_description)
                }
            })
            .catch(error => { console.error(error) })
    },
    methods: {
        /**
         * Metodo que envia la informacion de los input al padre para filtrar la Data
         * @param {Event} inputEvent InputEvent Object para almacenar el valor del input
         * @param {String} columnName captura la columna en donde se esta ingresando la data
         */
        emitInputSearch(inputEvent, columnName) {
            this.fieldsInput[columnName.toLowerCase()] = inputEvent.target.value.toLowerCase() //Creamos una propiedad en funcion del campo afectado
            this.$emit('search-data', this.fieldsInput)
        },
        /**
         * Metodo que envia la informacion de los select al padre para filtrar
         * @param {*} selectTarget Captura el valor del select
         * @param {*} columnName Almacena el nombre de la columna
         */
        emitSelectSearch(selectTarget, columnName) {
            //Por defecto enviamos el campo vacio.
            this.fieldsInput[columnName.toLowerCase()] = ''
            //Guardamos la informacion en el objeto de busqueda dependiendo del nombre de columna
            switch (true) {
                //Estatus de las horas cargadas
                case columnName === 'Estatus' && this.catchStatusTable === 'adminHours':
                    this.fieldsInput[columnName.toLowerCase()] = selectTarget === null ? '' : selectTarget;
                    break;
                //Estatus generales, usuarios, clientes, entre otros
                case columnName === 'Estatus' && this.catchStatusTable !== 'adminHours':
                    const statusId = this.dtoSelectStatus.filter(status => {
                        return status.status_description.includes(selectTarget)
                    });
                    //Si encuentra la Id en funcion de su descripcion, asignamos la variables
                    if (statusId.length != 0) {
                        this.fieldsInput[columnName.toLowerCase()] = statusId[0].status_id
                    }
                    break;
            }
            //Llamamos al evento personalizado
            this.$emit('search-data', this.fieldsInput)
        }
    },
    components: { Multiselect }
};
</script>

<style></style>
