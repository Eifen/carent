<template>
  <div :class="listClass">
    <div :class="tableClass.title">{{ titleTable }}</div>
    <div :class="tableClass.create">Crear {{ buttonTitle }}</div>
    <!-- Búsqueda de datos en tiempo real -->
    <div class="input-group mb-3" :class="tableClass.search">
      <span class="input-group-text" id="basic-addon1">
        <select class="form-select" @change="enableInput" aria-label="Default select example">
          <option selected disabled>Consultar por...</option>
          <option v-for="(select, cursor) in selectSearch" :key="cursor" :value="select">{{ select }}</option>
        </select>
      </span>
      <input type="text" class="form-control" aria-label="Username" aria-describedby="basic-addon1"
        :disabled="!controlInput.isSelect"
        :placeholder="controlInput.placeholder"
        @input="searchData"/>
    </div>
    <!-- =====================================================================
        Paginacion
        ========================================================================   -->
    <div :class="tableClass.pagination" v-if="controlView.isGreater">
      <!-- Se mostraran unicamente si el cursor de la paginación es mayor a 0 -->
      <font-awesome class="aLink" string-icon="fa-solid fa-angles-left" v-if="controlPagination.cursor != 0"
                    @click="controlPagination.cursor = 0"></font-awesome>
      <font-awesome class="aLink" string-icon="fa-solid fa-angle-left" v-if="controlPagination.cursor != 0"
                    @click="controlPagination.cursor--"></font-awesome>
      <input type="text"
        class="form-control"
        aria-label="Username"
        aria-describedby="basic-addon1"
        :value="controlView.pagActual"
        @input="inputPage"/>
      <!-- Se mostraran unicamente si el cursor de la paginacion es menor al máximo   -->
      <font-awesome class="aLink" string-icon="fa-solid fa-angle-right"
                    v-if="controlPagination.cursor != maxCursor"
                    @click="controlPagination.cursor++"></font-awesome>
      <font-awesome class="aLink" string-icon="fa-solid fa-angles-right"
                    v-if="controlPagination.cursor != maxCursor"
                    @click="controlPagination.cursor = maxCursor"></font-awesome>
      <span :class="tableClass.infoPag">Página {{ controlView.pagActual }} de {{ limitPage }}</span>
    </div>
    <!-- =====================================================================
        Data Table
        ========================================================================   -->
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
            <td v-for="(columnData, cursorTable) in controlTable.data[actualIndex + controlTable.minLength]"
              :key="cursorTable">
              <span v-if="cursorTable == 'estatus' && columnData == 1" class="badge text-bg-success">activo</span>
              <span v-else-if="cursorTable == 'estatus' && columnData == 2" class="badge text-bg-danger">inactivo</span>
              <span v-else>{{ columnData }}</span>
            </td>
            <td :class="tableClass.setting"
              v-if="controlTable.data[actualIndex + controlTable.minLength] !== undefined">
              <!-- Condicion del icono del FontAwesome -->
              <font-awesome string-icon="fa-solid fa-gear"
                :spin="actualIndex === controlTable.hover.index ? true : false"
                :style="actualIndex === controlTable.hover.index ? controlTable.hover.style : null"
                @mouseover="controlTable.hover.index = actualIndex"
                @mouseout="controlTable.hover.index = -1"
                @click="showSetting(actualIndex)"></font-awesome>
              <!-- Configuramos el modal de operaciones de la tabla -->
              <div
                :class="tableClass.modal"
                :id="actualIndex === controlTable.maxLength - 1 ? controlTable.setting.lastChild : null"
                :style="actualIndex === controlTable.setting.index ? controlTable.setting.style : null"
                @mouseover="controlTable.setting.index = actualIndex"
                @mouseout="controlTable.setting.index = -1">
                <div v-for="(setting, cursor) in titleObject.settings"
                  :key="cursor">
                  <!-- Separamos los select en base al objeto suministrado (Deben tener el mismo formato) -->
                  <span class="aLink" v-if="cursor == 'columnS1'">{{setting}}</span>
                  <span class="aLink" v-if="cursor == 'columnS2'">{{setting}}</span>
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
      maxCursor: this.paginationLenght - 1,
      limitPage: this.paginationLenght,
      //Table Object
      tableClass: {
        content: "",
        title: "",
        create: "",
        search: "",
        thead: "",
        setting: "",
        modal: "",
        pagination: "",
        infoPag: "",
      },
      controlInput: { isSelect: false, placeholder: "", valueTarget: "" }, //Controla el input de búsqueda
      controlTable: {
        data: this.tableInfo, //Inicialmente tendrá la informacion de toda la tabla objetivo
        dataLength: this.tableInfo.length, //Tamaño máximo del array
        minLength: 0, //Valor minimo del v-for en el table. Su valor inicial se asigna en el created
        maxLength: 0, //Cuanto recorre en cada página. Su valor inicial se asigna en el created
        active: { index: -1, class: "table-active" }, //Controla la selección
        hover: { index: -1, style: "color:#3490dc" }, //Controla estilo del hover
        setting: {
          index: -1,
          style: "background: #e9ecef;transform:scale(1)",
          lastChild: "lastModal",
        }, //Controla el estilo del Setting
      }, //Controla los estados de la tabla
      controlPagination: { init: 0, cursor: 0, regex: new RegExp("^([0-9]{1})$") }, //control de la lógica de la paginación
      controlView: { pagActual: 0, isGreater: false } //Controla la vista de la paginación
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
      pagination: this.listClass + "-pagination-container",
      infoPag: this.listClass + "-pagination-container-info",
    };

    //Asignamos el minimo y el máximo inicial
    this.controlTable.minLength = this.controlPagination.init * this.paginationLimit;
    this.controlTable.maxLength = this.controlTable.minLength + this.paginationLimit;

    //Asignamos el menu de paginación
    this.paginationLenght <= 1
      ? (this.controlView.isGreater = false)
      : (this.controlView.isGreater = true);

    //Establecemos la pagina actual a la vista
    this.controlView.pagActual = this.controlPagination.cursor + 1;
  },
  mounted() {},
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
      this.controlInput.placeholder =
        "Ingrese la información para " + this.controlInput.valueTarget;
    },
    /**
     * Metodo que muestra el modal del Settings
     * @param {*} targetIndex Captura el indice actual del hover
     */
    showSetting(targetIndex) {
      if (this.controlTable.setting.index === targetIndex) {
        this.controlTable.setting.index = -1;
        return;
      }
      //Si no son iguales, lo asignamos
      this.controlTable.setting.index = targetIndex;
    },
    /**
     * Metodo que se encarga de buscar un dato en función del select indicado
     * @param {*} inputEvent InputEvent Object para almacenar el valor del input
     */
    searchData(inputEvent) {
        //Filtramos el array en función al select, lo cual actualizará automaticamente
        const ArrayDTO = this.tableInfo.filter((data) =>
        data[this.controlInput.valueTarget].toLowerCase()
        .includes(inputEvent.target.value.toLowerCase()));

        //Espacio de transferencia de datos
        this.controlTable.data = ArrayDTO;
        this.controlTable.dataLength = ArrayDTO.length;
        this.controlTable.maxLength = ArrayDTO.length;

        //Estado de la paginación
        this.controlTable.dataLength <= this.paginationLimit
        ? (this.controlView.isGreater = false)
        : (this.controlView.isGreater = true);

        //Limite de la paginación
        this.limitPage = Math.ceil((this.controlTable.dataLength / this.paginationLimit))
        this.maxCursor = this.limitPage - 1;
        this.controlPagination.cursor = 0;
    },
    /**
     * Metodo que cambia de página a través del input
     * @param {*} inputEvent InputEvent Object para almacenar el valor del input
     */
    inputPage(inputEvent) {
      //Veríficamos que sea un número y que este comprendido en el intervalo de la página
      if ((!this.controlPagination.regex.test(inputEvent.data) && inputEvent.data != null)
        || parseInt(inputEvent.data) > this.limitPage
        || parseInt(inputEvent.data) == 0 ||
        inputEvent.target.value.length > 1) {
            inputEvent.target.value = this.controlView.pagActual;
            return;
        }
      if (inputEvent.data == null) return;
      //Si pasa todas las validaciones, actualizamos los cursores
      const newPage = parseInt(inputEvent.target.value) - 1;
      this.controlPagination.cursor = newPage;
    },
  },
  watch: {
    controlPagination: {
      deep: true,
      handler(controlChange) {
        this.controlTable.minLength = controlChange.cursor * this.paginationLimit;
        //Cambiamos el estado del INPUT de la paginación
        this.controlView.pagActual = controlChange.cursor + 1

        //Validaciones en función al estado actual del cursor. Sea valor máximo o valor minimo
        if(controlChange.cursor == this.maxCursor)
        this.controlTable.maxLength = this.controlTable.dataLength - this.controlTable.minLength;
      },
    },
  },
  components: { FontAwesome },
};
</script>
