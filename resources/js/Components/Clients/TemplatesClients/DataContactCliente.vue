<template>
    <fieldset :class="scope.formClass.fieldset">
        <!-- Selección de Pais -->
        <div class="mb-3">
            <label for="Pais">País <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <select class="form-select" title="PaisSelect"
                v-model="scope.inputPaisSelect">
                    <option v-for="(select, cursor) in scope.dataSelect.paises"
                    :key="cursor"
                    :value="select.Id"
                    :disabled="select.Id == 0"
                    :selected="select.Id == 0">
                        <span v-if="select.Id != 0">{{ select.Nombre_pais }}</span>
                        <span v-if="select.Id == 0">Seleccione el Pais</span>
                    </option>
                </select>
            </div>
        </div>
        <!-- Telefono que solo se muestra si se ha seleccionado un país-->
        <div class="mb-3" v-if="scope.inputPaisSelect != 0">
            <label for="Telefono">Teléfono Principal<span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon4">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input
                type="text"
                class="form-control"
                placeholder="Ejemplo: +584241234567"
                id="Telefono"
                aria-describedby="basic-addon4"
                v-model="scope.inputTelefono"/>
            </div>
            <!-- Mensajes de error en Telefono-->
            <div :class="scope.formClass.failureValidation"
                v-if="scope.messages.error.telefonoError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.telefonoError }}
            </div>
        </div>
        <!-- Direccion-->
        <div class="mb-3">
            <label for="direccion">Dirección fiscal<span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon4">
                    <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                </span>
                <textarea
                type="text"
                class="form-control"
                placeholder="Direccion del cliente"
                id="direccion"
                aria-describedby="basic-addon4"
                v-model="scope.inputDireccion"></textarea>
            </div>
            <!-- Mensajes de error en Nombre-->
            <div :class="scope.formClass.failureValidation"
                v-if="scope.messages.error.direccionError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.direccionError }}
            </div>
        </div>
        <!-- First Email -->
        <div class="mb-3">
            <label for="FirstEmail">Correo Principal <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon5">
                    <font-awesome string-icon="fa-solid fa-at"></font-awesome>
                </span>
                <input
                type="text"
                class="form-control"
                placeholder="Ejemplo: usuario@empresa.dominio"
                id="FirstEmail"
                aria-describedby="basic-addon5"
                v-model="scope.inputFirstEmail"/>
            </div>
            <!-- Mensajes de error en Email -->
            <div :class="scope.formClass.failureValidation"
            v-if="scope.messages.error.firstemailError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.firstemailError }}
            </div>
        </div>        
    </fieldset>
</template>

<script>
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";
export default {
    props:{ 
        scope: Object, //Hereda la data del padre
        isEdit: Boolean //Hereda el isEdit del padre
    },
    components: { FontAwesome }
}
</script>