<template>
    <div>
        <select class="form-select" v-if="isEdit" v-model="inputHourSelected" title="ConceptSelect" autocomplete="nope">
            <option v-for="(hour, cursor) in limitHours" :key="cursor" :value="cursor" :selected="cursor == 0"
                :disabled="cursor == 0">{{
                    hour.label }}</option>
        </select>
        <span class="no-edit" v-if="!isEdit && infoNoEdit != 0">{{
            infoNoEdit }} Horas cargadas</span>
        <textarea v-if="inputHourSelected != 0 && isEdit" type="text" rows="5"
            class="form-control register-hour-select-hours" placeholder="Observaciones" id="observation"
            aria-describedby="observation" autocomplete="nope" v-model="inputObservation"></textarea>
        <div v-if="loadRef == 'admin' && statusLoad == 1" class="admin_message badge bg-warning text-dark">Por aprobar</div>
        <div v-if="loadRef == 'admin' && statusLoad == 2" class="admin_message badge bg-success text-dark">Aprobada por {{
            aprrovedCode }}</div>
        <div v-if="loadRef == 'admin' && statusLoad == 3" class="admin_message badge bg-danger text-dark">No aprobada por {{
            aprrovedCode }}</div>
        <!-- Mensajes de error en Observaciones-->
        <div class="form-ErrorInput" v-if="inputObservationError != ''">
            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
            {{ inputObservationError }}
        </div>
        <div class="register-hour-check" v-if="inputObservation.length >= 7 && !isCharged && isEdit"
            @click="registerHour(inputHourSelected)">
            <font-awesome string-icon="fa-solid fa-check"></font-awesome>
        </div>
        <div class="register-hour-uncheck" v-if="inputObservation.length >= 7 && !isCharged && isEdit"
            @click="unRegisterHour(inputHourSelected)">
            <font-awesome string-icon="fa-solid fa-xmark"></font-awesome>
        </div>
    </div>
