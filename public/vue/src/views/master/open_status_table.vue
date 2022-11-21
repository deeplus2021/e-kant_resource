<template>
    <div>
        <br/>
        <Row>
            <p style="display: inline-block;margin-left: 10px;">日 付</p>
            <DatePicker v-model="shift_date" placeholder="Select date" @on-change="search"></DatePicker>
            <Button type="success" style="display: inline-block;width:100px;margin-left: 10px;" long @click="search">
                表　示
            </Button>
        </Row>
        <br/>
        <vTablePackage :loading="loading" :tableColumns="tableColumns" :tableData="tableData" :tableHeight="Height"
                       :pageTotal="pageTotal" :pagePage="pagePage" :pageSize="pageLimit" @changepage="changePage" @tableChange="tableChange"
                       :enableAdd="false" :enableChange="false" :enableDelete="false" border></vTablePackage>
        <Modal v-model="field_status_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="1000" :styles="{top: '70px'}">
            <FieldStatus ref="refFieldStatus"></FieldStatus>
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
        getFieldList,
        getFieldStatus,
    } from '@/api/open_status'
    import FieldStatus from '@/components/New/FieldStatus.vue'

    export default {
        components: {
            FieldStatus
        },
        data() {
            return {
                tableColumns: [
                    {
                        title: '現場名',
                        key: 'name'
                    },
                    {
                        title: '開始時間',
                        key: 's_time',
                        render: (h, params) => {
                            return h('span', this.$utils.Datetimes.num2hi(params.row.s_time))
                        }
                    },
                    {
                        title: '終了時間',
                        key: 'e_time',
                        render: (h, params) => {
                            return h('span', this.$utils.Datetimes.num2hi(params.row.e_time))
                        }
                    },
                    {
                        title: 'スタッフの状況',
                        key: 'status',
                        render:(h, params) => {
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
                            }, params.row.status ? params.row.status : (params.row.staff_name ? "警告" : ""))
                        }
                    },
                    {
                        title: '現場担当者',
                        key: 'staff_name',
                        render: (h, params) => {
                            return h('span', params.row.staff_name ? params.row.staff_name : "担当者がありません。")
                        }
                    },
                    {
                        title: '担当連絡先',
                        key: 'tel'
                    },
                    {
                        title: "詳 細",
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
                            }, '詳 細')
                        }
                    }
                ],
                tableData: [],
                shift_date: new Date(),
                pageTotal: 1,
                pagePage: 1,
                pageStart: 0,
                pageLimit: 15,
                loading: false,
                field_status_modal: false
            }
        },
        methods: {
            search() {
                this.pagePage = 1
                this.pageStart = 0
                this.getFieldList();
            },
            getFieldList() {
                this.loading = true
                let params = {
                    shift_date: this.$utils.Datetimes.getymd(this.shift_date),
                    page: this.pagePage,
                    start: this.pageStart,
                    limit: this.pageLimit
                }
                getFieldList(params).then(res => {
                    this.loading = false;
                    if (res.data.status == "success") {
                        this.tableData = res.data.result;
                        this.pageTotal = res.data.total;
                    }
                }).catch((error) => {this.loading = false})
            },
            getFieldStatus(field_info) {
                this.loading = true
                let params = {
                    field_id: field_info.id,
                    shift_date: this.$utils.Datetimes.getymd(this.shift_date),
                }
                getFieldStatus(params).then(res => {
                    this.loading = false;
                    if (res.data.status == "success") {
                        this.$refs.refFieldStatus.showData(field_info, res.data.result)
                        this.field_status_modal = true
                    }
                }).catch((error) => {this.loading = false})
            },
            changePage(page) {
                this.pagePage = page
                this.pageStart = page * this.pageLimit
                this.getFieldList()
            },
            tableChange(rows) {
                this.getFieldStatus(rows[0])
            },
        },
        mounted() {
            this.getFieldList()
        },
        computed: {
            ...mapGetters(["screenHeight"]),
            Height() {
                return parseInt(this.screenHeight - 300)
            }
        }
    }
</script>
