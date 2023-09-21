<template>
    <fieldset :class="scope.formClass.fieldset">
        <!-- RIF-->
        <div class="mb-3">
            <label for="Nit">NIT</label>
            <div class="input-group">
                <span class="input-group-text">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input type="text" class="form-control" placeholder="Ejemplo: 87595632147" id="Nit"
                    v-model="scope.inputNit" />
            </div>
        </div>
        <!-- RIF-->
        <div class="mb-3">
            <label for="Rif">RIF <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input type="text" class="form-control" placeholder="Iniciar con: V, E, P, G, J, C" id="Rif"
                    v-model="scope.inputRif" />
            </div>
            <!-- Mensajes de error en Rif-->
            <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.rifError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.rifError }}
            </div>
        </div>
        <!-- Razon social -->
        <div class="mb-3">
            <label for="razonSocial">Nombre o razón social <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text">
                    <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                </span>
                <input type="text" class="form-control" placeholder="Ejemplo: Banco de Venezuela" id="razonSocial"
                    v-model="scope.inputRazonSocial" />
            </div>
            <!-- Mensajes de error en Nombre-->
            <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.razonSocialError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.razonSocialError }}
            </div>
        </div>
        <!-- Selección de Sector -->
        <div class="mb-3">
            <label for="Sector">Sector <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <select class="form-select" title="SectorSelect" v-model="scope.inputSectorSelect">
                    <option v-for="(select, cursor) in scope.dataSelect.sectores" :key="cursor" :value="select.sector_id"
                        :disabled="select.sector_id == 0" :selected="select.sector_id == 0">
                        <span v-if="select.sector_id != 0">{{ select.sector_name }}</span>
                        <span v-if="select.sector_id == 0">Seleccione el Sector</span>
                    </option>
                </select>
            </div>
        </div>
        <!-- Selección de Servicio -->
        <div class="mb-3">
            <label for="Sector">Servicio <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <select class="form-select" title="ServicioSelect" v-model="scope.inputServicioSelect">
                    <option v-for="(select, cursor) in scope.dataSelect.servicios" :key="cursor" :value="select.service_id"
                        :disabled="select.service_id == 0" :selected="select.service_id == 0">
                        <span v-if="select.service_id != 0">{{ select.service_name }}</span>
                        <span v-if="select.service_id == 0">Seleccione el Servicio</span>
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
