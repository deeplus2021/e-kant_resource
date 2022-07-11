<template>
    <div style="position: relative; height: 100%;">
        <div class="div-header">
            <span class="iconfont icon-xingzhuang2 div-fspan"></span>
            <span class="div-name">角色信息</span>
            <span class="div-t">*</span>
            <span class="div-i">为必填项</span>
        </div>
        <div class="div-body">
            <Card shadow>
                <Form ref="ref_role_info" :model="role_info" :rules="rule_role_info" :label-width="140">
                    <Row :gutter="12">
                        <Col span="12">
                            <Row :gutter="16">
                                <FormItem prop="name" label="角色名称">
                                    <Input v-model="role_info.name"/>
                                </FormItem>
                            </Row>
                            <Row :gutter="16">
                                <FormItem prop="desc" label="角色描述">
                                    <Input v-model="role_info.desc" type="textarea" :rows="3"/>
                                </FormItem>
                            </Row>
                            <Row :gutter="16">
                                <FormItem prop="is_active" label="是否有效">
                                    <RadioGroup v-model="role_info.is_active">
                                        <Radio :label='1'>是</Radio>
                                        <Radio :label='0'>否</Radio>
                                    </RadioGroup>
                                </FormItem>
                            </Row>
                        </Col>
                        <Col span="11" :offset="1">
                            <Tree ref="ref_role_tree" :data="role_tree"
                                  :show-checkbox="role_info.is_super_admin != 1"></Tree>
                        </Col>
                    </Row>
                </Form>
            </Card>
            <div class="div-foot">
                <div class="div-foot-right">
                    <Button shape="circle" class="div-button button1 button4" @click="okSubmit">保存</Button>
                    <Button shape="circle" class="div-button button1" @click="toCancel(false)">取消</Button>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
    label {
        font-weight: 100;
    }

    .div-body {
        background-color: #F2F2F2;
        border-radius: 16px;
        font-size: 18px;
    }

    .vueMarginLeftTwo p {
        margin-top: 2px;
        margin-bottom: 5px;
        font-size: 18px;
    }

    .div-foot {
        position: relative;
        padding: 16px;
        height: 75px;
    }

    .div-foot-right {
        width: 300px;
        margin: 0 auto;
    }

    .div-button {
        width: 125px;
        font-size: 18px;
    }

    .button1 {
        margin-right: 20px;
        background-color: #7551AF;
        border: 1px solid;
        color: #fff;
    }

    .button1:hover {
        text-align: center;
        background-color: #FFF;
        border: 1px solid;
        color: #7551AF;
    }

    .button4 {
        /*position: fixed;*/
        right: 50px;
        bottom: 50px;
    }
</style>
<style scoped>
    .vueMarginLeftTwo p {
        margin-top: 2px;
        margin-bottom: 5px;
        font-size: 14px;
    }

    .vueMarginLeftTwo {
        margin-left: 5%;
    }

    .div-body {
        background-color: #F2F2F2;
        border-radius: 16px;
    }

    .div-header {
        position: relative;
        height: 70px;
        background-position: 23% 49%;
        width: 100%;
    }

    .div-fspan {
        font-size: 25px;
        display: inline-block;
        line-height: 3;
        margin-left: 15px;
    }

    .div-t {
        font-size: 20px;
        display: inline-block;
        margin-left: 30px;
        color: red;
    }

    .div-i {
        font-size: 16px;
        display: inline-block;
        margin-left: 20px;
        position: absolute;
        top: 22px;
    }

    .div-name {
        display: inline-block;
        font-size: 20px;
    }

    .div-form {
        padding: 0 0 16px 100px;

    }

    .div-button {
        width: 125px;
        font-size: 15px;
    }

    .layout-foot span {
        color: #fff;
        font-size: 18px;
        display: block;
        width: 150px;
        margin-top: 10%;
    }

    .leftselect span {
        color: #7D52B3;
        line-height: 34px;
    }

    .contentrow span {
        font-size: 16px;
        color: #524B9A;
    }

    .contentrow div:nth-child(1) {
        display: inline-block;
        height: 35px;
        width: 140px;
        background-color: #fff;
        border-radius: 20px;
        line-height: 33px;
        position: absolute;
        right: 170px;
        cursor: pointer;
        border: 1px solid #d7dde4;
    }

    .contentrow div:nth-child(1) i {
        font-size: 33px;
        color: rgba(83, 86, 165, 1);
    }

    .contentrow div span {
        font-size: 15px;
        font-family: PingFang-SC-Bold;
        font-weight: bold;
        color: rgba(83, 86, 165, 1);
    }

    .contentrow div:hover {
        border-color: #5cadff;
    }

    .contentrow div:nth-child(2) {
        display: inline-block;
        height: 35px;
        width: 130px;
        background-color: #fff;
        border-radius: 20px;
        line-height: 33px;
        position: absolute;
        right: 20px;
        cursor: pointer;
        border: 1px solid #d7dde4;
    }
