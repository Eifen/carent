<template>
    <div>
        
    </div>
</template>
<script>
import FontAwesome from '@/FontAwesome/FontAwesome.vue';

export default {
    props: {
        scope: Object, //Hereda la data del padre
        isEdit: Boolean //Cambia la información en caso de edit
    },
    components: { FontAwesome }
};
</script>