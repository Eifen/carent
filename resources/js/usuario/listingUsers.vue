<template>
    <div class="row align-items-center justify-content-center wrapper-forms" v-cloak>
        <Filters @clearUsersList="clearUsersList" @showUsers="showUsers"/>
        <div class="col-12" v-show="users.show">
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
                    <tr v-for="user in users.data">
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

    export default {
        components: {
            Filters
        },
        data() {
            return {
                canUpdate: false,
                users: {
                    data: [],
                    show: false
                }
            }
        },
        beforeCreate: function() {},
        mounted: function () {
        },
        methods: {

            clearUsersList: function() {
                this.users.show = false
                this.users.data = []
            },
            mostrarDetalleUsuario: function() {},
            showUsers: function(data) {

                this.canUpdate = data.canUpdate
                this.users.data = data.users
                this.users.show = data.showUsers

            }

        }
    }

</script>

<style lang="less" scoped src="../../less/usuario/listingUsers.less"></style>
