<template>
    <fieldset :class="scope.formClass.fieldset">
        <!-- Monto -->
        <div class="mb-3" v-if="scope.inputCurrenciesSelect != 0">
            <label for="Value">Monto
                <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon7">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input type="text" class="form-control" id="Value" aria-describedby="basic-addon7"
                    v-model="scope.inputValue" />
                <span class="input-group-text" id="basic-addon10" v-if="isEdit">
                    <project-additional-modal modal-title="Montos Adicionales" name-modal="additionalValues"
                        table-modal-title="Lista de montos" table-modal-insert="Agregar monto adicional"
                        :last-id-table="scope.lastValueId" :scope-modal="scope" :modal-columns="modalColumns"
                        :list-info="scope.listInfoToUpdate" @asign-list="asignValueList(scope.projectId)"
                        error-message="No posee montos adicionales" @update-modal="updateValueModal"
                        @prepare-save="prepareInfoTransfer"></project-additional-modal>
                </span>
            </div>
            <!-- Mensajes de error en Nombre-->
            <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.valueError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.valueError }}
            </div>
        </div>
        <!-- Divisiones -->
        <div class="mb-3">
            <label for="Departments">Divisiones
                <span :class="scope.formClass.requiredField">*</span></label>
            <!-- Departamentos -->
            <div class="input-group">
                <Multiselect v-model="scope.inputDepartments" mode="tags" :close-on-select="true" :searchable="true"
                    placeholder="Seleccione una o varias divisiones" openDirection="top"
                    :options="scope.dataSelect.departments"></Multiselect>
            </div>
        </div>
        <!-- Horas Totales -->
        <div class="mb-3" v-if="scope.inputDepartments != 0">
            <label for="HoursAssigned">Horas Totales
                <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon9">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input type="text" class="form-control" id="HoursAssigned" aria-describedby="basic-addon9"
                    v-model="scope.inputHoursAssigned" :disabled="true" />
            </div>
            <!-- Mensajes de error en Nombre-->
            <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.hoursAssignedError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.hoursAssignedError }}
            </div>
        </div>
        <!-- Tasa promedio -->
        <div class="mb-3" v-if="scope.inputValue != 0 && scope.inputHoursAssigned != 0">
            <label for="AverageRate">Tasa Promedio
                <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon14">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input type="text" class="form-control" id="AverageRate" aria-describedby="basic-addon14"
                    v-model="scope.inputAverageRate" :disabled="true" />
            </div>
            <!-- Mensajes de error en Nombre-->
            <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.averageRateError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.averageRateError }}
            </div>
        </div>
        <!-- Horas adicionales -->
        <div class="mb-3" v-if="scope.dataSelect.additionalHours.length > 0">
            <label for="HoursAdditional">Horas Adicionales</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon12">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input type="text" class="form-control" id="HoursAdditional" aria-describedby="basic-addon12"
                    v-model="scope.inputAdditionalHours" :disabled="true" />
            </div>
        </div>
        <!-- Montos adicionales -->
        <div class="mb-3" v-if="scope.dataSelect.additionalValues.length > 0">
            <label for="ValuesAdditional">Montos adicionales</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon13">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input type="text" class="form-control" id="ValuesAdditional" aria-describedby="basic-addon13"
                    v-model="scope.inputAdditionalValue" :disabled="true" />
            </div>
        </div>
    </fieldset>
</template>
<script>
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";
import DropdownSelect from "@/Components/DropdownSelect.vue";
import Multiselect from "@vueform/multiselect";
import ProjectAdditionalModal from "@/Components/UiComponents/Projects/TemplatesProjects/ProjectAdditionalModal.vue";

export default {
    props: {
        scope: Object, //Hereda la data del padre
        isEdit: Boolean, //Cambia la información en caso de edit
    },
    data() {
        return {
            modalColumns: {
                column1: "Codigo",
                column2: "Monto",
                column3: "Proyecto",
                column4: "Fecha",
                column5: "Estatus",
                settings: { columnS1: "Inactivar", columnS2: "Activar" },
            }
        }
    },
    emits: ["transfer-ref", "reload-changes"],
    methods: {
        /**
         * Metodo que autocompleta el campo
         * @param {String} stringToAutoComplete String que se va a autorrellenar
         */
        autoCompleteClient(stringToAutoComplete) {
            this.scope.inputClientAssociated = stringToAutoComplete;
            this.scope.dropDownControl.clients.noInput = false;
        },
        /**
         * Metodo que captura el evento emitido por el componente hijo del modal y la envia a un metodo global
         * @param {*} paramCatch Captura la información del array usado para el modal
         */
        prepareInfoTransfer(paramCatch) {
            //Emite informacion al padre
            this.$emit('reload-changes', {
                arrayToAssign: paramCatch,
                arrayTarget: "additionalValues",
                refs: ["value_id", "aditional_project_value", "project_id", "register_date", "status_id"]
            })
        },
        /**
         * Metodo que no reicbe parametros y se encarga de asignar la información a las diferentes variables de la data
         * @param {Number} projectId Almacena el id del proyecto
         */
        asignValueList(projectId) {
            //Reinicializamos los array
            this.scope.listInfoToUpdate = []
            this.scope.listInfoToCancel = []
            //Asignamos la propiedad a la su objeto de transferencia de Cancelación de cambios
            this.scope.listInfoToCancel = this.scope.dataSelect.additionalValues.filter(object => {
                return object['project_id'].toString().toLowerCase().includes(projectId.toString().toLowerCase())
            })
            //Inicializamos la información dependiendo de la tabla
            this.scope.listInfoToUpdate = this.scope.listInfoToCancel.map(object => {
                //Una vez capturamos los valores de las columnas, las asignamos un nuevo objeto
                const copyObject = {
                    codigo: object.value_id,
                    monto: parseFloat(object.aditional_project_value),
                    proyecto: object.project_id,
                    fecha: object.register_date,
                    estatus: object.status_id
                }

                return copyObject;
            })
        },
        /**
         * Metodo que añade una nueva fila al modal
         * @param {*} lastValueId Almacena el siguiente id de montos adicionales
         */
        updateValueModal(lastValueId) {
            this.scope.listInfoToUpdate.push({
                codigo: lastValueId,
                monto: parseFloat(this.scope.additionalInput),
                proyecto: this.scope.projectId,
                fecha: new Date(Date.now()).toISOString().substring(0, 10), //Formato de fecha YYYY-mm-dd,
                estatus: 1
            })
        }
    },
    mounted() {
        this.$emit("transfer-ref", this.$refs);
    },
    components: {
        FontAwesome,
        DropdownSelect,
        Multiselect,
        ProjectAdditionalModal,
    }
};
</script>
