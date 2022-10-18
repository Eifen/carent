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
            </table>
        </div>
    </div>
</template>


<script>

import Filters from './userListFilter.vue'
import { useUtils } from '../../js/components/Utils.js'
const utils = useUtils()
var self

export default {
    components: {
        Filters
    },
    data() {
        return {
            canUpdate: false,
            usersList: []
        }
    },
    beforeCreate: function() {
        self = this
    },
    mounted: function () {
        this.searchUser()
    },
    methods: {

        clearUsersList: function() {
            this.users.show = false
            this.users.data = []
        },
        mostrarDetalleUsuario: function() {},
        searchUser: function(data = null, searchBy = null) {

            let ajaxData = {
                method: "get",
                params: {
                    data: data,
                    searchBy: searchBy
                },
                url: "/searchUsers"
            }

            utils.ajaxRequest(ajaxData)
            .then(function (response) {

                if(response.status === 200 && response.data.response === true) {
                    console.log(response.data.users)
                    self.usersList = response.data.users
                    console.log(self.usersList)
                } else {
                    throw response.data;
                }

            })
            .catch(error => {


            })

        },
        showUsers: function(data) {

            this.canUpdate = data.canUpdate
            this.users.data = data.users
            this.users.show = data.showUsers

        }

    }
}

</script>

<style lang="less" scoped src="../../less/usuario/listingUsers.less"></style>
