<template>
    <div class="row align-items-center justify-content-center" v-cloak>
        <div class="col-11 col-md-10 wrapper-users">
            <div class="row">
                <div class="col-6 col-md-4">
                    <h2 class="title">Usuarios</h2>
                </div>
                <div class="col-6 col-md-8 text-end">
                    <button type="button" class="btn btn-primary create-user">Crear Usuario</button>
                </div>
                <Filters @clearUsersList="clearUsersList" @searchBy="searchBy" @showUsers="showUsers"/>
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Código</th>
                                    <th scope="col">Cédula</th>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Correo</th>
                                    <th scope="col">Estatus</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <User :user="user"
                                      :key="index"
                                      v-for="(user, index) in usersList"
                                      v-if="usersList.length > 0" />
                                <tr v-else-if="usersList.length = 0" class="table-warning">
                                    <td colspan="6">No se encontraron resultados</td>
                                </tr>
                                <tr v-if="loading">
                                    <td colspan="6">
                                        <div class="spinner-border m-2 text-primary" role="status"></div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot v-if="usersList.length > 0">
                                <tr>
                                    <td colspan="6">
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
            </div>
        </div>
    </div>
</template>


<script>

import Filters from './userListFilter.vue'
import User from './User.vue'
import VueNumeric from 'vue-numeric';
import { useUtils } from '../../js/components/Utils.js'
const utils = useUtils()
var self

export default {
    components: {
        Filters,
        User,
        VueNumeric
    },
    data() {
        return {
            canUpdate: false,
            pager: {
                data: null,
                max: 0,
                numPages: 0,
                page: 1,
                paginate: 100,
                resultsFrom: 0,
                searchBy: null
            },
            loading: false,
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
            self.pager.data = null
            self.pager.searchBy = null
            self.searchUser()
        },
        nextPage: () => {

            let pageTo = (self.pager.page + 1)
            if(pageTo > self.pager.max) {
                self.pager.page = self.pager.max
            } else {
                self.pager.page = pageTo
                self.resultsFrom()
            }


        },
        pageNumber: (page) => {

            if(page <= self.pager.max) {
                self.pager.page = page
                self.resultsFrom()
            }

        },
        prevPage: () =>  {

            let pageTo = (self.pager.page - 1)
            if(pageTo < 1) {
                self.pager.page = 1
            } else {
                self.pager.page = pageTo
                self.resultsFrom()
            }

        },
        resultsFrom: () => {
            let multiple = self.pager.page - 1
            self.pager.resultsFrom = (multiple === 0) ? multiple : (self.pager.paginate * multiple) + 1
            self.searchUser()
        },
        searchBy: (data) => {
            self.pager.data = data.data
            self.pager.searchBy = data.searchBy
            self.searchUser()
        },
        searchUser: () => {

            self.loading = true

            let ajaxData = {
                method: "get",
                params: {
                    data: self.pager.data,
                    paginate: self.pager.paginate,
                    searchBy: self.pager.searchBy,
                    searchFrom: self.pager.resultsFrom
                },
                url: "/searchUsers"
            }

            utils.ajaxRequest(ajaxData)
            .then(function (response) {

                self.loading = false
                if(response.status === 200 && response.data.response === true) {
                    self.usersList = response.data.users
                    self.pager.max = parseInt(response.data.pages)
                    self.pager.numPages = response.data.pages
                } else {
                    throw response.data;
                }

            })
            .catch(error => {
                self.loading = false
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
