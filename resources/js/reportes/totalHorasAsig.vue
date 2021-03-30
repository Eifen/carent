<template>
  <b-row>

    <b-col cols=12>
      <h5 v-if="formFiltro.mostrar">Filtros de Búsqueda</h5>
      <b-form class="row" v-if="formFiltro.mostrar">
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
          :invalid-feedback="formFiltro.campos.fechaDesde.invalidFeedback"
          class="form-group col-12 col-sm-6 col-md-4"
          label="Fecha Desde"
          label-for="fechaDesde"
          id="group-fechaDesde">
          <b-form-datepicker
            @input="fechaDesdeFiltro"
            :disabled="formFiltro.campos.fechaDesde.disabled"
            :locale="'es-VE'"
            :max="formFiltro.campos.fechaDesde.maxValue"
            :state="formFiltro.campos.fechaDesde.state"
            locale="es"
            size="sm"
            ref="fechaDesde"
            v-bind="formFiltro.traduccionCalendario.labels['es-VE'] || {}"
            v-model="$v.formFiltro.campos.fechaDesde.value.$model"></b-form-datepicker>
        </b-form-group>
        <b-form-group
          :invalid-feedback="formFiltro.campos.fechaHasta.invalidFeedback"
          class="form-group col-12 col-sm-6 col-md-4"
          label="Fecha Hasta"
          label-for="fechaHasta"
          id="group-fechaHasta">
          <b-form-datepicker
            @input="limpiarMensajeError(formFiltro.campos.fechaHasta)"
            :disabled="formFiltro.campos.fechaHasta.disabled"
            :locale="'es-VE'"
            :max="formFiltro.campos.fechaHasta.maxValue"
            :min="formFiltro.campos.fechaHasta.minValue"
            :state="formFiltro.campos.fechaHasta.state"
            locale="es"
            size="sm"
            ref="fechaHasta"
            v-bind="formFiltro.traduccionCalendario.labels['es-VE'] || {}"
            v-model="$v.formFiltro.campos.fechaHasta.value.$model"></b-form-datepicker>
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
          <download-excel :data="tabla.registros" :name="'RepTotalHorasCarg.xls'">
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
  @import '../../less/reportes/totalHorasAsig.less';
</style>

