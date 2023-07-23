<template>
    <div>

    </div>
</template>
<script>
export default {
    emits: ["init-project"],
    props: {
        infoProject: Object, //Informacion del proyecto y sus facturaciones realizadas
    },
    data() {
        return {}
    },
    created() {
        //Cargamos la informacion del proyecto
        this.$emit("init-project");
        console.log(this.infoProject)
    }
}
</script>
