<template>
    <div>
        <select class="form-select" v-model="inputConceptSelect" title="ConceptSelect">
            <option value=0 disabled>Seleccione el concepto</option>
        </select>
        <textarea type="text" class="form-control" placeholder="Direccion del cliente" id="direccion"
            aria-describedby="basic-addon4"></textarea>
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
