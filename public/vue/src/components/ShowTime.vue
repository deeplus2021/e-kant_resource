<template>
    <div>
        <p style="height: 20px;font-size: 25px;">{{datehis}}</p>
        <p>{{dateymd + week}}</p>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                dateymd: '',
                datehis: '',
                weeks: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
                week: new String()
            };
        },
        methods: {
            changetime(date) {
                this.dateymd = this.$utils.Datetimes.getymd(new Date(date))
                this.datehis = this.$utils.Datetimes.gethis(new Date(date))
                var weeks = date.getDay()
                this.week = ' ' + this.weeks[weeks]
            }
        },
        mounted() {
            let that = this; // 声明一个变量指向Vue实例this，保证作用域一致
            this.timer = setInterval(() => {
                that.changetime(new Date()); // 修改数据date
            }, 1000)
        },
        beforeDestroy() {
            if (this.timer) {
                clearInterval(this.timer); // 在Vue实例销毁前，清除我们的定时器
            }
        }
    };
</script>
