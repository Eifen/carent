<template>
    <span>
        <font-awesome
            string-icon="fa-solid fa-plus"
            class="aLink"
            data-bs-toggle="modal"
            :data-bs-target="'#' + idModal"
        ></font-awesome>
    </span>
    <!-- Modal -->
    <div class="modal" tabindex="-1" :id="idModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ modalTitle }}</h5>
                    <button
                        type="button"
                        class="btn btn-info"
                        data-bs-dismiss="modal"
                    >
                        X
                    </button>
                </div>
                <div class="modal-body" style="margin: -60px 0px 0px 0px">
                    <listing-crud
                        :title-table="tableModalTitle"
                        :title-object="modalColumns"
                        :pagination-lenght="paginationLenght"
                        :pagination-limit="paginationLimit"
                        :table-info="modalInfoDTO"
                    ></listing-crud>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon13">
                            {{ tableModalInsert }}
                        </span>
                        <input
                            type="text"
                            class="form-control"
                            id="Value"
                            aria-describedby="basic-addon13"
                        />
                        <span class="input-group-text" id="basic-addon14">
                            <font-awesome
                                string-icon="fa-solid fa-plus"
                                class="aLink"
                            ></font-awesome>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";
import ListingCrud from "@/Components/ListingCrud.vue";

export default {
    props: {
        modalTitle: String, //Captura el titulo del modal,
        tableModalTitle: String, //Titulo de la tabla
        modalData: Array, //Captura el array a controlar en el body. Es decir la informacion del CRUD
        idModal: String, //String que determina el nombre del modal
        tableModalInsert: String, //Almacena el nombre del input
    },
    data() {
        return {
            controlModal: null, //Controla el acceso al modal
            modalInfoDTO: [], //Objeto de transferencia para el crud de las horas
            paginationLenght: 0, //Cantidad de paginas
            paginationLimit: 5, //Length de informacion por pagina
            modalColumns: {
                column1: "#",
                column2: "Valor",
                column3: "Fecha",
                column4: "Estatus",
                settings: { columnS1: "Eliminar" },
            },
        };
    },
    created() {
        this.modalInfoDTO = this.modalData.map((object) => {
            let copyObject = { ...object };

            //Verificamos si existen ciertas propiedades
            if ("project_id" in copyObject) {
                delete copyObject.project_id;
            }

            if ("department_assigned_id" in copyObject) {
                delete copyObject.department_assigned_id;
            }

            if ("department_id" in copyObject) {
                delete copyObject.department_id;
            }

            if ("manager_id" in copyObject) {
                delete copyObject.manager_id;
            }

            if ("hours_assigned" in copyObject) {
                delete copyObject.hours_assigned;
            }

            if ("status_id" in copyObject) {
                //Cambiamos el valor de la propiedad antes de borrarla
                copyObject["estatus"] = copyObject.status_id;
                delete copyObject.status_id;
            }

            //Retornamos el objeto resultante
            return copyObject;
        });
    },
    mounted() {
        this.controlModal = document.getElementById(this.idModal);
        //Incializamos el listener
        this.controlModal.addEventListener("shown.bs.modal", this.openModal);
        console.log(this.modalInfoDTO);
    },
    beforeUnmount() {
        //Removemos el listener
        window.removeEventListener("show.bs.modal", this.openModal);
    },
    methods: {
        /**
         * Metodo que se encarga de controlar el modal.
         * No recibe parametros
         */
        openModal() {
            this.controlModal.focus();
        },
    },
    watch: {
        modalInfoDTO(newInfo) {
            //Una vez se asigna la informacion, se procede a crear la paginacion
            this.paginationLenght = newInfo.length / this.paginationLimit;
        },
    },
    components: { FontAwesome, ListingCrud },
};
</script>
