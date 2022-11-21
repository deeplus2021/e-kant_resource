<template>
    <div>
        <br/>
        <Form :label-width="80" label-position="right">
            <Row>
                <FormItem label="現場名" inline>
                    <Input v-model="search_box.name" placeholder="現場名" style="display: inline-block;width: 200px;" :maxlength="32" @on-enter="search"></Input>
                </FormItem>
            </Row>
            <Row>
                <Col span="8">
                    <FormItem label="日付" inline>
                        <DatePicker v-model="search_box.shift_date" @on-change="search"></DatePicker>
                    </FormItem>
                </Col>
                <Col span="6">
                    <Button type="success" style="display: inline-block;width:100px;margin-left: 10px;" long @click="search">検　索</Button>
                </Col>
            </Row>
        </Form>
        <br/>
        <vTablePackage :loading="loading" :tableColumns="tableColumns" :tableData="tableData" :tableHeight="Height"
                       :pageTotal="pageTotal" :pagePage="pagePage" :pageSize="pageLimit" @changepage="changePage"
                       :enableAdd="false" :enableChange="false" :enableDelete="false" border></vTablePackage>
        <Modal v-model="work_detail_modal" :mask-closable="true" :closable="true" :footer-hide="true" width="1000" :styles="{top: '70px'}">
            <div>
                <Card>
                    <p slot="title">詳　細</p>
                    <Table :columns="detailColumns" :data="detailData" size="small" border></Table>
                </Card>
            </div>
        </Modal>
    </div>
</template>
<style>
    .text-center {
        text-align: center;
        margin-top: 7px;
    }
