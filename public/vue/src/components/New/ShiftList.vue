<template>
    <div>
        <Card>
            <h3 slot="title" style="margin-left: 10px;">{{field_info.name}}:シフト表 <span style="padding-left: 50px;">{{field_info.address}}</span><span style="padding-left: 50px;">電話番号:{{field_info.tel}}</span></h3>
            <Form :label-width="120" label-position="right">
                <Row>
                    <Col span="12" style="display: inline-flex">
                        <FormItem label="日付" inline>
                            <DatePicker v-model="shift_date" placeholder="Select date" @on-change="getShiftInfoList" :options="disabled_date_option" ></DatePicker>
                        </FormItem>
                        <Button type="success" style="display: inline-block;width:100px;margin-left: 10px;" long
                                @click="getShiftInfoList">検　索
                        </Button>
                    </Col>
                    <Col span="12">
                        <FormItem label="コピー登録" prop="special_date_list">
                            <DatePicker v-model="copy_date" :options="add_disabled_date_option" type="daterange" placement="bottom-end" placeholder="Select date range" @on-change="addCopyDate"></DatePicker>
                            <br/>
                            <Tag v-for="(item, index) in copy_shift_list" closable @on-close="deleteCopyDate(index)" :key="index">{{item.s_date == item.e_date ? item.s_date : item.s_date + "~" + item.e_date}}</Tag>
                        </FormItem>
                    </Col>
                </Row>
            </Form>
            <Row :gutter="16">
                <Col span="12">
                    <Button type="info"  @click="addShift">新 規</Button>
                </Col>
                <Col span="12" style="text-align: right">
                  <Button type="error"  @click="deleteAllShift">一括削除</Button>
                </Col>
            </Row>
            <ScheduleView ref="refScheduleView" :shift_select_date="shift_date" :shift_list="shift_list" :staff_list="staff_list" :post_times="post_times" :loading="loading"></ScheduleView>
            <Row :gutter="16" style="margin-top: 10px;">
                <Col span="12" style="text-align: right">
                    <Button type="primary" size="large" style="width: 120px;" :loading="loading" @click="updateShiftList">登　録</Button>
                </Col>
                <Col span="12">
                    <Button type="info" size="large"  @click="toCancel">キャンセル</Button>
                </Col>
            </Row>
        </Card>
    </div>
</template>

