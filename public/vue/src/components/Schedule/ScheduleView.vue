<template>
    <div class="s-layout" :style="{'height': height + 'px'}">
        <div class="s-content">
            <div class="s-main" ref="refScrollEl">
                <table>
                    <thead>
                        <tr>
                            <th :style="{'width': '50px','min-width': '50px','height':'32px'}">
                                <p>時間</p>
                            </th>
                            <th :style="{'width': '80px','min-width': '80px','height':'32px'}">
                                <p>配置人数</p>
                            </th>
                            <th :style="{'width': '120px', 'min-width':'120px','height':'32px'}">
                                <p>現時点での人数</p>
                            </th>
                            <th v-for="(col, index) in shift_list" :style="{'width': '120px'}">
                                <HeaderSelect v-model="col.staff_id" :options="(col.id && is_old_data)?[{'id':col.staff_id,'name':col.name,'email':col.email}]:staff_list" :selected_options="selected_staffs" :shift_date="shift_date" :width="120" :shift_index="index" @change="seletedStaff"></HeaderSelect>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <ScheduleTitle class="s-col-body" :list="time_list"></ScheduleTitle>
                            </td>
                            <td>
                                <ScheduleTitle class="s-col-body" :list="post_list"></ScheduleTitle>
                            </td>
                            <td>
                                <ScheduleTitle class="s-col-body" :list="real_post_list"></ScheduleTitle>
                            </td>
                            <td v-for="(col, index) in shift_list">
                                <ScheduleItem :value="col" :index="index" class="s-col-body" :width="120" @setShift="setShift" @moveScroll="moveScroll"></ScheduleItem>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <Spin size="large" fix v-if="loading"></Spin>
    </div>
</template>

<script>
    import HeaderSelect from "./HeaderSelect";
    import ScheduleItem from "./ScheduleItem";
    import ScheduleTitle from "./ScheduleTitle";
    export default {
        name: "ScheduleView",
        components: {ScheduleItem, HeaderSelect, ScheduleTitle},
        props:{
            shift_select_date:{
                type: Date,
                default: new Date()
            },
            shift_list:{
                type: Array,
                default: () => []
            },
            staff_list:{
                type: Array,
                default: () => []
            },
            post_times:{
                type: Array,
                default: () => []
            },
            col_width:{
                type: Number,
                default: 150
            },
            height:{
                type: Number,
                default: 600
            },
            loading: {
                type:Boolean,
                default: false
            },
        },
        data(){
            return {
                time_list:[],
                post_list:[],
                shift_date: this.shift_select_date,
                showSpin:this.loading,
                end_hour: 36
            }
        },
        methods:{
            setShift(data){
                if(data.index >= 0){
                    if(data.is_delete){
                        this.shift_list.splice(data.index, 1);
                    }
                    else{
                        this.shift_list[data.index].s_time = data.s_time
                        this.shift_list[data.index].e_time = data.e_time
                        this.shift_list[data.index].ks_time = data.ks_time
                        this.shift_list[data.index].ke_time = data.ke_time
                    }
                }
            },
            setScrollTop(top){
                this.$refs.refScrollEl.scrollTop = top
            },
            moveScroll(diff){
                this.$refs.refScrollEl.scrollTop += diff
            },
            seletedStaff(data){
                this.$set(this.shift_list[data.shift_index], "name", this.staff_list[data.staff_index].name)
                this.$set(this.shift_list[data.shift_index], "email", this.staff_list[data.staff_index].email)
            }
        },
        mounted() {
            this.time_list = []
            this.post_list = []
            let j = 0
            for(let i=0; i< this.end_hour*60; i+=15){
                this.time_list.push(this.$utils.Datetimes.num2hi(i))
                this.post_list.push((this.post_times && this.post_times[j]) ? this.post_times[j].number : 0)
                j++
            }
        },
        computed:{
            selected_staffs(){
                return this.shift_list.map((shift)=>{
                    return shift.staff_id
                })
            },
            real_post_list(){
                let list = []
                for(let i=0; i< this.end_hour*60; i+=15){
                    list.push(0)
                }
                this.shift_list.map((shift)=>{
                    for(let i=shift.s_time; i< shift.e_time; i+=15){
                        if(shift.ke_time && i >= shift.ks_time && i < shift.ke_time){
                            continue
                        }
                        list[parseInt(i/15)] += 1
                    }
                })
                return list
            },
            is_old_data(){
                const now = new Date();
                return (this.shift_date < new Date(now.getFullYear(),now.getMonth(), now.getDate()))
            }
        },
        watch:{
            loading(val){
                this.showSpin = val;
            },
            post_times(val){
                this.post_list = []
                for(let i=0; i< this.end_hour * 4; i++){
                    this.post_list.push((this.post_times && this.post_times[i]) ? this.post_times[i].number : 0)
                }
            },
            shift_select_date(val){
                this.shift_date = val
            }
        },
        created() {
        }
    }
</script>

<style scoped>
    .s-layout{
        position: relative;
    }
    .s-content{
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: solid 1px #999;
    }
    .s-main{
        top: 30px;
        width: 100%;
        height: 600px;
        overflow: scroll;
    }
    .s-main table>thead>tr>th{
        background-color: white;
        position: sticky;
        position: -webkit-sticky;
        top: 0;
        border: 1px solid #999;
        z-index: 2;
    }
    .s-body{
       float: left;
        display: inline-flex;
    }
    .s-left{
        float: left;
    }
    .s-right{
        float: left;
    }
    .s-col{
        height: 100%;
        float: left;
    }
    .s-col-header{
        position: absolute;
        top: 0;
        margin: 0;
        padding: 0;
        height: 30px;
        float: left;
        background: #fff;
        border-bottom: 1px solid #666;
        border-right: 1px solid #666;
        text-align: center;
        z-index: 10;
    }
    .s-col-body{
    }
</style>