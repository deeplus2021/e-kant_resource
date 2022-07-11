<template>
    <div>
        <Card>
            <h3 slot="title" style="margin-left: 10px;">{{field_info.name}}:出勤状況一覧 <span style="padding-left: 50px;">{{field_info.address}}</span><span style="padding-left: 50px;">電話番号:{{field_info.tel}}</span></h3>
            <Form :label-width="80" label-position="right">
                <Row>
                    <Col span="8">
                        <FormItem label="日付" inline>
                            <DatePicker v-model="shift_date" placeholder="Select date"></DatePicker>
                        </FormItem>
                    </Col>
                    <Col span="6">
                        <Button type="success" style="display: inline-block;width:100px;margin-left: 10px;" long
                                @click="getWorkInfoList">検　索
                        </Button>
                    </Col>
                </Row>
            </Form>
            <Table :loading="loading" :columns="tableColumns" :data="tableData" size="small" border></Table>
            <Modal v-model="work_detail_modal" :mask-closable="true" :closable="true" :footer-hide="true" width="600" :styles="{top: '70px'}">
                <div>
                    <Card v-if="shift">
                        <p slot="title">詳　細</p>
                        <ul>
                            <li>名　前: {{shift.staff ? shift.staff.name : ''}}</li>
                            <li>時　間: {{$utils.Datetimes.num2hi(shift.s_time)}}-{{$utils.Datetimes.num2hi(shift.e_time)}}</li>
                            <li v-if="shift.ks_time">休憩時間: {{$utils.Datetimes.num2hi(shift.ks_time)}}-{{$utils.Datetimes.num2hi(shift.ke_time)}}</li>
                            <li>前日確認: {{shift.yesterday_checked_at}}</li>
                            <li>起床確認: {{shift.today_checked_at}}</li>
                        </ul>
                    </Card>
                </div>
            </Modal>
        </Card>
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
        props: ['field_info'],
        components: {},
        data() {
            return {
                tableColumns: [
                    {
                        title: '開始時間',
                        align: 'center',
                        key: 's_time',
                        render: (h, params) => {
                            return h('span', this.$utils.Datetimes.num2hi(params.row.s_time))
                        }
                    },
                    {
                        title: '終了時間',
                        align: 'center',
                        key: 'e_time',
                        render: (h, params) => {
                            return h('span', this.$utils.Datetimes.num2hi(params.row.e_time))
                        }
                    },
                    {
                        title: "現場担当者",
                        align: 'center',
                        render: (h, params) => {
                            return h('span', params.row.staff.name)
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
                    {
                        title: "携帯電話番号",
                        align: 'center',
                        render: (h, params) => {
                            return h('span', params.row.staff.tel)
                        }
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
                loading: false,
                shift_date: new Date(),
                work_detail_modal: false,
                shift: {}
            }
        },
        methods: {
            getWorkInfoList() {
                this.loading = true
                let params = {
                    field_id: this.field_info.id,
                    shift_date: this.$utils.Datetimes.getymd(this.shift_date),
                }
                getWorkInfoList(params).then(res => {
                    this.loading = false
                    if (res.data.status == "success") {
                        this.tableData = res.data.result
                    }
                })
            },
            showWorkList(data) {
                this.tableData = data
            },
            showDetail(row) {
                this.$set(this, "shift", row)
                this.work_detail_modal = true
            }
        },
        mounted() {

        },
        computed: {}
    }
</script>
