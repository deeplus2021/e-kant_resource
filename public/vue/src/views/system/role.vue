<template>
    <div>
        <br/>
        <vTablePackage :loading="loading" :tableColumns="tableColumns" :tableData="tableData" :tableHeight="Height"
                       :pageTotal="pageTotal" :pagePage="pagePage" :pageSize="pageLimit" @changepage="changePage" @tableAdd="tableAdd"
                       @tableChange="tableChange" @tableDelete="tableDelete" border></vTablePackage>
        <Modal v-model="new_role" :mask-closable="false" :closable="true" :footer-hide="true" width="1000" :styles="{top: '70px'}">
            <NewRole v-bind:is_edit="is_edit" v-bind:role_info="role_info" @toCancel="toCancel"></NewRole>
        </Modal>
    </div>
</template>

<script>
    import {
        mapGetters,
        mapActions
    } from 'vuex'
    import NewRole from '@/components/News/NewRole'
    import {
        system_role_get_staff_role_list,
        system_role_get_staff_role,
        system_role_delete_staff_roles
    } from '@/api/staffRole'

    export default {
        components: {
            NewRole,
        },
        data() {
            return {
                tableColumns: [{
                    type: 'selection',
                    width: 60,
                    align: 'center'
                },
                    {
                        title: '角色名称',
                        key: 'name'
                    },
                    {
                        title: '角色描述',
                        key: 'desc'
                    },
                    {
                        title: '是否超级管理员',
                        key: 'is_super_admin',
                        render: (h, params) => {
                            let value = params.row.is_super_admin;
                            return h("span", {}, value == 1 ? "是" : "否")
                        }
                    },
                    {
                        title: '是否有效',
                        key: 'is_active',
                        render: (h, params) => {
                            let value = params.row.is_active;
                            return h("span", {}, value == 1 ? "是" : "否")
                        }
                    },
                ],
                tableData: [],
                pageTotal: 1,
                pagePage: 1,
                pageStart: 0,
                pageLimit: 15,
                loading: false,
                new_role: false,
                is_edit: false,
                role_info: {
                    id: null,
                    name: '',
                    desc: '',
                    permissions: [],
                    is_active: 1,
                    is_super_admin: 0,
                }
            }
        },
        methods: {
            ...mapActions(["setStaffRoleTree"]),
            search() {
                this.getRoleList();
            },
            toCancel(is_refesh) {
                if (is_refesh) {
                    this.getRoleList()
                }
                this.new_role = false
            },
            getRoleList() {
                this.loading = true
                var params = {
                    page: this.pagePage,
                    start: this.pageStart,
                    limit: this.pageLimit
                }
                system_role_get_staff_role_list(params).then(res => {
                    this.loading = false;
                    if (res.data.status == "success") {
                        this.tableData = res.data.result;
                        this.pageTotal = res.data.total;
                    }
                })
            },
            changePage(page) {
                this.pagePage = page
                this.pageStart = page * this.pageLimit
                this.getRoleList()
            },
            tableAdd() {
                this.is_edit = false
                this.role_info = {
                    id: null,
                    name: '',
                    desc: '',
                    permissions: [],
                    is_active: 1,
                    is_super_admin: 0,
                }

                this.new_role = true
            },
            tableChange(rows) {
                var params = {
                    id: rows[0].id,
                }
                system_role_get_staff_role(params).then(res => {
                    if (res.data.status == "success") {
                        let row = res.data.result;
                        this.is_edit = true
                        this.role_info = {
                            id: row.id,
                            name: row.name,
                            desc: row.desc,
                            permissions: row.permissions,
                            is_active: row.is_active,
                            is_super_admin: row.is_super_admin,
                        }
                        this.new_role = true
                    }
                })
            },
            tableDelete(rows) {
                let ids = []
                for (let i = 0; i < rows.length; i++) {
                    ids.push(rows[i].id)
                }
                let params = {
                    ids: ids
                };
                this.$Modal.confirm({
                    title: '削除',
                    content: '削除しますか？',
                    okText: "はい",
                    cancelText: "いいえ",
                    onOk: () => {
                        system_role_delete_staff_roles(params).then(res => {
                            if (res.data.status == 'success') {
                                this.$Message.success('正常に削除されました');
                                this.getRoleList()
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
            this.setStaffRoleTree();
            this.getRoleList()
        },
        computed: {
            ...mapGetters(["screenHeight"]),
            ...mapGetters(["staff_role_tree"]),
            Height() {
                return parseInt(this.screenHeight - 300)
            }
        }
    }
</script>
