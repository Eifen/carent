<template>
    <fieldset :class="scope.formClass.fieldset">
        <!-- Gerente asociado -->
        <div class="mb-3">
            <label for="managerAssociated">Gerente asociado<span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text">
                    <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                </span>
                <input type="text" :ref="scope.dropDownControl.manager.ref" class="form-control"
                    placeholder="Ejemplo: Samuel Márquez" id="managerAssociated" v-model="scope.inputManagerAssociated" />
                <dropdown-select :stringToSearch="scope.inputManagerAssociated"
                    :arrayObjectResult="scope.dataSelect.managers" columnToSearch="user_name"
                    :controlList="scope.dropDownControl.manager.noInput"
                    @complete-input="autoCompleteDistribution($event, 'manager', 'inputManagerAssociated')"></dropdown-select>
            </div>
            <!-- Mensajes de error en Nombre-->
            <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.managerError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.managerError }}
            </div>
        </div>
        <!-- Socio -->
        <div class="mb-3">
            <label for="partnerAssociated">Socio<span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text">
                    <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                </span>
                <input type="text" :ref="scope.dropDownControl.partner.ref" class="form-control"
                    placeholder="Ejemplo: Samuel Márquez" id="partnerAssociated" v-model="scope.inputPartnerAssociated" />
                <dropdown-select :stringToSearch="scope.inputPartnerAssociated"
                    :arrayObjectResult="scope.dataSelect.partners" columnToSearch="user_name"
                    :controlList="scope.dropDownControl.partner.noInput"
                    @complete-input="autoCompleteDistribution($event, 'partner', 'inputPartnerAssociated')"></dropdown-select>
            </div>
            <!-- Mensajes de error en Nombre-->
            <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.partnerError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.partnerError }}
            </div>
        </div>
        <!-- Socio de Calidad-->
        <div class="mb-3">
            <label for="qualityPartnerAssociated">Socio de Calidad<span
                    :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text">
                    <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                </span>
                <input type="text" :ref="scope.dropDownControl.qualityPartner.ref" class="form-control"
                    placeholder="Ejemplo: Samuel Márquez" id="qualityPartnerAssociated"
                    v-model="scope.inputQualityPartnerAssociated" />
                <dropdown-select :stringToSearch="scope.inputQualityPartnerAssociated"
                    :arrayObjectResult="scope.dataSelect.partners" columnToSearch="user_name"
                    :controlList="scope.dropDownControl.qualityPartner.noInput"
                    @complete-input="autoCompleteDistribution($event, 'qualityPartner', 'inputQualityPartnerAssociated')"></dropdown-select>
            </div>
            <!-- Mensajes de error en Nombre-->
            <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.qualityPartnerError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.qualityPartnerError }}
            </div>
        </div>
        <!-- Tipo de moneda-->
        <div class="mb-3">
            <label for="Currencies">Monto en: <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <select class="form-select" v-model="scope.inputCurrenciesSelect" title="CurrenciesSelect">
                    <option value=0 selected disabled>Seleccione una opción</option>
                    <option v-for="(select, cursor) in scope.dataSelect.currencies" :key="cursor"
                        :value="select.currency_id">
                        {{ select.currency_name }}
                    </option>
                </select>
            </div>
        </div>
        <!-- Tipo de Empresa-->
        <div class="mb-3">
            <label for="Companies">Empresa: <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <select class="form-select" v-model="scope.inputCompaniesSelect" title="CompaniesSelect">
                    <option value=0 selected disabled>Seleccione una opción</option>
                    <option v-for="(select, cursor) in scope.dataSelect.companies" :key="cursor" :value="select.company_id">
                        {{ select.company_name }}
                    </option>
                </select>
            </div>
        </div>
        <!-- Fecha de contratacion -->
        <div class="mb-3">
            <label for="hiringDate">Fecha de contratación
                <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Ejemplo: 1990-02-18" id="hiringDate"
                    v-model="scope.inputHiringDate" disabled />
                <span class="input-group-text">
                    <calendar @to-input="insertHiringDate"></calendar>
                </span>
            </div>
            <!-- Mensajes de error en fecha -->
            <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.hiringDateError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.hiringDateError }}
            </div>
        </div>
    </fieldset>
</template>
<script>
import FontAwesome from '@/Components/FontAwesome/FontAwesome.vue';
import DropdownSelect from '@/Components/DropdownSelect.vue';
import Calendar from "@/Components/Calendar.vue";
export default {
    props: {
        scope: Object, //Hereda la data del padre
        isEdit: Boolean //Cambia la información en caso de edit
    },
    emits: ['transfer-ref', 'active-hiring'],
    methods: {
        /**
         * Metodo que autocompleta el campo
         * @param {String} stringToAutoComplete String que se va a autorrellenar
         * @param {String} columnTarget Campo asociado al String
         * @param {String} inputTarget Nombre del input donde se autorrellenara
         */
        autoCompleteDistribution(stringToAutoComplete, columnTarget, inputTarget) {
            //Aplicamos el autollenado y cerramos el dropdown
            this.scope[inputTarget] = stringToAutoComplete
            this.scope.dropDownControl[columnTarget].noInput = false
        },
        /**
         * Metodo que guarda la informacion de la fecha
         * @param {*} valueCalendar String de tipado date en formato US que dictamina la fecha
         */
        insertHiringDate(valueCalendar) { this.$emit('active-hiring', valueCalendar) }
    },
    mounted() {
        this.$emit('transfer-ref', this.$refs)
    },
    components: { FontAwesome, DropdownSelect, Calendar }
};
</script>
