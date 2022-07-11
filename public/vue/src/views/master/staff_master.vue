<template>
    <div>
        <br/>
        <Row>
            <Col span="12" style="padding-left: 20px;">
                <p style="display: inline-block;margin-left: 10px;">名 前</p>
                <Input v-model="search_box.name" placeholder="名前" style="display: inline-block;width: 130px;" :maxlength="32" @on-enter="search"></Input>
                <Button type="success" style="display: inline-block;width:100px;margin-left: 10px;" long @click="search">
                    検　索
                </Button>
            </Col>
        </Row>
        <br/>
        <vTablePackage :loading="loading" :tableColumns="tableColumns" :tableData="tableData" :tableHeight="Height"
                       :pageTotal="pageTotal" :pagePage="pagePage" :pageSize="pageLimit" @changepage="changePage"
                       :enableAdd="roles === 1 || roles === 2" :enableDelete="roles === 1 || roles === 2"
                       @tableAdd="tableAdd" @tableChange="tableChange" @tableDelete="tableDelete" border></vTablePackage>
        <Row v-if="roles === 1 || roles === 2">
            <Col span="6" style="padding-left: 20px;">
                <Button type="info" @click="exportStaffs">CSV出力</Button>
            </Col>
            <Col span="18">
                <Upload style="display: inline-block;"
                        :before-upload="handleUpload"
                        :format="['csv']"
                        :max-size="10240"
                        :on-format-error="handleFormatError"
                        :on-exceeded-size="handleMaxSize"
                        action="#"
                >
                    <Button type="primary" long style="display: inline-block;margin-left: 10px;"><Icon type="md-cloud-upload" />スタッフ情報取り込み</Button>
                </Upload>
                <div v-if="file !== null" style="display: inline-block;margin-left: 10px;"> {{ file.name }} <Button type="text" @click="excelUpload" :loading="loadingStatus">{{ loadingStatus ? 'アップロード。。。' : 'アップロード' }}</Button></div>
            </Col>
        </Row>
        <Modal v-model="new_staff_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="1250" :styles="{top: '70px'}">
            <NewStaff ref="refNewStaff" @toCancel="toCancel"></NewStaff>
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
    import NewStaff from '@/components/New/NewStaff.vue'
    import {
        getStaffInfoList,
        getStaffInfo,
        deleteStaffs,
        exportStaffs,
        importStaffs
    } from '@/api/staff_master'

    export default {
        components: {
            NewStaff
        },
        data() {
            return {
                tableColumns: [{
                    type: 'selection',
                    width: 60,
                    align: 'center'
                },
                    {
                        title: '名　前',
                        key: 'name'
                    },
                    {
                        title: '社員コード',
                        key: 'code'
                    },
                    {
                        title: '電話番号',
                        key: 'tel'
                    },
                    {
                        title: 'メールアドレス',
                        key: 'email'
                    },
                    {
                        title: '出発地登録',
                        key: 'address',
                        render: (h, params) => {
                            if(params.row.address_count > 0){
                               return h('span', '済')
                            }
                            else{
                                return h('span', {
                                    style: {
                                        color: "#ff0000"
                                    },
                                }, '未登録')
                            }
                        }
                    },
                    {
                        title: '権限',
                        key: 'role_name'
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
                                }, '編集'), (this.roles === 1 || this.roles === 2) ?
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
                                }, '削除') : ''
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
                new_staff_modal: false,
                file: null,
                loadingStatus: false
            }
        },
        methods: {
            search() {
                this.pagePage = 1
                this.pageStart = 0
                this.getStaffInfoList();
            },
            toCancel(is_refesh) {
                if (is_refesh) {
                    this.getStaffInfoList()
                }
                this.new_staff_modal = false
            },
            getStaffInfoList() {
                this.loading = true
                let params = {
                    name: this.search_box.name,
                    page: this.pagePage,
                    start: this.pageStart,
                    limit: this.pageLimit
                }
                getStaffInfoList(params).then(res => {
                    this.loading = false;
                    if (res.data.status == "success") {
                        this.tableData = res.data.result;
                        this.pageTotal = res.data.total;
                    }
                }).catch((error) => {this.loading = false;})
            },
            getStaffInfo(staff_id) {
                let params = {
                    staff_id: staff_id,
                }
                getStaffInfo(params).then(res => {
                    if (res.data.status == "success") {
                        this.$refs.refNewStaff.showStaff(res.data.result)
                        this.new_staff_modal = true
                    }
                })
            },
            changePage(page) {
                this.pagePage = page
                this.pageStart = page * this.pageLimit
                this.getStaffInfoList()
            },
            tableAdd() {
                this.$refs.refNewStaff.showStaff()
                this.new_staff_modal = true
            },
            tableChange(rows) {
                this.getStaffInfo(rows[0].id);
            },
            tableDelete(rows) {
                let ids = []
                for (let i = 0; i < rows.length; i++) {
                    ids.push(rows[i].id)
                }
                let params = {
                    staff_ids: ids
                };
                let that = this;
                this.$Modal.confirm({
                    title: '削除',
                    content: '削除してもよろしいですか？',
                    okText: "はい",
                    cancelText: "いいえ",
                    onOk: () => {
                        deleteStaffs(params).then(res => {
                            if (res.data.status == 'success') {
                                this.$Message.success('正常に削除されました');
                                this.getStaffInfoList()
                            } else {
                                this.$Message.error('削除に失敗しました');
                            }
                        })
                    },
                    onCancel: () => {
                    },
                });
            },
            exportStaffs(){
                let params = {
                    name: this.search_box.name,
                }
                exportStaffs(params).then((response)=>{
                    let fileURL = window.URL.createObjectURL(new Blob([response.data]));
                    let fileLink = document.createElement('a');
                    fileLink.href = fileURL;
                    fileLink.setAttribute('download', 'スタッフ.csv');
                    document.body.appendChild(fileLink);
                    fileLink.click();
                })
            },
            handleUpload(file){
                this.file = file;
                return false
            },
            handleFormatError (file) {
                this.$Notice.warning({
                    title: 'The file format is incorrect',
                    desc: 'File format of ' + file.name + ' is incorrect, please select jpg or png.'
                });
            },
            handleMaxSize (file) {
                this.$Notice.warning({
                    title: 'Exceeding file size limit',
                    desc: 'File  ' + file.name + ' is too large, no more than 10M.'
                });
            },
            excelUpload(){
                if(this.file == null) {
                    return;
                }
                this.loadingStatus = true
                let data = new FormData()
                data.append('staff_file', this.file)
                importStaffs(data).then((res) => {
                    this.loadingStatus = false
                    if(res.data.status == 'success'){
                        this.file = null
                        this.getStaffInfoList()
                        this.$Message.success("success")
                    }
                    else{
                        this.file = null
                        this.$Notice.error({
                            title: 'Excel Import Error',
                            desc: res.data.message,
                            duration: 0
                        })
                    }
                }).catch((res) => {
                    this.loadingStatus = false
                    this.$Notice.error({
                        title: 'Excel Import Error',
                        desc: res.toString(),
                        duration: 0
                    })
                })
            }
        },
        mounted() {
            this.getStaffInfoList()
        },
        computed: {
            ...mapGetters([
                "roles","screenHeight"
            ]),
            Height() {
                return parseInt(this.screenHeight - 300)
            }
        }
    }
</script>
