<template>
    <div style="display: inline-flex">
        <Select v-model="s_h" class="select" @on-change="onChangeH" style="width: 55px">
            <Option v-for="(v, idx) in hours_list" :value="v" :key="idx" :disabled="(gte>(v*60+s_m))||(lte<(v*60+s_m))">{{format(v)}}</Option>
        </Select>
        <span class="snap"> : </span>
        <Select v-model="s_m" class="select" @on-change="onChangeM" style="width: 55px">
            <Option v-for="(v, idx) in minutes_list" :value="v" :key="idx" :disabled="((s_h*60+v)>end_hour*60)||(gte>(s_h*60+v))||(lte<(s_h*60+v))">{{format(v)}}</Option>
        </Select>
    </div>
</template>

<script>
    export default {
        name: "TimeSelector",
        props: {
            value: {
                type: Number,
                defalut: 0
            },
            end_hour: {
                type: Number,
                default: 36
            },
            steps: {
                type: [Number, Array],
                default: function () {
                    return [1, 15]
                }
            },
            gte:{
                type: Number,
                default: 0
            },
            lte:{
                type: Number,
                default: 60*36
            }
        },
        data() {
            return {
                hour: parseInt(this.value / 60),
                minute: parseInt(this.value % 60),
                hours_list: [],
                minutes_list: [],
            }
        },
        watch: {},
        methods: {
            format(v) {
                return v < 10 ? "0" + v : "" + v
            },
            onChangeH(val){
                this.hour = val
                this.$emit('on-change',parseInt(val * 60) + parseInt(this.minute))
            },
            onChangeM(val){
                this.minute = val
                this.$emit('on-change', parseInt(this.hour * 60) + parseInt(val))
            }
        },
        mounted() {
            this.hours_list = []
            for (let i = 0; i <= this.end_hour; i += this.steps[0]) {
                this.hours_list.push(i)
            }
            this.minutes_list = []
            for (let i = 0; i < 60; i += this.steps[1]) {
                this.minutes_list.push(i)
            }
        },
        computed: {
            s_h: {
                get() {
                    try {
                        let v = parseInt(this.value / 60)
                        return this.hours_list.includes(v) ? v : 0
                    } catch (e) {
                        return 0
                    }
                },
                set(v) {
                    this.$emit('input', v * 60 + this.s_m)
                }

            },
            s_m: {
                get() {
                    try {
                        let v = parseInt(this.value % 60)
                        return this.minutes_list.includes(v) ? v : 0
                    } catch (e) {
                        return 0
                    }
                },
                set(v) {
                    this.$emit('input', this.s_h * 60 + v)
                }
            }
        }
    }
</script>

<style scoped>
    .snap {
        font-size: 14px;
        padding: 0 3px;
    }
</style>
