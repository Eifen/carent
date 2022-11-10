<template>
    <h1>Hello User</h1>
</template>


<script>

var self

import VueNumeric from 'vue-numeric';
import { useUtils } from '../../js/components/Utils.js'
const utils = useUtils()
var self

export default {
    components: {
        VueNumeric
    },
    data() {
        return {
            loading: false
        }
    },
    beforeCreate: function() {
        self = this
    },
    mounted: () => {

    },
    methods: {



    }
}

</script>

<style lang="less" scoped src="../../less/usuario/NewUser.less"></style>
