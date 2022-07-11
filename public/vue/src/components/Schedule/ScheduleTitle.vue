<template>
    <div class="s-layout">
        <div class="s-element" v-for="(t, i) in item_list" :class="{'m': i > 0, 'bm':(i % 4 === 0)}">{{t}}</div>
    </div>
</template>

<script>
    export default {
        name: "ScheduleTitle",
        props:{
            list:{
                type: Array,
                default: ()=>[]
            },
        },
        data(){
            return {
                item_list: this.list
            }
        },
        watch:{
            list(val){
                this.item_list = val
            }
        },
        methods:{

        },
        mounted() {
        },
        computed:{

        },
        created() {

        }
    }
</script>

<style scoped>
    .s-layout{
        position: relative;
        width: 100%;
        height: auto;
        border: 1px solid #666;
    }
    .s-element{
        width: 100%;
        height: 20px;
        text-align: center;
    }
    .s-element.m{
        border-top: 1px solid #ddd;
    }
    .s-element.bm{
        border-top: 1px solid #999!important;
    }
</style>