<script>
    import {mapGetters} from "vuex";
    import ScheduleView from '@/components/Schedule/ScheduleView.vue'
    import {
        getShiftInfo,
        deleteAllShifts,
        getShiftInfoList,
        getPostTimes,
        getStaffList,
        updateShiftList
    } from '@/api/shift_master'

    export default {
        name: "ShiftList",
        components: {
            ScheduleView
        },
        data() {
            return {
                field_id: null,
                field_info: {},
                shift_list: [],
                staff_list: [],
                search_box: {
                    name: '',
                },
                loading: false,
                shift_date: new Date(),
                post_times: [],
                shift_history_modal: false,
                day_loading:false,
                copy_date: [],
                copy_shift_list: [],

            }
        },
        methods: {
            toCancel() {
                this.$emit("toCancel")
            },
            getShiftInfoList() {
                this.loading = true
                this.getPostTimes()
                let params = {
                    field_id: this.field_id,
                    shift_date: this.$utils.Datetimes.getymd(this.shift_date),
                }
                let staff_params = {
                    field_id: this.field_id,
                    shift_date: this.$utils.Datetimes.getymd(this.shift_date),
                }
                this.$set(this, "staff_list", [])
                this.$set(this, "shift_list", [])
                getStaffList(staff_params).then(res => {
                    if (res.data.status == "success") {
                        getShiftInfoList(params).then(res1 => {
                            this.loading = false;
                            if (res1.data.status == "success") {
                                this.$set(this, "staff_list", res.data.result)
                                this.$set(this, "shift_list", res1.data.result)
                                this.$refs.refScheduleView.setScrollTop(560);
                                this.copy_shift_list = []
                            }
                        }).catch((error) => {this.loading = false;})
                    }
                })
            },
            getPostTimes() {
                let params = {
                    field_id: this.field_id,
                    shift_date: this.$utils.Datetimes.getymd(this.shift_date),
                }
                getPostTimes(params).then(res => {
                    if (res.data.status == "success") {
                        this.post_times = res.data.result ? res.data.result.post_times : []
                    }
                })
            },
            getShiftInfo(shift_id) {
                let params = {
                    shift_id: shift_id,
                }
                getShiftInfo(params).then(res => {
                    if (res.data.status == "success") {
                        this.$refs.refNewShift.showShift(res.data.result)
                        this.day_shift_modal = true
                    }
                })
            },
            addShift(){
                this.shift_list.push(
                    {
                        id: null,
                        staff_id: null,
                        field_id: this.field_info.id,
                        field_name: this.field_info.name,
                        s_time: null,
                        e_time: null,
                        ks_time: null,
                        ke_time: null
                    }
                )
            },
            deleteAllShift(){
              this.$Modal.confirm({
                title: '一括削除',
                content: '一括削除しますか？',
                okText: "はい",
                cancelText: "いいえ",
                onOk: () => {
                  let params = {
                    field_id: this.field_id,
                    shift_date: this.$utils.Datetimes.getymd(this.shift_date)
                  }
                  this.loading = true;
                  deleteAllShifts(params).then((res)=>{
                    this.loading = false
                    if(res.data.status=="success"){
                      this.$Message.success('成功');
                      this.toCancel()
                    }
                  }).catch((error) => {this.loading = false})
                },
                onCancel: () => {
                },
              });
            },
            updateShiftList(){
                if(this.shift_list.length === 0) return
                let params = {
                    field_id: this.field_id,
                    shift_date: this.$utils.Datetimes.getymd(this.shift_date),
                    shift_list: this.shift_list,
                    copy_shift_list: this.copy_shift_list
                }
                this.loading = true
                updateShiftList(params).then((res)=>{
                    this.loading = false
                    if(res.data.status=="success"){
                        this.$Message.success('成功');
                        this.toCancel()
                    }
                }).catch((error) => {this.loading = false})
            },
            showShiftList(data, shift_date) {
                this.field_id = data.id
                this.field_info = data
                this.shift_date = shift_date
                this.getShiftInfoList()
            },
            addCopyDate(){
                if(this.copy_date.length > 1){
                    let disabled = false;
                    for(let i = this.copy_date[0].getTime(); i<=this.copy_date[1].getTime();i+=86400000){
                        disabled = this.add_disabled_date_option.disabledDate(i)
                        if(disabled){
                            break;
                        }
                    }
                    if(!disabled) {
                        this.copy_shift_list.push({
                            s_date: this.$utils.Datetimes.getymd(this.copy_date[0]),
                            e_date: this.$utils.Datetimes.getymd(this.copy_date[1]),
                        })
                    }
                }
                setTimeout(()=>{
                    this.copy_date = []
                },300)
            },
            deleteCopyDate(index){
                this.copy_shift_list.splice(index,1)
            }
        },
        mounted() {
        },
        computed: {
            ...mapGetters(["screenHeight"]),
            Height() {
                return parseInt(this.screenHeight - 300)
            },
            disabled_date_option() {
                return {
                    disabledDate: (date) => {
                        if (date) {
                            for (let i = 0; i < this.copy_shift_list.length; i++) {
                                const one = this.copy_shift_list[i]
                                if (date.valueOf() >= this.$utils.Dates.str2Date(one.s_date).getTime() && date.valueOf() <= this.$utils.Dates.str2Date(one.e_date).getTime()) {
                                    return true
                                }
                            }
                        }
                        return false;
                    }
                }
            },
            add_disabled_date_option(){
                return {
                    disabledDate: (date) => {
                        if (date) {
                            if (date.valueOf() == this.shift_date.getTime()) {
                                return true
                            }
                            for (let i=0;i<this.copy_shift_list.length;i++) {
                                const one = this.copy_shift_list[i]
                                if (date.valueOf() >= this.$utils.Dates.str2Date(one.s_date).getTime() && date.valueOf() <= this.$utils.Dates.str2Date(one.e_date).getTime()) {
                                    return true
                                }
                            }
                        }
                        return false;
                    }
                }
            }
        }
    }
</script>

<style>
</style>
<style scoped>

</style>
