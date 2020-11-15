<template>
  <b-row>
    <b-col cols=12>
      <h5 v-if="formFiltro.mostrar">Filtros de Búsqueda</h5>
      <b-form class="row" v-if="formFiltro.mostrar">
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          description="Razón Social del Cliente"
          label="Cliente"
          label-for="cliente"
          id="group-cliente">
          <b-form-input
            :disabled="formFiltro.campos.cliente.disabled"
            id="cliente"
            ref="cliente"
            size="sm"
            type="text"
            v-model.trim="formFiltro.campos.cliente.value"></b-form-input>
        </b-form-group>

        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          label="Empresa"
          label-for="empresa"
          id="group-empresa">
          <select aria-describedby="empresaHelp"
                  class="form-control form-control-sm"
                  id="empresa"
                  data-validar="true"
                  v-bind:disabled="formFiltro.campos.empresa.disabled"
                  v-model="formFiltro.campos.empresa.value">
            <option :value="null" selected>Seleccione...</option>
            <option v-bind:value="empresa.id" v-for="empresa in formFiltro.campos.empresa.listado">{{ empresa.razon_social }}</option>
          </select>
        </b-form-group>

        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          label="Divisiones"
          label-for="divisiones"
          id="group-divisiones">
          <multiselect :clear-on-select="false"
                       :disabled="formFiltro.campos.divisiones.disabled"
                       :multiple="true"
                       :options="formFiltro.campos.divisiones.listado"
                       :preserve-search="true"
                       :show-labels="false"
                       id="divisiones"
                       label="descripcion"
                       placeholder="Seleccione..."
                       track-by="descripcion"
                       v-model="formFiltro.campos.divisiones.value">
             <template slot="selection"
                       slot-scope="{ values, search, isOpen }">
                       <span class="multiselect__single"
                             v-if="values.length &amp;&amp; !isOpen">{{ values.length }} seleccionado(s)</span>
             </template>
          </multiselect>
        </b-form-group>
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          label="Estatus"
          label-for="estatus"
          id="group-estatus">
          <select aria-describedby="estatusHelp"
                  class="form-control form-control-sm"
                  id="estatus"
                  data-validar="true"
                  v-bind:disabled="formFiltro.campos.estatus.disabled"
                  v-model="formFiltro.campos.estatus.value">
            <option :value="null" selected>Seleccione...</option>
            <option v-bind:value="estatus.id" v-for="estatus in formFiltro.campos.estatus.listado">{{ estatus.descripcion }}</option>
          </select>
        </b-form-group>
        <b-form-group class="col-12 col-sm-6 col-md-2">
          <label>&nbsp;</label>
          <b-button
            :disabled="formFiltro.btn.filtrar.disabled"
            block
            size="sm"
            v-html="formFiltro.btn.filtrar.html"
            v-on:click="buscar"
            variant="primary"></b-button>
        </b-form-group>
        <b-form-group class="col-12 col-sm-6 col-md-2">
          <label>&nbsp;</label>
          <b-button
            :disabled="formFiltro.btn.limpiarFiltro.disabled"
            block
            size="sm"
            v-html="formFiltro.btn.limpiarFiltro.html"
            v-on:click="limpiarFiltro"
            variant="outline-primary"></b-button>
        </b-form-group>
      </b-form>
    </b-col>

    <b-col cols="12">
      <b-row align-h="end" v-cloak v-if="formFiltro.mostrar">
        <b-col cols="12" md="6" lg="4">
          <b-card class="text-left card-clientes-activos">
            <b-card-text>
              <span class="titulo">TOTAL DE CLIENTES ACTIVOS</span>
            </b-card-text>
            <b-card-text>
              <span class="total">{{ total.clientes }}</span>
            </b-card-text>
          </b-card>
        </b-col>
        <b-col cols="12" md="6" lg="4">
          <b-card class="text-left card-clientes-proyecto">
            <b-card-text>
              <span class="titulo">TOTAL DE CLIENTES CON PROYECTO</span>
            </b-card-text>
            <b-card-text>
              <span class="total">{{ total.clientesProy }}</span>
            </b-card-text>
          </b-card>
        </b-col>
        <b-col cols="12" md="6" lg="4" class="wrapper-btn-generar-excel">
          <download-excel :data="tabla.registros" :name="'cantidad_clientes.xls'">
            <b-button
              block
              variant="success">
              Generar Excel
              <b-icon icon="file-earmark" aria-hidden="true"></b-icon>
            </b-button>
          </download-excel>
        </b-col>
      </b-row>
    </b-col>

    <b-col cols=12>
      <b-table
        :busy="tabla.cargando"
        :empty-text="'No se encontraron resultados'"
        :fields="tabla.encabezado"
        :items="tabla.registros"
        :select-mode="'multi'"
        :small="true"
        hover
        responsive
        selectable
        show-empty>
        <template v-slot:table-busy>
          <div class="text-center text-primary">
            <b-spinner class="align-middle"></b-spinner>
          </div>
        </template>
        <template v-slot:empty="scope" v-if="tabla.alert.mostrar">
          <alert :contador="tabla.alert.contador"
                 :icono-cerrar="tabla.alert.iconCerrar"
                 :mensaje="tabla.alert.mensaje"
                 :mostrar="tabla.alert.mostrar"
                 :ocultar-seg="tabla.alert.ocultarSeg"
                 :variante="tabla.alert.variante">
          </alert>
        </template>
        <template v-slot:cell(numero)="data">
          <b>{{ data.item.numero }}</b>
        </template>
        <template v-slot:custom-foot v-if="tabla.registros.length > 0">
          <b-tr>
            <b-td colspan="8">
              <div>
                <div><b>Página</b></div>
                <div class="wrapper-input" v-on:keyup="numeroPagina">
                  <vue-numeric :max="tabla.paginador.max"
                               :min="1"
                               :precision="0"
                               class="form-control text-center form-control-sm"
                               type="text"
                               v-model="tabla.paginador.pagina"></vue-numeric>
                </div>
                <div><b>de {{ tabla.paginador.numPaginas }}</b></div>
                <div>
                  <b-icon-chevron-compact-left class="icono border rounded" v-on:click="paginaAnterior"></b-icon-chevron-compact-left>
                </div>
                <div>
                  <b-icon-chevron-compact-right class="icono border rounded" v-on:click="paginaSiguiente"></b-icon-chevron-compact-right>
                </div>
              </div>
            </b-td>
          </b-tr>
        </template>
      </b-table>
    </b-col>

  </b-row>
