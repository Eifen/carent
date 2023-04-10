<template>
  <div :class="listClass">
    <div :class="titleClass">{{ titleTable }}</div>
    <div :class="createClass">Crear {{ buttonTitle }}</div>
    <div class="input-group mb-3" :class="searchClass">
      <span class="input-group-text" id="basic-addon1">
            <select class="form-select" @change="enableInput" aria-label="Default select example">
                <option selected disabled>Consultar por...</option>
                <option v-for="(select,cursor) in selectSearch" :key="cursor" :value="select">{{select}}</option>
            </select>
      </span>
      <input
        type="text"
        class="form-control"
        aria-label="Username"
        aria-describedby="basic-addon1"
        :disabled="!controlInput.isSelect"
        :placeholder="controlInput.placeholder"
      />
    </div>
    <!-- TODO: Pagination Reservation -->
    <div class="table-responsive" :class="tableContentClass">
      <table class="table table-hover">
        <thead>
          <tr>
            <!-- Table Object -->
            <th
              scope="col"
              v-for="(title, cursor) in titleObject"
              :key="cursor"
            >
              <span v-if="cursor != 'settings'">{{ title }}</span>
              <span v-else></span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cursor1 in paginationLimit" :key="cursor1">
            <td
              v-for="(column, cursor2) in tableInfo[cursor1 - 1]"
              :key="cursor2"
            >
              <span
                class="badge text-bg-success"
                v-if="cursor2 == 'estatus' && column == 1"
                >activo</span
              >
              <span
                class="badge text-bg-danger"
                v-else-if="cursor2 == 'estatus' && column == 2"
                >inactivo</span
              >
              <span v-else>{{ column }}</span>
            </td>
            <td>
              <font-awesome string-icon="fa-solid fa-gear"></font-awesome>
              <div>
                <span
                  v-for="(setting, cursor) in titleObject.settings"
                  :key="cursor"
                  >{{ setting }}</span
                >
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
      activeClass: { target: false, class: "table-active" }, //Selecciona alguna tabla
      listClass: "list-container",
      tableContentClass: "",
      titleClass: "",
      createClass: "",
      searchClass: "",
      controlInput: { isSelect: false, placeholder: ""} //Controla el input de búsqueda
    };
  },
  created() {
    //Asignamos las clases
    this.tableContentClass = this.listClass + "-table";
    this.titleClass = this.listClass + "-title";
    this.createClass = this.listClass + "-create";
    this.searchClass = this.listClass + "-search";
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
  methods:{
    enableInput(select){ 
        this.controlInput.isSelect = true;
        this.controlInput.placeholder = "Ingrese la información para " + select.target.value.toLowerCase()
    }
  },
  components: { FontAwesome },
};
</script>
