<template>
    <div>
        <Card>
            <h3 slot="title" style="margin-left: 10px;">{{field_info.name}}:配置ポスト表 <span style="padding-left: 50px;">{{field_info.address}}</span><span style="padding-left: 50px;">電話番号:{{field_info.tel}}</span></h3>
            <vTablePackage :loading="loading" :tableColumns="tableColumns" :tableData="tableData" :tableHeight="Height"
                           :pageTotal="pageTotal" :pagePage="pagePage" :pageSize="pageLimit" @changepage="changePage" @tableAdd="tableAdd"
                           @tableChange="tableChange" @tableDelete="tableDelete" border></vTablePackage>
            <Modal v-model="new_post_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="800" :styles="{top: '10px'}">
                <NewPost ref="refNewPost" :field_info="field_info" @toCancel="toCancel"></NewPost>
            </Modal>
        </Card>
    </div>
</template>

<script>
    import {mapGetters} from "vuex";
    import NewPost from '@/components/New/NewPost.vue'
    import {
        getPostInfo,
        deletePosts,
        getPostInfoList,
    } from '@/api/post_master'

    export default {
        name: "PostList",
        components: {
            NewPost
        },
        data() {
            return {
                field_id: null,
                field_info: {},
                s_time: null,
                e_time: null,
                weeks: ['月', '火', '水', '木', '金', '土', '日'],
                tableColumns: [{
                    type: 'selection',
                    width: 60,
                    align: 'center'
                },

                    {
                        title: '種類',
                        render: (h, params) => {
                            let value = params.row.p_week ? '曜日' : '特別日';
                            return h("span", {}, value)
                        }
                    },
                    {
                        title: '曜日',
                        key: 'p_week',
                        render: (h, params) => {
                            let value = params.row.p_week ? this.weeks[params.row.p_week - 1] : '';
                            return h("span", {}, value)
                        }
                    },
                    {
                        title: '開始日',
                        key: 's_date'
                    },
                    {
                        title: '終了日',
                        key: 'e_date'
                    },
                    {
                        title: "操　作",
                        align: 'center',
                        width: 150,
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
                new_post_modal: false,
            }
        },
        methods: {
            toCancel(is_refesh) {
                if (is_refesh) {
                    this.getPostInfoList()
                }
                this.new_post_modal = false
            },
            getPostInfoList() {
                this.loading = true
                let params = {
                    field_id: this.field_id,
                    page: this.pagePage,
                    start: this.pageStart,
                    limit: this.pageLimit
                }
                getPostInfoList(params).then(res => {
                    this.loading = false;
                    if (res.data.status == "success") {
                        this.tableData = res.data.result;
                        this.pageTotal = res.data.total;
                    }
                })
            },
            getPostInfo(post_id) {
                let params = {
                    post_id: post_id,
                    field_id: this.field_id,
                }
                getPostInfo(params).then(res => {
                    if (res.data.status == "success") {
                        this.$refs.refNewPost.showPost(res.data.result, post_id? null : this.field_id)
                        this.new_post_modal = true
                    }
                })
            },
            changePage(page) {
                this.pagePage = page
                this.pageStart = page * this.pageLimit
                this.getPostInfoList()
            },
            tableAdd() {
                this.getPostInfo(null)
            },
            tableChange(rows) {
                this.getPostInfo(rows[0].id);
            },
            tableDelete(rows) {
                let ids = []
                for (let i = 0; i < rows.length; i++) {
                    ids.push(rows[i].id)
                }
                let params = {
                    post_ids: ids
                };
                let that = this;
                this.$Modal.confirm({
                    title: '削除',
                    content: '削除しますか？',
                    okText: "はい",
                    cancelText: "いいえ",
                    onOk: () => {
                        deletePosts(params).then(res => {
                            if (res.data.status == 'success') {
                                this.$Message.success('正常に削除されました');
                                this.getPostInfoList()
                            } else {
                                this.$Message.error('削除に失敗しました');
                            }
                        })
                    },
                    onCancel: () => {
                    },
                });
            },
            showPostList(field_info) {
                this.field_info = field_info
                this.field_id = field_info.id
                this.s_time = field_info.s_time
                this.e_time = field_info.e_time
                this.pagePage = 1
                this.pageStart = 0
                this.pageLimit = 10
                this.getPostInfoList()
            }
        },
        mounted() {
        },
        computed: {
            ...mapGetters(["screenHeight"]),
            Height() {
                return parseInt(this.screenHeight - 300)
            }
        }
    }
</script>

<style scoped>

</style>
