<template>
    <div class="loading-container" v-if="active">
        <img class="loading-container-imagen" id="animacionLogo" src="/images/loading-1.png"/>
        <img class="loading-container-imagen" src="/images/loading-2.png"/>
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
