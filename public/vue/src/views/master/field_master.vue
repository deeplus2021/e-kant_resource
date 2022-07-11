<template>
    <div>
        <br/>
        <Row>
            <Col span="12" style="padding-left: 20px;">
                <p style="display: inline-block;margin-left: 10px;">現場名</p>
                <Input v-model="search_box.name" placeholder="現場名" style="display: inline-block;width: 130px;" :maxlength="32" @on-enter="search"></Input>
                <Button type="success" style="display: inline-block;width:100px;margin-left: 10px;" long @click="search">
                    検　索
                </Button>
            </Col>
        </Row>
        <br/>
        <vTablePackage :loading="loading" :tableColumns="tableColumns" :tableData="tableData" :tableHeight="Height"
                       :pageTotal="pageTotal" :pagePage="pagePage" :pageSize="pageLimit" @changepage="changePage" @tableAdd="tableAdd" :enableAdd="roles===1"
                       @tableChange="tableChange" @tableDelete="tableDelete" border></vTablePackage>
        <Modal v-model="new_field_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="1000" :styles="{top: '70px'}">
            <NewField ref="refNewField" @toCancel="toCancel"></NewField>
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
    import NewField from '@/components/New/NewField.vue'
    import {
        getFieldInfoList,
        getFieldInfo,
        deleteFields
    } from '@/api/field_master'

    export default {
        components: {
            NewField
        },
        data() {
            return {
                tableColumns: [{
                    type: 'selection',
                    width: 60,
                    align: 'center'
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
                            return h('div', [
                                h('Button', {
                                    props: {
                                        type: 'info',
                                        size: 'small'
                                    },
                                    style: {
                                        marginRight: '5px'
                                    },
                                    on: {
                                        click: () => {
                                            this.tableChange([params.row])
                                        }
                                    }
                                }, '編集'),
                                h('Button', {
                                    props: {
                                        type: 'error',
                                        size: 'small'
                                    },
                                    on: {
                                        click: () => {
                                            this.tableDelete([params.row])
                                        }
                                    }
                                }, '削除')
                            ]);
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
                new_field_modal: false,
            }
        },
        methods: {
            search() {
                this.pagePage = 1
                this.pageStart = 0
                this.getFieldInfoList();
            },
            toCancel(is_refesh) {
                if (is_refesh) {
                    this.getFieldInfoList()
                }
                this.new_field_modal = false
            },
            getFieldInfoList() {
                this.loading = true
                let params = {
                    name: this.search_box.name,
                    page: this.pagePage,
                    start: this.pageStart,
                    limit: this.pageLimit
                }
                getFieldInfoList(params).then(res => {
                    this.loading = false;
                    if (res.data.status == "success") {
                        this.tableData = res.data.result;
                        this.pageTotal = res.data.total;
                    }
                }).catch((error) => {this.loading = false;})
            },
            getFieldInfo(field_id) {
                let params = {
                    field_id: field_id,
                }
                getFieldInfo(params).then(res => {
                    if (res.data.status == "success") {
                        this.$refs.refNewField.showField(res.data.result)
                        this.new_field_modal = true
                    }
                })
            },
            changePage(page) {
                this.pagePage = page
                this.pageStart = page * this.pageLimit
                this.getFieldInfoList()
            },
            tableAdd() {
                this.$refs.refNewField.showField()
                this.new_field_modal = true
            },
            tableChange(rows) {
                this.getFieldInfo(rows[0].id);
            },
            tableDelete(rows) {
                let ids = []
                for (let i = 0; i < rows.length; i++) {
                    ids.push(rows[i].id)
                }
                let params = {
                    field_ids: ids
                };
                let that = this;
                this.$Modal.confirm({
                    title: '削除',
                    content: '削除しますか？',
                    okText: "はい",
                    cancelText: "いいえ",
                    onOk: () => {
                        deleteFields(params).then(res => {
                            if (res.data.status == 'success') {
                                this.$Message.success('正常に削除されました');
                                this.getFieldInfoList()
                            } else {
                                this.$Message.error('削除に失敗しました');
                            }
                        })
                    },
                    onCancel: () => {
                    },
                });
            }
        },
        mounted() {
            this.getFieldInfoList()
        },
        computed: {
            ...mapGetters([
                "roles",
                "screenHeight",
            ]),
            Height() {
                return parseInt(this.screenHeight - 300)
            }
        }
    }
</script>
