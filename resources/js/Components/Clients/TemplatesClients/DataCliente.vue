<template>
    <fieldset :class="scope.formClass.fieldset">
        <!-- NIT-->
        <div class="mb-3" v-if="!enableEdit">
        <label for="Nit">NIT<span :class="scope.formClass.requiredField">*</span></label>
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
            v-model="scope.inputNit"
            :disabled="enableEdit ? true : false"/>
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
