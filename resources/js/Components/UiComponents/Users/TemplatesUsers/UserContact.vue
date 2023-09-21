<template>
    <fieldset :class="scope.formClass.fieldset">
        <!-- First Email -->
        <div class="mb-3">
            <label for="FirstEmail">Correo Principal <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text">
                    <font-awesome string-icon="fa-solid fa-at"></font-awesome>
                </span>
                <input type="text" class="form-control" placeholder="Ejemplo: usuario@empresa.dominio" id="FirstEmail"
                    v-model="scope.inputFirstEmail" />
            </div>
            <!-- Mensajes de error en Email -->
            <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.firstemailError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.firstemailError }}
            </div>
        </div>

        <!-- Second Email -->
        <div class="mb-3">
            <label for="SecondEmail">Correo Secundario</label>
            <div class="input-group">
                <span class="input-group-text">
                    <font-awesome string-icon="fa-solid fa-at"></font-awesome>
                </span>
                <input type="text" class="form-control" placeholder="Ejemplo: usuario2@empresa2.dominio2" id="SecondEmail"
                    v-model="scope.inputSecondEmail" />
            </div>
            <!-- Mensajes de error en Email -->
            <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.secondemailError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.secondemailError }}
            </div>
        </div>

        <!-- First Phone -->
        <div class="mb-3">
            <label for="FirstPhone">Teléfono Principal
                <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input type="text" class="form-control" placeholder="Ingrese el número de TLF" id="FirstPhone"
                    v-model="scope.inputFirstPhone" />
            </div>
        </div>

        <!-- Second Phone -->
        <div class="mb-3">
            <label for="SecondPhone">Teléfono Secundario</label>
            <div class="input-group">
                <span class="input-group-text">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input type="text" class="form-control" placeholder="Ingrese el número de TLF" id="SecondPhone"
                    v-model="scope.inputSecondPhone" />
            </div>
        </div>
        <!-- Estado. Solo para Edit -->
        <div class="mb-3" v-if="enableEdit">
            <label for="Status">Status del empleado</label>
            <div class="input-group">
                <select class="form-select" v-model="scope.inputStatusSelect" title="StatusSelect">
                    <option value="0" selected disabled>Seleccione el status</option>
                    <option v-for="(select, cursor) in scope.statusData" :key="cursor" :value="select.status_id">
                        {{ select.status_description }}
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
