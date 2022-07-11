<template>
    <ul class="title">
        <li>
            <div class="li_div">
                <span style="font-size: 30px;">{{his}}</span>
                <div class="li_div2">
                    <p>{{week}}</p>
                    <p>{{ymd}}</p>
                </div>
            </div>
        </li>
        <li>
            <div class="li_div">
                <Icon type="md-contact" style="line-height: 60px;"/>
                <Dropdown  @on-click="viewChangePassword">
                    <a class="exit-span" href="javascript:void(0)">
                        {{name}}
                        <Icon type="ios-arrow-down"></Icon>
                    </a>
                    <DropdownMenu slot="list">
                        <DropdownItem name="setting"><Icon type="md-settings" />ユーザー設定</DropdownItem>
                        <DropdownItem name="password"><Icon type="ios-settings-outline"/>パスワードの変更</DropdownItem>
                    </DropdownMenu>
                </Dropdown>
            </div>
        </li>
        <li>
            <vNotifications ref="broadcasting"></vNotifications>
        </li>
        <!--<li>
            <vFirebaseNotify></vFirebaseNotify>
        </li>-->
        <li>
            <div class="li_div">
                <a href='javascript:;' v-if="!showFullScreen" @click="fullScreen">
                    <svg-icon class="svg-i" style="line-height: 60px;" icon-class="t1"></svg-icon>
                </a>
                <a href='javascript:;' v-else @click="exitFullscreen">
                    <svg-icon class="svg-i" style="line-height: 60px;" icon-class="t1"></svg-icon>
                </a>
                <!-- <svg-icon class="svg-i pointer" style="line-height: 60px;margin-left: 20px;" icon-class="t2"></svg-icon> -->
            </div>
        </li>
        <li>
            <div class="li_div pointer" @click="logout">
                <Icon type="md-power" style="line-height: 60px;"/>
                <span class="exit-span">ログアウト</span>
            </div>
        </li>
        <Modal v-model="change_password_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="600" :styles="{top: '70px'}">
            <Password ref="refPassword" @toCancel="toCancel"></Password>
        </Modal>
        <Modal v-model="change_staff_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="1250" :styles="{top: '70px'}">
            <NewStaff ref="refNewStaff" @toCancel="closeSetting"></NewStaff>
        </Modal>
    </ul>
</template>

<script>
    import {mapGetters, mapMutations, mapActions} from 'vuex'
    import Password from '@/components/Password'
    import NewStaff from '@/components/New/NewStaff'
    import {getInfo} from '@/api/login'
    export default {
        components:{Password, NewStaff},
        data() {
            return {
                weeks: ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'],
                week: '',
                his: '',
                ymd: '',
                change_password_modal: false,
                change_staff_modal: false
            }
        },
        methods: {
            ...mapActions(["setStaffRoleList"]),
            ...mapMutations(["setShowFullScreen"]),
            logout() {
                this.$store.dispatch('FedLogOut').then(() => {
                    location.reload() // 为了重新实例化vue-router对象 避免bug
                })
            },
            //全屏
            fullScreen() {
                var element = document.documentElement;
                if (element.requestFullscreen) {
                    element.requestFullscreen();
                } else if (element.msRequestFullscreen) {
                    element.msRequestFullscreen();
                } else if (element.mozRequestFullScreen) {
                    element.mozRequestFullScreen();
                } else if (element.webkitRequestFullscreen) {
                    element.webkitRequestFullscreen();
                }
                this.setShowFullScreen(true)
            },
            //退出全屏
            exitFullscreen() {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
                this.setShowFullScreen(false)
            },
            changetime(date) {
                this.ymd = this.$utils.Datetimes.getymd_locale(date)
                this.his = this.$utils.Datetimes.gethis(date)
                var weeks = date.getDay()
                this.week = ' ' + this.weeks[weeks]
            },
            viewChangePassword(v){
                if(v === "password"){
                    this.change_password_modal = true
                }
                else if(v ==="setting"){
                    getInfo().then(res => {
                        this.$refs.refNewStaff.showStaff(res.data.result, false)
                        this.change_staff_modal = true
                    })
                }
            },
            toCancel(){
                this.change_password_modal = false
            },
            closeSetting(){
                this.change_staff_modal = false
            }
        },
        mounted() {
            this.setStaffRoleList()
            let that = this; // 声明一个变量指向Vue实例this，保证作用域一致
            this.timer = setInterval(() => {
                that.changetime(new Date()); // 修改数据date
            }, 1000)
        },
        beforeDestroy() {
            if (this.timer) {
                clearInterval(this.timer); // 在Vue实例销毁前，清除我们的定时器
            }
        },
        computed: {
            ...mapGetters([
                'showFullScreen',
                'name'
            ])
        }
    }
</script>

<style scoped>
    .title {
        display: inline-block;
        list-style: none;
        position: relative;
        font-size: 20px;
        color: #FFFFFF;
        padding: 0 !important;
    }

    .title:focus {
        outline: unset;
    }

    .title li {
        float: left;
        border-left: 1px solid rgba(255, 255, 255, .1);
    }

    .title li:nth-child(1) {
        float: left;
        border-left: none;
    }

    .li_div {
        display: flex;
        flex-direction: row;
        justify-content: center;
        padding-left: 20px;
        padding-right: 20px;
    }

    .li_div2 {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        margin-left: 5px;
    }

    .li_div2 p {
        font-size: 12px;
        height: 12px;
        line-height: 4;
    }


    .svg-i {
        width: 20px !important;
        height: 20px !important;
        margin-top: 20px;
    }

    .exit-span {
        font-size: 14px;
        margin-left: 10px;
        font-family: PingFang SC;
        font-weight: 400;
    }
</style>
