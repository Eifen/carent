<template>
  <b-row>

    <b-col cols=12>
      <h5 v-if="formFiltro.mostrar">Filtros de Búsqueda</h5>
      <b-form class="row" v-if="formFiltro.mostrar">
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          label="División"
          label-for="division"
          id="group-division">
          <select @change="empleadosDivision"
                  aria-describedby="divisionHelp"
                  class="form-control form-control-sm"
                  id="division"
                  data-validar="true"
                  v-bind:disabled="formFiltro.campos.divisiones.disabled"
                  v-model="formFiltro.campos.divisiones.value">
            <option :value="null" selected>Seleccione...</option>
            <option v-bind:value="division.id" v-for="division in formFiltro.campos.divisiones.listado">{{ division.descripcion }}</option>
          </select>
        </b-form-group>
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          label="Empleado"
          label-for="empleado"
          id="group-empleado">
          <select aria-describedby="empleadoHelp"
                  class="form-control form-control-sm"
                  id="empleado"
                  data-validar="true"
                  v-bind:disabled="formFiltro.campos.empleados.disabled"
                  v-model="formFiltro.campos.empleados.value">
            <option :value="null" selected>Seleccione...</option>
            <option v-bind:value="empleado.id" v-for="empleado in formFiltro.campos.empleados.listado">{{ empleado.nombre }}</option>
          </select>
        </b-form-group>
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          label="Fecha Desde"
          label-for="fechaDesde"
          id="group-fechaDesde">
          <b-form-datepicker
            :disabled="formFiltro.campos.fechaDesde.disabled"
            :locale="'es-VE'"
            :max="formFiltro.campos.fechaDesde.maxValue"
            locale="es"
            size="sm"
            v-bind="formFiltro.campos.fechaDesde.labels['es-VE'] || {}"
            v-model="formFiltro.campos.fechaDesde.value"></b-form-datepicker>
        </b-form-group>
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          label="Fecha Hasta"
          label-for="fechaHasta"
          id="group-fechaHasta">
          <b-form-datepicker
            :disabled="formFiltro.campos.fechaHasta.disabled"
            :max="formFiltro.campos.fechaHasta.maxValue"
            locale="es"
            size="sm"
            v-model="formFiltro.campos.fechaHasta.value"></b-form-datepicker>
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
          <b-card class="text-left card-proyectos">
            <b-card-text>
              <span class="titulo">TOTAL DE PROYECTOS/CLIENTES</span>
            </b-card-text>
            <b-card-text>
              <span class="monto">{{ totales.proyectos }}</span>
            </b-card-text>
          </b-card>
        </b-col>
        <b-col cols="12" md="6" lg="4" class="wrapper-btn-generar-excel">
          <download-excel :data="tabla.registrosExcel" :name="'clientes.xls'">
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
  @import '../../less/reportes/totalHorasEmp.less';
</style>

