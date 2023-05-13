<template>
  <fieldset :class="scope.formClass.fieldset">
    <!-- Selección de Socio -->
    <div class="mb-3">
      <label for="Socio">Socio <span :class="scope.formClass.requiredField">*</span></label>
      <div class="input-group">
        <select class="form-select" title="SocioSelect"
          v-model="scope.inputSocioSelect">
          <option value=0 disabled selected>Seleccione el Socio encargado</option>
          <option v-for="(select, cursor) in scope.dataSelect.socio"
            :key="cursor"
            :value="select.Id">
            <span v-if="select.Id != 0">{{ select.Primer_nombre + " " + select.Segundo_nombre + " " +
                                        select.Primer_apellido + " " + select.Segundo_apellido }}</span>
          </option>
        </select>
      </div>
    </div>
    <!-- Estado del cliente. Solo para Edit -->
    <div class="mb-3" v-if="isEdit">
      <label for="Status">Status del cliente</label>
      <div class="input-group">
        <select class="form-select" v-model="scope.inputStatusSelect" title="StatusSelect">
          <option value="0" selected disabled>Seleccione el status</option>
          <option v-for="(select, cursor) in scope.dataSelect.status"
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
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";

export default {
    props: {
        scope: Object, //Hereda la data del padre
        isEdit: Boolean //Cambia la información en caso de edit
    },
    components: { FontAwesome }
};
</script>

<style>
</style>
