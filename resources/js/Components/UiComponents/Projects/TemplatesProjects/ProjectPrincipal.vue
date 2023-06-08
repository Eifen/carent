<template>
    <fieldset :class="scope.formClass.fieldset">
        <!-- Descripcion de proyecto -->
        <div class="mb-3">
            <label for="projectDescription">Descripción del proyecto <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon1">
                    <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                </span>
                <input type="text" class="form-control" placeholder="Ejemplo: Auditoria para el..." id="projectDescription"
                    aria-describedby="basic-addon1" v-model="scope.inputProjectDescription" />
            </div>
            <!-- Mensajes de error en Nombre-->
            <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.projectDescriptionError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.projectDescriptionError }}
            </div>
        </div>
        <!-- Cliente asociado -->
        <div class="mb-3">
            <label for="clientAssociated">Cliente Asociado<span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon2">
                    <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                </span>
                <input type="text" class="form-control" placeholder="Ejemplo: Mc Donalds" id="clientAssociated"
                    aria-describedby="basic-addon2" v-model="scope.inputClientAssociated"/>
                <dropdown-select :stringToSearch="scope.inputClientAssociated"
                :objectResult="scope.dataSelect.clients"
                columnToSearch="bussiness_name"></dropdown-select>
            </div>
            <!-- Mensajes de error en Nombre-->
            <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.clientAssociatedError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.clientAssociatedError }}
            </div>
        </div>        
        <!-- Estado del proyecto.-->
        <div class="mb-3">
            <label for="Status">Estado del proyecto <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <select class="form-select" v-model="scope.inputStatusSelect" title="StatusSelect">
                    <option value=0 selected disabled>Seleccione el estado</option>
                    <option v-for="(select, cursor) in scope.dataSelect.status" :key="cursor" :value="select.status_id">
                        {{ select.status_description }}
                    </option>
                </select>
            </div>
        </div>
    </fieldset>
</template>
<script>
import FontAwesome from '@/Components/FontAwesome/FontAwesome.vue';
import DropdownSelect from '@/Components/DropdownSelect.vue';
export default {
    props: {
        scope: Object, //Hereda la data del padre
        isEdit: Boolean //Cambia la información en caso de edit
    },
    components: { FontAwesome, DropdownSelect }
};
</script>