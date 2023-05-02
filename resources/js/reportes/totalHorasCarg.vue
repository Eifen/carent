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
                       :multiple="false"
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
                       :multiple="false"
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

    <b-col cols="12" v-if="tabla.registros.length != 0">
      <b-row align-h="end" v-cloak v-if="formFiltro.mostrar">
        <b-col cols="12" md="6" lg="4">
          <b-card class="text-left card-horas">
            <b-card-text>
              <span class="titulo">CANTIDAD DE HORAS DE TRABAJO</span>
            </b-card-text>
            <b-card-text>
              <span class="monto">{{ maximo_horas }}</span>
            </b-card-text>
          </b-card>
        </b-col>
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
        <template v-slot:cell(e)="data">
           <i v-if="data.item.eficiencia == 'Eficiente'" class="fas fa-check" style="color: #40d44a;"></i>
           <i v-if="data.item.eficiencia == 'Deficiente'" class="fas fa-times" style="color: #ce122e;"></i>
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
  @import '../../less/reportes/totalHorasCarg.less';
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
              cargos:{
                disabled: true,
                listado: [],
                value: []
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
          maximo_horas: 0,
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

        axios.get('/dataRepTotalHorasCarg')
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){

            let mensaje = "Si quiere un reporte de todas las divisiones y nombres solo coloque el intervalo de fechas.";
              self.mostrarAlert(self.tabla.alert, true, "warning", mensaje, false, false, 0);

            self.maximo_horas = parseInt(response.data.maximo_horas);
            self.formFiltro.campos.cargos.listado = response.data.cargos;
            self.formFiltro.campos.divisiones.listado = response.data.divisiones;

            self.tabla.paginador.paginar = response.data.paginar;
            self.tabla.paginador.numPaginas = 1;
            self.tabla.paginador.max = 1;

            self.formFiltro.campos.cargos.disabled = false;
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
        registroTabla(datos){
          let registros = [];
          let contadorRegistros = 1

          for (const division in datos) {
            for (const user in datos[division]) {
              const data = {
                nombre: datos[division][user].nombre,
                usuario_cargo: datos[division][user].usuario_cargo,
                nivel: datos[division][user].grupo_nivel,
                usuario_division: datos[division][user].usuario_division,
                eficiencia: (datos[division][user].eficiencia ? "Eficiente" : "Deficiente"),
                total_horas_cargables: datos[division][user].total_horas_cargables,
                porcen_horas_cargables: parseFloat(datos[division][user].porcen_horas_cargables.toFixed(2)).toLocaleString("es-ES"),
                total_horas_no_cargables: datos[division][user].total_horas_no_cargables,
                porcen_horas_no_cargables: parseFloat(datos[division][user].porcen_horas_no_cargables.toFixed(2)).toLocaleString("es-ES"),
                total_horas: datos[division][user].total_horas,
                porcen_carga_total: parseFloat(datos[division][user].porcen_carga_total.toFixed(2)).toLocaleString("es-ES"),
                total_horas_exceso_admin: parseFloat(datos[division][user].total_exceso_administrativo.toFixed(0)).toLocaleString("es-ES"),
                porcen_exceso_admin: parseFloat(datos[division][user].exceso_per_administrativo.toFixed(2)).toLocaleString("es-ES"),
                total_horas_exceso_proy: parseFloat(datos[division][user].total_exceso_proyectos.toFixed(0)).toLocaleString("es-ES"),
                porcen_exceso_proy: parseFloat(datos[division][user].exceso_per_proyectos.toFixed(2)).toLocaleString("es-ES"),
                ref_usuario_total: datos[division][user].ref_usuario_total,
                fecha_ingreso: datos[division][user].fecha_ingreso,
                fecha_egreso: datos[division][user].fecha_egreso,
                orden: datos[division][user].orden,
                //Si es verdadero colocamos un check, falso un cross
                //Fecha del intervalo
                fecha_desde: datos[division][user].fecha_desde,
                fecha_hasta: datos[division][user].fecha_hasta
              }

              registros.push(data);

              contadorRegistros++;
            }
          }

          //TODO orden jerarquia
          registros.sort((ordenA,ordenB) =>
          {
            switch (true) {
                //Son de la misma division pero el primero es mayor que el segundo
                case (ordenA.orden > ordenB.orden) && (ordenA.usuario_division === ordenB.usuario_division):
                    return 1;
                //Son de la misma division pero el primero es mayor que el segundo
                case (ordenA.orden < ordenB.orden) && (ordenA.usuario_division === ordenB.usuario_division):
                    return -1;
            }

            return 0;
          })
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

          self.formFiltro.campos.cargos.disabled = true;
          self.formFiltro.campos.divisiones.disabled = true;
          self.formFiltro.campos.empleado.disabled = true;
          self.formFiltro.campos.fechaDesde.disabled = true;
          self.formFiltro.campos.fechaHasta.disabled = true;

          self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlLoading;
          self.formFiltro.btn.filtrar.disabled = true;
          self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlLoading;
          self.formFiltro.btn.limpiarFiltro.disabled = true;

          //Evaluamos como filtraremos la division
          var param_divisiones = (self.formFiltro.campos.divisiones.value !== null
                                  ? [ self.formFiltro.campos.divisiones.value.id ]
                                  : 0);
          if(typeof param_divisiones[0] === 'undefined') param_divisiones = 0;

          //Evaluamos como filtraremos el cargo
          var param_cargos = (self.formFiltro.campos.cargos.value !== null
                                  ? [ self.formFiltro.campos.cargos.value.id ]
                                  : 0);
          if(typeof param_cargos[0] === 'undefined') param_cargos = 0;

          //Obtenemos los valores
          let desde = (self.tabla.paginador.pagina - 1) * self.tabla.paginador.paginar;
          let parametros = {
            cargos: param_cargos,
            desde: desde,
            divisiones: param_divisiones,
            empleado: self.formFiltro.campos.empleado.value,
            fecha_desde: self.formFiltro.campos.fechaDesde.value,
            fecha_hasta: self.formFiltro.campos.fechaHasta.value,
            paginar: self.tabla.paginador.paginar
          };

          //Se utiliza el metodo get para su busqueda y se envian con los parametros
          axios.get('/buscarRepTotalHorasCarg', {params: parametros})
          .then(function (response) {
            console.log(response)
            self.formFiltro.campos.cargos.disabled = false;
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
            self.maximo_horas = parseInt(response.data.maximo_horas);
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

            self.tabla.encabezado = [
              { key: 'nombre', label: 'Nombre y Apellido' },
              { key: 'usuario_cargo', label: "Cargo"},
              { key: 'usuario_division', label: "Division"},
              'e',
              //Proyectos
              { key: 'total_horas_cargables', label: 'Horas Proy' },
              { key: 'porcen_horas_cargables', label: '% Proy' },
              //Administrativos
              { key: 'total_horas_no_cargables', label: 'Horas Admon' },
              { key: 'porcen_horas_no_cargables', label: '% Horas Admon' },
              //Total Horas
              { key: 'total_horas', label: 'Total horas' },
              { key: 'porcen_carga_total', label: '% Carga total' },
              //Exceso
              { key: 'porcen_exceso_admin', label: '% Exceso Admon'},
              { key: 'porcen_exceso_proy', label: '% Exceso Proy'},
              { key: 'ref_usuario_total', label: 'Ref Total'},
              //Exceso
              //{ key: 'exceso_cargables', label: 'Exceso carga cliente' },
              //{ key: 'exceso_no_cargables', label: 'Exceso carga no cliente' },
              //Fecha
              { key: 'fecha_ingreso', label: 'Fecha Ingreso'},
              { key: 'fecha_egreso', label: 'Fecha Egreso'}

            ];

            self.tabla.registros = self.registroTabla(response.data.totales);

            if(response.data.cargos != "NoSelect")
            {
              self.tabla.registros = self.tabla.registros.filter(usuario => usuario.usuario_cargo == response.data.cargos)
            }

            console.log(self.tabla.registros)

            if(response.data.totales.length === 0){

              let mensaje = "No existen registro de horas para este filtrado";
              self.mostrarAlert(self.tabla.alert, true, "warning", mensaje, false, false, 0);

            }

          }).catch(error => {

            self.formFiltro.campos.cargos.disabled = false;
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

          self.formFiltro.campos.cargos.value = [];
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
