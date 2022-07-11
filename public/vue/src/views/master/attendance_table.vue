<template>
    <div>
        <br/>
        <Form :label-width="80" label-position="right">
            <Row>
                <Col span="24">
                    <FormItem label="日付">
                        <DatePicker v-model="searchBox.date_range" type="daterange" placeholder="Select date range"
                                    style="width: 300px" @on-change="search"></DatePicker>
                    </FormItem>
                </Col>
            </Row>
            <Row>
                <Col span="8">
                    <FormItem label="名前">
                        <Input v-model="searchBox.staff_name" @on-enter="search"></Input>
                    </FormItem>
                </Col>
                <Col span="8">
                    <FormItem label="現場名">
                        <Input v-model="searchBox.field_name" @on-enter="search"></Input>
                    </FormItem>
                </Col>
                <Col span="8" style="text-align: center">
                    <Button type="success" @click="search">表　示</Button>
                </Col>
            </Row>
        </Form>
        <vTablePackage :loading="loading" :tableColumns="tableColumns" :tableData="tableData" :tableHeight="Height"
                       :pageTotal="pageTotal" :pagePage="pagePage" :pageSize="pageLimit" @changepage="changePage" @tableChange="tableChange"
                       :enableAdd="false" :enableChange="false" :enableDelete="false" border></vTablePackage>
        <Row>
            <Col span="6" style="padding-left: 20px;">
                <Button type="success" @click="exportCSV" :loading="loadingExport">ＣＳＶ出力</Button>
            </Col>
            <Col span="6" style="padding-left: 20px;">
                <Button type="info" @click="checkAllStatus" :loading="loadingAllCheck"  :disabled="!enableAllCheck">一括勤怠承認</Button>
            </Col>
        </Row>
        <Modal v-model="detail_modal" @on-cancel="search" :mask-closable="false" :closable="true" :footer-hide="true" width="1400" :styles="{top: '70px'}">
            <AttendanceList ref="refAttendanceList"></AttendanceList>
        </Modal>
    </div>
</template>

