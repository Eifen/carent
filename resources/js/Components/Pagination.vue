<template>
    <div :class="scope.tableClass.search">
        <div class="input-group mb-3" v-for="(columnName, cursor) in columnsSearch" :key="cursor">
            <span v-if="columnName != 'Mes'" class="input-group-text" id="basic-addon1">{{ columnName }} </span>
            <!-- Representa el nombre de cada campo a buscar-->
            <input v-if="columnTarget(columnName)" type="text" class="form-control" :aria-label="columnName"
                aria-describedby="basic-addon1" :placeholder="'Ingrese ' + columnName"
                @input="emitInputSearch($event, columnName)" />
            <!-- Fechas  -->
            <input v-if="columnName == 'Fecha desde'" type="text" class="form-control" placeholder="Ejemplo: 1990-02-18"
                id="birthday" aria-describedby="basic-addon1" v-model="inputDateStart" disabled />
            <span v-if="columnName == 'Fecha desde'" class="input-group-text" id="basic-addon2">
                <calendar @to-input="emitDateSearch($event, 'start')"></calendar>
            </span>
            <input v-if="columnName == 'Fecha hasta'" type="text" class="form-control" placeholder="Ejemplo: 1990-02-18"
                id="birthday" aria-describedby="basic-addon1" v-model="inputDateEnd" disabled />
            <span v-if="columnName == 'Fecha hasta'" class="input-group-text" id="basic-addon2">
                <calendar @to-input="emitDateSearch($event, 'end')"></calendar>
            </span>
            <!-- Campos multiples -->
            <!-- Status -->
            <Multiselect v-if="columnName == 'Estatus'" v-model="multiSelectStatus" :options="multiSelectList.status"
                placeholder="Seleccione el status" mode="single" class="form-control"
                @input="emitSelectSearch($event, columnName)"></Multiselect>
            <!-- Mes -->
            <span v-if="columnName == 'Mes'" class="input-group-text" id="basic-addon1">Año</span>
            <select v-if="columnName == 'Mes'" class="form-select form-control" v-model="inputYearSelect"
                title="YearSelect">
                <option v-for="(year, cursor) in listYear" :key="cursor" :value="cursor" :selected="cursor === 0"
                    :disabled="cursor === 0">
                    <span>{{ year }}</span>
                </option>
            </select>
        </div>
        <div v-if="inputYearSelect != 0" class="input-group mb-3">
            <span class="input-group-text" id="basic-addon1">Mes</span>
            <select class="form-select form-control" v-model="inputMonthSelect" title="YearSelect">
                <option v-for="(month, cursor) in listMonth" :key="cursor" :value="cursor" :selected="cursor === 0"
                    :disabled="cursor === 0">
                    <span>{{ month }}</span>
                </option>
            </select>
        </div>
    </div>
</template>

