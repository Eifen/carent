<template>
  <fieldset :class="scope.formClass.fieldset">
    <!-- First Name -->
    <div class="mb-3">
      <label for="firstName">Primer Nombre <span :class="scope.formClass.requiredField">*</span></label>
      <div class="input-group">
        <span class="input-group-text" id="basic-addon1">
          <font-awesome string-icon="fa-solid fa-user"></font-awesome>
        </span>
        <input
          type="text"
          class="form-control"
          placeholder="Ejemplo: Pepe"
          id="firstName"
          aria-describedby="basic-addon1"
          v-model="scope.inputFirstname"/>
      </div>
      <!-- Mensajes de error en Nombre-->
      <div :class="scope.formClass.failureValidation"
        v-if="scope.messages.form.firstnameError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.form.firstnameError }}
      </div>
    </div>

    <!-- Second Name -->
    <div class="mb-3">
      <label for="secondName">Segundo Nombre</label>
      <div class="input-group">
        <span class="input-group-text" id="basic-addon2">
          <font-awesome string-icon="fa-solid fa-user"></font-awesome>
        </span>
        <input
          type="text"
          class="form-control"
          placeholder="Ejemplo: Eduardo"
          id="secondName"
          aria-describedby="basic-addon2"
          v-model="scope.inputSecondname"/>
      </div>
      <!-- Mensajes de error en Segundo Nombre -->
      <div
        :class="scope.formClass.failureValidation"
        v-if="scope.messages.form.secondnameError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.form.secondnameError }}
      </div>
    </div>

    <!-- Last Name -->
    <div class="mb-3">
      <label for="lastName">Primer Apellido <span :class="scope.formClass.requiredField">*</span></label>
      <div class="input-group">
        <span class="input-group-text" id="basic-addon3">
          <font-awesome string-icon="fa-solid fa-user"></font-awesome>
        </span>
        <input
          type="text"
          class="form-control"
          placeholder="Ejemplo: Salazar"
          id="lastName"
          aria-describedby="basic-addon3"
          v-model="scope.inputLastname"/>
      </div>
      <!-- Mensajes de error en Apellido -->
      <div :class="scope.formClass.failureValidation"
        v-if="scope.messages.form.lastnameError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.form.lastnameError }}
      </div>
    </div>

    <!-- Last Second Name -->
    <div class="mb-3">
      <label for="LastSecondName">Segundo Apellido</label>
      <div class="input-group">
        <span class="input-group-text" id="basic-addon4">
          <font-awesome string-icon="fa-solid fa-user"></font-awesome>
        </span>
        <input
          type="text"
          class="form-control"
          placeholder="Ejemplo: Marquéz"
          id="LastSecondName"
          aria-describedby="basic-addon4"
          v-model="scope.inputLastSecondname"/>
      </div>
      <!-- Mensajes de error en Segundo Apellido -->
      <div :class="scope.formClass.failureValidation"
        v-if="scope.messages.form.lastsecondnameError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.form.lastsecondnameError }}
      </div>
    </div>

    <!-- Documento identidad -->
    <div class="mb-3">
      <label for="DocumentoIdentidad">Documento de Identidad
        <span :class="scope.formClass.requiredField">*</span></label>
      <div class="input-group" :class="scope.formClass.select">
        <span class="input-group-text" id="basic-addon5">
          <select
            class="form-select"
            title="CedulaSelect"
            @change="documentHandler"
            v-model="scope.inputDocumentoSelect">
            <option value="0" selected disabled>Tipo</option>
            <option
              v-for="(select, cursor) in scope.typeDocument"
              :key="cursor"
              :value="select.AbreviaturaTipo">
              {{ select.DescripcionTipo }}
            </option>
          </select>
        </span>
        <input type="text"
          class="form-control"
          aria-describedby="basic-addon5"
          :disabled="scope.inputSelect === ''"
          placeholder="Ejemplo: 15,365,987"
          id="DocumentoIdentidad"
          v-model="scope.inputSelect"/>
      </div>
      <!-- Mensajes de error en Documento Identidad -->
      <div :class="scope.formClass.failureValidation"
        v-if="scope.messages.form.documentError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.form.documentError }}
      </div>
    </div>

    <!-- Birthday -->
    <div class="mb-3">
      <label for="birthday">Fecha de Nacimiento
        <span :class="scope.formClass.requiredField">*</span></label>
      <div class="input-group">
        <input
          type="text"
          class="form-control"
          placeholder="Ejemplo: 1990-02-18"
          id="birthday"
          aria-describedby="basic-addon6"
          v-model="scope.inputBirthday"
          disabled/>
        <span class="input-group-text" id="basic-addon6">
          <calendar @to-input="insertBirthday"></calendar>
        </span>
      </div>
      <!-- Mensajes de error en fecha -->
      <div :class="scope.formClass.failureValidation"
        v-if="scope.messages.form.birthdayError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.form.birthdayError }}
      </div>
    </div>

    <!-- Código -->
    <div class="mb-3">
      <label for="Codigo">Código <span :class="scope.formClass.requiredField">*</span></label>
      <div class="input-group">
        <span class="input-group-text" id="basic-addon7">
          <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
        </span>
        <input
          type="text"
          class="form-control"
          placeholder="Ejemplo: 0001"
          id="Codigo"
          aria-describedby="basic-addon7"
          v-model="scope.inputCode"/>
      </div>
    </div>
  </fieldset>
</template>

<script>
import Calendar from "@/Components/Calendar.vue";
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";

export default {
    props: { scope: Object }, //scope: Objeto encargado de heredar la data del padre
    components: { Calendar, FontAwesome },
    methods:
    {
        documentHandler(changeEvent){ this.$emit('active-document',changeEvent.target.value) },
        insertBirthday(valueCalendar){ this.$emit('active-birthday',valueCalendar) }
    }
}
</script>
