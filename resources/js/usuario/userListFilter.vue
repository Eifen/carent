<template>

    <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-7">
        <form class="row">
            <div class="form-group col-12 col-md-4">
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
            </div>
            <div class="form-group col-12 col-md-6">
                <input class="form-control inputSearch"
                    ref="inputSearch"
                    type="text"
                    v-bind:disabled="formSearch.inputSearch.disabled"
                    v-on:keyup="evaluateField('inputSearch', $event)"
                    v-model="formSearch.inputSearch.value">
                <div class="mensaje"></div>
            </div>
            <div class="form-group col-12 col-md-2">
                <button class="btn btn-primary"
                    type="button"
                    v-bind:disabled="formSearch.submit.disabled"
                    v-html="formSearch.submit.html"
                    v-on:click="buscar"></button>
            </div>
        </form>
    </div>

</template>


<script>

    var self;

    export default {
        data() {
            return {
                formSearch: {
                    submit: {
                        disabled: true,
                        html: "Consultar"
                    },
                    inputSearch: {
                        disabled: true,
                        value: ""
                    },
                    select: {
                        disabled:false,
                        value: ""
                    }
                }
            }
        },
        beforeCreate: function(){},
        mounted: function () {
            self = this
        },
        methods: {

            buscar: function(e){

                self.alert.mostrar = false;
                return
                if(self.formSearch.inputSearch.value.trim() !== ""){

                    self.formSearch.submit.html = '<i class="fas fa-cog fa-spin"></i>';
                    self.formSearch.submit.disabled = true;

                    let parametros = {
                        buscarPor: self.formSearch.select.value,
                        dato: self.formSearch.inputSearch.value
                    };

                    axios.get('/buscarUsuarios', {params: parametros})
                    .then(function (response) {

                        self.formSearch.submit.html = 'Consultar';
                        self.formSearch.submit.disabled = false;

                        if(response.status === 200 && response.data.response === true){

                            self.usuarios.mostrar = true;
                            self.usuarios.registros = response.data.usuarios;
                            self.permisoActualizar = response.data.permisoActualizar;

                        }else{

                            throw response.data;

                        }

                    })
                    .catch(error => {

                        self.formSearch.submit.html = 'Consultar';
                        self.formSearch.submit.disabled = false;

                        self.alert.mostrar = true;

                        self.usuarios.registros = [];
                        self.usuarios.mostrar = false;

                        if(error.response){

                            var message = "Existe un error!, consulte con el administrador del sistema.";

                        } else {

                            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

                        }

                        self.alert.message = message;

                    });

                } else {

                    $(".inputSearch").parent().find(".mensaje").html("Campo requerido").addClass("invalid-feedback");
                    $(".inputSearch").addClass("error");
                    //zenscroll.toY($(".inputSearch").offset().top - 100);

              }

            },
            cleanMessageError: function(e){

                //$(e.target).removeClass("error");
                //$(e.target).parent(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");

            },
            evaluateField: function(id, e){

                if(e.target.type === 'text'){
                    this.formSearch[id].value = (e.target.value.trim() === "") ? "" : e.target.value;
                }

                if(id === "inputSearch" && this.formSearch["inputSearch"].value.trim() === ""){
                    this.$emit('clearUsersList')
                }

                this.cleanMessageError(e);

            },
            filterType: function(e){

                let opcion = parseInt(e.target.value);
                let valoresPermitidos = [1,2,3,4,5];

                this.$emit('clearUsersList')

                if(valoresPermitidos.includes(opcion)){
                    this.formSearch.inputSearch.disabled = false;
                    this.formSearch.submit.disabled = false;
                }else{
                    this.formSearch.inputSearch.disabled = true;
                    this.formSearch.submit.disabled = true;
                }

            }

        }
    }

</script>
