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
                       :enableAdd="false" :enableChange="false" :enableDelete="false" border></vTablePackage>
        <Modal v-model="post_list_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="1000" :styles="{top: '70px'}">
            <PostList ref="refPostList"></PostList>
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
    import PostList from '@/components/New/PostList.vue'
    import {
        getFieldInfoList,
    } from '@/api/post_master'

    export default {
        components: {
            PostList
        },
        data() {
            return {
                tableColumns: [
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
                            }, '詳細')
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
                post_list_modal: false,
                field_id: null,
                s_time: 0,
                e_time: 0
            }
        },
        methods: {
            search() {
                this.pagePage = 1
                this.pageStart = 0
                this.getFieldInfoList();
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
            changePage(page) {
                this.pagePage = page
                this.pageStart = page * this.pageLimit
                this.getFieldInfoList()
            },
            tableChange(rows) {
                this.$refs.refPostList.showPostList(rows[0])
                this.post_list_modal = true
            },
        },
        mounted() {
            this.getFieldInfoList()
        },
        computed: {
            ...mapGetters(["screenHeight"]),
            Height() {
                return parseInt(this.screenHeight - 300)
            }
        }
    }
</script>
