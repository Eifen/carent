<template>
    <div>
        <Multiselect v-model="selectModel" :mode="selectMode" :close-on-select="true" :searchable="true"
            :placeholder="placeholderMessage" openDirection="top" :options="listOptions"></Multiselect>
    </div>
</template>
<script>
import Multiselect from "@vueform/multiselect";
export default {
    props: {
        selectModel: Array, //Variable del v-model
        selectMode: String, //mode del multiSelect
        placeholderMessage: String, //Información del select
        listOptions: Array, //Opciones del multiSelect
    },
    components: { Multiselect }
}
</script>
