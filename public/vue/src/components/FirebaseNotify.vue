<template>
    <div>

    </div>
</template>

<script>
    import {
        updateDeviceToken
    } from '@/api/staff_master'
    export default {
        name: "FirebaseNotify",
        data(){
            return {

            }
        },
        methods: {
            sendTokenToServer(currentToken){
                let params = {
                    "fcm_token": currentToken
                }
                updateDeviceToken(params).then(res => {
                    if (res.data.status == 'success') {

                    }
                })
            },
            viewMessage(payload){
                //TODO
            }
        },
        mounted() {
            // Retrieve Firebase Messaging object.
            const messaging = firebase.messaging();
            // Add the public key generated from the console here.
            messaging.usePublicVapidKey("BB4R2bG52GY5CLeinuN5PtF5MLEDlEmy6337Dy7EiTnAq6k75hre-1E1OR0BddEsCq_tmbN9FUrgHfSa4BcllXM");

            messaging.getToken().then((currentToken) => {
                if (currentToken) {
                    this.sendTokenToServer(currentToken);
                } else {
                    // Show permission request.
                    console.log('No Instance ID token available. Request permission to generate one.');
                }
            }).catch((err) => {
                console.log('An error occurred while retrieving token. ', err);
            });

            messaging.onMessage((payload) => {
                console.log('Message received. ', payload);
                this.viewMessage(payload)
            });
        }
    }
</script>

<style scoped>

</style>