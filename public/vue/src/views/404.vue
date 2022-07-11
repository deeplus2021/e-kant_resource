<template>
    <div>
        <div class="app-container">

        </div>
    </div>
</template>

<script>
    export default {
        name: 'wrongPage',
        data() {
            return {
            }
        },
        methods: {
            handleGoMain() {
                this.$router.push({path: '/'})
            }
        },
    }
</script>

<style scoped>
    .app-container {
        width: 80%;
        margin: 120px auto;
    }

    .img-style {
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: 100%;
    }
</style>
