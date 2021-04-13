<template>
  <b-row>

    <b-col cols=12>
      <h5 v-if="formFiltro.mostrar">Filtros de Búsqueda</h5>
      <b-form class="row" v-if="formFiltro.mostrar">
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          description="Nombre que se le dío la proyecto"
          label="Proyecto"
          label-for="proyecto"
          id="group-proyecto">
          <b-form-input
            :disabled="formFiltro.campos.proyecto.disabled"
            id="proyecto"
            ref="proyecto"
            size="sm"
            type="text"
            v-model.trim="formFiltro.campos.proyecto.value"></b-form-input>
        </b-form-group>
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          description="Nombre que se le dío al cliente"
          label="cliente"
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
          label="Socio"
          label-for="empleado"
          id="group-empleado">
          <select aria-describedby="empleadoHelp"
                  class="form-control form-control-sm"
                  id="empleado"
                  data-validar="true"
                  v-bind:disabled="formFiltro.campos.empleado.disabled"
                  v-model="formFiltro.campos.empleado.value">
            <option :value="null" selected>Seleccione...</option>
            <option v-bind:value="empleado.id" v-for="empleado in formFiltro.campos.empleado.listado">{{ empleado.nombre }}</option>
          </select>
        </b-form-group>

        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          label="Socio Calidad"
          label-for="empleadoC"
          id="group-empleadoC">
          <select aria-describedby="empleadoCHelp"
                  class="form-control form-control-sm"
                  id="empleadoC"
                  data-validar="true"
                  v-bind:disabled="formFiltro.campos.empleadoC.disabled"
                  v-model="formFiltro.campos.empleadoC.value">
            <option :value="null" selected>Seleccione...</option>
            <option v-bind:value="empleadoC.id" v-for="empleadoC in formFiltro.campos.empleadoC.listado">{{ empleadoC.nombre }}</option>
          </select>
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
        <b-col cols="12" md="6" lg="4" class="wrapper-btn-generar-excel">
          <download-excel :data="tabla.registros" :name="'horas_cargables.xls'">
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
  @import '../../less/reportes/horasProyectos.less';
</style>

<script>

  import Vue from 'vue';
  import axios from 'axios';
  import alert from '../components/alert.vue';
  import Multiselect from 'vue-multiselect';
  import JsonExcel from "vue-json-excel";
  import zenscroll from 'zenscroll';
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
              empleado: {
                disabled: true,
                listado: [],
                value: null
              },
              empleadoC: {
                disabled: true,
                listado: [],
                value: null
              },
              estatus: {
                disabled: true,
                listado: [],
                value: null
              },
              cliente: {
                disabled: true,
                value: ""
              },
              proyecto : {
                disabled: true,
                value: ""
              }
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
            registros: [],
            horasProyecto: []
          },
        };
      },
      components: {
        alert,
        "downloadExcel": JsonExcel,
        Multiselect
      },

      beforeCreate: function(){

        self = this;

        axios.get('/dataRepTotalHorasProyectos')
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.tabla.encabezado = [
              { key: 'numero', label: '#' },
              { key: 'proyecto', label: 'Proyecto' },
              { key: 'cliente', label: 'cliente' },
              { key: 'horas_contratadas', label: 'Horas Contratadas' },
              { key: 'horas_adicionales', label: 'Horas Adionales' },
              { key: 'total_horas_presupuestadas', label: 'Total Horas Presupuestadas' },
              { key: 'total_horas_cargadas', label: 'Total Horas Cargadas' },
              { key: 'porc_total_horas', label: '% Total Horas Cargadas' },
            ];

            self.formFiltro.campos.empleado.listado = response.data.empleados;
            self.formFiltro.campos.empleadoC.listado = response.data.empleados;
            self.formFiltro.campos.estatus.listado = response.data.estatus;
            self.tabla.horasProyecto = response.data.totalHorasProyectos
            self.tabla.registros = self.registroTabla(self.tabla.horasProyecto);

            if(self.tabla.horasProyecto.length === 0){

              let mensaje = "No hay proyectos";
              self.mostrarAlert(self.tabla.alert, true, "warning", mensaje, false, false, 0);

            }

            self.tabla.paginador.paginar = response.data.paginar;
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

            self.formFiltro.campos.empleado.disabled = false;
            self.formFiltro.campos.empleadoC.disabled = false;
            self.formFiltro.campos.estatus.disabled = false;
            self.formFiltro.campos.cliente.disabled = false;
            self.formFiltro.campos.proyecto.disabled = false;
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
              proyecto: item.proyecto,
              cliente: item.cliente,
              horas_contratadas: item.horas_contratadas,
              horas_adicionales: item.horas_adicionales,
              total_horas_presupuestadas: item.total_horas,
              total_horas_cargadas: item.horas_cargadas,
              porc_total_horas: item.porc_total_horas
            };

            registros.push(data);

          });

          return registros;

        },
        buscar: function(){

          self.tabla.horasProyecto = [];
          self.formFiltro.campos.cliente.disabled = true;
          self.formFiltro.campos.proyecto.disabled = true;
          self.formFiltro.campos.estatus.disabled = true;
          self.formFiltro.campos.empleado.disabled = true;
          self.formFiltro.campos.empleadoC.disabled = true;

          self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlLoading;
          self.formFiltro.btn.filtrar.disabled = true;
          self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlLoading;
          self.formFiltro.btn.limpiarFiltro.disabled = true;

          //Obtenemos los valores
          let desde = (self.tabla.paginador.pagina - 1) * self.tabla.paginador.paginar;
          let parametros = {
            empleado: self.formFiltro.campos.empleado.value,
            empleadoC: self.formFiltro.campos.empleadoC.value,
            cliente: self.formFiltro.campos.cliente.value,
            desde: desde,
            proyecto: self.formFiltro.campos.proyecto.value,
            paginar: self.tabla.paginador.paginar,
            estatus: self.formFiltro.campos.estatus.value
          };

          //Se utiliza el metodo get para su busqueda y se envian con los parametros
          axios.get('/buscarTotalHorasProyectos', {params: parametros})
          .then(function (response) {   

            self.tabla.horasProyecto = response.data.totalHorasProyectos;

            self.tabla.registros = self.registroTabla(self.tabla.horasProyecto);

            if(self.tabla.horasProyecto.length === 0){

              let mensaje = "No hay proyectos";
              self.mostrarAlert(self.tabla.alert, true, "warning", mensaje, false, false, 0);

            }
            self.formFiltro.campos.cliente.disabled = false;
            self.formFiltro.campos.empleado.disabled = false;
            self.formFiltro.campos.empleadoC.disabled = false;
            self.formFiltro.campos.proyecto.disabled = false;
            self.formFiltro.campos.estatus.disabled = false;

            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.filtrar.disabled = false;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.btn.limpiarFiltro.disabled = false;

            // Se le asigna los valores a las variables
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

          }).catch(error => {


          });

        },
        limpiarFiltro: function(){

          self.formFiltro.campos.cliente.value = null;
          self.formFiltro.campos.empleado.value = null;
          self.formFiltro.campos.empleadoC.value = null;
          self.formFiltro.campos.proyecto.value = null;
          self.formFiltro.campos.estatus.value = null;
          self.buscar();

        },

      }
  }

</script>
