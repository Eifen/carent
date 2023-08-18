<!-- Este componente sirve como intermedio entre blade y vue en el caso que tengamos que cargar una variable de php sobre el entorno Js -->
<template>
    <div>

    </div>
</template>
<script>
export default {
    emits: ["prepareSettings"],
    created() {
        this.$emit("prepareSettings")
    }
}
</script>
