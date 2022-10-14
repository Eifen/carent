<template>
    <div class="row align-items-center justify-content-center wrapper-forms" v-cloak>
        <Filters @clearUsersList="clearUsersList"/>
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
        beforeCreate: function(){},
        mounted: function () {
        },
        methods: {

            clearUsersList: function(){
                this.usuarios.mostrar = false;
                this.usuarios.registros = [];
            },
            searchUser: function(){
                console.log("buscar")
            }

        }
    }

</script>

<style lang="less" scoped src="../../less/usuario/listingUsers.less"></style>
