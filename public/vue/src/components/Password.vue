<template>
    <div>
        <Card>
            <h3 slot="title">パスワードの変更</h3>
            <Form :model="formItem" ref="refFormItem" :rules="rule_form" :label-width="150" label-position="right">
                <FormItem label="以前のパスワード" prop="old_password">
                    <Input class="input-width" size="large" type="password" v-model="formItem.old_password" prefix="ios-lock"
                           placeholder="以前のパスワード"/>
                </FormItem>
                <FormItem label="新しいパスワード" prop="new_password">
                    <Input class="input-width" size="large" type="password" v-model="formItem.new_password"
                           prefix="ios-lock" placeholder="新しいパスワード"/>
                </FormItem>
                <FormItem label="パスワードを認証" prop="c_password">
                    <Input class="input-width" size="large" type="password" v-model="formItem.c_password"
                           prefix="ios-lock" placeholder="パスワードを認証"/>
                </FormItem>
                <FormItem>
                    <Button type="primary" size="large" class="input-width" :loading="loading" @click="handleSubmit">変更</Button>
                </FormItem>
            </Form>
        </Card>
    </div>
</template>

<script>
    import {
        changePassword
    } from '@/api/login'
    import {
        setCookie,
    } from '@/utils/support';
    export default {
        name: "Password",
        data(){
            return{
                formItem:{
                    old_password:'',
                    new_password:'',
                    c_password:'',
                },
                rule_form:{
                    old_password:[
                        {required: true, message: '入力してください', trigger: 'change'},
                        {type: 'string', max: 64, message: '', trigger: 'change'}
                    ],
                    new_password: [
                        {required: true, message: '入力してください', trigger: 'change'},
                        {type: 'string', max: 64, message: '', trigger: 'change'}
                    ],
                    c_password: [
                        {required: true, message: '入力してください', trigger: 'change'},
                        {type: 'string', max: 64, message: '', trigger: 'change'}
                    ],
                },
                loading: false
            }
        },
        methods:{
            handleSubmit(){
                this.$refs['refFormItem'].validate((valid) => {
                    if (valid) {
                        this.loading = true;
                        changePassword(this.formItem).then(res => {
                            this.loading = false;
                            if (res.data.status == "success") {
                                var expiry = 7
                                setCookie('token', res.data.result.token, expiry)
                                this.$Message.success('success');
                                this.$emit("toCancel")
                            }
                        }).catch((error) => {this.loading = false})
                    }
                })
            }
        }
    }
</script>

<style scoped>
    .input-width {
        width: 310px;
    }
</style>