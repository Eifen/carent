<template>
    <form class="row">
        <div class="form-group col-12 col-sm-6 col-md-3">
            <label for="firstName">Primer Nombre <span class="campo-obligatorio">*</span></label>
            <input aria-describedby="firstNameHelp"
                   class="form-control text-lowercase"
                   data-min="3"
                   data-name-lastname="true"
                   data-validar="true"
                   id="firstName"
                   type="text"
                   v-bind:disabled="form.first_name.disabled"
                   v-model="form.first_name.value"
                   v-on:keyup="valuesForm">
            <small id="firstNameHelp" class="form-text text-muted">Ejemplo: Pedro</small>
            <div class="message"></div>
        </div>
        <div class="form-group col-12 col-sm-6 col-md-3">
            <label for="secondName">Segundo Nombre</label>
            <input aria-describedby="secondNameHelp"
                   class="form-control text-lowercase"
                   data-validar="true"
                   data-name-lastname="true"
                   id="secondName"
                   type="text"
                   v-bind:disabled="form.second_name.disabled"
                   v-model="form.second_name.value"
                   v-on:keyup="valuesForm">
            <small id="secondNameHelp" class="form-text text-muted">Ejemplo: Emilio</small>
            <div class="message"></div>
        </div>
        <div class="form-group col-12 col-sm-6 col-md-3">
            <label for="surname">Primer Apellido <span class="campo-obligatorio">*</span></label>
            <input aria-describedby="surnameHelp"
                   class="form-control text-lowercase"
                   data-min="3"
                   data-name-lastname="true"
                   data-validar="true"
                   id="surname"
                   type="text"
                   v-bind:disabled="form.surname.disabled"
                   v-model="form.surname.value"
                   v-on:keyup="valuesForm">
            <small id="surnameHelp" class="form-text text-muted">Ejemplo: Silva</small>
            <div class="message"></div>
        </div>
        <div class="form-group col-12 col-sm-6 col-md-3">
            <label for="secondSurname">Segundo Apellido</label>
            <input aria-describedby="secondSurnameHelp"
                   class="form-control text-lowercase"
                   data-validar="true"
                   data-name-lastname="true"
                   id="secondSurname"
                   type="text"
                   v-bind:disabled="form.second_surname.disabled"
                   v-model="form.second_surname.value"
                   v-on:keyup="valuesForm">
          <small id="secondSurnameHelp" class="form-text text-muted">Ejemplo: Ruíz</small>
          <div class="message"></div>
        </div>
        <div class="form-group col-12 col-sm-6 col-md-3">
            <label for="documenType">Documento de Identidad <span class="campo-obligatorio">*</span></label>
            <select aria-describedby="documenTypeHelp"
                    class="form-select"
                    id="documenType"
                    data-validar="true"
                    v-bind:disabled="form.documenType.disabled"
                    v-model="form.documenType.value"
                    v-on:click="clearErrorMessage">
            <option value="" disabled selected>Seleccione...</option>
            <option v-bind:value="documenType.id" v-for="documenType in comboDocumenType">@{{ documenType.descripcion }}</option>
            </select>
            <div class="mensaje"></div>
        </div>
        <div class="form-group col-12 col-sm-6">
            <label for="cedula">Cédula de Identidad <span class="campo-obligatorio">*</span></label>
            <input class="form-control"
                   data-formated-number="true"
                   data-only-number="true"
                   data-validar="true"
                   id="cedula"
                   type="text"
                   v-bind:disabled="form.cedula.disabled"
                   v-model="form.cedula.value"
                   v-on:keyup="valuesForm">
            <small id="cedulaHelp" class="form-text text-muted">Ejemplo: 17.471.899</small>
            <div class="mensaje"></div>
        </div>
    </form>
</template>


<script>

var self

import VueNumeric from 'vue-numeric';
import { useUtils } from '../../js/components/Utils.js'
const utils = useUtils()
var self

export default {
    components: {
        VueNumeric
    },
    data() {
        return {
            form: {
                first_name: {
                    disabled: false,
                    value: ""
                },
                second_name: {
                    disabled: false,
                    value: ""
                },
                surname: {
                    disabled: false,
                    value: ""
                },
                second_surname: {
                    disabled: false,
                    value: ""
                },
                documenType: {
                    disabled: false,
                    value: ""
                },
                fechaNacimiento: {
                    disabled: false,
                    value: ""
                },
                codigoUsuario: {
                    disabled: false,
                    value: ""
                },
                cedula: {
                    disabled: false,
                    value: ""
                },
                estado: {
                    disabled: true,
                    validar: false,
                    value: ""
                },
                municipio: {
                    disabled: true,
                    help: "Municipio de la oficina en donde se desempeña",
                    validar: false,
                    value: ""
                },
                parroquia: {
                    disabled: true,
                    help: "Parroquia de la oficina en donde se desempeña",
                    validar: false,
                    value: ""
                },
                division: {
                    disabled: true,
                    validar: false,
                    value: ""
                },
                cargo: {
                    disabled: true,
                    validar: false,
                    value: ""
                },
                correoPrincipal: {
                    disabled: false,
                    value: ""
                },
                correoSecundario: {
                    disabled: false,
                    validar: false,
                    value: ""
                },
                telefono1: {
                    disabled: false,
                    value: ""
                },
                telefono2: {
                    disabled: false,
                    value: ""
                },
                empleado: {
                    checked:false
                },
                fechaIngreso:{
                    disabled: true,
                    validar: false,
                    value: ""
                }
            },
            loading: false
        }
    },
    beforeCreate: function() {
        self = this
    },
    mounted: () => {

    },
    methods: {

        clearErrorMessage: function(e){
          $(e.target).removeClass("error");
          $(e.target).parents(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
        },
        valuesForm: function(e) {

            if(e.target.type === 'text' || e.target.type === 'textarea' || e.target.type === 'email') {
                self.form[e.target.id].value = (e.target.value.trim() === "") ? "" : $(e.target).val()
            }
            self.clearErrorMessage(e)

        }

    }
}

</script>

<style lang="less" scoped src="../../less/usuario/NewUser.less"></style>
