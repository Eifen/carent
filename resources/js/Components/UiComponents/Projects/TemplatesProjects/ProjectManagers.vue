<template>
    <!-- Carga de horas -->
    <span class="badge text-bg-info" v-if="scope.inputDepartments.length != 0">Indique la cantidad de horas por
        división</span>
    <fieldset :class="scope.formClass.fieldset" v-if="scope.inputDepartments.length != 0"
        v-for="(select, cursor) in scope.inputDepartments.length" :key="cursor">
        <!-- Division -->
        <div class="mb-3">
            <label for="projectDescription">División:</label>
            <div class="input-group">{{ scope.dataSelect.managersPerDepartment[cursor].departmentName }}
            </div>
        </div>
        <!-- Gerentes -->
        <div class="mb-3">
            <label for="Managers">Gerentes
                <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <select class="form-select" v-model="scope.dataSelect.managersPerDepartment[cursor].selectManager"
                    title="ManagersSelect">
                    <option value="0" selected disabled>
                        Seleccione una opción
                    </option>
                    <option v-for="(select2, cursor2) in scope.dataSelect.managersPerDepartment[cursor].managersDepartment"
                        :key="cursor2" :value="select2.user_id">
                        {{ select2.user_name }}
                    </option>
                </select>
            </div>
        </div>
        <!-- Horas asignadas -->
        <div class="mb-3" v-if="scope.dataSelect.managersPerDepartment[cursor].selectManager != 0">
            <label for="Hours">Horas asignadas
                <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input type="text" class="form-control" id="Hours"
                    v-model="scope.dataSelect.managersPerDepartment[cursor].hoursAssigned"
                    @input="$emit('total-hours', scope.dataSelect.managersPerDepartment)" />
                <span class="input-group-text" v-if="isEdit">
                    <project-additional-modal modal-title="Horas Adicionales" :name-modal="'additionalHours' + cursor"
                        table-modal-title="Lista de horas" table-modal-insert="Agregar hora adicional"
                        :last-id-table="scope.lastHoursId" :modal-columns="modalColumns" :scope-modal="scope"
                        @asign-list="asignHourList(scope.dataSelect.managersPerDepartment[cursor].departmentId)"
                        :list-info="scope.listInfoToUpdate"
                        @update-modal="updateHourModal($event, scope.dataSelect.managersPerDepartment[cursor].departmentId)"
                        error-message="No posee horas adicionales" @prepare-save="prepareInfoTransfer"
                        :key="scope.lastHoursId"></project-additional-modal>
                </span>
            </div>
        </div>
        <!-- Horas Cargadas -->
        <div class="mb-3" v-if="scope.dataSelect.managersPerDepartment[cursor].selectManager != 0 && isEdit">
            <label for="HoursRegister">Horas cargadas</label>
            <div class="input-group">
                <span class="input-group-text">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <span class="form-control">{{ scope.dataSelect.managersPerDepartment[cursor].registerHour }}</span>
            </div>
        </div>
    </fieldset>
</template>
<script>
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";
import ProjectAdditionalModal from "@/Components/UiComponents/Projects/TemplatesProjects/ProjectAdditionalModal.vue";

export default {
    props: {
        scope: Object, //Hereda la data del padre
        isEdit: Boolean, //Cambia la información en caso de edit
    },
    data() {
        return {
            modalColumns: {
                column1: "Codigo",
                column2: "Hora",
                column3: "Division",
                column4: "Fecha",
                column5: "Estatus",
                settings: { columnS1: "Inactivar", columnS2: "Activar" },
            },
            lastDepartmentId: 0, //almacena el ultimo id seleccionado
        }
    },
    emits: ["total-hours", "reload-changes"],
    methods: {
        /**
         * Metodo que captura el evento emitido por el componente hijo del modal y la envia a un metodo global
         * @param {*} paramCatch Captura la información del array usado para el modal
         */
        prepareInfoTransfer(paramCatch) {
            //Actualizamos el ID
            this.scope.lastHoursId = paramCatch.id
            //Emite informacion al padre
            this.$emit('reload-changes', {
                arrayToAssign: paramCatch.info,
                arrayTarget: "additionalHours",
                refs: ["hours_id", "additional_hour", "department_id", "register_date", "status_id"]
            })
        },
        /**
         * Metodo que e encarga de asignar la información a las diferentes variables de la data
         * @param {Number} departmentId Captura el id del departamento
         */
        asignHourList(departmentId) {
            //Asignamos la propiedad a la su objeto de transferencia de Cancelación de cambios
            this.lastDepartmentId = departmentId //Asignamos el ultimo id seleccionado antes de filtrar
            this.scope.listInfoToCancel = this.scope.dataSelect.additionalHours.filter(object => {
                return object['department_id'].toString().toLowerCase().includes(departmentId.toString().toLowerCase())
            })
            //Inicializamos la información dependiendo de la tabla
            this.scope.listInfoToUpdate = this.scope.listInfoToCancel.map(object => {
                //Una vez capturamos los valores de las columnas, las asignamos un nuevo objeto
                const copyObject = {
                    código: object.hours_id,
                    hora: parseInt(object.additional_hour),
                    division: object.department_id,
                    fecha: object.register_date,
                    estatus: object.status_id
                }

                return copyObject;
            })
        },
        /**
         * Metodo que añade una nueva fila al modal de horas
         * @param {*} lastValueId Almacena el siguiente id de horas adicionales
         * @param {int} departmentId Almacena el id del departamento, area, division seleccionada
         */
        updateHourModal(lastHourId, departmentId) {
            this.scope.listInfoToUpdate.push({
                código: lastHourId,
                monto: parseInt(this.scope.additionalInput),
                division: departmentId,
                fecha: new Date(Date.now()).toISOString().substring(0, 10), //Formato de fecha YYYY-mm-dd,
                estatus: 1
            })
        }
    },
    components: { FontAwesome, ProjectAdditionalModal },
};
</script>
