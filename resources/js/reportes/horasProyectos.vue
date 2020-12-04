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
              divisiones:{
                disabled: true,
                listado: [],
                value: []
              },
              empleado: {
                disabled: true,
                value: ""
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
          valor: 1
        };
      },
      components: {
        alert,
        "downloadExcel": JsonExcel,
        Multiselect
      },
      beforeCreate: function(){

        self = this;

        axios.get('/dataRepHorasProyectos')
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.tabla.encabezado = [
              { key: 'numero', label: '#' },
              { key: 'proyecto', label: 'Proyecto' },
              { key: 'cliente', label: 'cliente' },
              { key: 'empleado', label: 'Empleado' },
              { key: 'division', label: 'Division' },
              { key: 'cargo', label: 'Cargo' },
              { key: 'horas_trabajadas', label: 'Horas Cargadas' },
              { key: 'horas_contratadas', label: 'Horas Contratadas' },
              { key: 'monto', label: 'Monto' },
            ];

            for (var i = 0; i < response.data.horasProyectos.length; i++) {
              if (self.valor === 1) {
                self.tabla.horasProyecto.push({proyecto: response.data.horasProyectos[i].proyecto, cliente: response.data.horasProyectos[i].cliente, empleado: "", division:"", cargo: "", horas_trabajadas: "", horas_contratadas: response.data.horasProyectos[i].horas_contratadas, monto: response.data.horasProyectos[i].monto});
                self.valor = 0;
              }else if(response.data.horasProyectos[i].id_proyecto != response.data.horasProyectos[i-1].id_proyecto){
                self.tabla.horasProyecto.push({proyecto: response.data.horasProyectos[i].proyecto, cliente: response.data.horasProyectos[i].cliente, empleado: "", division:"", cargo: "", horas_trabajadas: "",horas_contratadas: response.data.horasProyectos[i].horas_contratadas, monto: response.data.horasProyectos[i].monto});
              }
              self.tabla.horasProyecto.push({proyecto: "", cliente: "", empleado: response.data.horasProyectos[i].empleado, division:response.data.horasProyectos[i].division, cargo: response.data.horasProyectos[i].cargo, horas_trabajadas: response.data.horasProyectos[i].horas_trabajadas, horas_contratadas: "", monto: ""});
            }

            self.tabla.registros = self.registroTabla(self.tabla.horasProyecto);

            if(self.tabla.horasProyecto.length === 0){

              let mensaje = "No hay proyectos";
              self.mostrarAlert(self.tabla.alert, true, "warning", mensaje, false, false, 0);

            }

            self.formFiltro.campos.cargos.listado = response.data.cargos;
            self.formFiltro.campos.divisiones.listado = response.data.divisiones;

            self.tabla.paginador.paginar = response.data.paginar;
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

            self.formFiltro.campos.cargos.disabled = false;
            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.campos.cliente.disabled = false;
            self.formFiltro.campos.empleado.disabled = false;
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
              empleado: item.empleado,
              division: item.division,
              cargo: item.cargo,
              horas_trabajadas: item.horas_trabajadas,
              horas_contratadas: item.horas_contratadas,
              monto: item.monto
            };

            registros.push(data);

          });

          return registros;

        },
        buscar: function(){

          self.tabla.horasProyecto = [];
          self.valor = 1;
          self.formFiltro.campos.cargos.disabled = true;
          self.formFiltro.campos.cliente.disabled = true;
          self.formFiltro.campos.divisiones.disabled = true;
          self.formFiltro.campos.empleado.disabled = true;
          self.formFiltro.campos.proyecto.disabled = true;

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
            cliente: self.formFiltro.campos.cliente.value,
            desde: desde,
            divisiones: param_divisiones,
            empleado: self.formFiltro.campos.empleado.value,
            proyecto: self.formFiltro.campos.proyecto.value,
            paginar: self.tabla.paginador.paginar
          };

          //Se utiliza el metodo get para su busqueda y se envian con los parametros
          axios.get('/buscarHorasProyectos', {params: parametros})
          .then(function (response) {

            for (var i = 0; i < response.data.horasProyectos.length; i++) {
              if (self.valor === 1) {
                self.tabla.horasProyecto.push({proyecto: response.data.horasProyectos[i].proyecto, cliente: response.data.horasProyectos[i].cliente, empleado: "", division:"", cargo: "", horas_trabajadas: "", horas_contratadas: response.data.horasProyectos[i].horas_contratadas, monto: response.data.horasProyectos[i].monto});
                self.valor = 0;
              }else if(response.data.horasProyectos[i].id_proyecto != response.data.horasProyectos[i-1].id_proyecto){
                self.tabla.horasProyecto.push({proyecto: response.data.horasProyectos[i].proyecto, cliente: response.data.horasProyectos[i].cliente, empleado: "", division:"", cargo: "", horas_trabajadas: "",horas_contratadas: response.data.horasProyectos[i].horas_contratadas, monto: response.data.horasProyectos[i].monto});
              }
              self.tabla.horasProyecto.push({proyecto: "", cliente: "", empleado: response.data.horasProyectos[i].empleado, division:response.data.horasProyectos[i].division, cargo: response.data.horasProyectos[i].cargo, horas_trabajadas: response.data.horasProyectos[i].horas_trabajadas, horas_contratadas: "", monto: ""});
            }
               
            
            
            self.tabla.registros = self.registroTabla(self.tabla.horasProyecto);

            if(self.tabla.horasProyecto.length === 0){

              let mensaje = "No hay proyectos";
              self.mostrarAlert(self.tabla.alert, true, "warning", mensaje, false, false, 0);

            }

            self.formFiltro.campos.cargos.disabled = false;
            self.formFiltro.campos.cliente.disabled = false;
            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.campos.empleado.disabled = false;
            self.formFiltro.campos.proyecto.disabled = false;

            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.filtrar.disabled = false;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.btn.limpiarFiltro.disabled = false;

            // Se le asigna los valores a las variables
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

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

          self.formFiltro.campos.cargos.value = [];
          self.formFiltro.campos.cliente.value = null;
          self.formFiltro.campos.divisiones.value = [];
          self.formFiltro.campos.empleado.value = null;
          self.formFiltro.campos.proyecto.value = null;
          self.buscar();

        },

      }
  }

</script>
