<template>
    <div>
        <Select class="s-select" v-model="selected_id" @on-change="onChange"
                :style="{'width': width + 'px', 'height': height + 'px'}">
            <Option v-for="(item, item_key) in options" :value="item.id" :key="item.id"
                    :disabled="disabled_options.includes(item.id)">
                {{item.name}}({{item.email}})
            </Option>
        </Select>
    </div>
</template>

<script>
    import {
        checkStaffHoliday,
    } from '@/api/shift_master'

    export default {
        name: "HeaderSelect",
        props: {
            value: {
                type: Number,
                default: null
            },
            options: {
                type: Array,
                default: () => []
            },
            selected_options: {
                type: Array,
                default: () => []
            },
            shift_date: {
                type: Date,
                default: new Date()
            },
            width: {
                type: Number,
                default: 150
            },
            height: {
                type: Number,
                default: 30
            },
            shift_index: {
                type: Number,
                default: 0
            }
        },
        data() {
            return {
                items: [],
                disabled_options: this.selected_options,
                staff_shift_date: this.shift_date,
                old_selected_id: null,
                staff_dict: {}
            }
        },
        watch: {
            value(val, old) {
                this.selected_id = val
                this.old_selected_id = old
            },
            selected_options(val) {
                this.disabled_options = val
            },
            shift_date(val) {
                this.staff_shift_date = val
            },
            options(val) {
                this.staff_dict = {}
                val.map((staff) => {
                    this.staff_dict[staff.id] = staff
                })
            }
        },
        methods: {
            onChange(val) {
                if (!val) return
                const params = {
                    staff_id: val,
                    shift_date: this.$utils.Datetimes.getymd(this.staff_shift_date)
                }
                checkStaffHoliday(params).then((res) => {
                    if (res.data.status == "success") {
                        if (res.data.result) {
                            let _this = this
                            this.$Modal.confirm({
                                title: 'シフト登録',
                                content: '対象スタッフは原則休日希望日に登録されてありますが、シフト登録してもよろしいですか？',
                                okText: "はい",
                                cancelText: "いいえ",
                                onOk: () => {
                                    _this.$emit("input", val)
                                    let index = 0
                                    for (let i = 0; i < this.options.length; i++) {
                                        if (this.options.id == val) {
                                            index = i
                                            break
                                        }
                                    }
                                    _this.$emit("change", {shift_index: _this.shift_index, staff_index: index})
                                },
                                onCancel: () => {
                                    _this.$set(_this, "selected_id", _this.old_selected_id)
                                },
                            });
                        }
                    }
                })
            }
        },
        computed: {
            selected_id: {
                get() {
                    return this.value
                },
                set(val) {
                    this.$emit("input", val)
                    let index = 0
                    for (let i = 0; i < this.options.length; i++) {
                        if (this.options.id == val) {
                            index = i
                            break
                        }
                    }
                    this.$emit("change", {shift_index: this.shift_index, staff_index: index})
                }
            }
        }
    }
</script>
<style scoped>
    .s-select {
        color: #9d2dee;
    }
</style>