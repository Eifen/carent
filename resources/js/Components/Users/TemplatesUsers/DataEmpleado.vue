<template>
  <fieldset :class="scope.formClass.fieldset" v-if="scope.inputCheckedCrowe">
    <!-- Estado -->
    <div class="mb-3">
      <label for="Estado">Estado</label>
      <div class="input-group">
        <select class="form-select" v-model="scope.inputEstadoSelect" title="StateSelect">
          <option v-for="(select, cursor) in scope.stateData"
            :key="cursor"
            :value="select.Id"
            :selected="select.Id == 0"
            :disabled="select.Id == 0">
            <span v-if="select.Id != 0">{{ select.NombreEstado }}</span>
            <span v-if="select.Id == 0">Seleccione el Estado</span>
          </option>
        </select>
      </div>
    </div>
    <!-- Municipio -->
    <div class="mb-3">
      <label for="Municipio">Municipio</label>
      <div class="input-group">
        <select class="form-select" title="MunicipalitySelect"
          :disabled="scope.inputEstadoSelect == 0"
          v-model="scope.inputMunicipioSelect">
          <option value=0 selected disabled>Seleccione el Municipio</option>
          <option v-for="(select, cursor) in scope.municipalityData"
            :key="cursor"
            :value="select.Id"
            :selected="select.Id == 0"
            :disabled="select.Id == 0">
            <span v-if="select.Id != 0">{{ select.NombreMunicipio }}</span>
          </option>
        </select>
      </div>
    </div>
    <!-- Parroquia -->
    <div class="mb-3">
      <label for="Parroquia">Parroquia</label>
      <div class="input-group">
        <select class="form-select" title="ParishSelect"
          :disabled="scope.inputMunicipioSelect == 0"
          v-model="scope.inputParroquiaSelect">
          <option value=0 selected disabled>Seleccione la Parroquia</option>
          <option v-for="(select, cursor) in scope.parishData"
            :key="cursor"
            :value="select.Id"
            :selected="select.Id == 0"
            :disabled="select.Id == 0">
            <span v-if="select.Id != 0">{{ select.NombreParroquia }}</span>
          </option>
        </select>
      </div>
    </div>
    <!-- División -->
    <div class="mb-3">
      <label for="Division">División</label>
      <div class="input-group">
        <select class="form-select" v-model="scope.inputDivisionSelect" title="DivisionSelect">
          <option v-for="(select, cursor) in scope.divisionData"
            :key="cursor"
            :value="select.Id"
            :selected="select.Id == 0"
            :disabled="select.Id == 0">
            <span v-if="select.Id != 0">{{ select.NombreDivision }}</span>
            <span v-if="select.Id == 0">Seleccione la División</span>
          </option>
        </select>
      </div>
    </div>
    <!-- Cargo -->
    <div class="mb-3">
      <label for="Cargo">Cargo</label>
      <div class="input-group">
        <select class="form-select" title="CargoSelect"
          :disabled="scope.inputDivisionSelect == 0"
          v-model="scope.inputCargoSelect">
          <option v-for="(select, cursor) in scope.cargoData"
            :key="cursor"
            :value="select.Id"
            :selected="select.Id == 0"
            :disabled="select.Id == 0">
            <span v-if="select.Id != 0">{{ select.NombreCargo }}</span>
            <span v-if="select.Id == 0">Seleccione el Cargo</span>
          </option>
        </select>
      </div>
    </div>
    <!-- Fecha de ingreso -->
    <div class="mb-3">
      <label for="ingreso">Fecha de Ingreso</label>
      <div class="input-group">
        <input
          type="text"
          class="form-control"
          placeholder="Ejemplo: 1990-02-18"
          id="ingreso"
          aria-describedby="basic-addon12"
          v-model="scope.inputIngreso"
          disabled/>
        <span class="input-group-text" id="basic-addon12">
          <calendar @to-input="ingresoDTO"></calendar>
        </span>
      </div>
      <!-- Mensajes de error en fecha -->
      <div :class="scope.formClass.failureValidation"
        v-if="scope.messages.form.ingresoError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.form.ingresoError }}
      </div>
    </div>
    <!-- Fecha de egreso. Solo para el edit -->
    <div class="mb-3" v-if="enableEdit">
      <label for="egreso">Fecha de Egreso</label>
      <div class="input-group">
        <input
          type="text"
          class="form-control"
          placeholder="Ejemplo: 1990-02-18"
          id="egreso"
          aria-describedby="basic-addon13"
          v-model="scope.inputEgreso"
          disabled/>
        <span class="input-group-text" id="basic-addon13">
          <calendar @to-input="egresoDTO"></calendar>
        </span>
      </div>
      <!-- Mensajes de error en fecha -->
      <div :class="scope.formClass.failureValidation"
        v-if="scope.messages.form.egresoError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.form.egresoError }}
      </div>
    </div>
  </fieldset>
</template>

<script>
import Calendar from "@/Components/Calendar.vue";
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";

export default {
  props: { scope: Object, enableEdit: Boolean }, //scope: Objeto encargado de heredar la data del padre
  components: { Calendar, FontAwesome },
  methods:
  {
    ingresoDTO(ingresoValue){ this.$emit('active-ingreso',ingresoValue) },
    egresoDTO(egresoValue){ this.$emit('active-egreso',egresoValue) }
  }
};
</script>
