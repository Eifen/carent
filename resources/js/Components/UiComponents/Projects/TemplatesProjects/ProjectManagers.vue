<template lang="">
    <!-- Carga de horas -->
    <span class="badge text-bg-info" v-if="scope.inputDepartments.length != 0">Indique la cantidad de horas por división</span>
    <fieldset :class="scope.formClass.fieldset"
    v-if="scope.inputDepartments.length != 0"
    v-for="(select,cursor) in scope.inputDepartments.length"
    :key="cursor">
        <!-- Division -->
        <div class="mb-3">
            <label for="projectDescription">División:</label>
            <div class="input-group">
                {{ scope.dataSelect.managersPerDepartment[cursor].departmentName}}
            </div>
        </div>
        <!-- Gerentes -->
        <div class="mb-3">
            <label for="Managers">Gerentes <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <select class="form-select" v-model="scope.dataSelect.managersPerDepartment[cursor].selectManager" title="ManagersSelect">
                    <option value=0 selected disabled>Seleccione una opción</option>
                    <option v-for="(select2, cursor2) in scope.dataSelect.managersPerDepartment[cursor].managersDepartment" :key="cursor2" :value="select2.user_id">
                        {{ select2.user_name }}
                    </option>
                </select>
            </div>
        </div>
        <!-- Horas asignadas -->
        <div class="mb-3" v-if="scope.dataSelect.managersPerDepartment[cursor].selectManager != 0">
            <label for="Hours">Horas asignadas <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon8">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input
                type="text"
                class="form-control"
                id="Hours"
                aria-describedby="basic-addon8"
                v-model="scope.dataSelect.managersPerDepartment[cursor].hoursAssigned"
                @input="$emit('total-hours',scope.dataSelect.managersPerDepartment)"/>
                <span class="input-group-text" id="basic-addon11" v-if="isEdit">
                    <project-additional-modal
                    modal-title="Horas Adicionales"
                    id-modal="additionalHours"></project-additional-modal>
                </span>
            </div>
        </div>
    </fieldset>
</template>
<script>
import FontAwesome from '@/Components/FontAwesome/FontAwesome.vue';
import ProjectAdditionalModal from '@/Components/UiComponents/Projects/TemplatesProjects/ProjectAdditionalModal.vue';

export default {
    props: {
        scope: Object, //Hereda la data del padre
        isEdit: Boolean //Cambia la información en caso de edit
    },
    emits: ['total-hours'],
    methods: {},
    components: { FontAwesome, ProjectAdditionalModal }
};
</script>
