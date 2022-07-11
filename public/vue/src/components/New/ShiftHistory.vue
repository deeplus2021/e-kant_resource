<template>
    <div>
        <Card id="print_me">
            <h3 slot="title" style="margin-left: 10px;">{{field_info.name}}:シフト表 <span style="padding-left: 50px;">{{field_info.address}}</span><span style="padding-left: 50px;">電話番号:{{field_info.tel}}</span></h3>
            <Table class="shift_h_table" :loading="loading" :columns="tableColumns" :data="tableData"
                   size="small" border></Table>
            <br/>
            <Table class="shift_h_table" :loading="loading" :columns="ntableColumns" :data="ntableData"
                   size="small" border></Table>
        </Card>
        <br/>
        <Row><Col span="24" style="text-align: center">
            <Button type="info" @click="exportCsv" :loading="loadingExport">帳票CSV印刷</Button>
        </Col> </Row>
    </div>
</template>

<script>
    import {
        exportMonthShifts,
        exportWeekShifts,
    } from '@/api/shift_master'
    export default {
        data() {
            return {
                field_info:{},
                tableColumns: [
                    {
                        title: '【日勤】',
                        width: 120,
                        key: 'staff_name',
                        align: 'center',
                        fixed: 'left'
                    },
                ],
                ntableColumns: [
                    {
                        title: '【夜勤】',
                        width: 120,
                        key: 'staff_name',
                        align: 'center',
                        fixed: 'left'
                    },
                ],
                tableData: [],
                ntableData: [],
                loading: false,
                view_night_shift: false,
                type: 0,
                shift_info: {},
                loadingExport: false
            }
        },
        methods: {
            showTable(shift_data, s_date, e_date, field_info, type, shift_info) {
                this.field_info = field_info
                this.type = type
                this.shift_info = shift_info
                let s_times = s_date.getTime()
                let e_times = e_date.getTime()
                let tableColumns = [
                    {
                        title: '【日勤】',
                        width: 120,
                        align: 'center',
                        key: 'staff_name',
                        fixed: 'left'
                    },
                ]
                let ntableColumns = [
                    {
                        title: '【夜勤】',
                        width: 120,
                        align: 'center',
                        key: 'staff_name',
                        fixed: 'left'
                    },
                ]
                let ymd_list = []
                for (let t = s_times; t <= e_times; t += 24 * 60 * 60 * 1000) {
                    let dw = this.$utils.Datetimes.getdw(new Date(t))
                    let md = this.$utils.Datetimes.getmd(new Date(t))
                    let ymd = this.$utils.Datetimes.getymd(new Date(t))
                    ymd_list.push(ymd)
                    tableColumns.push({
                        title: dw + "\n" + md,
                        align: 'center',
                        width: 50,
                        key: ymd,
                        renderHeader: (h, params) => {
                            return h('div', [
                                h('span', {}, dw),
                                h('br', {}, ''),
                                h('span', {}, md),
                            ])
                        }
                    })
                    ntableColumns.push({
                        title: dw + "\n" + md,
                        align: 'center',
                        width: 50,
                        key: ymd,
                        renderHeader: (h, params) => {
                            return h('div', [
                                h('span', {}, dw),
                                h('br', {}, ''),
                                h('span', {}, md),
                            ])
                        }
                    })
                }
                this.tableColumns = tableColumns
                this.ntableColumns = ntableColumns
                let table_data = []
                let ntable_data = []
                shift_data.forEach((item, index) => {
                    let one = {}
                    let none = {}
                    one["staff_name"] = item["name"]
                    none["staff_name"] = item["name"]
                    item["shifts"].forEach((shift, index) => {
                        let idx = ymd_list.indexOf(shift["shift_date"])
                        if (idx > -1) {
                            if(shift["s_time"] < 21 * 60){
                                one[ymd_list[idx]] = this.$utils.Datetimes.getTimesString(shift["s_time"], shift["e_time"])
                            }
                            else{
                                none[ymd_list[idx]] = this.$utils.Datetimes.getTimesString(shift["s_time"], shift["e_time"])
                            }
                        }
                    })
                    table_data.push(one)
                    ntable_data.push(none)
                })
                this.$set(this, "tableData", table_data)
                this.$set(this, "ntableData", ntable_data)
            },
            exportCsv(){
                if(this.type==0){
                    this.exportMonthShifts()
                }
                else{
                    this.exportWeekShifts()
                }
            },
            exportMonthShifts(){
                let params = {
                    field_id: this.field_info.id,
                    shift_month: this.shift_info['shift_month']
                }
                this.loadingExport = true
                exportMonthShifts(params).then((response)=>{
                    this.loadingExport = false
                    let fileURL = window.URL.createObjectURL(new Blob([response.data]));
                    let fileLink = document.createElement('a');
                    fileLink.href = fileURL;
                    fileLink.setAttribute('download', 'シフト表.csv');
                    document.body.appendChild(fileLink);
                    fileLink.click();
                }).catch((error) => {this.loadingExport = false})
            },
            exportWeekShifts(){
                let params = {
                    field_id: this.field_info.id,
                    shift_s_date: this.shift_info['s_date'],
                    shift_e_date: this.shift_info['e_date'],
                }
                this.loadingExport = true
                exportWeekShifts(params).then((response)=>{
                    this.loadingExport = false
                    let fileURL = window.URL.createObjectURL(new Blob([response.data]));
                    let fileLink = document.createElement('a');
                    fileLink.href = fileURL;
                    fileLink.setAttribute('download', 'シフト表.csv');
                    document.body.appendChild(fileLink);
                    fileLink.click();
                }).catch((error) => {this.loadingExport = false})
            }
        }
    }
</script>

<style>
    .shift_h_table .ivu-table-cell {
        padding-left: 1px!important;
        padding-right: 1px!important;
    }
</style>

<style scoped>

</style>
