<template>

    <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-7">
        <form class="row">
            <div class="form-group col-12">
                <div class="input-group">
                    <span class="input-group-text">
                        <select class="form-control"
                            v-bind:disabled="formSearch.select.disabled"
                            v-model="formSearch.select.value"
                            v-on:change="filterType">
                                <option value="" selected disabled>Consultar por</option>
                                <option value="1">Código de usuario</option>
                                <option value="2">Cédula</option>
                                <option value="3">Correo electrónico</option>
                                <option value="4">Primer o segundo nombre</option>
                                <option value="5">Primer o segundo apellido</option>
                        </select>
                    </span>
                    <input :class="formSearch.inputSearch.class"
                        :disabled="formSearch.inputSearch.disabled"
                        ref="inputSearch"
                        type="text"
                        v-on:keyup="evaluateField"
                        v-model.trim="formSearch.inputSearch.value">
                    <span class="input-group-text">
                        <button class="btn btn-primary"
                            type="button"
                            v-bind:disabled="formSearch.submit.disabled"
                            v-html="formSearch.submit.html"
                            v-on:click="searchUser"></button>
                    </span>
                    <div :class="this.formSearch.inputSearch.message.class"> {{ formSearch.inputSearch.message.text }} </div>
                </div>
            </div>
        </form>
    </div>

</template>


<script>

import { useUtils } from '../../js/components/Utils.js'
const utils = useUtils()
var self

export default {

    data() {
        return {
            formSearch: {
                submit: {
                    disabled: true,
                    html: "Consultar"
                },
                inputSearch: {
                    class: "form-control inputSearch",
                    disabled: true,
                    message: {
                        class: "",
                        text: ""
                    },
                    value: ""
                },
                select: {
                    disabled: false,
                    value: ""
                }
            }
        }
    },
    beforeCreate: function() {},
    mounted: function () {
        self = this
    },
    methods: {

        evaluateField: function() {

            if(this.formSearch.inputSearch.value === "") {
                this.$emit('clearUsersList')
            }

            this.formSearch.inputSearch.class = "form-control inputSearch"
            this.formSearch.inputSearch.message.class = "mensaje"
            this.formSearch.inputSearch.message.text = ""

        },
        filterType: function(e) {

            let opcion = parseInt(this.formSearch.select.value)
            let valoresPermitidos = [1,2,3,4,5]
            this.$emit('clearUsersList')

            if(valoresPermitidos.includes(opcion)) {
                this.formSearch.inputSearch.disabled = false
                this.formSearch.submit.disabled = false
            } else {
                this.formSearch.inputSearch.disabled = true
                this.formSearch.submit.disabled = true
            }

        },
        searchUser: function(e) {

            //self.alert.mostrar = false;

            if(this.formSearch.inputSearch.value !== "") {

                this.formSearch.submit.html = '<i class="fas fa-cog fa-spin"></i>'
                this.formSearch.submit.disabled = true

                let ajaxData = {
                    method: "get",
                    params: {
                        data: this.formSearch.inputSearch.value,
                        searchBy: this.formSearch.select.value
                    },
                    url: "/buscarUsuarios"
                }

                utils.ajaxRequest(ajaxData)
                .then(function (response) {

                    self.formSearch.submit.html = 'Consultar'
                    self.formSearch.submit.disabled = false

                    if(response.status === 200 && response.data.response === true) {

                        let emitData  = {
                            canUpdate: response.data.permisoActualizar,
                            showUsers: true,
                            users: response.data.usuarios
                        }
                        self.$emit('showUsers', emitData)

                    } else {
                        throw response.data;
                    }

                })
                .catch(error => {

                    console.log(error)
                    self.formSearch.submit.html = 'Consultar'
                    self.formSearch.submit.disabled = false

                    let emitData  = {
                        canUpdate: false,
                        showUsers: false,
                        users: []
                    }
                    self.$emit('showUsers', emitData)

                })

                return;

                axios.get('/buscarUsuarios', {params: parameters})
                .then(function (response) {})
                .catch(error => {

                    if(error.response){

                        var message = "Existe un error!, consulte con el administrador del sistema."

                    } else {

                        var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema."

                    }

                    self.alert.message = message

                });

            } else {

                this.formSearch.inputSearch.class = "form-control inputSearch error"
                this.formSearch.inputSearch.message.class = "mensaje invalid-feedback"
                this.formSearch.inputSearch.message.text = "Campo requerido"
                //zenscroll.toY($(".inputSearch").offset().top - 100);

            }

        }

    }

}

</script>

<style lang="less" scoped src="../../less/usuario/userListFilter.less"></style>
