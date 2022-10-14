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
            }

        }
    }

</script>
