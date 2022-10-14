<template>
    <div class="row align-items-center justify-content-center wrapper-forms" v-cloak>
        <Filters @clearUsersList="clearUsersList" @showUsers="showUsers"/>
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
                usuarios: {
                    mostrar: false,
                    registros: []
                }
            }
        },
        beforeCreate: function() {},
        mounted: function () {
        },
        methods: {

            clearUsersList: function() {
                this.usuarios.mostrar = false;
                this.usuarios.registros = [];
            },
            showUsers: function(users) {
                console.log("buscar")
                console.log(users)
            }

        }
    }

</script>

<style lang="less" scoped src="../../less/usuario/listingUsers.less"></style>
