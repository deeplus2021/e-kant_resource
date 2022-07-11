<template>
    <div style="position: relative;font-size: 10px;">
        <img class="img-b" :src="require(`../assets/images/icon/${imgUrl}.png`)"/>
        <Button class="button-d" @click="click">
            <slot></slot>
        </Button>
    </div>
</template>

<script>
    export default {
        props: {
            'img': {
                type: Number,
                default: 1
            }
        },
        methods: {
            click() {
                this.$emit('click')
            }
        },
        computed: {
            imgUrl() {
                let url = 'b_t'
                switch (this.img) {
                    case 1://完成
                        url = 'b_t';
                        break;
                    case 2://一般
                        url = 'b_b';
                        break;
                    case 3://未完成
                        url = 'b_f';
                        break;
                    default:
                        url = 'b_f';
                        break;
                }
                return url;
            }
        }
    }
</script>

<style scoped>
    .img-b {
        position: absolute;
        height: 14px;
        width: 14px;
    }

    .button-d {
        margin-top: 4px;
        margin-left: 4px;
    }
</style>
<style rel="stylesheet/scss" lang="scss" scoped>
    .button-d {
        height: calc(100% - 4px);
        width: calc(100% - 4px);
    }
</style>
