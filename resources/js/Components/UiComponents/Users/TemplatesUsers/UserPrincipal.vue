<template>
  <fieldset :class="scope.formClass.fieldset">
    <!-- First Name -->
    <div class="mb-3">
      <label for="firstName">Primer Nombre <span :class="scope.formClass.requiredField">*</span></label>
      <div class="input-group">
        <span class="input-group-text">
          <font-awesome string-icon="fa-solid fa-user"></font-awesome>
        </span>
        <input type="text" class="form-control" placeholder="Ejemplo: Pepe" id="firstName"
          v-model="scope.inputFirstname" />
      </div>
      <!-- Mensajes de error en Nombre-->
      <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.firstnameError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.error.firstnameError }}
      </div>
    </div>

    <!-- Second Name -->
    <div class="mb-3">
      <label for="secondName">Segundo Nombre</label>
      <div class="input-group">
        <span class="input-group-text">
          <font-awesome string-icon="fa-solid fa-user"></font-awesome>
        </span>
        <input type="text" class="form-control" placeholder="Ejemplo: Eduardo" id="secondName"
          v-model="scope.inputSecondname" />
      </div>
      <!-- Mensajes de error en Segundo Nombre -->
      <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.secondnameError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.error.secondnameError }}
      </div>
    </div>

    <!-- Last Name -->
    <div class="mb-3">
      <label for="lastName">Primer Apellido <span :class="scope.formClass.requiredField">*</span></label>
      <div class="input-group">
        <span class="input-group-text">
          <font-awesome string-icon="fa-solid fa-user"></font-awesome>
        </span>
        <input type="text" class="form-control" placeholder="Ejemplo: Salazar" id="lastName"
          v-model="scope.inputLastname" />
      </div>
      <!-- Mensajes de error en Apellido -->
      <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.lastnameError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.error.lastnameError }}
      </div>
    </div>

    <!-- Last Second Name -->
    <div class="mb-3">
      <label for="LastSecondName">Segundo Apellido</label>
      <div class="input-group">
        <span class="input-group-text">
          <font-awesome string-icon="fa-solid fa-user"></font-awesome>
        </span>
        <input type="text" class="form-control" placeholder="Ejemplo: Marquéz" id="LastSecondName"
          v-model="scope.inputLastSecondname" />
      </div>
      <!-- Mensajes de error en Segundo Apellido -->
      <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.lastsecondnameError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.error.lastsecondnameError }}
      </div>
    </div>

    <!-- Documento identidad -->
    <div class="mb-3">
      <label for="DocumentoIdentidad">Documento de Identidad
        <span :class="scope.formClass.requiredField">*</span></label>
      <div class="input-group" :class="scope.formClass.select">
        <span class="input-group-text">
          <select class="form-select" title="CedulaSelect" @change="documentHandler" v-model="scope.inputDocumentoSelect">
            <option value="0" selected disabled>Tipo</option>
            <option v-for="(select, cursor) in scope.typeDocument" :key="cursor" :value="select.identity_abbreviation">
              {{ select.identity_description }}
            </option>
          </select>
        </span>
        <input type="text" class="form-control" :disabled="scope.inputSelect === ''" placeholder="Ejemplo: 15.365.987"
          id="DocumentoIdentidad" v-model="scope.inputSelect" />
      </div>
      <!-- Mensajes de error en Documento Identidad -->
      <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.documentError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.error.documentError }}
      </div>
    </div>

    <!-- Birthday -->
    <div class="mb-3">
      <label for="birthday">Fecha de Nacimiento
        <span :class="scope.formClass.requiredField">*</span></label>
      <div class="input-group">
        <input type="text" class="form-control" placeholder="Ejemplo: 1990-02-18" id="birthday"
          v-model="scope.inputBirthday" disabled />
        <span class="input-group-text">
          <calendar @to-input="insertBirthday"></calendar>
        </span>
      </div>
      <!-- Mensajes de error en fecha -->
      <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.birthdayError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.error.birthdayError }}
      </div>
    </div>

    <!-- Código -->
    <div class="mb-3" v-if="!enableEdit">
      <label for="Codigo">Código <span :class="scope.formClass.requiredField">*</span></label>
      <div class="input-group">
        <span class="input-group-text">
          <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
        </span>
        <input type="text" class="form-control" placeholder="Ejemplo: 0001" id="Codigo" v-model="scope.inputCode"
          :disabled="enableEdit ? true : false" />
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
    documentHandler(changeEvent) { this.$emit('active-document', changeEvent.target.value) },
    insertBirthday(valueCalendar) { this.$emit('active-birthday', valueCalendar) }
  }
}
</script>
