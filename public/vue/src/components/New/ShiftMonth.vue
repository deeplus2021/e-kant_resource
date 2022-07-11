<template>
    <div>
        <Card>
            <h3 slot="title" style="margin-left: 10px;">{{field_info.name}}:シフト表 <span style="padding-left: 50px;">{{field_info.address}}</span><span style="padding-left: 50px;">電話番号:{{field_info.tel}}</span></h3>
            <div class="calendar-card">
                <FullCalendar v-if="field_id"
                        class='demo-app-calendar'
                        :options='calendarOptions'
                >
                </FullCalendar>
                <Spin size="large" fix v-if="spinShow"></Spin>
            </div>
            <div>
                <Form :label-width="80" label-position="right">
                    <Row>

                        <Col span="6">
                            <FormItem label="日付" inline>
                                <DatePicker v-model="shift_date" placeholder="Select date"></DatePicker>
                            </FormItem>
                        </Col>
                        <Col span="6">
                            <Button type="success" style="display: inline-block;width:200px;margin-left: 10px;" long
                                    @click="showDayShift">帳票印刷(１日単位)
                            </Button>
                        </Col>
                    </Row>
                    <Row>
                        <Col span="6">
                            <FormItem label="年月" inline>
                                <DatePicker v-model="shift_month" type="month" placeholder="Select month"
                                            style="width: 200px"></DatePicker>
                            </FormItem>
                        </Col>
                        <Col span="6">
                            <Button type="success" style="display: inline-block;width:150px;margin-left: 10px;" long
                                    @click="getShiftMonthInfo">帳票印刷(１ヶ月分)
                            </Button>
                        </Col>
                        <Col span="6">
                            <FormItem label="週間" inline>
                                <DatePicker v-model="shift_week" type="daterange" placeholder="Select week"
                                            style="width: 200px"></DatePicker>
                            </FormItem>
                        </Col>
                        <Col span="6">
                            <Button type="success" style="display: inline-block;width:150px;margin-left: 10px;" long
                                    @click="getShiftWeekInfo">帳票印刷(週単位)
                            </Button>
                        </Col>

                    </Row>
                </Form>
            </div>
        </Card>
        <Modal v-model="day_shift_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="1200" :styles="{top: '70px'}">
            <DayShift ref="refDayShift" :field_id="field_id" :field_name="field_name" :shift_date="shift_date"></DayShift>
        </Modal>
        <Modal v-model="shift_month_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="1200" :styles="{top: '70px'}">
            <ShiftList ref="refShiftList" @toCancel="toCancel"></ShiftList>
        </Modal>
        <Modal v-model="shift_history_modal" :mask-closable="false" :closable="true" :footer-hide="true"
               width="1000">
            <ShiftHistory ref="refShiftHistory"></ShiftHistory>
        </Modal>
    </div>
</template>

