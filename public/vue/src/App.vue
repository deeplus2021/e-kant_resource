<template>
    <div id="app">
        <router-view/>
    </div>
</template>
<script>
    import {mapActions, mapGetters, mapMutations} from 'vuex'
    export default {
        name: 'App',
        methods: {
            ...mapMutations(["setScreenHeight"])
        },
        mounted() {
            this.setScreenHeight(document.documentElement.clientHeight);
            window.onresize = () => {
                this.setScreenHeight(document.documentElement.clientHeight);
            }
        }
    }
</script>

<style scoped>
    #app{
        min-width: 1000px;
    }
</style>

<style>
    .ivu-modal-close .ivu-icon-ios-close{
        top: 4px!important;
        right: 4px!important;
    }
    .ivu-form-item-error-tip{
        font-size: 11px!important;
    }
    .zZindex {
        z-index:5000 !important;
    }
</style>