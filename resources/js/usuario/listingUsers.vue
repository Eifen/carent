<template>
    <div class="row align-items-center justify-content-center wrapper-forms" v-cloak>
        <!-- <Filters @clearUsersList="clearUsersList" @showUsers="showUsers"/> -->
        <div class="col-12">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Código</th>
                        <th scope="col">Cédula</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Correo</th>
                        <th scope="col">Estatus</th>
                        <th scope="col"></th>
                        <th scope="col" v-if="canUpdate"></th>
                        <th scope="col" v-if="canUpdate">Asignar Menus</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in usersList">
                        <th scope="row">{{ user.codigo }}</th>
                        <td>{{ user.cedula }}</td>
                        <td>{{ user.nombre }}</td>
                        <td>{{ user.correo_principal }}</td>
                        <td>{{ user.estatus }}</td>
                        <td>
                            <i class="fas fa-search-plus" v-on:click="mostrarDetalleUsuario(user.id, $event)"></i>
                        </td>
                        <td v-if="canUpdate">
                            <a v-bind:href="'/formModificarUsuario/'+user.id" target="_self">
                                <i class="far fa-edit"></i>
                            </a>
                        </td>
                        <td>
                            <i class="fas fa-user-edit" v-on:click="mostrarDetalleMenu(usuario.id, $event)"></i>
                        </td>
                    </tr>
                </tbody>
                <tfoot v-if="usersList.length > 0">
                    <tr>
                        <td  colspan="5">
                            <div>
                                <div><b>Página</b></div>
                                <div class="wrapper-input" v-on:keyup="pageNumber(pager.page)">
                                    <vue-numeric class="form-control text-center form-control-sm"
                                                 :max="pager.max"
                                                 :min="1"
                                                 :precision="0"
                                                 type="text"
                                                 v-model="pager.page"></vue-numeric>
                                </div>
                                <div><b>de {{ pager.numPages }}</b></div>
                                <div>
                                    <b-icon-chevron-compact-left class="icono border rounded" v-on:click="prevPage"></b-icon-chevron-compact-left>
                                </div>
                                <div>
                                    <b-icon-chevron-compact-right class="icono border rounded" v-on:click="nextPage"></b-icon-chevron-compact-right>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</template>


<script>

import Filters from './userListFilter.vue'
import VueNumeric from 'vue-numeric';
import { useUtils } from '../../js/components/Utils.js'
const utils = useUtils()
var self

export default {
    components: {
        Filters,
        VueNumeric
    },
    data() {
        return {
            canUpdate: false,
            pager: {
                max: 0,
                numPages: 0,
                page: 1,
                paginate: 100,
                resultsFrom: 0
            },
            usersList: []
        }
    },
    beforeCreate: function() {
        self = this
    },
    mounted: function () {
        self.searchUser()
    },
    methods: {

        clearUsersList: () => {
            self.users.show = false
            self.users.data = []
        },
        mostrarDetalleUsuario: () => {},
        nextPage: () => {
            self.pager.page = ((self.pager.page + 1) > self.pager.max) ? self.pager.max : (self.pager.page + 1)
            self.resultsFrom()
        },
        pageNumber: (page) => {

            if(page <= self.pager.max) {
                self.pager.page = page
                self.resultsFrom()
            }

        },
        prevPage: () =>  {
            self.pager.page = ((self.pager.page - 1) < 1) ? 1 : (self.pager.page - 1)
            self.resultsFrom()
        },
        resultsFrom: () => {
            let multiple = self.pager.page - 1
            self.pager.resultsFrom = (multiple === 0) ? multiple : (self.pager.paginate * multiple) + 1
            self.searchUser()
        },
        searchUser: (data = null, searchBy = null, searchFrom = 0) => {

            let ajaxData = {
                method: "get",
                params: {
                    data: data,
                    paginate: self.pager.paginate,
                    searchBy: searchBy,
                    searchFrom: self.pager.resultsFrom
                },
                url: "/searchUsers"
            }

            utils.ajaxRequest(ajaxData)
            .then(function (response) {

                if(response.status === 200 && response.data.response === true) {
                    self.usersList = response.data.users
                    self.pager.max = parseInt(response.data.pages)
                    self.pager.numPages = response.data.pages
                    console.log(self.usersList)
                } else {
                    throw response.data;
                }

            })
            .catch(error => {


            })

        },
        showUsers: (data) => {

            self.canUpdate = data.canUpdate
            self.users.data = data.users
            self.users.show = data.showUsers

        }

    }
}

</script>

<style lang="less" scoped src="../../less/usuario/listingUsers.less"></style>