<script>
    import FullCalendar from '@fullcalendar/vue'
    import dayGridPlugin from '@fullcalendar/daygrid'
    import interactionPlugin from '@fullcalendar/interaction'
    import {mapGetters} from "vuex";
    import {
        getShiftMonthInfo,
        getShiftWeekInfo,
        getShiftList
    } from '@/api/shift_master'
    import ShiftList from "../../components/New/ShiftList";
    import ShiftHistory from "./ShiftHistory";
    import DayShift from "./DayShift";
    export default {
        name: "ShiftMonth",
        components:{
            FullCalendar,
            ShiftList,
            ShiftHistory,
            DayShift
        },
        data(){
            return{
                field_id: null,
                field_name: '',
                field_info: {},
                spinShow:false,
                calendarOptions: {},
                currentEvents: [],
                shift_month_modal: false,
                shift_history_modal:false,
                shift_month: null,
                shift_week: [],
                shift_date: new Date(),
                day_shift_modal:false
            }
        },
        watch:{
            shift_month_modal(val){
                if(!val){
                    this.showShiftMonth(this.field_info)
                }
            }
        },
        methods:{
            handleDateSelect(selectInfo) {
                this.shift_date = selectInfo.start
                this.showShiftList()
            },
            toCancel(){
                console.log("cancel")
                this.shift_month_modal = false
            },
            showShiftList(){
                this.$refs.refShiftList.showShiftList(this.field_info, this.shift_date)
                this.shift_month_modal = true
            },
            showDayShift(){
                this.$refs.refDayShift.showDayShift()
                this.day_shift_modal = true
            },
            showShiftMonth(data) {
                this.field_id = data.id
                this.field_info = data
                this.field_name = data.name
                let _this = this
                let option = {
                    locale: 'ja',
                    plugins: [
                        dayGridPlugin,
                        interactionPlugin // needed for dateClick
                    ],
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: null
                    },
                    buttonText:{
                        today : '今日'
                    },
                    initialView: 'dayGridMonth',
                    initialEvents: [], // alternatively, use the `events` setting to fetch from a feed
                    editable: false,
                    selectable: true,
                    selectMirror: true,
                    dayMaxEvents: true,
                    weekends: true,
                    select: this.handleDateSelect,
                    nextDayThreshold: '12:00:00',
                    events: function (info, successCallback, failureCallback) {
                        _this.spinShow = true;
                        const params = {
                            field_id: _this.field_id,
                            s_date: _this.$utils.Datetimes.getymd(info.start),
                            e_date: _this.$utils.Datetimes.getymd(info.end)
                        }
                        getShiftList(params).then((res) => {
                            _this.spinShow = false;
                            if (res.data.status == "success") {
                                successCallback(
                                    Array.prototype.slice.call(res.data.result).map(function (one) {
                                        return {
                                            title: " - " + _this.$utils.Datetimes.num2hi(one.e_time) + "  " + one.staff.name,
                                            start: new Date(_this.$utils.Dates.str2Date(one.shift_date).getTime() + one.s_time * 1000 * 60),
                                            end: new Date(_this.$utils.Dates.str2Date(one.shift_date).getTime() + one.e_time * 1000 * 60),
                                        }
                                    })
                                )
                            } else {
                                failureCallback("error")
                            }
                        }).catch((error) => {_this.spinShow = false;})
                    },
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        meridiem: false
                    },
                    height: 550,
                    eventColor: '#8c40ce',
                    eventBackgroundColor: '#7df2fd',
                    eventTextColor:'#3d32ce',
                    displayEventEnd: false
                }
                this.$set(this, "calendarOptions", option)
            },
            getShiftMonthInfo() {
                if (this.shift_month) {
                    let s_date = new Date(this.shift_month.getFullYear(), this.shift_month.getMonth(), 1);
                    let e_date = new Date(this.shift_month.getFullYear(), this.shift_month.getMonth() + 1, 0);
                    let params = {
                        field_id: this.field_id,
                        shift_month: this.$utils.Datetimes.getym(this.shift_month),
                    }
                    getShiftMonthInfo(params).then(res => {
                        this.loading = false;
                        if (res.data.status == "success") {
                            this.$refs.refShiftHistory.showTable(res.data.result, s_date, e_date, this.field_info, 0, {shift_month:params['shift_month']})
                            this.shift_history_modal = true
                        }
                    })
                }
            },
            getShiftWeekInfo() {
                if (this.shift_week && this.shift_week.length === 2) {
                    let params = {
                        field_id: this.field_id,
                        shift_s_date: this.$utils.Datetimes.getymd(this.shift_week[0]),
                        shift_e_date: this.$utils.Datetimes.getymd(this.shift_week[1]),
                    }
                    getShiftWeekInfo(params).then(res => {
                        this.loading = false;
                        if (res.data.status == "success") {
                            this.$refs.refShiftHistory.showTable(res.data.result, this.shift_week[0], this.shift_week[1], this.field_info, 1, {s_date:params['shift_s_date'],e_date:params['shift_e_date']})
                            this.shift_history_modal = true
                        }
                    })
                }
            },
        },
        computed: {
        }
    }
</script>
<style>
    .fc-event-title{
        font-weight: normal!important;
    }
</style>

<style scoped>
    .calendar-card{
        padding:30px;
    }
</style>