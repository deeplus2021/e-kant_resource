<template>
    <div>
        <br/>
        <Row style="margin-left: 20px;">
            <Button type="success" @click="menuAdd" :disabled="add_button" :loading="add_loading">
                <Icon type="md-add"/>
                新建
            </Button>
            <Button type="info" @click="menuChange" :disabled="change_button" :loading="change_loading">
                <Icon type="ios-paper-outline"/>
                编辑
            </Button>
            <Button type="error" @click="menuDelete" :disabled="delete_button" :loading="delete_loading">
                <Icon type="md-close"/>
                删除
            </Button>
        </Row>
        <br>
        <Row :gutter="16" style="margin-left: 20px;">
            <Col span="20">
                <Card :style="{height:Height+'px',overflow:'scroll'}">
                    <Tree :data="menu" :render="renderContent" @on-select-change="onClickMenu"></Tree>
                </Card>
            </Col>
        </Row>
        <Modal v-model="edit_menu" :mask-closable="false" :closable="true" :footer-hide="true" width="600" :styles="{top: '70px'}">
            <NewMenu v-bind:is_edit="is_edit" v-bind:menu_info="menu_info" @toCancel="toCancel"></NewMenu>
        </Modal>
    </div>
</template>

<script>
    import {
        mapGetters,
        mapActions
    } from 'vuex'
    import NewMenu from '@/components/News/NewMenu.vue'
    import {system_get_editable_tree_menu, system_delete_menu} from '@/api/staffRole'

    export default {
        components: {
            NewMenu
        },
        data() {
            return {
                menu: [],
                edit_menu: false,
                is_edit: false,
                add_loading: false,
                change_loading: false,
                delete_loading: false,
                add_button: true,
                change_button: true,
                delete_button: true,
                loading: false,
                menu_info: {
                    id: null,
                    parent_id: null,
                    order: 1,
                    code: '',
                    name: '',
                    url: '',
                    button_type: 0
                },
                selected_menu: null
            }
        },
        methods: {
            menuAdd() {
                if (!this.selected_menu) {
                    return
                }

                this.menu_info = {
                    id: null,
                    parent_id: this.selected_menu.id,
                    order: 1,
                    code: '',
                    name: '',
                    url: '',
                    button_type: 0
                }
                this.is_edit = false
                this.edit_menu = true
            },
            menuChange() {
                if (!this.selected_menu) {
                    return
                }
                this.menu_info = {
                    id: this.selected_menu.id,
                    parent_id: this.selected_menu.parent_id,
                    order: this.selected_menu.order,
                    code: this.selected_menu.code,
                    name: this.selected_menu.name,
                    url: this.selected_menu.url,
                    button_type: this.selected_menu.button_type,
                }
                this.is_edit = true
                this.edit_menu = true
            },
            menuDelete() {
                if (!this.selected_menu) {
                    return
                }
                let params = {
                    id: this.selected_menu.id
                };
                let that = this
                this.$Modal.confirm({
                    title: '削除',
                    content: '削除しますか？',
                    okText: "はい",
                    cancelText: "いいえ",
                    onOk: () => {
                        system_delete_menu(params).then(res => {
                            if (res.data.status == 'success') {
                                this.$Message.success('正常に削除されました');
                                location.reload();
                            } else {
                                this.$Message.error('削除に失敗しました');
                            }
                        })
                    },
                    onCancel: () => {
                    },
                })
            },
            onClickMenu(node, item) {
                if (item.selected) {
                    this.selected_menu = item
                    this.add_button = false
                    this.change_button = false
                    this.delete_button = false
                } else {
                    this.selected_menu = null
                    this.add_button = true
                    this.change_button = true
                    this.delete_button = true
                }
            },
            getMenu() {
                this.loading = true
                system_get_editable_tree_menu().then(res => {
                    this.loading = false;
                    if (res.data.status == "success") {
                        this.menu = [{
                            id: 0,
                            expand: true,
                            name: "菜单树",
                            button_type: -1,
                            children: res.data.result
                        }]
                    }
                }).catch((error) => {this.loading = false})
            },
            renderContent(h, {root, node, data}) {
                let that = this
                if (data.children) {
                    return h('span', {
                            style: {
                                display: 'inline-block',
                                width: '100%'
                            }
                        },
                        [
                            h('span', [
                                h('Icon', {
                                    props: {
                                        type: 'ios-folder-outline'
                                    },
                                    style: {
                                        marginRight: '8px'
                                    },
                                    on: {
                                        click: () => {
                                            that.$set(data, 'expand', !data.expanded)
                                        }
                                    }
                                }),
                                h('span', {
                                    style: {
                                        cursor: 'pointer',
                                        fontSize: '15px'
                                    },
                                    on: {
                                        click: () => {
                                            //console.log(data)
                                        }
                                    }
                                }, data.name)
                            ])
                        ])
                } else {
                    return h('span', {
                            style: {
                                display: 'inline-block',
                                width: '100%'
                            }
                        },
                        [
                            h('span', [
                                h('Icon', {
                                    props: {
                                        type: 'ios-paper-outline'
                                    },
                                    style: {
                                        marginRight: '8px'
                                    }
                                }),
                                h('span', {
                                    style: {
                                        cursor: 'pointer',
                                        fontSize: '15px'
                                    },
                                    on: {
                                        click: () => {
                                            //console.log(data)
                                        }
                                    }
                                }, data.name)
                            ]),
                        ]
                    )
                }
            },
            toCancel(is_refesh) {
                if (is_refesh) {
                    location.reload();
                }
                this.edit_menu = false
            },
        },
        mounted() {
            this.getMenu()
        },
        computed: {
            ...mapGetters(["screenHeight"]),
            Height() {
                return parseInt(this.screenHeight - 200)
            }
        }
    }
</script>
