<template>
    <div class="container-fluid">
        <header>
            <Menu></Menu>
        </header>
        <main>
            <router-view></router-view>
        </main>
        <footer></footer>
    </div>
</template>

<script>

import Menu from './components/Menu.vue'
var self

export default {
    components: {
        Menu
    },
    data() {
        return {
            loading: false
        }
    },
    beforeCreate: function() {
        self = this
    },
    mounted: () => {},
    methods: {}
}

</script>

<style lang="less" src="../less/app.less"></style>
