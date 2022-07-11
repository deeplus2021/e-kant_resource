<template>
    <DatePicker
            :open="open"
            :value="datetime"
            type="date"
            @on-change="handleChange"
            @on-clear="handleClear"
            @on-ok="handleOk"
            @on-clickoutside="handleClear"
            style="width: 95%;"
    >
        <div class="datediv" @click="handleClick">
            <span class="iconfont" :class="iconimg"></span>
            <template v-if="datetime === ''">
                <p class="divtem temcol">{{tips}}</p>
            </template>
            <template v-else>
                <p class="divtem">{{ datetime }}</p>
            </template>
        </div>
    </DatePicker>
</template>
<style scoped>
    .datediv {
        width: 100%;
        height: 34px;
        background-color: #FFF;
        border-radius: 4px;
        border: 1px solid #d7dde4;
        transition: all .2s ease-in-out;
        cursor: pointer;
    }

    .datediv span {
        color: #7D52B3;
        line-height: 34px;
        padding-left: 4px;
    }

    .datediv:hover {
        border-color: #9826c5;
    }

    .divtem {
        padding-left: 7px;
        display: inline;
    }

    .temcol {
        color: #C3CBD6;
    }
</style>
<script>
    export default {
        props: ['iconimg', 'value', 'tips'],
        data() {
            return {
                open: false,
                datetime: ''
            }
        },
        methods: {
            handleClick() {
                this.open = !this.open;
            },
            handleChange(date) {
                this.datetime = date;
                this.$emit('changedate', date);
                this.open = false;
            },
            handleClear() {
                this.open = false;
            },
            handleOk() {
                this.open = false;
            }
        }
    }
</script>