</style>
<script>
    import {mapGetters, mapActions} from 'vuex'
    import {system_role_update_staff_role, system_role_add_staff_role} from '@/api/staffRole'

    export default {
        props: ['is_edit', 'role_info'],
        data() {
            return {
                rule_role_info: {
                    name: [
                        {required: true, message: '请输入角色名称', trigger: 'change'},
                        {type: 'string', max: 64, message: '不超过64字', trigger: 'change'}
                    ],
                    desc: [
                        {required: null, message: '请输入角色描述', trigger: 'change'},
                        {type: 'string', max: 255, message: '不超过255字', trigger: 'change'}
                    ],
                    is_active: [
                        {required: true, message: '请选择是否锁定', trigger: 'change', type: 'number'},
                    ],
                },
            }
        },
        methods: {
            getCheckedNode(Nodes) {
                let ids = []
                if (!!Nodes && Nodes.length !== 0) {
                    let has_checked = false
                    Nodes.forEach(item => {
                        if (item.checked) {
                            ids.push(item.id);
                            has_checked = true
                        }
                        let child_ids = this.getCheckedNode(item.children)
                        if (child_ids.length > 0) {
                            has_checked = true
                            for (let i = 0; i < child_ids.length; i++) {
                                ids.push(child_ids[i]);
                            }
                        }
                    });
                    if (has_checked && Nodes[0].parent_id > 0 && !ids.includes(Nodes[0].parent_id)) {
                        ids.push(Nodes[0].parent_id);
                    }
                }
                return ids;
            },
            okSubmit() {
                this.$refs['ref_role_info'].validate((valid) => {
                    let ids = this.getCheckedNode(this.$refs['ref_role_tree'].data)
                    let page_menu_ids = []
                    for (let i = 0; i < ids.length; i++) {
                        if (!page_menu_ids.includes(ids[i])) {
                            page_menu_ids.push(ids[i]);
                        }
                    }
                    if (valid) {
                        let params = {
                            id: this.role_info.id,
                            name: this.role_info.name,
                            desc: this.role_info.desc,
                            page_menu_ids: page_menu_ids,
                            is_active: this.role_info.is_active,
                        };
                        this.$Modal.confirm({
                            title: '保存',
                            content: '确定要保存吗？',
                            okText: "はい",
                            cancelText: "いいえ",
                            onOk: () => {
                                if (this.is_edit) {
                                    system_role_update_staff_role(params).then(res => {
                                        if (res.data.status == 'success') {
                                            this.$Message.success('保存成功');
                                            this.toCancel(true);
                                        } else {
                                            this.$Message.error('保存失败,请核对信息是否完整');
                                        }
                                    })
                                } else {
                                    system_role_add_staff_role(params).then(res => {
                                        if (res.data.status == 'success') {
                                            this.$Message.success('保存成功');
                                            this.toCancel(true);
                                        } else {
                                            this.$Message.error('保存失败,请核对信息是否完整');
                                        }
                                    })
                                }
                            },
                            onCancel: () => {
                            },
                        });
                    }
                });
            },
            toCancel(is_refresh) {
                this.$emit('toCancel', is_refresh);
            },

            getTreeData(permissions) {
                return this.makeTreeData(permissions, Object.assign([], this.staff_role_tree));
            }
        },
        mounted() {

        },
        created() {

        },
        computed: {
            ...mapGetters(["staff_role_tree"]),
            role_tree: function () {
                let makeTreeData = function (permissions, data) {
                    let arr = [];
                    if (!!data && data.length !== 0) {
                        data.forEach(item => {
                            let obj = {};
                            obj.title = item.name;
                            obj.id = item.id; // 其他你想要添加的属性
                            obj.parent_id = item.parent_id; // 其他你想要添加的属性
                            obj.name = item.name; // 其他你想要添加的属性
                            obj.expand = false;
                            obj.checked = permissions.includes(item.id) && !item.children.length;
                            obj.children = makeTreeData(permissions, item.children); // 递归调用
                            arr.push(obj);
                        });
                    }
                    return arr;
                }
                return makeTreeData(this.role_info.permissions, this.staff_role_tree)
            }
        }
    }
</script>

<style>
</style>
