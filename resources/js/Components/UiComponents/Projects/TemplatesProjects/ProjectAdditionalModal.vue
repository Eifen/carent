<template>
    <span>
        <font-awesome string-icon="fa-solid fa-plus" class="aLink" @click="openModal()"></font-awesome>
    </span>
    <!-- Modal -->
    <div class="modal" tabindex="-1" :id="nameModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ modalTitle }}</h5>
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">
                        X
                    </button>
                </div>
                <div class="modal-body" style="margin: -60px 0px 0px 0px">
                    <listing-crud :title-table="tableModalTitle" :title-object="modalColumns"
                        :pagination-lenght="paginationLenght" :pagination-limit="paginationLimit" :table-info="listInfo"
                        :key="componentReload" @columns1target="crudAdditional($event, 'delete')"
                        @columns2target="crudAdditional($event, 'update')" :not-found-message="errorMessage"></listing-crud>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon13">
                            {{ tableModalInsert }}
                        </span>
                        <input type="text" class="form-control" id="Value" v-model="scopeModal.additionalInput"
                            aria-describedby="basic-addon13" />
                        <span class="input-group-text" id="basic-addon14">
                            <font-awesome string-icon="fa-solid fa-plus" class="aLink"
                                @click="updateModal()"></font-awesome>
                        </span>
                    </div>
                    <button type="button" class="buttonCRUD" style="margin-top: 10px" data-bs-dismiss="modal"
                        @click="$emit('prepare-save', this.scopeModal.listInfoToUpdate)">
                        Guardar cambios
                    </button>
                    <!-- Al guardar cambios llama al padre para que actualice la informacion del array usado en el modal -->
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";
import ListingCrud from "@/Components/ListingCrud.vue";
import * as bootstrap from 'bootstrap';

export default {
    props: {
        modalTitle: String, //Captura el titulo del modal,
        tableModalTitle: String, //Titulo de la tabla
        nameModal: String, //String que determina el nombre del modal
        tableModalInsert: String, //Almacena el nombre del input
        lastIdTable: Number, //Almacena el ultimo valor de la tabla a mostrar en el modal
        scopeModal: Object, //Hereda el data del componente principal
        modalColumns: Object, //Define el objeto para el valor de las columnas
        listInfo: Array, //Captura la lista informativa para el componente listing crud
        errorMessage: String, //String en caso de que no existan valores en las tablas
    },
    emits: ["save-changes", "asign-list", "update-modal", "prepare-save"],
    data() {
        return {
            componentReload: 0, //Se encarga de refrescar el componente cuando sea necesario
            controlModal: null, //Controla el acceso al modal
            lastIdDTO: 0, //Objeto de transferencia que captura el ultimo ID de la tabla objetivo
            paginationLenght: 0, //Cantidad de paginas
            paginationLimit: 5, //Length de informacion por pagina
        };
    },
    created() {
        this.lastIdDTO = this.lastIdTable;
    },
    mounted() {
        this.controlModal = new bootstrap.Modal(document.getElementById(this.nameModal), { keyboard: false, backdrop: "static" });
    },
    methods: {
        /**
         * Metodo que se encarga de controlar el modal.
         * No recibe parametros
         */
        openModal() {
            this.controlModal.show();
            this.$emit('asign-list');
        },
        /**
         * Metodo que activa o inactiva una fila
         * @param {Number} paramsCatch Captura el codigo (o id) de la fila
         * @param {String} operationUD Almacena el comando si "update" para activar o "delete" para desactivar
         */
        crudAdditional(paramsCatch, operationUD) {
            //Capturamos el indice
            const getCodeIndex = this.listInfo
                .map((object) => object.codigo)
                .indexOf(paramsCatch);
            //Cambiamos el estado
            switch (operationUD.toLowerCase()) {
                case 'update':
                    this.listInfo[getCodeIndex].estatus = 1;
                    break;

                case 'delete':
                    this.listInfo[getCodeIndex].estatus = 2;
                    break;
            }
        },
        /**
         * Metodo que se encarga de agregar una nueva fila al array y recargar el componente de la lista
         */
        updateModal() {
            try {
                this.lastIdDTO++; //Incrementamos el ID
                //Validamos que el valor no sea 0 o vacio
                if (this.scopeModal.additionalInput == 0 || this.scopeModal.additionalInput.length == 0) throw "El valor no puede ser 0 o vacio";
                // //Si pasa la validación creamos un nuevo array
                this.$emit('update-modal', this.lastIdDTO)
                //Actualizamos el limite
                this.calculatePagination(this.listInfo);
            } catch (error) {
                alert(error);
            }
        },
        /**
         * Metodo que se encarga de representar la cantidad de paginas para el componente listing-crud
         * @param {*} arrayToCalculate Array a comparar para calcular su paginacion
         */
        calculatePagination(arrayToCalculate) {
            //Math.ceil redondea al numero mas cercano
            this.paginationLenght = Math.ceil(
                arrayToCalculate.length / this.paginationLimit
            );
            //Recargamos el componente
            this.componentReload++;
        },
    },
    watch: {
        listInfo(newInfo) {
            //Una vez se asigna la informacion, se procede a crear la paginacion
            this.calculatePagination(newInfo);
        },
    },
    components: { FontAwesome, ListingCrud },
};
</script>
