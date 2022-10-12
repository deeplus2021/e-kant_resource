<template>
    <div>
        <Card>
            <h3 slot="title" style="margin-left: 10px;">{{staff_info.staff_name}}:勤怠一覧 <span
                    style="padding-left: 50px;">電話番号:{{staff_info.staff_tel}}</span></h3>
            <Table id="attendance_table" :loading="loading" :columns="tableColumns" :data="tableData"
                   @on-cell-click="cellClick" border height="700"></Table>
        </Card>
    </div>
</template>

<script>
    import {mapGetters} from "vuex";
    import {
        getStaffAttendanceList,
        confirmRequestLate,
        confirmRequestEarlyLeave,
        confirmRequestRest,
        confirmRequestOverTime,
        confirmRequestAltDate,
        confirmAllRequest,
        confirmRequestArrive,
        confirmRequestLeave,
        confirmRequestBreak,
        confirmRequestNightBreak,
        getAttendanceInfo
    } from '@/api/attendance_master'

    export default {
        name: "AttendanceList",
        data() {
            return {
                loading: false,
                tableColumns: [
                    {
                        title: "名前",
                        key: 'staff_name',
                        align: 'center',
                        render: (h, params) => {
                            return h("div", params.row.staff ? params.row.staff.name : '')
                        }
                    },
                    {
                        title: "日付",
                        key: "shift_date",
                        align: 'center',
                        width: 100,
                        render: (h, params) => {
                            let dw = this.$utils.Datetimes.getdw(this.$utils.Dates.str2Date(params.row.shift_date))
                            return h("div", params.row.shift_date + '(' + dw + ')')
                        }
                    },
                    {
                        title: "シフト出社時間",
                        key: "s_time",
                        align: 'center',
                        render: (h, params) => {
                            if(!params.row.id){return ''}
                            return h('div', this.$utils.Datetimes.num2hi(params.row.s_time))
                        }
                    },
                    {
                        title: "シフト退社時間",
                        key: "e_time",
                        align: 'center',
                        render: (h, params) => {
                            if(!params.row.id){return ''}
                            return h('div', this.$utils.Datetimes.num2hi(params.row.e_time))
                        }
                    },
                    {
                        title: "シフト休憩時間",
                        key: 'k_time',
                        align: 'center',
                        render: (h, params) => {
                            if(!params.row.id || params.row.s_time >= 21* 60){return ''}
                            return h('div', this.$utils.Datetimes.num2hi(params.row.ke_time - params.row.ks_time))
                        }
                    },
                    {
                        title: "シフト深夜休憩時間",
                        key: 'sk_time',
                        align: 'center',
                        render: (h, params) => {
                            if(!params.row.id || params.row.s_time < 21* 60){return ''}
                            let time = ''
                            if (params.row.ks_time) {
                                time = this.$utils.Datetimes.num2hi(params.row.ke_time - params.row.ks_time)
                            }
                            return h('div', time)
                        }
                    },
                    {
                        title: "勤務開始時間",
                        key: 'bs_time',
                        align: 'center',
                        className: 'editable-col',
                        render: (h, params) => {
                            if(!params.row.id){return ''}
                            let time = ''
                            let color = {}
                            if (params.row.arrive_checked_at) {
                                time = this.$utils.Datetimes.ymdhis2hi(params.row.shift_date, params.row.arrive_checked_at)
                                const real_time = (new Date(params.row.arrive_checked_at.replace(' ', 'T'))).getTime()
                                const s_time = (new Date(params.row.shift_date + "T00:00:00")).getTime() + params.row.s_time * 60 * 1000
                                if ((s_time - real_time) > 10 * 60 * 1000) {
                                    color = {style: {color: "#00f"}}
                                }
                            }
                            if (params.row.arrive_changed_at && !params.row.arrive_changed_checked_at) {
                                color = {style: {color: "#f00"}}
                                const changed_time = this.$utils.Datetimes.ymdhis2hi(params.row.shift_date, params.row.arrive_changed_at)
                                return h('div', [
                                    h('span', color, changed_time + (time ? ('(' + time + ')') : '')),
                                    h('br'),
                                    h('Button', {
                                        props: {
                                            loading: params.row.$isLoadingArrive,
                                            type: 'info',
                                            size: 'small'
                                        },
                                        on: {
                                            click: () => {
                                                this.confirmRequestArrive(params.row, changed_time)
                                            }
                                        }
                                    }, '承認')
                                ])
                            } else {
                                if (params.row.arrive_checked_at){
                                    if(params.row.late_at && !params.row.late_checked_at){
                                        color = {style: {color: "#f00"}}
                                    }
                                    return h('div', color, time)
                                }
                                else{
                                    const s_time = (new Date(params.row.shift_date + "T00:00:00")).getTime() + params.row.s_time * 60 * 1000
                                    if((new Date()).getTime() > s_time + 10 * 60 * 1000){
                                        return h('div', [
                                            h('span', {
                                                style: {
                                                    background: "rgb(0,174,255)",
                                                    color:"#fff",
                                                    padding: "3px 5px",
                                                    "border-radius": "3px"
                                                }
                                            }, '確定')
                                        ])
                                    }
                                    else{
                                        return h('div', color, time)
                                    }
                                }
                            }
                        }
                    },
                    {
                        title: "勤務終了時間",
                        key: 'be_time',
                        align: 'center',
                        className: 'editable-col',
                        render: (h, params) => {
                            if(!params.row.id){return ''}
                            let time = ''
                            let color = {}
                            if (params.row.leave_checked_at) {
                                time = this.$utils.Datetimes.ymdhis2hi(params.row.shift_date, params.row.leave_checked_at)
                                const real_time = (new Date(params.row.leave_checked_at.replace(' ', 'T'))).getTime()
                                const e_time = (new Date(params.row.shift_date + "T00:00:00")).getTime() + params.row.e_time * 60 * 1000
                                if ((real_time - e_time) > 10 * 60 * 1000) {
                                    color = {style: {color: "#00f"}}
                                }
                            }
                            if (params.row.leave_changed_at && !params.row.leave_changed_checked_at) {
                                color = {style: {color: "#f00"}}
                                const changed_time = this.$utils.Datetimes.ymdhis2hi(params.row.shift_date, params.row.leave_changed_at)
                                return h('div', [
                                    h('span', color, changed_time + (time ? ('(' + time + ')') : '')),
                                    h('br'),
                                    h('Button', {
                                        props: {
                                            loading: params.row.$isLoadingLeave,
                                            type: 'info',
                                            size: 'small'
                                        },
                                        on: {
                                            click: () => {
                                                this.confirmRequestLeave(params.row, changed_time)
                                            }
                                        }
                                    }, '承認')
                                ])
                            } else {
                                if (params.row.leave_checked_at) {
                                    if((params.row.e_leave_at && !params.row.e_leave_checked_at) || (params.row.over_time_at && !params.row.over_time_checked_at)){
                                        color = {style: {color: "#f00"}}
                                    }
                                    return h('div', color, time)
                                }
                                else{
                                    const e_time = (new Date(params.row.shift_date + "T00:00:00")).getTime() + params.row.e_time * 60 * 1000
                                    if((new Date()).getTime() > e_time + 10 * 60 * 1000){
                                        return h('div', [
                                            h('span', {
                                                style: {
                                                    background: "rgb(0,174,255)",
                                                    color:"#fff",
                                                    padding: "3px 5px",
                                                    "border-radius": "3px"
                                                }
                                            }, '確定')
                                        ])
                                    }
                                    else {
                                        return h('div', color, time)
                                    }
                                }
                            }
                        }
                    },
                    {
                        title: "勤務休憩時間",
                        key: 'r_time',
                        align: 'center',
                        className: 'editable-col',
                        render: (h, params) => {
                            let color = {}
                            let time = params.row.break_time ? params.row.break_time : ''
                            if (params.row.break_changed_time && !params.row.break_changed_checked_at) {
                                color = {style: {color: "#f00"}}
                                const changed_time = params.row.break_changed_time
                                return h('div', [
                                    h('span', color, this.$utils.Datetimes.num2hi(changed_time) + (time ? ('(' + this.$utils.Datetimes.num2hi(time) + ')') : '')),
                                    h('br'),
                                    h('Button', {
                                        props: {
                                            loading: params.row.$isLoadingBreak,
                                            type: 'info',
                                            size: 'small'
                                        },
                                        on: {
                                            click: () => {
                                                this.confirmRequestBreak(params.row, changed_time)
                                            }
                                        }
                                    }, '承認')
                                ])
                            } else {
                                return h('div', time ? this.$utils.Datetimes.num2hi(time) : '')
                            }
                        }
                    },
                    {
                        title: "深夜勤務休憩時間",
                        key: 'nr_time',
                        align: 'center',
                        className: 'editable-col',
                        render: (h, params) => {
                            let color = {}
                            let time = params.row.night_break_time ? params.row.night_break_time : ''
                            if (params.row.night_break_changed_time && !params.row.night_break_changed_checked_at) {
                                color = {style: {color: "#f00"}}
                                const changed_time = params.row.night_break_changed_time
                                return h('div', [
                                    h('span', color, this.$utils.Datetimes.num2hi(changed_time) + (time ? ('(' + this.$utils.Datetimes.num2hi(time) + ')') : '')),
                                    h('br'),
                                    h('Button', {
                                        props: {
                                            loading: params.row.$isLoadingNightBreak,
                                            type: 'info',
                                            size: 'small'
                                        },
                                        on: {
                                            click: () => {
                                                this.confirmRequestNightBreak(params.row, changed_time)
                                            }
                                        }
                                    }, '承認')
                                ])
                            } else {
                                return h('div', time ? this.$utils.Datetimes.num2hi(time) : '')
                            }
                        }
                    },
                    /*{
                        title: '残業時間',
                        key:'',
                        align: 'center',
                        render: (h, params) => {
                            //TODO
                            return h('div', params.row.over_time ? this.$utils.Datetimes.num2hi(params.row.over_time):"")
                        }
                    },*/
                    {
                        title: "遅刻申請",
                        key: 'late',
                        className: 'editable-col',
                        render: (h, params) => {
                            if(!params.row.id){return ''}
                            if (params.row.arrive_checked_at && params.row.late_at) {
                                if (!params.row.late_checked_at) {
                                    return h('div', [
                                        h('span', this.$utils.Datetimes.ymdhis2hi(params.row.shift_date, params.row.arrive_checked_at)),
                                        h('br'),
                                        h('Button', {
                                            props: {
                                                loading: params.row.$isLoadingLate,
                                                type: 'info',
                                                size: 'small'
                                            },
                                            on: {
                                                click: () => {
                                                    this.confirmRequestLate(params.row)
                                                }
                                            }
                                        }, '承認')
                                    ])
                                } else {
                                    return h('div', '申請なし')
                                }
                            } else {
                                if(params.row.arrive_checked_at && !params.row.late_checked_at){
                                    const real_time = (new Date(params.row.arrive_checked_at.replace(' ', 'T'))).getTime()
                                    const s_time = (new Date(params.row.shift_date + "T00:00:00")).getTime() + params.row.s_time * 60 * 1000
                                    if ((real_time - s_time) > 15 * 60 * 1000) {
                                        return h('div', [
                                            h('Button', {
                                                props: {
                                                    loading: params.row.$isLoadingLate,
                                                    type: 'info',
                                                    size: 'small'
                                                },
                                                on: {
                                                    click: () => {
                                                        this.changeRequestLate(params.row)
                                                    }
                                                }
                                            }, '確定')
                                        ])
                                    }
                                    else{
                                        return h('div', params.row.late_checked_at ? '申請なし(変更有)' : '申請なし')
                                    }
                                }
                                else{
                                    return h('div', params.row.late_checked_at ? '申請なし(変更有)' : '申請なし')
                                }
                            }
                        }
                    },
                    {
                        title: "早退申請",
                        key: 'early',
                        align: 'center',
                        className: 'editable-col',
                        render: (h, params) => {
                            if(!params.row.id){return ''}
                            if (params.row.leave_checked_at && params.row.e_leave_at) {
                                if (!params.row.e_leave_checked_at) {
                                    return h('div', [
                                        h('span', this.$utils.Datetimes.ymdhis2hi(params.row.shift_date, params.row.leave_checked_at)),
                                        h('br'),
                                        h('Button', {
                                            props: {
                                                loading: params.row.$isLoadingLeave,
                                                type: 'info',
                                                size: 'small'
                                            },
                                            on: {
                                                click: () => {
                                                    this.confirmRequestEarlyLeave(params.row)
                                                }
                                            }
                                        }, '承認')
                                    ])
                                } else {
                                    return h('div', '申請なし')
                                }
                            } else {
                                if(params.row.leave_checked_at && !params.row.e_leave_checked_at){
                                    const real_time = (new Date(params.row.leave_checked_at.replace(' ', 'T'))).getTime()
                                    const e_time = (new Date(params.row.shift_date + "T00:00:00")).getTime() + params.row.e_time * 60 * 1000
                                    if ((e_time - real_time) > 30 * 60 * 1000) {
                                        return h('div', [
                                            h('Button', {
                                                props: {
                                                    loading: params.row.$isLoadingLeave,
                                                    type: 'info',
                                                    size: 'small'
                                                },
                                                on: {
                                                    click: () => {
                                                        this.changeRequestEarlyLeave(params.row)
                                                    }
                                                }
                                            }, '確定')
                                        ])
                                    }
                                    else{
                                        return h('div', params.row.e_leave_checked_at ? '申請なし(変更有)' : '申請なし')
                                    }
                                }
                                else{
                                    return h('div', params.row.e_leave_checked_at ? '申請なし(変更有)' : '申請なし')
                                }
                            }
                        }
                    },
                    {
                        title: "残業承認",
                        key: 'over',
                        align: 'center',
                        className: 'editable-col',
                        render: (h, params) => {
                            if(!params.row.id){return ''}
                            if (params.row.leave_checked_at && params.row.over_time_at) {
                                if (!params.row.over_time_checked_at) {
                                    return h('div', [
                                        h('span', this.$utils.Datetimes.ymdhis2hi(params.row.shift_date, params.row.leave_checked_at)),
                                        h('br'),
                                        h('Button', {
                                            props: {
                                                loading: params.row.$isLoadingOver,
                                                type: 'info',
                                                size: 'small'
                                            },
                                            on: {
                                                click: () => {
                                                    this.confirmRequestOverTime(params.row)
                                                }
                                            }
                                        }, '承認')
                                    ])
                                } else {
                                    return h('div', '済')
                                }
                            } else {
                                if(params.row.leave_checked_at && !params.row.over_time_checked_at){
                                    const real_time = (new Date(params.row.leave_checked_at.replace(' ', 'T'))).getTime()
                                    const e_time = (new Date(params.row.shift_date + "T00:00:00")).getTime() + params.row.e_time * 60 * 1000
                                    if ((real_time - e_time) > 30 * 60 * 1000) {
                                        return h('div', [
                                            h('Button', {
                                                props: {
                                                    loading: params.row.$isLoadingOver,
                                                    type: 'info',
                                                    size: 'small'
                                                },
                                                on: {
                                                    click: () => {
                                                        this.changeRequestOverTime(params.row)
                                                    }
                                                }
                                            }, '確定')
                                        ])
                                    }
                                    else{
                                        return h('div', params.row.over_time_checked_at ? '済': '未')
                                    }
                                }
                                else{
                                    return h('div', params.row.over_time_checked_at ? '済': '未')
                                }
                            }
                        }
                    },
                    {
                        title: "休日申請",
                        key: 'rest',
                        align: 'center',
                        className: 'editable-col',
                        render: (h, params) => {
                            if(!params.row.id){return ''}
                            if (params.row.rest_at && !params.row.rest_checked_at) {
                                return h('Button', {
                                    props: {
                                        loading: params.row.$isLoadingRest,
                                        type: 'info',
                                        size: 'small'
                                    },
                                    on: {
                                        click: () => {
                                            this.confirmRequestRest(params.row)
                                        }
                                    }
                                }, '承認')
                            } else {
                                return h('div', params.row.rest_checked_at ? '休日' :'---')
                            }
                        }
                    },
                    {
                        title: "振替日申請",
                        key: 'alt',
                        align: 'center',
                        className: 'editable-col',
                        render: (h, params) => {
                            if(!params.row.id){return ''}
                            if (params.row.alt_date_at && !params.row.alt_date_checked_at) {
                                return h('div', [
                                    h('span', params.row.alt_date),
                                    h('br'),
                                    h('Button', {
                                        props: {
                                            loading: params.row.$isLoadingAlt,
                                            type: 'info',
                                            size: 'small'
                                        },
                                        on: {
                                            click: () => {
                                                this.confirmRequestAltDate(params.row)
                                            }
                                        }
                                    }, '承認')
                                ])
                            } else {
                                if(params.row.alt_date && params.row.alt_date_checked_at)
                                {
                                    return h('div', [
                                        h('span', params.row.alt_date),
                                        h('br'),
                                        h('span', '振替日')
                                    ])

                                }
                                else{
                                    return h('div', '---')
                                }
                            }
                        }
                    },
                    {
                        title: "勤怠承認",
                        width: 100,
                        align: 'center',
                        render: (h, params) => {
                            if(!params.row.id){return ''}
                            if (
                                (params.row.arrive_checked_at && params.row.late_at && !params.row.late_checked_at) ||
                                (params.row.leave_checked_at && params.row.over_time_at && !params.row.over_time_checked_at) ||
                                (params.row.leave_checked_at && params.row.e_leave_at && !params.row.e_leave_checked_at) ||
                                (params.row.rest_at && !params.row.rest_checked_at) ||
                                (params.row.alt_date_at && !params.row.alt_date_checked_at)
                            ) {
                                return h('Button', {
                                    props: {
                                        loading: params.row.$isLoadingAll,
                                        type: 'info',
                                        size: 'small'
                                    },
                                    on: {
                                        click: () => {
                                            this.confirmAllRequest(params.row)
                                        }
                                    }
                                }, '承認待ち')
                            } else {
                                return h('span', (params.row.late_checked_at || params.row.over_time_checked_at || params.row.e_leave_checked_at || params.row.rest_checked_at || params.row.alt_date_checked_at) ? '承認済' : '')
                            }
                        }
                    },
                    {
                        title: "承認者名",
                        align: 'center',
                        render: (h, params) => {
                            if(!params.row.id){return ''}
                            let admin = ''
                            if (params.row.admin) {
                                admin = params.row.admin.name
                            }
                            return h('span', admin)
                        }
                    },
                ],
                tableData: [],
                staff_info: {},
                staff_id: null,
                s_date: null,
                e_date: null,
                shift_ids: null
            }
        },
        methods: {
            getStaffAttendanceList() {
                if (!this.shift_ids) {
                    this.loading = true
                    let params = {
                        staff_id: this.staff_id,
                        s_date: this.$utils.Datetimes.getymd(this.s_date),
                        e_date: this.$utils.Datetimes.getymd(this.e_date),
                    }
                    getStaffAttendanceList(params).then((res) => {
                        this.loading = false;
                        if (res.data.status == "success") {
                            this.tableData = res.data.result;
                        }
                    }).catch((error) => {
                        this.loading = false
                    })
                } else {
                    this.getAttendanceInfo(this.shift_ids)
                }
            },
            showData(staff_info, s_date, e_date) {
                this.staff_info = staff_info
                this.staff_id = staff_info.staff_id
                this.s_date = s_date
                this.e_date = e_date
                this.shift_ids = null

                this.getStaffAttendanceList()
            },
            getAttendanceInfo(shift_ids) {
                this.shift_ids = shift_ids
                this.loading = true
                let params = {
                    shift_ids: shift_ids,
                }
                getAttendanceInfo(params).then((res) => {
                    this.loading = false;
                    if (res.data.status == "success") {
                        this.tableData = res.data.result;
                        this.staff_info = {
                            staff_name: res.data.result[0].staff.name,
                            staff_tel: res.data.result[0].staff.tel
                        }
                    }
                }).catch((error) => {
                    this.loading = false
                })
            },
            confirmRequestLate(row) {
                let _this = this
                this.$Modal.confirm({
                    title: "遅刻申請承認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('span', this.$utils.Datetimes.ymdhis2hi(row.shift_date, row.arrive_checked_at)),
                            h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            _this.$set(row, '$isLoadingLate', true)
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 0,
                                            }
                                            confirmRequestLate(params).then(res => {
                                                _this.$set(row, '$isLoadingLate', false)
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            }).catch((error) => {
                                                _this.$set(row, '$isLoadingLate', false)
                                            })
                                        }
                                    }
                                }, '拒否(再申請)'
                            )])
                    },
                    okText: "承 認",
                    closable:true,
                    onOk: () => {
                        _this.$set(row, '$isLoadingLate', true)
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 1,
                        }
                        confirmRequestLate(params).then(res => {
                            _this.$set(row, '$isLoadingLate', false)
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        }).catch((error) => {
                            _this.$set(row, '$isLoadingLate', false)
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            confirmRequestEarlyLeave(row) {
                let _this = this
                this.$Modal.confirm({
                    title: "早退申請承認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('span', _this.$utils.Datetimes.ymdhis2hi(row.shift_date, row.leave_checked_at)),
                        h('Button',{
                            style: {
                                cursor: 'pointer',
                                marginLeft: '15px',
                                padding: '5px 10px'
                            },
                            props: {
                                type: 'warning',
                            },
                            on: {
                                click: () => {
                                    const params = {
                                        shift_id: row.id,
                                        is_confirmed: 0
                                    }
                                    _this.$set(row, '$isLoadingLeave', true)
                                    confirmRequestEarlyLeave(params).then(res => {
                                        _this.$set(row, '$isLoadingLeave', false)
                                        if (res.data.status == "success") {
                                            _this.$Message.success('成功');
                                            _this.getStaffAttendanceList()
                                            this.$Modal.remove()
                                        }
                                    }).catch((error) => {
                                        _this.$set(row, '$isLoadingLeave', false)
                                    })
                                }
                            }
                        }, '拒否(再申請)'
                        )])
                    },
                    okText: "承 認",
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 1
                        }
                        _this.$set(row, '$isLoadingLeave', true)
                        confirmRequestEarlyLeave(params).then(res => {
                            _this.$set(row, '$isLoadingLeave', false)
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        }).catch((error) => {
                            _this.$set(row, '$isLoadingLeave', false)
                        })
                    },
                    closable: true,
                    onCancel: () => {
                    },
                })
            },
            confirmRequestRest(row) {
                let _this = this
                this.$Modal.confirm({
                    title: "休日申請承認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('span', row.shift_date),
                            h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 0
                                            }
                                            _this.$set(row, '$isLoadingRest', true)
                                            confirmRequestRest(params).then(res => {
                                                _this.$set(row, '$isLoadingRest', false)
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            }).catch((error) => {
                                                _this.$set(row, '$isLoadingRest', false)
                                            })
                                        }
                                    }
                                }, '拒否(再申請)'
                            )
                    ])
                    },
                    okText: "承 認",
                    closable: true,
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 1
                        }
                        _this.$set(row, '$isLoadingRest', true)
                        confirmRequestRest(params).then(res => {
                            _this.$set(row, '$isLoadingRest', false)
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        }).catch((error) => {
                            _this.$set(row, '$isLoadingRest', false)
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            confirmRequestOverTime(row) {
                let _this = this
                this.$Modal.confirm({
                    title: "残業申請承認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('span', _this.$utils.Datetimes.ymdhis2hi(row.shift_date, row.leave_checked_at)),
                            h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 0
                                            }
                                            _this.$set(row, '$isLoadingOver', true)
                                            confirmRequestOverTime(params).then(res => {
                                                _this.$set(row, '$isLoadingOver', false)
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            }).catch((error) => {
                                                _this.$set(row, '$isLoadingOver', false)
                                            })
                                        }
                                    }
                                }, '拒否(再申請)'
                            )
                        ])
                    },
                    okText: "承 認",
                    closable: true,
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 1
                        }
                        _this.$set(row, '$isLoadingOver', true)
                        confirmRequestOverTime(params).then(res => {
                            _this.$set(row, '$isLoadingOver', false)
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        }).catch((error) => {
                            _this.$set(row, '$isLoadingOver', false)
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            confirmRequestAltDate(row) {
                let _this = this
                this.$Modal.confirm({
                    title: "振替日申請承認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('DatePicker', {
                            props: {
                                value: row.alt_date,
                                autofocus: true,
                            },
                            on: {
                                'on-change': (val) => {
                                    row.alt_date = val
                                }
                            }
                        }),
                            h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 0,
                                                alt_date: row.alt_date
                                            }
                                            _this.$set(row, '$isLoadingAlt', true)
                                            confirmRequestAltDate(params).then(res => {
                                                _this.$set(row, '$isLoadingAlt', false)
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            }).catch((error) => {
                                                _this.$set(row, '$isLoadingAlt', false)
                                            })
                                        }
                                    }
                                }, '拒否(再申請)'
                            )
                        ])
                    },
                    okText: "承 認",
                    closable: true,
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 1,
                            alt_date: row.alt_date
                        }
                        _this.$set(row, '$isLoadingAlt', true)
                        confirmRequestAltDate(params).then(res => {
                            _this.$set(row, '$isLoadingAlt', false)
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        }).catch((error) => {
                            _this.$set(row, '$isLoadingAlt', false)
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            confirmAllRequest(row) {
                let _this = this
                this.$Modal.confirm({
                    title: "残遅早欠申請承認",
                    render: (h) => {
                        return h('span', "")
                    },
                    okText: "承 認",
                    cancelText: "キャンセル",
                    onOk: () => {
                        let params = {
                            staff_ids: [row.staff_id],
                            s_date: row.shift_date,
                            e_date: row.shift_date,
                            is_confirmed: 1
                        };
                        _this.$set(row, '$isLoadingAll', true)
                        confirmAllRequest(params).then(res => {
                            _this.$set(row, '$isLoadingAll', false)
                            if (res.data.status == 'success') {
                                _this.getStaffAttendanceList()
                            }
                        }).catch((error) => {
                            _this.$set(row, '$isLoadingAll', false)
                        })
                    },
                    onCancel: () => {
                        /*let params = {
                            staff_ids: [row.staff_id],
                            s_date: row.shift_date,
                            e_date: row.shift_date,
                            is_confirmed: 0
                        };
                        _this.$set(row, '$isLoadingAll', true)
                        confirmAllRequest(params).then(res => {
                            _this.$set(row, '$isLoadingAll', false)
                            if (res.data.status == 'success') {
                                _this.getStaffAttendanceList()
                            }
                        }).catch((error) => {
                            _this.$set(row, '$isLoadingAll', false)
                        })*/
                    },
                })
            },
            confirmRequestArrive(row, changed_time) {
                let _this = this
                changed_time = this.$utils.Datetimes.hi2num(changed_time)
                let leave_time = row.leave_checked_at ? this.$utils.Datetimes.ymdhis2num(row.shift_date, row.leave_checked_at) : 2160
                this.$Modal.confirm({
                    title: "勤務開始時間申請承認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('vTimeSelector', {
                            props: {
                                value: changed_time,
                                steps: [1, 1],
                                lte: leave_time
                            },
                            on: {
                                'on-change': (val) => {
                                    console.log(val)
                                    changed_time = val
                                }
                            }
                        }),
                            h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 0,
                                                changed_time: changed_time
                                            }
                                            _this.$set(row, '$isLoadingArrive', true)
                                            confirmRequestArrive(params).then(res => {
                                                _this.$set(row, '$isLoadingArrive', false)
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            }).catch((error) => {
                                                _this.$set(row, '$isLoadingArrive', false)
                                            })
                                        }
                                    }
                                }, '拒否(再申請)'
                            )
                        ])
                    },
                    okText: "承 認",
                    closable: true,
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 1,
                            changed_time: changed_time
                        }
                        _this.$set(row, '$isLoadingArrive', true)
                        confirmRequestArrive(params).then(res => {
                            _this.$set(row, '$isLoadingArrive', false)
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        }).catch((error) => {
                            _this.$set(row, '$isLoadingArrive', false)
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            confirmRequestLeave(row, changed_time) {
                let _this = this
                changed_time = this.$utils.Datetimes.hi2num(changed_time)
                let arrive_time = row.arrive_checked_at ? row.arrive_checked_at : (row.field_s_time ? row.shift_date + " " + this.$utils.Datetimes.num2hi(row.field_s_time) + ":00" : this.$utils.Datetimes.getymdhis(new Date()))
                arrive_time = this.$utils.Datetimes.ymdhis2num(row.shift_date, arrive_time)
                this.$Modal.confirm({
                    title: "勤務終了時間申請承認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('vTimeSelector', {
                            props: {
                                value: changed_time,
                                steps: [1, 1],
                                gte: arrive_time
                            },
                            on: {
                                'on-change': (val) => {
                                    changed_time = val
                                }
                            }
                        }),
                            h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 0,
                                                changed_time: changed_time
                                            }
                                            _this.$set(row, '$isLoadingLeave', true)
                                            confirmRequestLeave(params).then(res => {
                                                _this.$set(row, '$isLoadingLeave', false)
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            }).catch((error) => {
                                                _this.$set(row, '$isLoadingLeave', false)
                                            })
                                        }
                                    }
                                }, '拒否(再申請)'
                            )
                        ])
                    },
                    okText: "承 認",
                    closable: true,
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 1,
                            changed_time: changed_time
                        }
                        _this.$set(row, '$isLoadingLeave', true)
                        confirmRequestLeave(params).then(res => {
                            _this.$set(row, '$isLoadingLeave', false)
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        }).catch((error) => {
                            _this.$set(row, '$isLoadingLeave', false)
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            confirmRequestBreak(row, changed_time) {
                let _this = this
                this.$Modal.confirm({
                    title: "勤務休憩時間申請承認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('vTimeSelector', {
                            props: {
                                value: changed_time,
                                steps: [1, 1],
                                lte: (row.leave_checked_at) ? this.$utils.Datetimes.ymdhis2num(row.shift_date, row.leave_checked_at) : 2160
                            },
                            on: {
                                'on-change': (val) => {
                                    changed_time = val
                                }
                            }
                        }),
                            h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 0,
                                                break_changed_time: changed_time
                                            }
                                            _this.$set(row, '$isLoadingBreak', true)
                                            confirmRequestBreak(params).then(res => {
                                                _this.$set(row, '$isLoadingBreak', false)
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            }).catch((error) => {
                                                _this.$set(row, '$isLoadingBreak', false)
                                            })
                                        }
                                    }
                                }, '拒否(再申請)'
                            )
                        ])
                    },
                    okText: "承 認",
                    closable: true,
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 1,
                            break_changed_time: changed_time
                        }
                        _this.$set(row, '$isLoadingBreak', true)
                        confirmRequestBreak(params).then(res => {
                            _this.$set(row, '$isLoadingBreak', false)
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        }).catch((error) => {
                            _this.$set(row, '$isLoadingBreak', false)
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            confirmRequestNightBreak(row, changed_time) {
                let _this = this
                this.$Modal.confirm({
                    title: "深夜勤務休憩時間申請承認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('vTimeSelector', {
                            props: {
                                value: changed_time,
                                steps: [1, 1],
                                lte: (row.leave_checked_at) ? this.$utils.Datetimes.ymdhis2num(row.shift_date, row.leave_checked_at) : 2160
                            },
                            on: {
                                'on-change': (val) => {
                                    changed_time = val
                                }
                            }
                        }),
                            h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 0,
                                                night_break_changed_time: changed_time
                                            }
                                            _this.$set(row, '$isLoadingNightBreak', true)
                                            confirmRequestNightBreak(params).then(res => {
                                                _this.$set(row, '$isLoadingNightBreak', false)
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            }).catch((error) => {
                                                _this.$set(row, '$isLoadingNightBreak', false)
                                            })
                                        }
                                    }
                                }, '拒否(再申請)'
                            )
                        ])
                    },
                    okText: "承 認",
                    cancelText: "拒否(再申請)",
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 1,
                            night_break_changed_time: changed_time
                        }
                        _this.$set(row, '$isLoadingNightBreak', true)
                        confirmRequestNightBreak(params).then(res => {
                            _this.$set(row, '$isLoadingNightBreak', false)
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        }).catch((error) => {
                            _this.$set(row, '$isLoadingNightBreak', false)
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            cellClick(row, column, data, event) {
                if(!row.id){return false}
                if (column.key === "bs_time") {
                    if (row.arrive_changed_at && !row.arrive_changed_checked_at) {
                    } else {
                        this.changeRequestArrive(row)
                    }
                } else if (column.key === "be_time") {
                    if (row.leave_changed_at && !row.leave_changed_checked_at) {
                    } else {
                        if(row.arrive_checked_at){
                            this.changeRequestLeave(row)
                        }
                        else{
                            this.$message.error("勤務開始時間を確定してください")
                        }
                    }
                } else if (column.key === "r_time") {
                    if (row.break_changed_time && !row.break_changed_checked_at) {
                    } else {
                        if(row.arrive_checked_at) {
                            if(row.s_time < 21 * 60) {
                                this.changeRequestBreak(row)
                            }
                        }else{
                            this.$message.error("勤務開始時間を確定してください")
                        }
                    }
                } else if (column.key === "nr_time") {
                    if (row.night_break_changed_time && !row.night_break_changed_checked_at) {
                    } else {
                        if(row.arrive_checked_at){
                            if(row.s_time >= 21 * 60){
                                this.changeRequestNightBreak(row)
                            }
                        }
                        else{
                            this.$message.error("勤務開始時間を確定してください")
                        }
                    }
                } else if (column.key === "rest") {
                    if (row.rest_at && !row.rest_checked_at) {
                    } else {
                        this.changeRequestRest(row)
                    }
                } else if (column.key === "alt") {
                    if (row.alt_date_at && !row.alt_date_checked_at) {
                    } else {
                        this.changeRequestAltDate(row)
                    }
                }
            },
            changeRequestLate(row) {
                let _this = this
                this.$Modal.confirm({
                    title: "遅刻確認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('span', this.$utils.Datetimes.ymdhis2hi(row.shift_date, row.arrive_checked_at)),
                            h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 2,
                                                canceled: 1,
                                            }
                                            confirmRequestLate(params).then(res => {
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            })
                                        }
                                    }
                                }, '取り消し'
                            )
                        ])
                    },
                    closable: true,
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 2,
                        }
                        confirmRequestLate(params).then(res => {
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            changeRequestEarlyLeave(row) {
                let _this = this
                this.$Modal.confirm({
                    title: "早退確認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('span', this.$utils.Datetimes.ymdhis2hi(row.shift_date, row.leave_checked_at)),
                            h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 2,
                                                canceled: 1
                                            }
                                            confirmRequestEarlyLeave(params).then(res => {
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            })
                                        }
                                    }
                                }, '取り消し'
                            )
                        ])
                    },
                    closable: true,
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 2
                        }
                        confirmRequestEarlyLeave(params).then(res => {
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            changeRequestRest(row) {
                let _this = this
                this.$Modal.confirm({
                    title: "休日確認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('span', row.shift_date),
                            row.rest_checked_at ? h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 2,
                                                canceled: 1
                                            }
                                            confirmRequestRest(params).then(res => {
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            })
                                        }
                                    }
                                }, '取り消し'
                            ):''
                        ])
                    },
                    closable: true,
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 2
                        }
                        confirmRequestRest(params).then(res => {
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            changeRequestOverTime(row) {
                let _this = this
                this.$Modal.confirm({
                    title: "残業確認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('span',  this.$utils.Datetimes.ymdhis2hi(row.shift_date, row.leave_checked_at)),
                            h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 2,
                                                canceled: 1
                                            }
                                            confirmRequestOverTime(params).then(res => {
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            })
                                        }
                                    }
                                }, '取り消し'
                            )
                        ])
                    },
                    closable: true,
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 2
                        }
                        confirmRequestOverTime(params).then(res => {
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            changeRequestAltDate(row) {
                let _this = this
                let changed_date = row.alt_date ? row.alt_date : this.$utils.Datetimes.getymd(new Date())
                this.$Modal.confirm({
                    title: "振替日確認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('DatePicker', {
                            props: {
                                value: changed_date,
                                autofocus: true,
                            },
                            on: {
                                'on-change': (val) => {
                                    changed_date = val
                                }
                            }
                        }),
                            row.alt_date ? h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 2,
                                                alt_date: null
                                            }
                                            confirmRequestAltDate(params).then(res => {
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            })
                                        }
                                    }
                                }, '取り消し'
                            ): ''
                        ])
                    },
                    closable: true,
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 2,
                            alt_date: changed_date
                        }
                        confirmRequestAltDate(params).then(res => {
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            changeRequestArrive(row) {
                let _this = this
                let changed_time = row.arrive_checked_at ? row.arrive_checked_at : (row.field_s_time ? row.shift_date + " " + this.$utils.Datetimes.num2hi(row.field_s_time) + ":00" : this.$utils.Datetimes.getymdhis(new Date()))
                changed_time = this.$utils.Datetimes.ymdhis2num(row.shift_date, changed_time)
                let leave_time = row.leave_checked_at ? this.$utils.Datetimes.ymdhis2num(row.shift_date, row.leave_checked_at) : 2160
                let modal = this.$Modal.confirm({
                    title: "勤務開始時間確認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('vTimeSelector', {
                            props: {
                                value: changed_time,
                                steps: [1, 1],
                                lte: leave_time
                            },
                            on: {
                                'on-change': (val) => {
                                    changed_time = val
                                }
                            }}),
                            row.arrive_checked_at ? h('Button',{
                                style: {
                                    cursor: 'pointer',
                                    marginLeft: '15px',
                                    padding: '5px 10px'
                                },
                                props: {
                                    type: 'warning',
                                },
                                on: {
                                    click: () => {
                                        if(!row.arrive_checked_at) return true
                                        const params = {
                                            shift_id: row.id,
                                            is_confirmed: 2,
                                            changed_time: null
                                        }
                                        confirmRequestArrive(params).then(res => {
                                            if (res.data.status == "success") {
                                                _this.$Message.success('成功');
                                                _this.getStaffAttendanceList()
                                                this.$Modal.remove();
                                            }
                                        })
                                    }
                                }
                            }, '取り消し'
                            ) : '']
                        )
                    },
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 2,
                            changed_time: changed_time
                        }
                        confirmRequestArrive(params).then(res => {
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        })
                    },
                    closable: true,
                    onCancel: () => {
                    },
                })
            },
            changeRequestLeave(row) {
                let _this = this
                let changed_time = row.leave_checked_at ? row.leave_checked_at : (row.field_e_time ? row.shift_date + " " + this.$utils.Datetimes.num2hi(row.field_e_time) + ":00" : this.$utils.Datetimes.getymdhis(new Date()))
                changed_time = this.$utils.Datetimes.ymdhis2num(row.shift_date, changed_time)
                let arrive_time = row.arrive_checked_at ? row.arrive_checked_at : (row.field_s_time ? row.shift_date + " " + this.$utils.Datetimes.num2hi(row.field_s_time) + ":00" : this.$utils.Datetimes.getymdhis(new Date()))
                arrive_time = this.$utils.Datetimes.ymdhis2num(row.shift_date, arrive_time)
                this.$Modal.confirm({
                    title: "勤務終了時間確認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('vTimeSelector', {
                            props: {
                                value: changed_time,
                                steps: [1, 1],
                                gte: arrive_time
                            },
                            on: {
                                'on-change': (val) => {
                                    changed_time = val
                                }
                            }
                        }),
                            row.leave_checked_at ? h('Button',{
                                    style: {
                                        cursor: 'pointer',
                                        marginLeft: '15px',
                                        padding: '5px 10px'
                                    },
                                    props: {
                                        type: 'warning',
                                    },
                                    on: {
                                        click: () => {
                                            const params = {
                                                shift_id: row.id,
                                                is_confirmed: 2,
                                                changed_time: null
                                            }
                                            confirmRequestLeave(params).then(res => {
                                                if (res.data.status == "success") {
                                                    _this.$Message.success('成功');
                                                    _this.getStaffAttendanceList()
                                                    this.$Modal.remove()
                                                }
                                            })
                                        }
                                    }
                                }, '取り消し'
                            ): ''
                        ])
                    },
                    closable: true,
                    onOk: () => {
                        if(changed_time < arrive_time){
                            _this.$message.error("終了時間は、開始時間以上でなければなりません。")
                            return;
                        }
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 2,
                            changed_time: changed_time
                        }
                        confirmRequestLeave(params).then(res => {
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        })
                    },
                    onCancel: () => {

                    },
                })
            },
            changeRequestBreak(row) {
                let _this = this
                let changed_time = row.break_time ? row.break_time : 0
                this.$Modal.confirm({
                    title: "勤務休憩時間確認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('vTimeSelector', {
                            props: {
                                value: changed_time,
                                steps: [1, 1],
                                lte: (row.leave_checked_at) ? this.$utils.Datetimes.ymdhis2num(row.shift_date, row.leave_checked_at) : 2160
                            },
                            on: {
                                'on-change': (val) => {
                                    changed_time = val
                                }
                            }
                        })])
                    },
                    closable: true,
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 2,
                            break_changed_time: changed_time
                        }
                        confirmRequestBreak(params).then(res => {
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        })
                    },
                    onCancel: () => {
                    },
                })
            },
            changeRequestNightBreak(row) {
                let _this = this
                let changed_time = row.night_break_time ? row.night_break_time : 0
                this.$Modal.confirm({
                    title: "深夜勤務休憩時間申請承認",
                    render: (h) => {
                        return h('div', {style: {'text-align': 'center'}}, [h('vTimeSelector', {
                            props: {
                                value: changed_time,
                                steps: [1, 1],
                                lte: (row.leave_checked_at) ? this.$utils.Datetimes.ymdhis2num(row.shift_date, row.leave_checked_at) : 2160
                            },
                            on: {
                                'on-change': (val) => {
                                    changed_time = val
                                }
                            }
                        })])
                    },
                    closable: true,
                    onOk: () => {
                        const params = {
                            shift_id: row.id,
                            is_confirmed: 2,
                            night_break_changed_time: changed_time
                        }
                        confirmRequestNightBreak(params).then(res => {
                            if (res.data.status == "success") {
                                _this.$Message.success('成功');
                                _this.getStaffAttendanceList()
                            }
                        })
                    },
                    onCancel: () => {
                    },
                })
            },
        },
        mounted() {

        },
        created() {

        }
    }
</script>

<style>
    #attendance_table .ivu-table-cell {
        padding: 3px 3px;
    }
    #attendance_table .editable-col .ivu-table-cell > div {
        padding: 10px 0!important;
        cursor: pointer!important;
    }
</style>
