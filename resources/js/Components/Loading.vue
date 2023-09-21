<template>
    <div class="loading-container" v-if="active">
        <img title="logo-animacion-1" class="loading-container-imagen" id="animacionLogo" src="/images/loading-1.png" />
        <img title="logo-animacion-2" class="loading-container-imagen" src="/images/loading-2.png" />
    </div>
</template>

<script>
export default {
    props:
    {
        active: Boolean
    }
}
</script>
