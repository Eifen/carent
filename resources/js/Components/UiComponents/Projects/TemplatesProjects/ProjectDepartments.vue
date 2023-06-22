<template>
    <fieldset :class="scope.formClass.fieldset">
        <!-- Monto -->
        <div class="mb-3" v-if="scope.inputCurrenciesSelect != 0">
            <label for="Value"
                >Monto
                <span :class="scope.formClass.requiredField">*</span></label
            >
            <div class="input-group">
                <span class="input-group-text" id="basic-addon7">
                    <font-awesome
                        string-icon="fa-solid fa-hashtag"
                    ></font-awesome>
                </span>
                <input
                    type="text"
                    class="form-control"
                    id="Value"
                    aria-describedby="basic-addon7"
                    v-model="scope.inputValue"
                />
                <span class="input-group-text" id="basic-addon10" v-if="isEdit">
                    <project-additional-modal
                        modal-title="Montos Adicionales"
                        id-modal="additionalValues"
                        :modal-data="scope.dataSelect.additionalValues"
                        table-modal-title="Lista de montos"
                        table-modal-insert="Agregar monto adicional"
                    ></project-additional-modal>
                </span>
            </div>
            <!-- Mensajes de error en Nombre-->
            <div
                :class="scope.formClass.failureValidation"
                v-if="scope.messages.error.valueError != ''"
            >
                <font-awesome
                    string-icon="fa-solid fa-circle-exclamation"
                ></font-awesome>
                {{ scope.messages.error.valueError }}
            </div>
        </div>
        <!-- Divisiones -->
        <div class="mb-3">
            <label for="Departments"
                >Divisiones
                <span :class="scope.formClass.requiredField">*</span></label
            >
            <!-- Departamentos -->
            <div class="input-group">
                <Multiselect
                    v-model="scope.inputDepartments"
                    mode="tags"
                    :close-on-select="true"
                    :searchable="true"
                    placeholder="Seleccione una o varias divisiones"
                    openDirection="top"
                    :options="scope.dataSelect.departments"
                ></Multiselect>
            </div>
        </div>
        <!-- Horas Totales -->
        <div class="mb-3" v-if="scope.inputDepartments != 0">
            <label for="HoursAssigned"
                >Horas Totales
                <span :class="scope.formClass.requiredField">*</span></label
            >
            <div class="input-group">
                <span class="input-group-text" id="basic-addon9">
                    <font-awesome
                        string-icon="fa-solid fa-hashtag"
                    ></font-awesome>
                </span>
                <input
                    type="text"
                    class="form-control"
                    id="HoursAssigned"
                    aria-describedby="basic-addon9"
                    v-model="scope.inputHoursAssigned"
                    :disabled="true"
                />
            </div>
            <!-- Mensajes de error en Nombre-->
            <div
                :class="scope.formClass.failureValidation"
                v-if="scope.messages.error.hoursAssignedError != ''"
            >
                <font-awesome
                    string-icon="fa-solid fa-circle-exclamation"
                ></font-awesome>
                {{ scope.messages.error.hoursAssignedError }}
            </div>
        </div>
        <!-- Horas adicionales -->
        <div class="mb-3" v-if="scope.dataSelect.additionalHours.length > 0">
            <label for="HoursAdditional">Horas Adicionales</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon12">
                    <font-awesome
                        string-icon="fa-solid fa-hashtag"
                    ></font-awesome>
                </span>
                <input
                    type="text"
                    class="form-control"
                    id="HoursAdditional"
                    aria-describedby="basic-addon12"
                    v-model="scope.inputAdditionalHours"
                    :disabled="true"
                />
            </div>
        </div>
        <!-- Montos adicionales -->
        <div class="mb-3" v-if="scope.dataSelect.additionalValues.length > 0">
            <label for="ValuesAdditional">Montos adicionales</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon13">
                    <font-awesome
                        string-icon="fa-solid fa-hashtag"
                    ></font-awesome>
                </span>
                <input
                    type="text"
                    class="form-control"
                    id="ValuesAdditional"
                    aria-describedby="basic-addon13"
                    v-model="scope.inputAdditionalValue"
                    :disabled="true"
                />
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
    emits: ["transfer-ref"],
    methods: {
        /**
         * Metodo que autocompleta el campo
         * @param {String} stringToAutoComplete String que se va a autorrellenar
         */
        autoCompleteClient(stringToAutoComplete) {
            this.scope.inputClientAssociated = stringToAutoComplete;
            this.scope.dropDownControl.clients.noInput = false;
        },
    },
    mounted() {
        this.$emit("transfer-ref", this.$refs);
    },
    components: {
        FontAwesome,
        DropdownSelect,
        Multiselect,
        ProjectAdditionalModal,
    },
};
</script>
