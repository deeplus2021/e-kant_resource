<template>
    <div>
        <br/>
        <Row>
            <p style="display: inline-block;margin-left: 10px;">現場名</p>
            <Input v-model="search_box.name" placeholder="現場名" style="display: inline-block;width: 130px;" :maxlength="32" @on-enter="search"></Input>
            <Button type="success" style="display: inline-block;width:100px;margin-left: 10px;" long @click="search">
                検　索
            </Button>
        </Row>
        <br/>
        <vTablePackage :loading="loading" :tableColumns="tableColumns" :tableData="tableData" :tableHeight="Height"
                       :pageTotal="pageTotal" :pagePage="pagePage" :pageSize="pageLimit" @changepage="changePage" @tableChange="tableChange"
                       :enableAdd="false" :enableDelete="false" border></vTablePackage>
        <Modal v-model="shift_month_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="1200" :styles="{top: '70px'}">
            <ShiftMonth ref="refShiftMonth" @toCancel="toCancel"></ShiftMonth>
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
        getShiftInfoList,
    } from '@/api/shift_master'
    import ShiftMonth from "../../components/New/ShiftMonth";
    export default {
        components: {
            ShiftMonth,
        },
        data() {
            return {
                tableColumns: [
                    {
                        type: 'selection',
                        width: 60,
                        align: 'center',
                    },
                    {
                        title: '現場名',
                        key: 'name'
                    },
                    {
                        title: '所在地',
                        key: 'address'
                    },
                    {
                        title: '電話番号',
                        key: 'tel'
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
                        title: "操　作",
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
                            }, '確認')
                        }
                    }
                ],
                tableData: [],
                search_box: {
                    name: '',
                },
                pageTotal: 1,
                pagePage: 1,
                pageStart: 0,
                pageLimit: 15,
                loading: false,
                shift_month_modal: false,
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
                    name: this.search_box.name,
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
            changePage(page) {
                this.pagePage = page
                this.pageStart = page * this.pageLimit
                this.getFieldList()
            },
            tableChange(rows) {
                this.shift_month_modal = true
                this.$refs.refShiftMonth.showShiftMonth(rows[0])
            },
            toCancel() {
                this.shift_month_modal = false
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
