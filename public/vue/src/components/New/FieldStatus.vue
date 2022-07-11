<template>
    <div>
        <Card>
            <h3 slot="title" style="margin-left: 10px;">{{field_info.name}}:オープン状況一覧 <span style="padding-left: 50px;">{{field_info.address}}</span><span style="padding-left: 50px;">電話番号:{{field_info.tel}}</span></h3>
            <Table :columns="tableColumns" :data="tableData" size="small" border></Table>
        </Card>
    </div>
</template>

<script>
    export default {
        name: "FieldStatus",
        data() {
            return {
                field_info:{},
                tableColumns: [
                    {
                        title: '名前',
                        align: 'center',
                        render: (h, params) => {
                            return h('span', params.row.staff.name)
                        }
                    },
                    {
                        title: '前日確認',
                        align: 'center',
                        key: 'yesterday_checked_at',
                        render: (h, params) => {
                            let str = '前日未確認'
                            if(params.row.yesterday_checked_at){
                                str = '前日確認済'
                            }
                            return h('span', str)
                        }
                    },
                    {
                        title: '当日確認',
                        align: 'center',
                        key: 'today_checked_at',
                        render: (h, params) => {
                            let str = '当日未確認'
                            if(params.row.today_checked_at){
                                str = '当日確認済'
                            }
                            return h('span', str)
                        }
                    },
                    {
                        title: '勤務時間',
                        align: 'center',
                        key: 's_time',
                        render: (h, params) => {
                            return h('span', (params.row.arrive_checked_at ? this.$utils.Datetimes.ymdhis2hi(params.row.shift_date, params.row.arrive_checked_at):"") + "~" + (params.row.leave_checked_at ? this.$utils.Datetimes.ymdhis2hi(params.row.shift_date, params.row.leave_checked_at):""))
                        }
                    },
                    {
                        title: '休憩時間',
                        align: 'center',
                        render: (h, params) => {
                            return h('span', params.row.break_time ? this.$utils.Datetimes.num2hi(params.row.break_time) : "")
                        }
                    },
                    {
                        title: "スタッフの状況",
                        align: "center",
                        render: (h, params) => {
                            let color = "#f66"
                            if(params.row.staff_status_id == 0){
                                color = "#666"
                            }
                            else if(params.row.staff_status_id == 1){
                                color = "#dd3"
                            }
                            else if(params.row.staff_status_id == 2){
                                color = "#66f"
                            }
                            else if(params.row.staff_status_id == 3){
                                color = "#3d3"
                            }
                            else if(params.row.staff_status_id == 4){
                                color = "#666"
                            }
                            else if(params.row.staff_status_id == 5){
                                color = "#666"
                            }

                            return h('span', {
                                style: {
                                    color: color
                                },
                            }, params.row.status ? params.row.status.name : "警告")
                        }
                    },
                ],
                tableData: []
            }
        },
        methods: {
            showData(field_info, data) {
                this.field_info = field_info
                this.tableData = data
            }
        }
    }
</script>

<style scoped>

</style>
