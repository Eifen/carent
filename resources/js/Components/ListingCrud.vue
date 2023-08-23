<template>
    <div :class="listClass" v-if="tableInfo.length != 0">
        <div :class="tableClass.title">{{ titleTable }}</div>
        <div :class="tableClass.create" v-if="viewCreate" @click="$emit('createbutton')">Crear {{ buttonTitle }}</div>
        <!-- Búsqueda de datos en tiempo real -->
        <pagination v-if="viewSearch" :scope="DTOData" :columns-search="selectSearch" :catch-status-table="statusTable"
            @search-data="searchData">
        </pagination>
        <!-- =====================================================================
        Paginacion
        ========================================================================   -->
        <div :class="tableClass.pagination" v-if="controlView.isGreater">
            <!-- Se mostraran unicamente si el cursor de la paginación es mayor a 0 -->
            <font-awesome class="aLink" string-icon="fa-solid fa-angles-left" v-if="controlPagination.cursor != 0"
                @click="controlPagination.cursor = 0"></font-awesome>
            <font-awesome class="aLink" string-icon="fa-solid fa-angle-left" v-if="controlPagination.cursor != 0"
                @click="controlPagination.cursor--"></font-awesome>
            <input type="text" class="form-control" aria-label="Username" aria-describedby="basic-addon1"
                :value="controlView.pagActual" @input="inputPage" />
            <!-- Se mostraran unicamente si el cursor de la paginacion es menor al máximo   -->
            <font-awesome class="aLink" string-icon="fa-solid fa-angle-right" v-if="controlPagination.cursor != maxCursor"
                @click="controlPagination.cursor++"></font-awesome>
            <font-awesome class="aLink" string-icon="fa-solid fa-angles-right" v-if="controlPagination.cursor != maxCursor"
                @click="controlPagination.cursor = maxCursor"></font-awesome>
            <span :class="tableClass.infoPag">Página {{ controlView.pagActual }} de {{ limitPage }}</span>
        </div>
        <!-- =====================================================================
        Data Table
        ========================================================================   -->
        <div class="table-responsive" :class="tableClass.content">
            <table class="table table-hover">
                <thead :class="tableClass.thead">
                    <tr>
                        <!-- Table Object -->
                        <th scope="col" align="center" valign="middle" v-for="(title, cursor) in titleObject" :key="cursor">
                            <span v-if="cursor != 'settings'">{{ title }}</span>
                            <span v-else></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Carga de los datos de entrada. Si detecta Estatus separa su contenido -->
                    <tr v-for="(cursorArray, actualIndex) in controlTable.maxLength" :key="cursorArray"
                        :class="[actualIndex === controlTable.active.index ? controlTable.active.class : null]"
                        @click="controlTable.active.index = actualIndex">
                        <!-- Recorremos el array de datos -->
                        <td align="center"
                            v-for="(columnData, cursorTable) in controlTable.data[actualIndex + controlTable.minLength]"
                            :key="cursorTable">
                            <span v-if="cursorTable == 'estatus' && columnData == 1"
                                class="badge text-bg-success">activo</span>
                            <span v-else-if="cursorTable == 'estatus' && columnData == 2"
                                class="badge text-bg-danger">inactivo</span>
                            <span v-else-if="cursorTable == 'estatus' && columnData == 3" class="badge text-bg-warning">De
                                reposo</span>
                            <span v-else-if="cursorTable == 'estatus' && columnData == 4" class="badge text-bg-warning">De
                                vacaciones</span>
                            <span v-else>{{ columnData }}</span>
                        </td>
                        <td :class="tableClass.setting"
                            v-if="controlTable.data[actualIndex + controlTable.minLength] !== undefined && 'settings' in titleObject">
                            <!-- Condicion del icono del FontAwesome -->
                            <font-awesome string-icon="fa-solid fa-gear"
                                :spin="actualIndex === controlTable.hover.index ? true : false"
                                :style="actualIndex === controlTable.hover.index ? controlTable.hover.style : null"
                                @mouseover="controlTable.hover.index = actualIndex"
                                @mouseout="controlTable.hover.index = -1" @click="showSetting(actualIndex)"></font-awesome>
                            <!-- Configuramos el modal de operaciones de la tabla -->
                            <div :class="tableClass.modal"
                                :id="actualIndex === controlTable.maxLength - 1 ? controlTable.setting.lastChild : null"
                                :style="actualIndex === controlTable.setting.index ? controlTable.setting.style : null"
                                @mouseover="controlTable.setting.index = actualIndex"
                                @mouseout="controlTable.setting.index = -1">
                                <div v-for="(setting, cursor) in titleObject.settings" :key="cursor">
                                    <!-- Separamos los select en base al objeto suministrado (Deben tener el mismo formato) -->
                                    <span class="aLink"
                                        @click="$emit('columns1target', (controlTable.data[actualIndex + controlTable.minLength].código))"
                                        v-if="cursor == 'columnS1'">{{ setting }}</span>
                                    <span class="aLink"
                                        @click="$emit('columns2target', (controlTable.data[actualIndex + controlTable.minLength].código))"
                                        v-if="cursor == 'columnS2'">{{ setting }}</span>
                                    <span class="aLink"
                                        @click="$emit('columns3target', (controlTable.data[actualIndex + controlTable.minLength].código))"
                                        v-if="cursor == 'columnS3'">{{ setting }}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div :class="listClass" v-else>
        <div :class="tableClass.create" v-if="viewCreate" @click="$emit('createbutton')">Crear {{ buttonTitle }}</div>
        <div class="badge bg-warning text-dark" :class="tableClass.title">{{ notFoundMessage }}</div>
    </div>
