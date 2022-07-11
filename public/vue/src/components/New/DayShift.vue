<template>
    <Card>
        <Row slot="title">
            <Col span="6">
                <h2>現場名: {{field_name}}</h2>
            </Col>
            <Col span="6">
                <h2>日付: {{this.$utils.Datetimes.getymd(this.shift_date)}}</h2>
            </Col>
        </Row>
        <vTablePackage id="shift_table" :loading="day_loading" :tableColumns="tableColumns" :tableData="tableData"
                       :tableHeight="Height"
                       :enablePage="false" :enableAdd="false" :enableChange="false" :enableDelete="false"
                       size="small" border></vTablePackage>
        <Row><Col span="24" style="text-align: center">
            <Button type="info" @click="exportCsv" :loading="loadingExport">帳票CSV印刷</Button>
        </Col> </Row>
    </Card>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex'
    import {
        getShiftInfoList,
        getPostTimes,
        exportDayShifts
    } from '@/api/shift_master'

    export default {
        components: {},
        props: ['field_id', 'shift_date', 'field_name'],
        data() {
            return {
                tableColumns: [
                    {
                        title: '名前',
                        width: 120,
                        align: 'center',
                        key: 'staff_name',
                        fixed: 'left'
                    },
                ],
                tableData: [],
                title: [],
                post_times:[],
                day_loading:false,
                loadingExport: false
            }
        },
        methods: {
            getShiftInfoList() {
                this.day_loading = true
                let params = {
                    field_id: this.field_id,
                    shift_date: this.$utils.Datetimes.getymd(this.shift_date),
                }
                getShiftInfoList(params).then(res => {
                    this.day_loading = false;
                    if (res.data.status == "success") {
                        this.getPostTimes(res.data.result)
                    }
                }).catch((error) => {this.day_loading = false})
            },
            getPostTimes(data) {
                let params = {
                    field_id: this.field_id,
                    shift_date: this.$utils.Datetimes.getymd(this.shift_date),
                }
                getPostTimes(params).then(res => {
                    if (res.data.status == "success") {
                        let post = {}
                        post["id"] = null
                        post["staff_name"] = "配置人数"
                        post["_disabled"] = true
                        let result = res.data.result
                        if (result && result["post_times"]) {
                            for (let i = 0; i < result["post_times"].length; i++) {
                                const one = result["post_times"][i]
                                const s_time = this.$utils.Datetimes.num2hi(one["s_time"])
                                if (this.title.includes(s_time)) {
                                    post[s_time] = one["number"]
                                }
                            }
                        }
                        this.post_times = post
                        this.makeTable(data)
                    }
                })
            },
            getTableTitle() {
                let start = 0
                let end = 2160
                let title = []
                for (let t = start; t < end; t += 15) {
                    title.push(this.$utils.Datetimes.num2hi(t))
                }
                return title
            },
            makeTable(data) {
                let table_data = []
                let sums = {}
                sums["sum_times"] = 0
                for (let i = 0; i < data.length; i++) {
                    let item = data[i]
                    let one = {}
                    one["id"] = item["id"]
                    one["staff_name"] = item["name"]
                    one["s_time"] = this.$utils.Datetimes.num2hi(item["s_time"])
                    one["e_time"] = this.$utils.Datetimes.num2hi(item["e_time"])
                    one["ks_time"] = this.$utils.Datetimes.num2hi(item["ks_time"])
                    one["ke_time"] = this.$utils.Datetimes.num2hi(item["ke_time"])
                    one["editabled"] = true
                    one["cellClassName"] = {}
                    let sum_times = 0
                    for (let j = 0; j < this.title.length; j++) {
                        one[this.title[j]] = ''
                        if (!sums[this.title[j]]) {
                            sums[this.title[j]] = 0
                        }
                        if (one["s_time"] <= this.title[j] && one["e_time"] > this.title[j]) {
                            one["cellClassName"][this.title[j]] = "info-cell-shift"
                            one[this.title[j]] = 1
                            sums[this.title[j]] += 1
                            sum_times += 15
                        }
                        if (one["ks_time"] && one["ke_time"] && one["ks_time"] <= this.title[j] && one["ke_time"] > this.title[j]) {
                            one["cellClassName"][this.title[j]] = "info-cell-rest"
                            one[this.title[j]] = 2
                            sums[this.title[j]] -= 1
                            sum_times -= 15
                        }
                    }
                    one["sum_times"] = parseInt(sum_times / 60) + ":" + (sum_times % 60)
                    sums["sum_times"] += sum_times
                    table_data.push(one)
                }
                sums["id"] = null
                sums["sum_times"] = parseInt(sums["sum_times"] / 60) + ":" + (sums["sum_times"] % 60)
                sums["staff_name"] = "合 計"
                sums["_disabled"] = true
                table_data.push(sums)
                table_data.push(this.post_times)
                this.$set(this, 'tableData', table_data);
            },
            showDayShift(){
                this.getShiftInfoList()
            },
            exportCsv(){
                let params = {
                    field_id: this.field_id,
                    shift_date: this.$utils.Datetimes.getymd(this.shift_date),
                }
                this.loadingExport = true
                exportDayShifts(params).then((response)=>{
                    this.loadingExport = false
                    let fileURL = window.URL.createObjectURL(new Blob([response.data]));
                    let fileLink = document.createElement('a');
                    fileLink.href = fileURL;
                    fileLink.setAttribute('download', 'シフト表.csv');
                    document.body.appendChild(fileLink);
                    fileLink.click();
                })
            }
        },
        mounted() {
            this.title = this.getTableTitle()
            this.tableColumns = [
                {
                    title: '名前',
                    width: 120,
                    align: 'center',
                    key: 'staff_name',
                    fixed: 'left'
                },
            ]
            for (let i = 0; i < this.title.length; i++) {
                this.tableColumns.push(
                    {
                        title: this.title[i],
                        width: 42,
                        align: 'center',
                        key: this.title[i],
                    }
                )
            }
            this.tableColumns.push(
                {
                    title: '配置時間',
                    width: 60,
                    align: 'center',
                    key: 'sum_times',
                    fixed: 'right'
                }
            )
        },
        created() {

        },
        watch: {},
        computed: {
            ...mapGetters(["screenHeight"]),
            Height() {
                return parseInt(this.screenHeight - 300)
            }
        }
    }
</script>

<style>
    .ivu-table .info-cell-shift {
        background-color: #2b90ff !important;
        color: #2b90ff !important;
    }

    .ivu-table .info-cell-rest {
        background-color: #6feeff !important;
        color: #6feeff !important;
    }

    #shift_table .ivu-table-cell {
        padding-left: 1px;
        padding-right: 1px;
    }
</style>
