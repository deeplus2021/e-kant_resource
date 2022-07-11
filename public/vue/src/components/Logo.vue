<template>
    <div class="logo-img">
        <div class="logo-span">
            <p class="span-p span-top"></p>
            <p class="span-p span-bottom"></p>
        </div>
    </div>
</template>

<script>
    import {
        mapGetters
    } from 'vuex'

    export default {
        computed: {}
    }
</script>

<style scoped>
    .logo-img {
        display: flex;
        justify-content: center;
        line-height: 1;
    }

    .layout-imgs {
        height: 46px;
        width: 82px;
    }

    .logo-span {
        display: block;
    }

    .span-p {
        height: 17px;
        font-size: 18px;
        font-family: PingFang SC;
        color: rgba(255, 255, 255, 1);
        margin-left: 10px;
    }

    .span-top {
        font-weight: bold;
        line-height: 21px;
    }

    .span-bottom {
        font-weight: 400;
        line-height: 39px;
    }
</style>