</template>

<script>
import FontAwesome from "../Components/FontAwesome/FontAwesome.vue";
import Pagination from "../Components/Pagination.vue";

export default {
    props: {
        titleObject: Object,
        /*Objeto que indica el titulo de cada Columna.
            Debe existir una propiedad llamada settings y si tiene más de 1 opción*/
        paginationLenght: Number, //La cantidad de páginas
        paginationLimit: Number, // El Tamaño máximo de cada página
        tableInfo: Array, //Array Objeto que almacena el resultado de la tabla
        titleTable: String, //Titulo de la tabla
        statusTable: String, //Indica que tipo de condicion usara para los status, si se quiere los status generales no llamar esta propiedad
        //para mas informacion sobre su valor revisar getAllStatus del ConfigModel.php
        buttonTitle: String, //Titulo del boton de crear
        notFoundMessage: String, //Mensaje en caso que no encuentre valores iniciales en la tabla
        selectSearch: Object, //Objeto que almaneca los select de búsqueda
        viewCreate: Boolean, // Boolean que se encarga de definir si ver el boton de crear
        viewSearch: Boolean, // Boolean que se encarga de definir si ver el sistema de búsqueda
    },
    data() {
        return {
            listClass: "list-container",
            maxCursor: this.paginationLenght - 1,
            limitPage: this.paginationLenght,
            //Table Object
            tableClass: {
                content: "",
                title: "",
                create: "",
                search: "",
                thead: "",
                setting: "",
                modal: "",
                pagination: "",
                infoPag: "",
            },
            controlTable: {
                data: this.tableInfo, //Inicialmente tendrá la informacion de toda la tabla objetivo
                dataLength: this.tableInfo.length, //Tamaño máximo del array
                minLength: 0, //Valor minimo del v-for en el table. Su valor inicial se asigna en el created
                maxLength: 0, //Cuanto recorre en cada página. Su valor inicial se asigna en el created
                active: { index: -1, class: "table-active" }, //Controla la selección
                hover: { index: -1, style: "color:#3490dc" }, //Controla estilo del hover
                setting: {
                    index: -1,
                    style: "background: #e9ecef;transform:scale(1)",
                    lastChild: "lastModal",
                }, //Controla el estilo del Setting
            }, //Controla los estados de la tabla
            listInputsDTO: {}, //Objeto que almacena los campos por los que buscar de forma temporal en sus propiedades. Hereda de fieldsInput del componente pagination.vue
            controlPagination: { init: 0, cursor: 0, regex: new RegExp("^([0-9]{1})$") }, //control de la lógica de la paginación
            controlView: { pagActual: 0, isGreater: false } //Controla la vista de la paginación
        };
    },
    created() {
        //Asignamos la distribución  de clases
        this.tableClass = {
            content: this.listClass + "-table",
            thead: this.listClass + "-table-thead",
            setting: this.listClass + "-table-setting-container",
            modal: this.listClass + "-table-setting-container-modal",
            title: this.listClass + "-title",
            create: this.listClass + "-create",
            search: this.listClass + "-search",
            pagination: this.listClass + "-pagination-container",
            infoPag: this.listClass + "-pagination-container-info",
        };

        //Asignamos el minimo y el máximo inicial
        this.controlTable.minLength = this.controlPagination.init * this.paginationLimit;
        this.controlTable.maxLength = this.controlTable.minLength + this.paginationLimit;

        //Asignamos el menu de paginación
        this.paginationLenght <= 1
            ? (this.controlView.isGreater = false)
            : (this.controlView.isGreater = true);

        //Establecemos la pagina actual a la vista
        this.controlView.pagActual = this.controlPagination.cursor + 1;
    },
    methods: {
        /**
         * Metodo que muestra el modal del Settings
         * @param {*} targetIndex Captura el indice actual del hover
         */
        showSetting(targetIndex) {
            if (this.controlTable.setting.index === targetIndex) {
                this.controlTable.setting.index = -1;
                return;
            }
            //Si no son iguales, lo asignamos
            this.controlTable.setting.index = targetIndex;
        },
        /**
         * Metodo que filtra la información en funcion de los ingresado en los campos
         * @param {Object} dataInput Objeto que almacena la información de los input y su correspondiente columna a buscar
         */
        searchData(dataInput) {
            //Almacenamos temporalmente la lista
            this.listInputsDTO = dataInput
            //Filtramos el array en función al select, lo cual actualizará automaticamente
            let dataListDTO = this.tableInfo
            for (const columnToSearch in dataInput) {
                //Valores pertenecientes a fechas
                if (columnToSearch === "fecha_desde" || columnToSearch === "fecha_hasta") {
                    //Por defecto la fecha inicial es 2020-07-01 y la fecha final es la actual
                    let startDate = dataInput["fecha_desde"]
                    let endDate = dataInput["fecha_hasta"]
                    dataListDTO = dataListDTO.filter(data => {
                        let dateToSearch = new Date(data["fecha"]);
                        return dateToSearch.getTime() >= startDate.getTime() && dateToSearch.getTime() <= endDate.getTime();
                    })
                }
                //Valores indistintos a fecha
                if (columnToSearch !== "fecha_desde" && columnToSearch !== "fecha_hasta") {
                    dataListDTO = dataListDTO.filter(data => {
                        return data[columnToSearch].toString().toLowerCase().includes(dataInput[columnToSearch].toString().toLowerCase())
                    })
                }
            }

            //Espacio de transferencia de datos
            this.controlTable.data = dataListDTO;
            this.controlTable.dataLength = dataListDTO.length;
            //Si el length del nuevo array es menor o igual al limite por paginacion, asignamos el nuevo length
            if (dataListDTO.length <= this.paginationLimit) {
                this.controlTable.maxLength = this.controlTable.minLength + dataListDTO.length;
            } else { this.controlTable.maxLength = this.controlTable.minLength + this.paginationLimit }
            //Caso contrario, volvemos a su valor inicial

            //Estado de la paginación
            this.controlTable.dataLength <= this.paginationLimit
                ? (this.controlView.isGreater = false)
                : (this.controlView.isGreater = true);

            //Limite de la paginación
            this.limitPage = Math.ceil((this.controlTable.dataLength / this.paginationLimit))
            this.maxCursor = this.limitPage - 1;
            this.controlPagination.cursor = 0;
        },
        /**
         * Metodo que cambia de página a través del input
         * @param {*} inputEvent InputEvent Object para almacenar el valor del input
         */
        inputPage(inputEvent) {
            //Veríficamos que sea un número y que este comprendido en el intervalo de la página
            if ((!this.controlPagination.regex.test(inputEvent.data) && inputEvent.data != null)
                || parseInt(inputEvent.data) > this.limitPage
                || parseInt(inputEvent.data) == 0 ||
                inputEvent.target.value.length > 1) {
                inputEvent.target.value = this.controlView.pagActual;
                return;
            }
            if (inputEvent.data == null) return;
            //Si pasa todas las validaciones, actualizamos los cursores
            const newPage = parseInt(inputEvent.target.value) - 1;
            this.controlPagination.cursor = newPage;
        },
    },
    watch: {
        controlPagination: {
            deep: true,
            handler(controlChange) {
                this.controlTable.minLength = controlChange.cursor * this.paginationLimit;
                //Cambiamos el estado del INPUT de la paginación
                this.controlView.pagActual = controlChange.cursor + 1

                //Validaciones en función al estado actual del cursor. Sea valor máximo o valor minimo
                if (controlChange.cursor == this.maxCursor)
                    this.controlTable.maxLength = this.controlTable.dataLength - this.controlTable.minLength;

                if (controlChange.cursor != this.maxCursor)
                    this.controlTable.maxLength = this.paginationLimit;
            },
        },
        tableInfo() {
            //Volvemos a llamar al searchData para actualizar el array con el DTO de inputs, luego de actualizar la lista
            this.controlTable.data = this.tableInfo
            this.searchData(this.listInputsDTO)
        }
    },
    computed: { DTOData() { return this.$data } }, //Enviamos el objeto data como parametro
    components: { FontAwesome, Pagination },
};
</script>
