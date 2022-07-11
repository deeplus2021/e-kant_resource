<template>
    <div>
		<span
                class="div-span"
                v-for="(value,index) in num"
                :class="{ 'pu': value == 0, 'zs': value == 1,'ro':value == 2}"
        ></span>
    </div>
</template>
<style scoped>
    .div-span {
        display: inline-block;
        height: 18px;
        width: 4px;
        margin-right: 5px;
        border-radius: 15px;
    }

    .pu {
        background-color: #DCDCDC;
    }

    .zs {
        background-color: #C77FF9;
    }

    .ro {
        background-color: #D60A6D;
    }
</style>
<script>
    export default {
        props: ['num'],
        data() {
            return {}
        }
    }
</script>