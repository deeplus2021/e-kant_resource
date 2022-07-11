<template>
    <div id="laravel-echo">
        <Dropdown>
            <a class="exit-span" href="javascript:void(0)">
                <!--<Icon type="md-notifications" style="line-height: 60px;"/>-->
                <!--<span style="color:#f66;font-size: 14px;font-weight: bold;">{{staff_info.unread_notifications.length}}</span>-->
            </a>
            <DropdownMenu slot="list">
                <DropdownItem v-for="(object, index) in staff_info.unread_notifications" :name="'nf'+ index" :key="index">{{object.data}}</DropdownItem>
            </DropdownMenu>
        </Dropdown>
        <Modal v-model="attendance_modal" :mask-closable="false" :closable="true" :footer-hide="true" width="1400" :styles="{top: '70px'}">
            <AttendanceList ref="refAttendanceList"></AttendanceList>
        </Modal>
    </div>
</template>

<script>
    import {getToken} from '@/utils/auth'
    import Echo from 'laravel-echo'
    window.Pusher = require('pusher-js');
    import AttendanceList from '@/components/New/AttendanceList.vue'

    export default {
        name: "Notifications",
        components: {
            AttendanceList
        },
        data() {
            return {
                echo: null,
                attendance_modal: false
            }
        },
        watch: {
            echo(){
                this.echo.private('App.Models.Staff.' + this.staff_info.id)
                    .notification((notification) => {
                        console.log(notification)
                        this.viewNotice(notification)
                    });
            }
        },
        computed: {
            staff_info: {
                cache: false,
                get(){ return this.$store.getters.staff_info }
            },
        },
        methods: {
            viewNotice(notification){
                let _this = this
                this.$Notice.info({
                    name: notification.id,
                    title: 'お知らせ: ' + notification.data.type,
                    desc:  "名 前:" + notification.data.sender + " => "  + notification.data.content,
                    render: h => {
                        return h('div', [
                            h('span', "名 前:" + notification.data.sender + " => "  + notification.data.content + "  "),
                            h('Button', {
                                props: {
                                    type: 'info',
                                    size: 'small'
                                },
                                on: {
                                    click: () => {
                                        _this.$refs.refAttendanceList.getAttendanceInfo(notification.data.shift_ids)
                                        _this.attendance_modal = true
                                        _this.$Notice.close(notification.id)
                                    }
                                }
                            }, '詳 細')
                        ])
                    },
                    duration: 0
                });
            }
        },
        beforeDestroy() {
        },
        mounted() {
            window.Pusher = require('pusher-js');
            this.echo = new Echo({
                broadcaster: 'pusher',
                key: '3dc291de66997e2570c0',
                cluster: 'ap3',
                encrypted: true,
                authEndpoint: '/api/v1/broadcasting-auth',
                auth: {
                    headers: {
                        'Authorization': 'Bearer ' + getToken(),
                    }
                }
            });
        },
        created() {
        }
    }
</script>

<style>
    .ivu-notice {
        z-index: 900!important;
    }
</style>