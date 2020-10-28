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
          label="Fecha Desde"
          label-for="fechaDesde"
          id="group-fechaDesde">
          <b-form-datepicker
            @input="fecha_hasta"
            :date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
            :disabled="formFiltro.campos.fechaDesde.disabled"
            :max="formFiltro.campos.fechaDesde.max"
            id="fechaDesde"
            label-help="Use las teclas del cursor para navegar por las fechas del calendario"
            label-no-date-selected="Ninguna fecha seleccionada"
            locale="es-ES"
            placeholder="Seleccione una fecha"
            ref="fechaDesde"
            size="sm"
            v-model="formFiltro.campos.fechaDesde.value"></b-form-datepicker>
        </b-form-group>
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          label="Fecha Hasta"
          label-for="fechaHasta"
          id="group-fechaHasta">
          <b-form-datepicker
            :date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
            :disabled="formFiltro.campos.fechaHasta.disabled"
            :min="formFiltro.campos.fechaHasta.min"
            id="fechaHasta"
            label-help="Use las teclas del cursor para navegar por las fechas del calendario"
            label-no-date-selected="Ninguna fecha seleccionada"
            locale="es-ES"
            placeholder="Seleccione una fecha"
            ref="fechaHasta"
            size="sm"
            v-model="formFiltro.campos.fechaHasta.value"></b-form-datepicker>
        </b-form-group>
        <b-form-group class="col-12 col-sm-6 col-md-4">
          <label>&nbsp;</label>
          <b-button
            :disabled="formFiltro.btn.filtrar.disabled"
            block
            size="sm"
            v-html="formFiltro.btn.filtrar.html"
            v-on:click="buscar"
            variant="primary"></b-button>
        </b-form-group>
        <b-form-group class="col-12 col-sm-6 col-md-4">
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
          <b-card class="text-left card-monto-contratado">
            <b-card-text>
              <span class="titulo">TOTAL DE HORAS TRABAJADAS</span>
            </b-card-text>
            <b-card-text>
              <span class="monto">{{ totales.horasTrabajadas }}</span>
            </b-card-text>
          </b-card>
        </b-col>
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

form{
  background-color: white;
  border:1px solid rgba(0,0,0,0.13);
  border-radius: 3px;
  margin-bottom: 30px;
  margin-left:0px !important;
  margin-right:0px !important;
  padding: 15px;
  transition: all .3s;

  &:hover{
    box-shadow: 0px 5px 5px 0px rgba(0, 0, 0, 0.18);
  }

  > div{

    &.form-group{

      label{
        color:#091F40;
      }

      .invalid-feedback{
        display: block !important;
      }

      .form-control{
        border-radius: 2px;

        &.error{
          border-color: #dc3545;
        }

        &:disabled{
          cursor: not-allowed;
        }

      }

      .multiselect{

        &.multiselect--disabled{
          min-height: 32px;
        }

        .multiselect__select{
          height: 32px;
          max-height: 50px;
        }

        .multiselect__tags{
          height: 32px;
          max-height: 50px;
          min-height: 32px;
          padding: 2px 40px 0 8px;

          .multiselect__single{
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
          }

        }

      }// Fin .multiselect

    }// Fin .form-group

  }

}// Fin form

.table{
  background-color: white;
  border:1px solid rgba(0,0,0,0.13);
  border-radius: 3px;
  margin-bottom: 50px;
  padding: 15px;
  transition: all .3s;

  &:hover{
    box-shadow: 0px 5px 5px 0px rgba(0, 0, 0, 0.18);
  }

  thead{

    tr{

      th{
        background-color: #0069D9;
        color:white;
        text-align: center;
        vertical-align: middle;
      }

    }

  }// Fin thead

  tbody{

    tr{
      transition: all .3s;

      &:hover{
        background-color: rgba(246,168,28,.5);
      }

      th{
        text-align: center;
      }

      td{
        text-align: center;

        a{
          color: #212529 !important;
        }

        .fas,
        .far{

          &:hover{
            cursor: pointer;
          }

        }

      }// Fin td

    }// Fin tr

  }// Fin tbody

  tfoot{

    tr{

      td{

        > div{
          display: table;
          margin-left: auto;
          margin-right: auto;

          > div{
            display: table-cell;
            padding-left: 5px;
            padding-right: 5px;
            vertical-align: middle;

            .form-control{
              max-width: 70px;
            }

            .icono{
              color: #091F40;
              font-size: 1.5rem;

              &:hover{
                color: #000000;
                cursor: pointer;
              }

            }

          }

        }

      }

    }

  }// Fin tfoot

}// Fin table

.card{
  box-shadow: 0 .15rem 1.55rem 0 rgba(58,59,69,.15);
  margin-bottom: 30px;
  transition: all .3s;

  &:hover{
    box-shadow: 0px 5px 5px 0px rgba(0, 0, 0, 0.18);
  }

  .card-body{
    padding: 0.85rem;
  }

  &.card-proyecto{
    border-left: 4px solid #091F40;

    .card-text{
      margin-bottom: 0.3rem;

      .titulo{
        color: rgba(0,0,0,0.8);
        font-weight: 600;
        text-decoration: underline;
      }

    }

  }

  &.card-monto-contratado,
  &.card-monto-facturado,
  &.card-monto-gasto,
  &.card-monto-notas-credito,
  &.card-monto-otros-gastos{
    margin-top: 20px;

    .card-text{

      &:nth-child(1){
        margin-bottom: 0px;
      }

      .titulo{
        font-size: 12px;
        font-weight: bold;
      }

    }

    .monto{
      color: rgba(0,0,0,0.8);
      font-size: 1.5rem;
      font-weight: bold;
    }

  }

  &.card-monto-contratado{
    border-left: 4px solid #36B9CC;

    .titulo{
      color: #36B9CC;
    }

  }

  &.card-monto-facturado{
    border-left: 4px solid #1CC88A;

    .titulo{
      color: #1CC88A;
    }

  }

  &.card-monto-notas-credito{

    border-left: 4px solid #FFC107;

    .titulo{
      color: #FFC107;
    }

  }

  &.card-monto-gasto,
  &.card-monto-otros-gastos{
    border-left: 4px solid #DD3D31;

    .titulo{
      color: #DD3D31;
    }

  }

}// Fin .card