<script>
    import {mapGetters} from "vuex";
    import {
        getAttendanceList,
        confirmAllRequest,
        exportAttendance,
    } from '@/api/attendance_master'
    import AttendanceList from '@/components/New/AttendanceList.vue'

    export default {
        name: "attendance_table",
        components: {
            AttendanceList
        },
        data() {
            return {
                searchBox: {
                    date_range: [],
                    staff_name: '',
                    field_name: ''
                },
                loading: false,
                tableColumns: [
                    {
                        title: "現場名",
                        key: "field_name"
                    },
                    {
                        title: "名前",
                        key: "staff_name"
                    },
                    {
                        title: "シフト時間",
                        key: "sum_shift_time",
                        render: (h, params) => {
                          return h('span', params.row.sum_shift_time>=0 ? (params.row.sum_shift_time / 60).toFixed(1) : '')
                        }
                    },
                    {
                        title: "勤務時間",
                        key: "sum_work_time",
                        render: (h, params) => {
                          return h('span', params.row.sum_work_time>=0 ? (params.row.sum_work_time / 60).toFixed(1) : '')
                        }
                    },
                    {
                        title: "勤怠申請",
                        render:(h, params)=>{
                            if(params.row.requested > 0){
                                return h('span', '✔')
                            }
                            else{
                                return h('span', '')
                            }
                        }
                    },
                    {
                        title: "勤怠申請承認",
                        render:(h, params)=>{
                            if(params.row.requested > 0 && params.row.no_checked == 0){
                                return h('span', '✔')
                            }
                            else{
                                return h('span', '')
                            }
                        }
                    },
                    {
                        title: "勤怠承認",
                        render:(h, params)=>{
                            if(params.row.no_checked > 0){
                                this.enableAllCheck = true
                                return h('Button', {
                                    props: {
                                        loading:params.row.$isLoadingAll,
                                        type: 'info',
                                        size: 'small'
                                    },
                                    on: {
                                        click: () => {
                                            this.checkStatus(params.row)
                                        }
                                    }
                                }, '未承認')
                            }
                            else{
                                return h('span', '')
                            }
                        }
                    },
                    {
                        title: "詳細",
                        align: "center",
                        render: (h, params) => {
                            return h('Button', {
                                props: {
                                    type: 'info',
                                    size: 'small'
                                },
                                on: {
                                    click: () => {
                                        this.tableChange([params.row])
                                    }
                                }
                            }, '詳細')
                        }
                    }
                ],
                tableData: [],
                pageTotal: 1,
                pagePage: 1,
                pageStart: 0,
                pageLimit: 15,
                detail_modal: false,
                loadingExport:false,
                loadingAllCheck: false,
                enableAllCheck: false
            }
        },
        methods: {
            search() {
                this.getAttendanceList()
            },
            changePage(page) {
                this.pagePage = page
                this.pageStart = page * this.pageLimit
                this.getAttendanceList()
            },
            tableChange(rows) {
                this.$refs.refAttendanceList.showData(rows[0], this.searchBox.date_range[0], this.searchBox.date_range[1])
                this.detail_modal = true
            },
            getAttendanceList() {
                this.loading = true
                this.enableAllCheck = false
                let params = {
                    staff_name: this.searchBox.staff_name,
                    field_name: this.searchBox.field_name,
                    s_date: this.$utils.Datetimes.getymd(this.searchBox.date_range[0]),
                    e_date: this.$utils.Datetimes.getymd(this.searchBox.date_range[1]),
                    page: this.pagePage,
                    start: this.pageStart,
                    limit: this.pageLimit
                }
                getAttendanceList(params).then(res => {
                    this.loading = false;
                    if (res.data.status == "success") {
                        this.tableData = res.data.result;
                        this.pageTotal = res.data.total;
                    }
                }).catch((error) => {this.loading = false;})
            },
            checkStatus(row){
                this.$message.info("右の詳細ボタンを押して、個別に承認をお願いします。");
                return;
                let _this = this
                this.$Modal.confirm({
                    title:"残遅早欠申請承認",
                    render: (h) => {
                        return h('span', "")
                    },
                    okText: "承 認",
                    cancelText: "キャンセル",
                    onOk: () => {
                        let params = {
                            staff_ids: [row.staff_id],
                            s_date: _this.$utils.Datetimes.getymd(this.searchBox.date_range[0]),
                            e_date: _this.$utils.Datetimes.getymd(this.searchBox.date_range[1]),
                            is_confirmed: 1
                        };
                        _this.$set(row, '$isLoadingAll', true)
                        confirmAllRequest(params).then(res => {
                            _this.$set(row, '$isLoadingAll', false)
                            if (res.data.status == 'success') {
                                _this.getAttendanceList()
                            }
                        }).catch((error) => {_this.$set(row, '$isLoadingAll', false)})
                    },
                    onCancel: () => {
                        /*let params = {
                            staff_ids: [row.staff_id],
                            s_date: _this.$utils.Datetimes.getymd(this.searchBox.date_range[0]),
                            e_date: _this.$utils.Datetimes.getymd(this.searchBox.date_range[1]),
                            is_confirmed: 0
                        };
                        _this.$set(row, '$isLoadingAll', true)
                        confirmAllRequest(params).then(res => {
                            _this.$set(row, '$isLoadingAll', false)
                            if (res.data.status == 'success') {
                                _this.getAttendanceList()
                            }
                        }).catch((error) => {_this.$set(row, '$isLoadingAll', false)})*/
                    },
                })
            },
            checkAllStatus(){
                let staff_ids = []

                for(let i =0; i<this.tableData.length;i++){
                    staff_ids.push(this.tableData[i].staff_id)
                }

                if(staff_ids.length == 0){
                    return false
                }
                let _this = this
                this.$Modal.confirm({
                    title:"残遅早欠申請一括承認",
                    render: (h) => {
                        return h('span', "")
                    },
                    okText: "一括承認",
                    cancelText: "キャンセル",
                    onOk: () => {
                        let params = {
                            staff_ids: staff_ids,
                            s_date: _this.$utils.Datetimes.getymd(this.searchBox.date_range[0]),
                            e_date: _this.$utils.Datetimes.getymd(this.searchBox.date_range[1]),
                            is_confirmed: 1
                        };
                        _this.loadingAllCheck =  true
                        confirmAllRequest(params).then(res => {
                            _this.loadingAllCheck =  false
                            if (res.data.status == 'success') {
                                _this.getAttendanceList()
                            }
                        }).catch((error) => {_this.loadingAllCheck =  false})
                    },
                    onCancel: () => {
                        /*let params = {
                            staff_ids: staff_ids,
                            s_date: _this.$utils.Datetimes.getymd(this.searchBox.date_range[0]),
                            e_date: _this.$utils.Datetimes.getymd(this.searchBox.date_range[1]),
                            is_confirmed: 0
                        };
                        _this.loadingAllCheck = true
                        confirmAllRequest(params).then(res => {
                            _this.loadingAllCheck = false
                            if (res.data.status == 'success') {
                                _this.getAttendanceList()
                            }
                        }).catch((error) => {_this.loadingAllCheck =  false})*/
                    },
                })
            },
            exportCSV(){
                let params = {
                    staff_name: this.searchBox.staff_name,
                    field_name: this.searchBox.field_name,
                    s_date: this.$utils.Datetimes.getymd(this.searchBox.date_range[0]),
                    e_date: this.$utils.Datetimes.getymd(this.searchBox.date_range[1]),
                }
                this.loadingExport = true
                exportAttendance(params).then((response)=>{
                    this.loadingExport = false
                    let fileURL = window.URL.createObjectURL(new Blob([response.data]));
                    let fileLink = document.createElement('a');
                    fileLink.href = fileURL;
                    fileLink.setAttribute('download', '勤怠.csv');
                    document.body.appendChild(fileLink);
                    fileLink.click();
                }).catch((error) => {this.loadingExport = false})
            }
        },
        mounted() {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            this.searchBox.date_range =  [start, end];
            
            this.getAttendanceList()
        },
        computed: {
            ...mapGetters(["screenHeight"]),
            Height() {
                return parseInt(this.screenHeight - 350)
            }
        }
    }
</script>

<style scoped>

</style>
