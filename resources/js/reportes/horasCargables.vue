<template>
  <b-row>

    <b-col cols=12>
      <b-form class="row">
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          description="Nombre que se le dío la proyecto"
          label="Proyecto"
          label-for="proyecto"
          id="group-proyecto">
          <b-form-input
            :disabled="formFiltro.proyecto.disabled"
            id="proyecto"
            ref="proyecto"
            size="sm"
            type="text"
            v-model.trim="formFiltro.proyecto.value"></b-form-input>
        </b-form-group>
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          description="Razón Social del Cliente"
          label="Cliente"
          label-for="cliente"
          id="group-cliente">
          <b-form-input
            :disabled="formFiltro.cliente.disabled"
            id="cliente"
            ref="cliente"
            size="sm"
            type="text"
            v-model.trim="formFiltro.cliente.value"></b-form-input>
        </b-form-group>
        <b-form-group
          class="form-group col-12 col-sm-6 col-md-4"
          description="Nombre del Empleado"
          label="Empleado"
          label-for="empleado"
          id="group-empleado">
          <b-form-input
            :disabled="formFiltro.empleado.disabled"
            id="empleado"
            ref="empleado"
            size="sm"
            type="text"
            v-model.trim="formFiltro.empleado.value"></b-form-input>
        </b-form-group>
      </b-form>
    </b-col>

    <b-col cols=12>
      <b-table
        :busy="tabla.cargando"
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

</style>

<script>

  import axios from 'axios';
  import alert from '../components/alert.vue';
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
            cliente : {
              disabled: true,
              value: ""
            },
            empleado : {
              disabled: true,
              value: ""
            },
            proyecto : {
              disabled: true,
              value: ""
            },
            estatus : {
              disabled: true,
              value: null
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
          }
        };
      },
      components: {
        alert
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

            self.tabla.paginador.paginar = response.data.paginar;
            self.tabla.paginador.numPaginas = response.data.paginas;
            self.tabla.paginador.max = parseInt(response.data.paginas);

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
          self.paginador.pagina = ((self.paginador.pagina - 1) === 0) ? 1 : (self.paginador.pagina - 1);
          self.buscar();
        },
        paginaSiguiente: function(){
          self.paginador.pagina = ((self.paginador.pagina + 1) > self.paginador.max) ? self.paginador.pagina : (self.paginador.pagina + 1);
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

        }

      }
  }

</script>
