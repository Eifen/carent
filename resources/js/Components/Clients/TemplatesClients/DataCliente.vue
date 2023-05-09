<template>
    <fieldset :class="scope.formClass.fieldset">
        <!-- RIF-->
        <div class="mb-3">
            <label for="Nit">NIT</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon1">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input
                type="text"
                class="form-control"
                placeholder="Ejemplo: 87595632147"
                id="Nit"
                aria-describedby="basic-addon1"
                v-model="scope.inputNit"/>
            </div>
        </div>
        <!-- RIF-->
        <div class="mb-3">
            <label for="Rif">RIF <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon2">
                    <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                </span>
                <input
                type="text"
                class="form-control"
                placeholder="Iniciar con: V, E, P, G, J, C"
                id="Rif"
                aria-describedby="basic-addon2"
                v-model="scope.inputRif"/>
            </div>
            <!-- Mensajes de error en Rif-->
            <div :class="scope.formClass.failureValidation"
                v-if="scope.messages.error.rifError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.rifError }}
            </div>
        </div>
        <!-- Razon social -->
        <div class="mb-3">
            <label for="razonSocial">Nombre o razón social <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon3">
                    <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                </span>
                <input
                type="text"
                class="form-control"
                placeholder="Ejemplo: Banco de Venezuela"
                id="razonSocial"
                aria-describedby="basic-addon3"
                v-model="scope.inputRazonSocial"/>
            </div>
            <!-- Mensajes de error en Nombre-->
            <div :class="scope.formClass.failureValidation"
                v-if="scope.messages.error.razonSocialError != ''">
                <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                {{ scope.messages.error.razonSocialError }}
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