<script>

  import axios from 'axios';
  import alert from '../components/alert.vue';
  import Multiselect from 'vue-multiselect';
  import JsonExcel from "vue-json-excel";
  import Vuelidate from 'vuelidate';
  import { required, minLength, minValue } from 'vuelidate/lib/validators';
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
              divisiones:{
                disabled: true,
                listado: [],
                value: []
              },
              empleado: {
                disabled: true,
                value: null
              },
              fechaDesde: {
                disabled: true,
                invalidFeedback: '',
                maxValue: "",
                state: null,
                value: null
              },
              fechaHasta:{
                disabled: true,
                invalidFeedback: '',
                maxValue: "",
                minValue: "",
                state: null,
                value: null
              }
            },
            mostrar : false,
            traduccionCalendario: {
              labels: {
                'es-VE': {
                  weekdayHeaderFormat: 'narrow',
                  labelPrevDecade: 'Década Anterior',
                  labelPrevYear: 'Año Anterior',
                  labelPrevMonth: 'Mes Anterior',
                  labelCurrentMonth: 'Mes Actualي',
                  labelNextMonth: 'Siguiente Mes',
                  labelNextYear: 'Siguiente Año',
                  labelNextDecade: 'Siguiente Década',
                  labelToday: 'Hoy',
                  labelSelected: 'Seleccionado',
                  labelNoDateSelected: 'Ninguna fecha seleccionada',
                  labelCalendar: 'Calendario',
                  labelNav: 'Navegación de calendario',
                  labelHelp: 'Use las teclas del cursor para navegar por las fechas del calendario'
                }
              },
              maxValue: "",
              minValue: "",
              value: null
            }
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
          horas_asignadas: {
            empleados: 0
          }
        };
      },
      components: {
        alert,
        "downloadExcel": JsonExcel,
        Multiselect
      },
      validations: {
        formFiltro:{
          campos:{
            fechaDesde: {
              value: {
                required
              }
            },
            fechaHasta: {
              value: {
                required
              }
            }
          }
        }
      },
      beforeCreate: function(){

        self = this;

        axios.get('/dataRepTotalHorasAsig')
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.tabla.encabezado = [
              { key: 'numero', label: '#' },
              { key: 'nombre', label: 'Empleado' },
              { key: 'division', label: 'Division' },
              { key: 'horas_cargadas_fecha', label: "Horas Cargadas. Desde: "
              +response.data.fecha_desde+" Hasta: "+response.data.fecha_hasta },
              { key: 'tota_horas_cargadas', label: 'Total Horas Cargadas' },
              { key: 'total_horas_asignadas', label: 'Total Horas Asignadas' },
              { key: 'porcentaje', label: '% Horas Cargadas' },

            ];

            self.tabla.registros = self.registroTabla(response.data.horas_asignadas);

            if(response.data.horas_asignadas.length === 0){

              let mensaje = "No hay carga de hora de los empleados";
              self.mostrarAlert(self.tabla.alert, true, "warning", mensaje, false, false, 0);

            }

            self.formFiltro.campos.divisiones.listado = response.data.divisiones;        

            self.tabla.paginador.paginar = response.data.paginar;
            self.tabla.paginador.numPaginas = 1;
            self.tabla.paginador.max = 1;

            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.campos.empleado.disabled = false;
            self.formFiltro.campos.fechaDesde.disabled = false;
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
      created: function(){

        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        self.formFiltro.campos.fechaDesde.maxValue = now;
        self.formFiltro.campos.fechaHasta.maxValue = now;

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
              nombre: item.nombre,
              division: item.division,
              horas_cargadas_fecha: item.horas_cargadas_fecha,
              tota_horas_cargadas: item.tota_horas_cargadas,
              total_horas_asignadas: item.total_horas_asignadas,
              porcentaje: item.porcentaje,
            };

            registros.push(data);

          });

          return registros;

        },
        fechaDesdeFiltro: function(fecha_seleccionada){

          self.limpiarMensajeError(self.formFiltro.campos.fechaDesde)

          self.formFiltro.campos.fechaHasta.value = null;
          self.formFiltro.campos.fechaHasta.minValue = fecha_seleccionada;
          self.formFiltro.campos.fechaHasta.disabled = false;

        },
        limpiarMensajeError: function(objeto){

          objeto.state = null;
          objeto.invalidFeedback = "";

        },
        buscar: function(){

          self.formFiltro.campos.divisiones.disabled = true;
          self.formFiltro.campos.empleado.disabled = true;
          self.formFiltro.campos.fechaDesde.disabled = true;
          self.formFiltro.campos.fechaHasta.disabled = true;

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
            desde: desde,
            divisiones: param_divisiones,
            empleado: self.formFiltro.campos.empleado.value,
            fecha_desde: self.formFiltro.campos.fechaDesde.value,
            fecha_hasta: self.formFiltro.campos.fechaHasta.value,
            paginar: self.tabla.paginador.paginar
          };

          //Se utiliza el metodo get para su busqueda y se envian con los parametros
          axios.get('/buscarTotalHorasAsig', {params: parametros})
          .then(function (response) {

            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.campos.empleado.disabled = false;
            self.formFiltro.campos.fechaDesde.disabled = false;
            self.formFiltro.campos.fechaHasta.disabled = false;
            if (self.formFiltro.campos.fechaDesde.value === null) {
              self.formFiltro.campos.fechaHasta.disabled = true;
            }
            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.filtrar.disabled = false;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.btn.limpiarFiltro.disabled = false;

            // Se le asigna los valores a las variables
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

            self.tabla.encabezado = [
              { key: 'numero', label: '#' },
              { key: 'nombre', label: 'Empleado' },
              { key: 'division', label: 'Division' },
              { key: 'horas_cargadas_fecha', label: "Horas Cargadas. Desde: "
              +response.data.fecha_desde+" Hasta: "+response.data.fecha_hasta },
              { key: 'tota_horas_cargadas', label: 'Total Horas Cargadas' },
              { key: 'total_horas_asignadas', label: 'Total Horas Asignadas' },
              { key: 'porcentaje', label: '% Horas Cargadas' },

            ];


            self.tabla.registros = self.registroTabla(response.data.horas_asignadas);

          }).catch(error => {

            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.campos.empleado.disabled = false;
            self.formFiltro.campos.fechaDesde.disabled = false;
            self.formFiltro.campos.fechaHasta.disabled = false;
            
            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.filtrar.disabled = false;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.btn.limpiarFiltro.disabled = false;

          });

        },
        limpiarFiltro: function(){

          self.formFiltro.campos.divisiones.value = [];
          self.formFiltro.campos.empleado.value = null;
          self.formFiltro.campos.fechaDesde.value = null;
          self.formFiltro.campos.fechaHasta.disabled = true;
          self.formFiltro.campos.fechaHasta.value = null;
          self.buscar();

        }

      }
  }

</script>