</template>
<script>
import Multiselect from "@vueform/multiselect";
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";
export default {
    props: {
        associatedLoadProject: Number, //Almacena el valor del id del la hora cargada
        infoAssignedProject: Array, //Array que almacena la información de carga de horas por día
        associatedDay: String, //Día seleccionado
        loadRef: String, //Texto que almacena el tipo de carga, project o admin
        isCharged: Boolean, //Control del boton
        statusProject: Number //Controla el estado del proyecto, solo aplica si loadRed es project
    },
    emits: ['register-hour', 'unregister-hour'],
    data() {
        return {
            limitHours: [{
                value: 0,
                label: "Horas trabajadas"
            }], // Array que muestra las horas en un intervalo de 0.30 a 11 horas
            intervalHours: 1 / 2, //Determina el intervalo entre horas en fracciones
            maximumHours: 12, // Multiplicador de horas
            inputHourSelected: 0, //Controla el seleccionador de la hora
            inputObservation: "", //Controla el input de las observaciones
            inputObservationError: "", //Controla el input de errores de observaciones
            statusLoad: "", //Solo para horas administrativas, muestra el estado de aprovacion de dicha hora
            aprrovedCode: null, //Codigo de aprobacion para esa hora administrativa.
            errorMessageObservation: "minimo 7 caracteres", //Mensaje de error para el tamaño minimo de las observaciones
            isEdit: true, //Valida si se encuentra en la fecha actual o 1 mes antes
            infoNoEdit: 0,
            dayClosure: 21 //Define el dia a cerrar la carga
        }
    },
    created() {
        const dateNow = new Date();
        dateNow.setHours(0, 0, 0, 0)
        const dateClosure = new Date(dateNow.getFullYear(), dateNow.getMonth(), this.dayClosure)
        const isDate = (dateNow.getTime() >= dateClosure.getTime())
        const splitDate = this.associatedDay.split("-")
        const dateAssociated = new Date(Number(splitDate[0]), Number(splitDate[1]) - 1, Number(splitDate[2]));
        //Determinamos en intervalo se encuentra la fecha
        let diffYear = dateClosure.getFullYear() - dateAssociated.getFullYear();
        let diffMonth = dateClosure.getMonth() - dateAssociated.getMonth();
        let diffDay = (dateClosure.getTime() - dateAssociated.getTime()) / (1000 * 3600 * 24)
        //Comparamos las fechas
        diffYear === 0 && diffMonth <= 3
            ? (isDate && diffDay >= this.dayClosure ? this.isEdit = false : this.isEdit = true)
            : this.isEdit = false;
        //Proceso de carga de horas\
        let fraccionCount = 0;
        for (let countHours = 0; countHours < (this.maximumHours / this.intervalHours); countHours++) {
            //Sumamos la fraccion
            fraccionCount = fraccionCount + this.intervalHours
            const hourFraccion = 60 * (fraccionCount % 1)
            //Agregamos la fraccion
            this.limitHours.push({
                value: fraccionCount,
                label: `${Math.trunc(fraccionCount).toString().padStart(2, '0')}:${hourFraccion.toString().padStart(2, '0')}`,
                day: this.associatedDay
            })

        }
    },
    beforeMount() {
        //Asignamos los valores por defecto
        //Hora por defecto. Debemos mapear la información para saber si coincide en funcion de la referencia
        switch (this.loadRef.toLowerCase()) {
            //Administrador
            case 'admin':
                this.loadAdmin();
                break;

            //Proyectos
            case 'project':
                this.loadProjects();
                break;
        }
    },
    methods: {
        /**
         * Metodo de inicializacion que configura las horas cargables a proyectos
         */
        loadProjects() {
            this.statusProject == 2 ? this.isEdit = false : null
            const indexAssigned = this.infoAssignedProject.map(assigned => { return assigned.user_assigned_id }).indexOf(this.associatedLoadProject);
            if (indexAssigned != -1) {
                //Si coincide asignamos la informacion de la hora cargada
                this.infoAssignedProject.forEach(assigned => {
                    //La fecha y el id de la asignacion deben coincidir para poder cargar el valor
                    if (assigned["register_date"] === this.associatedDay && assigned["user_assigned_id"] === this.associatedLoadProject) {
                        //Mapeamos el limite de horas y asignamos
                        const indexLimit = this.limitHours.map(hours => { return hours.value }).indexOf(parseFloat(assigned["register_hour"]))
                        this.inputHourSelected = indexLimit
                        //Observacion por defecto
                        this.inputObservation = assigned["project_load_observation"]
                        //Almacenamos la hora carga si no corresponde el intervalo
                        if (!this.isEdit) this.infoNoEdit = parseFloat(assigned["register_hour"]);
                    }
                })
            }
        },
        /**
         * Metodo de inicializacion que configura las horas cargables a conceptos administrativos
         */
        loadAdmin() {
            const indexAssigned = this.infoAssignedProject.map(assigned => { return assigned.admin_hours_id }).indexOf(this.associatedLoadProject);
            if (indexAssigned != -1) {
                //Si coincide asignamos la informacion de la hora cargada
                this.infoAssignedProject.forEach(assigned => {
                    //La fecha y el id de la asignacion deben coincidir para poder cargar el valor
                    if (assigned["register_date"] === this.associatedDay && assigned["admin_hours_id"] === this.associatedLoadProject) {
                        //Mapeamos el limite de horas y asignamos
                        const indexLimit = this.limitHours.map(hours => { return hours.value }).indexOf(parseFloat(assigned["register_hour"]))
                        this.inputHourSelected = indexLimit
                        //Observacion por defecto
                        this.inputObservation = assigned["admin_load_observation"]
                        this.statusLoad = assigned["status_load_id"]
                        this.aprrovedCode = assigned["approved_code"]
                        //Almacenamos la hora carga si no corresponde el intervalo
                        if (!this.isEdit) this.infoNoEdit = parseFloat(assigned["register_hour"]);
                    }
                })
            }
        },
        /**
         * Metodo que se encarga de realizar un emit al padre para cargar la hora
         * @param {*} idHourSelected El id del selector de horas
         * Retorna una tupla donde [0] son las horas, y [1] las observacion
         */
        registerHour(idHourSelected) {
            this.$emit('register-hour', [this.limitHours[idHourSelected], this.inputObservation])
        },
        /**
         * Metodo que elimina una columna de la grilla
         */
        unRegisterHour(idHourSelected) {
            this.$emit('unregister-hour', [this.limitHours[idHourSelected], this.inputObservation])
        }
    },
    watch: {
        inputObservation(newObservation) {
            try {
                //Registramos el error
                if (newObservation.length < 7) throw this.errorMessageObservation
                //Si pasa la validaciones borramos los errores
                if (newObservation.length >= 7) this.inputObservationError = "";
            } catch (error) {
                //Mostramos el error
                this.inputObservationError = error
            }
        },
        isEdit(newEdit) {
            !newEdit
                ? this.inputObservationError = "No tienes permiso para cargar esta fecha"
                : this.inputObservationError = ""
        }
    },
    components: { Multiselect, FontAwesome }
}
</script>