<script>

  import axios from 'axios';
  import alert from '../components/alert.vue';
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
              divisiones: {
                disabled: true,
                listado: [],
                value: null
              },
              empleados: {
                disabled: true,
                listado: [],
                value: null
              },
              fechaDesde: {
                disabled: true,
                labels: {
                  'es-VE': {
                    weekdayHeaderFormat: 'narrow',
                    labelPrevDecade: 'العقد السابق',
                    labelPrevYear: 'العام السابق',
                    labelPrevMonth: 'Mes Anterior',
                    labelCurrentMonth: 'الشهر الحالي',
                    labelNextMonth: 'الشهر المقبل',
                    labelNextYear: 'العام المقبل',
                    labelNextDecade: 'العقد القادم',
                    labelToday: 'اليوم',
                    labelSelected: 'التاريخ المحدد',
                    labelNoDateSelected: 'لم يتم اختيار تاريخ',
                    labelCalendar: 'التقويم',
                    labelNav: 'الملاحة التقويم',
                    labelHelp: 'استخدم مفاتيح المؤشر للتنقل في التواريخ'
                  }
                },
                maxValue: "",
                minValue: "",
                value: null
              },
              fechaHasta:{
                disabled: true,
                maxValue: "",
                minValue: "",
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
            registros: [],
            registrosExcel: []
          },
          totales: {
            proyectos: 0
          }
        };
      },
      components: {
        alert,
        "downloadExcel": JsonExcel
      },
      beforeCreate: function(){

        self = this;

        axios.get('/dataRepTotalHorasEmp')
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.formFiltro.campos.divisiones.listado = response.data.divisiones;
            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.mostrar = true;

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
        empleadosDivision: function(){

          let parametros = {
            id_division :  self.formFiltro.campos.divisiones.value
          };

          axios.get('/repTotalHorasEmpEmpleadosDivision', {params: parametros})
          .then(function (response) {

            if(response.status === 200){

              self.formFiltro.campos.empleados.listado = response.data.empleados;
              self.formFiltro.campos.empleados.disabled = false;
              self.formFiltro.campos.fechaDesde.disabled = false;

            }else{

              throw "error";

            }

          })
          .catch(error => {


          });

        },
        fechaDesdeFiltro: function(fecha_seleccionada){
          console.log("sape");
          return;

          self.formFiltro.fechaHasta.value = "";

          var fecha, fecha_desde_max;

          if(fecha_seleccionada !== ""){

            fecha = moment();
            fecha_desde_max = moment(fecha_seleccionada);

            var fecha_hasta_max = fecha;

            if(fecha_hasta_max.minute() < 30){
              self.formFiltro.fechaHasta.maxValue = fecha_hasta_max.startOf("hour").toISOString();
            }else{
              self.formFiltro.fechaHasta.maxValue = fecha_hasta_max.startOf("hour").add(30, "minutes").toISOString();
            }

            var fecha_hasta_min = fecha_desde_max;
            self.formFiltro.fechaHasta.minValue = fecha_hasta_min.add(30, "minutes").toISOString();
            self.formFiltro.fechaHasta.disabled = false;

          }else{

            fecha = fecha_desde_max = moment();

            if(fecha_desde_max.minute() < 30){
              fecha_desde_max = fecha_desde_max.startOf("hour").subtract(30, "minute");
              self.formFiltro.fechaDesde.maxValue = fecha_desde_max.toISOString();
            }else{
              fecha_desde_max =  fecha_desde_max.startOf("hour");
              self.formFiltro.fechaDesde.maxValue = fecha_desde_max.toISOString();
            }

          }

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
          const registrosExcel = [];
          datos.forEach((item, i) => {

            var variante = "";
            switch(item.id_estatus){

              case 1: variante = "success"; break;
              case 2: variante = "danger"; break;
              default: variante = "primary";

            }

            const data = {
              numero: (i + 1),
              rif: item.rif,
              razonSocial: item.cliente,
              estatus: item.estatus,
              monto_proyecto: item.moneda+' '+item.monto_proyecto_formated,
              monto_facturado: item.moneda+' '+item.monto_facturado_formated,
              monto_notas_credito: item.moneda+' '+item.monto_notas_credito_formated,
              monto_gasto: item.moneda+' '+item.monto_gasto_formated,
              monto_otros_gastos: item.moneda+' '+item.monto_otros_gastos_formated,
              proyecto: item.proyecto,
              variante: variante
            };

            const dataExcel = {
              rif: item.rif,
              razonSocial: item.cliente,
              proyecto: item.proyecto,
              estatus: item.estatus,
              moneda: item.moneda,
              monto_proyecto: item.monto_proyecto,
              monto_facturado: item.monto_facturado,
              monto_notas_credito: item.monto_notas_credito,
              monto_gasto: item.monto_gasto,
              monto_otros_gastos: item.monto_otros_gastos
            }

            registros.push(data);
            registrosExcel.push(dataExcel);

          });

          return [registros, registrosExcel];

        },
        buscar: function(){

          self.formFiltro.campos.rif.disabled = true;
          self.formFiltro.campos.proyecto.disabled = true;
          self.formFiltro.campos.razonSocial.disabled = true;
          self.formFiltro.campos.estatus.disabled = true;
          self.formFiltro.campos.monedas.disabled = true;

          self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlLoading;
          self.formFiltro.btn.filtrar.disabled = true;
          self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlLoading;
          self.formFiltro.btn.limpiarFiltro.disabled = true;

          //Obtenemos los valores
          let desde = (self.tabla.paginador.pagina - 1) * self.tabla.paginador.paginar;
          let parametros = {
            rif: self.formFiltro.campos.rif.value,
            proyecto: self.formFiltro.campos.proyecto.value,
            desde: desde,
            razonSocial: self.formFiltro.campos.razonSocial.value,
            estatus: self.formFiltro.campos.estatus.value,
            monedas: self.formFiltro.campos.monedas.value,
            paginar: self.tabla.paginador.paginar
          };

          //Se utiliza el metodo get para su busqueda y se envian con los parametros
          axios.get('/filtrarCliProy', {params: parametros})
          .then(function (response) {

            self.formFiltro.campos.rif.disabled = false;
            self.formFiltro.campos.proyecto.disabled = false;
            self.formFiltro.campos.razonSocial.disabled = false;
            self.formFiltro.campos.estatus.disabled = false;
            self.formFiltro.campos.monedas.disabled = false;

            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.filtrar.disabled = false;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.btn.limpiarFiltro.disabled = false;

            // Se le asigna los valores a las variables
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

            self.totales.proyectos = (response.data.totales.proyectos) ? response.data.totales.proyectos : 0;

            let resgitros = self.registroTabla(response.data.registros)
            self.tabla.registros = resgitros[0];
            self.tabla.registrosExcel = resgitros[1];

          }).catch(error => {

            self.formFiltro.campos.rif.disabled = false;
            self.formFiltro.campos.proyecto.disabled = false;
            self.formFiltro.campos.razonSocial.disabled = false;
            self.formFiltro.campos.estatus.disabled = false;
            self.formFiltro.campos.monedas.disabled = false;
            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.filtrar.disabled = false;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.btn.limpiarFiltro.disabled = false;

          });

        },
        limpiarFiltro: function(){

          self.formFiltro.campos.rif.value = null;
          self.formFiltro.campos.proyecto.value = null;
          self.formFiltro.campos.razonSocial.value = null;
          self.formFiltro.campos.estatus.value = null;
          self.formFiltro.campos.monedas.value = null;
          self.buscar();

        }

      }
  }

</script>