</style>
<script>
    import {
        mapGetters,
        mapActions
    } from 'vuex'
    import {
        getWorkInfoList,
    } from '@/api/work_master'

    export default {
        components: {},
        data() {
            return {
                work_detail_modal: false,
                tableColumns: [
                    {
                        title: '開始時間',
                        align: 'center',
                        key: 's_time',
                        render: (h, params) => {
                          if(params.row.no_shift == 0)
                            return h('span', this.$utils.Datetimes.num2hi(params.row.s_time))
                        }
                    },
                    {
                        title: '終了時間',
                        align: 'center',
                        key: 'e_time',
                        render: (h, params) => {
                          if(params.row.no_shift == 0)
                            return h('span', this.$utils.Datetimes.num2hi(params.row.e_time))
                        }
                    },
                    {
                        title: "現場担当者",
                        align: 'center',
                        key: 'staff_name',
                    },
                    {
                        title: "スタッフの状況",
                        align: "center",
                        render: (h, params) => {
                          if(params.row.no_shift == 0){
                            let color = "#f66"

                            if(params.row.staff_status_id == 1){
                              color = "#dd3"
                            }
                            else if(params.row.staff_status_id == 2){
                              color = "#66f"
                            }
                            else if(params.row.staff_status_id == 3){
                              color = "#3d3"
                            }
                            else if(params.row.staff_status_id == 4 || params.row.staff_status_id == 5 || params.row.staff_status_id == 8){
                              color = "#666"
                            }

                            return h('span', {
                              style: {
                                color: color
                              },
                            }, params.row.status_name ? params.row.status_name : "警告")
                          }
                          else{
                            let color = "#3d3"
                            if(params.row.leave_checked_at != null){
                              color = "#666"
                            }
                            return h('span', {
                              style: {
                                color: color
                              },
                            }, params.row.status_name ? params.row.status_name : "警告")
                          }

                        }
                    },
                    {
                        title: "携帯電話番号",
                        align: 'center',
                        key: 'staff_tel'
                    },
                    {
                        title: '現場名',
                        align: 'center',
                        key: 'field_name'
                    },
                    {
                        title: "詳　細",
                        align: "center",
                        render: (h, params) => {
                            return h('Button', {
                                props: {
                                    type: 'info',
                                    size: 'small'
                                },
                                on: {
                                    click: () => {
                                        this.showDetail(params.row)
                                    }
                                }
                            }, '詳細')
                        }
                    }
                ],
                tableData: [],
                search_box: {
                    name: '',
                    shift_date: new Date()
                },
                pageTotal: 1,
                pagePage: 1,
                pageStart: 0,
                pageLimit: 15,
                loading: false,
                field_info: {},
                detailColumns:[
                    {
                        title: '名　前',
                        align: 'center',
                        key: 'staff_name'
                    },
                    {
                        title: "シフト時間",
                        key: "s_time",
                        align: 'center',
                        render: (h, params) => {
                            if(!params.row.id || params.row.no_shift == 1){return ''}
                            return h('div', this.$utils.Datetimes.num2hi(params.row.s_time) + "~" + this.$utils.Datetimes.num2hi(params.row.e_time) )
                        }
                    },
                    {
                        title: "シフト休憩時間",
                        key: 'k_time',
                        align: 'center',
                        render: (h, params) => {
                            if(!params.row.ke_time){return ''}
                            return h('div', this.$utils.Datetimes.num2hi(params.row.ke_time - params.row.ks_time))
                        }
                    },
                    {
                        title: '前日確認',
                        align: 'center',
                        key:'yesterday_checked_at',
                        render: (h, params) => {
                          if(params.row.no_shift == 0){
                            let str = '前日未確認'
                            if(params.row.yesterday_checked_at){
                              str = '前日確認済'
                            }
                            return h('span', str)
                          }
                        }
                    },
                    {
                        title: '起床確認',
                        align: 'center',
                        key:'today_checked_at',
                        render: (h, params) => {
                          if(params.row.no_shift == 0){
                            let str = '当日未確認'
                            if(params.row.today_checked_at){
                              str = '当日確認済'
                            }
                            return h('span', str)
                          }
                        }
                    },
                    {
                        title: '勤務時間',
                        align: 'center',
                        render: (h, params) => {
                            if(params.row.no_shift != 0 || params.row.staff_status_id != 11){
                              const time = (params.row.arrive_checked_at ? this.$utils.Datetimes.ymdhis2hi(params.row.shift_date, params.row.arrive_checked_at):"") + "~" + (params.row.leave_checked_at ? this.$utils.Datetimes.ymdhis2hi(params.row.shift_date, params.row.leave_checked_at):"")
                              return h('span', time)
                            }
                        }
                    },
                    {
                        title: '勤務休憩時間',
                        align: 'center',
                        render: (h, params) => {
                          if(params.row.no_shift != 0 || params.row.staff_status_id != 11){
                            let time = ''
                            if(params.row.s_time < 21* 60){
                              time = params.row.break_time ? this.$utils.Datetimes.num2hi(params.row.break_time) : ''
                            }
                            else{
                              time = params.row.night_break_time ? this.$utils.Datetimes.num2hi(params.row.night_break_time):''
                            }
                            return h('span', time)
                          }
                        }
                    },
                    {
                        title: "スタッフの状況",
                        align: "center",
                        render: (h, params) => {
                          if(params.row.no_shift == 0){
                            let color = "#f66"
                            if(params.row.staff_status_id == 1){
                              color = "#dd3"
                            }
                            else if(params.row.staff_status_id == 2){
                              color = "#66f"
                            }
                            else if(params.row.staff_status_id == 3){
                              color = "#3d3"
                            }
                            else if(params.row.staff_status_id == 4 || params.row.staff_status_id == 5 || params.row.staff_status_id == 8){
                              color = "#666"
                            }

                            return h('span', {
                              style: {
                                color: color
                              },
                            }, params.row.status_name ? params.row.status_name : "警告")
                          }
                          else{
                            let color = "#3d3"
                            if(params.row.leave_checked_at != null){
                              color = "#666"
                            }
                            return h('span', {
                              style: {
                                color: color
                              },
                            }, params.row.status_name ? params.row.status_name : "警告")
                          }

                        }
                    },
                ],
                detailData:[]
            }
        },
        methods: {
            search() {
                this.pagePage = 1
                this.pageStart = 0
                this.getWorkInfoList();
            },
            getWorkInfoList() {
                this.loading = true
                let params = {
                    name: this.search_box.name,
                    shift_date: this.$utils.Datetimes.getymd(this.search_box.shift_date),
                    page: this.pagePage,
                    start: this.pageStart,
                    limit: this.pageLimit
                }
                getWorkInfoList(params).then(res => {
                    this.loading = false
                    if (res.data.status == "success") {
                        this.tableData = res.data.result
                        this.pageTotal = res.data.total
                    }
                }).catch((error) => {this.loading = false})
            },
            changePage(page) {
                this.pagePage = page
                this.pageStart = page * this.pageLimit
                this.getWorkInfoList()
            },
            showDetail(row) {
                this.$set(this, "detailData", [row])
                this.work_detail_modal = true
            },
            toCancel() {
                this.work_list_modal = false
            },
        },
        mounted() {
            this.getWorkInfoList()
        },
        computed: {
            ...mapGetters(["screenHeight"]),
            Height() {
                return parseInt(this.screenHeight - 300)
            }
        }
    }
</script>
