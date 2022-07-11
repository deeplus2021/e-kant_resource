<template>
    <div class="div-login">
        <span class="title-login">e_KANT</span>
        <vLogo class="title-logo"></vLogo>
        <Form :model="formItem" class="login-form-layout">
            <FormItem>
                <p class="welcome-p"></p>
                <p class="welcome-e">Login please</p>
            </FormItem>
            <FormItem>
                <Input class="input-width" size="large" v-model="formItem.email" prefix="ios-contact"
                       placeholder="email"
                       @on-enter="toPassword"/>
            </FormItem>
            <FormItem>
                <Input class="input-width" size="large" ref="inputPassword" type="password" v-model="formItem.password"
                       prefix="ios-lock"
                       placeholder="password" @on-enter="handleSubmit"/>
            </FormItem>
            <FormItem>
                <Button type="primary" size="large" class="input-width" :loading="loading" @click="handleSubmit">ログイン
                </Button>
            </FormItem>
        </Form>
        <p class="bottom-span">Copyright©2020 e_KANT All rights reserved.</p>
    </div>
</template>
<script>
    import {
        setSupport,
        getSupport,
        setCookie,
        getCookie
    } from '@/utils/support';
    import {
        login,
    } from '@/api/login'
    import {
        mapGetters
    } from 'vuex'

    export default {
        data() {
            return {
                formItem: {
                    email: '',
                    password: ''
                },
                loading: false,
            }
        },
        methods: {
            handleSubmit() {
                this.loading = true
                login(this.formItem.email, this.formItem.password).then(res => {
                    setTimeout(() => {
                        this.loading = false
                    }, 1000)
                    if (res.data.status == "success") {
                        var now = new Date();
                        var expiry = 7
                        setCookie('token', res.data.result.token, expiry)
                        this.$router.push({
                            path: '/'
                        })
                    } else {
                        this.$Message['warning']({
                            background: true,
                            content: res.data.message
                        });
                    }
                }).catch(error => {
                    this.loading = false
                })
            },
            toPassword() {
                this.$refs.inputPassword.focus()
            },
        },
        computed: {}
    }
</script>
<style scoped>
    .div-login {
        background-size: 100% 100%;
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        background-image: url(../../assets/images/login_bg.png);
        display: flex;
        justify-content: center;
        flex-direction: column;
    }

    .login-form-layout {
        /*    position: absolute;
    left: 0;
    right: 0;
    width: 360px;
    margin: 140px auto; */
        text-align: center;
    }

    .input-width {
        width: 310px;
    }

    .form-icon {
        font-size: 50px;
        color: #8C0776;
        border: #000000 1px solid;
        border-radius: 10px;
        cursor: pointer;
    }

    .nurse-icon {
        position: absolute;
        font-size: 40px;
        display: inline-block;
    }

    .title-login {
        position: absolute;
        width: 535px;
        height: 29px;
        font-size: 30px;
        font-family: PingFang SC;
        font-weight: bold;
        color: rgba(255, 255, 255, 1);
        line-height: 30px;
        left: 30px;
        top: 30px;
    }

    .title-logo {
        position: absolute;
        right: 30px;
        top: 22px;
    }

    .welcome-p {
        height: 68px;
        font-size: 72px;
        font-family: PingFang SC;
        font-weight: 500;
        color: rgba(255, 255, 255, 1);
        line-height: 42px;
    }

    .welcome-e {
        height: 35px;
        font-size: 36px;
        font-family: PingFang SC;
        font-weight: 400;
        color: rgba(255, 255, 255, 1);
        line-height: 42px;
        opacity: 0.5;
    }

    .font-left {
        text-align: left;
    }

    .bottom-span {
        height: 14px;
        font-size: 14px;
        font-family: PingFang SC;
        font-weight: 400;
        color: rgba(255, 255, 255, 1);
        line-height: 30px;
        position: absolute;
        left: 0;
        right: 0;
        bottom: 50px;
        text-align: center;
    }
</style>