.wrapper-btn-generar-excel{
  padding-top: 20px;

  > div {
    height:74%;
  }

  button{
    height: 100%;
    font-weight: bold;
  }
}

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
              cliente : {
                disabled: true,
                value: ""
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
              fechaDesde: {
                disabled: true,
                value: ""
              },
              fechaHasta: {
                disabled: true,
                min: null,
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
            registros: []
          },
          totales: {
            horasTrabajadas: 0
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

        axios.get('/dataRepHorasCargables')
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.tabla.encabezado = [
              { key: 'numero', label: '#' },
              { key: 'proyecto', label: 'Proyecto' },
              { key: 'cliente', label: 'Cliente' },
              { key: 'division', label: 'División' },
              { key: 'empleado', label: 'Empleado' },
              { key: 'cargo', label: 'Cargo' },
              { key: 'horas_trabajadas', label: 'Horas' }
            ];

            self.tabla.registros = self.registroTabla(response.data.horas);

            if(response.data.horas.length === 0){

              let mensaje = "No hay proyectos por facturar";
              self.mostrarAlert(self.tabla.alert, true, "warning", mensaje, false, false, 0);

            }

            self.formFiltro.campos.cargos.listado = response.data.cargos;
            self.formFiltro.campos.divisiones.listado = response.data.divisiones;

            self.totales.horasTrabajadas = (response.data.totales.horas_trabajadas) ? response.data.totales.horas_trabajadas : 0;

            self.tabla.paginador.paginar = response.data.paginar;
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

            self.formFiltro.campos.cargos.disabled = false;
            self.formFiltro.campos.cliente.disabled = false;
            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.campos.empleado.disabled = false;
            self.formFiltro.campos.fechaDesde.disabled = false;
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
              division: item.division,
              empleado: item.empleado,
              cargo: item.cargo,
              horas_trabajadas: item.horas_trabajadas
            };

            registros.push(data);

          });

          return registros;

        },
        buscar: function(){

          self.formFiltro.campos.cargos.disabled = true;
          self.formFiltro.campos.cliente.disabled = true;
          self.formFiltro.campos.divisiones.disabled = true;
          self.formFiltro.campos.empleado.disabled = true;
          self.formFiltro.campos.fechaDesde.disabled = true;
          self.formFiltro.campos.fechaHasta.disabled = true;
          self.formFiltro.campos.proyecto.disabled = true;

          self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlLoading;
          self.formFiltro.btn.filtrar.disabled = true;
          self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlLoading;
          self.formFiltro.btn.limpiarFiltro.disabled = true;

          //Obtenemos los valores
          let desde = (self.tabla.paginador.pagina - 1) * self.tabla.paginador.paginar;
          let parametros = {
            cargos: self.formFiltro.campos.cargos.value,
            cliente: self.formFiltro.campos.cliente.value,
            desde: desde,
            divisiones: self.formFiltro.campos.divisiones.value,
            empleado: self.formFiltro.campos.empleado.value,
            fechaDesde: self.formFiltro.campos.fechaDesde.value,
            fechaHasta: self.formFiltro.campos.fechaHasta.value,
            proyecto: self.formFiltro.campos.proyecto.value,
            paginar: self.tabla.paginador.paginar
          };

          //Se utiliza el metodo get para su busqueda y se envian con los parametros
          axios.get('/buscarHorasCargables', {params: parametros})
          .then(function (response) {

            self.formFiltro.campos.cargos.disabled = false;
            self.formFiltro.campos.cliente.disabled = false;
            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.campos.empleado.disabled = false;
            self.formFiltro.campos.fechaDesde.disabled = false;
            self.formFiltro.campos.fechaHasta.disabled = false;
            self.formFiltro.campos.proyecto.disabled = false;

            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.filtrar.disabled = false;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.btn.limpiarFiltro.disabled = false;

            // Se le asigna los valores a las variables
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

            self.totales.horasTrabajadas = (response.data.totales.horas_trabajadas) ? response.data.totales.horas_trabajadas : 0;

            self.tabla.registros = self.registroTabla(response.data.horas);

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

          self.formFiltro.campos.cargos.value = null;
          self.formFiltro.campos.cliente.value = null;
          self.formFiltro.campos.divisiones.value = null;
          self.formFiltro.campos.empleado.value = null;
          self.formFiltro.campos.fechaDesde.value = null;
          self.formFiltro.campos.fechaHasta.min = null;
          self.formFiltro.campos.fechaHasta.value = null;
          self.formFiltro.campos.fechaHasta.disabled = true;
          self.formFiltro.campos.proyecto.value = null;
          self.buscar();

        },
        fecha_hasta: function(fecha_desde){

          self.formFiltro.campos.fechaHasta.min = fecha_desde;
          self.formFiltro.campos.fechaHasta.disabled = false;

        }

      }
  }

</script>