</template>

<style lang="less">
  @import '../../less/reportes/clientesProyecto.less';
</style>

<script>

  import axios from 'axios';
  import alert from '../components/alert.vue';
  import Multiselect from 'vue-multiselect';
  import JsonExcel from "vue-json-excel";
  var self;

  export default {
      data() {
        return {
          formFiltro: {
            btn: {
              filtrar: {
                disabled: false,
                html: "",
                htmlInit: "Aplicar Filtro",
                htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
              },
              limpiarFiltro: {
                disabled: false,
                html: "",
                htmlInit: "Limpiar Filtro",
                htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
              }
            },
            campos: {
              cliente : {
                disabled: true,
                value: ""
              },
              empresa: {
                disabled: true,
                listado: [],
                value: null
              },
              divisiones:{
                disabled: true,
                listado: [],
                value: []
              },
              estatus: {
                disabled: true,
                listado: [],
                value: null
              },
            },
            mostrar : false
          },
          tabla: {
            alert:{
              contador: false,
              iconCerrar: false,
              mensaje: "",
              mostrar: false,
              ocultarSeg: 0,
              variante: ""
            },
            cargando: true,
            encabezado: [],
            paginador: {
              max: 0,
              numPaginas: 0,
              pagina:1,
              paginar: 0
            },
            registros: []
          },
          total:{
            clientesProy: 0,
            clientes: 0,
          }
        };
      },
      components: {
        alert,
        "downloadExcel": JsonExcel,
        Multiselect
      },
      beforeCreate: function(){

        self = this;

        axios.get('/dataRepClientesProyectos')
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.tabla.encabezado = [
              { key: 'numero', label: '#' },
              { key: 'cliente', label: 'Cliente' },
              { key: 'empresa', label: 'Empresa' },
              { key: 'division', label: 'Division' },
              { key: 'estatus', label: 'Estatus' }
            ];

            for (var i = 0; i < response.data.clientes.length; i++) {
              self.total.clientesProy ++;
            }
            self.total.clientes = response.data.cantidadClie.cantidad;
           
            self.tabla.registros = self.registroTabla(response.data.clientes);

            if(response.data.clientes.length === 0){

              let mensaje = "No hay proyectos";
              self.mostrarAlert(self.tabla.alert, true, "warning", mensaje, false, false, 0);

            }

            self.formFiltro.campos.divisiones.listado = response.data.divisiones;
            self.formFiltro.campos.estatus.listado = response.data.estatus;
            self.formFiltro.campos.empresa.listado = response.data.empresa;

            self.tabla.paginador.paginar = response.data.paginar;
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

            self.formFiltro.campos.cliente.disabled = false;
            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.campos.empresa.disabled = false;
            self.formFiltro.campos.estatus.disabled = false;
            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.mostrar = true;

            self.tabla.cargando = false;

            self.$parent.reporteCargado();

          }else{

            throw "error";

          }

        })
        .catch(error => {


        });


      },
      beforeUpdate:function(){},
      updated: function(){},
      methods: {

        mostrarAlert: function(alert, mostrar = false, variante = "", mensaje = "", iconCerrar = false, contador = false, ocultarSeg = 0){

          return new Promise(resolve => {

            alert.contador = contador;
            alert.iconCerrar = iconCerrar;
            alert.mensaje = mensaje;
            alert.mostrar = mostrar;
            alert.ocultarSeg = ocultarSeg;
            alert.variante = variante;

            resolve(true);

          });

        },
        numeroPagina: function(e){
          self.buscar();
        },
        paginaAnterior: function(){
          self.tabla.paginador.pagina = ((self.tabla.paginador.pagina - 1) === 0) ? 1 : (self.tabla.paginador.pagina - 1);
          self.buscar();
        },
        paginaSiguiente: function(){
          self.tabla.paginador.pagina = ((self.tabla.paginador.pagina + 1) > self.tabla.paginador.max) ? self.tabla.paginador.pagina : (self.tabla.paginador.pagina + 1);
          self.buscar();
        },
        registroTabla: function(datos){

          const registros = [];
          datos.forEach((item, i) => {

            const data = {
              numero: (i + 1),
              cliente: item.cliente,
              empresa: item.empresa,
              division: item.division,
              estatus: item.estatus
            };

            registros.push(data);

          });

          return registros;

        },
        buscar: function(){

          self.formFiltro.campos.cliente.disabled = true;
          self.formFiltro.campos.divisiones.disabled = true;
          self.formFiltro.campos.empresa.disabled = true;
          self.formFiltro.campos.estatus.disabled = true;

          self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlLoading;
          self.formFiltro.btn.filtrar.disabled = true;
          self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlLoading;
          self.formFiltro.btn.limpiarFiltro.disabled = true;

          //Evaluamos como filtraremos la division
          if(self.formFiltro.campos.divisiones.value.length === 0 && self.formFiltro.campos.divisiones.listado.length > 1){
            var param_divisiones = null;
          }else if(self.formFiltro.campos.divisiones.value.length > 0){
            var param_divisiones = self.formFiltro.campos.divisiones.value;
          }else if(self.formFiltro.campos.divisiones.value.length === 0 && self.formFiltro.campos.divisiones.listado.length === 1){
            var param_divisiones = self.formFiltro.campos.divisiones.listado[0].id;
          }else{
            var param_divisiones = null;
          }

          //Obtenemos los valores
          let desde = (self.tabla.paginador.pagina - 1) * self.tabla.paginador.paginar;
          let parametros = {
            cliente: self.formFiltro.campos.cliente.value,
            desde: desde,
            divisiones: param_divisiones,
            empresa: self.formFiltro.campos.empresa.value,
            estatus: self.formFiltro.campos.estatus.value,
            paginar: self.tabla.paginador.paginar
          };

          //Se utiliza el metodo get para su busqueda y se envian con los parametros
          axios.get('/buscarClientesProyectos', {params: parametros})
          .then(function (response) {

            self.formFiltro.campos.cliente.disabled = false;
            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.campos.empresa.disabled = false;
            self.formFiltro.campos.estatus.disabled = false;

            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.filtrar.disabled = false;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.btn.limpiarFiltro.disabled = false;

            // Se le asigna los valores a las variables
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

            self.tabla.registros = self.registroTabla(response.data.clientes);

          }).catch(error => {

          /*  self.formFiltro.proyecto.disabled = false;
            self.formFiltro.cliente.disabled = false;
            self.formFiltro.estatus.disabled = false;
            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.filtrar.disabled = false;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.btn.limpiarFiltro.disabled = false;*/

          });

        },
        limpiarFiltro: function(){

          self.formFiltro.campos.cliente.value = null;
          self.formFiltro.campos.divisiones.value = [];
          self.formFiltro.campos.empresa.value = null;
          self.formFiltro.campos.estatus.value = null;
  
          self.buscar();

        },
      }
  }

</script>
