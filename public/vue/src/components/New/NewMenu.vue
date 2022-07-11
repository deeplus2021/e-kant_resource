<template>
    <div style="position: relative; height: 100%;">
        <div class="div-header">
            <span class="iconfont icon-xingzhuang2 div-fspan"></span>
            <span class="div-name">菜单信息</span>
        </div>
        <div class="div-body">
            <Card shadow>
                <Form ref="ref_menu_info" :model="menu_info" :rules="rule_menu_info" :label-width="140">
                    <Row :gutter="16">
                        <Col span="20">
                            <FormItem prop="name" label="菜单名称">
                                <Input v-model="menu_info.name"></Input>
                            </FormItem>
                        </Col>
                        <Col span="20">
                            <FormItem prop="url" label="菜单链接">
                                <Input v-model="menu_info.url"></Input>
                            </FormItem>
                        </Col>
                        <Col span="20">
                            <FormItem prop="code" label="页面编码">
                                <Input v-model="menu_info.code"></Input>
                            </FormItem>
                        </Col>
                        <Col span="20">
                            <FormItem prop="button_type" label="页面类型">
                                <RadioGroup v-model="menu_info.button_type">
                                    <Radio :label="0">菜单</Radio>
                                    <Radio :label="1">按钮</Radio>
                                    <Radio :label="2">树</Radio>
                                </RadioGroup>
                            </FormItem>
                        </Col>
                        <Col span="20">
                            <FormItem prop="order" label="排序号">
                                <InputNumber v-model="menu_info.order" :min="0" :max="100"></InputNumber>
                            </FormItem>
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
        /* background-image: url('../assets/images/new-patient.png'); */
        height: 100px;
        background-position: 23% 49%;
        width: 100%;
        border-radius: 16px 16px 0 0;
        color: #666;
    }

    .div-fspan {
        font-size: 25px;
        display: inline-block;
        line-height: 3;
        margin-left: 15px;
    }

    .div-name {
        display: inline-block;
        font-size: 25px;
        font-family: PingFang-SC-Medium;
        font-weight: 500;
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
        font-size: 15px;
    }

    .button1 {
        margin-right: 20px;
        background-color: #7551AF;
        border: 1px solid;
        color: #fff;
    }

    .button1:hover {
        background-color: #FFF;
        border: 1px solid;
        color: #7551AF;
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
    import {system_add_menu, system_update_menu} from '@/api/staffRole'

    export default {
        props: ['is_edit', 'menu_info'],
        data() {
            return {
                rule_menu_info: {
                    name: [
                        {required: true, message: '请输入菜单名称', trigger: 'change'},
                        {type: 'string', max: 32, message: '不超过32字', trigger: 'change'}
                    ],
                    url: [
                        {required: false, message: '请输入菜单链接', trigger: 'change'},
                        {type: 'string', max: 64, message: '不超过64字', trigger: 'change'}
                    ],
                    code: [
                        {required: false, message: '请输入页面编码', trigger: 'change'},
                        {type: 'string', max: 32, message: '不超过32字', trigger: 'change'}
                    ],
                    button_type: [
                        {required: false, message: '请选择页面类型', trigger: 'change', type: 'number'},
                    ],
                    order: []
                }
            }
        },
        methods: {
            okSubmit() {
                this.$refs['ref_menu_info'].validate((valid) => {
                    if (valid) {
                        let params = {
                            id: this.menu_info.id,
                            parent_id: this.menu_info.parent_id,
                            order: this.menu_info.order,
                            code: this.menu_info.code,
                            name: this.menu_info.name,
                            url: this.menu_info.url,
                            button_type: this.menu_info.button_type,
                        };
                        this.$Modal.confirm({
                            title: '保存',
                            content: '确定要保存吗？',
                            okText: "はい",
                            cancelText: "いいえ",
                            onOk: () => {
                                if (this.is_edit) {
                                    system_update_menu(params).then(res => {
                                        if (res.data.status == 'success') {
                                            this.$Message.success('保存成功');
                                            this.toCancel(true)
                                        } else {
                                            this.$Message.error('保存失败,请核对信息是否完整');
                                        }
                                    })
                                } else {
                                    system_add_menu(params).then(res => {
                                        if (res.data.status == 'success') {
                                            this.$Message.success('保存成功');
                                            this.toCancel(true)
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
            }
        }
    }
</script>

<style>
</style>