<script>
//Importar multiselect
import Multiselect from '@vueform/multiselect'
import Calendar from "@/Components/Calendar.vue";


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
            monthReferences: [
                "Enero",
                "Febrero",
                "Marzo",
                "Abril",
                "Mayo",
                "Junio",
                "Julio",
                "Agosto",
                "Septiembre",
                "Octubre",
                "Noviembre",
                "Diciembre",
            ], //Array de meses
            multiSelectList: {
                "status": []
            }, //Almacena la informacion de los campos con multiselect
            inputDateStart: '', //Almacena la fecha desde
            inputDateEnd: '', //Almacena la fecha hasta
            inputYearSelect: 0, //Almacena el año seleccionado
            inputMonthSelect: 0, //Almacena el mes seleccionado
            dtoSelectStatus: [], //Objeto de transferencia que almacena la estructura de la tabla status
            listMonth: [], //Formato de meses
            listYear: ["Selecciona un año"], //Lista de years
        }
    },
    created() {
        //Detectamos si el filtro es por meses
        for (const key in this.columnsSearch) {
            if (this.columnsSearch[key] == "Mes") {
                //Cargamos los array de anos y meses
                const nowDate = new Date();
                for (let year = 2020; year <= nowDate.getFullYear(); year++) {
                    this.listYear.push(year);
                }
                //Obtenemos el indice del año actual
                const getYearIndex = this.listYear.indexOf(nowDate.getFullYear());
                this.inputYearSelect = getYearIndex;
                this.inputMonthSelect = nowDate.getMonth() + 1
            }
        }
        //Obtenemos la informacion de status
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
        /** Revisa si el buscador esta seleccionado en alguno particular o el general */
        columnTarget(columnName) {
            switch (true) {
                case columnName != 'Estatus'
                    && columnName != 'Fecha desde'
                    && columnName != 'Fecha hasta'
                    && columnName != 'Mes':
                    return true;
                default:
                    return false;
            }
        },
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
        },
        emitDateSearch(dateTarget, dateType) {
            switch (dateType) {
                case 'start':
                    this.inputDateStart = `${dateTarget.year}-${dateTarget.month}-${dateTarget.day}`
                    break;
                case 'end':
                    this.inputDateEnd = `${dateTarget.year}-${dateTarget.month}-${dateTarget.day}`
                    break;
            }
        }
    },
    watch: {
        //Detecta los cambios en el input
        inputDateStart(newDate, oldDate) {
            const starDate = this.inputDateStart.length != 0 ? new Date(newDate) : new Date('2020-03-01');
            const endDate = this.inputDateEnd.length != 0 ? new Date(this.inputDateEnd) : new Date()
            //Hacemos el emit unicamente si el valor es menor que la fecha hasta, o la fecha hasta esta vacia
            if (starDate.getTime() <= endDate.getTime()) {
                this.fieldsInput["fecha_desde"] = starDate;
                this.fieldsInput["fecha_hasta"] = endDate;
                //Llamamos al evento personalizado
                this.$emit('search-data', this.fieldsInput);
            } else {
                this.inputDateStart = oldDate
            }
        },
        inputDateEnd(newDate, oldDate) {
            const endDate = this.inputDateEnd.length != 0 ? new Date(newDate) : new Date();
            const starDate = this.inputDateStart.length != 0 ? new Date(this.inputDateStart) : new Date('2020-03-01')
            //Hacemos el emit unicamente si el valor es mayor que la fecha desde, o la fecha desde esta vacia
            if (starDate.getTime() <= endDate.getTime()) {
                this.fieldsInput["fecha_hasta"] = endDate;
                this.fieldsInput["fecha_desde"] = starDate;
                //Llamamos al evento personalizado
                this.$emit('search-data', this.fieldsInput);
            } else {
                this.inputDateEnd = oldDate
            }
        },
        //Detecta seleccion de año
        inputYearSelect(newYear) {
            this.listMonth = ["Seleccione un mes"]
            //Cargamos los meses
            const dateNow = new Date()
            //Limpiamos el mes por defecto y cargamos el intervalo final
            if (this.listYear[newYear] != dateNow.getFullYear()) this.inputMonthSelect = 0;
            const endInterval = this.listYear[newYear] == dateNow.getFullYear() ? dateNow.getMonth() : this.monthReferences.length
            //For de meses
            for (let month = 0; month <= endInterval; month++) {
                this.listMonth.push(this.monthReferences[month])
            }
        },
        //Detecta seleccion de mes
        inputMonthSelect(newMonth) {
            const stringMonth = newMonth.toString()
            const prepareDate = `${this.listYear[this.inputYearSelect]}-${stringMonth.padStart(2, "0")}`
            //Formateamos el start date y el end date
            console.log(prepareDate)
            this.fieldsInput["mes"] = prepareDate;
            //Actualizamos las horas estimadas
            axios.post('/reports/get-hours-estimated', { date: prepareDate })
                .then(request => {
                    this.$emit('update-estimated', request.data)
                })
                .catch(error => { console.error(error) })
            //Llamaos al evento personalizado
            this.$emit('search-data', this.fieldsInput);
        }
    },
    components: { Multiselect, Calendar }
};
</script>

<style></style>
