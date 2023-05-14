<template>
  <fieldset :class="scope.formClass.fieldset">
    <!-- First Email -->
    <div class="mb-3">
      <label for="FirstEmail">Correo Principal <span :class="scope.formClass.requiredField">*</span></label>
      <div class="input-group">
        <span class="input-group-text" id="basic-addon8">
          <font-awesome string-icon="fa-solid fa-at"></font-awesome>
        </span>
        <input
          type="text"
          class="form-control"
          placeholder="Ejemplo: usuario@empresa.dominio"
          id="FirstEmail"
          aria-describedby="basic-addon8"
          v-model="scope.inputFirstEmail"/>
      </div>
      <!-- Mensajes de error en Email -->
      <div
        :class="scope.formClass.failureValidation"
        v-if="scope.messages.form.firstemailError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.form.firstemailError }}
      </div>
    </div>

    <!-- Second Email -->
    <div class="mb-3">
      <label for="SecondEmail">Correo Secundario</label>
      <div class="input-group">
        <span class="input-group-text" id="basic-addon9">
          <font-awesome string-icon="fa-solid fa-at"></font-awesome>
        </span>
        <input
          type="text"
          class="form-control"
          placeholder="Ejemplo: usuario2@empresa2.dominio2"
          id="SecondEmail"
          aria-describedby="basic-addon9"
          v-model="scope.inputSecondEmail"/>
      </div>
      <!-- Mensajes de error en Email -->
      <div :class="scope.formClass.failureValidation"
        v-if="scope.messages.form.secondemailError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.form.secondemailError }}
      </div>
    </div>

    <!-- First Phone -->
    <div class="mb-3">
      <label for="FirstPhone">Telefono Principal
        <span :class="scope.formClass.requiredField">*</span></label>
      <div class="input-group">
        <span class="input-group-text" id="basic-addon10">
          <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
        </span>
        <input
          type="text"
          class="form-control"
          placeholder="Ingrese el número de TLF"
          id="FirstPhone"
          aria-describedby="basic-addon10"
          v-model="scope.inputFirstPhone"/>
      </div>
    </div>

    <!-- Second Phone -->
    <div class="mb-3">
      <label for="SecondPhone">Telefono Secundario</label>
      <div class="input-group">
        <span class="input-group-text" id="basic-addon11">
          <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
        </span>
        <input
          type="text"
          class="form-control"
          placeholder="Ingrese el número de TLF"
          id="SecondPhone"
          aria-describedby="basic-addon11"
          v-model="scope.inputSecondPhone"/>
      </div>
    </div>
    <!-- Estado. Solo para Edit -->
    <div class="mb-3" v-if="enableEdit">
      <label for="Status">Status del empleado</label>
      <div class="input-group">
        <select class="form-select" v-model="scope.inputStatusSelect" title="StatusSelect">
          <option value="0" selected disabled>Seleccione el status</option>
          <option v-for="(select, cursor) in scope.statusData"
            :key="cursor"
            :value="select.Id">
            {{ select.Descripcion }}
          </option>
        </select>
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
};
</script>
