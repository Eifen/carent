<template>
  <b-row>

    <b-col cols=12>
      <h5 v-if="formFiltro.mostrar">Filtros de Búsqueda</h5>
      <b-form class="row" v-if="formFiltro.mostrar">
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          description="Código Empleado"
          label="Código"
          label-for="codigo"
          id="group-codigo">
          <b-form-input
            :disabled="formFiltro.campos.codigo.disabled"
            id="codigo"
            ref="codigo"
            size="sm"
            type="text"
            v-model.trim="formFiltro.campos.codigo.value"></b-form-input>
        </b-form-group>
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          description="Nombre del Empleado"
          label="Empleado"
          label-for="empleado"
          id="group-empleado">
          <b-form-input
            :disabled="formFiltro.campos.empleado.disabled"
            id="empleado"
            ref="empleado"
            size="sm"
            type="text"
            v-model.trim="formFiltro.campos.empleado.value"></b-form-input>
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
          label="Cargos"
          label-for="cargos"
          id="group-cargos">
          <multiselect :clear-on-select="false"
                       :disabled="formFiltro.campos.cargos.disabled"
                       :multiple="true"
                       :options="formFiltro.campos.cargos.listado"
                       :preserve-search="true"
                       :show-labels="false"
                       id="cargos"
                       label="descripcion"
                       placeholder="Seleccione..."
                       track-by="descripcion"
                       v-model="formFiltro.campos.cargos.value">
             <template slot="selection"
                       slot-scope="{ values, search, isOpen }">
                       <span class="multiselect__single"
                             v-if="values.length &amp;&amp; !isOpen">{{ values.length }} seleccionado(s)</span>
             </template>
          </multiselect>
        </b-form-group>
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          label="Fecha Ingreso"
          label-for="fechaIngreso"
          id="group-fechaIngreso">
          <b-form-datepicker
            :date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
            :disabled="formFiltro.campos.fechaIngreso.disabled"
            :max="formFiltro.campos.fechaIngreso.max"
            id="fechaIngreso"
            label-help="Use las teclas del cursor para navegar por las fechas del calendario"
            label-no-date-selected="Ninguna fecha seleccionada"
            locale="es-ES"
            placeholder="Seleccione una fecha"
            ref="fechaIngreso"
            size="sm"
            v-model="formFiltro.campos.fechaIngreso.value"></b-form-datepicker>
        </b-form-group>
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          label="Fecha Egreso"
          label-for="fechaEgreso"
          id="group-fechaEgreso">
          <b-form-datepicker
            :date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
            :disabled="formFiltro.campos.fechaEgreso.disabled"
            :min="formFiltro.campos.fechaEgreso.min"
            id="fechaEgreso"
            label-help="Use las teclas del cursor para navegar por las fechas del calendario"
            label-no-date-selected="Ninguna fecha seleccionada"
            locale="es-ES"
            placeholder="Seleccione una fecha"
            ref="fechaEgreso"
            size="sm"
            v-model="formFiltro.campos.fechaEgreso.value"></b-form-datepicker>
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
          <b-card class="text-left card-empleados">
            <b-card-text>
              <span class="titulo">TOTAL DE EMPLEADOS</span>
            </b-card-text>
            <b-card-text>
              <span class="monto">{{ totales.empleados }}</span>
            </b-card-text>
          </b-card>
        </b-col>
        <b-col cols="12" md="6" lg="4" class="wrapper-btn-generar-excel">
          <download-excel :data="tabla.registros" :name="'empleados.xls'">
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
        <template v-slot:cell(estatus)="data">
          <b-badge :variant="data.item.variante">{{ data.item.estatus }}</b-badge>
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
  @import '../../less/reportes/empleados.less';
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
              cargos:{
                disabled: true,
                listado: [],
                value: []
              },
              codigo:{
                disabled: true,
                value: null
              },
              divisiones:{
                disabled: true,
                listado: [],
                value: []
              },
              empleado: {
                disabled: true,
                value: null
              },
              estatus: {
                disabled: true,
                listado: [],
                value: null
              },
              fechaIngreso: {
                disabled: true,
                value: null
              },
              fechaEgreso: {
                disabled: true,
                min: null,
                value: null
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
            registros: []
          },
          totales: {
            empleados: 0
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

        axios.get('/dataRepEmpleados')
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.tabla.encabezado = [
              { key: 'numero', label: '#' },
              { key: 'codigo', label: 'Código' },
              { key: 'empleado', label: 'Empleado' },
              { key: 'cargo', label: 'Cargo' },
              { key: 'division', label: 'División' },
              { key: 'fecha_ingreso', label: 'Fecha Ingreso' },
              { key: 'fecha_egreso', label: 'Fecha Egreso' },
              { key: 'estatus', label: 'Estatus' }
            ];

            self.tabla.registros = self.registroTabla(response.data.empleados);

            if(response.data.empleados.length === 0){

              let mensaje = "No hay empleados";
              self.mostrarAlert(self.tabla.alert, true, "warning", mensaje, false, false, 0);

            }

            self.formFiltro.campos.cargos.listado = response.data.cargos;
            self.formFiltro.campos.divisiones.listado = response.data.divisiones;
            self.formFiltro.campos.estatus.listado = response.data.estatus;

            self.totales.empleados = (response.data.totales.empleados) ? response.data.totales.empleados : 0;

            self.tabla.paginador.paginar = response.data.paginar;
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

            self.formFiltro.campos.cargos.disabled = false;
            self.formFiltro.campos.codigo.disabled = false;
            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.campos.empleado.disabled = false;
            self.formFiltro.campos.estatus.disabled = false;
            self.formFiltro.campos.fechaIngreso.disabled = false;
            self.formFiltro.campos.fechaEgreso.disabled = false;
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

            var variante = "";
            switch(item.id_estatus){

              case 1: variante = "success"; break;
              case 2: variante = "danger"; break;
              default: variante = "primary";

            }

            const data = {
              numero: (i + 1),
              codigo: item.codigo,
              empleado: item.empleado,
              cargo: item.cargo,
              division: item.division,
              fecha_ingreso: item.fecha_ingreso,
              fecha_egreso: item.fecha_egreso,
              estatus: item.estatus,
              variante: variante
            };

            registros.push(data);

          });

          return registros;

        },
        buscar: function(){

          self.formFiltro.campos.cargos.disabled = true;
          self.formFiltro.campos.codigo.disabled = true;
          self.formFiltro.campos.divisiones.disabled = true;
          self.formFiltro.campos.empleado.disabled = true;
          self.formFiltro.campos.estatus.disabled = true;
          self.formFiltro.campos.fechaIngreso.disabled = true;
          self.formFiltro.campos.fechaEgreso.disabled = true;

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

          //Evaluamos como filtraremos los cargos
          if(self.formFiltro.campos.cargos.value.length === 0 && self.formFiltro.campos.cargos.listado.length > 1){
            var param_cargos = null;
          }else if(self.formFiltro.campos.cargos.value.length > 0){

            var param_cargos = [];
            self.formFiltro.campos.cargos.value.forEach((cargo, index) => {
              param_cargos.push({id: cargo.id});
            });

          }else if(self.formFiltro.campos.cargos.value.length === 0 && self.formFiltro.campos.cargos.listado.length === 1){
            var param_cargos = self.formFiltro.campos.cargos.listado[0].id;
          }else{
            var param_cargos = null;
          }

          //Obtenemos los valores
          let desde = (self.tabla.paginador.pagina - 1) * self.tabla.paginador.paginar;
          let parametros = {
            cargos: param_cargos,
            codigo: self.formFiltro.campos.codigo.value,
            desde: desde,
            divisiones: param_divisiones,
            empleado: self.formFiltro.campos.empleado.value,
            estatus: self.formFiltro.campos.estatus.value,
            fechaIngreso: self.formFiltro.campos.fechaIngreso.value,
            fechaEgreso: self.formFiltro.campos.fechaEgreso.value,
            paginar: self.tabla.paginador.paginar
          };

          //Se utiliza el metodo get para su busqueda y se envian con los parametros
          axios.get('/buscarEmpleados', {params: parametros})
          .then(function (response) {

            self.formFiltro.campos.cargos.disabled = false;
            self.formFiltro.campos.codigo.disabled = false;
            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.campos.empleado.disabled = false;
            self.formFiltro.campos.estatus.disabled = false;
            self.formFiltro.campos.fechaIngreso.disabled = false;
            self.formFiltro.campos.fechaEgreso.disabled = false;

            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.filtrar.disabled = false;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.btn.limpiarFiltro.disabled = false;

            // Se le asigna los valores a las variables
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

            self.totales.empleados = (response.data.totales.empleados) ? response.data.totales.empleados : 0;

            self.tabla.registros = self.registroTabla(response.data.empleados);

          }).catch(error => {

            self.formFiltro.campos.cargos.disabled = false;
            self.formFiltro.campos.codigo.disabled = false;
            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.campos.empleado.disabled = false;
            self.formFiltro.campos.estatus.disabled = false;
            self.formFiltro.campos.fechaIngreso.disabled = false;
            self.formFiltro.campos.fechaEgreso.disabled = false;
            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.filtrar.disabled = false;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.btn.limpiarFiltro.disabled = false;

          });

        },
        limpiarFiltro: function(){

          self.formFiltro.campos.cargos.value = [];
          self.formFiltro.campos.codigo.value = null;
          self.formFiltro.campos.divisiones.value = [];
          self.formFiltro.campos.empleado.value = null;
          self.formFiltro.campos.fechaIngreso.value = null;
          self.formFiltro.campos.fechaEgreso.min = null;
          self.formFiltro.campos.fechaEgreso.value = null;
          self.formFiltro.campos.estatus.value = null;
          self.buscar();

        }

      }
  }

</script>
