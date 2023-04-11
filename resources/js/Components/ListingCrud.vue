<template>
  <div :class="listClass">
    <div :class="tableClass.title">{{ titleTable }}</div>
    <div :class="tableClass.create">Crear {{ buttonTitle }}</div>
    <!-- Búsqueda de datos en tiempo real -->
    <div class="input-group mb-3" :class="tableClass.search">
      <span class="input-group-text" id="basic-addon1">
        <select class="form-select" @change="enableInput" aria-label="Default select example">
          <option selected disabled>Consultar por...</option>
          <option v-for="(select, cursor) in selectSearch" :key="cursor" :value="select">
            {{ select }}
          </option>
        </select>
      </span>
      <input type="text" class="form-control" aria-label="Username" aria-describedby="basic-addon1"
            :disabled="!controlInput.isSelect" :placeholder="controlInput.placeholder"
            @input="searchData"/>
    </div>
    <!-- TODO: Pagination Reservation -->
    <!-- Data Table -->
    <div class="table-responsive" :class="tableClass.content">
      <table class="table table-hover">
        <thead :class="tableClass.thead">
          <tr>
            <!-- Table Object -->
            <th scope="col" v-for="(title, cursor) in titleObject" :key="cursor">
              <span v-if="cursor != 'settings'">{{ title }}</span>
              <span v-else></span>
            </th>
          </tr>
        </thead>
        <tbody>
            <!-- Carga de los datos de entrada. Si detecta Estatus separa su contenido -->
          <tr v-for="(cursorArray, actualIndex) in controlTable.maxLength" :key="cursorArray"
            :class="[actualIndex === controlTable.active.index ? controlTable.active.class : null]"
            @click="controlTable.active.index = actualIndex">
            <!-- Recorremos el array de datos -->
            <td v-for="(columnData, cursorTable) in controlTable.data[actualIndex]" :key="cursorTable">
              <span v-if="cursorTable == 'estatus' && columnData == 1" class="badge text-bg-success">activo</span>
              <span v-else-if="cursorTable == 'estatus' && columnData == 2" class="badge text-bg-danger">inactivo</span>
              <span v-else>{{ columnData }}</span>
            </td>
            <td :class="tableClass.setting">
                <!-- Condicion del icono del FontAwesome -->
              <font-awesome string-icon="fa-solid fa-gear"
                :spin="actualIndex === controlTable.hover.index ? true : false"
                :style="actualIndex === controlTable.hover.index ? controlTable.hover.style : null"
                @mouseover="controlTable.hover.index = actualIndex"
                @mouseout="controlTable.hover.index = -1"
                @click="showSetting(actualIndex)"></font-awesome>
                <!-- Configuramos el modal de operaciones de la tabla -->
              <div :class="tableClass.modal"
                :id="actualIndex === (controlTable.maxLength-1) ? controlTable.setting.lastChild : null"
                :style="actualIndex === controlTable.setting.index ? controlTable.setting.style : null"
                @mouseover="controlTable.setting.index = actualIndex"
                @mouseout="controlTable.setting.index = -1">
                <div v-for="(setting, cursor) in titleObject.settings" :key="cursor">
                <!-- Separamos los select en base al objeto suministrado (Deben tener el mismo formato) -->
                  <span class="aLink" v-if="cursor == 'columnS1'">{{ setting }}</span>
                  <span class="aLink" v-if="cursor == 'columnS2'">{{ setting }}</span>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import FontAwesome from "../Components/FontAwesome/FontAwesome.vue";

export default {
  props: {
    titleObject: Object,
    /*Objeto que indica el titulo de cada Columna.
        Debe existir una propiedad llamada settings y si tiene más de 1 opción*/
    paginationLenght: Number, //La cantidad de páginas
    paginationLimit: Number, // El Tamaño máximo de cada página
    tableInfo: Array, //Array Objeto que almacena el resultado del select
    titleTable: String, //Titulo de la tabla
    buttonTitle: String, //Titulo del boton de crear
    selectSearch: Object, //Objeto que almaneca los select de búsqueda
  },
  data() {
    return {
      listClass: "list-container",
      cursorPagination: 0, //Establece el inicio de la paginación
      //Table Object
      tableClass: {
        content: "",
        title: "",
        create: "",
        search: "",
        thead: "",
        setting: "",
        modal: "",
      },
      controlInput: { isSelect: false, placeholder: "", valueTarget: "" }, //Controla el input de búsqueda
      controlTable: {
        data: this.tableInfo, //Inicialmente tendrá la informacion de toda la tabla objetivo
        minLength: 0, //Se asignan en el created
        maxLength: 0, //Se asignan en el created
        active: { index: -1, class: "table-active" }, //Controla la selección
        hover: { index: -1, style: "color:#3490dc" }, //Controla estilo del hover
        setting: { index: -1, style: "background: #e9ecef;transform:scale(1)", lastChild: "lastModal" }, //Controla el estilo del Setting
      }, //Controla los estados de la tabla
    };
  },
  created() {
    //Asignamos la distribución  de clases
    this.tableClass = {
      content: this.listClass + "-table",
      thead: this.listClass + "-table-thead",
      setting: this.listClass + "-table-setting-container",
      modal: this.listClass + "-table-setting-container-modal",
      title: this.listClass + "-title",
      create: this.listClass + "-create",
      search: this.listClass + "-search",
    };

    //Asignamos el minimo y el máximo inicial
    this.controlTable.minLength = this.cursorPagination * this.paginationLimit;
    this.controlTable.maxLength =
      this.controlTable.minLength + this.paginationLimit;
  },
  mounted() {
    console.log(
      this.tableInfo,
      this.paginationLenght,
      this.paginationLimit,
      this.titleObject,
      this.selectSearch
    );
  },
  methods: {
    /**
     * Metodo que habilita el input tras seleccionar una delas opciones
     * @param {*} select Click Event para saber que valor se le hizo target y cambiar en función
     */
    enableInput(select) {
        this.controlInput.isSelect = true;
        //Guardamos el target
        this.controlInput.valueTarget = select.target.value.toLowerCase();

        //Actualizamos el Placeholder
        this.controlInput.placeholder = "Ingrese la información para " + this.controlInput.valueTarget;
    },
    /**
     * Metodo que muestra el modal del Settings
     * @param {*} targetIndex Captura el indice actual del hover
     */
    showSetting(targetIndex) { this.controlTable.setting.index = targetIndex; },
    searchData(inputEvent){
        //TODO: Acomodar el INPUT Reactivo 
        console.log(inputEvent.target.value)
        this.controlTable.data = this.tableInfo.filter(data => data.codigo == inputEvent.target.value);
        console.log(this.controlTable.data)
    }
    
  },
  components: { FontAwesome },
};
</script>
